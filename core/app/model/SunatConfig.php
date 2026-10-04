<?php

class SunatConfig {
    public static function getConfig(): array {
        $empresa = EmpresaData::getDatos();
        
        $ruc = !empty($empresa->Emp_Ruc) ? trim($empresa->Emp_Ruc) : '20000000001';
        $razonSocial = !empty($empresa->Emp_RazonSocial) ? trim($empresa->Emp_RazonSocial) : 'MI EMPRESA S.A.C.';
        $direccion = !empty($empresa->Emp_Direccion) ? trim($empresa->Emp_Direccion) : '-';
        $ambiente = !empty($empresa->sunat_ambiente) ? trim($empresa->sunat_ambiente) : 'beta';
        $usuarioSol = !empty($empresa->sunat_usuario_sol) ? trim($empresa->sunat_usuario_sol) : 'MODDATOS';
        $claveSol = !empty($empresa->sunat_clave_sol) ? trim($empresa->sunat_clave_sol) : 'moddatos';
        $certPass = !empty($empresa->sunat_cert_pass) ? trim($empresa->sunat_cert_pass) : '';
        $igvTipoDefecto = !empty($empresa->sunat_igv_tipo_defecto) ? trim($empresa->sunat_igv_tipo_defecto) : '20';
        
        // Determinar ruta del certificado
        $baseDir = dirname(dirname(dirname(__DIR__))); // c:\laragon\www\ContivaPharmacy
        $certDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'CERT';
        $certPath = '';

        if (!empty($empresa->sunat_cert_path) && file_exists($empresa->sunat_cert_path)) {
            $certPath = $empresa->sunat_cert_path;
        } elseif (!empty($empresa->sunat_cert_path) && file_exists($certDir . DIRECTORY_SEPARATOR . basename($empresa->sunat_cert_path))) {
            $certPath = $certDir . DIRECTORY_SEPARATOR . basename($empresa->sunat_cert_path);
        } else {
            // Buscar cualquier certificado .pfx en storage/CERT/
            $pfxFiles = glob($certDir . DIRECTORY_SEPARATOR . '*.pfx');
            if (!empty($pfxFiles)) {
                $certPath = $pfxFiles[0];
            }
        }

        // Validar que la contraseña pueda abrir el archivo PFX; si no, auto-detectar para certificados conocidos
        if (!empty($certPath) && file_exists($certPath)) {
            $certData = @file_get_contents($certPath);
            $testCerts = [];
            if (!@openssl_pkcs12_read($certData, $testCerts, $certPass)) {
                $candidatos = ['20611985500', '10465785531'];
                if (preg_match('/(\d{11})/', basename($certPath), $mRuc)) {
                    array_unshift($candidatos, $mRuc[1]);
                }
                foreach ($candidatos as $candPwd) {
                    if (@openssl_pkcs12_read($certData, $testCerts, $candPwd)) {
                        $certPass = $candPwd;
                        break;
                    }
                }
            }
        }

        $carpetas = [
            'cert'  => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'CERT' . DIRECTORY_SEPARATOR,
            'data'  => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'DATA' . DIRECTORY_SEPARATOR,
            'firma' => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'FIRMA' . DIRECTORY_SEPARATOR,
            'envio' => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'ENVIO' . DIRECTORY_SEPARATOR,
            'parse' => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'PARSE' . DIRECTORY_SEPARATOR,
            'repo'  => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'REPO' . DIRECTORY_SEPARATOR,
            'rpta'  => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'RPTA' . DIRECTORY_SEPARATOR,
            'logs'  => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        ];

        return [
            'ruc'                 => $ruc,
            'razon_social'        => $razonSocial,
            'nombre_comercial'    => $razonSocial,
            'direccion'           => $direccion,
            'urbanizacion'        => '-',
            'distrito'            => 'LIMA',
            'provincia'           => 'LIMA',
            'departamento'        => 'LIMA',
            'ubigeo'              => '150101',
            'codigo_pais'         => 'PE',
            'ambiente'            => $ambiente,
            'usuario_sol'         => $usuarioSol,
            'clave_sol'           => $claveSol,
            'cert_path'           => $certPath,
            'cert_password'       => $certPass,
            'igv_porcentaje'      => 18,
            'igv_tipo_defecto'    => $igvTipoDefecto,
            'moneda'              => 'PEN',
            'tipo_cambio'         => 3.8000,
            'ubl_version'         => '2.1',
            'customization_id'    => '2.0',
            'codigo_local_anexo'  => '0000',
            'endpoints'           => [
                'factura_produccion' => 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService',
                'factura_beta'       => 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService',
                'cdr_consulta'       => 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService',
                'ret_perc_produccion'=> 'https://e-factura.sunat.gob.pe/ol-ti-itemision-otroscpe-gem/billService',
                'ret_perc_beta'      => 'https://e-beta.sunat.gob.pe/ol-ti-itemision-otroscpe-gem-beta/billService',
                'guia_produccion'    => 'https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService',
                'guia_beta'          => 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService',
            ],
            'carpetas'            => $carpetas,
        ];
    }
}
