<?php
class TipoComprobanteData {
    public static $tablename = "tipo_comprobante";

    public $codigo;
    public $nombre;
    public $estado;

    public function __construct() {
        $this->codigo = "";
        $this->nombre = "";
        $this->estado = 1;
    }

    public static function getAll() {
        $sql = "select * from ".self::$tablename." where estado = 1 order by codigo";
        $query = Executor::doit($sql);
        return Model::many($query[0], new TipoComprobanteData());
    }

    public static function getById($codigo) {
        $sql = "select * from ".self::$tablename." where codigo = '$codigo'";
        $query = Executor::doit($sql);
        return Model::one($query[0], new TipoComprobanteData());
    }
}
?>
