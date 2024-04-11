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

function getALL_prod_cmd_datalist(id_datalist) {
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

function get_BOM_hidden_miss_M_of_C(id_parent, id_tbl) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_BOM_hidden_miss_M_of_C: 1,
            id_parent: id_parent
        },
        dataType: 'JSON',
        type: 'POST',
        success: function(response) {
            if (!response || !response.length) {
                console.error('No data received from the server');
                return;
            }

            $(id_tbl).DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: response,
                destroy: true,
                columnDefs: [
                    { targets: [0], orderData: [0, 1] },
                    { targets: [1], orderData: [1, 0] },
                    { targets: [4], orderData: [4, 0] }
                ],
                order: [0, 'desc'],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1; // Return row number
                        }
                    },
                    { data: 'type' },
                    { data: 'name' },
                    { data: 'quantity' },
                    { data: 'quantity_inStorage' },
                    {
                        render: function(data, type, row) {
                            var link = (row.type === 'Material') ?
                                'admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=' + row.id :
                                'admin.php?job=QLKHO&action=thongke&actionChild=ComponentDetail&id_Component_parent=' + row.id;
                            return '<a href="' + link + '">Chi tiết</a>';
                        }
                    }
                ],
                pageLength: 15,
                rowCallback: function(row, data) {
                    if (parseInt(data.quantity_inStorage) < parseInt(data.quantity)) {
                        $(row).addClass('highlight');
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', error);
        }
    });
}

function treeMap_DMNL(id_component_parent, width) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            treeMap_DMNL: 1,
            id_component_parent: id_component_parent
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            tree2(response)
        }
    });
}

function tree2(data) {
    new d3.OrgChart().container('.chart-container').data(data)
        .nodeContent(function(d, i, arr, state) {
            const color = '#FFFFFF';
            return `
        <div style="font-family: 'Inter', sans-serif;background-color:${color}; position:absolute;margin-top:-1px; margin-left:-1px;width:${d.width}px;height:${d.height}px;border-radius:10px;border: 1px solid #E4E2E9">
           
           <img src=" ${
             d.data.img
           }" style="position:absolute;margin-top:-20px;margin-left:${20}px;border-radius:5px;width:80px;height:50px;" />
           
          <div style="color:#08011E;position:absolute;right:20px;top:17px;font-size:10px;"><i class="fas fa-ellipsis-h"></i></div>

          <div style="font-size:15px;color:#08011E;margin-left:20px;margin-top:32px">              
            <a style="color: #e24d0e;font-size: 16px;text-decoration: none; " href="${d.data.link}"> ${d.data.name}</a></div>
          <div style="color:#716E7B;margin-left:20px;margin-top:3px;font-size:14px;"> Trong kho: ${
            d.data.quantity_inStorage
          } </div>
          <div style="color:#716E7B;margin-left:20px;margin-top:3px;font-size:14px;"> Cần: ${
            d.data.quantity
          } </div>
       </div>
`;
        })
        .render();
}


// function generateTree(data, width_input) {
//     // Set up dimensions and margins for the tree
//     var margin = { top: 20, right: 90, bottom: 30, left: 90 };
//     var width = width_input - margin.left - margin.right;
//     var height = 500 - margin.top - margin.bottom;

//     // Append an SVG element to the tree container
//     var svg = d3.select("#tree-container").append("svg")
//         .attr("width", "100%") // Set width to 100% for responsiveness
//         .attr("height", "500px") // Set height to 100% for responsiveness
//         .append("g")
//         .attr("transform", "translate(" + margin.left + "," + margin.top + ")")
//         .call(d3.zoom().on("zoom", function() {
//             svg.attr("transform", d3.event.transform)
//         }));

//     // Create a hierarchical layout
//     var root = d3.hierarchy(data);

//     // Create a tree layout
//     var tree = d3.tree().size([width, height]);

//     // Assigns the x and y position for the nodes
//     var treeData = tree(root);

//     // Draw links between nodes
//     // Draw links between nodes
//     var links = svg.selectAll(".link")
//         .data(treeData.links())
//         .enter().append("path")
//         .attr("class", "link")
//         .attr("fill", "transparent")
//         .attr("d", function(d) {
//             var sourceX = d.source.x;
//             var sourceY = d.source.y - 0;
//             var targetX = d.target.x;
//             var targetY = d.target.y + 100;
//             var deltaX = targetX - sourceX;
//             var deltaY = targetY - sourceY;
//             var offsetX = 20; // Adjust this value to control the offset of the horizontal line
//             var offsetY = 10; // Adjust this value to control the length of the vertical line
//             var path = "M" + sourceX + "," + sourceY +
//                 "V" + (sourceY + deltaY / 2) + // Draw a vertical line to the middle of the link
//                 "H" + (targetX); // Draw a horizontal line to the left of the target node
//             if (d.target.children) {
//                 // Draw an additional vertical line if the target node has children
//                 path += "V" + (targetY + offsetY);
//             } else {
//                 // Draw a shorter vertical line if the target node doesn't have children
//                 path += "V" + (targetY - offsetY);
//             }
//             return path;
//         })
//         .style("stroke", "#ccc") // Set line color to gray
//         .style("stroke-width", "1px");

