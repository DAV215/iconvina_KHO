<?php 
        include(__DIR__ . '/../../config/configDb.php');


    $sqlUserAll = "SELECT * FROM `tbl_user`";
    $queryALL = mysqli_query($mysqli, $sqlUserAll);

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
        $sql = "SELECT `username`, `fullname` FROM `tbl_admin` 
        UNION ALL
        SELECT `username`, `fullname` FROM `tbl_user` ;";
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
        $sql = "SELECT `supplier_name` FROM `tbl_buysuggest`";
        $query= mysqli_query($mysqli, $sql);
        $data = [];
        while ($row = mysqli_fetch_array($query)){
            $data[] = [
                'supplier_name' => $row['supplier_name'],
            ];
        }
        return $data;
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