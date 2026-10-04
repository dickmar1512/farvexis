<?php

class SunatService {
    private array  $config;
    private array  $carpetas;
    private SunatXmlService $xmlService;

    public function __construct(?array $config = null) {
        $autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        $this->config     = $config ?? SunatConfig::getConfig();
        $this->carpetas   = $this->config['carpetas'];
        $this->xmlService = new SunatXmlService($this->config);
        $this->inicializarCarpetas();
    }

    /**
     * Enviar comprobante (Factura o Boleta) a SUNAT
     */
    public function enviarComprobante(array $comprobante, array $detalles, array $cliente): array {
        $nombre = $this->nombreArchivo($comprobante);
        try {
            // 1. Generar XML sin firmar
            $xml = $this->xmlService->generarXml($comprobante, $detalles, $cliente);
            $this->guardar($this->carpetas['parse'], $nombre . '.xml', $xml);
            $this->log("PARSE: {$nombre}.xml generado");

            // 1b. Guardar snapshot JSON en DATA/
            $this->guardarDataJson($nombre, $comprobante, $detalles, $cliente);

            // 2. Si no hay certificado -> modo local / pendiente
            if (empty($this->config['cert_path']) || !file_exists($this->config['cert_path'])) {
                $this->log("MODO SIN CERTIFICADO: {$nombre} guardado sin firmar");
                return [
                    'exito'        => false,
                    'codigo'       => 'SIN_CERT',
                    'descripcion'  => 'Comprobante guardado localmente. No se encontró certificado digital para firmar.',
                    'xml_firmado'  => $xml,
                    'codigo_hash'  => '',
                    'cdr_xml'      => '',
                    'estado_sunat' => 'pendiente',
                ];
            }

            // 3. Firmar XML
            $firmaResult = $this->firmarXml($xml);
            $xmlFirmado  = $firmaResult['xml'];
            $hash        = $firmaResult['hash'];

            $this->guardar($this->carpetas['firma'], $nombre . '.xml', $xmlFirmado);
            $this->log("FIRMA: {$nombre}.xml firmado");

            // 4. Crear ZIP
            $zipPath = $this->crearZip($xmlFirmado, $nombre);
            $this->log("ENVIO: {$nombre}.zip creado");

            // 5. Enviar a SUNAT vía SOAP billService
            $endpoint      = $this->resolverEndpoint('factura');
            $respuestaSoap = $this->enviarSoapCurl($zipPath, $nombre . '.zip', $endpoint);
            $cdr           = $this->procesarCdr($respuestaSoap, $nombre);

            // 6. Archivar XML firmado en REPO/
            $this->guardar($this->carpetas['repo'], $nombre . '.xml', $xmlFirmado);
            $this->log("REPO: {$nombre}.xml archivado");

            $esAceptado = ($cdr['codigo'] === '0' || $cdr['codigo'] === 0 || $cdr['codigo'] === '0000');
            $estadoSunat = $esAceptado ? 'aceptado' : ($cdr['codigo'] === 'ERR' ? 'pendiente' : 'rechazado');

            return [
                'exito'        => $esAceptado,
                'codigo'       => (string)$cdr['codigo'],
                'descripcion'  => $cdr['descripcion'],
                'xml_firmado'  => $xmlFirmado,
                'codigo_hash'  => $hash,
                'cdr_xml'      => $cdr['xml'],
                'estado_sunat' => $estadoSunat,
            ];

        } catch (Exception $e) {
            $this->log("ERROR [{$nombre}]: " . $e->getMessage());
            return [
                'exito'        => false,
                'codigo'       => 'ERR',
                'descripcion'  => $e->getMessage(),
                'xml_firmado'  => $xmlFirmado ?? ($xml ?? null),
                'codigo_hash'  => $hash ?? null,
                'cdr_xml'      => null,
                'estado_sunat' => 'pendiente',
            ];
        }
    }

    /**
     * Enviar Nota de Crédito o Débito
     */
    public function enviarNota(array $comprobante, array $detalles, array $cliente): array {
        return $this->enviarComprobante($comprobante, $detalles, $cliente);
    }

