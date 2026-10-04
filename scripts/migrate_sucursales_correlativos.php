<?php
require_once __DIR__ . '/../core/autoload.php';

$db = Database::getCon();
if (!$db || $db->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

$userColumn = $db->query("SHOW COLUMNS FROM user LIKE 'sucursal_id'");
if ($userColumn && $userColumn->num_rows === 0 && !$db->query("ALTER TABLE user ADD COLUMN sucursal_id INT UNSIGNED NOT NULL DEFAULT 1")) {
    fwrite(STDERR, "Error agregando sucursal_id a user: {$db->error}\n");
    exit(1);
}

$queries = [
    "CREATE TABLE IF NOT EXISTS sucursal (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        codigo CHAR(4) NOT NULL,
        nombre VARCHAR(120) NOT NULL,
        direccion VARCHAR(255) NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_sucursal_codigo (codigo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "INSERT INTO sucursal (id, codigo, nombre)
        VALUES (1, '0000', 'Principal')
        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)",
    "CREATE TABLE IF NOT EXISTS comprobante_correlativo (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sucursal_id INT UNSIGNED NOT NULL DEFAULT 1,
        cod_local_emisor CHAR(4) NOT NULL DEFAULT '0000',
        tipo_comprobante CHAR(2) NOT NULL,
        serie VARCHAR(10) NOT NULL,
        ultimo_numero BIGINT UNSIGNED NOT NULL DEFAULT 0,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_correlativo (sucursal_id, cod_local_emisor, tipo_comprobante, serie),
        CONSTRAINT fk_correlativo_sucursal FOREIGN KEY (sucursal_id) REFERENCES sucursal(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($queries as $query) {
    if (!$db->query($query)) {
        fwrite(STDERR, "Error en migración: {$db->error}\n");
        exit(1);
    }
}

echo "Tablas de sucursales y correlativos listas.\n";