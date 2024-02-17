<?php 
    session_start();
    include("../config/configDb.php");
    if(isset($_POST['btn_login'])){
        $adminMail = $_POST["mail"];
        $adminPass = $_POST["pass"];
        $adminOtp = $_POST["otp"];
        $sql = "SELECT * FROM tbl_admin WHERE mail = '".$adminMail."' AND password = '".$adminPass."' AND otp = '".$adminOtp."'";
        $query = mysqli_query($mysqli, $sql);
        if(mysqli_num_rows($query) > 0){
            $_SESSION['mailAdmin'] = $adminMail;
            header('Location: ../../PHPmailer/mailControl/loginNoti.php?user=admin&mail=' . $adminMail);
        } else{
            echo '<script>alert("Cút");</script>';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../asset/css/reset.css">
    <link rel="stylesheet" href="../asset/css/login/loginPC.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <title>ICONVINA - Admin</title>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="../asset/media/base/logo/ICONVINA_logo.png" alt="">
        </div>
        <form action="" method="post" class="loginForm" autocomplete="off">
            <input type="text" placeholder="Mail" name="mail" autocomplete="false">
            <input type="password" placeholder="Password" name="pass">
            <input type="password" placeholder="Otp" name="otp">
            <button type="submit" name="btn_login" >Đăng nhập</button>
        </form>
    </div>
</body>

</html>