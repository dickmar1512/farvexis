<?php
/**
 * sync_correlativos.php
 * Sincroniza e inserta la cantidad actual / correlativo máximo de comprobantes 
 * en la tabla comprobante_correlativo.
 */

require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/../core/app/autoload.php';

$con = Database::getCon();
if (!$con || $con->connect_errno) {
    fwrite(STDERR, "Error al conectar a la base de datos.\n");
    exit(1);
}

try {
    $sucursalId = 1;
    $local = '0000';

    $configs = [
        ['01', 'F001', 'factura', '01'],
        ['03', 'B001', 'boleta', '03'],
        ['07', 'FNC1', 'factura', '07'],
        ['07', 'BNC1', 'boleta', '07'],
        ['08', 'FND1', 'factura', '08'],
        ['08', 'BND1', 'boleta', '08'],
    ];

    $updated = 0;
    foreach ($configs as $cfg) {
        list($tipo, $serie, $tabla, $tipoCond) = $cfg;

        $sqlMax = "SELECT COALESCE(MAX(CAST(COMPROBANTE AS UNSIGNED)), 0) AS max_num, COUNT(*) as cnt FROM `$tabla` WHERE TIPO = '$tipoCond' AND SERIE = '$serie'";
        $resMax = $con->query($sqlMax);
        $rowMax = $resMax ? $resMax->fetch_assoc() : ['max_num' => 0, 'cnt' => 0];

        $maxNum = (int)$rowMax['max_num'];
        $cnt = (int)$rowMax['cnt'];
        $ultimo = max($maxNum, $cnt);

        $chk = $con->query("SELECT id FROM comprobante_correlativo WHERE sucursal_id = $sucursalId AND cod_local_emisor = '$local' AND tipo_comprobante = '$tipo' AND serie = '$serie'");
        if ($chk && $chk->num_rows > 0) {
            $rowChk = $chk->fetch_assoc();
            $id = $rowChk['id'];
            $con->query("UPDATE comprobante_correlativo SET ultimo_numero = $ultimo, updated_at = NOW() WHERE id = $id");
        } else {
            $con->query("INSERT INTO comprobante_correlativo (sucursal_id, cod_local_emisor, tipo_comprobante, serie, ultimo_numero, updated_at) VALUES ($sucursalId, '$local', '$tipo', '$serie', $ultimo, NOW())");
        }
        $updated++;
    }

    // Limpiar inconsistencias si existieran
    $con->query("DELETE FROM comprobante_correlativo WHERE tipo_comprobante = '08' AND serie = 'BNC1'");

    echo "Sincronizacion de comprobante_correlativo finalizada exitosamente ($updated series procesadas).\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error durante la sincronizacion: " . $e->getMessage() . "\n");
    exit(1);
}
