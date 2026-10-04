<?php
class GastoData
{
    public static $tablename = "gastos";
    public $id;
    public $descripcion;
    public $comprobante;
    public $importe;
    public $fecha;
    public $usuario_id;

    public function __construct()
    {
        $this->descripcion = "";
        $this->comprobante = "";
        $this->importe = 0.0;
        $this->fecha = date("Y-m-d H:i:s");
        $this->usuario_id = 0;
    }

    public static function getAll()
    {
        $sql = "SELECT * FROM " . self::$tablename . " ORDER BY fecha DESC";
        $query = Executor::doit(sql: $sql);
        $array = array();
        $cnt = 0;
        while ($r = $query[0]->fetch_array()) {
            $array[$cnt] = new GastoData();
            $array[$cnt]->descripcion = $r['descripcion'];
            $array[$cnt]->comprobante = $r['comprobante'];
            $array[$cnt]->importe = $r['importe'];
            $array[$cnt]->fecha = $r['fecha'];
            $array[$cnt]->usuario_id = $r['usuario_id'];
            $cnt++;
        }
        return $array;
    }

    // public static function getByUserId($user_id) {
    //     $sql = "SELECT * FROM ".self::$tablename." WHERE usuario_id = ? ORDER BY fecha DESC";
    //     $params = [$user_id];
    //     return Model::getArray($sql, $params);
    // }

    public static function getByFilters($start_date, $end_date, $user_id = 0)
    {
        $sql = "SELECT g.* FROM " . self::$tablename . " g 
                WHERE DATE_FORMAT(g.fecha,'%Y-%m-%d') BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

        if ($user_id > 0) {
            $sql .= " AND g.usuario_id = " . $user_id;
        }

        $sql .= " ORDER BY g.fecha DESC";

        $query = Executor::doit(sql: $sql);

        $array = array();
        $cnt = 0;
        while ($r = $query[0]->fetch_array()) {
            $array[$cnt] = new GastoData();
            $array[$cnt]->id = $r['id'];
            $array[$cnt]->descripcion = $r['descripcion'];
            $array[$cnt]->comprobante = $r['comprobante'];
            $array[$cnt]->importe = $r['importe'];
            $array[$cnt]->fecha = $r['fecha'];
            $array[$cnt]->usuario_id = $r['usuario_id'];
            $cnt++;
        }
        return $array;
    }

    public static function getById($id)
    {
        if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
            return null;
        }
        $id_val = intval($id);
        $sql = "SELECT * FROM " . self::$tablename . " WHERE id = $id_val LIMIT 1";
        $query = Executor::doit($sql);
        return Model::one($query[0], new GastoData());
    }

    public function add()
    {
        $sql = "INSERT INTO " . self::$tablename . " (descripcion, comprobante, importe, fecha, usuario_id) 
                VALUES ('" . $this->descripcion . "', '" . $this->comprobante . "', '" . $this->importe . "', '" . $this->fecha . "', '" . $this->usuario_id . "')";
        return Executor::doit($sql);
    }

    public function update()
    {
        $sql = "UPDATE " . self::$tablename . " SET 
                descripcion = '" . $this->descripcion . "', 
                comprobante = '" . $this->comprobante . "', 
                importe = '" . $this->importe . "',
                fechaupd = '" . $this->fecha . "',
                useridupd = '" . $this->usuario_id . "' 
                WHERE id = " . $this->id;
        return Executor::doit($sql);
        ;
    }

    public function delete()
    {
        $sql = "UPDATE " . self::$tablename . "  SET estado = 0, fechaupd = '" . $this->fecha . "', usuarioidupd = '" . $this->usuario_id . "' WHERE id = " . $this->id;
        return Executor::doit($sql);
    }

    public function getUser()
    {
        return UserData::getById($this->usuario_id);
    }
}
?>