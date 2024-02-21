<?php 
    if(isset($_POST['addDXM'] ) ){
        $nameproject = $_POST['nameproject'];
        $namebuyer = $_POST['namebuyer'];
        $nameDXM = $_POST['nameDXM'];
        $quytrinhmuahang = $_POST['quytrinhmuahang'];
        $daySuggest = $_POST['daySuggest'];
        $supervisor = $_POST['supervisor'];
        $supplier_name = $_POST['supplier_name'];
        $bool_VAT = checkValue('bool_VAT')?1:0;
        $suppiler_phone = checkValue('suppiler_phone');
        $suppiler_add = checkValue('suppiler_add');
        $note = checkValue('note');
        

        $imgHoaDon = $_FILES['imgHoaDon']['name'];
        $imgHoaDon_tmp = $_FILES['imgHoaDon']['tmp_name'];
        $imgHoaDon = time().'_'.$namebuyer;
        $sqlAddDMX = "INSERT INTO `tbl_buysuggest` 
        (
            `namebuyer`, 
            `nameDXM`, 
            `quytrinh`, 
            `daySuggest`, 
            `supervisor`, 
            `nameproject`, 
            `supplier_name`, 
            `suppiler_phone`, 
            `suppiler_add`, 
            `bool_VAT`, 
            `img`, 
            `note`
        ) 
        VALUES 
        (
            '$namebuyer', 
            '$nameDXM', 
            '$quytrinhmuahang', 
            '$daySuggest', 
            '$supervisor', 
            '$nameproject', 
            '$supplier_name', 
            '$suppiler_phone', 
            '$suppiler_add', 
            '$bool_VAT', 
            '$imgHoaDon', 
            '$note'
        )";
        echo $sqlAddDMX;
        // $queryAddUse = mysqli_query($mysqli, $sqlAddUser);
        // move_uploaded_file($avt_tmp,'../asset/media/private/avt/'.$avt);
        // exit();
        // echo "<script> window.open('modules/admin.php?job=QLTC&action=dexuatmua');</script>";
    }
    else{
        echo "không nhận giá trị";
    }
    

    function checkValue($x){
        if(isset($_POST[$x])){
            return $x = $_POST[$x];
        } else {
            $x = '';
        }
        return $x;
    }
    include('QLNS/getdataUser.php');
    $userInfo = getPersonnel($_SESSION['username_Login']);

?>

<h1>Tạo đề xuất</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="avtGrid">
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type='file' name="imgHoaDon" id="avt" accept=".png, .jpg, .jpeg" />
                    <label for="avt"></label>
                </div>
                <div class="avatar-preview">
                    <div id="imagePreview" style="background-image: url(../asset/media/base/receipt/receipt.png);">
                    </div>
                </div>
            </div>
            <button type="submit" name="addDXM" style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Tạo đề xuất mua</button>
        </div>
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="hidden" name="namebuyer" value="<?php echo $userInfo['fullname']; ?>" required>
                    <div class="inputHaveLable">
                        <label for="nameproject">Dự án</label>
                        <select name="nameproject" id="">
                            <?php 
                            foreach (getAllProject() as $project) {
                                ?>
                            <option value="<?php echo $project['name'] ?>"><?php echo $project['name'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="nameDXM">Tên đề xuất</label>
                        <input type="text" name="nameDXM" placeholder="Tên đề xuất" required>
                    </div>

                    <div class="inputHaveLable">
                        <label for="quytrinhmuahang"> Quy trình mua hàng</label>
                        <select name="quytrinhmuahang" id="">
                            <?php 
                                foreach (getAllQuyTrinh() as $quytrinh) {
                                    ?>
                            <option value="<?php echo $quytrinh['name'] ?>"><?php echo $quytrinh['name'] ?></option>
                            <?php
                                }
                            ?>
                        </select>
                    </div>

                    <div class="inputHaveLable">
                        <label for="daySuggest"> Ngày đề xuất</label>
                        <input type="date" name="daySuggest">
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Người đồng kiểm</label>
                        <input type="text" list="suggestSupervisor" name="supervisor">
                        <datalist id="suggestSupervisor">
                            <?php 
                                foreach (getAllPersonnel() as $supervisor) {
                                    ?>
                                        <option value="<?php echo $supervisor['fullname'] ?>"><?php echo $supervisor['fullname'] ?></option>
                                <?php
                                    }
                            ?>
                        </datalist>
                    </div>
                    <div class="inputHaveLable">
                    <label for="supervisor"> Nhà cung cấp</label>
                        <input type="text" list="suggestSupplier" name="supplier_name">
                        <datalist id="suggestSupplier">
                            <?php 
                                foreach (getAllSupplier() as $Supplier) {
                                    ?>
                                        <option value="<?php echo $Supplier['supplier_name'] ?>"><?php echo $Supplier['supplier_name'] ?></option>
                                <?php
                                    }
                            ?>
                        </datalist>
                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for="bool_VAT"> VAT </label>
                        <input type="checkbox" name="bool_VAT">
                    </div>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <input type="text" name="suppiler_phone" placeholder="SDT nhà cung cấp">
                    <input type="text" name="suppiler_add" placeholder="Địa chỉ nhà cung cấp">
                    <textarea name="note" id="" cols="30" rows="10" placeholder="Ghi chú" autocomplete="list" aria-haspopup="true" ></textarea>

                </div>
            </div>

        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
            $('#imagePreview').hide();
            $('#imagePreview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#avt").change(function() {
    readURL(this);
});
</script>

<?php 
exit();
?>