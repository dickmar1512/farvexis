# Independencia de SUNAT Facturación Electrónica y Mejoras de Impuestos

Se ha completado la transición del sistema para que **ContivaPharmacy sea 100% autónomo e independiente** del Facturador externo SFS (`C:\laragon\www\efact1.3.4`). Ahora el sistema genera los archivos XML en formato **UBL 2.1**, los firma digitalmente mediante RSA-SHA256 con certificado `.pfx`, los empaqueta en `.zip` y los transmite directamente al Web Service SOAP de SUNAT (`billService`), procesando y archivando las Constancias de Recepción (**CDR**).

---

## 1. Cambios Principales Realizados

### A. Base de Datos y Modelos

- **`operation` (Detalle de la venta)**:
  - Se agregó la columna `igv_tipo VARCHAR(5) DEFAULT '20'` para almacenar el tipo de afectación al IGV por cada ítem vendido.
- **`empresa`**:
  - Se añadieron: `sunat_igv_tipo_defecto`, `sunat_usuario_sol`, `sunat_clave_sol`, `sunat_ambiente`, `sunat_cert_path`, `sunat_cert_pass`.
- **`sell`**:
  - Se añadieron: `estado_sunat` (`aceptado`, `rechazado`, `pendiente`), `cdr_codigo`, `cdr_descripcion`, `codigo_hash`, `fecha_envio_sunat`, `forma_pago` (1: Contado, 2: Crédito), y `fec_vencimiento`.
- **Nuevos Servicios SUNAT (`core/app/model/`)**:
  - [SunatConfig.php](file:///c:/laragon/www/ContivaPharmacy/core/app/model/SunatConfig.php): Configuración centralizada dinámica.
  - [SunatXmlService.php](file:///c:/laragon/www/ContivaPharmacy/core/app/model/SunatXmlService.php): Generador nativo de XML UBL 2.1 para Facturas (`01`), Boletas (`03`), Notas de Crédito (`07`) y Notas de Débito (`08`).
  - [SunatService.php](file:///c:/laragon/www/ContivaPharmacy/core/app/model/SunatService.php): Servicio de firma PKCS#12 RSA-SHA256, compresión ZIP, SOAP `sendBill` / `sendSummary` vía cURL y extracción de CDR.

### B. Múltiples Tipos de Impuestos (Afectación al IGV)

- **Gravado (10)**: Aplica IGV del 18%. La base imponible y el IGV se calculan matemáticamente a partir del precio unitario y se transmiten en `<cac:TaxTotal>` con categoría `S` y esquema `1000 VAT`.
- **Exonerado (20)**: Exonerado de IGV (categoría `E`, esquema `9997 VAT`).
- **Inafecto (30)**: Inafecto de IGV (categoría `O`, esquema `9998 FRE`).
- **Gratuito (11 / 21)**: Transferencia a título gratuito (categoría `Z`, esquema `9996 FRE`, con precio referencial en `PriceTypeCode = 02` y leyenda `1002`).
- **Configuración por defecto**: Se configura en `Administración > Configuración` y se aplica automáticamente al buscar y agregar productos, con opción de modificarlo en el carrito o en el buscador antes de añadirlo.

### C. Condiciones de Pago: Contado y Crédito

- En el formulario de ventas ([sell-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/sell-view.php)), se agregó el selector de **Forma de Pago** (`Contado` o `Crédito`).
- Al seleccionar **Crédito**, se despliega el campo de **Fecha de Vencimiento**.
- El XML UBL 2.1 genera los bloques `<cac:PaymentTerms>` tanto a nivel de cabecera como las cuotas con su fecha de vencimiento (`Cuota001`), tal como lo exige SUNAT.

### D. Flujo de Emisión y Desacople de SFS

- [addboleta-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/addboleta-view.php) y [addfactura-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/addfactura-view.php):
  - Eliminada la escritura de archivos planos en `../efact1.3.4/sunat_archivos/sfs/DATA/`.
  - Guardan `igv_tipo` en cada registro de `operation`.
  - Invocan directamente `(new SunatService())->enviarComprobante(...)` y actualizan `sell` con el estado, hash y CDR.
- [addnotacredito-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/addnotacredito-view.php) y [addnotacreditoboleta-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/addnotacreditoboleta-view.php):
  - Emisión de Notas de Crédito directa a SUNAT.
- [addnotadebito-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/addnotadebito-view.php):
  - Emisión de Notas de Débito directa a SUNAT.

### E. Vistas, Reportes e Impresión de Tickets

- [onesell-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/onesell-view.php):
  - Muestra desglose dinámico: Op. Gravada, IGV, Op. Exonerada, Op. Inafecta, Op. Gratuita y Total.
  - Muestra condición de pago (Contado o Crédito con fecha de vencimiento).
  - Muestra estado SUNAT (Aceptado, Rechazado o Pendiente) y Código Hash.
  - Botones para descargar XML firmado, CDR y reintentar envío a SUNAT.
- [imprimir80.js](file:///c:/laragon/www/ContivaPharmacy/assets/js/imprimir80.js):
  - El ticket de 80mm imprime los totales dinámicos exactos de impuestos y la condición de venta (Contado / Crédito con vencimiento).
- [getSells-action.php](file:///c:/laragon/www/ContivaPharmacy/core/app/action/getSells-action.php), [b-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/b-view.php), [box-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/box-view.php):
  - Priorizan los estados de la base de datos `sell.estado_sunat` y los archivos en `storage/`, eliminando la dependencia obligatoria de SQLite `BDFacturador.db`.
- [send_sunat_ajax-action.php](file:///c:/laragon/www/ContivaPharmacy/core/app/action/send_sunat_ajax-action.php):
  - Permite enviar o reintentar el envío a SUNAT con 1 solo clic desde los reportes o el detalle de la venta.
- [settings-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/settings-view.php) y [updateempresa-view.php](file:///c:/laragon/www/ContivaPharmacy/core/app/view/updateempresa-view.php):
  - Formulario de configuración con credenciales SOL, ambiente (Beta/Producción), carga de Certificado Digital `.pfx` y selección de afectación de IGV por defecto.

---

## 2. Verificación y Resultados de Pruebas

| Prueba                              | Componente                    | Resultado                                                                                                    |
| ----------------------------------- | ----------------------------- | ------------------------------------------------------------------------------------------------------------ |
| **Sintaxis PHP**              | 21 archivos modificados       | `No syntax errors detected` en todos los archivos                                                          |
| **Generación XML UBL 2.1**   | `SunatXmlService`           | Generó XML válido con etiquetas UBL 2.1,`<cac:PaymentTerms>`, `<cac:TaxTotal>` y `<cac:InvoiceLine>` |
| **Afectaciones simultáneas** | Gravado (10) + Exonerado (20) | Correctamente calculados y clasificados en la estructura XML                                                 |
| **Firma Digital PKCS#12**     | `SunatService` RSA-SHA256   | Hash generado y firma incrustada correctamente en`<ds:SignatureValue>`                                     |
| **Condición Crédito**       | Cuotas y PaymentMeansID       | Cuotas con monto y fecha de vencimiento validadas en el XML                                                  |
