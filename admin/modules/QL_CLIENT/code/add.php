<?php 
    if(isset($_POST['save_new_client'])){
        $company = $_POST['company'];
        $represent_user = $_POST['represent_user'];
        $addr = $_POST['addr'];
        $phone = $_POST['phone'];
        $mail = $_POST['mail'];
        $Client = new Client;
        $Client->addNew(array('represent_user' => $represent_user, 'addr' => $addr, 'phone' => $phone,'mail' => $mail, 'company' => $company));
    }
?>
<h1>Thêm khách hàng</h1>
<div class="detail" style="width:90%;">
    <fieldset class="info input_class tab_container" style="width:100%; display:flex; ">
        <legend>Thông tin cơ bản:</legend>
        <form action="" method="POST">
            <fieldset>
                <legend>Công ty:</legend>
                <input type="text" name="company" required>
            </fieldset>
            <fieldset>
                <legend>Cá nhân đại diện:</legend>
                <input type="text" name="represent_user" required>
            </fieldset>
            <fieldset>
                <legend>Địa chỉ gửi hàng:</legend>
                <input type="text" name="addr" required>
            </fieldset>
            <fieldset>
                <legend>Số điện thọai:</legend>
                <input type="phone" name="phone" required>
            </fieldset>
            <fieldset>
                <legend>Mail:</legend>
                <input type="email" name="mail" required>
            </fieldset>
            <div class="inforForm">
                <button name="save_new_client">Lưu khách hàng mới</button>
            </div>
        </form>

    </fieldset>
</div>