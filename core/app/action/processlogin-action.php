<?php
// Cargar las clases necesarias
header('Content-Type: application/json');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está logueado
if(isset($_SESSION["user_id"])) {
    echo json_encode(['success' => true]);
    exit;
}

// Validar que vengan los datos
if(empty($_POST['username']) || empty($_POST['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña son requeridos'
    ]);
    exit;
}

$user = $_POST['username'];
$pass = sha1(md5($_POST['password']));

$base = new Database();
$con = $base->connect();
$sql = "SELECT * FROM user WHERE (email= \"".$user."\" OR username= \"".$user."\") AND password= \"".$pass."\" AND is_active=1";
$query = $con->query($sql);
$found = false;
$userid = null;

while($r = $query->fetch_array()){
    $found = true;
    $userid = $r['id'];
    $datosUsuario = $r['name'] . " " . $r['lastname'];
    $usuario = $r['username'];
    $fechaIngreso = date("Y-m-d H:i:s");
}

if($found) {
    $_SESSION['user_id'] = $userid;
	$_SESSION['sucursal_id'] = (int)($r['sucursal_id'] ?? 1);
    
    // Enviar correo (puedes mover esto a un proceso en segundo plano si es muy lento)
    $arraddress = $arrAddcc = array();
    //$arraddress[] = 'juan.irene@kalpg.com';
    $arraddress[] = 'dick_mar@hotmail.com';
    $arrAddcc[] = 'sagitatario.1982@gmail.com';
    //$arrAddcc[] = 'mayaya.ocampo@gmail.com';
    $asunto = "Acceso al sistema CAREPHARM";
    
    $mailer = new CLSPHPMailer();
    $cuerpo = ' <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5; padding: 40px 0;font-family: Georgia, serif;">
                    <tr>
                    <td align="center">

                        <!-- Card Container -->
                        <table width="600" cellpadding="0" cellspacing="0" border="0"
                            style="background-color:#ffffff; border-radius:8px;
                                    box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow:hidden;
                                    max-width:600px; width:100%;">

                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #0d1b2a 0%, #1b3a5c 100%);
                                    padding: 36px 40px; text-align: left;">
                            <p style="margin:0; font-family: Georgia, serif;
                                        font-size: 22px; font-weight: bold;
                                        color: #ffffff; letter-spacing: 2px; text-transform: uppercase;">
                                CAREPHARM
                            </p>
                            <p style="margin: 4px 0 0 0; font-size: 12px;
                                        color: #7fa8cc; letter-spacing: 1px; text-transform: uppercase;">
                                Sistema de Seguridad
                            </p>
                            </td>
                        </tr>

                        <!-- Alert Badge -->
                        <tr>
                            <td style="padding: 32px 40px 8px 40px;">
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                <td style="background-color: #fff3cd; border-left: 4px solid #f0a500;
                                            border-radius: 4px; padding: 10px 16px;">
                                    <p style="margin:0; font-size:13px; color:#7a5000;
                                            font-family: Arial, sans-serif; font-weight:600; letter-spacing:0.5px;">
                                    ⚠️&nbsp; NOTIFICACIÓN DE ACCESO AL SISTEMA
                                    </p>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>

                        <!-- Body Text -->
                        <tr>
                            <td style="padding: 20px 40px 24px 40px;">
                            <p style="margin:0; font-size:15px; color:#3d3d3d;
                                        font-family: Arial, sans-serif; line-height:1.7;">
                                Se ha registrado un nuevo ingreso al sistema. A continuación se detallan
                                los datos del acceso realizado.
                            </p>
                            </td>
                        </tr>

                        <!-- Data Table -->
                        <tr>
                            <td style="padding: 0 40px 36px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                    style="border-radius:6px; overflow:hidden;
                                            border: 1px solid #e2e8f0;">

                                <!-- Table Header -->
                                <tr style="background-color: #0d1b2a;">
                                <td style="padding:12px 16px; font-family: Arial, sans-serif;
                                            font-size:11px; font-weight:700; color:#7fa8cc;
                                            letter-spacing:1.2px; text-transform:uppercase;
                                            border-right: 1px solid #1b3a5c;">
                                    Nombre y Apellidos
                                </td>
                                <td style="padding:12px 16px; font-family: Arial, sans-serif;
                                            font-size:11px; font-weight:700; color:#7fa8cc;
                                            letter-spacing:1.2px; text-transform:uppercase;
                                            border-right: 1px solid #1b3a5c;">
                                    Usuario
                                </td>
                                <td style="padding:12px 16px; font-family: Arial, sans-serif;
                                            font-size:11px; font-weight:700; color:#7fa8cc;
                                            letter-spacing:1.2px; text-transform:uppercase;">
                                    Fecha y Hora de Acceso
                                </td>
                                </tr>

                                <!-- Table Row -->
                                <tr style="background-color:#f8fafc;">
                                <td style="padding:16px; font-family: Arial, sans-serif;
                                            font-size:14px; color:#1a202c; font-weight:600;
                                            border-top: 1px solid #e2e8f0;
                                            border-right: 1px solid #e2e8f0;">
                                    ' . $datosUsuario . '
                                </td>
                                <td style="padding:16px; font-family: Arial, sans-serif;
                                            font-size:14px; color:#1a202c;
                                            border-top: 1px solid #e2e8f0;
                                            border-right: 1px solid #e2e8f0;">
                                    <span style="background-color:#eef2ff; color:#3730a3;
                                                padding:3px 10px; border-radius:20px;
                                                font-size:13px; font-weight:600;">
                                    ' . $usuario . '
                                    </span>
                                </td>
                                <td style="padding:16px; font-family: Arial, sans-serif;
                                            font-size:14px; color:#1a202c;
                                            border-top: 1px solid #e2e8f0;">
                                    🕐&nbsp;' . $fechaIngreso . '
                                </td>
                                </tr>

                            </table>
                            </td>
                        </tr>

                        <!-- Divider -->
                        <tr>
                            <td style="padding: 0 40px;">
                            <hr style="border:none; border-top:1px solid #e2e8f0; margin:0;">
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding: 24px 40px; background-color:#f8fafc;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;
                                        font-family: Arial, sans-serif; line-height:1.6;">
                                Este mensaje fue generado automáticamente por el sistema de seguridad.
                                Si usted no reconoce este acceso, contacte al administrador de inmediato.
                            </p>
                            <p style="margin: 12px 0 0 0; font-size:11px; color:#cbd5e1;
                                        font-family: Arial, sans-serif;">
                                © ' . date('Y') . ' CAREPHARM | Todos los derechos reservados
                            </p>
                            </td>
                        </tr>

                        </table>
                        <!-- End Card -->

                    </td>
                    </tr>
                </table>';
    
    $firma = '<tr>

          <!-- Nombre corporativo -->
          <td style="vertical-align: middle;">
            <p style="margin:0; font-family: Georgia, serif;
                      font-size: 15px; font-weight: bold;
                      color: #ffffff; letter-spacing: 2px;
                      text-transform: uppercase;">
              Botica Alfonzo Ugarte
            </p>
            <p style="margin: 4px 0 0 0; font-family: Arial, sans-serif;
                      font-size: 11px; color: #7fa8cc;
                      letter-spacing: 1px; text-transform: uppercase;">
              Salud &amp; Bienestar
            </p>
          </td>

          <!-- Email de contacto -->
          <td style="text-align: right; vertical-align: middle;">
            <a href="mailto:botica.au@gmail.com"
               style="font-family: Arial, sans-serif;
                      font-size: 13px; color: #7fa8cc;
                      text-decoration: none; letter-spacing: 0.3px;">
              ✉&nbsp; botica.au@gmail.com
            </a>
          </td>

        </tr>';           

    // Enviar el correo (puedes comentar esto si no siempre quieres enviar el correo)
    $mailer->fnMail($arraddress, $arrAddcc, $asunto, $cuerpo, 'pie', $firma, null);
    
    echo json_encode(['success' => true]);
	exit(0);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario o contraseña incorrectos'
    ]);
	exit(0);
}
?>