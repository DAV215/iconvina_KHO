<?php 
    if(isset($_POST['searchJob'])){
        $searchJob = $_POST['searchJob'];
        $sqlJob = "SELECT * FROM `tbl_Job` WHERE `name` LIKE '%$searchJob'";
        $query = mysqli_query($mysqli, $sqlJob);
    }else{
        $sqlJob = "SELECT * FROM `tbl_Job`";
        $query = mysqli_query($mysqli, $sqlJob);
    }

?>
<h1>Công việc</h1>
<div class="tableComponent">
    <form action="" method="post">
        <div class="searchBox">
            <input class="searchInput" type="text" name="searchJob" placeholder="Tìm công việc">
            <button class="searchButton" href="#">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input class="searchInput" type="text" name="ADD_job" placeholder="Thêm công việc">
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
                    <th>Công việc</th>
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
                <td><?php echo $row['job']?></td>
                <td class="tacvu">
                    <a href="admin.php?job=QLNS&action=chitiet&id=<?php echo $row['job']; ?>">Chi tiết</a>
                    <a href="admin.php?job=QLNS&action=permission&jobDelete=<?php echo $row['job'];?>">Xóa</a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>