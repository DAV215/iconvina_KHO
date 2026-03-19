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

function fill_client_datatable(data) {
    $('#client_listed_table').DataTable({
        data: data,
        retrieve: true,
        columnDefs: [
            { className: 'dt-center', targets: '_all' },
            { width: '30px', targets: [0] },
        ],


        columns: [{
                data: null,
                render: function(data, type, row, meta) {
                    // Trả về số thứ tự tăng dần bắt đầu từ 1
                    return meta.row + 1;
                }
            },
            { data: 'company' },
            { data: 'represent_user' },
            { data: 'addr' },
            { data: 'phone' },
            {
                data: 'id',
                render: function(data, type, row) {
                    return '<a href="admin.php?job=QL_Client&action=Client&actionChild=client_detail&id_client=' + data + '">Chi tiết</a>';
                }
            }
        ],
        "pageLength": 10
    });
}

function fill_client_data(data, id_container) {
    $(id_container).empty(); // Clear previous options
    data.forEach(element => {
        $(id_container).append(`<option value="${element.id}">${element.company} - ${element.represent_user}</option>`);
    });
}

var Promise_Client = new Promise((resolve, reject) => {
    $.ajax({
        url: "API/API_CLIENT.php",
        data: {
            client_listed_table: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(data) {
            // Call the t2 function with the received data
            fill_client_datatable(data);
            fill_client_data(data, "#represent_user")
            resolve();
        },
        error: reject // Reject the Promise in case of an error
    });
});