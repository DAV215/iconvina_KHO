<?php include('thongke.php'); 
    include_once('QLNS/getdataUser.php');
?>

<?php 
    if ($_SESSION['admin'] || checkPerOfUser(17, $_SESSION['userINFO']['id'])){
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
    }elseif($_SESSION['admin'] ){
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
    }else{
        echo '<script>';
        echo 'alert("Không có quyền truy cập!");';
        echo 'window.location.href = "admin.php?job=QLTC&action=dexuatmua";';
        echo '</script>';
    }

?>
