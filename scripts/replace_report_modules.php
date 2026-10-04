<?php
require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/../core/app/autoload.php';

$db = Database::getCon();
if (!$db || $db->connect_errno) {
    fwrite(STDERR, "No se pudo conectar a la base de datos.\n");
    exit(1);
}

try {
    $newView = 'reportsventas';
    $newName = 'Reportes de comprobantes';
    $parentId = 26;

    $find = $db->prepare('SELECT id FROM module WHERE view_name = ? LIMIT 1');
    $find->bind_param('s', $newView);
    $find->execute();
    $newModule = $find->get_result()->fetch_assoc();

    if ($newModule) {
        $newModuleId = (int)$newModule['id'];
    } else {
        $insert = $db->prepare("INSERT INTO module (name, view_name, icon, parent_id, is_active, sort_order) VALUES (?, ?, 'fas fa-file-invoice-dollar', ?, 1, 9)");
        $insert->bind_param('ssi', $newName, $newView, $parentId);
        $insert->execute();
        $newModuleId = $db->insert_id;
    }

    $oldViews = ['reportsboleta', 'reportsfactura', 'reportsnotascredito', 'reportsnotascreditoboleta'];
    $placeholders = implode(',', array_fill(0, count($oldViews), '?'));
    $types = str_repeat('s', count($oldViews));
    $findOld = $db->prepare("SELECT id FROM module WHERE view_name IN ($placeholders)");
    $findOld->bind_param($types, ...$oldViews);
    $findOld->execute();
    $oldIds = [];
    $result = $findOld->get_result();
    while ($row = $result->fetch_assoc()) $oldIds[] = (int)$row['id'];

    foreach ($oldIds as $oldId) {
        $access = $db->prepare('SELECT DISTINCT user_id FROM user_access WHERE module_id = ? AND is_active = 1');
        $access->bind_param('i', $oldId);
        $access->execute();
        $users = $access->get_result();
        while ($user = $users->fetch_assoc()) {
            $userId = (int)$user['user_id'];
            $exists = $db->prepare('SELECT id FROM user_access WHERE user_id = ? AND module_id = ? LIMIT 1');
            $exists->bind_param('ii', $userId, $newModuleId);
            $exists->execute();
            if (!$exists->get_result()->fetch_assoc()) {
                $grant = $db->prepare('INSERT INTO user_access (user_id, module_id, is_active, created_at) VALUES (?, ?, 1, NOW())');
                $grant->bind_param('ii', $userId, $newModuleId);
                $grant->execute();
            } else {
                $enable = $db->prepare('UPDATE user_access SET is_active = 1, updated_at = NOW() WHERE user_id = ? AND module_id = ?');
                $enable->bind_param('ii', $userId, $newModuleId);
                $enable->execute();
            }
        }
    }

    if ($oldIds) {
        $oldIdList = implode(',', $oldIds);
        $db->query("UPDATE module SET is_active = 0 WHERE id IN ($oldIdList)");
    }

    echo "Modulo unificado activo: $newView (ID $newModuleId)\n";
    echo "Modulos anteriores desactivados: " . count($oldIds) . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}