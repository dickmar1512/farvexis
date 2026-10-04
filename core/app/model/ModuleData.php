<?php
class ModuleData {
	public static $tablename = "module";

    public $id;
    public $name;
    public $view_name;
    public $icon;
    public $parent_id;
    public $is_active;
    public $sort_order;

	public function ModuleData(){
		$this->name = "";
		$this->view_name = "";
		$this->icon = "";
		$this->parent_id = null;
		$this->is_active = 1;
		$this->sort_order = 0;
	}

	public function add(){
		$sql = "insert into ".self::$tablename." (name, view_name, icon, parent_id, is_active, sort_order) ";
		$sql .= "value (\"$this->name\",\"$this->view_name\",\"$this->icon\",".($this->parent_id?$this->parent_id:"NULL").",$this->is_active,$this->sort_order)";
		return Executor::doit($sql);
	}

	public static function getById($id){
		if ($id === null || $id === "" || $id === "NULL" || !is_numeric($id)) {
			return null;
		}
		$id_val = intval($id);
		$sql = "select * from ".self::$tablename." where id=$id_val";
		$query = Executor::doit($sql);
		return Model::one($query[0],new ModuleData());
	}

	public static function getAll(){
		$sql = "select * from ".self::$tablename." where is_active=1 order by sort_order asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ModuleData());
	}

    public static function getAllParents(){
		$sql = "select * from ".self::$tablename." where parent_id is NULL and is_active=1 order by sort_order asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ModuleData());
	}

    public static function getChildrenByParentId($id){
		$sql = "select * from ".self::$tablename." where parent_id=$id and is_active=1 order by sort_order asc";
		$query = Executor::doit($sql);
		return Model::many($query[0],new ModuleData());
	}
}
?>
