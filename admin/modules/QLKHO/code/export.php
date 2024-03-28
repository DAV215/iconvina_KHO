<?php 
    $material = new material;
    $component = new component;
    $info_Material = new info_Material;
    if(isset($_POST['save_export'])){
        //common info
        $all = [];
        $name_export = isset($_POST['name_export'])?$_POST['name_export']:'';
        $note = isset($_POST['note'])?$_POST['note']:'';
        $purpose = isset($_POST['purpose'])?$_POST['purpose']:'';
        $id_prod_cmd = isset($_POST['id_prod_cmd'])?$_POST['id_prod_cmd']:null;
        $created_by = $_SESSION['userINFO']['fullname'];

        if(isset($_POST['id_material']) || isset($_POST['id_component'])){
        //Material
        $quantity_Material_need = isset($_POST['quantity_Material_need'])?$_POST['quantity_Material_need']:0;
        $name_Material = $_POST['name_Material'];
        $price_Material = $_POST['price_Material'];
        $id_material = $_POST['id_material'];
        //get value Component 
        $level_Component = $_POST['level_component'];
        $id_Component = $_POST['id_component'];
        $name_Component= $_POST['name_Component'];
        $quantity_Component_need= $_POST['quantity_Component_need'];     
        $price_Component= $_POST['price_Component'];     

        // create new export
        $id_prod_cmd = ($purpose=='Sản xuất nội bộ')?substr($name_export, 0, strpos($name_export, ' - ')):null;
        export_material::addNew($created_by, $name_export, $note, $purpose, $id_prod_cmd);

        $id_export = export_material::get_1row('*', " `name` = '$name_export' AND `created_by` = '$created_by'  ")['id'];

        if(isset($_POST['id_material']) && ( $_POST['name_Material'][0] != '' &&  $_POST['name_Material'][0] != null)){
            foreach ($id_material as $key => $value) {
                $old_quantity = material::get_info_Material($value)['quantity'];
                export_material_detail::addNew2(  $id_export, 'Material', $value, null , $quantity_Material_need[$key], $price_Material[$key]);
                $quantity = floatval($quantity_Material_need[$key]) * -1;
                material::update_quantity($quantity, $value);
        
                if(!isset(Super_detail::getAll('*', " `id_material` = $value ")[0]['id'])){
                    //Material - id_material - 0 - Class chưa phân loại - Vị trí chưa xác định - info kinh doanh chưa nhập;
                    Super_detail::addNew('Material', $value, 0, 12, 17, 39);
                }
                Record_KHO_SUPERDETAIL::addNew(Super_detail::getAll('*', " `id_material` = $value ")[0]['id'], 'Xuất kho', $old_quantity, $old_quantity+$quantity, $_SESSION['userINFO']['fullname']);
            }
        }
        if(isset($_POST['id_component']) && ( $_POST['name_Component'][0] != '' &&  $_POST['name_Component'][0] != null)){
            foreach ($id_Component as $key => $value) {
                $old_quantity = info_Component::get_info_Component($value)['quantity'];
                export_material_detail::addNew2(  $id_export, 'Component', null, $value , $quantity_Component_need[$key], $price_Component[$key]);
                $quantity = floatval($quantity_Component_need[$key]) * -1;
                info_component::update_quantity($quantity, $value);
                if(!isset(Super_detail::getAll('*', " `id_component` = $value ")[0]['id'])){
                    //Material - id_material - 0 - Class chưa phân loại - Vị trí chưa xác định - info kinh doanh chưa nhập;
                    Super_detail::addNew('Component',null, $value, 12, 17, 39);
                }
                Record_KHO_SUPERDETAIL::addNew(Super_detail::getAll('*', " `id_component` = $value ")[0]['id'], 'Xuất kho',$old_quantity ,$old_quantity+$quantity, $_SESSION['userINFO']['fullname']);
            }
        }
        } 

    }


?>
<h1>Xuất kho</h1>

