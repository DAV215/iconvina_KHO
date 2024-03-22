<?php 
    $id_material =  $_GET['id_material'] ;
    $material = material::get_info_Material($id_material);
    $info_Material = info_Material::get_info_Material($id_material);
    if(Super_detail::getAll('*', " `id_material` = $id_material ")){
        $super_detail = Super_detail::getAll('*', " `id_material` = $id_material ")[0];
        $position_db = isset(Position::getAll('sum','`id` = '.$super_detail['id_position'].'')[0]['sum'])?Position::getAll('sum','`id` = '.$super_detail['id_position'].'')[0]['sum']:'';
        $classify_db = isset(Classify::getAll('sum','`id` = '.$super_detail['id_classify'].'')[0]['sum'])?Classify::getAll('sum','`id` = '.$super_detail['id_classify'].'')[0]['sum']:'';
        $business_db = isset(Business::get_1row('*','`id` = '.$super_detail['id_business'].'')['id'])?Business::get_1row('*','`id` = '.$super_detail['id_business'].''):'';
    }else{
        $super_detail = null;
        $position_db = null;
        $classify_db = null;
        $business_db = ['store'=>'', 'price_buy'=> '0', 'discount'=>'0', 'vat'=>'0', 'delivery_fee'=>'0'];
    }


    if(isset($_POST['save_Modify'])){
        $name = $_POST['name'];
        $position_Material_New = $_POST['position_Material_New'];
        $quantity_Material_New = $_POST['quantity_Material_New'];
        $code_Material_New = $_POST['code_Material_New'];
        $note_Material_New = $_POST['note_Material_New'];

        //superDetail
        $classify = $_POST['classify'];
        if($super_detail){
            if($position_Material_New != null && $position_Material_New != 0){
                $id_position = Position::getAll('`id`', " `sum` = '$position_Material_New' ")[0]['id'];
            }else{
                $id_position = Position::getAll('`id`', " `sum` = 'Chưa xác định' ")[0]['id'];

            }
            if($classify != null && $classify != 0){
                $id_classify = Classify::getAll('`id`', " `sum` = '$classify' ")[0]['id'];
            }else{
                $id_classify = Classify::getAll('`id`', " `sum` = 'Không phân loại' ")[0]['id'];
            }
        }
        else{
            if($position_Material_New != null && $position_Material_New != 0){
                $id_position = Position::getAll('`id`', " `sum` = '$position_Material_New' ")[0]['id'];
            }else{
                $id_position = Position::getAll('`id`', " `sum` = 'Chưa xác định' ")[0]['id'];

            }
            if($classify != null && $classify != 0){
                $id_classify = Classify::getAll('`id`', " `sum` = '$classify' ")[0]['id'];
            }else{
                $id_classify = Classify::getAll('`id`', " `sum` = 'Không phân loại' ")[0]['id'];
            }
        }
        //Business
        $store = $_POST['store'];
        $price_buy = $_POST['price_buy'];
        $delivery_fee = $_POST['delivery_fee'];
        $discount = $_POST['discount'];
        $vat = $_POST['vat'];

        if(!Super_detail::getAll('*', " `type` = 'Material' AND `id_material` = $id_material")){
            Business::addNew($store, $price_buy,$delivery_fee,$discount,$vat);
            print_r( Business::get_1row('MAX(Id)', '  1 '));
            $id_business = Business::get_1row('MAX(Id)', '  1 ')['MAX(Id)'];
            Super_detail::addNew('Material', $id_material, null, $id_classify, $id_position, $id_business);
            Record_KHO_SUPERDETAIL::addNew(Super_detail::getAll('*', " `id_material` = $id_material ")[0]['id'], " Thêm mới ", $_SESSION['userINFO']['fullname']);
        }else{
            $id_business = Business::get_1row('*', '  `id` = '.$business_db['id'].' ')['id'];
            //Record
            $R = new Record_KHO_SUPERDETAIL;
            $R_Pos = $R::checkPosition($position_Material_New, $super_detail['id_position']);
            $R_Class = $R::checkCLassify($classify, $super_detail['id_classify']);
            $R_quantity = $R::checkQuantity_M($quantity_Material_New, $super_detail['id_material']);
            $R_Business = Record_KHO_SUPERDETAIL::checkBus($store, $price_buy,$delivery_fee,$discount,$vat, $id_business);
            if( $R_Pos || $R_Class || $R_Business || $R_quantity){
                Record_KHO_SUPERDETAIL::addNew($super_detail['id'], "Sửa ".$R_Business . $R_Pos . $R_Class. $R_quantity, $_SESSION['userINFO']['fullname']);
            }
            //Done Record
            Business::update($store, $price_buy,$delivery_fee,$discount,$vat, " `id` = $id_business");
            Super_detail::update('Material', $id_material, null, $id_classify, $id_position, $id_business, " `id_material` =  $id_material");

        }
        //processing
        //name & quantity
        $material_modify = new material;
        $material_modify->update($name, $quantity_Material_New, ' `id` = '.$id_material.' ');
        //detail Info
        $info_modify = new info_Material;
        if(info_Material::get_info_Material($id_material) == null){
            $link_folder = $id_material.'_'.$name;
            info_Material::modify_FILE($link_folder, 'img_Material_New');
            $info_modify->addNew($id_material, $position_Material_New, $code_Material_New, $link_folder, $note_Material_New);
       
        }else{
            $link_folder = $id_material.'_'.$name;
            $info_modify->update($position_Material_New, $code_Material_New, $note_Material_New, $link_folder,' `id_item` = '.$id_material.' ');
        }
        
        echo "<meta http-equiv='refresh' content='0'>";

    }
    function checkValue($x){
        if(isset($_POST[$x])){
            return $x = $_POST[$x];
        } else {
            $x = '';
        }
        return $x;
    }
    function checkNull($x) {
        return isset($x) ? $x : '';
    }
    
    
