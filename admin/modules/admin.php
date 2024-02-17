<?php 
    session_start();
    if(!isset($_SESSION['mailAdmin'])){
        header('Location:login.php');
    }
    if(isset($_GET['logout']) && $_GET['logout']=='true'){
        unset($_SESSION['mailAdmin']);
        header('Location:login.php');

    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/css/admin/main.css">
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
