<?php include('thongke.php'); ?>
<?php 
    include('../config/configDb.php');
    if(isset($_POST['addBuysuggest']) || isset($_POST['addDXM'])){
        
        include('add.php');
        exit;
    }elseif(isset($_GET['delBuysuggest'])){

        include('delete.php');
    }elseif(isset($_GET['actionChild'])){
        if($_GET['actionChild'] == "buysuggestDetail"){
            include_once('detail.php');
        exit;

        }
    }
?>
