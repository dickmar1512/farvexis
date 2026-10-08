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

    public static function guardar(int $id, int $sucursalId, string $tipo, string $serie, string $local, int $ultimo): void
    {
        $tipo = str_pad(preg_replace('/[^0-9]/', '', $tipo), 2, '0', STR_PAD_LEFT);
        $serie = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($serie)));
        $local = str_pad(preg_replace('/[^0-9]/', '', $local), 4, '0', STR_PAD_LEFT);
        $sucursalId = max(1, $sucursalId);
        $ultimo = max(0, $ultimo);
        if (!in_array($tipo, ['01', '03', '07', '08', '60', '65', '70'], true) || $serie === '') {
            throw new InvalidArgumentException('Tipo o serie de comprobante no válidos.');
        }
        $db = Database::getCon();
        $tipo = $db->real_escape_string($tipo);
        $serie = $db->real_escape_string($serie);
        $local = $db->real_escape_string($local);
        
        if ($id > 0) {
            $sql = "UPDATE comprobante_correlativo SET sucursal_id = $sucursalId, cod_local_emisor = '$local', tipo_comprobante = '$tipo', serie = '$serie', ultimo_numero = $ultimo WHERE id = $id";
        } else {
            $sql = "INSERT INTO comprobante_correlativo
                (sucursal_id, cod_local_emisor, tipo_comprobante, serie, ultimo_numero)
                VALUES ($sucursalId, '$local', '$tipo', '$serie', $ultimo)
                ON DUPLICATE KEY UPDATE ultimo_numero = GREATEST(ultimo_numero, VALUES(ultimo_numero))";
        }
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

        // Solo insertamos si no existe, iniciando en 0
        $sqlInsert = "INSERT IGNORE INTO comprobante_correlativo
            (sucursal_id, cod_local_emisor, tipo_comprobante, serie, ultimo_numero)
            VALUES ($sucursalId, '$localSql', '$tipoSql', '$serieSql', 0)";
        if (!$conexion->query($sqlInsert)) {
            throw new RuntimeException('No se pudo inicializar el correlativo: ' . $conexion->error);
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

    public static function verSiguiente(mysqli $conexion, string $tipo, string $serie, string $codLocalEmisor = '0000', int $sucursalId = 1): string
    {
        $tipo = preg_replace('/[^0-9]/', '', $tipo);
        $serie = strtoupper(trim($serie));
        $serie = preg_replace('/[^A-Z0-9]/', '', $serie);
        $codLocalEmisor = preg_replace('/[^0-9]/', '', $codLocalEmisor) ?: '0000';
        $sucursalId = max(1, $sucursalId);

        if ($tipo === '' || $serie === '') {
            return '00000001';
        }

        $tipoSql = $conexion->real_escape_string(str_pad($tipo, 2, '0', STR_PAD_LEFT));
        $serieSql = $conexion->real_escape_string($serie);
        $localSql = $conexion->real_escape_string(str_pad($codLocalEmisor, 4, '0', STR_PAD_LEFT));

        $sql = "SELECT ultimo_numero FROM comprobante_correlativo 
                WHERE sucursal_id = $sucursalId 
                  AND cod_local_emisor = '$localSql' 
                  AND tipo_comprobante = '$tipoSql' 
                  AND serie = '$serieSql'";
        $resultado = $conexion->query($sql);
        $ultimo = 0;
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $ultimo = (int)($fila['ultimo_numero'] ?? 0);
        }
        return str_pad((string)($ultimo + 1), 8, '0', STR_PAD_LEFT);
    }

    public static function getSerieDefecto(string $tipo, string $codLocalEmisor, string $tipoRelacionado = ''): string
    {
        $num = (int)$codLocalEmisor + 1;
        switch ($tipo) {
            case '01': return 'F' . str_pad((string)$num, 3, '0', STR_PAD_LEFT); // Factura: F001, F002
            case '03': return 'B' . str_pad((string)$num, 3, '0', STR_PAD_LEFT); // Boleta: B001, B002
            case '07': // Nota Credito
                if ($tipoRelacionado === '01') return 'FNC' . $num;
                return 'BNC' . $num;
            case '08': // Nota Debito
                if ($tipoRelacionado === '01') return 'FND' . $num;
                return 'BND' . $num;
            case '60': return 'I' . str_pad((string)$num, 3, '0', STR_PAD_LEFT); // Ingreso Diverso: I001, I002
            case '65': return 'S' . str_pad((string)$num, 3, '0', STR_PAD_LEFT); // Salida Diversa: S001, S002
            case '70': return 'NV' . str_pad((string)$num, 2, '0', STR_PAD_LEFT); // Nota Venta: NV01, NV02
            case '75': return 'T' . str_pad((string)$num, 3, '0', STR_PAD_LEFT); // Orden de Traslado: T001, T002
            default: return '000' . $num;
        }
    }
}
