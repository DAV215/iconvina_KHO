<?php 
    include_once('QLNS/getdataUser.php');
    include('../config/configDb.php');
    $sql = "SELECT * FROM `tbl_phanquyenduyet`";
    $query = mysqli_query($mysqli, $sql);
    $data = [];
    while ($row = mysqli_fetch_array($query)){
        $data[]= $row;
    }
    // print_r($data);
    foreach($data as $row){
        if($row['permission']=='admin1'){
            $UserNameA1 = $row['username'];
        }elseif($row['permission']=='admin2'){
            $UserNameA2 = $row['username'];
        }
        elseif($row['permission']=='thuquy'){
            $UserNameTQ = $row['username'];
        }elseif($row['permission']=='ketoan'){
            $UserNameKT= $row['username'];
        }
    }

$phieuchi = new getPhieuChi;
$phieuchi_Phanquyen = $phieuchi -> getPC_Phanquyen(getPhanQuyenDuyet($_SESSION['userINFO']['username']));
?>
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
            foreach ($phieuchi_Phanquyen as $row)  {
                $i++;
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
    <!-- <div class="Pagination">
        <?php
            $i = 0;
            while($i < $totalPageofPhieuchi){
                $i++;
        ?>
            <a href="admin.php?job=QLTC&action=phieuchi&PageofPhieuchi=<?php echo $i?>&searchphieuchi=<?php echo isset($searchphieuchi) ? urlencode($searchphieuchi) : ''; ?>"><?php echo $i?></a>
        <?php
            }
        ?>
    </div> -->


</div>