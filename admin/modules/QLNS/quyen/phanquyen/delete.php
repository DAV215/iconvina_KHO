<?php 
if(isset($_GET['jobDelete'])){
    $jobDelete =  $_GET['jobDelete'];
    $sqlCheck = "SELECT * FROM `tbl_role` WHERE `job`='$jobDelete'"  ;
    $result = $mysqli->query($sqlCheck);
    if ($result->num_rows > 0) {
        echo '<script>';
        echo 'alert("Quyền này đã thêm hành động không được xóa !");';
        echo 'window.location.href = "admin.php?job=QLNS&action=permission";';
        echo '</script>';

    }
    else{
        $sqlDel = "DELETE FROM `tbl_job` WHERE `job`='$jobDelete'"  ;
        mysqli_query($mysqli, $sqlDel);
        echo '<script>';
        echo 'alert("Xóa thành công!");';
        echo 'window.location.href = "admin.php?job=QLNS&action=permission";';
        echo '</script>';

    }
}
?>