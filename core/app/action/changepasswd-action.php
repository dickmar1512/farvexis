<?php
/**
 * changepasswd-action.php
 * Cambia la contraseña del usuario actual y responde en formato JSON.
 */

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Sesión inválida o expirada."]);
    exit;
}

$user = UserData::getById($_SESSION["user_id"]);
if (!$user) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
    exit;
}

$currentPassword = sha1(md5($_POST["password"] ?? ''));
if ($currentPassword !== $user->password) {
    echo json_encode(["status" => "error", "message" => "La contraseña actual no es correcta."]);
    exit;
}

$newPassword = $_POST["newpassword"] ?? '';
if (empty($newPassword)) {
    echo json_encode(["status" => "error", "message" => "La nueva contraseña no puede estar vacía."]);
    exit;
}

$user->password = sha1(md5($newPassword));
$user->update_passwd();
setcookie("password_updated", "true");

echo json_encode([
    "status"   => "success",
    "message"  => "Contraseña actualizada con éxito. Debe iniciar sesión nuevamente.",
    "redirect" => "./?action=logout"
]);
exit;