?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <h2>Bổ sung thông tin</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="" class="formLable"> Sản phẩm</label>
                        <input type="text" name="name" placeholder="Tên sản phẩm" required
                            value="<?php echo $material['name'] ?>">
                    </div>
                    <div class="Info_tab">
                        <div class="tab">
                            <button type="button" class="tablinks" onclick="change_tab(event, 'common_info')"
                                id="defaultOpen">Thông tin chung</button>
                            <button type="button" class="tablinks" onclick="change_tab(event, 'detail_info')">Thông tin
                                chi tiết</button>
                            <button type="button" class="tablinks" onclick="change_tab(event, 'special_info')">Thông tin
                                đặc biệt</button>
                        </div>
                        <!-- Tab content -->
                        <div id="common_info" class="tabcontent">
                            <h3>Vị trí</h3>
                            <input type="text" name="position_Material_New" list="getPosition_KHO" 
                                value="<?php echo $position_db; ?>"
                                id="inputPosition"
                                oninput="dataList_setting_Position($('#inputPosition').val(), '#getPosition_KHO')">
                            <datalist id="getPosition_KHO">

                            </datalist>
                            <button type="button"
                                style="width: 60px; max-height: 60px !important; border-radius: 50%; padding: 0; margin: 0; transform: scale(0.8);"
                                onclick="$('.sub.NewPosition').css('display', 'flex');">
                                <i class="fa-solid fa-plus"></i>
                            </button>

                            <div class="sub NewPosition">
                                <input type="text" name="storage" placeholder="Kho">
                                <input type="text" name="row" placeholder="Hàng">
                                <input type="text" name="col" placeholder="Cột">
                                <input type="text" name="shelf_level" placeholder="Vị trí trên kệ">
                                <summary>*Cách điền vị trí tương đối: Kho 1, hàng Ngoài sân => Lưu</summary>
                                <button type="button" style="width: 20%;" onclick="new_setting_Position_2(this, '.NewPosition');$('.sub.NewPosition').css('display', 'none');">Lưu
                                    vị trí mới</button>
                            </div>
                            <h3>Số lượng</h3>
                            <input type="text" name="quantity_Material_New" value="<?php echo $material['quantity'] ?>">
                        </div>

                        <div id="detail_info" class="tabcontent">
                            <div class="part">
                                <h3>Code</h3>
                                <input type="text" name="code_Material_New"
                                    value="<?php echo isset($info_Material['code'])?$info_Material['code']:''?>">
                                <h3>Phân loại</h3>
                                <input type="text" name="classify" id="inputClassify" list="getClassify_KHO"
                                value="<?php echo $classify_db ?>"
                                    oninput="dataList_setting_Classify($('#inputClassify').val(), '#getClassify_KHO')">
                                <datalist id="getClassify_KHO">

                                </datalist>
                                <button type="button"
                                style="width: 60px; max-height: 60px !important; border-radius: 50%; padding: 0; margin: 0; transform: scale(0.8);"
                                onclick="$('.sub.NewClassify').css('display', 'flex');">
                                <i class="fa-solid fa-plus"></i>
                            </button>

                            <div class="sub NewClassify">
                                <input type="text" name="mainClass" placeholder="Danh mục chính">
                                <input type="text" name="subClass" placeholder="Danh mục phụ">
                                <input type="text" name="note" placeholder="Ghi chú">
                                <summary>*Cách điền vị trí tương đối: Kho 1, hàng Ngoài sân => Lưu</summary>
                                <button type="button" style="width: 20%;" onclick="new_setting_Classify_2(this, '.NewClassify'); $('.sub.NewClassify').css('display', 'none');">Lưu
                                    danh mục mới</button>
                            </div>
                                <h3>Ghi chú</h3>
                                <input type="text" name="note_Material_New"
                                    value="<?php echo isset($info_Material['note'])?$info_Material['note']:''?>">
                            </div>
                            <div class="part">
                                <label for="img_Material_New"> Thêm File đính kèm</label>
                                <input type="file" name="img_Material_New[]" id="img_Material_New" multiple
                                    onchange="ADD_Img_Material(this)">
                                <div class="preview_IMG" id="img_preview_Material_New">
                                    <?php 
                                    if(isset($info_Material['link_folder']) && $info_Material['link_folder'] != null){
                                        if(is_dir('QLKHO/MEDIA/material'.'/' .$info_Material['link_folder'].'/')){
                                            $images = array('jpg','png','jpeg','gif');
                                            $path = 'QLKHO\MEDIA\material'.'/' .$info_Material['link_folder'].'/';
                                            $files = scandir( $path);
                                            foreach($files as $key => $value) {
                                                if($value != '.' && $value != '..'){
                                                    $ext = pathinfo($value, PATHINFO_EXTENSION);
                                                    if(in_array($ext,$images)) {
                                                        ?>
                                        <div class="sub_preview_Img">
                                            <img src="<?php echo $path.$value ?>" alt="">
                                            <button type="button" class="delete_ITEM_CT"
                                                onclick="del_Img_Material('<?php echo $info_Material['link_folder'].'/'.$value; ?>')">X</button>
    
                                        </div>
                                        <?php
                                                    }else{
                                                        ?>
                                        <div class="sub_preview_Img">
                                            <a href="<?php echo $path . $value ?>" target="_blank"><?php echo $value ?></a>
    
                                            <button type="button" class="delete_ITEM_CT"
                                                onclick="del_Img_Material('<?php echo $info_Material['link_folder'].'/'.$value; ?>')">X</button>
    
                                        </div>
                                        <?php
                                                    }
                                                }
        
                                            }
                                        }   else  info_Material::createFolder($info_Material['link_folder']);
                                    }

                                    ?>
                                </div>
                            </div>
                        </div>
                        <div id="special_info" class="tabcontent">
                            <div class="sub " style="display:flex;">
                                <h3>Xuất xứ</h3>
                                <input type="text" name="store" placeholder="Xuất xứ" value="<?php echo checkNull($business_db['store']) ?>">
                                <h3>Giá vào</h3>
                                <input type="text" name="price_buy" placeholder="Giá vào" value="<?php echo $business_db['price_buy'] ?>">
                                <h3>Phí vận chuyển về</h3>
                                <input type="text" name="delivery_fee" placeholder="Phí vận chuyển về" value="<?php echo $business_db['delivery_fee'] ?>">
                                <h3>% Có thể giảm</h3>
                                <input type="text" name="discount" placeholder="% Có thể giảm" value="<?php echo $business_db['discount'] ?>">
                                <h3>% VAT</h3>
                                <input type="text" name="vat" placeholder="VAT" value="<?php echo $business_db['vat'] ?>">
                            </div>
                            <h3>Giá thành: <?php $noVat = $business_db['price_buy']+$business_db['delivery_fee']-$business_db['discount']; echo number_format($noVat+$noVat*$business_db['vat']/100) ?> </h3>
                        </div>
                    </div>
                </div>
                <button type="submit" name="save_Modify">Lưu</button>

            </div>

        </div>

    </div>
