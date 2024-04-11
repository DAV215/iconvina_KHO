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
                    { data: 'name', width: '10%' },
                    { data: 'quantity' },
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

function update_process(button, div) {
    let container = button.closest(div);
    let form = {
        job_child_prod_cmd: $("#job_child_prod_cmd").val(),
        progress: container.querySelector('input[name="process_ofself"]').value,
        id_prod_cmd: container.querySelector('input[name="id_prod_cmd"]').value,
    };
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            update_process_jobChild_Prod_CMD: 1,
            data: form
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            getALL_prod_cmd_jobchild(form.id_prod_cmd);
            getALL_division_job(form.id_prod_cmd);
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
    return $.ajax({
        url: "API/API_USER.php",
        data: {
            getdataStaff: 1
        },
        dataType: 'JSON',
        type: 'post'
    });
}

getallData().then(function(response) {
    fillmember_list(response, '#manager');
    alldataStaff = response;
});

function fillmember_list(data, id_datalist) {
    $(id_datalist).empty();
    for (let i = 0; i < data.length; i++) {
        let option = $("<option>").val(data[i].fullname).text(data[i].fullname);
        $(id_datalist).append(option);
    }
}

// fillmember_list(alldataStaff, '#manager');

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
            $("#job_child_prod_cmd").empty();
            for (let i = 0; i < data.length; i++) {
                let t = data[i];
                let str = str_division_job(t.id_jobchild, t.name_staff, t.name, t.start, t.finish, t.percent_ofall);
                $("#table_division_job").append(str); // Sửa đổi ở đây
                all_progress += t.percent_ofall * (t.progress) / 100;
                $("#job_child_prod_cmd").append(`<option value="${t.id_jobchild}">${t.name}</option>`);
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

function get_BOM_hidden_miss_M_of_C_PROD_CMD(id_prod, id_Component_parent, id_tbl, quantity_component) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_BOM_hidden_miss_M_of_C_in_PROD_CMD: 1,
            id_prod: id_prod,
            id_Component_parent: id_Component_parent,
            quantity_component: quantity_component
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
var all_PROD_CMD_unique;

function getALL_prod_cmd_fill_map(callback) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            getALL_prod_cmd: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(result) {
            let data = result.data;
            let arr_unique = [];

            for (let i = 0; i < data.length; i++) {
                let id_component = data[i].id_component;
                let quantity = parseInt(data[i].quantity);
                let name = data[i].name;

                let found = false;
                for (let j = 0; j < arr_unique.length; j++) {
                    if (arr_unique[j].id_component === id_component) {
                        arr_unique[j].quantity += quantity;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    let temp = { id_component: id_component, name: name, quantity: quantity };
                    arr_unique.push(temp);
                }
            }
            callback(arr_unique); // Pass the data to the callback function
        }
    });
}

// Usage
getALL_prod_cmd_fill_map(function(data, error) {
    all_PROD_CMD_unique = data;
});
var chart_prod_cmd = null;

function tree2_Prod_CMD(data, quantity_component) {
    chart_prod_cmd = new d3.OrgChart().container('.chart-container').data(data).compactMarginBetween((d) => 50).compactMarginPair((d) => 200)
        .nodeContent(function(d, i, arr, state) {
            let background = '#FFFFFF';
            let color = '#08011E'; // Khai báo biến color
            let content = 0;
            if (parseInt(d.data.quantity * quantity_component) > parseInt(d.data.quantity_inStorage)) {
                background = 'red';
                color = 'white';
                for (let i = 0; i < all_PROD_CMD_unique.length; i++) {
                    let temp = all_PROD_CMD_unique[i];
                    if (d.data.real_id === temp.id_component) {
                        content = 'Đang sản xuất tổng cộng: ' + temp.quantity;
                    }
                }
            } else {
                background = '#FFFFFF';
                color = '#08011E';
            }
            let data_all = { id: d.data.real_id, name: d.data.name };

            return `
                <div style="font-family: 'Inter', sans-serif;background-color:${background}; position:absolute;margin-top:-1px; margin-left:-1px;width:${200}px;height:${d.height}px;border-radius:10px 0 0 10px;border: 1px solid #E4E2E9">
                
                    <img src=" ${
                        d.data.img
                    }" style="position:absolute;margin-left:${200}px;border-radius:0 5px 5px 0;width:100px;height:150px; object-fit: cover;" />
                    
                    <div style="color:${color};position:absolute;right:20px;top:17px;font-size:10px;">
    <i class="fas fa-ellipsis-h" onclick="modal_form_add_Childjob_PROD_CMD({ id: '${d.data.real_id}', name: '${d.data.name}',  level: '${d.data.level}' ,  quantity_need: '${d.data.quantity*quantity_component - d.data.quantity_inStorage}'})"></i>
</div>
                    <div style="font-size:15px;color:${color};margin-left:20px;margin-top:32px">              
                        <a style="color:${color};font-size: 16px;text-decoration: none; " href="${d.data.link}"> ${d.data.name}</a></div>
                    <div style="color:${color};margin-left:20px;margin-top:3px;font-size:14px;" class="quantity_inStorage"> Trong kho: ${
                        d.data.quantity_inStorage
                    } </div>
                    <div style="color:${color};margin-left:20px;margin-top:3px;font-size:14px;"> Cần: ${
                        d.data.quantity*quantity_component
                    } </div>
                    <div style="color:${color};margin-left:20px;margin-top:3px;font-size:14px;"> ${
                        content
                    } </div>
                </div>
                `;
        })
        .render();

}

