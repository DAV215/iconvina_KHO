<?php 
    include('../config/configDb.php');
    if(isset($_POST['ADD_job'])){
        include('quyen/thietlapquyen/add.php');
    }elseif(isset($_GET['jobDelete'])){
        include('quyen/thietlapquyen/delete.php');
    }
?>

<?php 
    include('quyen/thietlapquyen/lietke.php');
?>
