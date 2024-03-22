<?php 
    $material = new material;
    $component = new component;
    $info_Material = new info_Material;
    if(isset($_POST['save_import'])){
        $all = [];
        $name_import = isset($_POST['name_import'])?$_POST['name_import']:'';
        $note = isset($_POST['note'])?$_POST['note']:'';
        $created_by = $_SESSION['userINFO']['fullname'];
        if(isset($_POST['id_material'])){
        import_material::addNew($created_by, $name_import, $note);
        $id_import = import_material::get_1row('*', " `name` = '$name_import' AND `created_by` = '$created_by'  ")['id'];

            foreach ($_POST['id_material'] as $key => $value) {
                $all[] = ['id_import' => $id_import,'name' => $value, 'quantity' => $_POST['quantity_Material_need'][$key], 'price' => $_POST['price'][$key]];
                import_material_detail::addNew2(  $id_import, $value, $_POST['quantity_Material_need'][$key], $_POST['price'][$key]);
                material::update_quantity($_POST['quantity_Material_need'][$key], $value);
            }
        }
    }


?>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <h2>Nhập kho
                </h2>
                <div class="bodyofForm Material" id="table_material_CT">
                    <div class="item_CT">
                        <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')"
                            onchange="show_value_Storage_Material(this, getdata_Material()); "
                            oninput="hide_add_new_products_btn();" list="ALL_data_material">
                        <datalist id="ALL_data_material">

                        </datalist>

                        <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')">
                        
                            <input type="number" name="price[]" placeholder="Giá tiền"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')">    
                        <input type="hidden" name="id_material[]">
                        <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                            onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.item_CT')">X</button>
                    </div>

                </div>
                <div class="bodyofForm">
                    <div class="v " id="add_new_products" style="display:none;">
                        <button type="button" class="circle_button" onclick="toggleVisibility('#modal_addNEW_prods')"><i
                                class="fa-solid fa-plus"></i></button>
                        <label for="">New prods</label>
                    </div>
                </div>
                <div class="bodyofForm Material">
                    <div class="sub " style="display:flex;     width: 50%;">
                    <h3>Tiêu đề nhập kho:</h3>
                        <input type="text" name="name_import" id="">
                        <h3>Ghi chú:</h3>
                        <input type="text" placeholder="note">
                    </div>
                    <div class="sub " style="display:flex;     width: 50%;">
                    <button name = "save_import">Lưa phiếu nhập</button>
                    </div>
                </div>

            </div>

        </div>
</form>
<div class="big inforForm" id="modal_addNEW_prods" style="display: none;">
    <div class="bodyofForm modal_main">
        <div class="modal_header">
            Thêm sản phẩm mới
            <button type = "button" onclick="resetInputsAndClearDiv(); toggleVisibility('#modal_addNEW_prods') "><i
                    class="fa-regular fa-circle-xmark"></i></button>
        </div>
        <div class="Info_tab">
            <div id="add_NEW_prods" class="tabcontent">
                <h3>Tên</h3>
                <input type="text" name="name_prods_New">
                <h3>Số lượng</h3>
                <input type="text" name="quantity_prods_New">
            </div>
        </div>
        <div class="modal_footer">
            <button onclick="add_new_prods_todb(this,'add_NEW_prods');"> Lưu
            </button>
            <button type = "button"  onclick="toggleVisibility('#modal_addNEW_prods')"> Hủy </button>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.2/xlsx.full.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="../asset/js/KHO/settingKho.js"></script>
<script>
function toggleVisibility(id) {
    if ($(id).css('display') === 'none') {
        $(id).css('display', 'block');
    } else {
        $(id).css('display', 'none');
    }
}
//////////////////đang làm modal
function add_new_prods_todb(button, id) {
    let container = document.getElementById(id);
    let form = {
        name: container.querySelector('input[name="name_prods_New"]').value,
        quantity: container.querySelector('input[name="quantity_prods_New"]').value,
    };
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            import: 1,
            add_NEW_PRODS: 1,
            form_data: form
        },
        success: function(data) {
            alert('Thêm thành công, hãy bổ sung thông tin chi tiết');
            let a = document.createElement("a");
            let id = data;
            a.href = 'admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=' + id;
            a.text = 'Bổ sung thông tin'
            a.target = "_blank";
            container.appendChild(a);
        }

    });
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