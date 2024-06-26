<?php 
        include(__DIR__ . '/../../config/configDb.php');


    $sqlUserAll = "SELECT * FROM `tbl_user`";
    $queryALL = mysqli_query($mysqli, $sqlUserAll);
    class DB_driver_THUCHI extends DB_driver_THUCHI_root{
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
    class DB_driver_THUCHI_root{
        private $__conn;
        private $table;
        function connection(){
            if(!$this->__conn){
                $mysqli_kho = new mysqli("127.0.0.1:3307","root","","iconvina_thuchi");
                $this->__conn = $mysqli_kho;
            }
            mysqli_query($this->__conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
            $mysqli_kho->set_charset("utf8");
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
        function getALL_WHERE_assoc($table,$sql_GET, $sql_WHERE){
            $this->connection();
            $sql = "SELECT " .$sql_GET." FROM ".$table . " WHERE " .$sql_WHERE;
            // echo $sql;
            $query = mysqli_query($this-> __conn, $sql);
            // $data = [];
            while ($row = mysqli_fetch_assoc($query)){
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
    class user{
        private $id;

        function __construct($id = null) {
            $this->id = $id;
        }
        public static function getAll($GET, $WHERE){
            $db = new DB_driver_THUCHI_root;
            return $db->getALL_WHERE('`tbl_user`',$GET,  $WHERE);
        }
    }
    //////API user;
    
    //////
    function getDepartment(){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_department`";
        $query= mysqli_query($mysqli, $sql);
        $arrChucvu = [];
        while ($row = mysqli_fetch_array($query)){
            $arrChucvu[] = [
                'department' => $row['name'],
            ];
        }
        return $arrChucvu;
    }
    function getChucvu($department){
        include('../../config/configDb.php');
        $sql = "SELECT * FROM `tbl_chucvu` WHERE `department` = '$department'";
        $query = mysqli_query($mysqli, $sql);
        $arrChucvu = [];
        while ($row = mysqli_fetch_array($query)){
            $arrChucvu[] = [
                'chucvu' => $row['chucvu'],
            ];
        }
        return $arrChucvu;
    }
    
    if(isset($_POST['selectedDepartment'])){
        $selectedDepartment = $_POST['selectedDepartment'];
        $chucvuData = getChucvu($selectedDepartment);
    
        // You can echo or json_encode the result to send it back to JavaScript
        echo json_encode($chucvuData, JSON_UNESCAPED_UNICODE);
        // Alternatively, if you want to return HTML or another format, you can customize this part
    }
    function getUserdetail($id){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_user` WHERE `id` = $id";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data = $row;
        }
        return $data;
    }
    function getIDbyUNAME($username){
        include('../config/configDb.php');
        $sql = "SELECT `id` FROM `tbl_user` WHERE `username` = '$username'";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        $data  = mysqli_fetch_assoc($query);
        return $data;
    }
    function deleteUser($id){
            include('../config/configDb.php');
            $sqlDelUser = "DELETE FROM `tbl_user` WHERE `id` = $id";
            $queryDelUse = mysqli_query($mysqli, $sqlDelUser);
    }
    function getAllJob(){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_job` ";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'job' => $row['job'],
            ];
        }
        return $data;
    }
    function getActionofJob($job){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_jobaction` WHERE `job` = '$job' ORDER BY `action` ASC";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'id' => $row['id'],
                'action' => $row['action'],
            ];
        }
        return $data;
    }
    function getPermission($id){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_user_role` WHERE `id_user` = '$id' ";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'stt' => $row['stt'],
                'id_role' => $row['id_role'],
            ];
        }
        return $data;
    }
    function getAllPersonnel(){
        include('../config/configDb.php');
        $sql = "SELECT  `username`, `fullname` FROM `tbl_admin` 
        UNION ALL
        SELECT DISTINCT  `username`, `fullname` FROM `tbl_user` ;";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'username' => $row['username'],
                'fullname' => $row['fullname'],
            ];
        }
        return $data;
    }
    function getPersonnel($username){
        include('../config/configDb.php');
        $sql = "SELECT `username`, `fullname` FROM `tbl_admin` WHERE `username` = '$username'
        UNION ALL
        SELECT `username`, `fullname` FROM `tbl_user` WHERE `username` = '$username';";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data = $row;
        }
        return $data;
    }
    function getAllQuyTrinh(){
        include('../config/configDb.php');
        $sql = "SELECT `name` FROM `tbl_quytrinhmuahang`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
    
        return $data;
    }
    function getAllProject(){
        include('../config/configDb.php');
        $sql = "SELECT `name` FROM `tbl_project`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)) {
            $data[] = $row;
        }
    
        return $data;
    }
    function getAllSupplier(){
        include('../config/configDb.php');
        $sql = "SELECT DISTINCT  `supplier_name` FROM `tbl_buysuggest`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'supplier_name' => $row['supplier_name'],
            ];
        }
        return $data;
    }
        function getBuySuggestDetail($id){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_buysuggest` WHERE `id` = '$id'";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data = $row;
        }
        return $data;
    }
    function getBuySuggest_IMG($id){
        include('../config/configDb.php');
        $sql = "SELECT `link` FROM `tbl_imgbuysugest` WHERE `buysuggestCode` = '$id'";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'link' => $row['link'],
            ];
        }
        return $data;
    }
    function getPhieuChi_IMG($id){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_imgphieuchi` WHERE `codePhieuChi` = '$id'";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'link' => $row['link'],
            ];
        }
        return $data;
    }
    class getBuySuggest{
        private $id;

        function __construct($id = null) {
            $this->id = $id;
        }
        function getAll(){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_buysuggest`";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
            }
            return $data;
        }
        function get1Data($type){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_buysuggest`";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = [
                    $type => $row[$type],
                ];
            }
            return $data;
        }
        function getBuySuggestDetail($id){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_buysuggest` WHERE `id` = '$id'";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
            }
            return $data;
        }
        
        function getDXM_ofUSER( $id_buyer){
            $Permission = checkPerOfUser(16, $id_buyer) ? 1:16;
            include('../config/configDb.php');
            if($Permission == 16){
                $sql = "SELECT * FROM `tbl_buysuggest` ORDER BY `id` DESC";
            }else{
                $sql = "SELECT * FROM `tbl_buysuggest` WHERE `id_buyer` = '$id_buyer' ORDER BY `id` DESC";
            }
                $query= mysqli_query($mysqli, $sql);
                $data = [];
                while ($row = mysqli_fetch_array($query)){
                    $data[] = $row;
                }
                return $data;
        }
        function getNumberPage($id_buyer, $rowOFPage){
            $numberPage  = ceil(count($this->getDXM_ofUSER($id_buyer))/$rowOFPage);
            return $numberPage;
        }
        
        function getNumberPage2($id_buyer, $rowOFPage, $searchBuysuggest){
            $numberPage  = ceil(count($this->get_ALLDXM_ofUSER_follow_Search($id_buyer, $searchBuysuggest))/$rowOFPage);
            return $numberPage;
        }
         //xác định số lượng phần tử khớp với ID và tìm kiếm và phân trang
        function getDXM_ofUSER_followPAGE( $id_buyer, $rowOFPage, $page, $searchBuysuggest){
            if($searchBuysuggest != null){
                $sql_search =" AND"."
                (`nameDXM` LIKE '%$searchBuysuggest%' OR 
                `namebuyer` LIKE '%$searchBuysuggest%' OR 
                `daySuggest` LIKE '%$searchBuysuggest%' OR 
                `supplier_name` LIKE '%$searchBuysuggest%')";
            }else $sql_search = '';
            $number = ($page-1)*$rowOFPage;
            include('../config/configDb.php');
            if (checkPerOfUser(16, $id_buyer)) {
                $sql = "SELECT * FROM `tbl_buysuggest` WHERE 1" . $sql_search . " ORDER BY `id` DESC LIMIT $number, $rowOFPage";
            } else {
                $sql = "SELECT * FROM `tbl_buysuggest` WHERE `id_buyer` = '$id_buyer'" . $sql_search . " ORDER BY `id` DESC LIMIT $number, $rowOFPage";
            }
                $query= mysqli_query($mysqli, $sql);
          
                $data = [];
                while ($row = mysqli_fetch_array($query)){
                    $data[] = $row;
                }
                return $data;
        }
        //xác định số lượng phần tử khớp với ID và tìm kiếm
        function get_ALLDXM_ofUSER_follow_Search( $id_buyer,  $searchBuysuggest){
            if ($searchBuysuggest != null) {
                $sql_search = " AND (
                    `nameDXM` LIKE '%$searchBuysuggest%' OR 
                    `namebuyer` LIKE '%$searchBuysuggest%' OR 
                    `daySuggest` LIKE '%$searchBuysuggest%' OR 
                    `supplier_name` LIKE '%$searchBuysuggest%'
                )";
            } else {
                $sql_search = '';
            }
            
            include('../config/configDb.php');
            
            if (checkPerOfUser(16, $id_buyer)) {
                $sql = "SELECT * FROM `tbl_buysuggest` WHERE 1" . $sql_search . " ORDER BY `id` DESC ";
            } else {
                $sql = "SELECT * FROM `tbl_buysuggest` WHERE `id_buyer` = '$id_buyer'" . $sql_search . " ORDER BY `id` DESC ";
            }
            
            $query = mysqli_query($mysqli, $sql);
            
            $data = [];
            while ($row = mysqli_fetch_array($query)) {
                $data[] = $row;
            }
            
            return $data;
        }

        function getTotal($id_buyer,  $searchBuysuggest){
            $total = 0;
            foreach($this->get_ALLDXM_ofUSER_follow_Search($id_buyer,  $searchBuysuggest) as $row){
                $total += $row['money'];
            }
            return $total;
        }
    }
    class getPhieuChi{
      public  function __construct() {

        }
       public function getAll(){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_phieuchi`";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = $row;
            }
            return $data;
        }
        public   function get1Data($type){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_phieuchi`";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data[] = [
                    $type => $row[$type],
                ];
            }
            return $data;
        }
        function getPhieuChiDetail($id){
            include('../config/configDb.php');
            $sql = "SELECT * FROM `tbl_phieuchi` WHERE `id` = '$id'";
            $query= mysqli_query($mysqli, $sql);
            $data = [];
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
            }
            return $data;
        }
        function checkHavePC($id_BuySuggest){
            foreach ($this->getAll() as $row) {
                if ($id_BuySuggest == $row['id_buySuggest']) {
                    return 1;
                }
            }
            return 0;  // Return 0 only after checking all rows
        }
        function getPC_From_IdBuySuggest($id_BuySuggest){
            foreach ($this->getAll() as $row) {
                if ($id_BuySuggest == $row['id_buySuggest']) {
                    return $row;
                }
            }
            return null;
        }
        function getSTT_PC($Permission, $id_PC){
            include('../config/configDb.php');
            $PC = $this->getPhieuChiDetail($id_PC);
            if(!$PC['bool_AllApprove']){
                if($Permission == 'thuquy'){
                    if($PC['bool_approveBy_TQ']){
                        return 1;
                    } else return 0;
                }elseif($Permission == 'admin1'){
                    if($PC['bool_approveBy_ADMIN1']){
                        return 1;
                    } else return 0;
                }elseif($Permission == 'admin2'){
                    if($PC['bool_approveBy_ADMIN2']){
                        return 1;
                    } else return 0;
                }elseif($Permission == 'ketoan'){
                    if($PC['bool_approveBy_KT']){
                        return 1;
                    } else return 0;
                }else return 0;
            }else return 99;
        }
        function blockModify_PC($id_PC, $Permission){
            include('../config/configDb.php');
            $PC = $this->getPhieuChiDetail($id_PC);
            if( ($Permission == "admin1") || $Permission == "admin2"){
                return 0;
            }
            else{
                if(!($this->getSTT_PC($Permission,$id_PC) == 99)){
                    if($Permission == 'thuquy'){
                        if($PC['bool_approveBy_ADMIN1'] || $PC['bool_approveBy_ADMIN2'] || $PC['bool_approveBy_KT']){
                            return 1;
                        }else return 0;
                    }elseif($Permission == 'ketoan'){
                        if($PC['bool_approveBy_ADMIN1'] || $PC['bool_approveBy_ADMIN2'] ){
                            return 1;
                        }else return 0;
                    }
                    else return 0;
                }else return 1;
            }
        }
        function getPC_Phanquyen($Permission){
            include('../config/configDb.php');
            if($Permission != 'thuquy' ||  $Permission == 'admin1' || $Permission == 'admin2' || $Permission == 'ketoan'){

            }else{
                if($Permission == 'thuquy'){
                    $sql = "SELECT * FROM `tbl_phieuchi` WHERE `bool_approveBy_TQ` IS NULL ORDER BY `id` DESC ";
                }elseif($Permission == 'admin1'){
                    $sql = "SELECT * FROM `tbl_phieuchi` WHERE  `bool_approveBy_TQ`=1  AND `taikhoanchi`='Tiền Mặt' AND `bool_approveBy_ADMIN1`IS NULL ORDER BY `id` DESC";
                }elseif($Permission == 'admin2'){
                    $sql = "SELECT * FROM `tbl_phieuchi` WHERE  `bool_approveBy_TQ`=1 AND `taikhoanchi`='Ngân hàng cá nhân ' AND `bool_approveBy_ADMIN1`IS NULL ORDER BY `id` DESC";
                }elseif($Permission == 'ketoan'){
                    $sql = "SELECT * FROM `tbl_phieuchi` WHERE `bool_approveBy_TQ`=1 AND `taikhoanchi`='Ngân hàng công ty' AND `bool_approveBy_KT`IS NULL ORDER BY `id` DESC ";
                }
                $query= mysqli_query($mysqli, $sql);
                $data = [];
                while ($row = mysqli_fetch_array($query)){
                    $data[] = $row;
                }
                return $data;
            } 
        }
        function getTotal_ALL(){
            $total = 0;
            foreach ($this->getAll() as $row) {
                $total += $row['total'];
            }
            return $total;
        }
        function get_ALLPC_ofUSER_follow_Search( $searchPhieuchi){
            if ($searchPhieuchi != null) {
                $sql_search = " AND (
                    `name` LIKE '%$searchPhieuchi%' OR 
                    `nguoitaolenh` LIKE '%$searchPhieuchi%' OR 
                    `taikhoanchi` LIKE '%$searchPhieuchi%' OR 
                    `createDay` LIKE '%$searchPhieuchi%' OR 
                    `loaichi` LIKE '%$searchPhieuchi%'
                )";
                
            } else {
                $sql_search = '';
            }
            
            include('../config/configDb.php');
            
            $sql = "SELECT * FROM `tbl_phieuchi` WHERE 1" . $sql_search . " ORDER BY `id` DESC ";
            
            $query = mysqli_query($mysqli, $sql);
            
            $data = [];
            while ($row = mysqli_fetch_array($query)) {
                $data[] = $row;
            }
            
            return $data;
        }
        function getTotal($searchPhieuchi){
            $total = 0;
            foreach($this->get_ALLPC_ofUSER_follow_Search($searchPhieuchi) as $row){
                $total += $row['total'];
            }
            return $total;
        }
    }
    function getReceiptOfPC($loaichi, $id_phieuchi){
        include('../config/configDb.php');
        if($loaichi == "Chi mua hàng"){
            $sql = "SELECT * FROM `tbl_itemdetail_cmh` WHERE `id_phiechi` = '$id_phieuchi'";
        }elseif($loaichi == "Chi khác"){
            $sql = "SELECT * FROM `tbl_itemdetail_ckhac` WHERE `id_phiechi` = '$id_phieuchi'";
        }elseif($loaichi == "Chi tạm ứng"){
            $sql = "SELECT * FROM `tbl_itemdetail_tu` WHERE `id_phiechi` = '$id_phieuchi'";
        }elseif($loaichi == "Chi tạm ứng lương"){
            $sql = "SELECT * FROM `tbl_itemdetail_tul` WHERE `id_phiechi` = '$id_phieuchi'";
        }
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = $row;
        }
        return $data;
    }
    function resetReceiptPC($loaichi, $id_phieuchi){
        include('../config/configDb.php');
        if ($loaichi == "Chi mua hàng") {
            $sql = "DELETE FROM `tbl_itemdetail_cmh` WHERE `id_phiechi` = '$id_phieuchi'";
        } elseif ($loaichi == "Chi khác") {
            $sql = "DELETE FROM `tbl_itemdetail_ckhac` WHERE `id_phiechi` = '$id_phieuchi'";
        } elseif ($loaichi == "Chi tạm ứng") {
            $sql = "DELETE FROM `tbl_itemdetail_tu` WHERE `id_phiechi` = '$id_phieuchi'";
        } elseif ($loaichi == "Chi tạm ứng lương") {
            $sql = "DELETE FROM `tbl_itemdetail_tul` WHERE `id_phiechi` = '$id_phieuchi'";
        }
        mysqli_query($mysqli, $sql);
    }
    function resetReceiptPC_( $id_phieuchi){
        include('../config/configDb.php');
        $sql = "DELETE FROM `tbl_itemdetail_cmh` WHERE `id_phiechi` = '$id_phieuchi'";
        $sql = "DELETE FROM `tbl_itemdetail_ckhac` WHERE `id_phiechi` = '$id_phieuchi'";
        $sql = "DELETE FROM `tbl_itemdetail_tu` WHERE `id_phiechi` = '$id_phieuchi'";
        $sql = "DELETE FROM `tbl_itemdetail_tul` WHERE `id_phiechi` = '$id_phieuchi'";
        mysqli_query($mysqli, $sql);
    }
    function resetIMGPC($id_phieuchi){
        include('../config/configDb.php');
        $sql = "DELETE FROM `tbl_imgphieuchi` WHERE `codePhieuChi` = '$id_phieuchi'";
        mysqli_query($mysqli, $sql);
    }
    function getAllLoaiChi(){
        include('../config/configDb.php');
        $sql = "SELECT `name` FROM `tbl_loaichi`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
    
        return $data;
    }
    function getAllLoaiTaiKhoan(){
        include('../config/configDb.php');
        $sql = "SELECT `name` FROM `tbl_loaitaikhoan`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
    
        return $data;
    }
    function checkPerOfUser($id_role, $id_user){
        include('../config/configDb.php');
        $sql = "SELECT COUNT(*) AS count FROM `tbl_user_role` WHERE `id_user`='$id_user' AND `id_role`='$id_role'";
        $query = mysqli_query($mysqli, $sql);
        if (!$query) {
            // Handle the query error if needed
            echo "Error: " . mysqli_error($mysqli);
            return false;
        }
        $result = mysqli_fetch_assoc($query);
        $count = $result['count'];
        return $count;
    }
    function checkBuySuggestofUser($id_BuySuggest, $id_user){
        include('../config/configDb.php');
        $sql = "SELECT COUNT(*) AS count FROM `tbl_buysuggest` WHERE `id_buyer`='$id_user' AND `id`='$id_BuySuggest'";
        $query = mysqli_query($mysqli, $sql);
        if (!$query) {
            // Handle the query error if needed
            echo "Error: " . mysqli_error($mysqli);
            return false;
        }
        $result = mysqli_fetch_assoc($query);
        $count = $result['count'];
        return $count;
    }
    function check_PC_ofBuySuggest($id_BuySuggest){
        include('../config/configDb.php');
        $sql = "SELECT COUNT(*) AS count FROM `tbl_phieuchi` WHERE `id_buysuggest`='$id_BuySuggest'";
        $query = mysqli_query($mysqli, $sql);
        if (!$query) {
            // Handle the query error if needed
            echo "Error: " . mysqli_error($mysqli);
            return false;
        }
        $result = mysqli_fetch_assoc($query);
        $count = $result['count'];
        return $count;
    }
    function getPhanQuyenDuyet($username){
        include('../config/configDb.php');
        $sql = "SELECT * FROM `tbl_phanquyenduyet`";
        $query = mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[]= $row;
        }
        $i=0;
        foreach($data as $row){
            if($row['username']==$username){
                return $row['permission'];
                $i = 1;
            }
        }
        if(!$i) return null;
    }

    function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
    
            $ipaddress = 'UNKNOWN';
        }
    
        return $ipaddress;
    }

?>