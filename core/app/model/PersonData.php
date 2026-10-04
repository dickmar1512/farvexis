<?php

#[AllowDynamicProperties]
class PersonData
{
	public static $tablename = "person";
	public $tipo_persona;
	public $numero_documento;
	public $name;
	public $lastname;
	public $email1;
	public $ubigeo;
	public $address1;
	public $phone1;
	public $created_at;
	public $company;
	public $password;
	public $status;
	public $image;
	public $kind;
	public $email;
	public $username;
	public $mail;
	public $id;

	public function PersonData()
	{
		$this->tipo_persona = "";
		$this->numero_documento = "";
		$this->name = "";
		$this->lastname = "";
		$this->email1 = "";
		$this->phone1 = "";
		$this->image = "";
		$this->company = "";
		$this->password = "";
		$this->created_at = "NOW()";
		$this->id = "";
		$this->ubigeo = "";
		$this->status = 1;
	}

	public function add_client()
	{
		$sql = "insert into person (tipo_persona, numero_documento, name, lastname, ubigeo,  address1, email1, phone1, kind, created_at, company) ";
		$sql .= "value ('" . $this->tipo_persona . "', '" . $this->numero_documento . "', '" . $this->name . "', '" . $this->lastname . "', '" . $this->ubigeo . "', '" . $this->address1 . "', '" . $this->email1 . "','" . $this->phone1 . "', '1', '" . $this->created_at . "', '" . $this->company . "')";
		return Executor::doit($sql);
	}

	public function add_provider()
	{
		$sql = "insert into person (tipo_persona, numero_documento, name, lastname, ubigeo, address1, email1, phone1, kind, created_at) ";
		$sql .= "value ('" . $this->tipo_persona . "', '" . $this->numero_documento . "', '" . $this->name . "', '" . $this->lastname . "', '" . $this->ubigeo . "', '" . $this->address1 . "','" . $this->email1 . "','" . $this->phone1 . "', 2, '" . $this->created_at . "')";
		Executor::doit($sql);
	}

	public static function delById($id)
	{
		$sql = "update " . self::$tablename . " set status=0 where id=$id";
		Executor::doit($sql);
	}
	public function del()
	{
		$sql = "update " . self::$tablename . " set status = " . $this->status . " where id=$this->id";
		Executor::doit($sql);
	}

	// partiendo de que ya tenemos creado un objecto PersonData previamente utilizamos el contexto
	public function update()
	{
		$sql = "update " . self::$tablename . " set name=\"$this->name\",email1=\"$this->email1\",address1=\"$this->address1\",lastname=\"$this->lastname\",phone1=\"$this->phone1\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_client()
	{
		$sql = "update " . self::$tablename . " set name=\"$this->name\",email1=\"$this->email1\",address1=\"$this->address1\",lastname=\"$this->lastname\",phone1=\"$this->phone1\",ubigeo=\"$this->ubigeo\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_provider()
	{
		$sql = "update " . self::$tablename . " set name=\"$this->name\",email1=\"$this->email1\",address1=\"$this->address1\",lastname=\"$this->lastname\",phone1=\"$this->phone1\",ubigeo=\"$this->ubigeo\" where id=$this->id";
		Executor::doit($sql);
	}

	public function update_passwd()
	{
		$sql = "update " . self::$tablename . " set password=\"$this->password\" where id=$this->id";
		Executor::doit($sql);
	}


	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from " . self::$tablename . " where id=$id_val";
		$query = Executor::doit($sql);
		$found = null;
		$data = new PersonData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->numero_documento = $r['numero_documento'];
			$data->name = $r['name'];
			$data->lastname = $r['lastname'];
			$data->address1 = $r['address1'];
			$data->phone1 = $r['phone1'];
			$data->email1 = $r['email1'];
			$data->tipo_persona = $r["tipo_persona"];
			$data->ubigeo = $r["ubigeo"];
			$data->created_at = $r['created_at'];
			//$data->company = $r['company'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function verificar_persona($numero_documento, $kind)
	{

		$sql = "SELECT id FROM person WHERE numero_documento = '" . $numero_documento . "' AND kind = $kind LIMIT 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PersonData());
	}

	public static function getAll()
	{
		$sql = "select * from " . self::$tablename;
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->numero_documento = $r['numero_documento'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->lastname = $r['lastname'];
			$array[$cnt]->email = $r['email1'];
			$array[$cnt]->username = $r['username'];
			$array[$cnt]->phone1 = $r['phone1'];
			$array[$cnt]->address1 = $r['address1'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}

	public static function getClients()
	{
		$sql = "select * from " . self::$tablename . " where kind=1 order by TRIM(CONCAT(lastname, ' ', name))";

		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->numero_documento = $r['numero_documento'];
			$array[$cnt]->lastname = $r['lastname'];
			$array[$cnt]->email1 = $r['email1'];
			$array[$cnt]->phone1 = $r['phone1'];
			$array[$cnt]->company = $r['company'];
			$array[$cnt]->address1 = $r['address1'];
			$array[$cnt]->ubigeo = $r['ubigeo'];
			$array[$cnt]->created_at = $r['created_at'];
			$array[$cnt]->status = $r['status'];
			$cnt++;
		}
		return $array;
	}


	public static function getProviders()
	{
		$sql = "select * from " . self::$tablename . " where kind=2 order by name,lastname";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->numero_documento = $r['numero_documento'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->lastname = $r['lastname'];
			$array[$cnt]->numero_documento = $r['numero_documento'];
			$array[$cnt]->email1 = $r['email1'];
			$array[$cnt]->phone1 = $r['phone1'];
			$array[$cnt]->address1 = $r['address1'];
			$array[$cnt]->ubigeo = $r['ubigeo'];
			$array[$cnt]->kind = $r['kind'];
			$array[$cnt]->created_at = $r['created_at'];
			$array[$cnt]->status = $r['status'];
			$cnt++;
		}
		return $array;
	}

	public static function getLike($q)
	{
		$sql = "select * from " . self::$tablename . " where name like '%$q%'";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->mail = $r['mail'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}

	public static function datosbyDocumento($numDocUsuario, $tipo)
	{
		$sql = "SELECT id, CONCAT(name,' ', lastname) as name, address1, ubigeo
				FROM person
				WHERE numero_documento = '" . $numDocUsuario . "' AND kind = 1 AND tipo_persona = $tipo LIMIT 1";
		$query = Executor::doit($sql);
		$found = null;
		$data = new PersonData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
			$data->name = $r['name'];
			$data->address1 = $r['address1'];
			$data->ubigeo = $r['ubigeo'];
			$found = $data;
			break;
		}

		return $found;
	}
}
?>