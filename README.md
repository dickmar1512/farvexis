# Farvexis POS & Inventory System

## Descripción General
Farvexis es un sistema integral de Punto de Venta (POS), facturación electrónica, control de inventario y gestión multi-sucursal. Está diseñado para ofrecer trazabilidad, precisión en la caja y cumplimiento normativo (SUNAT), con un sólido registro de auditoría.

---

## 🚀 Funcionalidades Principales

### 1. Gestión Multi-Sucursal
* **Administración de Sucursales:** Creación y control de múltiples locales o puntos de venta.
* **Correlativos Dinámicos y Centralizados:** El sistema de correlativos (Series) es inteligente. Calcula automáticamente el prefijo y sufijo de cada documento según la sucursal emisora (ej. `F001` para la matriz, `F002` para la sucursal 1) aplicable a Facturas, Boletas, Notas de Venta, Ingresos, Salidas y Órdenes de Traslado.
* **Traspasos de Almacén:** Envío de mercancía entre sucursales mediante el documento "Orden de Traslado" (Código 75), con descuento y recepción automatizados de stock.

### 2. Facturación y Ventas
* **Punto de Venta (POS):** Interfaz ágil para la selección de productos y cobro.
* **Facturación Electrónica:** Emisión de Facturas (01), Boletas (03) y envío directo a SUNAT (incluyendo XML y representación impresa).
* **Notas de Venta (70):** Comprobantes internos para el registro de ventas simplificadas.
* **Notas de Crédito y Débito:** Aplicables tanto para Facturas como para Boletas, gestionando anulaciones y devoluciones conectadas directamente con el inventario y correlativos.
* **Control de Stock Estricto:** Previene la venta de productos sin stock y gestiona precios de "Mayoreo" automáticamente cuando aplica.

### 3. Control de Inventarios y Almacén
* **Catálogo Completo:** Gestión de Productos, Servicios, Categorías, Unidades de Medida y Paquetes/Kits.
* **Sistema de Lotes y Vencimientos:** Todo ingreso exige el registro de Número de Lote, Fecha de Fabricación y Fecha de Vencimiento. El sistema consume el stock del lote correcto al realizar ventas o traslados.
* **Kardex (Bincard):** Historial detallado de entradas y salidas por producto.
* **Ingresos y Salidas Diversas:** Movimientos de almacén justificados (Mermas, ajustes, ingresos extraordinarios) usando correlativos controlados (60 y 65).

### 4. Caja y Finanzas
* **Apertura y Cierre de Caja:** Control estricto de los turnos de venta con base de inicio.
* **Ingreso y Registro de Gastos:** La pantalla de `expenseentry` permite asentar gastos diarios. Estos egresos se restan automáticamente de la caja activa, reflejándose en el arqueo y cierre diario.
* **Ventas a Crédito y Pagos Parciales:** Gestión de deudas de clientes y control de lo pagado en el día.

### 5. Configuración y Auditoría Global
* **Triggers de Auditoría (Trazabilidad 360°):** Todas las tablas del sistema están configuradas a nivel de Base de Datos para capturar automáticamente:
  * `created_at` (Fecha y hora de creación)
  * `created_by` (ID del usuario que creó el registro)
  * `updated_at` (Fecha y hora de la última modificación)
  * `updated_by` (ID del usuario que modificó el registro)
* **Gestión de Usuarios y Permisos:** Control de acceso detallado a módulos específicos según el rol del empleado.
* **Empresa / Emisor:** Configuración del logo, RUC y credenciales de facturación.

### 6. Reportes e Inteligencia de Negocio
* Reporte de Ventas (General, mensual, detallado por producto).
* Reporte de Compras y sugerencias de reabastecimiento.
* Reporte de Fechas de Vencimiento y Lotes Próximos a caducar.
* Reportes de comprobantes enviados y pendientes SUNAT.

---
*Documento generado tras la auditoría técnica de código y base de datos.*