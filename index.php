<?php
define("ROOT", dirname(__FILE__));

$debug = false;
if ($debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

include "core/autoload.php";

// ── Router: URLs amigables ───────────────────────────────────────────────────
include "core/router.php";
router_parse(); // Convierte /onesell/7573/3 en $_GET['id']=7573, $_GET['tipodoc']=3
// ────────────────────────────────────────────────────────────────────────────

date_default_timezone_set("America/Lima");

ob_start();
session_start();
Core::$root = "";

// si quieres que se muestre las consultas SQL debes decomentar la siguiente linea
// Core::$debug_sql = true;

$lb = new Lb();
$lb->start();

?>