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
            // $("#tbody_Prod_CMD").empty();
            // $("#pagination_Prod_CMD").empty();
            // let page_quantity = Math.ceil(quantity_el / 5);
            // for (let i = 0; i < data.length; i++) {
            //     m = data[i];
            //     let str = `
            //     <tr>
            //         <td>${i + (pagenumber_Prods_cmd-1)*5}</td>
            //         <td>${m['name']}</td>
            //         <td>${m['time']}</td>
            //         <td>${m['deadline']}</td>
            //         <td>${m['progress_realtime']}</td>
            //         <td>${m['receiver']}</td>
            //         <td>${m['priority']}</td>
            //         <td class="tacvu">
            //             <a href="admin.php?job=QL_Production_CMD&action=thongke&actionChild=CMD_Detail&id_cmd=${m['id']}">
            //                 Chi tiết
            //             </a>
            //         </td>
            //     </tr>
            // `;
            //     $("#tbody_Prod_CMD").append(str);
            // }
            // for (let i = 0; i < page_quantity; i++) {
            //     let str = `<button onclick="getPage_Prod_cmd(this); getALL_prod_cmd()">${i + 1}</button>`;
            //     $('#pagination_Prod_CMD').append(str);
            // }
            $('#table3').DataTable({
                pagingType: 'simple_numbers',
                scrollX: true,
                data: data,
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
                $("#progress_PCMD").text(response.progress + '%');
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