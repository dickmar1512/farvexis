<?php
#[AllowDynamicProperties]
class PersonData
{
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
    public $persona_id; // new field to keep track

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
        // 1. Insert into persona
        $tipo_doc = 1;
        if(strlen($this->numero_documento) == 11) {
            $tipo_doc = 6;
        }
        $sqlPersona = "INSERT INTO persona (tipo_documento_id, numero_documento, nombres, apellido_paterno, apellido_materno, direccion, telefono, email, created_at) ";
        $sqlPersona .= "VALUES ($tipo_doc, '{$this->numero_documento}', '{$this->name}', '{$this->lastname}', '', '{$this->address1}', '{$this->phone1}', '{$this->email1}', '{$this->created_at}')";
        
        $res = Executor::doit($sqlPersona);
        $persona_id = $res[1];

        // 2. Insert into cliente
        $sqlCliente = "INSERT INTO cliente (persona_id, tipo_cliente, company, ubigeo, kind) ";
        $sqlCliente .= "VALUES ($persona_id, '{$this->tipo_persona}', '{$this->company}', '{$this->ubigeo}', 1)";
        
        return Executor::doit($sqlCliente);
	}

	public function add_provider()
	{
		// 1. Insert into persona
        $tipo_doc = 1;
        if(strlen($this->numero_documento) == 11) {
            $tipo_doc = 6;
        }
        $sqlPersona = "INSERT INTO persona (tipo_documento_id, numero_documento, nombres, apellido_paterno, apellido_materno, direccion, telefono, email, created_at) ";
        $sqlPersona .= "VALUES ($tipo_doc, '{$this->numero_documento}', '{$this->name}', '{$this->lastname}', '', '{$this->address1}', '{$this->phone1}', '{$this->email1}', '{$this->created_at}')";
        
        $res = Executor::doit($sqlPersona);
        $persona_id = $res[1];

        // 2. Insert into cliente
        $sqlCliente = "INSERT INTO cliente (persona_id, tipo_cliente, company, ubigeo, kind) ";
        $sqlCliente .= "VALUES ($persona_id, '{$this->tipo_persona}', '', '{$this->ubigeo}', 2)";
        
        return Executor::doit($sqlCliente);
	}

	public static function delById($id)
	{
		$sql = "UPDATE cliente c JOIN persona p ON c.persona_id = p.id SET c.status=0 WHERE c.id=$id";
		Executor::doit($sql);
	}
	public function del()
	{
        // status is missing in cliente? Actually let's assume we can just drop it or use kind status if it exists. 
        // Wait, I didn't add status to cliente table. Let's add status.
		$sql = "UPDATE cliente SET status = " . $this->status . " WHERE id=$this->id";
		Executor::doit($sql);
	}

	public function update()
	{
        // We need persona_id. Assuming we fetch it before update, or we update using JOIN.
		$sql = "UPDATE cliente c JOIN persona p ON c.persona_id = p.id 
                SET p.nombres=\"$this->name\", p.email=\"$this->email1\", p.direccion=\"$this->address1\", p.apellido_paterno=\"$this->lastname\", p.telefono=\"$this->phone1\" 
                WHERE c.id=$this->id";
		Executor::doit($sql);
	}

	public function update_client()
	{
		$sql = "UPDATE cliente c JOIN persona p ON c.persona_id = p.id 
                SET p.nombres=\"$this->name\", p.email=\"$this->email1\", p.direccion=\"$this->address1\", p.apellido_paterno=\"$this->lastname\", p.telefono=\"$this->phone1\", c.ubigeo=\"$this->ubigeo\" 
                WHERE c.id=$this->id";
		Executor::doit($sql);
	}

	public function update_provider()
	{
		$sql = "UPDATE cliente c JOIN persona p ON c.persona_id = p.id 
                SET p.nombres=\"$this->name\", p.email=\"$this->email1\", p.direccion=\"$this->address1\", p.apellido_paterno=\"$this->lastname\", p.telefono=\"$this->phone1\", c.ubigeo=\"$this->ubigeo\" 
                WHERE c.id=$this->id";
		Executor::doit($sql);
	}

	public function update_passwd()
	{
        // PersonData didn't really use password except maybe somewhere.
		// password=\"$this->password\" 
        // Not modifying for now.
	}


	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind 
                FROM cliente c JOIN persona p ON c.persona_id = p.id WHERE c.id=$id_val";
		$query = Executor::doit($sql);
		$found = null;
		$data = new PersonData();
		while ($r = $query[0]->fetch_array()) {
			$data->id = $r['id'];
            $data->persona_id = $r['persona_id'];
			$data->numero_documento = $r['numero_documento'];
			$data->name = $r['name'];
			$data->lastname = $r['lastname'];
			$data->address1 = $r['address1'];
			$data->phone1 = $r['phone1'];
			$data->email1 = $r['email1'];
			$data->tipo_persona = $r["tipo_persona"];
			$data->ubigeo = $r["ubigeo"];
			$data->created_at = $r['created_at'];
			$data->company = $r['company'];
            $data->kind = $r['kind'];
			$found = $data;
			break;
		}
		return $found;
	}

	public static function verificar_persona($numero_documento, $kind)
	{
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind 
                FROM cliente c JOIN persona p ON c.persona_id = p.id 
                WHERE p.numero_documento = '" . $numero_documento . "' AND c.kind = $kind LIMIT 1";
		$query = Executor::doit($sql);
		return Model::one($query[0], new PersonData());
	}

	public static function getAll()
	{
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind 
                FROM cliente c JOIN persona p ON c.persona_id = p.id";
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
			$array[$cnt]->phone1 = $r['phone1'];
			$array[$cnt]->address1 = $r['address1'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}

	public static function getClients()
	{
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind, 1 as status 
                FROM cliente c JOIN persona p ON c.persona_id = p.id WHERE c.kind=1 ORDER BY TRIM(CONCAT(p.apellido_paterno, ' ', p.nombres))";

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
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind, 1 as status 
                FROM cliente c JOIN persona p ON c.persona_id = p.id WHERE c.kind=2 ORDER BY p.nombres, p.apellido_paterno";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->numero_documento = $r['numero_documento'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->lastname = $r['lastname'];
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
		$sql = "SELECT c.id, c.persona_id, p.numero_documento, p.nombres as name, p.apellido_paterno as lastname, p.direccion as address1, p.telefono as phone1, p.email as email1, c.tipo_cliente as tipo_persona, c.ubigeo, p.created_at, c.company, c.kind 
                FROM cliente c JOIN persona p ON c.persona_id = p.id WHERE p.nombres LIKE '%$q%' OR p.apellido_paterno LIKE '%$q%'";
		$query = Executor::doit($sql);
		$array = array();
		$cnt = 0;
		while ($r = $query[0]->fetch_array()) {
			$array[$cnt] = new PersonData();
			$array[$cnt]->id = $r['id'];
			$array[$cnt]->name = $r['name'];
			$array[$cnt]->mail = $r['email1'];
			$array[$cnt]->created_at = $r['created_at'];
			$cnt++;
		}
		return $array;
	}

	public static function datosbyDocumento($numDocUsuario, $tipo)
	{
		$sql = "SELECT c.id, CONCAT(p.nombres,' ', p.apellido_paterno) as name, p.direccion as address1, c.ubigeo
				FROM cliente c JOIN persona p ON c.persona_id = p.id
				WHERE p.numero_documento = '" . $numDocUsuario . "' AND c.kind = 1 AND c.tipo_cliente = $tipo LIMIT 1";
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