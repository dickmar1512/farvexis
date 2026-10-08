# Diagramas Entidad-Relación por Módulo

Dado el gran tamaño de la base de datos, el diagrama ha sido dividido en módulos para facilitar su visualización y lectura sin requerir un zoom excesivo.

## Ventas y Comprobantes

```mermaid
erDiagram
    SELL {
        int id PK
        int person_id FK
        int user_id FK
        tinyint tipo_comprobante 
        varchar serie 
        varchar comprobante 
        int operation_type_id FK
        int box_id FK
        double total 
        double cash 
        double discount 
        date fecha_emi 
        datetime created_at 
        tinyint estado 
        tinyint tipo_pago 
        int idpaquete 
        varchar observacion 
        tinyint forma_pago 
        date fec_vencimiento 
        int medico_id 
        int paciente_id 
        varchar estado_sunat 
        varchar cdr_codigo 
        text cdr_descripcion 
        varchar codigo_hash 
        datetime fecha_envio_sunat 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    COMPROBANTE {
        int id PK
        varchar serie 
        varchar numero 
        tinyint tipo 
        int sell_id 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    BOLETA {
        int id PK
        varchar RUC 
        varchar TIPO 
        varchar SERIE 
        varchar COMPROBANTE 
        int ESTADO 
        int EXTRA1 
        int EXTRA2 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    FACTURA {
        int id PK
        varchar RUC 
        varchar TIPO 
        varchar SERIE 
        varchar COMPROBANTE 
        int ESTADO 
        int EXTRA1 
        int EXTRA2 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    NOTA {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        int ESTADO 
        int tipOperacion 
        date fecEmision 
        time horEmision 
        varchar codLocalEmisor 
        varchar tipDocUsuario 
        varchar numDocUsuario 
        varchar rznSocialUsuario 
        varchar tipMoneda 
        tinyint codTipoNota 
        varchar descMotivo 
        tinyint tipDocModifica 
        varchar serieDocModifica 
        decimal sumTotTributos 
        decimal sumTotValVenta 
        decimal sumPrecioVenta 
        decimal sumDescTotal 
        decimal sumOtrosCargos 
        decimal sumTotalAnticipos 
        decimal sumImpVenta 
        varchar ublVersionId 
        varchar customizationId 
        varchar estado_sunat 
        varchar cdr_codigo 
        text cdr_descripcion 
        varchar codigo_hash 
        datetime fecha_envio_sunat 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PAGO_PARCIAL {
        int id PK
        int sellid FK
        int tipoPago 
        decimal importe 
        int boxid 
        datetime fechareg 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    COMPROBANTE_CORRELATIVO {
        bigint_unsigned id PK
        int_unsigned sucursal_id FK
        char cod_local_emisor 
        char tipo_comprobante 
        varchar serie 
        bigint_unsigned ultimo_numero 
        datetime updated_at 
        datetime created_at 
        int created_by 
        int updated_by 
    }

    TIPO_COMPROBANTE {
        varchar codigo PK
        varchar nombre 
        int estado 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    GASTOS {
        int id PK
        varchar descripcion 
        varchar comprobante 
        decimal importe 
        datetime fecha 
        int usuario_id 
        datetime fechaupd 
        int useridupd 
        int box_id 
        int estado 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    INGRESOS_PAGOS {
        int id PK
        varchar codigo 
        varchar descripcion 
        float importe 
        char tipo 
        int id_usuario 
        datetime fecha_cre 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    SELL ||--o{ COMPROBANTE : "has"

```

## Inventario y Productos

