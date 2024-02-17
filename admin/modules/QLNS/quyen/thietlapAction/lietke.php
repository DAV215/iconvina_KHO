<?php 
    if(isset($_POST['searchJobAction'])){
        $searchJobAction = $_POST['searchJobAction'];
        $sqlJobAction = "SELECT * FROM `tbl_jobaction` WHERE `job` LIKE '%$searchJobAction%' OR `action` LIKE '%$searchJobAction%'";

    }else{
        $sqlJobAction = "SELECT * FROM `tbl_jobaction`";
    }
    $query = mysqli_query($mysqli, $sqlJobAction);
?>
<h1>Hành động</h1>
<div class="tableComponent">
    <form action="" method="post">
        <div class="searchBox">
            <input class="searchInput" type="text" name="searchJobAction" placeholder="Tìm công việc">
            <button class="searchButton" href="#">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <select name="job" id="">
                <?php 
                    $sqlJob = "SELECT * FROM `tbl_job`";
                    $queryJob = mysqli_query($mysqli, $sqlJob);
                    $i = 0;
                    while ($row = mysqli_fetch_array($queryJob)) {
                        $i++;
                ?>
                <option value="<?php echo $row['job'] ?>">
                    <?php echo $row['job'] ?></option>
                <?php 
                    }
                ?>
            </select>
            <select name="ADD_JobAction" id=""  class="searchInput">
                <option value="Add">Thêm</option>
                <option value="Del">Xóa</option>
                <option value="Modify">Sửa</option>
                <option value="Aporval">Duyệt</option>
            </select>

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
                    <th>Hành động</th>
                    <th>Mã quyền</th>
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
                <td><?php echo $row['action']?></td>
                <td><?php echo $row['id']?></td>

                <td class="tacvu">
                    <a href="admin.php?job=QLNS&action=permission&jobDel=<?php echo $row['job'];?>&actionDel=<?php echo $row['action'];?>">Xóa</a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
