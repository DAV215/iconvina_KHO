<?php 
    include('../config/configDb.php');

?>
<?php

    // if(isset($_POST['searchUser'])){
    //     $searchUser = $_POST['searchUser'];
    //     $sql_searchUser = "SELECT * FROM `tbl_user` WHERE `fullname` LIKE '%$searchUser%' OR `username` LIKE '%$searchUser%' OR `sdt` LIKE '%$searchUser%' OR `department` LIKE '%$searchUser%' OR `chucvu` LIKE '%$searchUser%'";

    // }else{
    //     $sql_searchUser = "SELECT * FROM `tbl_user`";
    // }
    // $query = mysqli_query($mysqli, $sql_searchUser);


    $sqlUserAll = "SELECT * FROM `tbl_user`";
    $queryALL = mysqli_query($mysqli, $sqlUserAll);
    $num_rows = mysqli_num_rows($queryALL);
    $totalPageofUser = round($num_rows/10, 2);

    if(isset($_GET['PageofUser'])){
        $PageofUser=$_GET['PageofUser'];
        $end = ($PageofUser-1)*10; 
        if(isset($_GET['searchUser'])){
            $searchUser = $_GET['searchUser'];
            $sqlUser = "SELECT * FROM `tbl_user` WHERE `fullname` LIKE '%$searchUser%' OR `username` LIKE '%$searchUser%' OR `sdt` LIKE '%$searchUser%' OR `department` LIKE '%$searchUser%' OR `chucvu` LIKE '%$searchUser%' LIMIT  $end,10";
    
        }else{
            $sqlUser = "SELECT * FROM `tbl_user` LIMIT $end,10";
        }
        $query = mysqli_query($mysqli, $sqlUser);
    }else{
        if(isset($_GET['searchUser'])){
            $searchUser = $_GET['searchUser'];
            $sqlUser = "SELECT * FROM `tbl_user` WHERE `fullname` LIKE '%$searchUser%' OR `username` LIKE '%$searchUser%' OR `sdt` LIKE '%$searchUser%' OR `department` LIKE '%$searchUser%' OR `chucvu` LIKE '%$searchUser%' LIMIT  10";
        }else{
            $sqlUser = "SELECT * FROM `tbl_user` LIMIT 10";
        }
        $query = mysqli_query($mysqli, $sqlUser);
    }
?>

<h1>Nhân sự</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLNS">
            <input type="hidden" name="action" value="personnel">
            <input class="searchInput" type="text" name="searchUser" placeholder="Search">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input type="hidden" name="addUser" value = "true">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLNS">
        <input type="hidden" name="action" value="personnel">
        <div class="searchBox more2">
            <button class="searchButton" href="">
                <i class="fa-solid fa-filter-circle-xmark"></i>
            </button>
        </div>
    </form>
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
    <div class="Pagination">
        <?php
            $i = 0;
            while($i < $totalPageofUser){
                $i++;
        ?>
            <a href="admin.php?job=QLNS&action=personnel&PageofUser=<?php echo $i?>&searchUser=<?php echo isset($searchUser) ? urlencode($searchUser) : ''; ?>"><?php echo $i?></a>
        <?php
            }
        ?>
    </div>
</div>