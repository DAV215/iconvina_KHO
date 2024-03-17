<?php 

?>
<form action="" method="post" enctype="multipart/form-data">
    <div class="userFormm">
        <div class="mainForm">
            <div class="big inforForm">
                <div class="bodyofForm Material" id="tbl_Position">
                    <h3>Vị trí trong kho</h3>
                    <div class="tableComponent">
                        <form action="" method="get">
                            <div class="searchBox">
                                <input type="hidden" name="job" value="QLTC">
                                <input type="hidden" name="action" value="phieuchi">
                                <input class="searchInput" type="text" name="search_Material" id="search_Material"
                                    placeholder="Search" oninput="Litsed_Material()">
                                <button class="searchButton" type="submit">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                        <form action="" method="get">
                            <div class="searchBox more1">
                                <input type="hidden" name="job" value="QLKHO">
                                <input type="hidden" name="action" value="thongke">
                                <input type="hidden" name="actionChild" value="addFILE_ADD">
                                <button class="searchButton" type="submit">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </form>
                        <form action="">
                            <input type="hidden" name="job" value="QLKHO">
                            <input type="hidden" name="action" value="thongke">
                            <input type="hidden" name="actionChild" value="thongke">
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
                                        <th>Tên kho</th>
                                        <th>VỊ trí hàng</th>
                                        <th>Vị trí cột</th>
                                        <th>Vị trí trên kệ</th>
                                        <th>Tác vụ</th>
                                    </div>
                                </tr>
                            </thead>
                            <tbody id="tbody_Material">
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="Pagination" id="pagination_Material">

                        </div>
                    </div>
                </div>
                <div class="bodyofForm Material" id="tbl_Position">
                    <h3>Vị trí trong kho</h3>
                    <div class="component_CT">
                        <input required type="text" name="storage[]" placeholder="Kho" list="All_storage"
                            onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <datalist id="All_storage">

                        </datalist>
                        <input required type="text" name="row[]" placeholder="Hàng"
                            onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <input required type="text" name="col[]" placeholder="Cột"
                            onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <input required type="text" name="shelf_level[]" placeholder="Vị trí trên kệ"
                            onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.component_CT')">X</button>
                    </div>
                </div>
                <button type="submit" name="save_Position">Lưu</button>
            </div>
            <div class="big inforForm">
                <div class="bodyofForm Material" id="tbl_Position">
                    <h3>Danh mục</h3>
                    <div class="component_CT">
                        <input required type="text" name="master_class[]" placeholder="Loại sản phẩm"
                            list="All_Main_Class" onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <input required type="text" name="main_class[]" placeholder="Danh mục chính"
                            list="All_Main_Class" onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <datalist id="All_Main_Class">

                        </datalist>
                        <input required type="text" name="sub_class[]" placeholder="Danh mục con"
                            onkeydown="addROW_(event, 'component_CT', 'tbl_Position')">
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.component_CT')">X</button>
                    </div>
                </div>
                <button type="submit" name="save_classify">Lưu</button>
            </div>
        </div>

    </div>
</form>
<script>
function delROW_(button, classname) {
    let s = document.querySelectorAll(classname);
    if (s.length > 1) {
        $(button).closest(classname).remove();
    } else {
        alert('Không cần xóa');
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
</script>