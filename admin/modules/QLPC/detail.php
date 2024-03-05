<?php
if ($_GET['actionChild'] == "phieuchiDetail") {
    $idPhieuChi = $_GET['idPhieuChi'];
}
// include('QLNS/getdataUser.php');
$infoBuySuggest = getBuySuggestDetail($idPhieuChi);
$imgBuysuggest = getBuySuggest_IMG($idPhieuChi);
$phieuchi = new getPhieuChi;
$infoPhieuChi = $phieuchi->getPhieuChiDetail($idPhieuChi);

?>
<?php
if (isset($_POST['modifyPC'])) {
    $name = $_POST['name'];

    $quytrinh = $_POST['quytrinhmuahang'];
    $total = $_POST['total'];
    $taikhoanchi = $_POST['loaitaikhoan'];

    $loaichi = $_POST['loaichi'];
    $note = checkValue('note');
    $prodName = $_POST['prodName'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $tax = $_POST['tax'];
    $bool_VAT = checkValue('bool_VAT') ? 1 : 0;


    $sqlUPDATEPC = "UPDATE `tbl_phieuchi` SET
            `name` = '$name',
            `note` = '$note',
            `quytrinh` = '$quytrinh',
            `total` = '$total',
            `taikhoanchi` = '$taikhoanchi',
            `bool_VAT` = '$bool_VAT'
            WHERE `id` = '$idPhieuChi'";  // Adjust the WHERE clause according to your identifier

    $queryUpdatePC = mysqli_query($mysqli, $sqlUPDATEPC);
    resetReceiptPC($loaichi, $idPhieuChi);
    if ($loaichi == "Chi mua hàng") {
        foreach ($prodName as $key => $value) {
            if ($value != null) {
                $sql = "INSERT INTO `tbl_itemdetail_cmh`(`id_phiechi`, `name`, `price`, `quantity`, `tax`) VALUES ('$idPhieuChi', '$value', '$price[$key]', '$quantity[$key]', '$tax[$key]')";
                $query = mysqli_query($mysqli, $sql);
                echo $sql;
            }
        }
    } elseif ($loaichi == 'Chi khác') {
        foreach ($prodName as $key => $value) {
            if ($value != null) {
                $sql = "INSERT INTO `tbl_itemdetail_ckhac`(`id_phiechi`, `name`, `price`, `tax`) VALUES ('$idPhieuChi', '$value', '$price[$key]', '$tax[$key]')";
                $query = mysqli_query($mysqli, $sql);
            } else {
                continue;
            }
        }
    } elseif ($loaichi == 'Chi tạm ứng') {
        foreach ($prodName as $key => $value) {
            if ($value != null) {
                $sql = "INSERT INTO `tbl_itemdetail_tu`(`id_phiechi`, `name`, `price`) VALUES ('$idPhieuChi', '$value', '$price[$key]')";
                $query = mysqli_query($mysqli, $sql);

            } else {
                continue;
            }
        }
    } elseif ($loaichi == 'Chi tạm ứng lương') {
        foreach ($prodName as $key => $value) {
            if ($value != null) {
                $sql = "INSERT INTO `tbl_itemdetail_tul`(`id_phiechi`, `name`, `price`)VALUES ('$idPhieuChi', '$value', '$price[$key]')";
                $query = mysqli_query($mysqli, $sql);

            } else {
                continue;
            }
        }
    }
    echo "<meta http-equiv='refresh' content='0'>";
}
if (isset($_POST['deletePC'])){
    $sql = "DELETE FROM `tbl_phieuchi` WHERE `id`='$idPhieuChi'";
    $query = mysqli_query($mysqli, $sql);

    resetReceiptPC_( $idPhieuChi);
    resetIMGPC($idPhieuChi);
    echo "<meta http-equiv='refresh' content='0'>";

}
if (isset($_POST['ImgDel'])) {
    $linkDel = $_POST['ImgDel'];
    $sql = "DELETE FROM `tbl_imgphieuchi` WHERE `codePhieuChi`='$idPhieuChi' AND `link`='$linkDel'";
    $query = mysqli_query($mysqli, $sql);
    echo "<meta http-equiv='refresh' content='0'>";
}
if (isset($_FILES['imgHoaDon'])) {
    $name = $_POST['name'];
    $linkImg = [];
    $imgurData = array(); 
    $coutImage = count(array_filter($_FILES['imgHoaDon']['tmp_name']));
    $pathImgTemp = "QLPC/media/";
    removeImgTemp($pathImgTemp);
    if ($coutImage == 1 ) {
        $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][0];
        $linkImg = uploadToImgur($uploadedFilePath, 'HD_PC' . $name);
        insertImgtoDB($idPhieuChi, $linkImg);
    }
    elseif ($coutImage > 1){
        $link ;
        for ($i=0; $i < $coutImage; $i++) { 
            $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][$i];
            $linkImg = uploadToImgur($uploadedFilePath, 'HD_PC' . $name.$i);
            insertImgtoDB($idPhieuChi, $linkImg);
        
        }
    }else{
        echo "không có ảnh";
        echo '<script>alert("Nhập hóa đơn");</script>'; 
    }
    echo "<meta http-equiv='refresh' content='0'>";
}
// if (isset($_POST['ApprovePC'])) {
//     $linkDel = $_POST['ImgDel'];
//     $sql = "DELETE FROM `tbl_imgphieuchi` WHERE `codePhieuChi`='$idPhieuChi' AND `link`='$linkDel'";
//     $query = mysqli_query($mysqli, $sql);
//     echo "<meta http-equiv='refresh' content='0'>";
// }
if($_SESSION['boolUser']){
    $phanquyenDuyet = getPhanQuyenDuyet($_SESSION['userINFO']['username']);
    echo $phanquyenDuyet;
}
if(isset($_POST['ApprovePC'])){
    if(!$infoPhieuChi['bool_VAT']){
        if($infoPhieuChi['taikhoanchi']=='Tiền Mặt'){
            if($infoPhieuChi['total'] < 500000){
                if(getPhanQuyenDuyet($_SESSION['userINFO']['username']) == 'thuquy'){
                    $id = $infoPhieuChi['id'];
                    $sql = " UPDATE `tbl_phieuchi` SET `bool_approveBy_TQ`= 1,`bool_AllApprove`=1 WHERE `id` = '$id'";
                    $query = mysqli_query($mysqli, $sql);
                }
            }else{
                if(getPhanQuyenDuyet($_SESSION['userINFO']['username']) == 'thuquy'){
                    $id = $infoPhieuChi['id'];
                    $sql = " UPDATE `tbl_phieuchi` SET `bool_approveBy_TQ`= 1 WHERE `id` = '$id'";
                    $query = mysqli_query($mysqli, $sql);
                }elseif(getPhanQuyenDuyet($_SESSION['userINFO']['username']) == 'admin1'){
                    $id = $infoPhieuChi['id'];
                    $sql = " UPDATE `tbl_phieuchi` SET `bool_approveBy_ADMIN1`= 1 ,`bool_AllApprove`=1  WHERE `id` = '$id'";
                    $query = mysqli_query($mysqli, $sql);
                }
            }
        }elseif($infoPhieuChi['taikhoanchi']=='Ngân hàng cá nhân '){
            if(getPhanQuyenDuyet($_SESSION['userINFO']['username']) == 'thuquy'){
                $id = $infoPhieuChi['id'];
                $sql = " UPDATE `tbl_phieuchi` SET `bool_approveBy_TQ`= 1 WHERE `id` = '$id'";
                $query = mysqli_query($mysqli, $sql);
            }elseif(getPhanQuyenDuyet($_SESSION['userINFO']['username']) == 'admin2'){
                $id = $infoPhieuChi['id'];
                $sql = " UPDATE `tbl_phieuchi` SET `bool_approveBy_ADMIN2`= 1 ,`bool_AllApprove`=1  WHERE `id` = '$id'";
                $query = mysqli_query($mysqli, $sql);
            }
        }

    }
}
//FUNCTION
function insertImgtoDB($id, $link)
{
    include('../config/configDb.php');
    $sql = "INSERT INTO `tbl_imgphieuchi`(`codePhieuChi`, `link`)
        VALUES(
            '$id',
            '$link'
        )";
    $query = mysqli_query($mysqli, $sql);

        if (!$query) {
            echo "Error: " . mysqli_error($mysqli);
        }
}
function compressImage($source, $destination, $quality)
{
    // Get image info 
    $imgInfo = getimagesize($source);
    $mime = $imgInfo['mime'];

    // Create a new image from file 
    switch ($mime) {
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
function checkValue($x)
{
    if (isset($_POST[$x])) {
        return $x = $_POST[$x];
    } else {
        $x = '';
    }
    return $x;
}
function removeImgTemp($folderPath)
{
    $files = glob($folderPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
function uploadToImgur($file, $title)
{
    $IMGUR_CLIENT_ID = "2207b606e4513b2";
    $pathImgTemp = "../media";
    chmod($pathImgTemp, 0755);
    // Compress the image
    chmod($pathImgTemp, 755);
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
    <h4 class="approve_MAP_element">Đã lập phiếu chi</h4>

    <div class="PC_DUYET" >
        <?php 
            if($infoPhieuChi['bool_AllApprove']){
                ?>
                    <h4 class="approve_MAP_element">Đã được duyệt</h4>
                <?php
            }else{
                if($infoPhieuChi['bool_approveBy_TQ']){
                    ?>
                        <h4 class="approve_MAP_element">Thủ Quỹ đã duyệt</h4>
                    <?php
                }else{
                    ?>
                        <h4>Thủ Quỹ chưa duyệt</h4>
                    <?php
                }
                if($infoPhieuChi['taikhoanchi']=="Tiền Mặt"){
                    if($infoPhieuChi['bool_approveBy_ADMIN1']){
                        ?>
                            <h4 class="approve_MAP_element">Admin 1 đã duyệt</h4>
                        <?php
                    }else{
                        ?>
                            <h4>Admin 1 chưa duyệt</h4>
                        <?php
                    }
                }elseif($infoPhieuChi['taikhoanchi']=="Ngân hàng cá nhân "){
                        if($infoPhieuChi['bool_approveBy_ADMIN2']){
                            ?>
                                <h4 class="approve_MAP_element">Admin 2 đã duyệt</h4>
                            <?php
                        }else{
                            ?>
                                <h4>Admin 2 chưa duyệt</h4>
                            <?php
                        }
                }elseif($infoPhieuChi['taikhoanchi']=="Ngân hàng công ty "){
                    if($infoPhieuChi['bool_approveBy_KT']){
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
                    <div class="inputHaveLable">
                        <label for="nameproject">Mục đích</label>
                        <select name="nameproject" id="">
                            <?php
                            foreach (getAllProject() as $project) {
                                ?>
                                <option value="<?php echo $infoPhieuChi['nameproject'] ?>">
                                    <?php echo $infoPhieuChi['nameproject'] ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="name">Tên phiếu chi</label>
                        <input type="text" name="name" placeholder="Tên đề xuất"
                            value="<?php echo $infoPhieuChi['name']; ?>">
                    </div>

                    <div class="inputHaveLable">
                        <label for="quytrinhmuahang"> Quy trình mua hàng</label>
                        <select name="quytrinhmuahang" id="">
                            <?php
                            $allQuyTrinh = getAllQuyTrinh();
                            foreach (getAllQuyTrinh() as $row) {
                                if ($row['name'] == $infoPhieuChi['quytrinh']) {
                                    echo '<option selected value="' . $infoPhieuChi['quytrinh'] . '">';
                                    echo $infoPhieuChi['quytrinh'] . '</option>';
                                } else {
                                    echo '<option value="' . $row['name'] . '">';
                                    echo $row['name'] . '</option>';
                                }
                            }
                            ?>
                        </select>

                    </div>
                    <div class="inputHaveLable">
                        <label for="name">Tên người mua</label>
                        <input type="text" name="namebuyer" value="<?php echo $infoPhieuChi['id_receiver']; ?>" required
                            disabled>

                    </div>


                    <div class="inputHaveLable">
                        <label for="createDay"> Ngày tạo lệnh</label>
                        <input type="datetime-local" name="createDay" value="<?php echo $infoPhieuChi['createDay'] ?>" required
                            disabled>
                    </div>
                    <div class="inputHaveLable">
                        <label for=""> Quy trình mua hàng</label>
                        <input type="text" name="quytrinh" value="<?php echo $infoPhieuChi['quytrinh']; ?>" required
                            disabled>
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Loại chi</label>
                        <select name="loaichi" id="loaichi" onchange="changeCalTable()">
                            <?php
                            foreach (getAllLoaiChi() as $row) {
                                if ($row['name'] == $infoPhieuChi['loaichi']) {
                            ?>
                                    <option selected value="<?php echo $row['name'] ?>">
                                        <?php echo $row['name'] ?>
                                    </option>
                            <?php
                                } else {
                            ?>
                                    <option value="<?php echo $row['name'] ?>">
                                        <?php echo $row['name'] ?>
                                    </option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="inputHaveLable">
                        <label for="supervisor"> Loại Tài khoản</label>
                        <select name="loaitaikhoan" id="loaitaikhoan">
                            <?php foreach (getAllLoaiTaiKhoan() as $row): ?>
                                <option <?php echo ($row['name'] == $infoPhieuChi['taikhoanchi']) ? 'selected' : ''; ?>
                                    value="<?php echo $row['name']; ?>">
                                    <?php echo $row['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for=""> Tổng tiền duyệt chi: </label>
                        <input type="text" name="mn" value="<?php echo number_format($infoPhieuChi['total']); ?>"
                            disabled>
                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for="bool_VAT"> VAT </label>
                        <input type="checkbox" name="bool_VAT" <?php echo $infoPhieuChi['bool_VAT'] ? 'checked' : ''; ?>>
                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for="bool_VAT"> Tổng tiền đề xuất: </label>
                        <input type="text" name="mn"
                            value="<?php echo number_format(getBuySuggestDetail($infoPhieuChi['id_buySuggest'])['money']); ?>"
                            disabled>
                    </div>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <textarea name="note" id="" cols="30" rows="10" placeholder="Ghi chú" autocomplete="list"
                        aria-haspopup="true"></textarea>
                    <?php 
                        if($_SESSION['admin'] || checkPerOfUser(20, $_SESSION['userINFO']['id'])) {
                            if(!$_SESSION['admin']){
                                if($infoPhieuChi['taikhoanchi']=="Tiền Mặt" ){
                                    if(( $phanquyenDuyet == "admin1" || $phanquyenDuyet == "thuquy")){
                                        if(($phieuchi->getSTT_PC($phanquyenDuyet,$idPhieuChi)) == 1){
                                            $content_BTN_DUYET = "Đã duyệt trong quyền hạn!";
                                            $disable_BTN_DUYET = 1;
                                        } elseif(($phieuchi->getSTT_PC($phanquyenDuyet,$idPhieuChi)) == 99){
                                            $content_BTN_DUYET = "Phiếu chi đã duyệt!";
                                            $disable_BTN_DUYET = 1;
                                        }else{
                                            $content_BTN_DUYET = "Duyệt phiếu chi";
                                            $disable_BTN_DUYET = 0;
                                        }
                                    }else{
                                        $content_BTN_DUYET = "Không đủ quyền hạn - Tài Khoản chi";
                                        $disable_BTN_DUYET = 1;
                                    }

                                }elseif($infoPhieuChi['taikhoanchi'] != "Tiền Mặt" ){
                                    if(( $phanquyenDuyet == "admin2" || $phanquyenDuyet == "ketoan" || $phanquyenDuyet == "thuquy")){
                                        if(($phieuchi->getSTT_PC($phanquyenDuyet,$idPhieuChi)) == 1){
                                            $content_BTN_DUYET = "Đã duyệt trong quyền hạn!";
                                            $disable_BTN_DUYET = 1;
                                        } elseif(($phieuchi->getSTT_PC($phanquyenDuyet,$idPhieuChi)) == 99){
                                            $content_BTN_DUYET = "Phiếu chi đã duyệt!";
                                            $disable_BTN_DUYET = 1;
                                        }else{
                                            $content_BTN_DUYET = "Duyệt phiếu chi";
                                            $disable_BTN_DUYET = 0;
                                        }
                                    }else{
                                        $content_BTN_DUYET = "Không đủ quyền hạn - Tài Khoản chi";
                                        $disable_BTN_DUYET = 1;
                                    }
                                }
                            }else{
                                $content_BTN_DUYET = "Duyệt phiếu chi";
                                $disable_BTN_DUYET = 0;
                            }

                            ?>
                            <button type="submit" name="ApprovePC"style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);" <?php echo $disable_BTN_DUYET ? 'disabled' : ''; ?>> 
                                <?php 
                                    echo $content_BTN_DUYET;
                                ?>    
                            </button>
                            <?php
                        }
                    ?>
                    <?php 
                        if(!$_SESSION['admin']){
                            if($phieuchi->blockModify_PC($idPhieuChi, $phanquyenDuyet) == 0){
                                $content_BTN_modifyDUYET = "Sửa phiếu chi";
                                $disable_BTN_modifyDUYET = 0;
                            }else{
                                $content_BTN_modifyDUYET = "Cấp cao hơn đã duyệt, không thể sửa !";
                                $disable_BTN_modifyDUYET = 1;
                            }
                        }else{
                            $content_BTN_modifyDUYET = "Sửa phiếu chi";
                                $disable_BTN_modifyDUYET = 0;
                        }
                    ?>
                    <button type="submit" name="modifyPC" <?php echo $disable_BTN_modifyDUYET == 1 ? 'disabled':'' ?>
                    style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);"> <?php echo $content_BTN_modifyDUYET  ?></button>                    

                        <?php 
                            if($_SESSION['admin'] || checkPerOfUser(19, $_SESSION['userINFO']['id'])) {
                                ?>
                                    <button type="submit" name="deletePC">Xóa phiếu chi</button>

                                <?php
                            }
                        ?>
                </div>
            </div>


        </div>
    </div>
    <div class="userForm" id="CalMoney_typeLoaichi" style="margin-top:20px;">
        <div class="mainForm">
            <div class="big inforForm">
                <h2 class="nameForm">Chi tiết</h2>
                <?php
                if ($infoPhieuChi['loaichi'] == 'Chi mua hàng') {
                    ?>
  
                    <div class="bodyofForm calculator" id="loaichi_muahang">  
                    <div class="cal_row heading_TBL">
                        <input type="text" style="width: 30%;"  placeholder="Sản phẩm" value="Sản phẩm" disabled>
                        <input type="text" style="width: 8%;"  placeholder="Số lượng" value="Số lượng" disabled>
                        <input type="text" style="width: 10%;"  placeholder="Đơn giá" value="Đơn giá" disabled>
                        <input type="text" style="width: 8%;"  placeholder="Thuế" value="Thuế" disabled>
                        <input type="text" style="width: 20%;"  disabled value="Thành tiền" disabled>
                        <button type="button" id="del_cal_row" disabled></button>
                    </div>
                            <?php
                                if(count(getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id'])) < 1){
                                ?>
                                    <div class="bodyofForm calculator" id="loaichi_muahang">
    
                                        <div class="cal_row">
                                            <input type="text" style="width: 30%;" name="prodName[]" placeholder="Sản phẩm">
                                            <input type="text" style="width: 8%;" name="quantity[]" placeholder="Số lượng">
                                            <input type="text" style="width: 10%;" name="price[]" placeholder="Đơn giá">
                                            <input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế">
                                            <input type="text" style="width: 20%;" name="subTotal[]" disabled>
                                            <button type="button" id="del_cal_row">X</button>
                                        </div>
                                    </div>
                                <?php
                            }
                            $i=0;
                            foreach (getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id']) as $row) {
                                $i++;
                                # code... ?>
                                <div class="cal_row">
                                    
                                    <input type="text" style="width: 30%;" name="prodName[]" placeholder="Sản phẩm"
                                    value="<?php echo $row['name'] ?>">
                                    <input type="text" style="width: 8%;" name="quantity[]" placeholder="Số lượng"
                                    value="<?php echo $row['quantity'] ?>">
                                    <input type="text" style="width: 10%;" name="price[]" placeholder="Đơn giá"
                                    value="<?php echo $row['price'] ?>">
                                    <input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế"
                                    value="<?php echo $row['tax'] ?>">
                                    <input type="text" style="width: 20%;" name="subTotal[]"
                                    value="<?php echo number_format($row['quantity'] * $row['price'] + $row['quantity'] * $row['price'] * $row['tax'] / 100) ?>"
                                    disabled>
                                    <button type="button" id="del_cal_row">X</button>
                                </div>
                                <?php
                            }
                                ?>
                    </div>
                    <?php
                } elseif ($infoPhieuChi['loaichi'] == 'Chi khác') {
                    ?>

                    <div class="bodyofForm calculator" id="loaichi_chikhac">    
                        <div class="cal_row heading_TBL">
                            <input type="text" style="width: 30%;"  placeholder="Sản phẩm" value="Mục đích chi" disabled>
                            <input type="text" style="width: 8%;"  placeholder="Số lượng" value="Số tiền" disabled>
                            <input type="text" style="width: 8%;"  placeholder="Thuế" value="Thuế" disabled>
                            <input type="text" style="width: 20%;"  disabled value="Thành tiền" disabled>
                            <button type="button" id="del_cal_row" disabled></button>
                        </div>
                    <?php
                    if(count(getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id'])) < 1){
                        ?>
                            <div class="bodyofForm calculator" id="loaichi_chikhac">
                                    <div class="cal_row">
                                        <input type="text" style="width: 30%;" name="prodName[]" placeholder="Mục đích chi">
                                        <input type="text" style="width: 8%;" name="price[]" placeholder="Số tiền">
                                        <input type="hidden" name="quantity[]" value="1">
                                        <input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế">
                                        <input type="text" style="width: 20%;" name="subTotal[]" disabled>
                                        <button type="button" id="del_cal_row">X</button>
                                    </div>
                                </div>
                        <?php
                    }
                    foreach (getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id']) as $row) {
                        # code... ?>
                        <div class="cal_row">
                                <input type="text" style="width: 30%;" name="prodName[]" placeholder="Mục đích chi"
                                    value="<?php echo $row['name'] ?>">
                                <input type="text" style="width: 8%;" name="price[]" placeholder="Số tiền"
                                    value="<?php echo $row['price'] ?>">
                                <input type="hidden" name="quantity[]" value="1">
                                <input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế"
                                    value="<?php echo $row['tax'] ?>">
                                <input type="text" style="width: 20%;" name="subTotal[]"
                                    value="<?php echo number_format(1 * $row['price'] + 1 * $row['price'] * $row['tax'] / 100) ?>"
                                    disabled>
                                <button type="button" id="del_cal_row">X</button>
                        </div>
                        <?php
                    }
                        ?>
                </div>
                        <?php
                } elseif ($infoPhieuChi['loaichi'] == 'Chi tạm ứng') {
                    foreach (getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id']) as $row) {
                        ?>
                        <div class="bodyofForm calculator" id="loaichi_tamung">
                            
                            <div class="cal_row">
      
                                    <select name="prodName[]" id="">
                                        <?php
                                        foreach (getAllPersonnel() as $rowName) {
                                            ?>
                                            <?php
                                            if ($row['name'] == $rowName['fullname']) {
                                                ?>
                                                <option selected value="<?php echo $row['name'] ?>">
                                                    <?php echo $row['name'] ?>
                                                </option>
                                                <?php
                                            } else {
                                                ?>
                                                <option value="<?php echo $rowName['fullname'] ?>">
                                                    <?php echo $rowName['fullname'] ?>
                                                </option>

                                                <?php
                                            }
                                            ?>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                <input type="hidden" name="quantity[]" value="1">
                                <input type="hidden" name="tax[]" value="0">
                                <input type="hidden" style="width: 20%;" name="subTotal[]" disabled>
                                <input type="text" style="width: 30%;" name="price[]" placeholder="Số tiền"
                                    value="<?php echo ( $row['price'] )?>">
                            </div>
                        </div>
                        <?php
                    }
                }
                elseif ($infoPhieuChi['loaichi'] == 'Chi tạm ứng lương') {
                    foreach (getReceiptOfPC($infoPhieuChi['loaichi'], $infoPhieuChi['id']) as $row) {
                        ?>
                        <div class="bodyofForm calculator" id="loaichi_tamung">
                            <div class="cal_row">
      
                                    <select name="prodName[]" id="">
                                        <?php
                                        foreach (getAllPersonnel() as $rowName) {
                                            ?>
                                            <?php
                                            if ($row['name'] == $rowName['fullname']) {
                                                ?>
                                                <option selected value="<?php echo $row['name'] ?>">
                                                    <?php echo $row['name'] ?>
                                                </option>
                                                <?php
                                            } else {
                                                ?>
                                                <option value="<?php echo $rowName['fullname'] ?>">
                                                    <?php echo $rowName['fullname'] ?>
                                                </option>

                                                <?php
                                            }
                                            ?>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                <input type="hidden" name="quantity[]" value="1">
                                <input type="hidden" name="tax[]" value="0">
                                <input type="hidden" style="width: 20%;" name="subTotal[]" disabled>
                                <input type="text" style="width: 30%;" name="price[]" placeholder="Số tiền"
                                    value="<?php echo ( $row['price'] )?>">
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                ?>
                
                <input type="hidden" name="total" id="sqlTotal">
                <div class="totalMoney" style="display: flex;">Tổng tiền: <h2 id="totalMoney"></h2>
                </div>
            </div>
        </div>
    </div>
</form>
<h1>Hóa đơn từ tạo lệnh phiếu chi</h1>
<form action="" method="post" id="addReceipForm" enctype="multipart/form-data">
    <div class="addReceipt">
        <input type='file' name="imgHoaDon[]" id="fileInputaddReceipt" accept=".png, .jpg, .jpeg" multiple <?php echo $disable_BTN_modifyDUYET==1?'disabled':'' ?> />
        <label for="fileInputaddReceipt" class="lableAddReceipt">Thêm</label>
        <input type="hidden" name="name" placeholder="Tên đề xuất"value="<?php echo $infoPhieuChi['name']; ?>">
        <input type="hidden" name="createDay" value="<?php echo $infoPhieuChi['createDay'] ?>" required disabled>
    </div>
</form>
<form action="" method="post">
    <div class="userForm">
        <div class="mainForm receipt">
            <?php
            $imgPhieuChi = getPhieuChi_IMG($infoPhieuChi['id']);
            foreach ($imgPhieuChi as $link => $value) {

                ?>
                <div class="receiptItem">
                    <a href="<?php echo $value['link']; ?>" target="_blank">
                        <div class="subImg">
                            <img src="<?php print_r($value['link']) ?>" alt="">
                        </div>
                        <div class="subACT">
                            <input type="hidden" name="ImgDel" value="<?php print_r($value['link']) ?>">
                            <a href="<?php echo $value['link']; ?>" target="_blank">Chi tiết</a>
                            <button type="submit">Xóa</button>
                        </div>
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</form>
<h1>Hóa đơn từ đề xuất mua</h1>
<form action="" method="post">
    <div class="userForm">
        <div class="mainForm receipt">
            <?php
            $imgBuysuggest = getBuySuggest_IMG($infoPhieuChi['id_buySuggest']);
            foreach ($imgBuysuggest as $link => $value) {

                ?>
                <div class="receiptItem">
                    <a href="<?php echo $value['link']; ?>" target="_blank">
                        <div class="subImg">
                            <img src="<?php print_r($value['link']) ?>" alt="">
                        </div>
                        <div class="subACT">
                            <input type="hidden" name="ImgDel" value="<?php print_r($value['link']) ?>">
                            <a href="<?php echo $value['link']; ?>" target="_blank">Chi tiết</a>

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
$(document).ready(function () {
    $('#fileInputaddReceipt').change(function () {
        $('#addReceipForm').submit();
    });
});

$("#loaichi_muahang").on('input',
    '.cal_row input[name="quantity[]"], .cal_row input[name="price[]"], .cal_row input[name="tax[]"]',
    function () {
        updateSubtotal($(this).closest('.cal_row'));
    });
$("#loaichi_chikhac").on('input', '.cal_row input[name="price[]"], .cal_row input[name="tax[]"]', function () {
    updateSubtotal($(this).closest('.cal_row'));
});
$("#loaichi_tamung").on('input', '.cal_row input[name="price[]"]', function () {
        updateSubtotal($(this).closest('.cal_row'));
});
$("#loaichi_tamungluong").on('input', '.cal_row input[name="price[]"]', function () {
    updateSubtotal($(this).closest('.cal_row'));
});
updateSubtotal($('#loaichi_tamung .cal_row'));
function updateSubtotal(row) {
    var quantity = parseFloat(row.find("input[name='quantity[]']").val()) || 1;
    var price = parseFloat(row.find("input[name='price[]']").val()) || 0;
    var tax = parseFloat(row.find("input[name='tax[]']").val()) || 0;

    var subtotal = quantity * price + (quantity * price * tax / 100);
    row.find("input[name='subTotal[]']").val(subtotal);
    calculator_Money();

}

function actionRow(id, html) {
    $(id).on('click', '#del_cal_row', function () {
        $(this).closest('.cal_row').remove();
        calculator_Money();
    });

    $(id).on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var newRow = $(html);
            $(id).append(newRow);
            newRow.find('input[name="prodName[]"]').focus();
            calculator_Money();
        }
    });
}

    var html_CMK =
        '<div class="cal_row"><input type="text" style="width: 30%;" name="prodName[]" placeholder="Mục đích chi" ><input type="text" style="width: 8%;" name="price[]" placeholder="Số tiền" ><input type="hidden" name="quantity[]" value="1"><input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế" ><input type="text" style="width: 20%;" name="subTotal[]"  disabled><button type="button" id="del_cal_row">X</button></div>';
    actionRow('#loaichi_chikhac', html_CMK);
    var html_CMH =
        '<div class="cal_row"><input type="text" style="width: 30%;" name="prodName[]" placeholder="Sản phẩm" autofocus ><input type="text" style="width: 8%;" name="quantity[]" placeholder="Số lượng" ><input type="text" style="width: 10%;" name="price[]" placeholder="Đơn giá" ><input type="text" style="width: 8%;" name="tax[]" placeholder="Thuế" ><input type="text" style="width: 20%;" name="subTotal[]"  disabled><button type="button" id="del_cal_row">X</button></div>';
    actionRow('#loaichi_muahang', html_CMH);

function calculator_Money() {
    var totalSumInput = document.getElementById("totalMoney");
    var sqlTotal = document.getElementById("sqlTotal");
    var allCellMoney = document.getElementsByName("subTotal[]");
    var sum = 0;

    for (var i = 0; i < allCellMoney.length; i++) {
        var value = parseFloat(allCellMoney[i].value.replace(/,/g, ''));
        if (!isNaN(value)) {
            sum += value;
        }
    }
    const formatter = new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    });

    totalSumInput.innerText = formatter.format(sum);
    sqlTotal.value = sum;
}

calculator_Money();



    function resetInputValues(divId) {
        var divElement = document.getElementById(divId);

        if (divElement) {
            var inputElements = divElement.getElementsByTagName('input');

            for (var i = 0; i < inputElements.length; i++) {
                if (inputElements[i].type !== 'button' && inputElements[i].type !== 'submit' && inputElements[i].type !==
                    'reset') {
                    inputElements[i].value = '';
                }
            }
        }
    }

    function changeCalTable() {
        let loaichi = document.getElementById('loaichi');
        let calcForm = document.querySelectorAll('.bodyofForm.calculator');
        document.querySelector('.totalMoney').style.display = 'none'
        resetInputValues('CalMoney_typeLoaichi');
        i = 0;
        for (i = 0; i < calcForm.length; i++) {
            calcForm[i].style.display = 'none';
        }
        if (loaichi.value === 'Chi khác') {
            document.getElementById('loaichi_chikhac').style.display = 'flex';
            document.querySelector('.totalMoney').style.display = 'flex'

        } else if (loaichi.value === 'Chi tạm ứng') {
            document.getElementById('loaichi_tamung').style.display = 'flex';
        } else if (loaichi.value === 'Chi tạm ứng lương') {
            document.getElementById('loaichi_tamungluong').style.display = 'flex';
        } else {
            document.getElementById('loaichi_muahang').style.display = 'flex';
            document.querySelector('.totalMoney').style.display = 'flex'

        }
    }

</script>

<?php
exit();
?>