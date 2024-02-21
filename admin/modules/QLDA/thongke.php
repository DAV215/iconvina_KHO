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


    $sqlUserAll = "SELECT * FROM `tbl_project`";
    $queryALL = mysqli_query($mysqli, $sqlUserAll);
    $num_rows = mysqli_num_rows($queryALL);
    $totalPageofUser = round($num_rows/10, 2);

    if(isset($_GET['PageofProject'])){
        $PageofProject=$_GET['PageofProject'];
        $end = ($PageofProject-1)*10; 
        if(isset($_GET['searchProject'])){
            $searchProject = $_GET['searchProject'];
            $sqlProject = "SELECT * FROM `tbl_Project` WHERE `name` LIKE '%$searchProject%' LIMIT  $end,10";
    
        }else{
            $sqlUser = "SELECT * FROM `tbl_user` LIMIT $end,10";
        }
        $query = mysqli_query($mysqli, $sqlUser);
    }else{
        if(isset($_GET['searchProject'])){
            $searchProject = $_GET['searchProject'];
            $sqlProject = "SELECT * FROM `tbl_Project` WHERE `name` LIKE '%$searchProject%' LIMIT  10";
        }else{
            $sqlProject = "SELECT * FROM `tbl_Project` LIMIT 10";
        }
        $query = mysqli_query($mysqli, $sqlProject);
    }
?>

<h1>Dự án</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLDA">
            <input type="hidden" name="action" value="thongke">
            <input class="searchInput" type="text" name="searchProject" placeholder="Search">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input type="hidden" name="addProject" value = "true">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLDA">
        <input type="hidden" name="action" value="thongke">
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
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Thu nhập</th>
                    <th>Chi tiêu</th>
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
                <td><?php echo $row['startDay']?></td>
                <td><?php echo $row['finishDay']?></td>
                <td><?php echo $row['income']?></td>
                <td><?php echo $row['spent']?></td>
                <td class="tacvu">
                    <a href="admin.php?job=QLDA&action=thongke&actionChild=userDetail&id=<?php echo $row['id']; ?>">Chi tiết</a>
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
            <a href="admin.php?job=QLDA&action=thongke&PageofUser=<?php echo $i?>&searchUser=<?php echo isset($searchUser) ? urlencode($searchUser) : ''; ?>"><?php echo $i?></a>
        <?php
            }
        ?>
    </div>


</div>