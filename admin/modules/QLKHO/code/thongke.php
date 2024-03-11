<?php 
    $material = new material;
    $component = new component;
    if(isset($_POST['addComponent'])){
        $id_parent = $_POST['id_parent'];
        $name_parent = $_POST['name_parent'];
        $name = $_POST['name'];
    }
    if(isset($_POST['save'])){
        if($_POST['type_relation'] == 'Material'){
            $name = $_POST['name'];
            $quantity = $_POST['quantity'];
            $material->addNew($name, $quantity);
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
                    $component->addNew($value,$id_parent,$id_material[$key],0,$quantity_Material_need[$key],$name);
                }
            }else{
                $level_parent_ADD = max($level_component)+1;
                $component->addNew($name,0,0,$level_parent_ADD,0,0);
                $id_parent_ADD = $component->get_Newest_Component($name,$level_parent_ADD);
                if($name_Material[0] != null){
                    foreach($name_Material as $key => $value){
                        $component->addNew($value,$id_parent_ADD,$id_material[$key],0,$quantity_Material_need[$key],$name);
                    }
                }
                foreach($name_Component as $key => $value){
                    $component->addNew($value,$id_parent_ADD,$id_component[$key],$level_component[$key],$quantity_Component_need[$key],$name);
                }
            }
        }
    }
?>
<h1>Vật liệu thô</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="searchPhieuchi" placeholder="Search">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input type="hidden" name="addPhieuchi" value="true">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLTC">
        <input type="hidden" name="action" value="phieuchi">
        <div class="searchBox more2">
            <button class="searchButton" href="">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </button>
        </div>
    </form>
    <table class="data_table">
        <thead>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
                    <th>Tên</th>
                    <th>Số lượng</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($material->getALL_material() as $row) {
                $i++;
      
                    ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $row['name'] ?? '' ?></td>
                <td><?php echo $row['quantity'] ?? '' ?></td>
                <td class="tacvu">
                    <a
                        href="admin.php?job=QLTC&action=phieuchi&actionChild=phieuchiDetail&idPhieuChi=<?php echo $row['id']; ?>">Chi
                        tiết</a>
                </td>
            </tr>
            <?php
                
            }
        ?>

        </tbody>
    </table>
</div>

<?php 
    // $component->testDEQUY(41);
    // echo "<h1>Thống kê vật tư:</h1>";
    // foreach ($component->thongke_Vattu_Component($component->testDEQUY_thongke(41)) as $row) {
    //     echo $row['name'] . '-SL:' . $row['quantity'] . '</br>';
    // }
?>


<?php $component->testDEQUY_2(41);
    echo "<h1>Thống kê vật tư:</h1>";
    foreach ($component->thongke_Vattu_Component($component->testDEQUY_thongke(41)) as $row) {
        echo $row['name'] . '-SL:' . $row['quantity'] . '</br>';
    }
?>
<h1></h1>


<form action="" method="post">
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
                            <input type="text" name="position">
                            <h3>Số lượng</h3>
                            <input type="text" name="quantity">
                        </div>

                        <div id="detail_info" class="tabcontent">
                            <h3>Paris</h3>
                            <p>Paris is the capital of France.</p>
                        </div>
                    </div>


                </div>
                <div class="bodyofForm Material" id="table_material_CT">
                    <h3>Vật liệu thô</h3>
                    <div class="item_CT">
                        <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                            onkeydown="addROW_(event,'item_CT','table_material_CT')"
                            onchange="show_value_Storage(this,'.item_CT','id_material[]','saveROW_data_Material[]',show_Result('material','ALL_data_material'))"
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
var sv;

function show_Result(type, id) {

    // Clear the previous timeout (if any)
    clearTimeout(timeoutId);
    // Set a new timeout for 500 milliseconds
    timeoutId = setTimeout(function() {
        $.ajax({
            type: "POST",
            url: "QLKHO/code/getdata_Kho.php",
            dataType: "JSON",
            data: {
                type: type
            },
            success: function(data) {
                // Assuming input is a datalist element
                var datalist = document.getElementById(id);
                sv = data;
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
    return sv;
}
show_Result('material', 'ALL_data_material');

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
    clearTimeout(timeoutId);
    // Set a new timeout for 500 milliseconds
    timeoutId = setTimeout(function() {
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
    }, 500);
}
getdata_COMPONENT();

function show_value_Storage_Component(input) {
    let idInput = $(input).closest('.component_CT').find('input[name="id_component[]"]');
    let level = $(input).closest('.component_CT').find('input[name="level_component[]"]');
    let data = returnData;
    let chooseName = data.find(item => item.name === input.value);
    if (chooseName) {
        idInput.val(chooseName.id);
        level.val(chooseName.level);
    } else {
        storageInput.val(""); // Clear the value if the chosen name is not found
        idInput.val(""); // Clear the id value as well
        Rowdata.val("");
    }
}
</script>