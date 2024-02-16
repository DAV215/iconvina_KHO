<?php 
    include('../config/configDb.php');

?>
<?php
    $sqlUser = "SELECT * FROM `tbl_department`";
    $query = mysqli_query($mysqli, $sqlUser);
?>
<div class="searchBox">
    <input class="searchInput" type="text" name="" placeholder="Search">
    <button class="searchButton" href="#">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
</div>
<div class="searchBox more1">
    <input class="searchInput" type="text" name="" placeholder="Search">
    <button class="searchButton" href="department.php/FFF">
        <i class="fa-solid fa-plus"></i>
    </button>
</div>
<table class="data_table">
    <thead>
        <tr class="headerTable">
            <div class="rowTitle">
                <th>Số thứ tự</th>
                <th>Phòng</th>
                <th>Số người</th>
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
            <td><?php echo $row['name']?></td>
            <td>
                <?php 
                    $sql = "SELECT COUNT(*) as count FROM `tbl_chucvu` WHERE `department` = '$row[name]'";
                    $query_ = mysqli_query($mysqli, $sql);
                    $result = mysqli_fetch_assoc($query_);
                    $count = $result['count'];
                    echo $count;
                ?>
            </td>
            <td class="tacvu">
                <a href="admin.php?job=QLNS&action=chitiet&id=<?php echo $row['id']; ?>">Chi tiết</a>
            </td>
        </tr>
        <?php
            }
            ?>
    </tbody>
</table>
<h1>Bổ sung chức vụ</h1>