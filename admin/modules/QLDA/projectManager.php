
<?php 
    include('thongke.php');
?>
<?php 
    if(isset($_POST['addProject'])){
        include('add.php');
    }elseif(isset($_GET['jobDelete'])){
        include('delete.php');
    }
?>


