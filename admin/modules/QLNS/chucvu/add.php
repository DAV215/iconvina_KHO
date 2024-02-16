<?php 
if(isset($_POST['ADD_chucvu'])){
    $chucvu =  $_POST['ADD_chucvu'];
    $phong = $_POST['department'];
    $sql = "SELECT * FROM `tbl_chucvu` WHERE `department`='$phong' AND `chucvu` LIKE '$chucvu' ";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        echo '<script>alert("Đã có chức vụ này trong phòng ban !");</script>';
    }
    else{
        $sql = "INSERT INTO `tbl_chucvu`( `department`, `chucvu`) VALUES ('$phong','$chucvu') ";
        $result = $mysqli->query($sql);
        header('Location: admin.php?job=QLNS&action=department');
    }
}
?>