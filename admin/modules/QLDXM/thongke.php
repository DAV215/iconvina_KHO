<?php 
    include('../config/configDb.php');

?>
<?php 
    if(isset($_GET['searchBuysuggest'])){
        $searchBuysuggest = $_GET['searchBuysuggest'];
    }else{
        $searchBuysuggest = null;
    }
?>
<h1>Đề xuất mua</h1>
<div class="tableComponent pc">
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
    <table class="data_table pc">
        <thead>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
                    <th>Dự án</th>
                    <th>Tên đề xuất</th>
                    <th>Tổng tiền</th>
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
            $temp_DXM = new getBuySuggest;
            $idUser = $_SESSION['userINFO']['id'];
            if(!isset($_GET['searchBuysuggest'])){
                $search = null;
            }else $search = $_GET['searchBuysuggest'];
            if(!isset($_GET['PageofBuysuggestMB'])){
                $All_DXM = $temp_DXM->getDXM_ofUSER_followPAGE($idUser, 5,1, $search);
            }else{
                $page = $_GET['PageofBuysuggestMB'];
                $All_DXM = $temp_DXM->getDXM_ofUSER_followPAGE($idUser,5, $page, $search);
            }
            $i = 0;
            foreach ($All_DXM as $row) {
                $i++;
            ?>
            <tr>
                <td><?php echo $i ?></td>
                <td><?php echo $row['nameproject']?></td>
                <td><?php echo $row['nameDXM']?></td>
                <td><?php echo number_format($row['money'])?></td>
                <td><?php echo $row['namebuyer']?></td>
                <td><?php echo $row['daySuggest']?></td>
                <td><?php echo $row['supplier_name']?></td>
                <td>
                    <?php 
                        echo $row['bool_approve'] ? "Đã duyệt" : "Đang xét duyệt";
                    ?>
                </td>
                <td class="tacvu">
                    <a href="admin.php?job=QLTC&action=dexuatmua&actionChild=buysuggestDetail&idBuySuggest=<?php echo $row['id']; ?>">Chi tiết</a>
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
            while($i < $temp_DXM->getNumberPage2($_SESSION['userINFO']['id'], 5, $searchBuysuggest)){
                $i++;
        ?>
            <a href="admin.php?job=QLTC&action=dexuatmua&PageofBuysuggestMB=<?php echo $i ?>&searchBuysuggest=<?php echo isset($searchBuysuggest) ? urlencode($searchBuysuggest) : ''; ?>" 
                <?php
                    if(isset($_GET['PageofBuysuggestMB'])){
                        echo ($_GET['PageofBuysuggestMB'] == $i? 'style="background:tomato;"' : '');
                    }
                ?>
            >
                <?php echo $i ?>
            </a>

        <?php
            }
        ?>
    </div> 


</div>
<div class="tableComponent mb">
    <form action="" method="get" class = "search_MB">
        <input type="hidden" name="job" value="QLTC">
        <input type="hidden" name="action" value="dexuatmua">
        <input type="text" name="searchBuysuggest" placeholder="Tìm đề xuất mua" value="<?php echo isset($_GET['searchBuysuggest']) ? $_GET['searchBuysuggest'] : ''; ?>">

        <button type="submit">Tìm</button>
    </form>
    <div class="data_table mb">

        <?php
            $temp_DXM = new getBuySuggest;
            $idUser = $_SESSION['userINFO']['id'];
            $row_of_page = 3;
            if(!isset($_GET['PageofBuysuggestMB'])){
                $All_DXM = $temp_DXM->getDXM_ofUSER_followPAGE($idUser, $row_of_page,1, $search);
            }else{
                $page = $_GET['PageofBuysuggestMB'];
                $All_DXM = $temp_DXM->getDXM_ofUSER_followPAGE($idUser,$row_of_page, $page, $search);
            }
            $i = 0;
            foreach ($All_DXM as $row) {
                $i++;
            ?>
            <a href="admin.php?job=QLTC&action=dexuatmua&actionChild=buysuggestDetail&idBuySuggest=<?php echo $row['id']; ?>">
        
            <div class="table-item-mb">
                    <ul class="main_conntent_tbl">
                        <div class="stt_of_row">
                            <span><?php echo $row['daySuggest']?></span>
                            <span style="color:tomato; font-weight:bolder;"><?php echo $row['bool_approve'] ? "Đã duyệt" : "Đang xét duyệt";?></span>
                        </div>
                        <li></li>
                        <li><?php echo $row['namebuyer']?></li>
                        <li><?php echo $row['nameDXM'].'----'.number_format($row['money'])?></li>
                    </ul>

                </div>
        
            </a>
                

            <?php
            }
        ?>
    </div>
    <div class="allPage">
        <div class="Pagination mb">
            <?php
                $i = 0;
                while($i < $temp_DXM->getNumberPage2($_SESSION['userINFO']['id'], $row_of_page, $searchBuysuggest)){
                    $i++;
            ?>
                <a href="admin.php?job=QLTC&action=dexuatmua&PageofBuysuggestMB=<?php echo $i ?>&searchBuysuggest=<?php echo isset($searchBuysuggest) ? urlencode($searchBuysuggest) : ''; ?>" 
                    <?php
                        if(isset($_GET['PageofBuysuggestMB'])){
                            echo ($_GET['PageofBuysuggestMB'] == $i? 'style="background:tomato;"' : '');
                        }
                    ?>
                >
                    <?php echo $i ?>
                </a>

            <?php
                }
            ?>
        </div> 
    </div>
</div>
<div class="report">
        <h3> Tổng đề xuất mua theo tìm kiếm: <?php echo number_format($temp_DXM->getTotal($idUser, $search)) ?></h3>
    </div>