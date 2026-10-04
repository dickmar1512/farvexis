<?php

class SunatXmlService {
    private array $config;
    private float $igv;

    public function __construct(?array $config = null) {
        $this->config = $config ?? SunatConfig::getConfig();
        $this->igv    = ($this->config['igv_porcentaje'] ?? 18) / 100;
    }

    /**
     * Genera XML UBL 2.1 según el tipo de documento
     */
    public function generarXml(array $comprobante, array $detalles, array $cliente): string {
        $tipoDoc = $comprobante['tipo_doc'];
        
        if ($tipoDoc === '01' || $tipoDoc === '03') {
            return $this->buildInvoice($comprobante, $detalles, $cliente);
        } else if ($tipoDoc === '07') {
            return $this->buildCreditNote($comprobante, $detalles, $cliente);
        } else if ($tipoDoc === '08') {
            return $this->buildDebitNote($comprobante, $detalles, $cliente);
        }
        
        throw new Exception("Tipo de documento no soportado para XML: " . $tipoDoc);
    }

    private function buildInvoice(array $c, array $detalles, array $cliente): string {
        $cfg = $this->config;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"' . "\n";
        $xml .= '  xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"' . "\n";
        $xml .= '  xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"' . "\n";
        $xml .= '  xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"' . "\n";
        $xml .= '  xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . "\n";

        $xml .= '  <ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>' . "\n";
        $xml .= '  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>' . "\n";
        $xml .= '  <cbc:CustomizationID>2.0</cbc:CustomizationID>' . "\n";
        $xml .= '  <cbc:ID>' . htmlspecialchars($c['serie'] . '-' . str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT)) . '</cbc:ID>' . "\n";
        $xml .= '  <cbc:IssueDate>' . $c['fecha_emision'] . '</cbc:IssueDate>' . "\n";
        $xml .= '  <cbc:IssueTime>' . ($c['hora_emision'] ?? date('H:i:s')) . '</cbc:IssueTime>' . "\n";
        $xml .= '  <cbc:InvoiceTypeCode listID="0101">' . $c['tipo_doc'] . '</cbc:InvoiceTypeCode>' . "\n";
        $xml .= '  <cbc:Note languageLocaleID="1000"><![CDATA[' . $this->numeroALetras($c['total'], $c['moneda'] ?? 'PEN') . ']]></cbc:Note>' . "\n";
        if (($c['subtotal_gratuito'] ?? 0) > 0) {
            $xml .= '  <cbc:Note languageLocaleID="1002">TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE</cbc:Note>' . "\n";
        }
        $xml .= '  <cbc:DocumentCurrencyCode>' . ($c['moneda'] ?? 'PEN') . '</cbc:DocumentCurrencyCode>' . "\n";

        $xml .= $this->buildSignature($cfg);
        $xml .= $this->buildSupplier($cfg);
        $xml .= $this->buildCustomer($cliente);
        $xml .= $this->buildPaymentTerms($c);
        $xml .= $this->buildTaxTotals($c);

        $lineExtensionAmount = ($c['subtotal_gravado'] ?? 0) + ($c['subtotal_exonerado'] ?? 0) + ($c['subtotal_inafecto'] ?? 0);
        $xml .= '  <cac:LegalMonetaryTotal>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";
        $xml .= '    <cbc:TaxExclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:TaxExclusiveAmount>' . "\n";
        $xml .= '    <cbc:TaxInclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:TaxInclusiveAmount>' . "\n";
        $xml .= '    <cbc:PayableAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:PayableAmount>' . "\n";
        $xml .= '  </cac:LegalMonetaryTotal>' . "\n";

