<?php 
    if($_GET['actionChild'] == "buysuggestDetail"){
        $id_BuySuggest = $_GET['idBuySuggest'];
    }
    if($_SESSION['admin']){
        if($_SESSION['boolUser'] && !checkPerOfUser(16, $_SESSION['userINFO']['id']) && !checkBuySuggestofUser($id_BuySuggest,$_SESSION['userINFO']['id'])){
            echo '<script>';
            echo 'alert("Không có quyền truy cập!");';
            echo 'window.location.href = "admin.php?job=QLTC&action=dexuatmua";';
            echo '</script>';
       }
    }
    // include('QLNS/getdataUser.php');
    $infoBuySuggest = getBuySuggestDetail($id_BuySuggest);
    $imgBuysuggest = getBuySuggest_IMG($id_BuySuggest);
?>
<?php 
    $temp_PC = new getPhieuChi;
    $checkHavePC = $temp_PC ->checkHavePC($id_BuySuggest);
    if($checkHavePC){

    }else{
        if(isset($_POST['modifyBuySuggest'] ) ){
            $nameDXM = $_POST['nameDXM'];
            $money = $_POST['money'];
            $daySuggest = $_POST['daySuggest'];
            $supervisor = $_POST['supervisor'];
            $supplier_name = $_POST['supplier_name'];
            $bool_VAT = checkValue('bool_VAT')?1:0;
            $suppiler_phone = checkValue('suppiler_phone');
            $suppiler_add = checkValue('suppiler_add');
            $note = checkValue('note');
    
            $sqlUpdateDMX = "UPDATE `tbl_buysuggest` SET 
            `nameDXM` = '$nameDXM',
            `money` = '$money',
            `daySuggest` = '$daySuggest',
            `supervisor` = '$supervisor',
            `supplier_name` = '$supplier_name',
            `suppiler_phone` = '$suppiler_phone',
            `suppiler_add` = '$suppiler_add',
            `bool_VAT` = '$bool_VAT',
            `note` = '$note'
            WHERE `id` = $id_BuySuggest";
            $queryUpdateDMX = mysqli_query($mysqli, $sqlUpdateDMX);
            echo "<meta http-equiv='refresh' content='0'>";
        }
        if(isset($_POST['ImgDel'])){
            $linkDel = $_POST['ImgDel'];
            $sql  = "DELETE FROM `tbl_imgbuysugest` WHERE `buysuggestCode`='$id_BuySuggest' AND `link`='$linkDel'";
            $query = mysqli_query($mysqli, $sql);
            echo "<meta http-equiv='refresh' content='0'>";
        }
        if(isset($_FILES['imgHoaDon']) ){

            $daySuggest = $_POST['daySuggest'];
            $nameDXM = $_POST['nameDXM'];
            $linkImg = [];
            $imgurData = array(); 
            $coutImage = count(array_filter($_FILES['imgHoaDon']['tmp_name']));
            $pathImgTemp = "QLDXM/media/";
            removeImgTemp($pathImgTemp);
            if ($coutImage == 1 ) {
                $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][0];
                $linkImg = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $nameDXM.$daySuggest);
                insertImgtoDB($id_BuySuggest, $linkImg);
            }
            elseif ($coutImage > 1){
                $link ;
                for ($i=0; $i < $coutImage; $i++) { 
                    $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][$i];
                    $link = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $nameDXM . $daySuggest);
                    insertImgtoDB($id_newestBuggest, $link);
                }
            }else{
                echo "không có ảnh";
                echo '<script>alert("Nhập hóa đơn");</script>'; 
            }
            echo "<meta http-equiv='refresh' content='0'>";
        }
        if(isset($_POST['approveDXM'])){
            $bool_approve = 1;
            $approve_by = $_SESSION['userFullname'];
    
            $sqlApprove = "UPDATE `tbl_buysuggest` SET 
            `bool_approve` = '$bool_approve',
            `approve_by` = '$approve_by'
            WHERE `id` = $id_BuySuggest";
            $queryApprove = mysqli_query($mysqli, $sqlApprove);
            echo "<meta http-equiv='refresh' content='0'>";
        }
    }

    //FUNCTION
    function insertImgtoDB($id, $link){
        include('../config/configDb.php');
        $sql = "INSERT INTO `tbl_imgbuysugest`(`buysuggestCode`, `link`)
        VALUES(
            '$id',
            '$link'
        )";
        $query = mysqli_query($mysqli, $sql);

        if (!$query) {
            echo "Error: " . mysqli_error($mysqli);
        }
    }
    function compressImage($source, $destination, $quality) { 
        // Get image info 
        $imgInfo = getimagesize($source); 
        $mime = $imgInfo['mime']; 
         
        // Create a new image from file 
        switch($mime){ 
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($source); 
                break; 
            case 'image/png': 
                $image = imagecreatefrompng($source); 
                break; 
            case 'image/gif': 
                $image = imagecreatefromgif($source); 
                break; 
            default: 
                $image = imagecreatefromjpeg($source); 
        } 
         
        // Save image 
        imagejpeg($image, $destination, $quality); 
         
        // Return compressed image 
        return $destination; 
    }
    function checkValue($x){
        if(isset($_POST[$x])){
            return $x = $_POST[$x];
        } else {
            $x = '';
        }
        return $x;
    }
    function removeImgTemp($folderPath ){
        $files = glob($folderPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }   
    }
    function uploadToImgur($file, $title ) {
        $IMGUR_CLIENT_ID = "2207b606e4513b2";
        $pathImgTemp = "../media";
        // Compress the image
        $compressedImage = compressImage($file, $pathImgTemp, 50);
        // Prepare API post parameters
        $postFields = array(
            'title' => $title,
            'image' => base64_encode(file_get_contents($compressedImage))
        );
    
        // Initialize cURL session
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $IMGUR_CLIENT_ID));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    
        // Execute cURL session
        $response = curl_exec($ch);
    
        // Close cURL session
        curl_close($ch);
    
        // Decode API response to array
        $responseArr = json_decode($response);
    
        // Check if the image was successfully uploaded
        if (!empty($responseArr->data->link)) {
            return $responseArr->data->link;
        } else {
            return "ERR";
        }
    }
