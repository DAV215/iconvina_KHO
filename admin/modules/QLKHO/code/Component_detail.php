<?php 
    $id_Component_parent =  $_GET['id_Component_parent'] ;
    // $material = material::get_info_Material($id_material);
    // $info_Material = info_Material::get_info_Material($id_material);

    $component_parent = new component;
    $info_component = info_Component::get_info_Component($id_Component_parent);

    $component_parent_basicInfo = $component_parent->get_1row(' `id` = '.$id_Component_parent.'')[0];
    $get_DMNL_Material = $component_parent->getALL_Child(' `level` = 0 AND `id_parent` = '.$component_parent_basicInfo['id'].'');
    $get_DMNL_Component = $component_parent->getALL_Child(' `level` > 0 AND `id_parent` = '.$component_parent_basicInfo['id'].' ORDER BY `id` DESC');

    if(isset($_POST['save_component_Modify'])){
        $name = $_POST['name'];
        $position_Component = $_POST['position_Component'];
        // $quantity_Component = $_POST['quantity_Component'];
        $quantity_Component = 0;
        $code_Component = $_POST['code_Component'];
        $note_Component = $_POST['note_Component'];

        //processing
        //name & quantity

        //detail Info
        $info_modify = new info_Component;
        if(info_Component::get_info_Component($id_Component_parent) == null){
            $link_folder = $id_Component_parent.'_'.$name;
            info_Component::upload_Files($link_folder, 'img_Component');
            $info_modify->addNew($id_Component_parent, $position_Component, $quantity_Component, $code_Component, $link_folder, $note_Component);
        }
        else{
            $link_folder = $id_Component_parent.'_'.$name;
            $info_modify->update($position_Component, $code_Component, $note_Component, $link_folder,' `id_component` = '.$id_Component_parent.' ');
        }
        
        echo "<meta http-equiv='refresh' content='0'>";
    }
    if(isset($_POST['save_component_DMNL'])){
        $component_parent->remove('`id_parent` = '.$id_Component_parent.''); 
        // Material
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
        $name =  $component_parent_basicInfo['name'];

        if( $name_Component[0] == null){
            foreach($name_Material as $key => $value){
                if($value != null){
                    $component_parent->addNew($value,$id_Component_parent,$id_material[$key],0,$quantity_Material_need[$key],$name);
                }
            }
        }else{
            $level_parent_ADD = max($level_component)+1;
            if($name_Material[0] != null){
                foreach($name_Material as $key => $value){
                    if($value != null){
                        $component_parent->addNew($value,$id_Component_parent,$id_material[$key],0,$quantity_Material_need[$key],$name);
                        // echo $value.'</br>';
                    }
                }
            }
            foreach($name_Component as $key => $value){
                if($value != null){
                    // echo 'C-'.$value.$level_component[$key].'</br>';
                    $component_parent->addNew($value,$id_Component_parent,$id_component[$key],$level_component[$key],$quantity_Component_need[$key],$name);
                }
            }
        }
        echo "<meta http-equiv='refresh' content='0'>";
        
    }
    if(isset($_POST['downloadExcel'])){
        $data = $component->thongke_Vattu_Component($component->testDEQUY_thongke($id_Component_parent));
        echo exportToExcel($data, 'T1'); 
    }
    
