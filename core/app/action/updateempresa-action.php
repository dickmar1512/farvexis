<?php
/**
 * updateempresa-action.php
 * Actualiza la información de la empresa y la configuración de SUNAT vía AJAX.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    if (count($_POST) === 0) {
        throw new Exception("No se recibieron datos.");
    }

    $empresa = EmpresaData::getDatos();
    if (!$empresa) {
        throw new Exception("No se encontró el registro de la empresa.");
    }

    $empresa->Emp_Ruc         = trim($_POST["ruc"] ?? "");
    $empresa->Emp_RazonSocial = addslashes(trim($_POST["razon_social"] ?? ""));
    $empresa->Emp_Descripcion = addslashes(trim($_POST["descripcion"] ?? ""));
    $empresa->Emp_Direccion   = addslashes(trim($_POST["direccion"] ?? ""));
    $empresa->Emp_Telefono    = trim($_POST["telefono"] ?? "");
    $empresa->Emp_Celular     = trim($_POST["celular"] ?? "");

    // Manejo de logo
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $rootDir = dirname(__DIR__, 3);
        $targetDir = $rootDir . "/storage/images/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            throw new Exception('El logo debe ser una imagen válida (.jpg, .jpeg, .png, .webp o .gif).');
        }
        $logoName = "logo_" . time() . "." . $ext;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $logoName)) {
            throw new Exception('No se pudo guardar el archivo del logo en el servidor.');
        }
        $empresa->Emp_Logo = $logoName;
    }

    $empresa->update();

    // Manejo de configuración SUNAT
    $empresa->sunat_igv_tipo_defecto = $_POST["sunat_igv_tipo_defecto"] ?? "20";
    $empresa->sunat_usuario_sol     = trim($_POST["sunat_usuario_sol"] ?? "MODDATOS");
    $empresa->sunat_clave_sol       = trim($_POST["sunat_clave_sol"] ?? "moddatos");
    $empresa->sunat_ambiente        = $_POST["sunat_ambiente"] ?? "beta";
    if (isset($_POST["sunat_cert_pass"]) && trim($_POST["sunat_cert_pass"]) !== '') {
        $empresa->sunat_cert_pass   = trim($_POST["sunat_cert_pass"]);
    }

    // Manejo de certificado digital
    if (isset($_FILES['sunat_cert_file']) && $_FILES['sunat_cert_file']['error'] == UPLOAD_ERR_OK) {
        $rootDir = dirname(__DIR__, 3);
        $certDir = $rootDir . "/storage/CERT/";
        if (!file_exists($certDir)) {
            mkdir($certDir, 0777, true);
        }
        $certExt = pathinfo($_FILES['sunat_cert_file']['name'], PATHINFO_EXTENSION);
        $certName = "cert_" . $empresa->Emp_Ruc . "_" . time() . "." . $certExt;
        $certDestination = $certDir . $certName;
        if (move_uploaded_file($_FILES['sunat_cert_file']['tmp_name'], $certDestination)) {
            $empresa->sunat_cert_path = "storage/CERT/" . $certName;
        }
    }

    $empresa->updateSunat();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Configuración de empresa y SUNAT guardada exitosamente.'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
