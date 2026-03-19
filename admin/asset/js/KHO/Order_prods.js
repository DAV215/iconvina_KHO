function fill_order_listed() {
    $.ajax({
        url: "API/API_CLIENT.php",
        data: {
            order_listed: 1
        },
        dataType: 'json', // Expect JSON response from the PHP script
        type: 'post',
        success: function(response) {
            $('#tbl_listed_order').DataTable({
                data: response,
                retrieve: true,
                columnDefs: [
                    { className: 'dt-center', targets: '_all' },
                    { width: '30px', targets: [0] },
                ],
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'start_day' },
                    { data: 'finish_day' },
                    { data: 'id_client' },
                    { data: 'priority' },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QL_Client&action=Client&actionChild=client_detail&id_client=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 10
            });
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error(xhr, status, error);
        }
    });
}
fill_order_listed();