?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <h2>Bổ sung thông tin:</h2>
                <div class="bodyofForm">
                    <div class="inputHaveLable">
                        <label for="" class="formLable"> Sản phẩm</label>
                        <input type="text" name="name" placeholder="Tên sản phẩm"
                            value="<?php echo  $component_parent_basicInfo['name'] ?>" required>
                    </div>
                    <div class="Info_tab">
                        <div class="tab">
                            <button type="button" class="tablinks" onclick="change_tab(event, 'detail_info')"
                                id="defaultOpen">Thông tin
                                chi tiết</button>
                            <button type="button" class="tablinks" onclick="change_tab(event, 'tbl_dmnl')">Định mức
                                nguyên liệu</button>
                            <button type="button" class="tablinks" onclick="change_tab(event, 'tbl_taolenhsanxuat')">Tạo
                                lệnh sản xuất</button>
                        </div>

                        <!-- Tab content -->
                        <div id="detail_info" class="tabcontent">
                            <div class="part">
                                <h3>Vị trí</h3>
                                <input type="text" name="position_Component" value="<?php echo isset($info_component['position'])?$info_component['position']:'';  ?>">
                                <h3>Code</h3>
                                <input type="text" name="code_Component"  value="<?php echo isset($info_component['code'])?$info_component['code']:'';  ?>">
                                <h3>Ghi chú</h3>
                                <input type="text" name="note_Component"  value="<?php echo isset($info_component['note'])?$info_component['note']:'';  ?>">
                            </div>
                            <div class="part">
                                <label for="img_Component"> Thêm File đính kèm</label>
                                <input type="file" name="img_Component[]" id="img_Component" multiple
                                    onchange="ADD_new_FILE(this)">
                                <div class="preview_IMG" id="img_preview_Component">
                                    <img src="" alt="">
                                    <?php 
                                    if(isset($info_component['link_folder'])){
                                        $images = array('jpg','png','jpeg','gif');
                                        $path = info_Component::$baseDirectory .$info_component['link_folder'].'/';
                                        $files = scandir($path);
                                        $found = false;
                                        foreach($files as $key => $value) {
                                            if($value != '.' && $value != '..'){
                                                $ext = pathinfo($value, PATHINFO_EXTENSION);
                                                if(in_array($ext,$images)) {
                                                    ?>
                                                    <div class="sub_preview_Img">
                                                        <img src="<?php echo $path.$value ?>" alt="">
                                                        <button type="button" class="delete_ITEM_CT" onclick="del_Img_Component('<?php echo $path.$value; ?>')">X</button>

                                                    </div>
                                                    <?php
                                                }else{
                                                    ?>
                                                    <div class="sub_preview_Img">
                                                        <a href="#" onclick="openFileInNewTab('<?php echo $path.$value; ?>')"><?php echo $value ?></a>
                                                        <button type = "button" class="delete_ITEM_CT" onclick="del_Img_Component('<?php echo $path.$value; ?>')">X</button>

                                                    </div>
                                                    <?php
                                                }
                                            }
    
                                        }
                                    
                                    }

                                    ?>
                                </div>
                            </div>
                            <button type="submit" name="save_component_Modify">Lưu</button>
                        </div>

                    </div>

                </div>
                <div class="tabcontent" id="tbl_dmnl">
                    <div class="bodyofForm Material" id="table_material_CT">
                        <h3>Vật liệu thô</h3>
                        <?php 
                            foreach($get_DMNL_Material as $row){
                        ?>
                        <div class="item_CT">
                            <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')"
                                onchange="show_value_Storage_Material(this, getdata_Material())"
                                list="ALL_data_material"
                                value="<?php echo material::get_info_Material($row['id_child'])['name']  ?>">
                            <datalist id="ALL_data_material">

                            </datalist>
                            <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')"
                                value="<?php echo $row['quantity_ofChild'] ?>">
                            <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled
                                value="<?php echo material::get_info_Material($row['id_child'])['quantity']  ?>">
                            <input type="hidden" name="id_material[]" value="<?php echo $row['id_child'] ?>">
                            <button type="button" name="delete_ITEM_CT" onclick="deleteMaterial(this)">X</button>
                        </div>
                        <?php
                        }
                        ?>
                        <div class="item_CT">
                            <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')"
                                onchange="show_value_Storage_Material(this,getdata_Material())"
                                list="ALL_data_material">
                            <datalist id="ALL_data_material">

                            </datalist>
                            <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')">
                            <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                            <input type="hidden" name="id_material[]">
                            <button type="button" name="delete_ITEM_CT" onclick="deleteMaterial(this)">X</button>
                        </div>
                    </div>
                    <div class="bodyofForm Material" id="table_component_CT">
                        <h3>Component</h3>
                        <?php 
                            foreach($get_DMNL_Component as $row){
                               $Component_detail = info_Component::get_info_Component($row['id']);
                        ?>
                        <div class="component_CT">
                            <input type="text" name="name_Component[]" placeholder="Sản phẩm con"
                                list="ALL_data_Component"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                                onchange="show_value_Storage_Component(this)"
                                value="<?php echo $component_parent->get_1row('`id` = '.$row['id'].'')[0]['name'] ?>">
                            <datalist id="ALL_data_Component">

                            </datalist>
                            <input type="number" name="quantity_Component_need[]" placeholder="Số lượng"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                                value="<?php echo $component_parent->get_1row('`id` = '.$row['id'].'')[0]['quantity_ofChild'] ?>">
                            <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                                value="<?php echo isset($Component_detail['quantity']) ?  $Component_detail['quantity']: '0'; ?>"
                                disabled>
                            <input type="hidden" name="id_component[]" value="<?php echo $row['id_child'] ?>">
                            <input type="hidden" name="level_component[]" value="<?php echo $row['level']   ?>">
                            <button type="button" name="delete_ITEM_CT" onclick="deleteComponent(this)">X</button>
                        </div>
                        <?php
                        }
                        ?>
                        <div class="component_CT">
                            <input type="text" name="name_Component[]" placeholder="Sản phẩm con"
                                list="ALL_data_Component"
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
                            <button type="button" name="delete_ITEM_CT" onclick="deleteComponent(this)">X</button>
                        </div>
                    </div>
                    <button type="submit" name="save_component_DMNL">Lưu Định mức nguyên liệu</button>

                </div>


            </div>

        </div>

    </div>
