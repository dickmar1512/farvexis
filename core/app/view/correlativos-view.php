<?php
$sucursales = SucursalData::getAll(true);
$correlativos = ComprobanteCorrelativoData::configurados();
$tiposDb = TipoComprobanteData::getAll();
$tiposComprobante = [];
foreach ($tiposDb as $t) {
    $tiposComprobante[$t->codigo] = $t->nombre;
}
?>
<div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-7"><h1 class="m-0"><i class="fas fa-list-ol mr-2"></i>Series y correlativos</h1></div><div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?view=sucursales">Sucursales</a></li><li class="breadcrumb-item active">Correlativos</li></ol></div></div></div></div>
<section class="content"><div class="container-fluid"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title">Numeración por sucursal</h3><button class="btn btn-primary btn-sm float-right" id="nuevoCorrelativo"><i class="fas fa-plus mr-1"></i>Agregar serie</button></div><div class="card-body"><div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>El valor indicado es el último número usado. El siguiente comprobante se reservará automáticamente con el número siguiente.</div><table class="table table-hover table-sm"><thead><tr><th>Sucursal</th><th>Tipo</th><th>Serie</th><th>Último usado</th><th>Próximo</th><th></th></tr></thead><tbody><?php foreach ($correlativos as $item): ?><tr><td><?= htmlspecialchars($item->sucursal_nombre . ' (' . $item->sucursal_codigo . ')') ?></td><td><?= htmlspecialchars($tiposComprobante[$item->tipo_comprobante] ?? $item->tipo_comprobante) ?></td><td class="font-weight-bold"><?= htmlspecialchars($item->serie) ?></td><td><?= (int)$item->ultimo_numero ?></td><td><?= str_pad((int)$item->ultimo_numero + 1, 8, '0', STR_PAD_LEFT) ?></td><td><button class="btn btn-warning btn-xs editarCorrelativo" data-item='<?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-edit"></i></button></td></tr><?php endforeach; ?></tbody></table></div></div></div></section>
<script>
$(function () {
    const sucursales = <?= json_encode($sucursales, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    const tipos = <?= json_encode($tiposComprobante, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    function abrir(item) {
        item = item || {id: 0, sucursal_id: sucursales[0] ? sucursales[0].id : 1, tipo_comprobante: '01', serie: 'F001', ultimo_numero: 0};
        const sucursalOptions = sucursales.map(s => '<option value="' + s.id + '" ' + (String(s.id) === String(item.sucursal_id) ? 'selected' : '') + '>' + s.codigo + ' - ' + s.nombre + '</option>').join('');
        const tipoOptions = Object.keys(tipos).map(k => '<option value="' + k + '" ' + (k === String(item.tipo_comprobante) ? 'selected' : '') + '>' + tipos[k] + '</option>').join('');
        Swal.fire({title: item.id ? 'Editar correlativo' : 'Agregar serie', html: '<select id="corrSucursal" class="swal2-select">' + sucursalOptions + '</select><select id="corrTipo" class="swal2-select">' + tipoOptions + '</select><input id="corrSerie" class="swal2-input" maxlength="10" placeholder="Serie, por ejemplo F001" value="' + (item.serie || '') + '"><input id="corrUltimo" type="number" min="0" class="swal2-input" placeholder="Último número usado" value="' + (item.ultimo_numero || 0) + '">', showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar', preConfirm: () => ({id: item.id || 0, sucursal_id: $('#corrSucursal').val(), tipo_comprobante: $('#corrTipo').val(), serie: $('#corrSerie').val(), ultimo_numero: $('#corrUltimo').val()})}).then(r => { if (!r.isConfirmed) return; $.ajax({url: './?action=savecorrelativo', method: 'POST', contentType: 'application/json', dataType: 'json', data: JSON.stringify(r.value)}).done(() => location.reload()).fail(xhr => Swal.fire('Error', xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo guardar.', 'error')); });
    }
    $('#nuevoCorrelativo').on('click', () => abrir());
    $('.editarCorrelativo').on('click', function () { abrir($(this).data('item')); });
});
</script>