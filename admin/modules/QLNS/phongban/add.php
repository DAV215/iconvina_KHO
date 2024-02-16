<?php 
if(isset($_POST['ADD_department'])){
    if(empty($_POST["ADD_department"])){
        echo '<script>';
        echo 'alert("Mời nhập lại tên Phòng !");';
        echo 'window.location.href = "admin.php?job=QLNS&action=department";';
        echo '</script>';
    }else{
        $phong =  $_POST['ADD_department'];
        $sql = "SELECT * FROM `tbl_department` WHERE `name`='$phong' ";
        $result = $mysqli->query($sql);
        if ($result->num_rows > 0) {
            echo '<script>';
            echo 'alert("Đã có phòng ban này!");';
            echo 'window.location.href = "admin.php?job=QLNS&action=department";';
            echo '</script>';
        }
        else{
            $sql = "INSERT INTO `tbl_department`( `name`) VALUE ('$phong') ";
            $result = $mysqli->query($sql);
            echo '<script>';
            echo 'alert("Thêm phòng ban thành công !");';
            echo 'window.location.href = "admin.php?job=QLNS&action=department";';
            echo '</script>';
        }
    }
}
?>