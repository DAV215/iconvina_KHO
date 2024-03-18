<?php 
    $id_material =  $_GET['id_material'] ;
    $material = material::get_info_Material($id_material);
    $info_Material = info_Material::get_info_Material($id_material);

    if(isset($_POST['save_Modify'])){
        $name = $_POST['name'];
        $position_Material_New = $_POST['position_Material_New'];
        $quantity_Material_New = $_POST['quantity_Material_New'];
        $code_Material_New = $_POST['code_Material_New'];
        $note_Material_New = $_POST['note_Material_New'];


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
        return isset($x) ? $x : '0';

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
                        <input type="text" name="name" placeholder="Tên sản phẩm" required value="<?php echo $material['name'] ?>">
                    </div>
                    <div class="Info_tab">
                        <div class="tab">
                            <button type="button" class="tablinks" onclick="change_tab(event, 'common_info')"
                                id="defaultOpen">Thông tin chung</button>
                            <button type="button" class="tablinks" onclick="change_tab(event, 'detail_info')">Thông tin
                                chi tiết</button>
                        </div>
                        <!-- Tab content -->
                        <div id="common_info" class="tabcontent">
                            <h3>Vị trí</h3>
                            <input type="text" name="position_Material_New" value = "<?php echo isset($info_Material['position'])?$info_Material['position']:''?>">
                            <h3>Số lượng</h3>
                            <input type="text" name="quantity_Material_New" value = "<?php echo $material['quantity'] ?>">
                        </div>

                        <div id="detail_info" class="tabcontent">
                            <div class="part">
                                <h3>Code</h3>
                                <input type="text" name="code_Material_New" value = "<?php echo isset($info_Material['code'])?$info_Material['code']:''?>">
                                <h3>Ghi chú</h3>
                                <input type="text" name="note_Material_New" value = "<?php echo isset($info_Material['note'])?$info_Material['note']:''?>">
                            </div>
                            <div class="part">
                                <label for="img_Material_New"> Thêm File đính kèm</label>
                                <input type="file" name="img_Material_New[]" id="img_Material_New" multiple
                                    onchange="ADD_Img_Material(this)">
                                <div class="preview_IMG" id="img_preview_Material_New">
                                    <?php 
                                    if(isset($info_Material['link_folder'])){
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
                                                        <button type="button" class="delete_ITEM_CT" onclick="del_Img_Material('<?php echo $info_Material['link_folder'].'/'.$value; ?>')">X</button>

                                                    </div>
                                                    <?php
                                                }else{
                                                    ?>
                                                    <div class="sub_preview_Img">
                                                    <a href="<?php echo $path . $value ?>" target="_blank"><?php echo $value ?></a>

                                                        <button type = "button" class="delete_ITEM_CT" onclick="del_Img_Material('<?php echo $info_Material['link_folder'].'/'.$value; ?>')">X</button>

                                                    </div>
                                                    <?php
                                                }
                                            }
    
                                        }
                                    
                                    }

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" name="save_Modify">Lưu</button>

            </div>

        </div>

    </div>
</form>
<script  src = "https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

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
function del_Img_Material(path_Material_Img_DEL){
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
        data: form_data,  // <-- send form data directly
        dataType: 'text',  // <-- what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'post',
        success: function(php_script_response){
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


</script>