</form>

<h3>Lịch sử thay đổi:</h3>
<summary>
<?php 
        if(isset($super_detail['id'])){
            $i = $super_detail['id'];
            $v = Record_KHO_SUPERDETAIL::get_1row('*'," id_superDetail = $i  ");
            echo $v['note'].' By:  '.$v['addBy']; 
        }
    ?>
</summary>
<div class="inforForm ">
<button onclick="toggleVisibility('#history')"><?php echo isset($super_detail['id'])?'Xem lịch sử thay đổi':'Chưa tạo Super Detail'; ?></button>
</div>
<?php 
        if(isset($super_detail['id'])){
            ?>
<div class="tableComponent" id="history" style="display:none;">
    <table class="data_table">
        <thead>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
                    <th>Tên</th>
                    <th>Note</th>
                    <th>Thời gian</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody id="tbody_history_Material_Change">

        </tbody>
    </table>
    <div class="Pagination" id="">

    </div>
</div>
            <?php
        }
    ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="../asset/js/KHO/settingKho.js"></script>
<script>

</script>
<script>
function change_tab(event, nameTab) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = 'none';
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("active", "");
    }
    document.getElementById(nameTab).style.display = "block";
    event.currentTarget.className += " active";
}
document.getElementById("defaultOpen").click();

