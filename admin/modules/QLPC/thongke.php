<?php 
    include('../config/configDb.php');
    // include('QLNS/getdataUser.php');

    $classPhieuChi = new getPhieuChi();
    $allPhieuChi = $classPhieuChi->getAll();
?>
<?php

$sqlphieuchiAll = "SELECT * FROM `tbl_phieuchi`";
$queryALL = mysqli_query($mysqli, $sqlphieuchiAll);
$num_rows = mysqli_num_rows($queryALL);
$totalPageofPhieuchi = round($num_rows/10, 2);
if(isset($_GET['searchPhieuchi'])){
    $searchPhieuchi = $_GET['searchPhieuchi'];
}else{
    $searchPhieuchi = '';

}
if(isset($_GET['PageofPhieuchi'])){
    $PageofPhieuchi = $_GET['PageofPhieuchi'];
    $end = ($PageofPhieuchi-1)*10; 
    if(isset($_GET['searchPhieuchi'])){
        $searchPhieuchi = $_GET['searchPhieuchi'];
        $sqlPhieuchi = "SELECT * FROM `tbl_phieuchi` WHERE 
        `name` LIKE '%$searchPhieuchi%' OR 
        `nguoitaolenh` LIKE '%$searchPhieuchi%' OR 
        `createDay` LIKE '%$searchPhieuchi%' OR 
        `taikhoanchi` LIKE '%$searchPhieuchi%' OR 
        `loaichi` LIKE '%$searchPhieuchi%' ORDER BY `id` DESC LIMIT  $end,10";
    }else{
        $sqlPhieuchi = "SELECT * FROM `tbl_phieuchi` ORDER BY `id` DESC LIMIT $end,10";
    }
    $query = mysqli_query($mysqli, $sqlPhieuchi);
}else{
    if(isset($_GET['searchPhieuchi'])){
        $searchPhieuchi = $_GET['searchPhieuchi'];
        $sqlPhieuchi = "SELECT * FROM `tbl_phieuchi` WHERE 
        `name` LIKE '%$searchPhieuchi%' OR 
        `nguoitaolenh` LIKE '%$searchPhieuchi%' OR 
        `taikhoanchi` LIKE '%$searchPhieuchi%' OR 
        `createDay` LIKE '%$searchPhieuchi%' OR 
        `loaichi` LIKE '%$searchPhieuchi%' ORDER BY `id` DESC LIMIT 10";
    }else{
        $sqlPhieuchi = "SELECT * FROM `tbl_phieuchi` ORDER BY `id` DESC LIMIT 10";
    }
    $query = mysqli_query($mysqli, $sqlPhieuchi);
}

?>

<h1>Phiếu Chi</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="searchPhieuchi" placeholder="Search">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input type="hidden" name="addPhieuchi" value = "true">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLTC">
        <input type="hidden" name="action" value="phieuchi">
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
                    <th>Dự án</th>
                    <th>Tên phiếu chi</th>
                    <th>Tổng tiền</th>
                    <th>Quy trình</th>
                    <th>Người tạo lệnh</th>
                    <th>Ngày tạo</th>
                    <th>Ngày duyệt</th>
                    <th>Tình trạng</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody>
        <?php
            $i = 0;
            $total = 0;
            while ($row = mysqli_fetch_array($query)) {
                $i++;
                $total += $row['total'];
            ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $row['nameproject'] ?? '' ?></td>
                <td><?php echo $row['name'] ?? '' ?></td>
                <td><?php echo number_format($row['total']) ?></td>
                <td><?php echo $row['quytrinh'] ?? '' ?></td>
                <td><?php echo $row['nguoitaolenh'] ?? '' ?></td>
                <td><?php echo $row['createDay'] ?? '' ?></td>
                <td><?php echo $row['successDay'] ?? '' ?></td>
                <td>
                    <?php 
                        echo isset($row['bool_AllApprove']) && $row['bool_AllApprove'] ? "Đã duyệt" : "Đang xét duyệt";
                    ?>
                </td>
                <td class="tacvu">
                    <a href="admin.php?job=QLTC&action=phieuchi&actionChild=phieuchiDetail&idPhieuChi=<?php echo $row['id']; ?>">Chi tiết</a>
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
            while($i < $totalPageofPhieuchi){
                $i++;
        ?>
            <a href="admin.php?job=QLTC&action=phieuchi&PageofPhieuchi=<?php echo $i?>&searchphieuchi=<?php echo isset($searchphieuchi) ? urlencode($searchphieuchi) : ''; ?>"><?php echo $i?></a>
        <?php
            }
        ?>
    </div>
    <div class="report">
        <h1>Tổng chi: <?php echo  number_format($classPhieuChi->getTotal($searchPhieuchi)); ?></h1>
    </div>
</div>