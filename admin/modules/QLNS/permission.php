<?php 
    include('../config/configDb.php');
    if(isset($_POST['ADD_job'])){
        include('quyen/thietlapCongviec/add.php');
    }elseif(isset($_GET['jobDelete'])){
        include('quyen/thietlapCongviec/delete.php');
    }
    if(isset($_POST['ADD_JobAction'])){
        include('quyen/thietlapAction/add.php');
    }elseif(isset($_GET['jobDel']) && isset($_GET['actionDel'])){
        include('quyen/thietlapAction/delete.php');
    }
?>

<?php 
    include('quyen/thietlapCongviec/lietke.php');
    include('quyen/thietlapAction/lietke.php');
?>