        foreach ($detalles as $i => $det) {
            $xml .= $this->buildInvoiceLine($i + 1, $det, $c['moneda'] ?? 'PEN');
        }
        $xml .= '</Invoice>';
        return $xml;
    }

    private function buildCreditNote(array $c, array $detalles, array $cliente): string {
        $cfg = $this->config;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"' . "\n";
        $xml .= '  xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"' . "\n";
        $xml .= '  xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"' . "\n";
        $xml .= '  xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"' . "\n";
        $xml .= '  xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . "\n";

        $xml .= '  <ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>' . "\n";
        $xml .= '  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>' . "\n";
        $xml .= '  <cbc:CustomizationID>2.0</cbc:CustomizationID>' . "\n";
        if (($c['subtotal_gratuito'] ?? 0) > 0) {
            $xml .= '  <cbc:Note languageLocaleID="1002">TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE</cbc:Note>' . "\n";
        }
        $xml .= '  <cbc:ID>' . htmlspecialchars($c['serie'] . '-' . str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT)) . '</cbc:ID>' . "\n";
        $xml .= '  <cbc:IssueDate>' . $c['fecha_emision'] . '</cbc:IssueDate>' . "\n";
        $xml .= '  <cbc:IssueTime>' . ($c['hora_emision'] ?? date('H:i:s')) . '</cbc:IssueTime>' . "\n";
        $xml .= '  <cbc:Note languageLocaleID="1000"><![CDATA[' . $this->numeroALetras($c['total'], $c['moneda'] ?? 'PEN') . ']]></cbc:Note>' . "\n";
        $xml .= '  <cbc:DocumentCurrencyCode>' . ($c['moneda'] ?? 'PEN') . '</cbc:DocumentCurrencyCode>' . "\n";

        $xml .= '  <cac:DiscrepancyResponse>' . "\n";
        $xml .= '    <cbc:ReferenceID>' . htmlspecialchars($c['documento_referencia'] ?? '') . '</cbc:ReferenceID>' . "\n";
        $xml .= '    <cbc:ResponseCode>' . htmlspecialchars($c['nota_motivo'] ?? '01') . '</cbc:ResponseCode>' . "\n";
        $xml .= '    <cbc:Description><![CDATA[' . htmlspecialchars($c['nota_sustento'] ?? 'Anulación de la operación') . ']]></cbc:Description>' . "\n";
        $xml .= '  </cac:DiscrepancyResponse>' . "\n";

        $refTipo = (strpos($c['documento_referencia'] ?? '', 'F') === 0) ? '01' : '03';
        $xml .= '  <cac:BillingReference><cac:InvoiceDocumentReference>' . "\n";
        $xml .= '    <cbc:ID>' . htmlspecialchars($c['documento_referencia'] ?? '') . '</cbc:ID>' . "\n";
        $xml .= '    <cbc:DocumentTypeCode>' . $refTipo . '</cbc:DocumentTypeCode>' . "\n";
        $xml .= '  </cac:InvoiceDocumentReference></cac:BillingReference>' . "\n";

        $xml .= $this->buildSignature($cfg);
        $xml .= $this->buildSupplier($cfg);
        $xml .= $this->buildCustomer($cliente);
        $xml .= $this->buildTaxTotals($c);

        $lineExtensionAmount = ($c['subtotal_gravado'] ?? 0) + ($c['subtotal_exonerado'] ?? 0) + ($c['subtotal_inafecto'] ?? 0);
        $xml .= '  <cac:LegalMonetaryTotal>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";
        $xml .= '    <cbc:TaxExclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:TaxExclusiveAmount>' . "\n";
        $xml .= '    <cbc:TaxInclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:TaxInclusiveAmount>' . "\n";
        $xml .= '    <cbc:PayableAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:PayableAmount>' . "\n";
        $xml .= '  </cac:LegalMonetaryTotal>' . "\n";

        foreach ($detalles as $i => $det) {
            $xml .= $this->buildCreditNoteLine($i + 1, $det, $c['moneda'] ?? 'PEN');
        }
        $xml .= '</CreditNote>';
        return $xml;
    }

    private function buildDebitNote(array $c, array $detalles, array $cliente): string {
        $cfg = $this->config;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2"' . "\n";
        $xml .= '  xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"' . "\n";
        $xml .= '  xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"' . "\n";
        $xml .= '  xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"' . "\n";
        $xml .= '  xmlns:ds="http://www.w3.org/2000/09/xmldsig#">' . "\n";

        $xml .= '  <ext:UBLExtensions><ext:UBLExtension><ext:ExtensionContent/></ext:UBLExtension></ext:UBLExtensions>' . "\n";
        $xml .= '  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>' . "\n";
        $xml .= '  <cbc:CustomizationID>2.0</cbc:CustomizationID>' . "\n";
        if (($c['subtotal_gratuito'] ?? 0) > 0) {
            $xml .= '  <cbc:Note languageLocaleID="1002">TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE</cbc:Note>' . "\n";
        }
        $xml .= '  <cbc:ID>' . htmlspecialchars($c['serie'] . '-' . str_pad($c['correlativo'], 8, '0', STR_PAD_LEFT)) . '</cbc:ID>' . "\n";
        $xml .= '  <cbc:IssueDate>' . $c['fecha_emision'] . '</cbc:IssueDate>' . "\n";
        $xml .= '  <cbc:IssueTime>' . ($c['hora_emision'] ?? date('H:i:s')) . '</cbc:IssueTime>' . "\n";
        $xml .= '  <cbc:Note languageLocaleID="1000"><![CDATA[' . $this->numeroALetras($c['total'], $c['moneda'] ?? 'PEN') . ']]></cbc:Note>' . "\n";
        $xml .= '  <cbc:DocumentCurrencyCode>' . ($c['moneda'] ?? 'PEN') . '</cbc:DocumentCurrencyCode>' . "\n";

        $xml .= '  <cac:DiscrepancyResponse>' . "\n";
        $xml .= '    <cbc:ReferenceID>' . htmlspecialchars($c['documento_referencia'] ?? '') . '</cbc:ReferenceID>' . "\n";
        $xml .= '    <cbc:ResponseCode>' . htmlspecialchars($c['nota_motivo'] ?? '01') . '</cbc:ResponseCode>' . "\n";
        $xml .= '    <cbc:Description><![CDATA[' . htmlspecialchars($c['nota_sustento'] ?? 'Penalidad / Aumento de valor') . ']]></cbc:Description>' . "\n";
        $xml .= '  </cac:DiscrepancyResponse>' . "\n";

        $refTipo = (strpos($c['documento_referencia'] ?? '', 'F') === 0) ? '01' : '03';
        $xml .= '  <cac:BillingReference><cac:InvoiceDocumentReference>' . "\n";
        $xml .= '    <cbc:ID>' . htmlspecialchars($c['documento_referencia'] ?? '') . '</cbc:ID>' . "\n";
        $xml .= '    <cbc:DocumentTypeCode>' . $refTipo . '</cbc:DocumentTypeCode>' . "\n";
        $xml .= '  </cac:InvoiceDocumentReference></cac:BillingReference>' . "\n";

        $xml .= $this->buildSignature($cfg);
        $xml .= $this->buildSupplier($cfg);
        $xml .= $this->buildCustomer($cliente);
        $xml .= $this->buildTaxTotals($c);

        $lineExtensionAmount = ($c['subtotal_gravado'] ?? 0) + ($c['subtotal_exonerado'] ?? 0) + ($c['subtotal_inafecto'] ?? 0);
        $xml .= '  <cac:RequestedMonetaryTotal>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";
        $xml .= '    <cbc:TaxExclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($lineExtensionAmount, 2, '.', '') . '</cbc:TaxExclusiveAmount>' . "\n";
        $xml .= '    <cbc:TaxInclusiveAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:TaxInclusiveAmount>' . "\n";
        $xml .= '    <cbc:PayableAmount currencyID="' . ($c['moneda'] ?? 'PEN') . '">' . number_format($c['total'], 2, '.', '') . '</cbc:PayableAmount>' . "\n";
        $xml .= '  </cac:RequestedMonetaryTotal>' . "\n";

        foreach ($detalles as $i => $det) {
            $xml .= $this->buildDebitNoteLine($i + 1, $det, $c['moneda'] ?? 'PEN');
        }
        $xml .= '</DebitNote>';
        return $xml;
    }

    private function buildSignature(array $cfg): string {
        $xml = '  <cac:Signature>' . "\n";
        $xml .= '    <cbc:ID>SignatureSP</cbc:ID>' . "\n";
        $xml .= '    <cac:SignatoryParty><cac:PartyIdentification>' . "\n";
        $xml .= '      <cbc:ID>' . $cfg['ruc'] . '</cbc:ID>' . "\n";
        $xml .= '    </cac:PartyIdentification><cac:PartyName>' . "\n";
        $xml .= '      <cbc:Name><![CDATA[' . $cfg['razon_social'] . ']]></cbc:Name>' . "\n";
        $xml .= '    </cac:PartyName></cac:SignatoryParty>' . "\n";
        $xml .= '    <cac:DigitalSignatureAttachment><cac:ExternalReference>' . "\n";
        $xml .= '      <cbc:URI>#SignatureSP</cbc:URI>' . "\n";
        $xml .= '    </cac:ExternalReference></cac:DigitalSignatureAttachment>' . "\n";
        $xml .= '  </cac:Signature>' . "\n";
        return $xml;
    }

    private function buildSupplier(array $cfg): string {
        $xml = '  <cac:AccountingSupplierParty><cac:Party>' . "\n";
        $xml .= '    <cac:PartyIdentification><cbc:ID schemeID="6">' . $cfg['ruc'] . '</cbc:ID></cac:PartyIdentification>' . "\n";
        $xml .= '    <cac:PartyLegalEntity>' . "\n";
        $xml .= '      <cbc:RegistrationName><![CDATA[' . $cfg['razon_social'] . ']]></cbc:RegistrationName>' . "\n";
        $xml .= '      <cac:RegistrationAddress>' . "\n";
        $xml .= '        <cbc:ID>' . $cfg['ubigeo'] . '</cbc:ID>' . "\n";
        $xml .= '        <cbc:AddressTypeCode>' . ($cfg['codigo_local_anexo'] ?? '0000') . '</cbc:AddressTypeCode>' . "\n";
        $xml .= '        <cac:AddressLine><cbc:Line><![CDATA[' . $cfg['direccion'] . ']]></cbc:Line></cac:AddressLine>' . "\n";
        $xml .= '        <cac:Country><cbc:IdentificationCode>PE</cbc:IdentificationCode></cac:Country>' . "\n";
        $xml .= '      </cac:RegistrationAddress>' . "\n";
        $xml .= '    </cac:PartyLegalEntity>' . "\n";
        $xml .= '  </cac:Party></cac:AccountingSupplierParty>' . "\n";
        return $xml;
    }

    private function buildCustomer(array $cliente): string {
        $tipoDoc = $cliente['tipo_doc'] ?? '1';
        $numDoc  = $cliente['numero_doc'] ?? '00000000';
        $rzn     = !empty($cliente['razon_social']) ? $cliente['razon_social'] : 'CLIENTES VARIOS';

        $xml = '  <cac:AccountingCustomerParty><cac:Party>' . "\n";
        $xml .= '    <cac:PartyIdentification><cbc:ID schemeID="' . $tipoDoc . '">' . htmlspecialchars($numDoc) . '</cbc:ID></cac:PartyIdentification>' . "\n";
        $xml .= '    <cac:PartyLegalEntity><cbc:RegistrationName><![CDATA[' . $rzn . ']]></cbc:RegistrationName></cac:PartyLegalEntity>' . "\n";
        $xml .= '  </cac:Party></cac:AccountingCustomerParty>' . "\n";
        return $xml;
    }

    private function buildPaymentTerms(array $c): string {
        $cond = strtoupper($c['condicion_pago'] ?? 'CONTADO');
        $mon = $c['moneda'] ?? 'PEN';
        
        $xml = '  <cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID>' . "\n";
        $xml .= '    <cbc:PaymentMeansID>' . ($cond === 'CREDITO' ? 'Credito' : 'Contado') . '</cbc:PaymentMeansID>' . "\n";
        if ($cond === 'CREDITO') {
            $montoCredito = number_format((float)$c['total'], 2, '.', '');
            $xml .= '    <cbc:Amount currencyID="' . $mon . '">' . $montoCredito . '</cbc:Amount>' . "\n";
            $xml .= '  </cac:PaymentTerms>' . "\n";

            // Si hay cuotas definidas o fecha de vencimiento
            $cuotas = $c['cuotas'] ?? [];
            if (!empty($cuotas) && is_array($cuotas)) {
                foreach ($cuotas as $i => $cuota) {
                    $nro = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
                    $montoCuota = number_format((float)($cuota['monto'] ?? $c['total']), 2, '.', '');
                    $vencCuota = $cuota['fecha_vencimiento'] ?? ($c['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days')));
                    
                    $xml .= '  <cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID>' . "\n";
                    $xml .= '    <cbc:PaymentMeansID>Cuota' . $nro . '</cbc:PaymentMeansID>' . "\n";
                    $xml .= '    <cbc:Amount currencyID="' . $mon . '">' . $montoCuota . '</cbc:Amount>' . "\n";
                    $xml .= '    <cbc:PaymentDueDate>' . $vencCuota . '</cbc:PaymentDueDate>' . "\n";
                    $xml .= '  </cac:PaymentTerms>' . "\n";
                }
            } else {
                // 1 cuota única con fecha de vencimiento
                $venc = !empty($c['fecha_vencimiento']) && $c['fecha_vencimiento'] !== '-' 
                    ? $c['fecha_vencimiento'] 
                    : date('Y-m-d', strtotime('+30 days'));
                $xml .= '  <cac:PaymentTerms><cbc:ID>FormaPago</cbc:ID>' . "\n";
                $xml .= '    <cbc:PaymentMeansID>Cuota001</cbc:PaymentMeansID>' . "\n";
                $xml .= '    <cbc:Amount currencyID="' . $mon . '">' . $montoCredito . '</cbc:Amount>' . "\n";
                $xml .= '    <cbc:PaymentDueDate>' . $venc . '</cbc:PaymentDueDate>' . "\n";
                $xml .= '  </cac:PaymentTerms>' . "\n";
            }
            return $xml;
        }

        $xml .= '  </cac:PaymentTerms>' . "\n";
        return $xml;
    }

    private function buildTaxTotals(array $c): string {
        $mon = $c['moneda'] ?? 'PEN';
        $xml = '  <cac:TaxTotal>' . "\n";
        $xml .= '    <cbc:TaxAmount currencyID="' . $mon . '">' . number_format($c['igv_total'] ?? 0, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        
        if (($c['subtotal_gravado'] ?? 0) > 0) {
            $xml .= $this->buildTaxSubtotal($c['igv_total'] ?? 0, $c['subtotal_gravado'], '1000', 'VAT', 'IGV', $mon, $this->config['igv_porcentaje'] ?? 18, 'S');
        }
        if (($c['subtotal_exonerado'] ?? 0) > 0) {
            $xml .= $this->buildTaxSubtotal(0, $c['subtotal_exonerado'], '9997', 'VAT', 'EXO', $mon, 0, 'E');
        }
        if (($c['subtotal_inafecto'] ?? 0) > 0) {
            $xml .= $this->buildTaxSubtotal(0, $c['subtotal_inafecto'], '9998', 'FRE', 'INA', $mon, 0, 'O');
        }
        if (($c['subtotal_gratuito'] ?? 0) > 0) {
            $xml .= $this->buildTaxSubtotal(0, $c['subtotal_gratuito'], '9996', 'FRE', 'GRA', $mon, 0, 'Z');
        }

        // Si no hay subtotales calculados (caso de comprobante vacío o cero), enviar al menos EXO o GRA
        if (($c['subtotal_gravado'] ?? 0) == 0 && ($c['subtotal_exonerado'] ?? 0) == 0 && ($c['subtotal_inafecto'] ?? 0) == 0 && ($c['subtotal_gratuito'] ?? 0) == 0) {
            $xml .= $this->buildTaxSubtotal(0, 0, '9997', 'VAT', 'EXO', $mon, 0, 'E');
        }

        $xml .= '  </cac:TaxTotal>' . "\n";
        return $xml;
    }

    private function buildTaxSubtotal(float $taxAmount, float $taxableAmount, string $taxId, string $taxScheme, string $taxName, string $moneda, float $percent, string $category): string {
        $xml = '    <cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $moneda . '">' . number_format($taxableAmount, 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
        $xml .= '      <cbc:TaxAmount currencyID="' . $moneda . '">' . number_format($taxAmount, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        $xml .= '      <cac:TaxCategory><cbc:ID>' . $category . '</cbc:ID>' . "\n";
        $xml .= '        <cbc:Percent>' . (int)$percent . '</cbc:Percent>' . "\n";
        $xml .= '        <cac:TaxScheme><cbc:ID>' . $taxId . '</cbc:ID><cbc:Name>' . $taxName . '</cbc:Name><cbc:TaxTypeCode>' . $taxScheme . '</cbc:TaxTypeCode></cac:TaxScheme>' . "\n";
        $xml .= '      </cac:TaxCategory></cac:TaxSubtotal>' . "\n";
        return $xml;
    }

    private function isGratuito(string $igvTipo): bool {
        return in_array($igvTipo, ['11','12','13','14','15','16','21','31','32','33','34','35','36','37']);
    }

    private function buildInvoiceLine(int $num, array $det, string $mon): string {
        $igvTipo = $det['igv_tipo'] ?? '20';
        $tax     = $this->getTaxInfo($igvTipo);
        $igvCod  = $tax['id'];
        $isGrat  = $this->isGratuito($igvTipo);

        $cantidad = (float)$det['cantidad'];
        $valorVenta = $isGrat ? 0.00 : (float)$det['valor_venta'];
        $igvMonto = (float)($det['igv_monto'] ?? 0);
        $precioUnit = $isGrat ? 0.00 : (float)$det['precio_unitario'];
        $precioRef = (float)($det['precio_con_igv'] ?? $det['precio_unitario'] ?? 0);

        $xml = '  <cac:InvoiceLine><cbc:ID>' . $num . '</cbc:ID>' . "\n";
        $xml .= '    <cbc:InvoicedQuantity unitCode="' . ($det['unidad_medida'] ?? 'NIU') . '">' . number_format($cantidad, 3, '.', '') . '</cbc:InvoicedQuantity>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . $mon . '">' . number_format($valorVenta, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";
        
        $xml .= '    <cac:PricingReference><cac:AlternativeConditionPrice>' . "\n";
        $xml .= '      <cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioRef, 8, '.', '') . '</cbc:PriceAmount>' . "\n";
        $xml .= '      <cbc:PriceTypeCode>' . ($isGrat ? '02' : '01') . '</cbc:PriceTypeCode>' . "\n";
        $xml .= '    </cac:AlternativeConditionPrice></cac:PricingReference>' . "\n";

        $xml .= '    <cac:TaxTotal><cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        $xml .= '      <cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $mon . '">' . number_format($isGrat ? ($det['valor_venta'] ?? $precioRef * $cantidad) : $valorVenta, 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
        $xml .= '        <cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        
        $percent   = ($igvTipo === '10' || in_array($igvTipo, ['11','12','13','14','15','16'])) ? (int)($this->config['igv_porcentaje'] ?? 18) : 0;
        $xml .= '        <cac:TaxCategory><cbc:ID>' . $tax['category'] . '</cbc:ID><cbc:Percent>' . $percent . '</cbc:Percent>' . "\n";
        $xml .= '          <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $igvTipo . '</cbc:TaxExemptionReasonCode>' . "\n";
        $xml .= '          <cac:TaxScheme><cbc:ID>' . $igvCod . '</cbc:ID><cbc:Name>' . $tax['name'] . '</cbc:Name><cbc:TaxTypeCode>' . $tax['type'] . '</cbc:TaxTypeCode></cac:TaxScheme>' . "\n";
        $xml .= '        </cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>' . "\n";
        $xml .= '    <cac:Item><cbc:Description><![CDATA[' . htmlspecialchars($det['descripcion'] ?? '', ENT_QUOTES) . ']]></cbc:Description></cac:Item>' . "\n";
        $xml .= '    <cac:Price><cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioUnit, 4, '.', '') . '</cbc:PriceAmount></cac:Price>' . "\n";
        $xml .= '  </cac:InvoiceLine>' . "\n";
        return $xml;
    }

    private function buildCreditNoteLine(int $num, array $det, string $mon): string {
        $igvTipo = $det['igv_tipo'] ?? '20';
        $tax     = $this->getTaxInfo($igvTipo);
        $igvCod  = $tax['id'];
        $isGrat  = $this->isGratuito($igvTipo);

        $cantidad = (float)$det['cantidad'];
        $valorVenta = $isGrat ? 0.00 : (float)$det['valor_venta'];
        $igvMonto = (float)($det['igv_monto'] ?? 0);
        $precioUnit = $isGrat ? 0.00 : (float)$det['precio_unitario'];
        $precioRef = (float)($det['precio_con_igv'] ?? $det['precio_unitario'] ?? 0);

        $xml = '  <cac:CreditNoteLine><cbc:ID>' . $num . '</cbc:ID>' . "\n";
        $xml .= '    <cbc:CreditedQuantity unitCode="' . ($det['unidad_medida'] ?? 'NIU') . '">' . number_format($cantidad, 3, '.', '') . '</cbc:CreditedQuantity>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . $mon . '">' . number_format($valorVenta, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";

        $xml .= '    <cac:PricingReference><cac:AlternativeConditionPrice>' . "\n";
        $xml .= '      <cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioRef, 8, '.', '') . '</cbc:PriceAmount>' . "\n";
        $xml .= '      <cbc:PriceTypeCode>' . ($isGrat ? '02' : '01') . '</cbc:PriceTypeCode>' . "\n";
        $xml .= '    </cac:AlternativeConditionPrice></cac:PricingReference>' . "\n";

        $xml .= '    <cac:TaxTotal><cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        $xml .= '      <cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $mon . '">' . number_format($isGrat ? ($det['valor_venta'] ?? $precioRef * $cantidad) : $valorVenta, 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
        $xml .= '        <cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        
        $percent   = ($igvTipo === '10' || in_array($igvTipo, ['11','12','13','14','15','16'])) ? (int)($this->config['igv_porcentaje'] ?? 18) : 0;
        $xml .= '        <cac:TaxCategory><cbc:ID>' . $tax['category'] . '</cbc:ID><cbc:Percent>' . $percent . '</cbc:Percent>' . "\n";
        $xml .= '          <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $igvTipo . '</cbc:TaxExemptionReasonCode>' . "\n";
        $xml .= '          <cac:TaxScheme><cbc:ID>' . $igvCod . '</cbc:ID><cbc:Name>' . $tax['name'] . '</cbc:Name><cbc:TaxTypeCode>' . $tax['type'] . '</cbc:TaxTypeCode></cac:TaxScheme>' . "\n";
        $xml .= '        </cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>' . "\n";
        $xml .= '    <cac:Item><cbc:Description><![CDATA[' . htmlspecialchars($det['descripcion'] ?? '', ENT_QUOTES) . ']]></cbc:Description></cac:Item>' . "\n";
        $xml .= '    <cac:Price><cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioUnit, 4, '.', '') . '</cbc:PriceAmount></cac:Price>' . "\n";
        $xml .= '  </cac:CreditNoteLine>' . "\n";
        return $xml;
    }

    private function buildDebitNoteLine(int $num, array $det, string $mon): string {
        $igvTipo = $det['igv_tipo'] ?? '20';
        $tax     = $this->getTaxInfo($igvTipo);
        $igvCod  = $tax['id'];
        $isGrat  = $this->isGratuito($igvTipo);

        $cantidad = (float)$det['cantidad'];
        $valorVenta = $isGrat ? 0.00 : (float)$det['valor_venta'];
        $igvMonto = (float)($det['igv_monto'] ?? 0);
        $precioUnit = $isGrat ? 0.00 : (float)$det['precio_unitario'];
        $precioRef = (float)($det['precio_con_igv'] ?? $det['precio_unitario'] ?? 0);

        $xml = '  <cac:DebitNoteLine><cbc:ID>' . $num . '</cbc:ID>' . "\n";
        $xml .= '    <cbc:DebitedQuantity unitCode="' . ($det['unidad_medida'] ?? 'NIU') . '">' . number_format($cantidad, 3, '.', '') . '</cbc:DebitedQuantity>' . "\n";
        $xml .= '    <cbc:LineExtensionAmount currencyID="' . $mon . '">' . number_format($valorVenta, 2, '.', '') . '</cbc:LineExtensionAmount>' . "\n";

        $xml .= '    <cac:PricingReference><cac:AlternativeConditionPrice>' . "\n";
        $xml .= '      <cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioRef, 8, '.', '') . '</cbc:PriceAmount>' . "\n";
        $xml .= '      <cbc:PriceTypeCode>' . ($isGrat ? '02' : '01') . '</cbc:PriceTypeCode>' . "\n";
        $xml .= '    </cac:AlternativeConditionPrice></cac:PricingReference>' . "\n";

        $xml .= '    <cac:TaxTotal><cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        $xml .= '      <cac:TaxSubtotal><cbc:TaxableAmount currencyID="' . $mon . '">' . number_format($isGrat ? ($det['valor_venta'] ?? $precioRef * $cantidad) : $valorVenta, 2, '.', '') . '</cbc:TaxableAmount>' . "\n";
        $xml .= '        <cbc:TaxAmount currencyID="' . $mon . '">' . number_format($igvMonto, 2, '.', '') . '</cbc:TaxAmount>' . "\n";
        
        $percent   = ($igvTipo === '10' || in_array($igvTipo, ['11','12','13','14','15','16'])) ? (int)($this->config['igv_porcentaje'] ?? 18) : 0;
        $xml .= '        <cac:TaxCategory><cbc:ID>' . $tax['category'] . '</cbc:ID><cbc:Percent>' . $percent . '</cbc:Percent>' . "\n";
        $xml .= '          <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $igvTipo . '</cbc:TaxExemptionReasonCode>' . "\n";
        $xml .= '          <cac:TaxScheme><cbc:ID>' . $igvCod . '</cbc:ID><cbc:Name>' . $tax['name'] . '</cbc:Name><cbc:TaxTypeCode>' . $tax['type'] . '</cbc:TaxTypeCode></cac:TaxScheme>' . "\n";
        $xml .= '        </cac:TaxCategory></cac:TaxSubtotal></cac:TaxTotal>' . "\n";
        $xml .= '    <cac:Item><cbc:Description><![CDATA[' . htmlspecialchars($det['descripcion'] ?? '', ENT_QUOTES) . ']]></cbc:Description></cac:Item>' . "\n";
        $xml .= '    <cac:Price><cbc:PriceAmount currencyID="' . $mon . '">' . number_format($precioUnit, 4, '.', '') . '</cbc:PriceAmount></cac:Price>' . "\n";
        $xml .= '  </cac:DebitNoteLine>' . "\n";
        return $xml;
    }

    public function getTaxInfo(string $tipo): array {
        $map = [
            '10' => ['id' => '1000', 'name' => 'IGV', 'type' => 'VAT', 'category' => 'S'],
            '20' => ['id' => '9997', 'name' => 'EXO', 'type' => 'VAT', 'category' => 'E'],
            '30' => ['id' => '9998', 'name' => 'INA', 'type' => 'FRE', 'category' => 'O'],
            '11' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '12' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '13' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '14' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '15' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '16' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '21' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '31' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '32' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '33' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '34' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '35' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '36' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
            '37' => ['id' => '9996', 'name' => 'GRA', 'type' => 'FRE', 'category' => 'Z'],
        ];
        return $map[$tipo] ?? ['id' => '1000', 'name' => 'IGV', 'type' => 'VAT', 'category' => 'S'];
    }

    public function numeroALetras(float $monto, string $moneda = 'PEN'): string {
        $monedas = ['PEN' => 'SOLES', 'USD' => 'DÓLARES AMERICANOS', 'EUR' => 'EUROS'];
        $nombreMoneda = $monedas[$moneda] ?? 'SOLES';
        $entero = (int)floor($monto);
        $decimal = round(($monto - $entero) * 100);
        return 'SON: ' . $this->convertirEntero($entero) . ' Y ' . str_pad($decimal, 2, '0', STR_PAD_LEFT) . '/100 ' . $nombreMoneda;
    }

    private function convertirEntero(int $num): string {
        if ($num === 0) return 'CERO';
        $unidades  = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
                      'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
                      'DIECIOCHO', 'DIECINUEVE', 'VEINTE'];
        $decenas   = ['', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas  = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
                      'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        if ($num <= 20) return $unidades[$num];
        if ($num < 100) {
            $d = intdiv($num, 10); $u = $num % 10;
            return $d < 3 ? $decenas[$d] . ($u > 0 ? $unidades[$u] : '') : $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : '');
        }
        if ($num == 100) return 'CIEN';
        if ($num < 1000) {
            $c = intdiv($num, 100); $r = $num % 100;
            return $centenas[$c] . ($r > 0 ? ' ' . $this->convertirEntero($r) : '');
        }
        if ($num < 1000000) {
            $miles = intdiv($num, 1000); $r = $num % 1000;
            $prefijo = $miles === 1 ? 'MIL' : $this->convertirEntero($miles) . ' MIL';
            return $prefijo . ($r > 0 ? ' ' . $this->convertirEntero($r) : '');
        }
        $millones = intdiv($num, 1000000); $r = $num % 1000000;
        $prefijo = $millones === 1 ? 'UN MILLÓN' : $this->convertirEntero($millones) . ' MILLONES';
        return $prefijo . ($r > 0 ? ' ' . $this->convertirEntero($r) : '');
    }
}