?>

<div class="roadmap">
    <h4 class="approve_MAP_element">Đã lập đề xuất</h4>
    <?php 
        if($temp_PC->checkHavePC($id_BuySuggest)){
            ?>
                <h4 class="approve_MAP_element">Đã lập phiếu chi</h4>
            <?php
        }else{
            ?>
                <h4>Chưa lập phiếu chi</h4>
            <?php
        }
    ?>
    <div class="PC_DUYET" style="display: <?php ($temp_PC->checkHavePC($id_BuySuggest)) == 1 ? 'block':'none'; ?> ;">
        <?php 
            if($temp_PC->checkHavePC($id_BuySuggest)){
                $CheckMap = $temp_PC->getPC_From_IdBuySuggest($id_BuySuggest);
                if($CheckMap['bool_AllApprove']){
                    ?>
                        <h4 class="approve_MAP_element">Đã được duyệt</h4>
                    <?php
                }else{
                    if($CheckMap['bool_approveBy_TQ']){
                        ?>
                            <h4 class="approve_MAP_element">Thủ Quỹ đã duyệt</h4>
                        <?php
                    }else{
                        ?>
                            <h4>Thủ Quỹ chưa duyệt</h4>
                        <?php
                    }
                    if($CheckMap['taikhoanchi']=="Tiền Mặt"){
                        if($CheckMap['bool_approveBy_ADMIN1']){
                            ?>
                                <h4 class="approve_MAP_element">Admin 1 đã duyệt</h4>
                            <?php
                        }else{
                            ?>
                                <h4>Admin 1 chưa duyệt</h4>
                            <?php
                        }
                    }elseif($CheckMap['taikhoanchi']=="Ngân hàng cá nhân "){
                            if($CheckMap['bool_approveBy_ADMIN2']){
                                ?>
                                    <h4 class="approve_MAP_element">Admin 2 đã duyệt</h4>
                                <?php
                            }else{
                                ?>
                                    <h4>Admin 2 chưa duyệt</h4>
                                <?php
                            }
                    }elseif($CheckMap['taikhoanchi']=="Ngân hàng công ty "){
                        if($CheckMap['bool_approveBy_KT']){
                            ?>
                                <h4 class="approve_MAP_element">Kế toán đã duyệt</h4>
                            <?php
                        }else{
                            ?>
                                <h4>Kế toán chưa duyệt</h4>
                            <?php
                        }
                }
                }
            }
        ?>
    </div>


