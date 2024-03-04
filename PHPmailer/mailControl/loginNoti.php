<?php
    $user = $_GET['user'];
    $mailLogin=$_GET['mail'];
    require_once '../../security/checkDevice.php';
    $infoDevice = checkDevice();
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $time =date('Y-m-d H:i:s');
    include("../setup_mail.php");

    try {
    //Recipients
        $mail->setFrom('designledvn@gmail.com', 'ICONVINA IT STAFF');
        $mail->addAddress($mailLogin);     
        //Content
        $mail->isHTML(true);    
        $mail->CharSet = 'UTF-8';                              // Set email format to HTML
        $mail->Subject = 'Cảnh báo đăng nhập !';
        $mail->Body    = file_get_contents('../mailForm/loginNotiForm.php');
        $mail->Body = str_replace('$mailLogin', $mailLogin, $mail->Body);
        $mail->Body = str_replace('$infoDevice', $infoDevice, $mail->Body);
        $mail->Body = str_replace('$time', $time, $mail->Body);
        $mail->send();
        if( $user == 'admin'){
            header('Location:../../admin/modules/admin.php ');
        }
     
    } catch (Exception $e) {
        header('Location:../../admin/modules/admin.php ');
    
    }
?>