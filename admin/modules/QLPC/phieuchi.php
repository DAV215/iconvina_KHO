<?php include('thongke.php'); 
    include_once('QLNS/getdataUser.php');
?>

<?php 
    include('../config/configDb.php');
    if (isset($_POST['addPhieuchi']) || (isset($_GET['actionChild'] ) && $_GET['actionChild']  == "addPhieuChi")) {
        include('add.php');
        exit;
    }
    elseif(isset($_GET['delPhieuchi'])){

        include('delete.php');
    }elseif(isset($_GET['actionChild'])){
        if($_GET['actionChild'] == "phieuchiDetail"){
            include_once('detail.php');
        exit;

        }
    }
?>
