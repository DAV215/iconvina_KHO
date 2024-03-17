<?php 
    $material = new material;
    $component = new component;
    $info_Material = new info_Material;
    if(isset($_POST['addComponent'])){
        $id_parent = $_POST['id_parent'];
        $name_parent = $_POST['name_parent'];
        $name = $_POST['name'];
    }
    if(isset($_POST['save'])){
        if($_POST['type_relation'] == 'Material'){
            $name = $_POST['name'];
            $position_Material_New = $_POST['position_Material_New'];
            $quantity_Material_New = $_POST['quantity_Material_New'];
            $code_Material_New = $_POST['code_Material_New'];
            $note_Material_New = $_POST['note_Material_New'];
            $_FILES['img_Material_New_fix']['tmp_name'];

            $material->addNew($name, $quantity_Material_New);
            $sql = 'WHERE `name` = "'.$name.'" AND `quantity` = '.$quantity_Material_New.' ORDER BY `id` DESC';
            $id_Material_New =  $material->get_1row_Material($sql)['id'];
            $link_folder = $id_Material_New.'_'.$name;
            info_Material::upload_Files($link_folder, 'img_Material_New_fix');
            $info_Material->addNew($id_Material_New, $position_Material_New, $code_Material_New, $link_folder, $note_Material_New);
        }
        if($_POST['type_relation'] == 'Component'){
            //Material
            $quantity_Component_need = isset($_POST['quantity_Component_need'])?$_POST['quantity_Component_need']:0;
            $name_Material = $_POST['name_Material'];
            $quantity_Material_need = $_POST['quantity_Material_need'];
            $id_material = $_POST['id_material'];
            //get value Component 
            $level_component = $_POST['level_component'];
            $id_component = $_POST['id_component'];
            $name_Component= $_POST['name_Component'];
            $quantity_Component_need= $_POST['quantity_Component_need'];
            //name of parent
            $name =  $_POST['name'];
            if( $name_Component[0] == null){
                $component->addNew($name,0,0,1,0,0);
                $id_parent = $component->get_Newest_Component($name,1);
                foreach($name_Material as $key => $value){
                    if($value != null){
                        $component->addNew($value,$id_parent,$id_material[$key],0,$quantity_Material_need[$key],$name);
                    }
                }
            }else{
                var_dump(max($level_component));
                $level_parent_ADD = max($level_component)+1;
                $component->addNew($name,0,0,$level_parent_ADD,0,0);
                $id_parent_ADD = $component->get_Newest_Component($name,$level_parent_ADD);
                if($name_Material[0] != null){
                    foreach($name_Material as $key => $value){
                        if($value != null){
                            $component->addNew($value,$id_parent_ADD,$id_material[$key],0,$quantity_Material_need[$key],$name);
                        }
                    }
                }
                foreach($name_Component as $key => $value){
                    if($value != null){
                        $component->addNew($value,$id_parent_ADD,$id_component[$key],$level_component[$key],$quantity_Component_need[$key],$name);
                    }
                }
            }
        }
    }
