<?php include('thongke.php'); ?>
<?php 
    include('../config/configDb.php');
    if(isset($_POST['addBuysuggest']) || isset($_POST['addDXM'])){
        include('add.php');
    }elseif(isset($_GET['delBuysuggest'])){
        include('delete.php');
    }
?>