```mermaid
erDiagram
    PRODUCT {
        int id PK
        int cod_digemid 
        varchar image 
        varchar barcode 
        varchar name 
        text principio_activo 
        text description 
        float stock 
        tinyint is_stock 
        int inventary_min 
        float price_in 
        float price_out 
        varchar unit 
        varchar presentation 
        varchar laboratorio 
        varchar reg_san 
        int user_id FK
        int category_id FK
        datetime created_at 
        varchar fecha_venc 
        tinyint is_active 
        float price_may 
        varchar anaquel 
        tinyint is_may 
        int_unsigned sucursal_id FK
        tinyint is_controlled 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    LOTE {
        int id FK
        int id_prod 
        varchar num_lot 
        datetime fech_ing 
        int id_sell 
        int user_id 
        date fecha_fabricacion 
        date fecha_vencimiento 
        int proveedor_id 
        decimal cantidad_inicial 
        decimal cantidad_disponible 
        decimal costo_unitario 
        varchar estado 
        varchar motivo_bloqueo 
        varchar ubicacion 
        datetime created_at 
        datetime updated_at 
        int created_by 
        int updated_by 
    }

    LOTE_MOVIMIENTO {
        int id PK
        int lote_id FK
        int product_id FK
        int operation_id FK
        varchar tipo 
        decimal cantidad 
        decimal costo_unitario 
        varchar referencia 
        varchar observacion 
        int user_id 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    CATEGORY {
        int id PK
        varchar image 
        varchar name 
        text description 
        datetime created_at 
        bit status 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    UNIDAD_MEDIDA {
        int id PK
        varchar name 
        varchar sigla 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PAQUETE {
        int idpaquete PK
        varchar imagen 
        varchar barcode 
        varchar nombre 
        varchar descripcion 
        float precio 
        datetime fecha_cre 
        datetime fecha_fin 
        datetime fecha_upd 
        char estado 
        varchar id_user 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    DETALLE_PAQ {
        int iddetalle PK
        int idpaquete FK
        int idprod 
        int cantidad 
        datetime fecha_ing 
        decimal precio 
        decimal descuento 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    OPERATION {
        int id PK
        int product_id FK
        float q 
        decimal cu 
        decimal prec_alt 
        decimal descuento 
        int operation_type_id FK
        int sell_id FK
        datetime created_at 
        varchar descripcion 
        varchar idpaquete 
        tinyint estado 
        varchar igv_tipo 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    OPERATION_TYPE {
        int id PK
        varchar name 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    OPERATION_LOTE {
        int id PK
        int operation_id FK
        int lote_id FK
        decimal cantidad 
        decimal costo_unitario 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PRICE_HISTORY {
        int id PK
        int product_id 
        decimal price_in 
        decimal price_out 
        int user_id 
        int sell_id 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    MERGED_PRODUCT_HISTORY {
        int id PK
        int primary_product_id 
        varchar primary_product_name 
        int duplicate_product_id 
        varchar duplicate_product_name 
        varchar duplicate_barcode 
        decimal merged_stock 
        int user_id 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    TRASPASO {
        int id PK
        int sucursal_origen_id 
        int sucursal_destino_id 
        int user_id 
        int user_recepcion_id 
        datetime fecha_envio 
        datetime fecha_recepcion 
        int estado 
        varchar serie 
        varchar comprobante 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    TRASPASO_DETALLE {
        int id PK
        int traspaso_id 
        int product_id_origen 
        int product_id_destino 
        int q 
        int operation_id_origen 
        int operation_id_destino 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    CATEGORY ||--o{ PRODUCT : "has"
    LOTE ||--o{ LOTE_MOVIMIENTO : "has"
    PRODUCT ||--o{ LOTE_MOVIMIENTO : "has"
    OPERATION ||--o{ LOTE_MOVIMIENTO : "has"
    PRODUCT ||--o{ OPERATION : "has"
    OPERATION_TYPE ||--o{ OPERATION : "has"
    OPERATION ||--o{ OPERATION_LOTE : "has"
    LOTE ||--o{ OPERATION_LOTE : "has"
    PRODUCT ||--o{ PRICE_HISTORY : "has"
    TRASPASO ||--o{ TRASPASO_DETALLE : "has"

```

## Personas y Usuarios

```mermaid
erDiagram
    PERSON {
        int id PK
        tinyint tipo_persona 
        varchar numero_documento 
        varchar ubigeo 
        varchar image 
        varchar name 
        varchar lastname 
        varchar company 
        varchar address1 
        varchar address2 
        varchar phone1 
        varchar phone2 
        varchar email1 
        varchar email2 
        int kind 
        datetime created_at 
        bit status 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PERSONA {
        int id PK
        int tipo_documento_id 
        varchar numero_documento FK
        varchar nombres 
        varchar apellido_paterno 
        varchar apellido_materno 
        date fecha_nacimiento 
        char sexo 
        varchar direccion 
        varchar telefono 
        varchar email 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    USER {
        int id PK
        int persona_id FK
        varchar username 
        varchar password 
        varchar image 
        tinyint is_active 
        tinyint is_admin 
        tinyint is_caja 
        tinyint is_dirtec 
        tinyint is_desc 
        int montomax 
        datetime created_at 
        int_unsigned sucursal_id 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    EMPLEADO {
        int id PK
        int persona_id FK
        varchar cargo 
        date fecha_contratacion 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    MEDICO {
        int id PK
        int persona_id FK
        varchar cmp 
        varchar especialidad 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    CLIENTE {
        int id PK
        int persona_id FK
        int tipo_cliente 
        varchar company 
        int kind 
        varchar ubigeo 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    EMPRESA {
        int Emp_IdEmpresa PK
        varchar Emp_Ruc 
        varchar Emp_RazonSocial 
        varchar Emp_Descripcion 
        varchar Emp_Direccion 
        varchar Emp_Telefono 
        varchar Emp_Celular 
        varchar Emp_Sucursal 
        varchar Emp_Logo 
        varchar Emp_personaId 
        varchar Emp_personaToken 
        varchar sunat_igv_tipo_defecto 
        varchar sunat_usuario_sol 
        varchar sunat_clave_sol 
        varchar sunat_ambiente 
        varchar sunat_cert_path 
        varchar sunat_cert_pass 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    SUCURSAL {
        int_unsigned id PK
        char codigo FK
        varchar nombre 
        varchar direccion 
        tinyint activo 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    USER_ACCESS {
        int id PK
        int user_id 
        int module_id 
        tinyint is_active 
        datetime created_at 
        datetime updated_at 
        int created_by 
        int updated_by 
    }

    MODULE {
        int id PK
        varchar name 
        varchar view_name 
        varchar icon 
        int parent_id 
        tinyint is_active 
        int sort_order 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PERMISO {
        int Per_IdPermiso PK
        varchar Per_Nombre 
        varchar Per_Key 
        tinyint Per_Estado 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PERMISO_EMPRESA {
        int Pee_IdPermisoEmpresa PK
        int Per_IdPermiso FK
        int Emp_IdEmpresa FK
        tinyint Pee_Valor 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    PERSONA ||--o{ CLIENTE : "references"
    PERSONA ||--o{ EMPLEADO : "references"
    PERSONA ||--o{ MEDICO : "references"
    EMPRESA ||--o{ PERMISO_EMPRESA : "references"
    PERMISO ||--o{ PERMISO_EMPRESA : "references"
    PERSONA ||--o{ USER : "references"

```