?>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <h2>Thêm</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="" class="formLable"> Sản phẩm</label>
                        <input type="text" name="name" placeholder="Tên sản phẩm" required>
                    </div>
                    <select name="type_relation" id="" onchange="hide_div_Material()">
                        <option value="Material" selected>Material</option>
                        <option value="Component">Component</option>
                    </select>
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
                            <input type="text" name="position_Material_New">
                            <h3>Số lượng</h3>
                            <input type="text" name="quantity_Material_New">
                        </div>

                        <div id="detail_info" class="tabcontent">
                            <div class="part">
                                <h3>Code</h3>
                                <input type="text" name="code_Material_New">
                                <h3>Ghi chú</h3>
                                <input type="text" name="note_Material_New">
                            </div>
                            <div class="part">
                                <label for="img_Material_New"> Thêm File đính kèm</label>
                                <input type="file" name="img_Material_New_fix[]" id="img_Material_New" multiple
                                    onchange="preview_IMG(this, 'img_preview_Material_New')">
                                <div class="preview_IMG" id="img_preview_Material_New">
                                    <img src="" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bodyofForm Material" id="table_material_CT">
                    <h3>Vật liệu thô</h3>
                    <div class="item_CT">
                        <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')"
                            onchange="show_value_Storage_Material(this, getdata_Material())"
                            list="ALL_data_material">
                        <datalist id="ALL_data_material">

                        </datalist>
                        <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')">
                        <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                        <input type="hidden" name="id_material[]">
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.item_CT')">X</button>
                    </div>
                </div>
                <div class="bodyofForm Material" id="table_component_CT">
                    <h3>Component</h3>
                    <div class="component_CT">
                        <input type="text" name="name_Component[]" placeholder="Sản phẩm con" list="ALL_data_Component"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                            onchange="show_value_Storage_Component(this)">
                        <datalist id="ALL_data_Component">

                        </datalist>
                        <input type="number" name="quantity_Component_need[]" placeholder="Số lượng"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')">
                        <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')">
                        <input type="hidden" name="id_component[]">
                        <input type="hidden" name="level_component[]">
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.component_CT')">X</button>
                    </div>
                </div>
                <button type="submit" name="save">Lưu</button>

            </div>

        </div>

    </div>
</form>

<script>
function hide_div_Material() {
    var type_relation = document.querySelector('select[name=type_relation]').value;
    let div_Material = document.querySelectorAll('.Material');
    if (type_relation == 'Component') {
        for (i = 0; i < div_Material.length; i++) {
            div_Material[i].style.display = 'flex';
        }
        document.querySelector('.Info_tab').style.display = 'none';

    } else {
        for (i = 0; i < div_Material.length; i++) {
            div_Material[i].style.display = 'none';
        }
        document.querySelector('.Info_tab').style.display = 'flex';

    }
}

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

function addROW(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent the default Enter key behavior

        // Clone the last item_CT div
        var newItemCal = document.querySelector(".item_CT:last-child").cloneNode(true);

        // Clear the input values in the cloned div
        newItemCal.querySelectorAll("input").forEach(function(input) {
            input.value = "";
        });

        // Get the container element by ID
        var container = document.getElementById("table_CT");

        // Check if the container element exists before appending
        if (container) {
            // Append the cloned div to the container
            container.appendChild(newItemCal);
        } else {
            console.error("Container element not found");
        }
    }
}

function addROW_(event, class_Append, Id_container) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent the default Enter key behavior

        // Get the collection of elements with the specified class
        let c = document.getElementsByClassName(class_Append);

        // Get the container element by ID
        let container = document.getElementById(Id_container);

        // Check if the container element and at least one element with the specified class exist
        if (container && c.length > 0) {
            // Clone the last element with the specified class
            var newItemCal = c[c.length - 1].cloneNode(true);

            // Clear the input values in the cloned div
            newItemCal.querySelectorAll("input").forEach(function(input) {
                input.value = "";
            });

            // Append the cloned div to the container
            container.appendChild(newItemCal);
            newItemCal.querySelector("input").focus();
        } else {
            console.error("Container element not found or no elements with the specified class");
        }
    }
}

function delROW(button) {
    let s = document.querySelectorAll('.item_CT');
    if (s.length > 1) {
        $(button).closest('.item_CT').remove();
    } else {
        alert('Không cần xóa');
    }
}

function delROW_(button, classname) {
    let s = document.querySelectorAll(classname);
    if (s.length > 1) {
        $(button).closest(classname).remove();
    } else {
        alert('Không cần xóa');
    }
}
document.getElementById("defaultOpen").click();
hide_div_Material();

var timeoutId;
var data_Material ;
function getdata_Material() {

        $.ajax({
            type: "POST",
            url: "QLKHO/code/getdata_Kho.php",
            dataType: "JSON",
            data: {
                type: 'material'
            },
            success: function(data) {
                // Assuming input is a datalist element
                data_Material = data;
                var datalist = document.getElementById('ALL_data_material');
                datalist.innerHTML = ""; // Clear existing options
                // Populate the datalist with the "name" values from the materials
                for (var count = 0; count < data.length; count++) {
                    var option = document.createElement("option");
                    option.value = data[count].name;
                    datalist.appendChild(option);
                }

            }
        });
   
    return data_Material;
}


