<?php
/**
 * logout-action.php
 * Destruye la sesión y redirige al inicio.
 */
session_start();

if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}

session_destroy();
header('Location: ./');
exit;
?>
