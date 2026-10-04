<?php

#[AllowDynamicProperties]
class SucursalData
{
    public static $tablename = 'sucursal';
    public $id;
    public $codigo;
    public $nombre;
    public $direccion;
    public $activo;

    public static function getAll(bool $soloActivas = false): array
    {
        $where = $soloActivas ? ' WHERE activo = 1' : '';
        $query = Executor::doit('SELECT * FROM sucursal' . $where . ' ORDER BY nombre');
        return Model::many($query[0], new SucursalData());
    }

    public static function getById($id): ?SucursalData
    {
        if (!is_numeric($id)) return null;
        $query = Executor::doit('SELECT * FROM sucursal WHERE id = ' . (int)$id);
        return Model::one($query[0], new SucursalData());
    }

    public function save(): void
    {
        $codigo = addslashes(str_pad(preg_replace('/[^0-9]/', '', (string)$this->codigo), 4, '0', STR_PAD_LEFT));
        $nombre = addslashes(trim((string)$this->nombre));
        $direccion = addslashes(trim((string)$this->direccion));
        $activo = (int)$this->activo;
        if ($this->id) {
            Executor::doit("UPDATE sucursal SET codigo='$codigo', nombre='$nombre', direccion='$direccion', activo=$activo WHERE id=" . (int)$this->id);
            return;
        }
        Executor::doit("INSERT INTO sucursal (codigo, nombre, direccion, activo) VALUES ('$codigo', '$nombre', '$direccion', $activo)");
    }
}
