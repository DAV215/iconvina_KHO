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
        public static function getMaterial_WHERE($WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getALL_WHERE('`tbl_material`','*',  $WHERE);
        }
        public static function join_With_Info($moreSQL){
            $db = new DB_driver_KHO_Material;
            $sql = "SELECT  `tbl_material`.* , tbl_info_material.* 
            FROM tbl_material LEFT JOIN tbl_info_material ON tbl_material.id = tbl_info_material.id_item ".$moreSQL;
            return $db->overview($sql);
        }
        public static function get_1row_Material($where){
            include('config_DB_KHO.php');
            $sql = "SELECT `id`,`name`, `quantity` FROM `tbl_material`";
            $sql .= $where;
            $query = mysqli_query($mysqli_kho, $sql);
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
            }
            return $data;
        }
        public static function get_info_Material($id){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_material`','*',"  `id` =  ".$id);
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
        function update($name, $quantity, $where){
            $material = array(
                'name'=>$name,
                'quantity'=>$quantity
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_material";
            $m->update_($material,$where);
        }
        public static function update_quantity( $quantity_more, $id){
            include('config_DB_KHO.php');
            $sql = "UPDATE `tbl_material` SET `quantity` =  `quantity` + $quantity_more WHERE `id` = $id ";
            $query = mysqli_query($mysqli_kho, $sql);
            if($query) return 1;
        }
        function remove($where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_material";
            $m->remove($where);
        }
    }
    class info_Material {
        public $id;
        public $name;
        public $quantity;

        public  static $baseDirectory =  __DIR__.'/..'.'//MEDIA//material/';

        function __construct($id = null) {
            $this->id = $id;
        }
        function addNew($id_item, $position, $code, $link_folder, $note){
            $Info_material = array(
                'id_item'=>$id_item,
                'position'=>$position,
                'code'=>$code,
                'link_folder'=>$link_folder,
                'note'=>$note
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_material";
            $m->add($Info_material);
        }
        function update($position, $code, $note, $link_folder,$where){
            $Info_material = array(
                'position'=>$position,
                'code'=>$code,
                'note'=>$note,
                'link_folder'=>$link_folder,
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_material";
            $m->update($m->table_, $Info_material, $where);
        }
        public static function get_info_Material($id){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_info_material`','*',"  `id_item` =  ".$id);
        }
        public static function createFolder($folderName) {
            $newFolderPath =self::$baseDirectory . $folderName;
            // chmod($newFolderPath,0777);
            if (!is_dir($newFolderPath)) {
                mkdir($newFolderPath, 0777, true);
            }
            return $newFolderPath;
        }
        public static function upload_Files($folderName, $_POST_name_Files){
            // Specify the target directory for file upload
            $targetDirectory = info_Material::createFolder($folderName);
        
            if (isset($_FILES[$_POST_name_Files]) && is_array($_FILES[$_POST_name_Files]['tmp_name'])) {
                $uploadedFiles = count($_FILES[$_POST_name_Files]['tmp_name']);
            
                for ($i = 0; $i < $uploadedFiles; $i++) {
                    // Get the temporary file name
                    $tempFileName = $_FILES[$_POST_name_Files]['tmp_name'][$i];
                    // Extract the original file name
                    $originalFileName = basename($_FILES[$_POST_name_Files]['name'][$i]);
                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = time() . '_' . $originalFileName;
                    // Specify the full path for the uploaded file
                    $targetFilePath = $targetDirectory . '/'.$uniqueFileName ;
                    move_uploaded_file($tempFileName, $targetFilePath);
                }
            }
            return $targetDirectory;
        }
        public static function modify_FILE($folderName, $_POST_name_Files){
            $targetDirectory =info_Material::createFolder($folderName);
            if (isset($_FILES[$_POST_name_Files]) && is_array($_FILES[$_POST_name_Files]['tmp_name'])) {
                $uploadedFiles = count($_FILES[$_POST_name_Files]['tmp_name']);
            
                for ($i = 0; $i < $uploadedFiles; $i++) {
                    // Get the temporary file name
                    $tempFileName = $_FILES[$_POST_name_Files]['tmp_name'][$i];
                    // Extract the original file name
                    $originalFileName = basename($_FILES[$_POST_name_Files]['name'][$i]);
                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = time() . '_' . $originalFileName;
                    // Specify the full path for the uploaded file
                    $targetFilePath = $targetDirectory . '/'.$uniqueFileName ;
                    move_uploaded_file($tempFileName, $targetFilePath);
                }
            }
            return $targetDirectory;
        }
        public static function checkFile($path){
            if (file_exists($path)) {
                // Attempt to remove the file
                echo 1;
            } else {
                echo 0;
            }
            exit;
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
        function getAll($table){
            return parent::getALL($table);
        }
        function get_1row($table, $select, $where){
            return parent::get_1row($table, $select, $where);
        }
        function getALL_WHERE($table, $select, $where){
            return parent::getALL_WHERE($table, $select, $where);
        }
        function overview($sql){
            return parent::overview($sql);
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
            $sql = "DELETE FROM `$table` WHERE $where";
            // echo $sql;
            return mysqli_query($this->__conn, $sql);
        }
        function getALL($table){
            $this->connection();
            $sql = "SELECT * FROM ".$table;
            $query = mysqli_query($this-> __conn, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function getALL_WHERE($table,$sql_GET, $sql_WHERE){
            $this->connection();
            $sql = "SELECT " .$sql_GET." FROM ".$table . " WHERE " .$sql_WHERE;
            // echo $sql;
            $query = mysqli_query($this-> __conn, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function overview($sql){
            $this->connection();
            $query = mysqli_query($this-> __conn, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        function get_1row($table,$select,$where){
            $this->connection();
            $sql = "SELECT " . $select." FROM " . $table ." WHERE ". $where;
            $query = mysqli_query($this-> __conn, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
            }
            return $data;
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
        
        function getALL_Child($sql_WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getALL_WHERE('`tbl_component_CT`', '*', $sql_WHERE);
        }
        function getALL_Parent(){
            $db = new DB_driver_KHO_Material;
            return $db->getALL_WHERE('`tbl_component_CT`', '*', '`id_parent` = 0 AND (`name_parent`  = 0  || `name_parent` IS NULL) ORDER BY `id` DESC');
        }
         function get_1row($sql_WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getALL_WHERE('`tbl_component_CT`', '*',$sql_WHERE);
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
        function addNew_Component_Info($id_component, $code, $quantity, $link_folder, $note){
            // $material = array($name, $quantity);
            $component_info = array(
                'id_component'=>$id_component,
                'code'=>$code,
                'quantity'=>$quantity,
                'link_folder'=>$link_folder,
                'note'=>$note
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_component";
            $m->add($component_info);
        }
        function update($data, $where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_component_CT";
            $m->update_($data,$where);
        }
        function remove($where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_component_ct";
            $m->remove($where);
        }
        function get_component(){
            include('config_DB_KHO.php');
            $sql = "SELECT tbl_component_CT.*, tbl_info_component.quantity
            FROM tbl_component_CT
            JOIN tbl_info_component ON tbl_component_CT.id = tbl_info_component.id_component;
            ;";
            $query = mysqli_query($mysqli_kho, $sql);
            $data = [];
            while ($row = mysqli_fetch_assoc($query)){
                $data[] = $row;
            }
            return $data;
        }
        public static function Component_Join_Info($WHERE){
            $sql = "SELECT
            tbl_component_ct.id,
            tbl_component_ct.name,
            tbl_component_ct.level,
            tbl_info_component.*
            FROM
                tbl_component_ct
            LEFT JOIN tbl_info_component ON tbl_component_ct.id = tbl_info_component.id_component
            WHERE
                (tbl_component_ct.id_parent = 0 AND (tbl_component_ct.name_parent = 0 OR tbl_component_ct.name_parent IS NULL))
                " . $WHERE . " "; // Ensure space after WHERE clause
            // $sql .= "ORDER BY tbl_component_ct.id DESC"; 

            $db = new DB_driver_KHO_Material;
            return $db->overview($sql);
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
        $get_DMNL_Component = $this->getALL_Child(' `id_parent` = '.$id_parent.' ORDER BY `id` DESC');
            if(count($get_DMNL_Component)!=0){
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
        }
        function testDEQUY_22($id_parent, $data = []) {
            $get_DMNL_Component = $this->getALL_Child(' `id_parent` = '.$id_parent.' ORDER BY `id` DESC');
                if(count($get_DMNL_Component)!=0){
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
            }
            public function testDEQUY_5($id_parent, $data = []) {
                $get_DMNL_Component = $this->getALL_Child(' `id_parent` = '.$id_parent.' ORDER BY `id` DESC');
                if(count($get_DMNL_Component) != 0) {
                    $container_Parent = $this->getChild_ofParent($id_parent);  
                    if ($this->get_oneRow_Onecomponent('level', $id_parent)['level'] > 0) {
                        $level = [];
                        foreach ($container_Parent as $row) {
                            $level[] = $row['level'];
                        }
                        $lv = max($level);
                        foreach ($this->getChild_ofParent_FL_Level($id_parent, $lv) as $row) {
                            $item = [
                                'id' => $row['id_child'],
                                'name' => $row['name'],
                                'post' => '', // Assuming post information is not available in the current context
                                'phone' => '', // Assuming phone information is not available in the current context
                                'mail' => '', // Assuming mail information is not available in the current context
                                'photo' => '', // Assuming photo information is not available in the current context
                            ];
                            if ($row['level'] > 0) {
                                $children = $this->testDEQUY_5($row['id_child']);
                                if (!empty($children)) {
                                    $item['children'] = $children;
                                }
                            }
                            $data[] = $item;
                        }
                    }
                }
                return $data;
            }
            
            
        
        
        
        
        
        function testDEQUY_thongke($id_parent, $indent = 0, $data = []){
            $get_DMNL_Component = $this->getALL_Child(' `id_parent` = '.$id_parent.' ORDER BY `id` DESC');
            if(count($get_DMNL_Component)!=0){
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
                            else $data[] = array('id'=>$row['id_child'],'name' => $row['name'],'quantity'=> $row['quantity_ofChild']);
                        }
                    }
                }else{
                    foreach ($container_Parent as $row) {
                
                        $data[] = array('id'=>$row['id_child'],'name' => $row['name'],'code' => $row['code'],'quantity'=> $row['quantity_ofChild']);
                    }
                }
                return $data;
            }
        }
        function thongke_Vattu_Component($data){
            $result = [];
            foreach ($data as $item) {
                $id = $item['id'];
                $name = $item['name'];
                $code = isset(info_Material::get_info_Material($id)['code'])?info_Material::get_info_Material($id)['code']:0;
                // $code = $item['code'];
                $quantity = $item['quantity'];
        
                if (isset($result[$name])) {
                    $result[$name]['quantity'] += $quantity;
                } else {
                    $result[$name] = ['id'=>$id,'name' => $name, 'code' => $code, 'quantity' => $quantity];
                }
            }
        
            return array_values($result); 
        }
    }
    class info_Component {
        public $id;
        public $name;
        public $quantity;
        public  static $baseDirectory =  __DIR__.'/..'.'/MEDIA/component/';

        function __construct($id = null) {
            $this->id = $id;
        }
        function addNew($id_component, $position, $quantity ,$code, $link_folder, $note){
            $Info_material = array(
                'id_component'=>$id_component,
                'position'=>$position,
                'quantity'=>$quantity,
                'code'=>$code,
                'link_folder'=>$link_folder,
                'note'=>$note
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_component";
            $m->add($Info_material);
        }
        function update($position, $code, $note, $link_folder,$where){
            $Info_material = array(
                'position'=>$position,
                'code'=>$code,
                'note'=>$note,
                'link_folder'=>$link_folder,
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_component";
            $m->update($m->table_, $Info_material, $where);
        }
        function remove($where){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_info_material";
            $m->remove($where);
        }
        public static function get_info_Component($id){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_info_component`','*',"  `id_component` =  ".$id);
        }
        
        public static function createFolder($folderName) {
            $newFolderPath =self::$baseDirectory . $folderName;
            if (!is_dir($newFolderPath)) {
                mkdir($newFolderPath, 0777);
            }
            return $newFolderPath;
        }
        public static function upload_Files($folderName, $_POST_name_Files){
            // Specify the target directory for file upload
            $targetDirectory = info_Component::createFolder($folderName);
        
            if (isset($_FILES[$_POST_name_Files]) && is_array($_FILES[$_POST_name_Files]['tmp_name'])) {
                $uploadedFiles = count($_FILES[$_POST_name_Files]['tmp_name']);
            
                for ($i = 0; $i < $uploadedFiles; $i++) {
                    // Get the temporary file name
                    $tempFileName = $_FILES[$_POST_name_Files]['tmp_name'][$i];
                    // Extract the original file name
                    $originalFileName = basename($_FILES[$_POST_name_Files]['name'][$i]);
                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = time() . '_' . $originalFileName;
                    // Specify the full path for the uploaded file
                    $targetFilePath = $targetDirectory . '/'.$uniqueFileName ;
                    move_uploaded_file($tempFileName, $targetFilePath);
                }
            }
            return $targetDirectory;
        }
        public static function modify_FILE($folderName, $_POST_name_Files){
            // Specify the target directory for file upload
            $targetDirectory = info_Component::createFolder($folderName);
            if (isset($_FILES[$_POST_name_Files]) && is_array($_FILES[$_POST_name_Files]['tmp_name'])) {
                $uploadedFiles = count($_FILES[$_POST_name_Files]['tmp_name']);
            
                for ($i = 0; $i < $uploadedFiles; $i++) {
                    // Get the temporary file name
                    $tempFileName = $_FILES[$_POST_name_Files]['tmp_name'][$i];
                    // Extract the original file name
                    $originalFileName = basename($_FILES[$_POST_name_Files]['name'][$i]);
                    // Generate a unique filename to prevent overwriting existing files
                    $uniqueFileName = time() . '_' . $originalFileName;
                    // Specify the full path for the uploaded file
                    $targetFilePath = $targetDirectory . '/'.$uniqueFileName ;
                    move_uploaded_file($tempFileName, $targetFilePath);
                }
            }
            return $targetDirectory;
        }
    
    }
    class ftp_server{
        private $var;
        public $ftp;
         public function __construct( $var = null) {
            $this->var = $var;
        }

        public static function ftp_Config(){
            $ftp_server = "aquabolo.vn";
            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            ftp_login($ftp_conn, 'iconvina@aquabolo.vn', 'anhvu21052001');
            ftp_pasv($ftp_conn, true);
            return $ftp_conn;
        }
        public static function ftp_createFolder($path){
            $ftp_conn = ftp_server::ftp_Config();
            ftp_mkdir($ftp_conn, $path);
        }
        public static function ftp_Set_permission($permission, $path){
            $v = ftp_chmod(ftp_server::ftp_Config(), $permission, $path);
            return $v;
        }
        public static function ftp_Get_root(){
            // $v = ftp_chdir(ftp_server::ftp_Config(), '~');
            $v = ftp_pwd(ftp_server::ftp_Config());
            return $v;
        }
        
        public static function put_file_FR_folder($localPath, $remotePath) {
            $remotePath .= '/'.$localPath;
            $error = "";
            try {
                $absoluteLocalPath = __DIR__ . '/' . $localPath;
                ftp_put(ftp_server::ftp_Config(), $remotePath, $absoluteLocalPath, FTP_BINARY); 
            } catch (Exception $e) {
                if ($e->getCode() == 2) $error = $e->getMessage(); 
            }
            return $error;
        }
    }

    class Position{
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_position`',$GET,  $WHERE);
        }
        public static function getAll_Where($WHERE){
            return self::getAll('*', $WHERE);
        }
        public static function addNew($array){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_position";
            $m->add($array);
        }
        function update($sum,$where){
            $InfoSUM = array(
                'sum'=>$sum
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_position";
            $m->update($m->table_, $InfoSUM, $where);
        }
        public static function updateSUM(){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_position";
            foreach( self::getAll('*', '  1 ') as $row){
                $sum = $row['storage'].' '.$row['row'].' '.$row['col'].' '.$row['shelf_level'];
                $InfoSUM = array(
                    'sum'=>$sum
                );
                $id = $row['id'];
                $m->update($m->table_, $InfoSUM, "  id = $id ");
            }
        }
        public static function get_1row($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_position`',$GET,  $WHERE);
        }
    }
    class Classify{
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_classify`',$GET,  $WHERE);
        }
        public static function getAll_Where($WHERE){
            return self::getAll('*', $WHERE);
        }
        public static function addNew($array){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_classify";
            $m->add($array);
        }
        function update($sum,$where){
            $InfoSUM = array(
                'sum'=>$sum
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_classify";
            $m->update($m->table_, $InfoSUM, $where);
        }
        public static function updateSUM(){
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_classify";
            foreach( self::getAll('*', '  1 ') as $row){
                $sum = $row['main_class'].' '.$row['sub_class'].' '.$row['note'];
                $InfoSUM = array(
                    'sum'=>$sum
                );
                $id = $row['id'];
                $m->update($m->table_, $InfoSUM, "  id = $id ");
            }
        }
        public static function get_1row($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_classify`',$GET,  $WHERE);
        }
    }
    class Super_detail{
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function addNew($type, $id_material, $id_component, $id_classify, $id_position, $id_business){
            $array = array(
                'type' => $type,
                'id_material' => $id_material,
                'id_component' => $id_component,
                'id_classify' => $id_classify,
                'id_position' => $id_position,
                'id_business' => $id_business
            );            
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_super_detail";
            $m->add($array);
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_super_detail`',$GET,  $WHERE);
        }
        public static function update($type, $id_material, $id_component, $id_classify, $id_position, $id_business, $where){
            $array = array(
                'type' => $type,
                'id_material' => $id_material,
                'id_component' => $id_component,
                'id_classify' => $id_classify,
                'id_position' => $id_position,
                'id_business' => $id_business
            );    
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_super_detail";
            $m->update($m->table_, $array, $where);
        }
    }
    class Business{
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function addNew($store, $price_buy, $delivery_fee, $discount, $vat){
            $array = array(
                'store' => $store,
                'price_buy' => $price_buy,
                'delivery_fee' => $delivery_fee,
                'discount' => $discount,
                'vat' => $vat
            );
                    
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_business";
            $m->add($array);
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_business`',$GET,  $WHERE);
        }
        public static function get_1row($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_business`',$GET,  $WHERE);
        }
        public static function update($store, $price_buy, $delivery_fee, $discount, $vat, $where){
            $array = array(
                'store' => $store,
                'price_buy' => $price_buy,
                'delivery_fee' => $delivery_fee,
                'discount' => $discount,
                'vat' => $vat
            );
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_business";
            $m->update($m->table_, $array, $where);
        }
    }
    class Record_KHO_SUPERDETAIL{
        
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function addNew($id_superDetail, $note, $addBy){
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $time  =  date("Y-m-d H:i:s");
            $array = array(
                'id_superDetail' => $id_superDetail,
                'note' => $note,
                'addBy' => $addBy,
                'time' => $time
            );
                    
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_lastupdate";
            $m->add($array);
        }
        public static function check_DIFF($old, $new, $feild){
            if($old != $new){
                return '<br>'. $feild ." -- Cũ: $old - Mới: $new".'<br>'; 
            }else return null;
        }
        public static function checkBus($store, $price_buy,$delivery_fee,$discount,$vat, $id_business){
            $old = Business::get_1row('*', " `id` = $id_business  ");
            $diff = '';
            $array = array(
                'store' => $store,
                'price_buy' => $price_buy,
                'delivery_fee' => $delivery_fee,
                'discount' => $discount,
                'vat' => $vat
            );
            // Compare each field with its corresponding old value
            foreach ($array as $key => $value) {
                // Remove the dollar sign ('$') from the key
                $field = trim($key, '$');
                if ($old[$field] !== $value) {
                    $diff .= '<br>'.'Cũ: '.$field.' = '.$old[$field] .'-Mới: '. $field.' = '.$value .'<br>';
                }
            }
            return $diff;
        }
        public static function checkCLassify($classify, $id_business){
            $old = Classify::get_1row('*', " `id` = $id_business  ")['sum'];
            return self::check_DIFF($old, $classify, 'Danh mục');
        }
        public static function checkPosition($Position, $id_position){
            $old = Position::get_1row('*', " `id` = $id_position  ")['sum'];
            return self::check_DIFF($old, $Position, 'Vị trí');
        }
        public static function checkQuantity_M($quantity, $id_material){
            $old = material::get_info_Material($id_material)['quantity'];
            return self::check_DIFF($old, $quantity, 'Số lượng');
        }        
        public static function checkQuantity_C($quantity, $id_material){
            $old = info_Component::get_info_Component($id_material)['quantity'];
            return self::check_DIFF($old, $quantity, 'Số lượng');
        }     
        public static function get_1row($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_lastupdate`',$GET,  $WHERE);
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_lastupdate`',$GET,  $WHERE);
        }
    }
    class import_material{
        
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function addNew($created_by,$name, $note){
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $date  =  date("Y-m-d H:i:s");
            $array = array(
                'created_by' => $created_by,
                'date' => $date,
                'note' => $note,
                'name' => $name
            );
                    
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_import";
            $m->add($array);
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->getAll_WHERE('`tbl_import`',$GET,  $WHERE);
        }
        public static function get_1row($GET, $WHERE){
            $db = new DB_driver_KHO_Material;
            return $db->get_1row('`tbl_import`',$GET,  $WHERE);
        }
    }
    class import_material_detail{
        private $var;
        public function __construct( $var = null) {
            $this->var = $var;
        }
        public static function addNew2($id_import, $id_material, $quantity, $import_price){
            $array = array(
                'id_import' => $id_import,
                'id_material' => $id_material,
                'quantity' => $quantity,
                'import_price' => $import_price
            );
                    
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_import_detail";
            $m->add($array);
        }
        public static function addNew( $array){          
            $m = new DB_driver_KHO_Material;
            $m->table_ = "tbl_import_detail";
            $m->add($array);
        }
    }
////////API REDORD:
// -- Material:
if(isset($_POST['Record_Material_Detail'])){
    $id = $_POST['id_MDetail_Record'];
    echo json_encode(Record_KHO_SUPERDETAIL::getAll('*', " id_SuperDetail = $id ORDER BY id DESC"));
}
////////API FOR POSITION:

if(isset($_POST['new_setting_Position'])){
    $result = Position::addNew($_POST['Form_new_setting_Position']);
}
if(isset($_POST['show_setting_Position'])){
    $page = $_POST['page_Position'];
    $offset = ($page - 1) * 10;
    $search = $_POST['search_Position'];
    if(isset($search) && $search!=null ){
        $getAllROW = Position::getAll(' * ', " (sum LIKE '%" . $search . "%'  )");
        $result = Position::getAll(' * ', " (sum LIKE '%" . $search . "%'  ) ORDER BY `id` DESC LIMIT $offset, 10 ");
        echo  json_encode(array('data' => $result, 'allRow' => count($getAllROW)));
    }else{
        echo  json_encode(array('data' => Position::getAll(' * ', '  1 ORDER BY `id` DESC LIMIT '.$offset.', 10'), 'allRow' => count(Position::getAll(' * ', '  1 ORDER BY `id` DESC '))));
    }

}
////////////////////////
////////API FOR Classify:

if(isset($_POST['new_setting_Classify'])){
    $result = Classify::addNew($_POST['Form_new_setting_Classify']);
}
if(isset($_POST['show_setting_Classify'])){
    $page = $_POST['page_Classify'];
    $offset = ($page - 1) * 10;
    $search = $_POST['search_Classify'];
    if(isset($search) && $search!=null ){
        $getAllROW = Classify::getAll(' * ', " (main_class LIKE '%" . $search . "%' OR sub_class LIKE '%" . $search . "%' OR note LIKE '%" . $search . "%' )");
        $result = Classify::getAll(' * ', " (main_class LIKE '%" . $search . "%' OR sub_class LIKE '%" . $search . "%' OR note LIKE '%" . $search . "%' ) ORDER BY `id` DESC LIMIT $offset, 10 ");
        echo  json_encode(array('data' => $result, 'allRow' => count($getAllROW)));
    }else{
        echo  json_encode(array('data' => Classify::getAll(' * ', '  1 ORDER BY `id` DESC LIMIT '.$offset.', 10'), 'allRow' => count(Classify::getAll(' * ', '  1 ORDER BY `id` DESC '))));
    }
}
////////////////////////

    if (isset($_POST["type"])) {
        if($_POST["type"] == 'material'){
            $m = new material;
            echo json_encode($m->get_material());
        }elseif($_POST["type"] == 'component' && isset($_POST["level"])){
            $level = $_POST["level"];
            $m = new component;
            echo json_encode(component::Component_Join_Info("AND  tbl_component_ct.level < '$level' " ));
        }
        else{
            $c = new component;
            echo json_encode($c->get_component());
        }

    }
    if (isset($_POST["path_Material_Img_DEL"])) {
        $path =info_Material::$baseDirectory.$_POST['path_Material_Img_DEL'];
        if (is_file(    $path)) {
            // Attempt to remove the file
            if (unlink($path)) {
                echo "File removed successfully.";
            } else {
                echo "Error: Unable to remove the file.";
            }
        } else {
            echo "Error: File does not exist." .$path;
        }
        exit;
    }
    if (isset($_POST["path_Component_Img_DEL"])) {
        $path = info_Component::$baseDirectory. $_POST['path_Component_Img_DEL'];

        if (is_file(    $path)) {
            // Attempt to remove the file
            if (unlink($path)) {
                echo "File removed successfully.";
            } else {
                echo "Error: Unable to remove the file.";
            }
        } else {
            echo "Error: File does not exist." .$path;
        }
        exit;
    }
    if (isset($_FILES['file_material_add'])) {
        $id_material = $_POST['id_material'];
        $name_material = $_POST['name_material'];
        if(info_Material::get_info_Material($id_material) == null){
            info_Material::upload_Files($id_material.'_'.$name_material, 'file_material_add');
        }else{
            $folder = $_POST['name_folder_material_modify'];
            $result = info_Material::modify_FILE($folder, 'file_material_add');
    
            if ($result !== false) {
                echo "Files uploaded successfully. Folder name: $result";
            } else {
                echo "Error: Failed to upload files.";
            }
        }
    }
    if (isset($_FILES['file_component_add'])) {
        $id_component = $_POST['id_component'];
        $name_component = $_POST['name_component'];
        if(info_Component::get_info_Component($id_component) == null){
            info_component::upload_Files($id_component.'_'.$name_component, 'file_component_add');
        }else{
            $folder = $_POST['name_folder_component_modify'];
            $result = info_component::modify_FILE($folder, 'file_component_add');
    
            if ($result !== false) {
                echo "Files uploaded successfully. Folder name: $result";
            } else {
                echo "Error: Failed to upload files.";
            }
        }
    }
    if(isset($_POST['action_AJAX'])){
        if($_POST['action_AJAX'] == 'del_Material'){
            $id_parent = $_POST['id_parent'];
            $id_child = $_POST['id_material'];
            $c = new component;
            return $c->remove(' `id_parent` = '.$id_parent.' AND `id_child` = '.$id_child.' ');
        }
        if($_POST['action_AJAX'] == 'del_Component'){
            $id_parent = $_POST['id_parent'];
            $id_component = $_POST['id_component'];
            $c = new component;
            return $c->remove(' `id_parent` = '.$id_parent.' AND `id_child` = '.$id_component.' ');
        }
    }
    if(isset($_POST['thongke'])){
        if($_POST['thongke']  == 'material'){
            if(isset($_POST['search']) && $_POST['search']!=null ){
                $page = $_POST['page'];
                $start = ($page-1)*10;
                $allPage = count(material::join_With_Info(" WHERE (name LIKE '%" . $_POST['search'] . "%' OR code LIKE '%" . $_POST['search'] . "%') "));
                echo json_encode( array(
                    'data' => material::join_With_Info(" WHERE (name LIKE '%" . $_POST['search'] . "%' OR code LIKE '%" . $_POST['search'] . "%') LIMIT ".$start." , 10"),
                    'allPage' => $allPage
                ));
            }else{
                $page = $_POST['page'];
                $start = ($page-1)*10;
                $allPage = count(material::join_With_Info(' ' ));
                echo json_encode( array(
                    'data' => material::join_With_Info(' LIMIT '.$start.' , 10 ' ),
                    'allPage' => $allPage
                ));
            }
        }
        if($_POST['thongke']  == 'Component'){
            if(isset($_POST['search_Component']) && $_POST['search_Component']!=null ){
                $page = $_POST['page_Component'];
                $start = ($page-1)*5;
                $allPage = count(component::Component_Join_Info("AND   (name LIKE '%" . $_POST['search_Component'] . "%' OR code LIKE '%" . $_POST['search_Component'] . "%') "));
                echo json_encode( array(
                    'data' => component::Component_Join_Info("AND  (name LIKE '%" . $_POST['search_Component'] . "%' OR code LIKE '%" . $_POST['search_Component'] . "%') LIMIT ".$start." , 5"),
                    'allPage' => $allPage
                ));
            }else{
                $page = $_POST['page_Component'];
                $start = ($page-1)*5;
                $allPage = count(component::Component_Join_Info(' ' ));
                echo json_encode( array(
                    'data' => component::Component_Join_Info(' LIMIT '.$start.' , 5 ' ),
                    'allPage' => $allPage
                ));
            }
        }
    }
//////API IMPORT EXPORT
if(isset($_POST['import'])){
    if($_POST['add_NEW_PRODS']){
        $m =  new material;
        $name = $_POST['form_data']['name'];
        $quantity = $_POST['form_data']['quantity'];
        $m->addNew($name, $quantity);
        $id_newest = $m->getMaterial_WHERE(" `name` =  '$name' AND quantity = $quantity")[0]['id'];
        echo json_encode($id_newest);

    }
}

// Function to export data from MySQL table to Excel
require $_SERVER['DOCUMENT_ROOT']. '/ICONVINA_KHO/vendor/autoload.php'; // Include PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Function to export data to Excel
    function exportToExcel($data, $filename) {
        // Create a new PhpSpreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet
        ->setCellValue('A1', 'STT')
        ->setCellValue('B1', 'Tên')
        ->setCellValue('C1', 'Code')
        ->setCellValue('D1', 'Số lượng');
        // Set data to the worksheet
        $rowCount = 2;
        foreach ($data as $key => $value) {
            $sheet->setCellValue('A'.$rowCount, $rowCount-1); 
            $sheet->setCellValue('B'.$rowCount, $value['name']);
            $sheet->setCellValue('C'.$rowCount, $value['code']);
            $sheet->setCellValue('D'.$rowCount, $value['quantity']);
            // $sheet->setCellValue('E'.$rowCount, $value->nguoi);
            $rowCount++;
        }
        // ob_end_clean();
        $writer = new Xlsx($spreadsheet);
        $writer->setOffice2003Compatibility(true);
        $filename=time().".xlsx";
        return realpath($filename);
    }
    if(isset($_POST['TREEDATA'])){
        $id_parent = $_POST['id_parent'];
        $m =  new component;
        $data = $m->testDEQUY_5($id_parent);
        echo json_encode($data);
    }
?>