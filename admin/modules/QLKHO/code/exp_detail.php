<?php 
    $id_exp = $_GET['id_exp'];
    $info_exp = export_material::getAll('*', "id = $id_exp")[0];
?>
<h1>Chi tiết phiếu xuất:</h1>
<h2 style="color:tomato;"> <?php echo $info_exp['name'] ?></h2>
<h3>Người nhập: <?php echo $info_exp['created_by'] ?></h3>
<h3>Ngày nhập: <?php echo $info_exp['date'] ?></h3>
<h3>Ghi chú: <?php echo $info_exp['note'] ?></h3>
<div class="tableComponent" style="width:90%;">
    <table class="display dataTable  cell-border compact " style="width:100%" id="table_exp_detail">
        <thead>
            <tr class="">
                <th>Số thứ tự</th>
                <th>Tên</th>
                <th>Loại</th>
                <th>Số lượng</th>
                <th>Giá xuất</th>
                <th>Thành tiền</th>
                <th>Tác vụ</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <h2 id="total"></h2>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css">
<script src="../asset/js/KHO/settingKho.js"></script>

<script>
    get_export_note_detail(<?php echo $id_exp ?>);
</script>