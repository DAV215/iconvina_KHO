<?php 
    include('config_DB_KHO.php');
    class material{
        public $id;
        public $name;
        public $quantity;

        function __construct($id = null) {
            $this->id = $id;
        }
        function getALL_material(){
            include('config_DB_KHO.php');
            $sql = "SELECT * FROM `tbl_material`";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function get_material(){
            include('config_DB_KHO.php');
            $sql = "SELECT `id`,`name`, `quantity` FROM `tbl_material`";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_assoc($query)){
                $data[] = $row;
            }
            return $data;
        }
        function addNew($name, $quantity){
            // $material = array($name, $quantity);
            $material = array(
                'name'=>$name,
                'quantity'=>$quantity
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_material";
            $m->add($material);
        }
        function update($data, $where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_material";
            $m->update_($data,$where);
        }
        function remove($where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_material";
            $m->remove($where);
        }
    }
    class DB_driver_KHO_Material extends DB_driver{
        public $table_ ='';
        private $id;

        function add($data){
            return parent::insert($this->table_, $data);
        }
        function update_($data, $where){
            return parent::update($this->table_, $data, $where);
        }
        function remove($where){
            return parent::delete($this->table_, $where);
        }
    }
    class DB_driver{
        private $__conn;
        private $table;
        function connection(){
            if(!$this->__conn){
                $mysqli_kho = new mysqli("127.0.0.1:3307","root","","iconvina_kho");
                $this->__conn = $mysqli_kho;
            }
            mysqli_query($this->__conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
        }
        function insert($tb, $data){
            $this->connection();
        
            // Lưu trữ danh sách field
            $field_list = '';
            // Lưu trữ danh sách giá trị tương ứng với field
            $value_list = '';
        
            foreach ($data as $key => $value){
                $field_list .= "$key,";
                $value_list .= "'".mysqli_real_escape_string($this->__conn, $value)."',";
            }
        
            // Vì sau vòng lặp các biến $field_list và $value_list sẽ thừa một dấu , nên ta sẽ dùng hàm trim để xóa đi
            $sql = 'INSERT INTO '.$tb. '('.trim($field_list, ',').') VALUES ('.trim($value_list, ',').')';
            return mysqli_query($this->__conn, $sql);
        }
        function update($table, $data, $where){
            $this->connection();
            $sql = '';
            foreach($data as $key => $value){
                $sql .= " $key = '".mysqli_real_escape_string($this->__conn, $value)."', ";
            }
            //Xóa , ở vị trí cuối cùng của chuỗi SQL
            $sql = rtrim($sql, ', ');
            $sql = 'UPDATE '.$table. ' SET '.$sql.' WHERE '.$where;
            return mysqli_query($this->__conn, $sql);
        }
        function delete($table,$where){
            $this->connection();
            $sql = "DELETE FROM $table WHERE $where";
            return mysqli_query($this->__conn, $sql);
        }
    }

    class component{
        public $id;
        public $name;
        public $quantity;

        function __construct($id = null) {
            $this->id = $id;
        }
        function getALL_(){
            include('config_DB_KHO.php');
            $sql = "SELECT * FROM `tbl_component_CT`";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function addNew($name, $id_parent, $id_child, $level,  $quantity_ofChild, $name_parent){
            // $material = array($name, $quantity);
            $material = array(
                'id_parent'=>$id_parent,
                'id_child'=>$id_child,
                'level'=>$level,
                'quantity_ofChild'=>$quantity_ofChild,
                'name_parent'=>$name_parent,
                'name'=>$name
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_component_CT";
            $m->add($material);
        }
        function update($data, $where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_component_CT";
            $m->update_($data,$where);
        }
        function remove($where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_component_CT";
            $m->remove($where);
        }
        function get_component(){
            include('config_DB_KHO.php');
            $sql = "SELECT `name`, `id`,`level` FROM `tbl_component_CT` WHERE `id_parent` = 0 AND (`name_parent` IS NULL OR `name_parent` = 0);";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_assoc($query)){
                $data[] = $row;
            }
            return $data;
        }
        function get_oneRow_Onecomponent($type,$id){
            include('config_DB_KHO.php');
            $sql = "SELECT $type FROM `tbl_component_CT` WHERE `id` = $id";
            $query = mysqli_query($mysqli_kho, $sql);
            return mysqli_fetch_assoc($query);
        }
        function get_Newest_Component($name, $level){
            include('config_DB_KHO.php');
            $sql = "SELECT `id` FROM `tbl_component_CT` WHERE `id_parent` = 0 AND `level` = '$level' AND `name` = '$name'  ";
            $query = mysqli_query($mysqli_kho, $sql);
            if (!$query) {
                die('Error: ' . mysqli_error($mysqli_kho));
            }
            $row = mysqli_fetch_assoc($query);
            echo $sql;
            return $row['id'];
        }
        function getChild_ofParent($id_parent){
            include('config_DB_KHO.php');
            $sql = "SELECT * FROM `tbl_component_CT` WHERE `id_parent` = '$id_parent';";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_assoc($query)){
                $data[] = $row;
            }
            return $data;
        }
        
        function getChild_ofParent_FL_Level($id_parent,$level_ofChild){
            include('config_DB_KHO.php');
            $sql = "SELECT * FROM `tbl_component_CT` WHERE `id_parent` = '$id_parent' AND `level` = '$level_ofChild';";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function testDEQUY($id_parent, $indent = 0, $data = []){
            echo '</br>';
            $container_Parent  = $this->getChild_ofParent($id_parent);
            $componentName = $this->get_oneRow_Onecomponent('name',$id_parent)['name'].'</br>';
            $indentation = str_repeat('----', $indent*2);
            // echo $indentation . $componentName . '</br>';
            if($this->get_oneRow_Onecomponent('level',$id_parent)['level']>0){
                $level=[];
                foreach ($container_Parent as $row) {
                    $level[] = $row['level'];
                }
                $lv = max($level);
                for($i = 0; $i <= $lv; $i++){ // Concatenate additional '--' for each iteration
                    foreach ($this->getChild_ofParent_FL_Level($id_parent,$i) as $row) {
                        echo $indentation . $row['name'].'-- SL:'.$row['quantity_ofChild'] . '</br>';
                        if($row['level']>0){
                            for($i_ = 0; $i_ < $row['quantity_ofChild']; $i_++){
                                $data = $this->testDEQUY($row['id_child'],$indent + 1,$data);
                            }
                        }
                        else $data[] = array('name' => $row['name'],'quantity'=> $row['quantity_ofChild']);
                    }
                }
            }else{
                foreach ($container_Parent as $row) {
                    echo $row['name'].'</br>';
                    $data[] = array('name' => $row['name'],'quantity'=> $row['quantity_ofChild']);


                }
            }
            return $data;
        }
        
        function testDEQUY_2($id_parent, $data = []) {
            $container_Parent = $this->getChild_ofParent($id_parent);  
            if ($this->get_oneRow_Onecomponent('level', $id_parent)['level'] > 0) {
                $level = [];
                foreach ($container_Parent as $row) {
                    $level[] = $row['level'];
                }
                $lv = max($level);
                if ($lv > 0) {
                    echo "<ol>";
                }
                for ($i = 0; $i <= $lv; $i++) {
                    foreach ($this->getChild_ofParent_FL_Level($id_parent, $i) as $row) {
                        if ($row['level'] > 0) {
                            echo "<li>" . $row['name'] . '-- SL:' . $row['quantity_ofChild'] . "</li>";
                            echo "<ol>";
                            $data = $this->testDEQUY_2($row['id_child'], $data);
                            echo "</ol>";
                        } else {
                            $data[] = ['name' => $row['name'], 'quantity' => $row['quantity_ofChild']];
                            echo "<li>" . $row['name'] . '-- SL:' . $row['quantity_ofChild'] . "</li>";
                        }
                    }
                }
                if ($lv > 0) {
                    echo "</ol>";
                }
            }
            return $data;
        }
        
        function testDEQUY_thongke($id_parent, $indent = 0, $data = []){
            $container_Parent  = $this->getChild_ofParent($id_parent);
            if($this->get_oneRow_Onecomponent('level',$id_parent)['level']>0){
                $level=[];
                foreach ($container_Parent as $row) {
                    $level[] = $row['level'];
                }
                $lv = max($level);
                for($i = 0; $i <= $lv; $i++){ // Concatenate additional '--' for each iteration
                    foreach ($this->getChild_ofParent_FL_Level($id_parent,$i) as $row) {
                
                        if($row['level']>0){
                            for($i_ = 0; $i_ < $row['quantity_ofChild']; $i_++){
                                $data = $this->testDEQUY_thongke($row['id_child'],$indent + 1,$data);
                            }
                        }
                        else $data[] = array('name' => $row['name'],'quantity'=> $row['quantity_ofChild']);
                    }
                }
            }else{
                foreach ($container_Parent as $row) {
            
                    $data[] = array('name' => $row['name'],'quantity'=> $row['quantity_ofChild']);
                }
            }
            return $data;
        }
        function thongke_Vattu_Component($data){
            $result = [];
            foreach ($data as $item) {
                $name = $item['name'];
                $quantity = $item['quantity'];
        
                if (isset($result[$name])) {
                    $result[$name]['quantity'] += $quantity;
                } else {
                    $result[$name] = ['name' => $name, 'quantity' => $quantity];
                }
            }
        
            return array_values($result); 
        }
    }

    if (isset($_POST["type"])) {
        if($_POST["type"] == 'material'){
            $m = new material;
            echo json_encode($m->get_material());
        }
        else{
            $c = new component;
            echo json_encode($c->get_component());
        }

    }
?>