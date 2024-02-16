
<?php 
    include('../config/configDb.php');

?>
<?php
    $sqlUser = "SELECT `tbl_user`.`id`,`tbl_user`.`username`, `tbl_user`.`fullname` ,`tbl_user`.`sdt` , `tbl_chucvu`.`department` , `tbl_chucvu`.`chucvu` FROM `tbl_user` JOIN `tbl_chucvu` ON `tbl_user`.`id_department` = `tbl_chucvu`.`stt`;";
    $query = mysqli_query($mysqli, $sqlUser);
?>
<div class="searchBox">
    <input class="searchInput" type="text" name="" placeholder="Search">
    <button class="searchButton" href="#">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
</div>
<table class="data_table">
    <thead>
        <tr class="headerTable">
            <div class="rowTitle">
                <th>Số thứ tự</th>
                <th>Tên</th>
                <th>Số điện thoại</th>
                <th>Phòng</th>
                <th>Chức vụ</th>
                <th>Tác vụ</th>
            </div>
        </tr>
    </thead>
    <tbody>
        <?php
            $i = 0;
            while ($row = mysqli_fetch_array($query)) {
                $i++;
            ?>
        <tr>
            <td><?php echo $i ?></td>
            <td><?php echo $row['fullname']?></td>
            <td><?php echo $row['sdt']?></td>
            <td><?php echo $row['department']?></td>
            <td><?php echo $row['chucvu']?></td>
            <td class="tacvu">
                <a href="admin.php?job=QLNS&action=chitiet&id=<?php echo $row['id']; ?>">Chi tiết</a>
            </td>
        </tr>
        <?php
            }
            ?>
    </tbody>

</table>