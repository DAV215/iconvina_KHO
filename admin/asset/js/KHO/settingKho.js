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

function toggleVisibility_flex(id) {
    if ($(id).css('display') === 'none') {
        $(id).css('display', 'flex');
    } else {
        $(id).css('display', 'none');
    }
}


function getdataStaff(id_container) {
    $.ajax({
        type: "POST",
        url: "API/API_USER.php",
        data: {
            getdataStaff: 1
        },
        success: function(data) {
            let container = document.getElementById(id_container);
            let jsonData = JSON.parse(data);
            $(id_container).empty();
            console.log(jsonData.length);
            for (let i = 0; i < jsonData.length; i++) {
                let temp = jsonData[i];
                let option = $("<option>").val(temp.fullname).text(temp.fullname);
                $(id_container).append(option);

                // console.log(jsonData[i]);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching data:", error);
            // Optionally handle the error here
        }
    });
}
getdataStaff('#staff');

function getAll_import_note() {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getAll_import_note: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            data = response.data;
            total = response.total;
            $('#table_import_listed').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                total: total,
                "bDestroy": true,
                columnDefs: [{
                        targets: [0],
                        orderData: [0, 1]
                    },
                    {
                        targets: [1],
                        orderData: [1, 0]
                    },
                    {
                        targets: [4],
                        orderData: [4, 0]
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Trả về số thứ tự tăng dần bắt đầu từ 1
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'created_by' },
                    { data: 'date' },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            // Lấy giá trị tổng tương ứng với chỉ mục hàng
                            var index = meta.row;
                            var sumTotal = total[index];
                            return sumTotal.toLocaleString();
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QLKHO&action=thongke_imp_exp&actionChild=imp_exp_detail&id_imp_exp=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 10
            });

        }
    });
}

function get_import_note_detail(id) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_import_note_detail: 1,
            id_imp_deatail: id
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            let total = 0;
            data = response;
            $('#table_imp_detail').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                "bDestroy": true,
                columnDefs: [{

                        className: 'dt-body-center'
                    },
                    {
                        targets: [0],
                        orderData: [0, 1]
                    },
                    {
                        targets: [1],
                        orderData: [1, 0]
                    },
                    {
                        targets: [4],
                        orderData: [4, 0]
                    }
                ],
                order: [
                    [3, 'desc']
                ],

                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Trả về số thứ tự tăng dần bắt đầu từ 1
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'quantity' },
                    { data: 'price' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            total += row.quantity * row.price;
                            return (row.quantity * row.price).toLocaleString();
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=' + data + '">Chi tiết</a>';

                        }
                    }
                ],

                "pageLength": 10
            });
            $("#total").text('Tổng: ' + (total / 3).toLocaleString());
        }
    });
}

function getALL_prod_cmd(id_datalist) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getALL_prod_cmd: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            // Xóa dữ liệu cũ của phần tử datalist
            $(id_datalist).empty();

            // Lặp qua dữ liệu và thêm các tùy chọn vào phần tử datalist
            $.each(response.data, function(index, item) {
                let value = item.id + ' - ' + item.name;
                $(id_datalist).append($('<option>', {
                    value: value
                }));
            });
        }
    });
}

function getAll_export_note() {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getAll_export_note: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            data = response.data;
            total = response.total;
            $('#table_export_listed').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                total: total,
                "bDestroy": true,
                columnDefs: [{
                        targets: [0],
                        orderData: [0, 1]
                    },
                    {
                        targets: [1],
                        orderData: [1, 0]
                    },
                    {
                        targets: [4],
                        orderData: [4, 0]
                    }
                ],
                order: [
                    [3, 'desc']
                ],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Trả về số thứ tự tăng dần bắt đầu từ 1
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'purpose' },
                    { data: 'created_by' },
                    { data: 'date' },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            // Lấy giá trị tổng tương ứng với chỉ mục hàng
                            var index = meta.row;
                            var sumTotal = total[index];
                            return sumTotal.toLocaleString();
                        }
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QLKHO&action=thongke_imp_exp&actionChild=exp_detail&id_exp=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 10
            });

        }
    });
}

function get_export_note_detail(id) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_export_note_detail: 1,
            id_exp_detail: id
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            let total = 0;
            data = response.data;
            $('#table_exp_detail').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                "bDestroy": true,
                columnDefs: [{

                        className: 'dt-body-center'
                    },
                    {
                        targets: [0],
                        orderData: [0, 1]
                    },
                    {
                        targets: [1],
                        orderData: [1, 0]
                    },
                    {
                        targets: [4],
                        orderData: [4, 0]
                    }
                ],
                order: [
                    [3, 'desc']
                ],

                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Trả về số thứ tự tăng dần bắt đầu từ 1
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'type' },
                    { data: 'quantity' },
                    { data: 'price' },
                    { data: 'into_money' },
                    {
                        render: function(data, type, row) {
                            if (row.type == 'Material') {
                                return '<a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=' + row.id + '">Chi tiết</a>';
                            } else {
                                return '<a href="admin.php?job=QLKHO&action=thongke&actionChild=ComponentDetail&id_Component_parent=' + row.id + '">Chi tiết</a>';

                            }

                        }
                    }
                ],

                "pageLength": 10
            });
            $("#total").text('Tổng: ' + (response.total).toLocaleString());
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
    var wbout = XLSX.write(wb, {
        bookType: 'xlsx',
        type: 'binary'
    });

    // Specify file name
    filename = filename ? filename.replace(/\.[^.]+$/, '') + '.xlsx' :
        'excel_data.xlsx'; // Update default filename to .xlsx

    // Create download link element
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);

    // Convert binary string to Blob
    var blob = new Blob([s2ab(wbout)], {
        type: dataType
    });

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

function update_member_prod_cmd(id_member_container, id_prod_cmd) {
    let member = $(id_member_container).val();
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            update_member_prod_cmd: 1,
            id_prod_cmd: id_prod_cmd,
            member: member
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            alert('okl');
        }
    });
}

function fillmember_list_divJOB(id_container, id_datalist) {
    let members = $("#member").val();
    $(id_datalist).empty();
    members = members.split(',').map(member => member.trim());
    for (let i = 0; i < members.length; i++) {
        let option = $("<option>").val(members[i]).text(members[i]);
        $(id_datalist).append(option);
    }
}