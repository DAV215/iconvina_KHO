<?php 

    if(isset($_POST['addDXM'] ) ){
        $nameproject = $_POST['nameproject'];
        $namebuyer = $_POST['namebuyer'];
        $nameDXM = $_POST['nameDXM'];
        $quytrinhmuahang = $_POST['quytrinhmuahang'];
        $money = $_POST['money'];
        $daySuggest = $_POST['daySuggest'];
        $supervisor = $_POST['supervisor'];
        $supplier_name = $_POST['supplier_name'];
        $bool_VAT = checkValue('bool_VAT')?1:0;
        $suppiler_phone = checkValue('suppiler_phone');
        $suppiler_add = checkValue('suppiler_add');
        $note = checkValue('note');
        if($_SESSION['boolUser']){
            $id_buyer =  $_SESSION['userINFO']['id'];
        }else{
            $id_buyer = 210520;
        }

        $sqlAddDMX = "INSERT INTO `tbl_buysuggest` 
        (
            `id_buyer`,
            `namebuyer`, 
            `nameDXM`, 
            `money`, 
            `quytrinh`, 
            `daySuggest`, 
            `supervisor`, 
            `nameproject`, 
            `supplier_name`, 
            `suppiler_phone`, 
            `suppiler_add`, 
            `bool_VAT`, 
            `note`
        ) 
        VALUES 
        (
            '$id_buyer', 
            '$namebuyer', 
            '$nameDXM', 
            '$money', 
            '$quytrinhmuahang', 
            '$daySuggest', 
            '$supervisor', 
            '$nameproject', 
            '$supplier_name', 
            '$suppiler_phone', 
            '$suppiler_add', 
            '$bool_VAT', 
      
            '$note'
        )";
        $queryAddUse = mysqli_query($mysqli, $sqlAddDMX);
        $sql = "SELECT `id` FROM `tbl_buysuggest` WHERE `namebuyer` = '$namebuyer' ORDER BY `id` DESC LIMIT 1";

        $query = mysqli_query($mysqli, $sql);
        $id_newestBuggest = mysqli_fetch_array($query)['id'];

        $linkImg = [];
        $imgurData = array(); 
        $coutImage = count(array_filter($_FILES['imgHoaDon']['tmp_name']));
        $pathImgTemp = "QLDXM/media/";
        removeImgTemp($pathImgTemp);
        if ($coutImage == 1 ) {
            $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][0];
            $linkImg = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $nameDXM.$daySuggest.$namebuyer);
            insertImgtoDB($id_newestBuggest, $linkImg);
        }
        elseif ($coutImage > 1){
            $link ;
            for ($i=0; $i < $coutImage; $i++) { 
                $uploadedFilePath = $_FILES['imgHoaDon']['tmp_name'][$i];
                $link = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $nameDXM . $daySuggest.$namebuyer);
                insertImgtoDB($id_newestBuggest, $link);
            
            }
        }else{
            echo "không có ảnh";
            echo '<script>alert("Nhập hóa đơn");</script>'; 
        }
        echo "<meta http-equiv='refresh' content='0'>";
    }
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
        chmod($pathImgTemp, 0755);

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
    
    // include('QLNS/getdataUser.php');
    $userInfo = getPersonnel($_SESSION['username_Login']);
    $getBuySuggest = new getBuySuggest();
    $ranCode = $getBuySuggest->get1Data('ranCode');
?>

