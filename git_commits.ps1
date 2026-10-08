git add core/app/action/*traspaso* core/app/model/Traspaso* core/app/view/*traspaso*
git commit -m "feat: Implementar modulo de traspasos de sucursales"

git add core/app/action/savecorrelativo-action.php core/app/model/ComprobanteCorrelativoData.php core/app/view/correlativos-view.php core/app/model/TipoComprobanteData.php
git commit -m "feat: Modulo de gestion de correlativos por sucursal"

git add core/app/model/LoteData.php core/app/view/lotes-view.php
git commit -m "feat: Gestion de lotes y fechas de vencimiento"

git add core/app/action/saveemail-action.php core/app/action/delemail-action.php core/app/model/EmailConfigData.php core/app/view/emails-view.php core/app/model/CLSPHPMailer.php core/app/action/processbox-action.php core/app/action/processlogin-action.php scripts/cron_reporte_*.php
git commit -m "feat/refactor: Modulo de configuracion de correos y remocion de emails hardcodeados"

git add core/app/model/OperationData.php core/app/model/ModuleData.php core/app/model/UserAccessData.php core/app/model/GastoData.php core/app/model/ProductData.php core/app/model/SellData.php core/app/model/UserData.php core/app/model/PersonData.php
git commit -m "fix: Resolver advertencias de propiedades dinamicas obsoletas (Deprecated) en modelos"

git add core/app/action/addboleta-action.php core/app/action/addfactura-action.php core/app/action/addsalidadiversa-action.php core/app/view/newsalidadiversa-view.php core/app/action/processre-action.php core/app/view/re-view.php core/app/view/box-view.php core/app/view/boxhistory-view.php core/app/action/cart_table-action.php core/app/action/re_cart_table-action.php core/app/view/sell-view.php core/app/view/onesell-view.php core/app/action/processsell-action.php core/app/action/searchproduct_re-action.php
git commit -m "feat: Mejoras en procesos de ventas, compras, caja y salidas diversas"

git add core/app/action/addproductxls-action.php core/app/action/downloadtemplate-action.php core/app/view/importarexcel-view.php core/app/view/products-view.php assets/templates/plantilla_productos.xlsx
git commit -m "feat: Importacion de productos via Excel y plantillas"

git add core/controller/Database.php assets/js/pages/ core/app/action/addpersonajax-action.php core/app/action/addtore_ajax-action.php
git commit -m "fix/refactor: Ajustes menores en JS, conexion DB y acciones AJAX"

git add storage/ scripts/
git commit -m "chore: Actualizacion de scripts de migracion, temporales de SUNAT y logs"

git add README.md database_erd.md walkthrough.md
git commit -m "docs: Actualizacion de documentacion del proyecto"

git add -A
git commit -m "chore: Otros ajustes y limpieza final"
