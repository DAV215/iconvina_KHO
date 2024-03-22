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
////////ADD POSITION
function new_setting_Position(button) {
    let container = button.closest('.tbl_row');
    let form = {
        storage: container.querySelector('input[name="storage[]"]').value,
        row: container.querySelector('input[name="row[]"]').value,
        col: container.querySelector('input[name="col[]"]').value,
        shelf_level: container.querySelector('input[name="shelf_level[]"]').value,
    };
    form.sum = form.storage + ' ' + form.row + ' ' + form.col + ' ' + form.shelf_level;
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Form_new_setting_Position: form,
            new_setting_Position: 1
        },
        success: function(data) {
            location.replace(location.href);
        }
    });
    console.log(form); // Output the form object to console
}

function new_setting_Position_2(button, div) {
    let container = button.closest(div);
    let form = {
        storage: container.querySelector('input[name="storage"]').value,
        row: container.querySelector('input[name="row"]').value,
        col: container.querySelector('input[name="col"]').value,
        shelf_level: container.querySelector('input[name="shelf_level"]').value,
    };
    form.sum = form.storage + ' ' + form.row + ' ' + form.col + ' ' + form.shelf_level;
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Form_new_setting_Position: form,
            new_setting_Position: 1
        },
        success: function(data) {
            alert('Đã thêm thành công');
        },
        error: function(xhr, status, error) {
            alert('Error occurred: ' + error);
        }
    });
}

function show_setting_Position() {
    let tbl = document.getElementById('tbl_Position');
    $("#tbl_Position").empty();
    let search = $("#search_Position").val();

    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            show_setting_Position: 1,
            page_Position: pagenumber_Position,
            search_Position: search
        },
        success: function(result) {
            let data_1 = result.data;
            for (i = 0; i < 10; i++) {
                let m = data_1[i];
                let str = `
                    <tr>
                        <td>${i+ 1}</td>
                        <td>${m['storage']}</td>
                        <td>${m['row']}</td>
                        <td>${m['col']}</td>
                        <td>${m['shelf_level']}</td>
                        <td class="tacvu"> <!-- This seems incomplete. You may want to replace it with appropriate content -->
                            <!-- Content goes here -->
                        </td>
                    </tr>
                `;
                // Append the row to the tbody
                $("#tbl_Position").append(str);
            }
            $('#pagination_Position').empty();
            let allPage = Math.ceil(result.allRow / 10);
            for (i = 0; i < allPage; i++) {
                let str =
                    `<button onclick="getPagePosition(this); show_setting_Position()">${i + 1}</button>`;
                $('#pagination_Position').append(str);
            }
        }

    });
    Row_addPosition();

}

function dataList_setting_Position(search, id_dataList) {

    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            show_setting_Position: 1,
            page_Position: pagenumber_Position,
            search_Position: search
        },
        success: function(result) {
            let data_1 = result.data;
            $(id_dataList).empty();
            for (let i = 0; i < 10; i++) {
                let m = data_1[i];
                let option = $("<option></option>").val(m.sum);
                $(id_dataList).append(option);
            }

        }

    });
}

function Row_addPosition() {
    let str_add = `
                <tr class="tbl_row">
                    <td>0</td>
                    <td><input required type="text" name="storage[]" placeholder="Kho" list="All_storage"
                            onkeydown="addROW_(event, 'row', 'tbl_Position')"></td>
                    <td><input required type="text" name="row[]" placeholder="Hàng"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Position')"></td>
                    <td><input required type="text" name="col[]" placeholder="Cột"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Position')"></td>
                    <td><input required type="text" name="shelf_level[]" placeholder="Vị trí trên kệ"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Position')"></td>
                    <td>
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.tbl_row')">X</button>
                        <button type="button" onclick="new_setting_Position(this)">Process</button>

                    </td>
                </tr>
                `;
    $("#tbl_Position").append(str_add);
}
var pagenumber_Position = 1;

