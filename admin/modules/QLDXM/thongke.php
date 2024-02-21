<?php 
    include('../config/configDb.php');

?>
<?php

    $sqlbuysuggestAll = "SELECT * FROM `tbl_buysuggest`";
    $queryALL = mysqli_query($mysqli, $sqlbuysuggestAll);
    $num_rows = mysqli_num_rows($queryALL);
    $totalPageofbuysuggest = round($num_rows/10, 2);

    if(isset($_GET['PageofBuysuggest'])){
        $PageofBuysuggest=$_GET['PageofBuysuggest'];
        $end = ($PageofBuysuggest-1)*10; 
        if(isset($_GET['searchBuysuggest'])){
            $searchBuysuggest = $_GET['searchBuysuggest'];
            $sqlBuysuggest = "SELECT * FROM `tbl_buysuggest` WHERE 
            `nameDXM` LIKE '%$searchBuysuggest%' OR 
            `namebuyer` LIKE '%$searchBuysuggest%' OR 
            `daySuggest` LIKE '%$searchBuysuggest%' OR 
            `supplier_name` LIKE '%$searchBuysuggest%' LIMIT  $end,10";
        }else{
            $sqlbuysuggest = "SELECT * FROM `tbl_buysuggest` LIMIT $end,10";
        }
        $query = mysqli_query($mysqli, $sqlbuysuggest);
    }else{
        if(isset($_GET['searchBuysuggest'])){
            $searchBuysuggest = $_GET['searchBuysuggest'];
            $sqlBuysuggest = "SELECT * FROM `tbl_buysuggest` WHERE 
            `nameDXM` LIKE '%$searchBuysuggest%' OR 
            `namebuyer` LIKE '%$searchBuysuggest%' OR 
            `daySuggest` LIKE '%$searchBuysuggest%' OR 
            `supplier_name` LIKE '%$searchBuysuggest%' LIMIT 10";
        
        }else{
            $sqlBuysuggest = "SELECT * FROM `tbl_buysuggest` LIMIT 10";
        }
        $query = mysqli_query($mysqli, $sqlBuysuggest);
    }
?>

<h1>Đề xuất mua</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="dexuatmua">
            <input class="searchInput" type="text" name="searchBuysuggest" placeholder="Search">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="post">
        <div class="searchBox more1">
            <input type="hidden" name="addBuysuggest" value = "true">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLTC">
        <input type="hidden" name="action" value="dexuatmua">
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
                    <th>Tên đề xuất</th>
                    <th>Người đề xuất</th>
                    <th>Ngày đề xuất</th>
                    <th>Nhà cung cấp</th>
                    <th>Tình trạng</th>
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
                <td><?php echo $row['nameproject']?></td>
                <td><?php echo $row['nameDXM']?></td>
                <td><?php echo $row['namebuyer']?></td>
                <td><?php echo $row['daySuggest']?></td>
                <td><?php echo $row['supplier_name']?></td>
                <td>
                    <?php 
                        echo $row['bool_approve'] ? "Đã duyệt" : "Đang xét duyệt";
                    ?>
                </td>
                <td class="tacvu">
                    <a href="admin.php?job=QLTC&action=dexuatmua&actionChild=buysuggestDetail&id=<?php echo $row['id']; ?>">Chi tiết</a>
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
            while($i < $totalPageofbuysuggest){
                $i++;
        ?>
            <a href="admin.php?job=QLTC&action=dexuatmua&Pageofbuysuggest=<?php echo $i?>&searchbuysuggest=<?php echo isset($searchbuysuggest) ? urlencode($searchbuysuggest) : ''; ?>"><?php echo $i?></a>
        <?php
            }
        ?>
    </div>


</div>