getdata_Material();

function show_value_Storage_Material(input, data) {
    let idInput = $(input).closest('.item_CT').find('input[name="id_material[]"]');
    let storageInput = $(input).closest('.item_CT').find('input[name="quantity_Component_inStorage"]');
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        storageInput.val(chooseName.quantity);
    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
    }
}

// show_Result('component','ALL_data_Component');

function show_value_Storage(input, className, idInputName, saveROW_data, data) {
    let storageInput = $(input).closest(className).find('input[name="quantity_Component_inStorage"]');
    let idInput = $(input).closest(className).find('input[name="' + idInputName + '"]');
    let Rowdata = $(input).closest(className).find('input[name="' + saveROW_data + '"]');
    let chooseName = data.find(item => item.name === input.value);

    if (chooseName) {
        storageInput.val(chooseName.quantity);
        idInput.val(chooseName.id);
        Rowdata.val(chooseName);
    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
        Rowdata.val("");
    }
}
var returnData;

function getdata_COMPONENT() {

    // Clear the previous timeout (if any)

        $.ajax({
            type: "POST",
            url: "QLKHO/code/getdata_Kho.php",
            dataType: "JSON",
            data: {
                type: 'component'
            },
            success: function(data) {
                // Assuming input is a datalist element
                let datalist = document.getElementById('ALL_data_Component');
                returnData = data;
                datalist.innerHTML = ""; // Clear existing options
                // Populate the datalist with the "name" values from the materials

                for (var count = 0; count < data.length; count++) {
                    var option = document.createElement("option");
                    option.value = data[count].name;
                    datalist.appendChild(option);
                }
            }
        });
        return returnData;
}
getdata_COMPONENT();

function show_value_Storage_Component(input) {
    let idInput = $(input).closest('.component_CT').find('input[name="id_component[]"]');
    let level = $(input).closest('.component_CT').find('input[name="level_component[]"]');
    let quantity = $(input).closest('.component_CT').find('input[name="quantity_Component_inStorage"]');
    let data = getdata_COMPONENT() ;
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        level.val(chooseName.level);
        quantity.val(chooseName.quantity);
    } else {
        idInput.val("0"); // Clear the id value as well
        quantity.val("0"); // Clear the id value as well
    }
}

function preview_IMG(input, id_Container) {
    let container = document.getElementById(id_Container);
    container.innerHTML = '';

    for (let i = 0; i < input.files.length; i++) {

        if (getIconForFileType(input.files[i].name) == 'image') {
            let image = document.createElement('img');
            image.src = URL.createObjectURL(input.files[i]);
            image.style.maxWidth = '30%'; // Optional: Set max width for the preview
            container.appendChild(image);
        } else {
            let filenameElement = document.createElement('span');
            filenameElement.textContent = input.files[i].name;
            // Make the filename clickable to open the file
            filenameElement.style.cursor = 'pointer';
            filenameElement.onclick = function() {
                window.open(URL.createObjectURL(input.files[i]), '_blank');
            };
            container.appendChild(filenameElement);
        }

    }
}

function getIconForFileType(fileName) {
    let extension = fileName.split('.').pop().toLowerCase();

    // Add more file type mappings as needed
    switch (extension) {
        case 'pdf':
            return 'pdf';
        case 'doc':
        case 'docx':
            return 'word';
        case 'xls':
        case 'xlsx':
            return 'excel';
        case 'ppt':
        case 'pptx':
            return 'powerpoint';
        case 'zip':
        case 'rar':
            return 'archive';
        case 'png':
        case 'jpg':
        case 'jpeg':
        case 'gif':
            return 'image';
        default:
            return 'file'; // Default icon for other file types
    }
}
</script>