</div>

<h1>Chi tiết đề xuất</h1>

<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="hidden" name="namebuyer" value="<?php echo $infoBuySuggest['namebuyer']; ?>" required>
                    <div class="inputHaveLable">
                        <label for="nameproject">Dự án</label>
                        <input type="text" name="nameproject" value="<?php echo $infoBuySuggest['nameproject']; ?>"
                            disabled required>

                    </div>
                    <div class="inputHaveLable">
                        <label for="nameDXM">Tên đề xuất</label>
                        <input type="text" name="nameDXM" placeholder="Tên đề xuất"
                            value="<?php echo $infoBuySuggest['nameDXM']; ?> " required>
                    </div>

                    <div class="inputHaveLable">
                        <label for="quytrinhmuahang"> Quy trình mua hàng</label>
                        <input type="text" name="quytrinh" value="<?php echo $infoBuySuggest['quytrinh']; ?>" disabled
                            required>
                    </div>
                    <div class="inputHaveLable">
                        <label for="money">Tổng tiền</label>
                        <input type="text" onkeypress="CurrencyFormat(this)" name="money" placeholder="Tổng tiền"
                            value="<?php echo $infoBuySuggest['money']; ?>" required>
                    </div>

                    <div class="inputHaveLable">
                        <label for="daySuggest"> Ngày đề xuất</label>
                        <input type="datetime-local" name="daySuggest"
                            value="<?php echo $infoBuySuggest['daySuggest']; ?>">
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Người đồng kiểm</label>
                        <input type="text" list="suggestSupervisor" name="supervisor"
                            value="<?php echo $infoBuySuggest['supervisor']; ?>">
                        <datalist id="suggestSupervisor">
                            <?php 
                                foreach (getAllPersonnel() as $supervisor) {
                                    ?>
                            <option value="<?php echo $supervisor['fullname'] ?>"><?php echo $supervisor['fullname'] ?>
                            </option>
                            <?php
                                    }
                            ?>
                        </datalist>
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Nhà cung cấp</label>
                        <input type="text" list="suggestSupplier" name="supplier_name"
                            value="<?php echo $infoBuySuggest['supplier_name']; ?>">
                        <datalist id="suggestSupplier">
                            <?php 
                                foreach (getAllSupplier() as $Supplier) {
                                    ?>
                            <option value="<?php echo $Supplier['supplier_name'] ?>">
                                <?php echo $Supplier['supplier_name'] ?></option>
                            <?php
                                    }
                            ?>
                        </datalist>
                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for="bool_VAT"> VAT </label>
                        <input type="checkbox" name="bool_VAT"
                            <?php echo $infoBuySuggest['bool_VAT'] ? 'checked' : ''; ?>>

                    </div>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <input type="text" name="suppiler_phone" placeholder="SDT nhà cung cấp"
                        value="<?php echo $infoBuySuggest['suppiler_phone']; ?>">
                    <input type="text" name="suppiler_add" placeholder="Địa chỉ nhà cung cấp"
                        value="<?php echo $infoBuySuggest['suppiler_add']; ?>">
                        
                    <textarea name="note" id="" cols="30" rows="10" placeholder="Ghi chú" autocomplete="list"
                        aria-haspopup="true"><?php echo $infoBuySuggest['note']; ?></textarea>
                        <?php 
                                $temp_PC = new getPhieuChi;
                                $checkHavePC = $temp_PC ->checkHavePC($id_BuySuggest);
                                if($checkHavePC){
                                    ?>
                                        <h3 style="color:white;">Đã lập phiếu chi không thể thay đổi đề xuất mua !</h3>
                                    <?php
                                }else{
                                    ?>
                                        <button type="button">Xóa</button>
                                        <button type="submit" name="modifyBuySuggest"
                                            style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Sửa đề xuất
                                            mua</button>           
                                    <?php
                                }
                            
  
                        ?>

                        <?php 
                            if($_SESSION['admin'] || checkPerOfUser(16, $_SESSION['userINFO']['id'])) {
                                $temp_PC = new getPhieuChi;
                                $checkHavePC = $temp_PC ->checkHavePC($id_BuySuggest);
                                if(!$checkHavePC){
                                    ?>
                                        <a href="admin.php?job=QLTC&action=phieuchi&actionChild=addPhieuChi&idBuySuggest=<?php echo $infoBuySuggest['id']; ?>">Duyệt và lập phiếu chi</a>
                                    <?php
                                }else{
                                    ?>
                                        <a href="admin.php?job=QLTC&action=phieuchi&actionChild=phieuchiDetail&idPhieuChi=<?php echo $temp_PC->getPC_From_IdBuySuggest($id_BuySuggest)['id'] ?>">Chi tiết phiếu chi</a>
                                    <?php
                                }
                            }
  
                        ?>


                        
                </div>
            </div>

        </div>
    </div>
