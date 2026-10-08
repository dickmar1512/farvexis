<?php
#[AllowDynamicProperties]
class UserData
{
	public $id;
	public $persona_id; // new
	public $name;
	public $lastname;
	public $username;
	public $email;
	public $montomax;
	public $is_admin;
	public $password;
	public $is_active;
	public $is_caja;
	public $is_dirtec;
	public $is_desc;
	public $image;
	public $created_at;
	public $sucursal_id;

	public function Userdata()
	{
		$this->name = "";
		$this->lastname = "";
		$this->email = "";
		$this->montomax = 0;
		$this->is_admin = 0;
		$this->is_caja = 0;
		$this->is_dirtec = 0;
		$this->is_active = 1;
		$this->is_desc = 0;
		$this->username = "";
		$this->image = "";
		$this->password = "";
		$this->sucursal_id = 1;
		$this->created_at = 'sysdate()';
	}

	public function add()
	{
		// 1. Insert into persona
		$sqlPersona = "INSERT INTO persona (nombres, apellido_paterno, email) ";
		$sqlPersona .= "VALUES ('{$this->name}', '{$this->lastname}', '{$this->email}')";
		$res = Executor::doit($sqlPersona);
		$persona_id = $res[1];

		// 2. Insert into user
		$sql = "INSERT INTO user (persona_id, username, is_admin, is_caja, is_dirtec, password, created_at, sucursal_id) ";
		$sql .= "VALUES ($persona_id, '{$this->username}', '{$this->is_admin}', '{$this->is_caja}', '{$this->is_dirtec}', '{$this->password}', {$this->created_at}, " . (int)($this->sucursal_id ?: 1) . ")";
		Executor::doit($sql);
	}

	public static function delById($id)
	{
		$sql = "UPDATE user SET is_active=0 WHERE id=$id";
		Executor::doit($sql);
	}
	public function del()
	{
		$sql = "UPDATE user SET is_active=0 WHERE id={$this->id}";
		Executor::doit($sql);
	}

	public function update()
	{
		// Update user
		$sql = "UPDATE user u JOIN persona p ON u.persona_id = p.id 
				SET p.nombres=\"{$this->name}\", p.email=\"{$this->email}\", u.montomax=\"{$this->montomax}\", u.username=\"{$this->username}\", 
				p.apellido_paterno=\"{$this->lastname}\", u.is_active=\"{$this->is_active}\", u.is_admin=\"{$this->is_admin}\", 
				u.is_caja=\"{$this->is_caja}\", u.is_dirtec=\"{$this->is_dirtec}\", u.is_desc=\"{$this->is_desc}\", u.sucursal_id=" . (int)($this->sucursal_id ?: 1) . " 
				WHERE u.id={$this->id}";
		Executor::doit($sql);
	}

	public function update_passwd()
	{
		$sql = "UPDATE user SET password=\"{$this->password}\" WHERE id={$this->id}";
		Executor::doit($sql);
	}


	public static function getById($id)
	{
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "SELECT u.*, p.nombres as name, p.apellido_paterno as lastname, p.email 
				FROM user u JOIN persona p ON u.persona_id = p.id 
				WHERE u.id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0], new UserData());
	}

	public static function getByMail($mail)
	{
		$sql = "SELECT u.*, p.nombres as name, p.apellido_paterno as lastname, p.email 
				FROM user u JOIN persona p ON u.persona_id = p.id 
				WHERE p.email=\"$mail\"";
		$query = Executor::doit($sql);
		return Model::one($query[0], new UserData());
	}

	public static function getAll()
	{
		$sql = "SELECT u.*, p.nombres as name, p.apellido_paterno as lastname, p.email 
				FROM user u JOIN persona p ON u.persona_id = p.id";
		$query = Executor::doit($sql);
		return Model::many($query[0], new UserData());
	}

	public static function getLike($q)
	{
		$sql = "SELECT u.*, p.nombres as name, p.apellido_paterno as lastname, p.email 
				FROM user u JOIN persona p ON u.persona_id = p.id 
				WHERE p.nombres LIKE '%$q%' OR p.apellido_paterno LIKE '%$q%'";
		$query = Executor::doit($sql);
		return Model::many($query[0], new UserData());
	}
}
?>