<?php 
    $id_BuySuggest = $_GET['idBuySuggest'];
    if(isset($_POST['addPC'] ) ){
        $nameproject = $_POST['nameproject'];
        $name = $_POST['name'];
        $code = rand();
        $nguoitaolenh =  $_SESSION['userFullname'];
        $quytrinh = $_POST['quytrinhmuahang'];
        $total = $_POST['total'];
        $createDay = $_POST['createDay'];
        $taikhoanchi = $_POST['loaitaikhoan'];
  
        $loaichi = $_POST['loaichi'];
        $note = checkValue('note');
        $id_receiver = getBuySuggestDetail($id_BuySuggest)['id'];
        $prodName = $_POST['prodName'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $tax = $_POST['tax'];
        $bool_VAT = checkValue('bool_VAT')?1:0;
        $sqlADDPC ="INSERT INTO `tbl_phieuchi`(
            `id_buySuggest`,
            `code`,
            `name`,
            `note`,
            `nguoitaolenh`,
            `quytrinh`,
            `createDay`,
            `taikhoanchi`,
            `id_receiver`,
            `nameproject`,
            `loaichi`,
            `total`,
            `bool_VAT`
        )
        VALUES(
            '$id_BuySuggest',
            '$code',
            '$name',
            '$note',
            '$nguoitaolenh',
            '$quytrinh',
            '$createDay',
            '$taikhoanchi',
            '$id_receiver',
            '$nameproject',
            '$loaichi',
            '$total',
            '$bool_VAT'
        )";
        $query = mysqli_query($mysqli, $sqlADDPC);

        $sql = "SELECT `id` FROM `tbl_phieuchi` WHERE `id_buySuggest` = '$id_BuySuggest' ORDER BY `id` DESC LIMIT 1";
        $query = mysqli_query($mysqli, $sql);
        $id_newestPC = mysqli_fetch_array($query)['id'];

        if($loaichi == 'Chi mua hàng'){
            foreach ($prodName as $key => $value) {
                if( $value != null){
                    $sql = "INSERT INTO `tbl_itemdetail_cmh`(`id_phiechi`, `name`, `price`, `quantity`, `tax`) VALUES ('$id_newestPC', '$value', '$price[$key]', '$quantity[$key]', '$tax[$key]')";
                    $query = mysqli_query($mysqli, $sql);
                    
                }
            }
        }elseif($loaichi == 'Chi khác'){
            foreach ($prodName as $key => $value) {
                if($value!=null){
                    $sql = "INSERT INTO `tbl_itemdetail_ckhac`(`id_phiechi`, `name`, `price`, `tax`) VALUES ('$id_newestPC', '$value', '$price[$key]', '$tax[$key]')";
                    $query = mysqli_query($mysqli, $sql);
                   
                }else{
                    continue;
                }
            }
        }elseif($loaichi == 'Chi tạm ứng'){
            foreach ($prodName as $key => $value) {
                if($value!=null){
                    $sql = "INSERT INTO `tbl_itemdetail_tu`(`id_phiechi`, `name`, `price`) VALUES ('$id_newestPC', '$value', '$price[$key]')";
                    $query = mysqli_query($mysqli, $sql);
                    echo $sql;
                   
                }else{
                    continue;
                }
            }
        }
        elseif($loaichi == 'Chi tạm ứng lương'){
            foreach ($prodName as $key => $value) {
                if($value!=null){
                    $sql = "INSERT INTO `tbl_itemdetail_tul`(`id_phiechi`, `name`, `price`)VALUES ('$id_newestPC', '$value', '$price[$key]')";
                    $query = mysqli_query($mysqli, $sql);
                  
                }else{
                    continue;
                }
            }
        }

        $linkImg = [];
        $imgurData = array(); 
        $coutImage = count(array_filter($_FILES['imgPhieuChi']['tmp_name']));
        $pathImgTemp = "QLPC/media/";
        removeImgTemp($pathImgTemp);
        if ($coutImage == 1 ) {
            $uploadedFilePath = $_FILES['imgPhieuChi']['tmp_name'][0];
            $linkImg = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $name.$createDay);
            insertImgtoDB($id_newestPC, $linkImg);
        }
        elseif ($coutImage > 1){
            $link ;
            for ($i=0; $i < $coutImage; $i++) { 
                $uploadedFilePath = $_FILES['imgPhieuChi']['tmp_name'][$i];
                $linkImg = uploadToImgur($uploadedFilePath, 'HDBL_DXM_' . $name.$createDay);
                insertImgtoDB($id_newestPC, $link);
            
            }
        }else{
            echo "không có ảnh";
            echo '<script>alert("Nhập hóa đơn");</script>'; 
        }
        echo "<meta http-equiv='refresh' content='0'>";
    }
    function insertImgtoDB($id, $link){
        include('../config/configDb.php');
        $sql = "INSERT INTO  `tbl_imgphieuchi`(`codePhieuChi`, `link`)
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
    $userInfo = getPersonnel($_SESSION['username_Login']);
?>
<?php 
    $DXM = new getBuySuggest();
    $DXM_Detail = $DXM -> getBuySuggestDetail($id_BuySuggest);
?>
<h1>Tạo Phiếu Chi</h1>
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
                            <option value="<?php echo $project['name'] ?>"><?php echo $project['name'] ?></option>
                            <?php
                            }
                        ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="name">Tên phiếu chi</label>
                        <input type="text" name="name" placeholder="Tên đề xuất"
                            value="<?php echo $DXM_Detail['nameDXM']; ?>" >
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
                        <label for="name">Tên người mua</label>
                        <input type="text" name="namebuyer" value="<?php echo $DXM_Detail['namebuyer']; ?>" required
                            disabled>

                    </div>


                    <div class="inputHaveLable">
                        <label for="createDay"> Ngày tạo lệnh</label>
                        <input type="datetime-local" name="createDay"
                            value="<?php echo date("Y-m-d\TH:i", strtotime("+6 hours")); ?>">
                    </div>
                    <div class="inputHaveLable">
                        <label for=""> Quy trình mua hàng</label>
                        <input type="text" name="quytrinh" value="<?php echo $DXM_Detail['quytrinh']; ?>" required
                            disabled>
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Loại chi</label>
                        <select name="loaichi" id="loaichi" onchange="changeCalTable()">
                            <?php 
                            foreach (getAllLoaiChi() as $row) {
                            ?>
                            <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="inputHaveLable">
                        <label for="supervisor"> Loại Tài khoản</label>
                        <select name="loaitaikhoan" id="loaitaikhoan" value="Tiền Mặt">
                            <?php 
                            foreach (getAllLoaiTaiKhoan() as $row) {
                            ?>
                            <option value="<?php echo $row['name'] ?>"><?php echo $row['name'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="inputHaveLable oneRow">
                        <label for=""> Tổng tiền đề xuất: </label>
                        <input type="text" name="mn" value="<?php echo number_format($DXM_Detail['money']); ?>" 
                            disabled>
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
                    <button type="submit" name="addPC"
                        style="background-image: linear-gradient(147deg, #fe8a39 0%, #fd3838 74%);">Tạo Phiếu
                        chi</button>
                </div>
            </div>


        </div>
    </div>
    <div class="userForm" id="CalMoney_typeLoaichi" style="margin-top:20px;">
        <div class="mainForm">
            <div class="big inforForm">
                <h2 class="nameForm">Chi tiết</h2>
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
                <div class="bodyofForm calculator" id="loaichi_tamungluong">
                    <div class="cal_row">
                        <input type="text" style="width: 30%;" list="allPerson" name="prodName[]"
                            placeholder="Tạm ứng cho">
                        <datalist id="allPerson">
                            <?php 
                                foreach (getAllPersonnel() as $row) {
                                    ?>
                            <option value="<?php echo $row['fullname'] ?>"><?php echo $row['fullname'] ?>
                            </option>
                            <?php
                                    }
                            ?>
                        </datalist>
                        <input type="hidden" name="tax[]" value="0">
                        <input type="hidden" style="width: 8%;" name="quantity[]" value="1">
                        <input type="text" style="width: 30%;" name="price[]" placeholder="Số tiền">
                        <input type="hidden" style="width: 20%;" name="subTotal[]" disabled>

                    </div>
                </div>
                <div class="bodyofForm calculator" id="loaichi_tamung">
                    <div class="cal_row">
                        <input type="text" style="width: 30%;" name="prodName[]" placeholder="Tạm ứng cho"
                            list="allPerson">
                        <datalist id="allPerson">
                            <?php 
                                foreach (getAllPersonnel() as $row) {
                                    ?>
                            <option value="<?php echo $row['fullname'] ?>"><?php echo $row['fullname'] ?>
                            </option>
                            <?php
                                    }
                            ?>
                        </datalist>
                        <input type="hidden" name="quantity[]" value="1">
                        <input type="hidden" name="tax[]" value="0">
                        <input type="text" style="width: 30%;" name="price[]" placeholder="Số tiền">
                        <input type="hidden" style="width: 20%;" name="subTotal[]" disabled>

                    </div>
                </div>
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
                <input type="hidden" name="total" id="sqlTotal">
                <div class="totalMoney" style="display: flex;">Tổng tiền: <h2 id="totalMoney"></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="userForm" id="" style="margin-top:20px; width:90%;">
                <div class="big inforForm upload">
                <div class="uploadImg">
                    <label for="uploadImg">Thêm Hóa đơn</label>
                    <input type="file"name="imgPhieuChi[]"id="uploadImg" multiple="multiple">
                </div>
                <div class="imgContainer" id="HoadonPC">

                </div>
            </div>
    </div>
</form>
<h1>Hóa đơn từ đề xuất mua</h1>
<form action="" method="post">
    <div class="userForm">
        <div class="mainForm receipt">
            <?php
            $imgBuysuggest = getBuySuggest_IMG($id_BuySuggest);
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
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
function readURL(input) {
    if (input.files && input.files.length > 0) {
        for (let i = 0; i < input.files.length; i++) {
            let reader = new FileReader();

            reader.onload = function(e) {
                // Append image and delete button
                $('#HoadonPC').append(`
                    <div class="image-container">
                        <div class="preview-item" style="background-image: url(${e.target.result});"></div>
                        <button class="delete-button">Delete</button>
                        <input type="file" name="imgPhieuChi[]" value="${e.target.result}" style="display: none;">
                    </div>
                `);
            };
            reader.readAsDataURL(input.files[i]);
        }
    }
}
$("#uploadImg").change(function() {
    $('#imagePreview').empty(); // Clear previous previews
    readURL(this);
});
$('#HoadonPC').on('click', '.delete-button', function () {
    var container = $(this).closest('.image-container');
    container.find('input[type="file"]').val('');
    container.remove(); 
});
var parentElement = document.getElementById("CalMoney_typeLoaichi");

// Get all elements with class "bodyofForm" under the parent element
var bodyOfFormElements = parentElement.querySelectorAll(".bodyofForm");

// Set display: none for each element
bodyOfFormElements.forEach(function(element) {
    element.style.display = "none";
});

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
changeCalTable();
$("#loaichi_muahang").on('input',
    '.cal_row input[name="quantity[]"], .cal_row input[name="price[]"], .cal_row input[name="tax[]"]',
    function() {
        updateSubtotal($(this).closest('.cal_row'));
    });
$("#loaichi_chikhac").on('input', '.cal_row input[name="price[]"], .cal_row input[name="tax[]"]', function() {
    updateSubtotal($(this).closest('.cal_row'));
});
$("#loaichi_tamung").on('input', '.cal_row input[name="price[]"]', function () {
            updateSubtotal($(this).closest('.cal_row'));
});
$("#loaichi_tamungluong").on('input', '.cal_row input[name="price[]"]', function () {
    updateSubtotal($(this).closest('.cal_row'));
});
function updateSubtotal(row) {
    var quantity = parseFloat(row.find("input[name='quantity[]']").val()) || 1;
    var price = parseFloat(row.find("input[name='price[]']").val()) || 0;
    var tax = parseFloat(row.find("input[name='tax[]']").val()) || 0;

    var subtotal = quantity * price + (quantity * price * tax / 100);
    row.find("input[name='subTotal[]']").val(subtotal);
    calculator_Money();

}

function actionRow(id, html) {
    $(id).on('click', '#del_cal_row', function() {
        $(this).closest('.cal_row').remove();
        calculator_Money();
    });

    $(id).on('keydown', 'input[name="tax[]"]', function(e) {
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
        var value = parseFloat(allCellMoney[i].value);
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


</script>

<?php 
exit();
?>