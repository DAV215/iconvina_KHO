function getALL_prod_cmd() {
    let search = $("#search_Prod_CMD").val();
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getALL_prod_cmd: 1,
            search_prod_cmd: search,
            pagenumber_Prods_cmd: pagenumber_Prods_cmd
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            data = response.data;
            quantity_el = response.quantity_el;
            $('#table3').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                retrieve: true,
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
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'time' },
                    { data: 'deadline' },
                    { data: 'progress_realtime' },
                    { data: 'receiver' },
                    { data: 'priority' },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QL_Production_CMD&action=thongke&actionChild=CMD_Detail&id_cmd=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 5
            });

        }
    });
}
var pagenumber_Prods_cmd = 1;

function getPage_Prod_cmd(button) {
    pagenumber_Prods_cmd = $(button).text();
}
getALL_prod_cmd();

function addMember() {
    // Get the input element and the selected value
    var searchInput = document.getElementById('searchStaff');
    var selectedValue = searchInput.value.trim(); // Trim whitespace from the input

    // Check if the value is not empty
    if (selectedValue !== '') {
        // Select the member input element by name attribute
        var memberInput = document.querySelector('input[name="member[]"]');

        // Append the selected value to the existing value of the member input
        memberInput.value += (memberInput.value ? ', ' : '') + selectedValue;

        // Clear the search input
        searchInput.value = '';
    }
}

function chat_send(button, div) {
    let container = button.closest(div);
    let form = {
        id_Prod_CMD: container.querySelector('input[name="id_Prod_CMD"]').value,
        id_user: container.querySelector('input[name="id_user"]').value,
        content: container.querySelector('input[name="content"]').value,
        progress: container.querySelector('input[name="progress"]').value,
    };
    $.ajax({
        url: "API/API_CHAT.php",
        data: {
            update_chat_Prod_CMD: 1,
            data: form
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {

        }
    });
}

function chat_get_prod_cmd(id_Prod_CMD, id_user) {
    let container = document.querySelector('.chatbox-container');
    $.ajax({
        url: "API/API_CHAT.php",
        data: {
            getdata_chat_prod_cmd: 1,
            id_Prod_CMD: id_Prod_CMD
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            $(".chatbox-container").empty();
            data_chat = response.data_chat;
            for (let i = 0; i < data_chat.length; i++) {
                let m = data_chat[i];
                let str = `                        
                <div class="chat-element">
                    <div class="chat-info">
                        <span> ${checkFullname(m['id_user'], alldataStaff)}  - ${m['time']} </span>
                    </div>
                    <div class="chat-comment">
                        <div class="chat-content">
                        ${m['comment']}
                        </div>
                    </div>
                </div>`;
                if (m['id_user'] == id_user) {
                    str = `                        
                    <div class="chat-element ofuser">
                        <div class="chat-info">
                            <span> ${checkFullname(m['id_user'], alldataStaff)}  - ${m['time']} </span>
                        </div>
                        <div class="chat-comment">
                            <div class="chat-content">
                            ${m['comment']}
                            </div>
 
                        </div>
                    </div>`;
                }
                $(".chatbox-container").append(str);
            }

        }
    });
}

function getFullname(id_user) {
    $.ajax({
        url: "API/API_USER.php",
        data: {
            getdataStaff_fromID: 1,
            id_user: id_user
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            return response.fullname;
        }

    });
}
var alldataStaff;

function getallData() {
    $.ajax({
        url: "API/API_USER.php",
        data: {
            getdataStaff: 1
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            alldataStaff = response;
        }
    });
    return alldataStaff;
}

getallData();

function checkFullname(id, data) {
    for (let i = 0; i < data.length; i++) {
        if (data[i].id == id) return data[i].fullname;
    }
}

function update_cmd(button) {
    let form = $('#production_cmd_form_update');
    $.ajax({
        url: "API/API_KHO.php",
        data: form.serialize(), // Send form data directly
        dataType: 'json', // Expect JSON response from the PHP script
        type: 'post',
        success: function(response) {
            // Handle success response if needed
            alert(response);
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error(xhr, status, error);
        }
    });
}

function getALL_prod_cmd_jobchild(id_prod_cmd) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getALL_prod_cmd_jobchild: 1,
            id_prod_cmd: id_prod_cmd
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            data = response;
            $('#table_jobchild').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
                retrieve: true,
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
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            // Trả về số thứ tự tăng dần bắt đầu từ 1
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'name_staff' },
                    { data: 'start' },
                    { data: 'finish' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let startTime = new Date(row.start);
                            let finishTime = new Date(row.finish);
                            let now = new Date();
                            let timeDiff = 0;

                            if (now > startTime) {
                                timeDiff = finishTime.getTime() - startTime.getTime();
                            } else {
                                timeDiff = finishTime.getTime() - startTime.getTime() - (now.getTime() - startTime.getTime());
                            }

                            // Chuyển đổi thành giờ và làm tròn đến 2 chữ số thập phân
                            let hoursDiff = (timeDiff / (1000 * 60 * 60)).toFixed(2);
                            return hoursDiff + ' giờ';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return row.percent_ofall + '%';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return row.progress + '%';
                        }
                    }
                ],

                "pageLength": 5
            });

        }
    });
}

