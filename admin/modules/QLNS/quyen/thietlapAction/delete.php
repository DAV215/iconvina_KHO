<?php 
if(isset($_GET['jobDel']) && isset($_GET['actionDel'])){
    $jobDelete =  $_GET['jobDel'];
    $actionDel =  $_GET['actionDel'];
    $sqlCheck = "SELECT * FROM `tbl_user_role` WHERE `job`='$jobDelete' AND `action`= '$actionDel'";
    $result = $mysqli->query($sqlCheck);
    if ($result->num_rows > 0) {
        echo '<script>';
        echo 'alert("Đã phân quyền cho người dùng Không xóa được!");';
        echo 'window.location.href = "admin.php?job=QLNS&action=permission";';
        echo '</script>';

    }
    else{
        $sqlDel = "DELETE FROM `tbl_jobaction` WHERE `job`='$jobDelete' AND `action`= '$actionDel'"  ;
        mysqli_query($mysqli, $sqlDel);
        echo '<script>';
        echo 'alert("Xóa thành công!");';
        echo 'window.location.href = "admin.php?job=QLNS&action=permission";';
        echo '</script>';

    }
}
?>