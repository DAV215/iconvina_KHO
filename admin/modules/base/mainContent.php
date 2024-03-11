<link rel="stylesheet" href="../asset/css/admin/table.css">
<link rel="stylesheet" href="../asset/css/admin/formInfo.css">
<link rel="stylesheet" href="../asset/css/admin/modalForm.css">
<script src="../asset/js/modal.js"></script>
<div class="mainContent">
    <?php 
    include("../modules/base/menuTop.php");
    ?>
    <div class="app">
    <?php 

            if(isset($_GET['job']) && $_GET['action']){
                $job = $_GET['job'];
                $action = $_GET['action'];
                if(isset($_GET['id'])) $id = $_GET['id'];
            }
            else{
                $tam = '';
                $action = '';
            }
            if(isset($job)){
                if($job=="QLNS"){
                    if($action == 'them') include('QLNS/them.php');
                    if($action == 'thongke') include('QLNS/thongke.php');
                    if($action == 'personnel') include('QLNS/personnel.php'); 
                    if($action == 'department') include('QLNS/department.php'); 
                    if($action == 'permission') include('QLNS/permission.php'); 
                }
                if($job=="QLTC"){
                    if($action == 'dexuatmua') include('QLDXM/buysuggest.php');
                    if($action == 'phieuchi') include('QLPC/phieuchi.php'); 
                    if($action == 'phieuthu') include('QLNS/department.php');  
                    if($action == 'quytrinhxetduyet') include('QLQT/QuanlyQuyTrinh.php');  
                }
                if($job=="QLDA"){
                    include('QLDA/projectManager.php');
                }
                if($job=="DashBoard"){
                    if($action == 'listJob') include('Dashboard/listJob.php');  
                }
                if($job=="QLKHO"){
                    if($action == 'thongke') include('QLKHO/code/QLKHO.php');  
                }
            }else{
                include('QLDXM/buysuggest.php');
            }
        ?>
    </div>
</div>
