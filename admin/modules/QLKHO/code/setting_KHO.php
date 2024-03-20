<?php 
Position::updateSUM();
Classify::updateSUM();
?>
<h2>Vị trí </h2>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="search_Material" id="search_Position" placeholder="Search"
                oninput="show_setting_Position()">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <form action="" method="get">
        <div class="searchBox more1">
            <input type="hidden" name="job" value="QLKHO">
            <input type="hidden" name="action" value="thongke">
            <input type="hidden" name="actionChild" value="addFILE_ADD">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </form>
    <form action="">
        <input type="hidden" name="job" value="QLKHO">
        <input type="hidden" name="action" value="thongke">
        <input type="hidden" name="actionChild" value="thongke">
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
                    <th>Kho</th>
                    <th>Hàng</th>
                    <th>Cột</th>
                    <th>Vị trí trên kệ </th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody id="tbl_Position">

        </tbody>
    </table>
    <div class="Pagination" id="pagination_Position">

    </div>
</div>
<h2>Danh mục</h2>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="search_Material" id="search_Classify" placeholder="Search"
                oninput="show_setting_Classify()">
            <button class="searchButton" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
    <table class="data_table">
        <thead>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
                    <th>Danh mục chính</th>
                    <th>Danh mục phụ</th>
                    <th>Note</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody id="tbl_Classify">

        </tbody>
    </table>
    <div class="Pagination" id="pagination_Classify">

    </div>
</div>
<script src="../asset/js/KHO/settingKho.js"></script>
