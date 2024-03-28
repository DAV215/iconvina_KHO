<?php 

?>
<h1>Thống kê Nhập kho:</h1>
<div class="tableComponent" style="width:90%;">
    <table class="display dataTable " style="width:100%" id="table_import_listed">
        <thead>
            <tr class="">
                <th>Số thứ tự</th>
                <th>Tên</th>
                <th>Người nhập</th>
                <th>Ngày nhập</th>
                <th>Ghi chú</th>
                <th>Tác vụ</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
<script src="../asset/js/KHO/settingKho.js"></script>

<script>
    getAll_import_note();
</script>