<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <h2>Material</h2>
                <div class="bodyofForm Material" id="table_material_CT">
                    <div class="item_CT">
                        <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                            onchange="show_value_Storage_Material(this, getdata_Material())"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')"
                            list="ALL_data_material">
                        <datalist id="ALL_data_material">

                        </datalist>

                        <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')">

                        <input type="number" name="price_Material[]" placeholder="Giá tiền"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')">
                            
                        <input type="number" name="price_Material_Storage" placeholder="Giá bán đề xuất"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')" disabled>
                        <input type="hidden" name="id_material[]">
                        <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.item_CT')">X</button>
                    </div>
                </div>
                <h2>Component</h2>

                <div class="bodyofForm Component" id="table_component_CT">
                    <div class="component_CT">
                        <input type="text" name="name_Component[]" placeholder="Sản phẩm con" list="ALL_data_Component"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                            onchange="show_value_Storage_Component(this)">
                        <datalist id="ALL_data_Component">

                        </datalist>
                        <input type="number" name="quantity_Component_need[]" placeholder="Số lượng"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')">
                            <input type="number" name="price_Component[]" placeholder="Giá tiền"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')" >
                            <input type="number" name="price_Material_Storage" placeholder="Giá bán đề xuất"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                        <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                        <input type="hidden" name="id_component[]">
                        <input type="hidden" name="level_component[]">
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this,'.component_CT')">X</button>
                    </div>

                </div>
                <div class="bodyofForm Material">
                    <div class="sub " style="display:flex;     width: 50%;">
                    <h3>Mục đích xuất kho:</h3>
                        <select name="purpose" id="" onchange=" fill_suggset_CMD()">
                            <option value="Bán hàng">Bán hàng</option>
                            <option value="Sản xuất nội bộ">Sản xuất nội bộ</option>
                        </select>
                        <h3>Tiêu đề xuất kho:</h3>
                        <input type="text" name="name_export" id="" list="ALL_data_PROD_CMD">
                        <datalist id="ALL_data_PROD_CMD">

                        </datalist>
                        <h3>Ghi chú:</h3>
                        <input type="text" placeholder="note" name="note">
                    </div>
                    <div class="sub " style="display:flex;     width: 50%;">
                        <button name="save_export">Lưa phiếu xuất</button>
                    </div>
                </div>

            </div>

        </div>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.2/xlsx.full.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="../asset/js/KHO/settingKho.js"></script>                                           
<script>

function fill_suggset_CMD(){
    $('input[name="name_export"]').val('');
    let selectedValue = $('select[name="purpose"]').val();
    if(selectedValue == 'Sản xuất nội bộ'){
        getALL_prod_cmd('#ALL_data_PROD_CMD');

    }
}
function toggleVisibility(id) {
    if ($(id).css('display') === 'none') {
        $(id).css('display', 'block');
    } else {
        $(id).css('display', 'none');
    }
}

function resetInputsAndClearDiv() {
    // Reset input values
    let container = document.getElementById('add_NEW_prods');
    if (container) {
        let inputFields = container.querySelectorAll('input[type="text"]');
        inputFields.forEach(function(input) {
            input.value = ''; // Clear input value
        });
    }
    if (container.querySelector('a')) {
        container.querySelector('a').remove();
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

function delROW_(button, classname) {
    let s = document.querySelectorAll(classname);
    if (s.length > 1) {
        $(button).closest(classname).remove();
    } else {
        alert('Không cần xóa');
    }
}

var timeoutId;
var data_Material;

function getdata_Material() {

    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            getAll_Info_Material: 1
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
    let price_storage = $(input).closest('.item_CT').find('input[name="price_Material_Storage"]');
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        storageInput.val(chooseName.quantity);
        price_storage.val(chooseName.total.toLocaleString());

    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
        price_storage.val(""); // Clear the id value as well
    }
}
function getdata_COMPONENT() {
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            getAll_Info_Component: 1,
            level: 999999999
        },
        success: function(data) {
            // Assuming input is a datalist element
            returnData = data;
            let datalist = document.getElementById('ALL_data_Component');
            datalist.innerHTML = ""; // Clear existing options
            // Populate the datalist with the "name" values from the materials

            for (var count = 0; count < returnData.length; count++) {
                var option = document.createElement("option");
                option.value = data[count].name;
                datalist.appendChild(option);
            }
        }
    });
}
getdata_COMPONENT();

function show_value_Storage_Component(input) {
    let idInput = $(input).closest('.component_CT').find('input[name="id_component[]"]');
    let level = $(input).closest('.component_CT').find('input[name="level_component[]"]');
    let quantity = $(input).closest('.component_CT').find('input[name="quantity_Component_inStorage"]');
    let price_storage = $(input).closest('.component_CT').find('input[name="price_Material_Storage"]');
    let data = returnData;
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        level.val(chooseName.level);
        quantity.val(chooseName.quantity);
        price_storage.val(chooseName.total.toLocaleString());
    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
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

function hide_add_new_products_btn() {
    let inputs = document.querySelectorAll('input[name="name_Material[]"]');
    let val_input = inputs[inputs.length - 1].value.toLowerCase(); // Corrected to use .value instead of .val()
    let options = document.getElementById('ALL_data_material').querySelectorAll('option');
    let isInDatalist = Array.from(options).some(option => option.value.toLowerCase().includes(val_input));

    if (isInDatalist) {
        document.getElementById('add_new_products').style.display = 'none';
    } else {
        document.getElementById('add_new_products').style.display = 'flex';
    }

}
</script>