<?php
require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/../core/app/autoload.php';

$db = Database::getCon();
if (!$db || $db->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

$parentResult = $db->query("SELECT id FROM module WHERE (name IN ('Administración', 'Administracion') OR view_name IN ('admin', 'administracion')) AND parent_id IS NULL LIMIT 1");
$parent = $parentResult ? $parentResult->fetch_assoc() : null;
if (!$parent) {
    fwrite(STDERR, "No se encontró el módulo padre Administración.\n");
    exit(1);
}
$parentId = (int)$parent['id'];
$modules = [
    ['Sucursales', 'sucursales', 'fas fa-code-branch', 20],
    ['Series y correlativos', 'correlativos', 'fas fa-list-ol', 21]
];
$moduleIds = [];

foreach ($modules as [$name, $view, $icon, $sort]) {
    $find = $db->prepare('SELECT id FROM module WHERE view_name = ? LIMIT 1');
    $find->bind_param('s', $view);
    $find->execute();
    $row = $find->get_result()->fetch_assoc();
    if ($row) {
        $moduleId = (int)$row['id'];
        $update = $db->prepare('UPDATE module SET name = ?, icon = ?, parent_id = ?, is_active = 1, sort_order = ? WHERE id = ?');
        $update->bind_param('ssiii', $name, $icon, $parentId, $sort, $moduleId);
        $update->execute();
    } else {
        $insert = $db->prepare('INSERT INTO module (name, view_name, icon, parent_id, is_active, sort_order) VALUES (?, ?, ?, ?, 1, ?)');
        $insert->bind_param('sssii', $name, $view, $icon, $parentId, $sort);
        $insert->execute();
        $moduleId = (int)$db->insert_id;
    }
    $moduleIds[] = $moduleId;
}

$admins = $db->query('SELECT id FROM user WHERE is_admin = 1 AND is_active = 1');
while ($admin = $admins->fetch_assoc()) {
    foreach ($moduleIds as $moduleId) {
        $userId = (int)$admin['id'];
        $exists = $db->query("SELECT id FROM user_access WHERE user_id = $userId AND module_id = $moduleId LIMIT 1");
        if ($exists && $exists->num_rows) {
            $db->query("UPDATE user_access SET is_active = 1, updated_at = NOW() WHERE user_id = $userId AND module_id = $moduleId");
        } else {
            $db->query("INSERT INTO user_access (user_id, module_id, is_active, created_at) VALUES ($userId, $moduleId, 1, NOW())");
        }
    }
}

echo "Módulos de Administración listos: sucursales y correlativos.\n";
