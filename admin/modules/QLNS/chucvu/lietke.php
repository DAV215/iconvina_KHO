<?php 
    if(isset($_POST['searchPhongban'])){
        $searchPhongban = $_POST['searchPhongban'];
        $sqlUser = "SELECT * FROM `tbl_department` WHERE `name` LIKE '%$searchPhongban'";
        $query = mysqli_query($mysqli, $sqlUser);
    }else{
        $sqlUser = "SELECT * FROM `tbl_department`";
        $query = mysqli_query($mysqli, $sqlUser);
    }
    include('../config/configDb.php');
    $per[] = getPermission(getIDbyUNAME($_SESSION['username_Login']));
?>
<div class="tableComponent">
    <form action="" method="post">
        <div class="searchBox">
            <input class="searchInput" type="text" name="searchPhongban" placeholder="Tìm phòng ban">
            <button class="searchButton" href="#">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <?php ?>
            <input class="searchInput" type="text" name="ADD_department" placeholder="Thêm phòng ban">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>

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
                    $sql = "SELECT COUNT(*) as count FROM `tbl_user` WHERE `department` = '$row[name]'";
                    $query_ = mysqli_query($mysqli, $sql);
                    $result = mysqli_fetch_assoc($query_);
                    $count = $result['count'];
                    echo $count;
                ?>
                </td>
                <td class="tacvu">
                    <a href="admin.php?job=QLNS&action=chitiet&id=<?php echo $row['id']; ?>">Chi tiết</a>
                    <a href="admin.php?job=QLNS&action=department&phongDelete=<?php echo $row['name'];?>">Xóa</a>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>
