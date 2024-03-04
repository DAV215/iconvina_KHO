<?php 
    session_start();
    $_SESSION['boolUser'] = 1;
    $_SESSION['admin'] = 0;
    include("../config/configDb.php");
    if(isset($_POST['btn_login'])){
        $username = $_POST["username"];
        $adminPass = $_POST["pass"];
        $adminOtp = $_POST["otp"];
        $sql = "SELECT * FROM tbl_user WHERE username = '".$username."' AND password = '".$adminPass."' AND otp = '".$adminOtp."'";
        $query = mysqli_query($mysqli, $sql);
        $data=[];
        if(mysqli_num_rows($query) > 0){
            while ($row = mysqli_fetch_array($query)){
                $data = $row;
                $_SESSION['userINFO'] = $row;
            }
            $_SESSION['mailUser'] = $data['mail'];
            $_SESSION['username_Login'] =  $data['username'];
            $_SESSION['userFullname'] =  $data['fullname'];
            header('Location: ../../PHPmailer/mailControl/loginNoti.php?user=admin&mail='.$data['mail']);
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
        <form action="" method="post" class="loginForm" autocomplete="true">
            <h1>USER</h1>
            <input type="text" placeholder="username" name="username" autocomplete="true">
            <input type="password" placeholder="Password" name="pass" autocomplete="true">
            <input type="text" placeholder="Otp" name="otp" autocomplete="true">
            <button type="submit" name="btn_login" >Đăng nhập</button>
        </form>
    </div>
</body>

</html>