    /**
     * Comunicación de baja (anulación)
     */
    public function enviarBaja(string $serie, int $correlativo, string $tipoDoc, string $motivo): array {
        $ruc      = $this->config['ruc'];
        $fecha    = date('Y-m-d');
        $corrBaja = date('Ymd') . '01';
        $nombre   = "{$ruc}-RA-{$corrBaja}";

        try {
            $xml = $this->generarXmlBaja($ruc, $fecha, $serie, $correlativo, $tipoDoc, $motivo, $corrBaja);
            $xmlFirmado = (!empty($this->config['cert_path']) && file_exists($this->config['cert_path'])) 
                ? $this->firmarXml($xml)['xml'] 
                : $xml;

            $this->guardar($this->carpetas['parse'], $nombre . '.xml', $xml);
            $this->guardar($this->carpetas['firma'], $nombre . '.xml', $xmlFirmado);

            $zipPath  = $this->crearZip($xmlFirmado, $nombre);
            $endpoint = $this->resolverEndpoint('factura');
            $respuesta = $this->enviarSoapCurl($zipPath, $nombre . '.zip', $endpoint);

            $this->log("BAJA enviada: {$serie}-{$correlativo}");
            return ['exito' => true, 'descripcion' => 'Baja enviada correctamente.'];

        } catch (Exception $e) {
            $this->log("ERROR BAJA [{$nombre}]: " . $e->getMessage());
            return ['exito' => false, 'descripcion' => $e->getMessage()];
        }
    }

    /**
     * Consultar CDR por número de comprobante
     */
    public function consultarCdr(string $ruc, string $tipoDoc, string $serie, int $correlativo): array {
        $endpoint = $this->config['endpoints']['cdr_consulta'];
        $usuario  = $ruc . $this->config['usuario_sol'];
        $clave    = $this->config['clave_sol'];

        $soap = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ser="http://service.sunat.gob.pe"
    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
  <soapenv:Header>
    <wsse:Security>
      <wsse:UsernameToken>
        <wsse:Username>' . htmlspecialchars($usuario, ENT_XML1) . '</wsse:Username>
        <wsse:Password>' . htmlspecialchars($clave, ENT_XML1) . '</wsse:Password>
      </wsse:UsernameToken>
    </wsse:Security>
  </soapenv:Header>
  <soapenv:Body>
    <ser:getStatusCdr>
      <rucComprobante>' . $ruc . '</rucComprobante>
      <tipoComprobante>' . $tipoDoc . '</tipoComprobante>
      <serieComprobante>' . $serie . '</serieComprobante>
      <numero>' . $correlativo . '</numero>
    </ser:getStatusCdr>
  </soapenv:Body>
</soapenv:Envelope>';

        try {
            $resp = $this->curlPost($endpoint, $soap);
            $this->log("CDR CONSULTA: {$ruc}-{$tipoDoc}-{$serie}-{$correlativo}");
            $extracted = $this->extraerRespuestaSoap($resp);
            $cdr = $this->procesarCdr($extracted, "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}");
            return ['exito' => true, 'codigo' => $cdr['codigo'], 'descripcion' => $cdr['descripcion'], 'respuesta' => $resp];
        } catch (Exception $e) {
            return ['exito' => false, 'descripcion' => $e->getMessage()];
        }
    }

    private function resolverEndpoint(string $tipo = 'factura'): string {
        $ambiente  = $this->config['ambiente'];
        $endpoints = $this->config['endpoints'];
        $key       = $tipo . '_' . $ambiente;

        if (!isset($endpoints[$key])) {
            throw new Exception("Endpoint no definido para: {$key}");
        }
        return $endpoints[$key];
    }

    private function enviarSoapCurl(string $zipPath, string $fileName, string $endpoint): string {
        $usuario   = $this->config['ruc'] . $this->config['usuario_sol'];
        $clave     = $this->config['clave_sol'];
        $zipBase64 = base64_encode(file_get_contents($zipPath));

        $soap = '<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:ser="http://service.sunat.gob.pe"
    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
  <soapenv:Header>
    <wsse:Security>
      <wsse:UsernameToken>
        <wsse:Username>' . htmlspecialchars($usuario, ENT_XML1) . '</wsse:Username>
        <wsse:Password>' . htmlspecialchars($clave, ENT_XML1) . '</wsse:Password>
      </wsse:UsernameToken>
    </wsse:Security>
  </soapenv:Header>
  <soapenv:Body>
    <ser:sendBill>
      <fileName>' . htmlspecialchars($fileName, ENT_XML1) . '</fileName>
      <contentFile>' . $zipBase64 . '</contentFile>
    </ser:sendBill>
  </soapenv:Body>
</soapenv:Envelope>';

        $this->log("SOAP: Enviando a {$endpoint} (archivo: {$fileName})");
        $rawResponse = $this->curlPost($endpoint, $soap);
        $extracted   = $this->extraerRespuestaSoap($rawResponse);
        $this->log("SOAP: Respuesta extraída (largo: " . strlen($extracted) . ")");
        
        return $extracted;
    }

