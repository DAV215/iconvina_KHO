<?php 
include(__DIR__ . '/../../QLKHO/code/getdata_Kho.php');
    if(isset($_GET['actionChild'])){
        include('thongke.php');
        if($_GET['actionChild'] == 'CMD_Detail'){
            include('Prod_CMD_detail.php');
        }
    }else{
        include('thongke.php');
    }
?>