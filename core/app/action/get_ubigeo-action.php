<?php
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type == 'departments') {
    $sql = "SELECT DISTINCT departamento FROM ubigeo ORDER BY departamento";
    $query = Executor::doit($sql);
    $data = [];
    while ($r = $query[0]->fetch_array()) {
        $data[] = $r['departamento'];
    }
    echo json_encode($data);
} 
else if ($type == 'provinces') {
    $dep = $_GET['dep'] ?? '';
    $sql = "SELECT DISTINCT provincia FROM ubigeo WHERE departamento = '$dep' ORDER BY provincia";
    $query = Executor::doit($sql);
    $data = [];
    while ($r = $query[0]->fetch_array()) {
        $data[] = $r['provincia'];
    }
    echo json_encode($data);
} 
else if ($type == 'districts') {
    $dep = $_GET['dep'] ?? '';
    $prov = $_GET['prov'] ?? '';
    $sql = "SELECT codubigeo, distrito FROM ubigeo WHERE departamento = '$dep' AND provincia = '$prov' ORDER BY distrito";
    $query = Executor::doit($sql);
    $data = [];
    while ($r = $query[0]->fetch_array()) {
        $data[] = ['id' => $r['codubigeo'], 'name' => $r['distrito']];
    }
    echo json_encode($data);
}
else if ($type == 'details') {
    $id = $_GET['id'] ?? '';
    if ($id) {
        $sql = "SELECT departamento, provincia, distrito FROM ubigeo WHERE codubigeo = '$id' LIMIT 1";
        $query = Executor::doit($sql);
        $r = $query[0]->fetch_array();
        echo json_encode($r);
    } else {
        echo json_encode(null);
    }
}
exit;
?>
