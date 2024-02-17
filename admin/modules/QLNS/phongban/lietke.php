<?php 
    if(isset($_POST['searchChucvu'])){
        $searchChucvu = $_POST['searchChucvu'];
        $sqlUser_Chucvu = "SELECT * FROM `tbl_chucvu` WHERE `department` LIKE '%$searchChucvu' OR `chucvu` LIKE '%$searchChucvu'";
    }else{
        $sqlUser_Chucvu = "SELECT * FROM `tbl_chucvu`";
    }
    $query_Chucvu = mysqli_query($mysqli, $sqlUser_Chucvu);

?>
<div class="tableComponent">
    <form action="" method="post">
        <div class="searchBox">
            <input class="searchInput" type="text" name="searchChucvu" placeholder="Tìm phòng ban">
            <button class="searchButton" href="#">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>

    <form action="" method="post">
        <div class="searchBox more1">
            <select name="department" id="">
                <?php 
                    $sqlUser = "SELECT * FROM `tbl_department`";
                    $query = mysqli_query($mysqli, $sqlUser);
                    $i = 0;
                    while ($row = mysqli_fetch_array($query)) {
                        $i++;
                ?>
                <option value="<?php echo $row['name'] ?>">
                    <?php echo $row['name'] ?></option>
                <?php 
                    }
                ?>
            </select>
            <input class="searchInput" type="text" name="ADD_chucvu" placeholder="Thêm chức vụ">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>

    <table class="data_table">
        <thead>
            <tr class="tableName">
                <th colspan="5">Chức vụ</th>
            </tr>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
                    <th>Phòng</th>
                    <th>Chức vụ</th>
                    <th>Số người</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            while ($row = mysqli_fetch_array($query_Chucvu)) {
                $i++;
            ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $row['department']?></td>
                <td><?php echo $row['chucvu']?></td>
                <td>
                    <?php 
                    $sql = "SELECT COUNT(*) as count FROM `tbl_user` WHERE `department` = '$row[department]' AND `chucvu` = '$row[chucvu]'";
                    $query_ = mysqli_query($mysqli, $sql);
                    $result = mysqli_fetch_assoc($query_);
                    $count = $result['count'];
                    echo $count;
                ?>
                </td>
                <td class="tacvu">
                    <a
                        href="admin.php?job=QLNS&action=department&depDelete=<?php echo $row['department'];?>&&chucvuDelete=<?php echo $row['chucvu']; ?>">Xóa</a>
                </td>
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
</div>