function material_listed_to_table() {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            material_listed_table: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(data) {
            $('#material_listed_table').DataTable({


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
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'quantity' },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 10
            });

        }
    });
}
material_listed_to_table();

function component_listed_to_table() {
    $.ajax({
        url: "API/API_KHO.php",
        data: {
            component_listed_table: 1,
        },
        dataType: 'JSON',
        type: 'post',
        success: function(data) {
            $('#component_listed_table').DataTable({


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
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'level' },
                    { data: 'quantity' },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return '<a href="admin.php?job=QLKHO&action=thongke&actionChild=ComponentDetail&id_Component_parent=' + data + '">Chi tiết</a>';
                        }
                    }
                ],
                "pageLength": 10
            });

        }
    });
}
component_listed_to_table();