    private function curlPost(string $url, string $body): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 35,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
                'Content-Length: ' . strlen($body),
            ],
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (is_resource($ch)) {
            @curl_close($ch);
        }

        if ($error) {
            throw new Exception("Error cURL conectando a SUNAT: {$error}");
        }

        if ($httpCode === 500 && !empty($response)) {
            $msg = $this->extraerMensajeSoapFault($response);
            throw new Exception("SUNAT SoapFault [500]: " . ($msg ?: 'Error interno del servicio SUNAT'));
        }

        if ($httpCode !== 200 && $httpCode !== 500) {
            throw new Exception("SUNAT HTTP {$httpCode}: " . substr($response, 0, 300));
        }

        return $response;
    }

    private function extraerMensajeSoapFault(string $xml): string {
        if (preg_match('/<faultstring[^>]*>(.*?)<\/faultstring>/si', $xml, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function extraerRespuestaSoap(string $soapResponse): string {
        if (empty($soapResponse)) return '';

        $tags = ['applicationResponse', 'ticket', 'return'];
        foreach ($tags as $tag) {
            if (preg_match('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/si', $soapResponse, $matches)) {
                return trim($matches[1]);
            }
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        if ($dom->loadXML($soapResponse)) {
            foreach ($tags as $tag) {
                $nodes = $dom->getElementsByTagName($tag);
                if ($nodes->length > 0) return trim($nodes->item(0)->nodeValue);
            }
        }

        return '';
    }

    private function procesarCdr(string $cdrBase64, string $nombre): array {
        if (empty($cdrBase64)) {
            return ['codigo' => 'ERR', 'descripcion' => 'SUNAT no devolvió CDR (respuesta vacía)', 'xml' => ''];
        }

        $cdrZip = base64_decode($cdrBase64);
        $tmpZip = tempnam(sys_get_temp_dir(), 'cdr_') . '.zip';
        file_put_contents($tmpZip, $cdrZip);

        $zip = new ZipArchive();
        if ($zip->open($tmpZip) !== true) {
            @unlink($tmpZip);
            return ['codigo' => 'ERR', 'descripcion' => 'CDR ZIP devuelto por SUNAT es inválido', 'xml' => ''];
        }

        $cdrXml   = '';
        $numFiles = $zip->numFiles;
        for ($i = 0; $i < $numFiles; $i++) {
            $fn = $zip->getNameIndex($i);
            if (str_ends_with(strtolower($fn), '.xml')) {
                $cdrXml = $zip->getFromIndex($i);
                break;
            }
        }
        $zip->close();
        @unlink($tmpZip);

        // Guardar ZIP y XML del CDR en RPTA/
        $this->guardar($this->carpetas['rpta'], 'R-' . $nombre . '.zip', $cdrZip);
        if ($cdrXml) {
            $this->guardar($this->carpetas['rpta'], 'R-' . $nombre . '.xml', $cdrXml);
        }

        $codigo = '0';
        $descripcion = 'Comprobante aceptado por SUNAT';

        if ($cdrXml) {
            if (preg_match('/<cbc:ResponseCode[^>]*>(.*?)<\/cbc:ResponseCode>/si', $cdrXml, $m)) {
                $codigo = trim($m[1]);
            }
            if (preg_match('/<cbc:Description[^>]*>(.*?)<\/cbc:Description>/si', $cdrXml, $m)) {
                $descripcion = trim($m[1]);
            }
        }

        $this->log("RPTA [{$nombre}]: Código={$codigo} - {$descripcion}");
        return ['codigo' => $codigo, 'descripcion' => $descripcion, 'xml' => $cdrXml];
    }

    /**
     * Firma digital nativa RSA-SHA256 con certificado PKCS#12 (.pfx)
     */
    private function firmarXml(string $xml): array {
        $certPath = $this->config['cert_path'] ?? '';
        if (empty($certPath) || !file_exists($certPath)) {
            throw new Exception("No se encontró el archivo de certificado digital PFX.");
        }

        $certData = file_get_contents($certPath);
        $certs    = [];
        $pwd      = $this->config['cert_password'] ?? '';

        if (!@openssl_pkcs12_read($certData, $certs, $pwd)) {
            // Intentar auto-detección si es certificado demo conocido
            $candidatos = ['20611985500', '10465785531'];
            if (preg_match('/(\d{11})/', basename($certPath), $mRuc)) {
                array_unshift($candidatos, $mRuc[1]);
            }
            foreach ($candidatos as $candPwd) {
                if (@openssl_pkcs12_read($certData, $certs, $candPwd)) {
                    $this->log("PFX: Contraseña recuperada automáticamente ({$candPwd})");
                    break;
                }
            }
        }

        if (empty($certs) || empty($certs['pkey']) || empty($certs['cert'])) {
            $msg = "No se pudo leer el certificado digital PFX: contraseña incorrecta o archivo dañado. Verifique la contraseña en Configuración.";
            $this->log("ERROR: {$msg}");
            throw new Exception($msg);
        }

        $privateKey = $certs['pkey'];
        $x509Clean  = $this->limpiarCertificado($certs['cert']);

        if (!class_exists('\RobRichards\XMLSecLibs\XMLSecurityDSig')) {
            throw new Exception("La librería robrichards/xmlseclibs no está cargada. Ejecute 'composer require robrichards/xmlseclibs'.");
        }

        // 1. Cargar XML en DOMDocument
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = false;
        $doc->loadXML($xml);

        // 2. Inicializar XMLSecurityDSig
        $objDSig = new \RobRichards\XMLSecLibs\XMLSecurityDSig();
        $objDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);
        $objDSig->addReference(
            $doc,
            \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['force_uri' => true]
        );

        // 3. Crear llave privada y firmar
        $objKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(\RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($privateKey, false);
        $objDSig->sign($objKey);

        // 4. Agregar certificado público
        $objDSig->add509Cert($x509Clean, true, false);

        // 5. Insertar Signature en <ext:ExtensionContent>
        $extContent = $doc->getElementsByTagName('ExtensionContent')->item(0);
        if ($extContent) {
            $objDSig->appendSignature($extContent);
        } else {
            $this->log('ADVERTENCIA: No se encontró <ext:ExtensionContent>, agregando al final del documento.');
            $objDSig->appendSignature($doc->documentElement);
        }

        // 6. Extraer el hash generado
        $hash = '';
        $digestNode = $doc->getElementsByTagName('DigestValue')->item(0);
        if ($digestNode) {
            $hash = $digestNode->nodeValue;
        }

        $this->log('FIRMA: XML firmado exitosamente con xmlseclibs');
        return ['xml' => $doc->saveXML(), 'hash' => $hash];
    }

    private function limpiarCertificado(string $pem): string {
        $pem = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----'], '', $pem);
        return trim(str_replace(["\r", "\n", ' '], '', $pem));
    }

    private function nombreArchivo(array $comprobante): string {
        return $this->config['ruc']
            . '-' . $comprobante['tipo_doc']
            . '-' . $comprobante['serie']
            . '-' . str_pad($comprobante['correlativo'], 8, '0', STR_PAD_LEFT);
    }

    private function crearZip(string $xmlContent, string $nombre): string {
        $dir     = $this->carpetas['envio'];
        $zipPath = $dir . $nombre . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("No se pudo crear ZIP en: {$zipPath}");
        }
        $zip->addFromString($nombre . '.xml', $xmlContent);
        $zip->close();
        return $zipPath;
    }

    private function guardar(string $dir, string $archivo, string $contenido): void {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $archivo, $contenido);
    }

    private function inicializarCarpetas(): void {
        foreach ($this->carpetas as $nombre => $ruta) {
            if (!is_dir($ruta)) {
                mkdir($ruta, 0755, true);
            }
        }
    }

    private function generarXmlBaja(string $ruc, string $fecha, string $serie, int $correlativo, string $tipoDoc, string $motivo, string $corrBaja): string {
        return '<?xml version="1.0" encoding="UTF-8"?>
<VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1">
  <ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>
  <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
  <cbc:CustomizationID>1.0</cbc:CustomizationID>
  <cbc:ID>RA-' . $corrBaja . '</cbc:ID>
  <cbc:ReferenceDate>' . $fecha . '</cbc:ReferenceDate>
  <cbc:IssueDate>' . $fecha . '</cbc:IssueDate>
  <cac:AccountingSupplierParty>
    <cac:Party>
      <cac:PartyIdentification>
        <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>
      </cac:PartyIdentification>
      <cac:PartyLegalEntity>
        <cbc:RegistrationName><![CDATA[' . $this->config['razon_social'] . ']]></cbc:RegistrationName>
      </cac:PartyLegalEntity>
    </cac:Party>
  </cac:AccountingSupplierParty>
  <sac:VoidedDocumentsLine>
    <cbc:LineID>1</cbc:LineID>
    <cbc:DocumentTypeCode>' . $tipoDoc . '</cbc:DocumentTypeCode>
    <sac:DocumentSerialID>' . $serie . '</sac:DocumentSerialID>
    <sac:DocumentNumberID>' . $correlativo . '</sac:DocumentNumberID>
    <sac:VoidReasonDescription>' . htmlspecialchars($motivo, ENT_XML1) . '</sac:VoidReasonDescription>
  </sac:VoidedDocumentsLine>
</VoidedDocuments>';
    }

    private function guardarDataJson(string $nombre, array $comprobante, array $detalles, array $cliente): void {
        $carpetaData = $this->carpetas['data'];
        if (!is_dir($carpetaData)) mkdir($carpetaData, 0755, true);

        $snapshot = [
            '_id'           => $comprobante['id'] ?? null,
            '_nombre'       => $nombre,
            '_generado'     => date('Y-m-d H:i:s'),
            '_ambiente'     => $this->config['ambiente'],
            'emisor' => [
                'ruc'          => $this->config['ruc'],
                'razon_social' => $this->config['razon_social'],
                'direccion'    => $this->config['direccion'],
                'ubigeo'       => $this->config['ubigeo'],
            ],
            'cliente' => [
                'razon_social' => $cliente['razon_social'] ?? '',
                'numero_doc'   => $cliente['numero_doc'] ?? '',
                'tipo_doc'     => $cliente['tipo_doc'] ?? '',
                'direccion'    => $cliente['direccion'] ?? '',
            ],
            'comprobante' => [
                'tipo_doc'       => $comprobante['tipo_doc'],
                'serie'          => $comprobante['serie'],
                'correlativo'    => $comprobante['correlativo'],
                'fecha_emision'  => $comprobante['fecha_emision'],
                'moneda'         => $comprobante['moneda'] ?? 'PEN',
                'condicion_pago' => $comprobante['condicion_pago'] ?? '',
                'observaciones'  => $comprobante['observaciones'] ?? '',
                'documento_referencia' => $comprobante['documento_referencia'] ?? '',
                'nota_motivo'          => $comprobante['nota_motivo'] ?? '',
                'nota_sustento'        => $comprobante['nota_sustento'] ?? '',
            ],
            'totales' => [
                'subtotal_gravado'   => $comprobante['subtotal_gravado'] ?? 0,
                'subtotal_exonerado' => $comprobante['subtotal_exonerado'] ?? 0,
                'subtotal_inafecto'  => $comprobante['subtotal_inafecto'] ?? 0,
                'subtotal_gratuito'  => $comprobante['subtotal_gratuito'] ?? 0,
                'descuento_global'   => $comprobante['descuento_global'] ?? 0,
                'igv_total'          => $comprobante['igv_total'] ?? 0,
                'total'              => $comprobante['total'] ?? 0,
                'igv_porcentaje'     => $this->config['igv_porcentaje'] ?? 18,
            ],
            'items' => array_map(fn($d) => [
                'descripcion'     => $d['descripcion'] ?? '',
                'codigo_producto' => $d['codigo_producto'] ?? '',
                'unidad_medida'   => $d['unidad_medida'] ?? 'NIU',
                'cantidad'        => $d['cantidad'] ?? 0,
                'precio_unitario' => $d['precio_unitario'] ?? 0,
                'precio_con_igv'  => $d['precio_con_igv'] ?? 0,
                'igv_tipo'        => $d['igv_tipo'] ?? '20',
                'valor_venta'     => $d['valor_venta'] ?? 0,
                'igv_monto'       => $d['igv_monto'] ?? 0,
                'total_item'      => $d['total_item'] ?? 0,
            ], $detalles),
        ];

        file_put_contents(
            $carpetaData . $nombre . '.json',
            json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        $this->log("DATA: {$nombre}.json generado");
    }

    private function log(string $msg): void {
        $dir  = $this->carpetas['logs'];
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = $dir . 'sunat_' . date('Y-m') . '.log';
        file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
