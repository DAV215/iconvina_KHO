<?php 
    include(__DIR__ . '/../../QLKHO/code/getdata_Kho.php');

    class BaseDBEntity {
        protected $tbl;
    
        public function __construct($tbl) {
            $this->tbl = $tbl;
        }
    
        public function addNew($array) {
            $c = new DB_driver_KHO_Material;
            $c->table_ = $this->tbl;
            return $c->add($array);
        }
    
        public function update($Info_material, $where) {
            $c = new DB_driver_KHO_Material;
            $c->table_ = $this->tbl;
            $c->update($this->tbl, $Info_material, $where);
        }
    
        public function getAll($where) {
            $c = new DB_driver_KHO_Material;
            return $c->getALL_WHERE($this->tbl, '*', $where);
        }
    }
    
    class Client extends BaseDBEntity {
        public function __construct() {
            parent::__construct('tbl_client');
        }
    }
    
    class Order extends BaseDBEntity {
        public function __construct() {
            parent::__construct('tbl_order');
        }
    }
    
?>