</form>

<div id="tbl_taolenhsanxuat" class="tableComponent tabcontent">
                    <?php
                        $component->testDEQUY_2($id_Component_parent); 
                        echo "<h1>Thống kê vật tư:</h1>";
                        ?>
                            <table class="data_table" id = "tbl_BOM">
                                <thead>
                                    <tr class="headerTable">
                                        <div class="rowTitle">
                                            <th>Số thứ tự</th>
                                            <th>Tên</th>
                                            <th>Code</th>
                                            <th>Số lượng</th>
                                            <th>Tác vụ</th>
                                        </div>
                                    </tr>
                                </thead>
                                <tbody id="tbody_Component">
                                    <?php 
                                        $i = 0;
                                        foreach ($component->thongke_Vattu_Component($component->testDEQUY_thongke($id_Component_parent)) as $row) {
                                            // echo $row['id'] . $row['name'] . '-SL:' . $row['quantity'] . '</br>';
                                            $i++;
                                            ?>
                                                <tr>
                                                    <td><?php echo $i ?></td>
                                                    <td><?php echo $row['name']  ?></td>
                                                    <td><?php echo $row['code']  ?></td>
                                                    <td><?php echo $row['quantity']  ?></td>
                                                    <td class="tacvu">
                                                        <a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=<?php echo $row['id']  ?>">
                                                            Chi tiết
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php
                                        }
                                    ?>
                                    
                                </tbody>
                            </table>
                            <button onclick="exportTableToExcel('tbl_BOM', '<?php echo 'BOM_'.$component_parent_basicInfo['name'] ?>')">Xuất EXCEL</button>
                        <?
                    ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.2/xlsx.full.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
function change_tab(event, nameTab) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    tbl_dmnl = document.getElementById('table_material_CT');
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
    window.open(fileUrl, '_blank');
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
    let elements = document.querySelectorAll(classname);
    if (elements.length > 2) {
        $(button).closest(classname).remove();
    } else {
        alert('Không cần xóa');
    }
}
document.getElementById("defaultOpen").click();

var timeoutId;
var returnData;

function getdata_COMPONENT() {
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            type: 'component',
            level: '<?php echo $component_parent_basicInfo['level'] ?>'
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
    return returnData;
}
getdata_COMPONENT();
function show_value_Storage_Component(input) {
    let idInput = $(input).closest('.component_CT').find('input[name="id_component[]"]');
    let level = $(input).closest('.component_CT').find('input[name="level_component[]"]');
    let quantity = $(input).closest('.component_CT').find('input[name="quantity_Component_inStorage"]');
    let data = returnData;
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        level.val(chooseName.level);
        quantity.val(chooseName.quantity);
    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
    }
}
var timeoutId_2;
var data_Material;

