<?php 
    include('getdata_Kho.php');
    if(isset($_GET['actionChild'])){
        if($_GET['actionChild'] == 'addFILE_ADD'){
            include('thongke.php');
            include('add.php');
        }
        if($_GET['actionChild'] == 'import'){
            include('thongke.php');
            include('import.php');
        }
        if($_GET['actionChild'] == 'export'){
            include('thongke.php');
            include('export.php');
        }
        if($_GET['actionChild'] == 'setting'){
            include('setting.php');
        }elseif($_GET['actionChild'] == 'MaterialDetail'){
            include('thongke.php');
            include('Material_Detail.php');
        }elseif($_GET['actionChild'] == 'ComponentDetail'){
            include('thongke.php');
            include('Component_Detail.php');
        }
    }else{
        include('thongke.php');
    }
?>