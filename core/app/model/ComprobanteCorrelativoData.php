<?php

#[AllowDynamicProperties]
class ComprobanteCorrelativoData
{
    public static function configurados(): array
    {
        $query = Executor::doit("SELECT c.*, s.nombre AS sucursal_nombre, s.codigo AS sucursal_codigo
            FROM comprobante_correlativo c
            INNER JOIN sucursal s ON s.id = c.sucursal_id
            ORDER BY s.nombre, c.tipo_comprobante, c.serie");
        return Model::many($query[0], new ComprobanteCorrelativoData());
    }

    public static function guardar(int $sucursalId, string $tipo, string $serie, string $local, int $ultimo): void
    {
        $tipo = str_pad(preg_replace('/[^0-9]/', '', $tipo), 2, '0', STR_PAD_LEFT);
        $serie = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($serie)));
        $local = str_pad(preg_replace('/[^0-9]/', '', $local), 4, '0', STR_PAD_LEFT);
        $sucursalId = max(1, $sucursalId);
        $ultimo = max(0, $ultimo);
        if (!in_array($tipo, ['01', '03', '07', '08'], true) || $serie === '') {
            throw new InvalidArgumentException('Tipo o serie de comprobante no válidos.');
        }
        $db = Database::getCon();
        $tipo = $db->real_escape_string($tipo);
        $serie = $db->real_escape_string($serie);
        $local = $db->real_escape_string($local);
        $sql = "INSERT INTO comprobante_correlativo
            (sucursal_id, cod_local_emisor, tipo_comprobante, serie, ultimo_numero)
            VALUES ($sucursalId, '$local', '$tipo', '$serie', $ultimo)
            ON DUPLICATE KEY UPDATE ultimo_numero = GREATEST(ultimo_numero, VALUES(ultimo_numero))";
        if (!$db->query($sql)) throw new RuntimeException('No se pudo guardar la serie: ' . $db->error);
    }

    public static function siguiente(mysqli $conexion, string $tipo, string $serie, string $codLocalEmisor = '0000', int $sucursalId = 1): string
    {
        $tipo = preg_replace('/[^0-9]/', '', $tipo);
        $serie = strtoupper(trim($serie));
        $serie = preg_replace('/[^A-Z0-9]/', '', $serie);
        $codLocalEmisor = preg_replace('/[^0-9]/', '', $codLocalEmisor) ?: '0000';
        $sucursalId = max(1, $sucursalId);

        if ($tipo === '' || $serie === '') {
            throw new InvalidArgumentException('El tipo y la serie del comprobante son obligatorios.');
        }

        $tipoSql = $conexion->real_escape_string(str_pad($tipo, 2, '0', STR_PAD_LEFT));
        $serieSql = $conexion->real_escape_string($serie);
        $localSql = $conexion->real_escape_string(str_pad($codLocalEmisor, 4, '0', STR_PAD_LEFT));

        $maxFactura = self::maximoExistente($conexion, 'factura', $tipoSql, $serieSql);
        $maxBoleta = self::maximoExistente($conexion, 'boleta', $tipoSql, $serieSql);
        $inicio = max($maxFactura, $maxBoleta);

        $sqlInsert = "INSERT IGNORE INTO comprobante_correlativo
            (sucursal_id, cod_local_emisor, tipo_comprobante, serie, ultimo_numero)
            VALUES ($sucursalId, '$localSql', '$tipoSql', '$serieSql', $inicio)";
        if (!$conexion->query($sqlInsert)) {
            throw new RuntimeException('No se pudo inicializar el correlativo: ' . $conexion->error);
        }

        $sqlSync = "UPDATE comprobante_correlativo
            SET ultimo_numero = GREATEST(ultimo_numero, $inicio)
            WHERE sucursal_id = $sucursalId
              AND cod_local_emisor = '$localSql'
              AND tipo_comprobante = '$tipoSql'
              AND serie = '$serieSql'";
        if (!$conexion->query($sqlSync)) {
            throw new RuntimeException('No se pudo sincronizar el correlativo: ' . $conexion->error);
        }

        $sqlUpdate = "UPDATE comprobante_correlativo
            SET ultimo_numero = LAST_INSERT_ID(ultimo_numero + 1)
            WHERE sucursal_id = $sucursalId
              AND cod_local_emisor = '$localSql'
              AND tipo_comprobante = '$tipoSql'
              AND serie = '$serieSql'";
        if (!$conexion->query($sqlUpdate) || $conexion->affected_rows !== 1) {
            throw new RuntimeException('No se pudo reservar el correlativo del comprobante.');
        }

        $resultado = $conexion->query('SELECT LAST_INSERT_ID() AS numero');
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        $numero = (int)($fila['numero'] ?? 0);
        if ($numero < 1) {
            throw new RuntimeException('El correlativo reservado no es válido.');
        }

        return str_pad((string)$numero, 8, '0', STR_PAD_LEFT);
    }

    private static function maximoExistente(mysqli $conexion, string $tabla, string $tipo, string $serie): int
    {
        $sql = "SELECT COALESCE(MAX(CAST(COMPROBANTE AS UNSIGNED)), 0) AS ultimo
                FROM `$tabla`
                WHERE TIPO = '$tipo' AND SERIE = '$serie'";
        $resultado = $conexion->query($sql);
        if (!$resultado) {
            throw new RuntimeException('No se pudo consultar el historial de correlativos: ' . $conexion->error);
        }
        $fila = $resultado->fetch_assoc();
        return (int)($fila['ultimo'] ?? 0);
    }
}