</form>
<h1>Hóa đơn</h1>
<form action="" method="post" id="addReceipForm" enctype="multipart/form-data">
    <div class="addReceipt">
        <input type="hidden" name="nameDXM" value = "<?php echo $infoBuySuggest['nameDXM']; ?>">
        <input type="hidden" name="daySuggest" value = "<?php echo $infoBuySuggest['daySuggest']; ?>">
        <input type="hidden" name="namebuyer" value = "<?php echo $infoBuySuggest['namebuyer']; ?>">
        <input type='file' name="imgHoaDon[]" id="fileInputaddReceipt" accept=".png, .jpg, .jpeg" multiple />
        <label for="fileInputaddReceipt" class="lableAddReceipt">Thêm</label>
    </div>
</form>
<form action="" method="post">
    <div class="userForm">
        <div class="mainForm receipt">
            <?php 
                foreach ($imgBuysuggest as $link => $value) {

                    ?>
                <div class="receiptItem">
                    <a  href="<?php echo $value['link']; ?>" target="_blank">
                        <div class="subImg">
                            <img src="<?php print_r($value['link']) ?>" alt="">
                        </div>
                        <div class="subACT">
                            <?php 
                                $temp_PC = new getPhieuChi;
                                $checkHavePC = $temp_PC ->checkHavePC($id_BuySuggest);
                                if($checkHavePC){
                                    ?>
                                        <a href="<?php echo $value['link']; ?>" target="_blank">Chi tiết</a>

                                    <?php
                                }else{
                                    ?>
                                        <input type="hidden" name="ImgDel" value="<?php print_r($value['link']) ?>">
                                        <a href="<?php echo $value['link']; ?>" target="_blank">Chi tiết</a>
                                        <button type="submit">Xóa</button>
                                    <?php
                                }
                            ?>

                        </div>
                    </a>
                </div>
            <?php
                }
            ?>
        </div>
    </div>
</form>
<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
$(document).ready(function() {
    $('#fileInputaddReceipt').change(function() {
        $('#addReceipForm').submit();
    });
});
</script>

<?php 
exit();
?>