<h1>Tạo đề xuất</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userForm">
        <div class="avtGrid">
            <div class="avatar-upload">
                <div class="avatar-edit">
                    <input type='file' name="imgHoaDon[]" id="avt" accept=".png, .jpg, .jpeg" multiple />
                    <label for="avt"></label>
                </div>
                <div class="avatar-preview">
                    <div id="firstImg"></div>
                </div>
            </div>
            <button type="button"  id="addMoreImg" style="display:none;" onclick="openModal('modalReceipt')">Thêm nhiều
                hóa đơn</button>
            <button type="submit" name="addDXM"
                style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Tạo đề xuất mua</button>
        </div>
        <div class="mainForm">
            <div class="inforForm">
                <h2 class="nameForm">Thông tin cơ bản</h2>
                <div class="bodyofForm">
                    <input type="hidden" name="namebuyer" value="<?php echo $userInfo['fullname']; ?>" required>
                    <div class="inputHaveLable">
                        <label for="nameproject">Mục đích</label>
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
                        <label for="money">Tổng tiền</label>
                        <input type="text" onkeypress="CurrencyFormat(this)" name="money" placeholder="Tổng tiền"
                            required>
                    </div>

                    <div class="inputHaveLable">
                        <label for="daySuggest"> Ngày đề xuất</label>
                        <input type="datetime-local" name="daySuggest" value="<?php echo date("Y-m-d\TH:i:s", strtotime("+6 hours")); ?>">
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Người đồng kiểm</label>
                        <input type="text" list="suggestSupervisor" name="supervisor">
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
                        <input type="text" list="suggestSupplier" name="supplier_name">
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
                        <input type="checkbox" name="bool_VAT">
                    </div>
                </div>
            </div>
            <div class="inforForm">
                <h2 class="nameForm">Thông tin bổ sung</h2>
                <div class="bodyofForm">
                    <input type="text" name="suppiler_phone" placeholder="SDT nhà cung cấp">
                    <input type="text" name="suppiler_add" placeholder="Địa chỉ nhà cung cấp">
                    <textarea name="note" id="" cols="30" rows="10" placeholder="Ghi chú" autocomplete="list"
                        aria-haspopup="true"></textarea>
                    <div class="btn_MB">
                        <input type='file' name="imgHoaDon[]" id="avt" accept=".png, .jpg, .jpeg" multiple />
                        <label for="avt">Thêm hóa đơn</label>
                        <button type="submit" name="addDXM">Nộp đề xuất</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal" id="modalReceipt">
        <article class="modal-container">
            <header class="modal-container-header">
                <h1 class="modal-container-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                        aria-hidden="true">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path fill="currentColor"
                            d="M14 9V4H5v16h6.056c.328.417.724.785 1.18 1.085l1.39.915H3.993A.993.993 0 0 1 3 21.008V2.992C3 2.455 3.449 2 4.002 2h10.995L21 8v1h-7zm-2 2h9v5.949c0 .99-.501 1.916-1.336 2.465L16.5 21.498l-3.164-2.084A2.953 2.953 0 0 1 12 16.95V11zm2 5.949c0 .316.162.614.436.795l2.064 1.36 2.064-1.36a.954.954 0 0 0 .436-.795V13h-5v3.949z" />
                    </svg>

                </h1>
                <button class="icon-button" id="ModalClose" onclick="closeModal('modalReceipt')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path fill="currentColor"
                            d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95L7.05 5.636z" />
                    </svg>
                </button>
            </header>
            <section class="modal-container-body rtf">
                <div id="imagePreview" style="">
                </div>
            </section>
            <footer class="modal-container-footer">
                <label for="avtMore">Thêm hóa đơn</label>
                <input type='file'  id="avtMore" accept=".png, .jpg, .jpeg" multiple />
                <button type="button" class="button is-ghost" onclick="closeModal('modalReceipt')">Đã xong</button>
            </footer>
        </article>
    </div>
</form>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function readURL(input) {
    if (input.files && input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
            let reader = new FileReader();

            reader.onload = function (e) {
                // Append image and delete button
                $('#imagePreview').append(`
                    <div class="image-container">
                        <div class="preview-item" style="background-image: url(${e.target.result});"></div>
                        <button class="delete-button">Delete</button>
                        <input type="file" name="imgHoaDon[]" value="${e.target.result}" style="display: none;">
                    </div>
                `);
            };

            reader.readAsDataURL(input.files[i]);
        }
    }
}

$('#imagePreview').on('click', '.delete-button', function () {
    $(this).closest('.image-container').remove();
});

// Remove corresponding input field when deleting an image
$('#imagePreview').on('click', '.delete-button', function () {
    $(this).closest('.image-container').remove();
});

// Remove corresponding input field when deleting an image
$('#imagePreview').on('click', '.delete-button', function () {
    var container = $(this).closest('.image-container');
    container.remove();
});

$("#avt").change(function() {
    $('#imagePreview').empty(); // Clear previous previews
    readURL(this);
    document.getElementById('addMoreImg').style.display = 'block';
});
$("#avtMore").change(function() {
    // $('#imagePreview').empty(); // Clear previous previews
    readURL(this);
    document.getElementById('addMoreImg').style.display = 'block';
});

function CurrencyFormat(el) {

}
</script>

<?php 
exit();
?>