function getPagePosition(button) {
    pagenumber_Position = $(button).text();
}
show_setting_Position();
////////////--- CLASSIFY ---------/////////////
function Row_addClassify() {
    let str_add = `
                <tr class="tbl_row">
                    <td>0</td>
                    <td><input required type="text" name="mainClass" placeholder="Danh mục chính" list="All_storage"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Classify')"></td>
                    <td><input required type="text" name="subClass" placeholder="Danh mục phụ"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Classify')"></td>
                    <td><input required type="text" name="note" placeholder="Ghi chú"
                            onkeydown="addROW_(event, 'tbl_row', 'tbl_Classify')"></td>
                    <td>
                        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.tbl_row')">X</button>
                        <button type="button" onclick="new_setting_Classify(this)">Process</button>
                    </td>
                </tr>
                `;
    $("#tbl_Classify").append(str_add);
}
Row_addClassify();

function new_setting_Classify(button) {
    let container = button.closest('.tbl_row');
    let form = {
        main_class: container.querySelector('input[name="mainClass"]').value,
        sub_class: container.querySelector('input[name="subClass"]').value,
        note: container.querySelector('input[name="note"]').value,
    };
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Form_new_setting_Classify: form,
            new_setting_Classify: 1
        },
        success: function(data) {
            location.replace(location.href);
        }
    });
    console.log(form); // Output the form object to console
}

function show_setting_Classify() {
    let tbl = document.getElementById('tbl_Classify');
    $("#tbl_Classify").empty();
    let search = $('#search_Classify').val();
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            show_setting_Classify: 1,
            page_Classify: pagenumber_Classify,
            search_Classify: search
        },
        success: function(result) {
            let data_1 = result.data;
            for (i = 0; i < 10; i++) {
                let m = data_1[i];
                let str = `
                    <tr>
                        <td>${i+ 1}</td>
                        <td>${m['main_class']}</td>
                        <td>${m['sub_class']}</td>
                        <td>${m['note']}</td>
                        <td class="tacvu"> <!-- This seems incomplete. You may want to replace it with appropriate content -->
                            <!-- Content goes here -->
                        </td>
                    </tr>
                `;
                // Append the row to the tbody
                $("#tbl_Classify").append(str);
            }
            $('#pagination_Classify').empty();
            let allPage = Math.ceil(result.allRow / 10);
            for (i = 0; i < allPage; i++) {
                let str =
                    `<button onclick="getPageClassify(this); show_setting_Classify()">${i + 1}</button>`;
                $('#pagination_Classify').append(str);
            }
        }

    });
    Row_addClassify();
}

function dataList_setting_Classify(search, id_dataList) {
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            show_setting_Classify: 1,
            page_Classify: pagenumber_Classify,
            search_Classify: search
        },
        success: function(result) {
            let data_1 = result.data;
            $(id_dataList).empty();
            for (i = 0; i < 10; i++) {
                let m = data_1[i];
                let option = $("<option></option>").val(m.sum);
                $(id_dataList).append(option);
            }
        }

    });
}

function new_setting_Classify_2(button, div) {
    let container = button.closest(div);
    let form = {
        main_class: container.querySelector('input[name="mainClass"]').value,
        sub_class: container.querySelector('input[name="subClass"]').value,
        note: container.querySelector('input[name="note"]').value,
    };
    form.sum = form.main_class + ' ' + form.sub_class + ' ' + form.note;

    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Form_new_setting_Classify: form,
            new_setting_Classify: 1
        },
        success: function(data) {
            alert('Đã thêm thành công');
        },
        error: function(xhr, status, error) {
            alert('Error occurred: ' + error);
        }
    });
}
var pagenumber_Classify = 1;

function getPageClassify(button) {
    pagenumber_Classify = $(button).text();
}
show_setting_Classify();

function showRecord(id_MDetail_Record) {
    $.ajax({

        url: "QLKHO/code/getdata_Kho.php",
        data: {
            Record_Material_Detail: 1,
            id_MDetail_Record: id_MDetail_Record
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

function toggleVisibility(id) {
    if ($(id).css('display') === 'none') {
        $(id).css('display', 'block');
    } else {
        $(id).css('display', 'none');
    }
}