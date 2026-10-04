<?php

#[AllowDynamicProperties]
class PermisoData
{
	public static $tablename = "permiso";
	public $Emp_Ruc;
	public $Emp_RazonSocial;
	public $Emp_Descripcion;
	public $Emp_Direccion;
	public $Emp_Telefono;
	public $Emp_Celular;

	public function PermisoData()
	{
		$this->Per_IdPermiso = "";
		$this->Per_Nomnre = "";
		$this->Per_Key = "";
		$this->Per_Estado = "";
	}

	public static function get_permisos()
	{
		$sql = "select * from " . self::$tablename . "";
		$query = Executor::doit($sql);
		return Model::many($query[0], new PermisoData());
	}

	public static function get_permiso_x_key($Per_Key)
	{
		$sql = "SELECT Pee_Valor 
					FROM permiso_empresa pee
					INNER JOIN " . self::$tablename . " per ON per.Per_IdPermiso = pee.Per_IdPermiso					
					WHERE Per_Key = '" . $Per_Key . "' AND Emp_IdEmpresa=1 ";

		$query = Executor::doit($sql);
		return Model::one($query[0], new PermisoData());
	}

	public function update()
	{
		$sql = "UPDATE " . self::$tablename . " set Emp_Ruc='" . $this->Emp_Ruc . "', Emp_RazonSocial='" . $this->Emp_RazonSocial . "', Emp_Descripcion='" . $this->Emp_Descripcion . "', Emp_Direccion='" . $this->Emp_Direccion . "', Emp_Telefono='" . $this->Emp_Telefono . "', Emp_Celular='" . $this->Emp_Celular . "' WHERE Emp_IdEmpresa=1";
		Executor::doit($sql);
	}
}
?>