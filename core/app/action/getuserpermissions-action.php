<?php
$user_id = $_GET["id"];
$modules = ModuleData::getAll();
$access = UserAccessData::getAllByUserId($user_id);

$access_ids = [];
foreach($access as $a){
    $access_ids[] = $a->module_id;
}

$data = [];
foreach($modules as $m){
    $data[] = [
        "id" => $m->id,
        "name" => $m->name,
        "parent_id" => $m->parent_id,
        "has_access" => in_array($m->id, $access_ids)
    ];
}

echo json_encode($data);
?>