function getALL_division_job(id_prod_cmd) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getALL_prod_cmd_jobchild: 1,
            id_prod_cmd: id_prod_cmd
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            data = response;
            let all_progress = 0;
            for (let i = 0; i < data.length; i++) {
                let t = data[i];
                let str = str_division_job(t.id_jobchild, t.name_staff, t.name, t.start, t.finish, t.percent_ofall);
                $("#table_division_job").append(str); // Sửa đổi ở đây
                all_progress += t.percent_ofall * (t.progress) / 100;
            }
            $("#table_division_job").append(str_division_job('', '', '', '', '', ''));
            $("#progress_PCMD").text(all_progress + '%');

        }
    });
}

function str_division_job(id_jobchild, name_staff, name_job, start, finish, percent_ofall) {
    let str = `
    <div class="item_CT">
        <input type="text" name="name_staff[]" placeholder="Tên nhân viên"
            onkeydown="addROW_(event,'item_CT','table_division_job')"
            style="max-width: 150px;" list="ALL_member_in_prod_cmd"
            value="${name_staff}" required>
        <input type="hidden" name="id_jobchild[]" value="${id_jobchild}">
        <input type="text" name="name_job[]" placeholder="Tên công việc"
            onkeydown="addROW_(event,'item_CT','table_division_job')"
            style="max-width: 150px;" value="${name_job}" required>
        <input type="datetime-local" name="start[]" placeholder="Bắt đầu"
            onkeydown="addROW_(event,'item_CT','table_division_job')"
            style="min-width: 200px;" value="${start}" required>
        <input type="datetime-local" name="finish[]" placeholder="Kết thúc"
            onkeydown="addROW_(event,'item_CT','table_division_job')"
            style="min-width: 200px;" value="${finish}" required>
        <input type="text" name="percent_ofall[]" placeholder="% của tổng"
            style="min-width: 50px;"
            onkeydown="addROW_(event,'item_CT','table_division_job')" value="${percent_ofall} %" required>
        <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.item_CT'); del_jobchild(${id_jobchild})"
            style="min-width: 50px;">X</button>
    </div>
`;
    return str;
}

function del_jobchild(id_jobchild) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            del_jobchild: 1,
            id_jobchild: id_jobchild
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {}
    });
}

function get_BOM_hidden_miss_M_of_C_PROD_CMD(id_prod, id_Component_parent, id_tbl) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_BOM_hidden_miss_M_of_C_in_PROD_CMD: 1,
            id_prod: id_prod,
            id_Component_parent: id_Component_parent
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
                    { data: 'code' },
                    { data: 'quantity' },
                    { data: 'quantity_geted' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return row.quantity_geted - row.quantity;
                        }

                    },
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