## Otros Módulos (SUNAT, Órdenes, etc)

```mermaid
erDiagram
    ACA {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        varchar ctaBancoNacionDetraccion 
        varchar codBienDetraccion 
        decimal porDetraccion 
        decimal mtoDetraccion 
        varchar codPaisCliente 
        varchar codUbigeoCliente 
        varchar desDireccionCliente 
        varchar codPaisEntrega 
        varchar codUbigeoEntrega 
        varchar desDireccionEntrega 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    ACTIVO {
        int id PK
        varchar nombre 
        varchar modelo 
        varchar serie 
        int tipo 
        date fecha_fabricacion 
        date fecha_compra 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    BOX {
        int id PK
        datetime created_at 
        int user_id 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    BOXDETALLE {
        int id PK
        int idbox FK
        int b200 
        int b100 
        int b50 
        int b20 
        int b10 
        int m5 
        int m2 
        int m1 
        int c50 
        int c20 
        int c10 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    CAB {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        varchar tipOperacion 
        date fecEmision 
        time horEmision 
        varchar fecVencimiento 
        varchar codLocalEmisor 
        varchar tipDocUsuario 
        varchar numDocUsuario 
        varchar rznSocialUsuario 
        varchar tipMoneda 
        decimal sumTotTributos 
        decimal sumTotValVenta 
        decimal sumPrecioVenta 
        decimal sumDescTotal 
        decimal sumOtrosCargos 
        decimal sumTotalAnticipos 
        decimal sumImpVenta 
        varchar ublVersionId 
        varchar customizationId 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    CONFIGURATION {
        int id PK
        varchar short FK
        varchar name FK
        int kind 
        varchar val 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    DET {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        varchar codUnidadMedida 
        varchar ctdUnidadItem 
        varchar codProducto 
        varchar codProductoSUNAT 
        varchar desItem 
        varchar mtoValorUnitario 
        varchar sumTotTributosItem 
        varchar codTriIGV 
        varchar mtoIgvItem 
        decimal mtoBaseIgvItem 
        varchar nomTributoIgvItem 
        varchar codTipTributoIgvItem 
        varchar tipAfeIGV 
        varchar porIgvItem 
        varchar codTriISC 
        varchar mtoIscItem 
        decimal mtoBaseIscItem 
        varchar nomTributoIscItem 
        varchar codTipTributoIscItem 
        varchar tipSisISC 
        varchar porIscItem 
        varchar codTriOtroItem 
        varchar mtoTriOtroItem 
        decimal mtoBaseTriOtroItem 
        varchar nomTributoIOtroItem 
        varchar codTipTributoIOtroItem 
        varchar porTriOtroItem 
        varchar codTriIcbper 
        varchar mtoTriIcbperItem 
        varchar ctdBolsasTriIcbperItem 
        varchar nomTributoIcbperItem 
        varchar codTipTributoIcbperItem 
        varchar mtoTriIcbperUnidad 
        decimal mtoPrecioVentaUnitario 
        decimal mtoValorVentaItem 
        decimal mtoValorReferencialUnitario 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    DETALLE_ORDEN {
        int id PK
        int product_id 
        float q 
        decimal prec_alt 
        int orden_id 
        timestamp created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    LEY {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        varchar codLeyenda 
        varchar desLeyenda 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    ORDEN_TRABAJO {
        int id PK
        int person_id 
        int user_id 
        tinyint tipo_servicio 
        int activo_id 
        varchar serie_comprobante 
        varchar descripcion 
        varchar diagnostico 
        decimal mano_obra 
        decimal total 
        decimal cash 
        date fecha_evaluacion 
        timestamp created_at 
        tinyint estado 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    TIPO_DOCUMENTO {
        int id PK
        varchar codigo_sunat 
        varchar nombre 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    TRI {
        int id PK
        int TIPO_DOC 
        int ID_TIPO_DOC 
        varchar ideTributo 
        varchar nomTributo 
        varchar codTipTributo 
        decimal mtoBaseImponible 
        decimal mtoTributo 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    UBIGEO {
        int idubigeo PK
        varchar codubigeo 
        varchar departamento 
        varchar provincia 
        varchar distrito 
        varchar capital 
        int codregnat 
        varchar regnatural 
        datetime created_at 
        int created_by 
        datetime updated_at 
        int updated_by 
    }

    ACTIVO ||--o{ ORDEN_TRABAJO : "has"

```

