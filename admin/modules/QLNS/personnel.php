

<?php 
    include('nhansu/lietke.php');
    if(isset($_POST['addUser'])){
        include('nhansu/add.php');
    }
    if(isset($_GET['actionChild'])){
        include('nhansu/userDetail.php');
    }

?>