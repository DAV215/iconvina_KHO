<link rel="stylesheet" href="../asset/css/admin/table.css">
<link rel="stylesheet" href="../asset/css/admin/formInfo.css">

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
            if($job=="QLNS"){
                if($action == 'them') include('QLNS/them.php');
                if($action == 'thongke') include('QLNS/thongke.php');
                if($action == 'personnel') include('QLNS/personnel.php'); 
                if($action == 'department') include('QLNS/department.php'); 
                if($action == 'permission') include('QLNS/permission.php'); 
            }
            if($job=="QLTC"){
                if($action == 'dexuatmua') include('QLDXM/buysuggest.php');
                if($action == 'phieuchi') include('QLNS/personnel.php'); 
                if($action == 'phieuthu') include('QLNS/department.php');  
            }
            if($job=="QLDA"){
                include('QLDA/projectManager.php');

            }
        ?>
    </div>
</div>
