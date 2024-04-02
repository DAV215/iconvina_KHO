<?php 
    include('getdata_Kho.php');
    if($_GET['action'] == 'thongke_imp_exp'){
        include('thongke_imp_exp.php');
        include('thongke_exp.php');
        if(isset($_GET['actionChild'])){
            if($_GET['actionChild'] == 'imp_exp_detail'){
                include('imp_exp_detail.php');
            }
            if($_GET['actionChild'] == 'exp_detail'){
                include('exp_detail.php');
            }
        }
    }else{
        if(isset($_GET['actionChild'])){
            if($_GET['actionChild'] == 'addFILE_ADD'){
                include('thongke.php');
                include('add.php');
            }
            if($_GET['actionChild'] == 'import'){
                include('import.php');
            }
            if($_GET['actionChild'] == 'export'){
                include('export.php');
            }
            if($_GET['actionChild'] == 'setting'){
                
                include('setting_KHO.php');
            }elseif($_GET['actionChild'] == 'MaterialDetail'){
                include('thongke.php');
                include('Material_Detail.php');
            }elseif($_GET['actionChild'] == 'ComponentDetail'){
                include('thongke.php');
                include('Component_detail.php');
            }
        }else{
            include('thongke.php');
        }
    }

?>
<script src="../asset/js/export_excel.js"></script>
<script src="../asset/js/ex/src/jquery.table2excel.js"></script>
<script src="../asset/js/KHO/prod_cmd.js"></script>

<!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script> -->
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">