function openFileInNewTab(fileUrl) {
    // Open the file in a new tab using JavaScript
    window.open(fileUrl, '_blank');
}

function del_Img_Material(path_Material_Img_DEL) {
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "text",
        data: {
            path_Material_Img_DEL: path_Material_Img_DEL
        },
        success: function(data) {
            alert(data);
            if (<?php echo info_Material::get_info_Material($id_material) == null ? '0' : '1' ?>) {
                location.reload();
            }

        },
        error: function() {
            alert('Error in AJAX request');
        }
    });
}

function ADD_Img_Material(file) {
    let file_data = [];
    let form_data = new FormData();
    form_data.append('name_folder_material_modify', '<?php echo $material['id'].'_'.$material['name'] ; ?>');
    form_data.append('id_material', '<?php echo $material['id']; ?>');
    form_data.append('name_material', '<?php echo $material['name']; ?>');

    for (let i = 0; i < $('#img_Material_New').prop('files').length; i++) {
        file_data.push($('#img_Material_New').prop('files')[i]);
        form_data.append('file_material_add[]', file_data[i]);
    }

    $.ajax({
        url: "QLKHO/code/getdata_Kho.php",
        data: form_data, // <-- send form data directly
        dataType: 'text', // <-- what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'post',
        success: function(php_script_response) {
            alert(php_script_response);
            if (<?php echo info_Material::get_info_Material($id_material) == null ? '0' : '1' ?>) {
                location.reload();
            }

        },
        error: function() {
            alert('Error in AJAX request');
        }
    });
}
function showRecord(){
    $.ajax({

        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Record_Material_Detail: 1,
            id_MDetail_Record: <?php echo isset($super_detail['id'])?$super_detail['id']:'0' ?>
        }, // <-- send form data directly
        dataType: 'JSON', // <-- what to expect back from the PHP script, if anything
        type: 'post',
        success: function(result) {
            for (let i = 0; i < result.length; i++) {
                let m = result[i];
                let str = `
                    <tr>
                        <td>${i+1}</td>
                        <td>${m['addBy']}</td>
                        <td style="width:50%;">${m['note']}</td>
                        <td>${m['time']}</td>
                        <td class="tacvu">
                            <a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=${m['id']}">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                `;
                // Append the row to the tbody
                $("#tbody_history_Material_Change").append(str);
            }
        },
        error: function() {
            alert('Error in AJAX request');
        }
    });
}
showRecord();
function toggleVisibility(id) {
    if ($(id).css('display') === 'none') {
        $(id).css('display', 'block');
    } else {
        $(id).css('display', 'none');
    }
}
</script>