//     // Draw nodes
//     // Draw nodes
//     // Draw nodes
//     var nodes = svg.selectAll(".node")
//         .data(treeData.descendants())
//         .enter().append("g")
//         .attr("class", "node")
//         .attr("transform", function(d) {
//             return "translate(" + d.x + "," + (d.depth * 200) + ")"; // Adjust y position based on node depth
//         });


//     // Add rectangles as node containers
//     nodes.append("rect")
//         .attr("x", -50) // Half of the box width
//         .attr("y", -50) // Half of the box height
//         .attr("width", 100)
//         .attr("height", 150)
//         .attr("fill", "lightgray")
//         .attr("rx", 10) // Set border radius for x-axis
//         .attr("ry", 10) // Set border radius for y-axis
//         .attr("stroke", "black");

//     // Add image to the node
//     nodes.append("image")
//         .attr("xlink:href", function(d) { return d.data.img; }) // Set image dynamically based on data
//         .attr("x", -50) // Adjust x position to center the image
//         .attr("y", -40) // Adjust y position to place the image above the text
//         .attr("width", 100)
//         .attr("height", 80);

//     // Add text labels to the node
//     // Add text labels to the node with links
//     nodes.append("a")
//         .attr("xlink:href", function(d) { return d.data.link; }) // Set link dynamically based on data
//         .append("text")
//         .attr("dy", ".35em")
//         .style("text-anchor", "middle")
//         .text(function(d) { return d.data.name; })
//         .attr("x", -50) // Adjust x position to center the image
//         .attr("y", 50)
//         .style("font-size", 10)
//         .style("font-weight", "bold")
//         .call(wrap, 100); // Wrap text with a maximum width of 50px

//     // Function to wrap text
//     function wrap(text, width) {
//         text.each(function() {
//             var text = d3.select(this),
//                 words = text.text().split(/\s+/).reverse(),
//                 word,
//                 line = [],
//                 lineNumber = 0,
//                 lineHeight = 0.9, // ems
//                 y = text.attr("y"),
//                 dy = parseFloat(text.attr("dy")),
//                 tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", dy + "em");
//             while (word = words.pop()) {
//                 line.push(word);
//                 tspan.text(line.join(" "));
//                 if (tspan.node().getComputedTextLength() > width) {
//                     line.pop();
//                     tspan.text(line.join(" "));
//                     line = [word];
//                     tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
//                 }
//             }
//         });
//     }


//     // Define zoom behavior
//     let zoom = d3.zoom().on("zoom", function(event) {
//         svg.attr("transform", event.transform)
//     });
//     svg.call(zoom);
//     // Apply zoom behavior to the SVG container on button click
//     d3.select("#zoom-in-button").on("click", function() {
//         svg.transition().duration(250).attr("pointer-events", "none").call(zoom.scaleBy, 1.25).transition().attr("pointer-events", "all"); // Zoom in by scaling
//     });

//     d3.select("#zoom-out-button").on("click", function() {
//         svg.transition().duration(250).attr("pointer-events", "none").call(zoom.scaleBy, 0.75).transition().attr("pointer-events", "all"); // Zoom out by scaling
//     });
//     d3.select("#center_treeMap").on("click", function() {
//         svg.transition().call(zoom.translateTo, 0.5 * width, 0.5 * height);
//     });
//     d3.select("#move_left_treeMap").on("click", function() {
//         svg.transition().call(zoom.translateBy, -200, 0);
//     });
//     d3.select("#move_right_treeMap").on("click", function() {
//         svg.transition().call(zoom.translateBy, 200, 0);
//     });
//     d3.select("#move_up_treeMap").on("click", function() {
//         svg.transition().call(zoom.translateBy, 0, 200);
//     });

//     d3.select("#move_down_treeMap").on("click", function() {
//         svg.transition().call(zoom.translateBy, 0, -200);
//     });
//     // Create a tooltip div
//     var tooltip = d3.select("#tree-container")
//         .append("div")
//         .attr("class", "tooltip_treeMap")
//         .style("opacity", 0);

//     // Add mouseover event to the nodes
//     nodes.on("mouseover", function(event, d) {
//             // Update the tooltip content with node data
//             tooltip.html(`<strong>Name:</strong> ${d.data.name}<br><strong>Số lượng trong kho:</strong> ${d.data.quantity_inStorage}<br><strong>Số lượng cần:</strong> ${d.data.quantity_need}<br>`)
//                 .style("opacity", 1)
//                 .style("left", (event.pageX - 350) + "px")
//                 .style("top", (event.pageY - 28) + "px");
//         })
//         .on("mouseout", function() {
//             // Hide the tooltip when mouse leaves the node
//             tooltip.style("opacity", 0);
//         });
// }