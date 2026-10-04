<?php
/**
 * router.php
 * Define el mapa de rutas y los helpers para generar/parsear URLs amigables.
 *
 * MAPA DE RUTAS:
 *   'view-name' => ['param1', 'param2', ...]
 *
 * Ejemplo:
 *   url('onesell', ['id'=>7573, 'tipodoc'=>3])  →  /onesell/7573/3
 *   url('notacreditot', ['num'=>'F001-0000123']) →  /notacreditot/F001-0000123
 *   url('home')                                 →  /home
 */

// ── BASE URL del proyecto ────────────────────────────────────────────────────
define('BASE_URL', '/farvexis');

// ── Mapa de rutas ────────────────────────────────────────────────────────────
$GLOBALS['_routes'] = [
    // Ventas
    'onesell'                  => ['id', 'tipodoc'],
    'onesellc'                 => ['id'],
    'onesellf'                 => ['id'],
    'onesell2c'                => ['id'],
    'onesell2f'                => ['id'],
    'onesell2t'                => ['id'],
    'onesellsd'                => ['id'],
    'proforma'                 => ['id'],
    'ordenventa3'              => ['id'],
    // Notas de crédito/débito
    'nocboleta'                => ['id'],
    'nocfactura'               => ['id'],
    'nodboleta'                => ['id'],
    'nodfactura'               => ['id'],
    'notacredito'              => ['num'],
    'notacreditot'             => ['num'],
    'notacreditoboleta'        => ['num'],
    'notacreditoboletat'       => ['num'],
    'notadebito'               => ['num'],
    'notadebitot'              => ['num'],
    'notadebitoboleta'         => ['num'],
    'notadebitoboletat'        => ['num'],
    'noc2'                     => ['id'],
    'nodf'                     => ['id'],
    // Reabastecimientos
    'onere'                    => ['id'],
    'editre'                   => ['id'],
    // Órdenes de trabajo
    'oneorden'                 => ['id'],
    'editarordentrabajo'       => ['id'],
    'repuestosordentrabajo'    => ['id'],
    // Productos / kits
    'editproduct'              => ['id'],
    'editkit'                  => ['id'],
    'history'                  => ['id'],
    // Caja
    'b'                        => ['id'],
    'box'                      => ['id'],
    // Categorías
    'editcategory'             => ['id'],
    'delcategory'              => ['id'],
];

// ── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Genera una URL amigable.
 *
 * url('onesell', ['id' => 7573, 'tipodoc' => 3])  →  /farvexis/onesell/7573/3
 * url('notacreditot', ['num' => 'F001-001'])       →  /farvexis/notacreditot/F001-001
 * url('home')                                      →  /farvexis/home
 */
function url(string $view, array $params = []): string {
    $routes = $GLOBALS['_routes'];
    $base   = BASE_URL . '/' . $view;

    if (!empty($routes[$view]) && !empty($params)) {
        $segments = [];
        $extras   = [];
        foreach ($params as $key => $val) {
            $pos = array_search($key, $routes[$view], true);
            if ($pos !== false) {
                $segments[$pos] = rawurlencode((string)$val);
            } else {
                $extras[$key] = $val;
            }
        }
        ksort($segments);
        $base .= '/' . implode('/', $segments);
        if ($extras) {
            $base .= '?' . http_build_query($extras);
        }
    } elseif (!empty($params)) {
        $base .= '?' . http_build_query($params);
    }

    return $base;
}

/**
 * Parsea los segmentos de la URL y rellena $_GET con los parámetros
 * correspondientes según el mapa de rutas.
 *
 * Llamado automáticamente por index.php al inicio.
 */
function router_parse(): void {
    if (!isset($_GET['_path']) || $_GET['_path'] === '' || $_GET['_path'] === '/') {
        unset($_GET['_path']);
        return;
    }

    $view     = $_GET['view'] ?? '';
    $routes   = $GLOBALS['_routes'];
    $segments = array_values(array_filter(explode('/', trim($_GET['_path'], '/'))));

    if (isset($routes[$view])) {
        foreach ($routes[$view] as $pos => $key) {
            if (isset($segments[$pos]) && !isset($_GET[$key])) {
                $_GET[$key] = rawurldecode($segments[$pos]);
            }
        }
    }

    unset($_GET['_path']);
}
?>
