<?php
/**
 * Migracion idempotente para trazabilidad de lotes.
 * Ejecutar una sola vez desde la raiz: php scripts/migrate_lotes.php
 */
require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/../core/app/autoload.php';

$db = Database::getCon();
if (!$db || $db->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

function columnExists(mysqli $db, string $table, string $column): bool
{
    $table = $db->real_escape_string($table);
    $column = $db->real_escape_string($column);
    $result = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

function addColumn(mysqli $db, string $table, string $column, string $definition): void
{
    if (!columnExists($db, $table, $column)) {
        if (!$db->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
            throw new RuntimeException($db->error);
        }
        echo "Columna agregada: $table.$column\n";
    }
}

try {
    addColumn($db, 'lote', 'fecha_fabricacion', 'DATE NULL');
    addColumn($db, 'lote', 'fecha_vencimiento', 'DATE NULL');
    addColumn($db, 'lote', 'proveedor_id', 'INT NULL');
    addColumn($db, 'lote', 'cantidad_inicial', 'DECIMAL(12,3) NOT NULL DEFAULT 0');
    addColumn($db, 'lote', 'cantidad_disponible', 'DECIMAL(12,3) NOT NULL DEFAULT 0');
    addColumn($db, 'lote', 'costo_unitario', 'DECIMAL(12,4) NULL');
    addColumn($db, 'lote', 'estado', "VARCHAR(20) NOT NULL DEFAULT 'cuarentena'");
    addColumn($db, 'lote', 'motivo_bloqueo', 'VARCHAR(255) NULL');
    addColumn($db, 'lote', 'ubicacion', 'VARCHAR(100) NULL');
    addColumn($db, 'lote', 'created_at', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    addColumn($db, 'lote', 'updated_at', 'DATETIME NULL');

    $tables = [
        "CREATE TABLE IF NOT EXISTS lote_movimiento (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lote_id INT NOT NULL,
            product_id INT NOT NULL,
            operation_id INT NULL,
            tipo VARCHAR(20) NOT NULL,
            cantidad DECIMAL(12,3) NOT NULL,
            costo_unitario DECIMAL(12,4) NULL,
            referencia VARCHAR(100) NULL,
            observacion VARCHAR(255) NULL,
            user_id INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lote_movimiento_lote (lote_id),
            INDEX idx_lote_movimiento_producto (product_id),
            INDEX idx_lote_movimiento_operation (operation_id)
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS operation_lote (
            id INT AUTO_INCREMENT PRIMARY KEY,
            operation_id INT NOT NULL,
            lote_id INT NOT NULL,
            cantidad DECIMAL(12,3) NOT NULL,
            costo_unitario DECIMAL(12,4) NULL,
            UNIQUE KEY uq_operation_lote (operation_id, lote_id),
            INDEX idx_operation_lote_lote (lote_id)
        ) ENGINE=InnoDB"
    ];

    foreach ($tables as $sql) {
        if (!$db->query($sql)) {
            throw new RuntimeException($db->error);
        }
    }

    $seedSql = "INSERT INTO lote
        (id_prod, num_lot, fech_ing, user_id, cantidad_inicial, cantidad_disponible, estado, created_at)
        SELECT p.id, 'INVENTARIO-INICIAL', NOW(), COALESCE(p.user_id, 1),
               p.stock, p.stock,
               CASE WHEN p.stock > 0 THEN 'disponible' ELSE 'agotado' END,
               NOW()
        FROM product p
        LEFT JOIN lote l ON l.id_prod = p.id AND l.num_lot = 'INVENTARIO-INICIAL'
        WHERE p.stock > 0 AND COALESCE(p.is_stock, 1) = 1 AND l.id IS NULL";

    if (!$db->query($seedSql)) {
        throw new RuntimeException($db->error);
    }

    echo "Tablas de lotes creadas. Registros iniciales: {$db->affected_rows}\n";
    echo "Migracion completada correctamente.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error en migracion: {$e->getMessage()}\n");
    exit(1);
}
