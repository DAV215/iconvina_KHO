
<?php 
if(isset($_POST['ADD_JobAction'])){
    if(empty($_POST["ADD_JobAction"])){
        echo '<script>';
        echo 'alert("Mời nhập lại tên Phòng !");';
        echo 'window.location.href = "admin.php?job=QLNS&action=department";';
        echo '</script>';
    }else{
        $job =  $_POST['job'];
        $action =  $_POST['ADD_JobAction'];
        $sql = "SELECT * FROM `tbl_jobaction` WHERE `job` = '$job' AND `action` LIKE '%$action%' ";
        $result = $mysqli->query($sql);
        if ($result->num_rows > 0) {
            echo '<script>alert("Đã có công việc này !");</script>';
        }
        else{
            $sql = "INSERT INTO `tbl_jobaction`( `job`,`action`) VALUES ('$job','$action') ";
            $result = $mysqli->query($sql);
            echo '<script>';
            echo 'alert("Thêm hành động thành công !");';
            echo 'window.location.href = "admin.php?job=QLNS&action=permission";';
            echo '</script>';
        }
    }
}
?>