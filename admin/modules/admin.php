<?php 
    session_start();
    if($_SESSION['boolUser']){
        if(!isset($_SESSION['username_Login'])){
        header('Location:userlogin.php');
        }
    }
    elseif(!isset($_SESSION['mailAdmin'])){
        header('Location:userlogin.php');
    }
    if(isset($_GET['logout']) && $_GET['logout']=='true'){
        if($_SESSION['boolUser'] ){
            unset($_SESSION['boolUser']);
            header('Location:userlogin.php');
        }else{
            unset($_SESSION['mailAdmin']);
            header('Location:userlogin.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/css/admin/main.css">
    <link rel="stylesheet" href="../asset/css/mobile/formInfo_MB.css">
    <link rel="stylesheet" href="../asset/css/mobile/sidebar_MB.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <?php 
            include("../modules/base/sidebar.php");
            include("../modules/base/mainContent.php");
        ?>
    </div>
</body>

</html>
