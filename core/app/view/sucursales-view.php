<?php $sucursales = SucursalData::getAll(); ?>
<div class="container-fluid mb-2 text-right"><a href="./?view=correlativos" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list-ol mr-1"></i>Administrar series y correlativos</a></div>
<div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-7"><h1 class="m-0"><i class="fas fa-code-branch mr-2"></i>Sucursales</h1></div><div class="col-sm-5"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?view=users">Usuarios</a></li><li class="breadcrumb-item active">Sucursales</li></ol></div></div></div></div>
<section class="content"><div class="container-fluid"><div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title">Administrar sucursales y locales SUNAT</h3><button class="btn btn-primary btn-sm float-right" id="nuevaSucursal"><i class="fas fa-plus mr-1"></i>Nueva sucursal</button></div><div class="card-body"><table class="table table-hover table-sm"><thead><tr><th>Código local</th><th>Nombre</th><th>Dirección</th><th>Estado</th><th></th></tr></thead><tbody><?php foreach ($sucursales as $sucursal): ?><tr><td><?= htmlspecialchars($sucursal->codigo) ?></td><td><?= htmlspecialchars($sucursal->nombre) ?></td><td><?= htmlspecialchars($sucursal->direccion ?? '') ?></td><td><?= (int)$sucursal->activo ? '<span class="badge badge-success">Activa</span>' : '<span class="badge badge-secondary">Inactiva</span>' ?></td><td><button class="btn btn-warning btn-xs editarSucursal" data-sucursal='<?= htmlspecialchars(json_encode($sucursal), ENT_QUOTES, 'UTF-8') ?>'><i class="fas fa-edit"></i></button></td></tr><?php endforeach; ?></tbody></table></div></div></div></section>
<script>
$(function () {
    function abrirSucursal(sucursal) {
        sucursal = sucursal || {id: 0, codigo: '', nombre: '', direccion: '', activo: 1};
        Swal.fire({
            title: sucursal.id ? 'Editar sucursal' : 'Nueva sucursal',
            html: '<input id="sucursalCodigo" class="swal2-input" maxlength="4" placeholder="Código local SUNAT" value="' + (sucursal.codigo || '') + '">' +
                  '<input id="sucursalNombre" class="swal2-input" placeholder="Nombre" value="' + (sucursal.nombre || '') + '">' +
                  '<input id="sucursalDireccion" class="swal2-input" placeholder="Dirección" value="' + (sucursal.direccion || '') + '">' +
                  '<label><input id="sucursalActiva" type="checkbox" ' + (Number(sucursal.activo) ? 'checked' : '') + '> Activa</label>',
            showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
            preConfirm: function () { return {id: sucursal.id, codigo: $('#sucursalCodigo').val(), nombre: $('#sucursalNombre').val(), direccion: $('#sucursalDireccion').val(), activo: $('#sucursalActiva').is(':checked') ? 1 : 0}; }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({url: './?action=savesucursal', method: 'POST', contentType: 'application/json', dataType: 'json', data: JSON.stringify(result.value)}).done(function (response) { if (response.success) location.reload(); }).fail(function (xhr) { Swal.fire('Error', xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'No se pudo guardar.', 'error'); });
        });
    }
    $('#nuevaSucursal').on('click', function () { abrirSucursal(); });
    $('.editarSucursal').on('click', function () { abrirSucursal($(this).data('sucursal')); });
});
</script>