function getdata_Material() {
    // Clear the previous timeout (if any)
    clearTimeout(timeoutId);
    // Set a new timeout for 500 milliseconds
    timeoutId = setTimeout(function() {
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
    }, 500);
    return data_Material;
}


getdata_Material();
var data_Material = getdata_Material();

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

function del_Child_ofComponent(button, className, type_relation) {
    let componentDiv = $(button).closest(className);
    if (componentDiv.length < 1) {
        let form_data = new FormData(); // Instantiate a new FormData object
        form_data.append('type_relation', type_relation); // Append additional data
        form_data.append('action_AJAX', 'del_' + type_relation); // Append additional data
        form_data.append('id_parent', <?php echo $id_Component_parent ?>); // Append additional data

        // Serialize input values and append to form_data
        componentDiv.find('input').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            form_data.append(name, $(this).val());

        });
        $.ajax({
            url: "QLKHO/code/getdata_Kho.php",
            type: 'POST',
            data: form_data, // Form data
            processData: false, // Prevent jQuery from automatically processing the data
            contentType: false, // Prevent jQuery from automatically setting the Content-Type header
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error(error);
            }
        });
    }

}

function deleteMaterial(button) {
    // Call del_Child_ofMaterial and delROW_ functions
    del_Child_ofComponent(button, '.item_CT', 'Material');
    delROW_(button, '.item_CT');
}

function deleteComponent(button) {
    // Call del_Child_ofComponent and delROW_ functions
    del_Child_ofComponent(button, '.component_CT', 'Component');
    delROW_(button, '.component_CT');
}



function del_Img_Component(path_Component_Img_DEL){
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "text",
        data: {
            path_Component_Img_DEL: path_Component_Img_DEL
        },
        success: function(data) {
            alert(data);
            if (<?php echo info_Component::get_info_Component($id_Component_parent) == null ? '0' : '1' ?>) {
                location.reload();
            }

        },
        error: function() {
            alert('Error in AJAX request');
        }
    });
}
function ADD_new_FILE(file) {
    let file_data = [];  
    let form_data = new FormData();      
    form_data.append('name_folder_component_modify', '<?php echo $component_parent_basicInfo['id'].'_'.$component_parent_basicInfo['name'] ; ?>');
    form_data.append('id_component', '<?php echo $component_parent_basicInfo['id']; ?>');
    form_data.append('name_component', '<?php echo $component_parent_basicInfo['name']; ?>');

    for (let i = 0; i < $('#img_Component').prop('files').length; i++) {
        file_data.push($('#img_Component').prop('files')[i]);
        form_data.append('file_component_add[]', file_data[i]);
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
            if (<?php echo info_Component::get_info_Component($id_Component_parent) == null ? '0' : '1' ?>) {
    location.reload();
}

        },
        error: function() {
            alert('Error in AJAX request');
        }
    });
}
function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; // MIME type for .xlsx
    var tableSelect = document.getElementById(tableID);

    // Check if tableSelect is valid
    if (!tableSelect) {
        console.error("Table with ID '" + tableID + "' not found.");
        return;
    }

    // Create a new Excel workbook
    var wb = XLSX.utils.book_new();
    
    // Convert table to worksheet
    var ws = XLSX.utils.table_to_sheet(tableSelect);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Generate Excel file in binary string
    var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

    // Specify file name
    filename = filename ? filename.replace(/\.[^.]+$/, '') + '.xlsx' : 'excel_data.xlsx'; // Update default filename to .xlsx
    
    // Create download link element
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    // Convert binary string to Blob
    var blob = new Blob([s2ab(wbout)], { type: dataType });

    // Create object URL for Blob
    var url = window.URL.createObjectURL(blob);
    
    // Create a link to the file
    downloadLink.href = url;

    // Setting the file name
    downloadLink.download = filename;

    // Trigger the download
    downloadLink.click();

    // Clean up
    window.URL.revokeObjectURL(url);
}

// Utility function to convert string to array buffer
function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}



</script>