function treeMap_DMNL_Prod_CMD(id_component_parent, quantity_component) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            treeMap_DMNL: 1,
            id_component_parent: id_component_parent
        },
        dataType: 'JSON',
        type: 'post',
        success: function(response) {
            tree2_Prod_CMD(response, quantity_component)
        }
    });
}

function modal_form_add_Childjob_PROD_CMD(data) {
    if (data.level > 0) {
        $('#modal_form_add_JOB').modal('show');
        $('#name_ofJob_add').text(data.name);
        $('#modal_form_add_JOB input[name="name_production_cmd"]').val(data.name);
        $('#modal_form_add_JOB input[name="quantity_production"]').val(data.quantity_need);
        $('#modal_form_add_JOB input[name="id_component"]').val(data.id);
    } else {
        alert('Đây là nguyên liệu thô, làm phiếu nhập kho !')
    }

}

function get_BOM_to_export(id_prod, id_Component_parent, id_modal, quantity_component) {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            get_BOM_hidden_miss_M_of_C_in_PROD_CMD: 1,
            id_prod: id_prod,
            id_Component_parent: id_Component_parent,
            quantity_component: quantity_component
        },
        dataType: 'JSON',
        type: 'POST',
        success: function(response) {
            console.table(response)
            $("#table_material_CT").empty();
            $("#table_component_CT").empty();
            for (let i = 0; i < response.length; i++) {
                let row = response[i];
                if (row.type == 'Material') {
                    let str = `
                        <div class="item_CT">
                            <input type="text" name="name_Material[]" placeholder="Sản phẩm con"
                                onchange="show_value_Storage_Material(this, getdata_Material())"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')"
                                list="ALL_data_material" value="${row.name}">
                            <datalist id="ALL_data_material">

                            </datalist>

                            <input type="number" name="quantity_Material_need[]" placeholder="Số lượng"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')" value="${(row.quantity)}">

                            <input type="number" name="price_Material[]" placeholder="Giá tiền"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')">
                                
                            <input type="number" name="price_Material_Storage" placeholder="Giá bán đề xuất"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')" disabled>
                            <input type="hidden" name="id_material[]" value="${row.id}">
                            <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled value="${row.quantity_inStorage}">
                            <button type="button" name="delete_ITEM_CT" onclick="delROW_(this, '.item_CT')">X</button>
                        </div>
                    `;
                    $("#table_material_CT").append(str);
                } else {
                    let str = `
                        <div class="component_CT">
                            <input type="text" name="name_Component[]" placeholder="Sản phẩm con" list="ALL_data_Component"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"
                                onchange="show_value_Storage_Component(this)" value="${row.name}">
                            <datalist id="ALL_data_Component">

                            </datalist>
                            <input type="number" name="quantity_Component_need[]" placeholder="Số lượng"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')"  value="${(row.quantity)}">
                                <input type="number" name="price_Component[]" placeholder="Giá tiền"
                                onkeydown="addROW_(event,'item_CT','table_material_CT')" >
                                <input type="number" name="price_Material_Storage" placeholder="Giá bán đề xuất"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled>
                            <input type="number" name="quantity_Component_inStorage" placeholder="Số lượng trong kho"
                                onkeydown="addROW_(event, 'component_CT', 'table_component_CT')" disabled value="${row.quantity_inStorage}">
                            <input type="hidden" name="id_component[]" value="${row.id}">
                            <input type="hidden" name="level_component[]" >
                            <button type="button" name="delete_ITEM_CT" onclick="delROW_(this,'.component_CT')">X</button>
                        </div>
                    `;
                    $("#table_component_CT").append(str);

                }
                $('select[name="purpose"]').val('Sản xuất nội bộ');
                $('#modal_form_export_material textarea[name="note"]').val('Dự toán xuất cho: ' + quantity_component + ' sản phẩm');
                let name_export = document.getElementById("ALL_data_PROD_CMD").options;
                for (let i = 0; i < name_export.length; i++) {
                    let regex = /^\d+/;
                    let number = name_export[i].value.match(regex)[0];
                    if (number == id_prod) {
                        $('input[name="name_export"]').val(name_export[i].value);
                    }
                }



            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX request failed:', error);
        }
    });
}

function modal_form_export_material_from_PROD() {
    $('#modal_form_export_material').modal('show');
}

function modal_form_import_material_from_PROD() {
    $('#modal_form_import_material').modal('show');
}