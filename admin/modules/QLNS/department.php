<?php 
    include('../config/configDb.php');

?>
<?php

    if(isset($_POST['ADD_chucvu'])){
        include('QLNS\chucvu\add.php');
    }elseif(isset($_GET['depDelete']) && isset($_GET['chucvuDelete'])){
        include('QLNS\chucvu\delete.php');
    }elseif(isset($_POST['ADD_department'])){
        include('QLNS\phongban\add.php');
    }elseif(isset($_GET['phongDelete'])){
        include('QLNS\phongban\delete.php');
    }
?>
<h1 class="tableName">Phòng ban</h1>
<?php 
    require_once('chucvu/lietke.php');
?>
<!-- chucvu -->
<?php 
    require_once('phongban/lietke.php');
?>
