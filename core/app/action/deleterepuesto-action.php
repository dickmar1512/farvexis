<?php

$id = $_GET["id"];
$orden_id = $_GET["orden_id"];

$detalleorden = DetalleOrdenData::getById($id);

$detalleorden->del();

print "<script>window.location='./?view=oneorden&id=$orden_id';</script>";

?>