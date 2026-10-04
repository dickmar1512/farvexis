<?php
require_once __DIR__ . '/../core/autoload.php';

$db = Database::getCon();
if (!$db || $db->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

function notaColumnExists(mysqli $db, string $column): bool
{
    $column = $db->real_escape_string($column);
    $result = $db->query("SHOW COLUMNS FROM nota LIKE '$column'");
    return $result && $result->num_rows > 0;
}

$columns = [
    'estado_sunat' => "VARCHAR(20) NOT NULL DEFAULT 'pendiente'",
    'cdr_codigo' => 'VARCHAR(30) NULL',
    'cdr_descripcion' => 'TEXT NULL',
    'codigo_hash' => 'VARCHAR(255) NULL',
    'fecha_envio_sunat' => 'DATETIME NULL'
];

foreach ($columns as $name => $definition) {
    if (!notaColumnExists($db, $name) && !$db->query("ALTER TABLE nota ADD COLUMN `$name` $definition")) {
        fwrite(STDERR, "Error agregando $name: {$db->error}\n");
        exit(1);
    }
}

echo "Campos SUNAT de notas listos.\n";
