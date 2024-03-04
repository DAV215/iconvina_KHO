<?php 
if(isset($_POST['ADD_job'])){
    $job =  $_POST['ADD_job'];
    $sql = "SELECT * FROM `tbl_job` WHERE `job` LIKE '$job' ";
    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        echo '<script>alert("Đã có công việc này !");</script>';
    }
    else{
        $sql = "INSERT INTO `tbl_job`( `job`) VALUES ('$job') ";
        $result = $mysqli->query($sql);
        echo "<meta http-equiv='refresh' content='0'>";

    }
}
?>