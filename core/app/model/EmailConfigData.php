<?php
class EmailConfigData
{
    public static $tablename = "email_config";
    public $id;
    public $email;
    public $type;
    public $is_active;
    public $estado;
    public $created_at;

    public function __construct()
    {
        $this->email = "";
        $this->type = "to";
        $this->is_active = 1;
        $this->estado = 1;
        $this->created_at = date("Y-m-d H:i:s");
    }

    public function add()
    {
        $sql = "INSERT INTO " . self::$tablename . " (email, type, is_active, estado, created_at) 
                VALUES ('" . $this->email . "', '" . $this->type . "', " . $this->is_active . ", " . $this->estado . ", '" . $this->created_at . "')";
        return Executor::doit($sql);
    }

    public static function delById($id)
    {
        $sql = "UPDATE " . self::$tablename . " SET estado = 0 WHERE id = " . $id;
        return Executor::doit($sql);
    }

    public function del()
    {
        $sql = "UPDATE " . self::$tablename . " SET estado = 0 WHERE id = " . $this->id;
        return Executor::doit($sql);
    }

    public function update()
    {
        $sql = "UPDATE " . self::$tablename . " SET 
                email = '" . $this->email . "', 
                type = '" . $this->type . "', 
                is_active = " . $this->is_active . " 
                WHERE id = " . $this->id;
        return Executor::doit($sql);
    }

    public static function getById($id)
    {
        $sql = "SELECT * FROM " . self::$tablename . " WHERE id = $id AND estado = 1 LIMIT 1";
        $query = Executor::doit($sql);
        return Model::one($query[0], new EmailConfigData());
    }

    public static function getAll()
    {
        $sql = "SELECT * FROM " . self::$tablename . " WHERE estado = 1 ORDER BY created_at DESC";
        $query = Executor::doit($sql);
        return Model::many($query[0], new EmailConfigData());
    }

    public static function getActives()
    {
        $sql = "SELECT * FROM " . self::$tablename . " WHERE is_active = 1 AND estado = 1";
        $query = Executor::doit($sql);
        return Model::many($query[0], new EmailConfigData());
    }

    public static function getActiveByType($type)
    {
        $sql = "SELECT * FROM " . self::$tablename . " WHERE is_active = 1 AND estado = 1 AND type = '$type'";
        $query = Executor::doit($sql);
        return Model::many($query[0], new EmailConfigData());
    }
}
?>
