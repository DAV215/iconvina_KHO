
<?php 
    include('thongke.php');
?>
<?php 
    include('../config/configDb.php');
    if(isset($_POST['addProject'])){
        include('add.php');
    }elseif(isset($_GET['jobDelete'])){
        include('delete.php');
    }
?>


