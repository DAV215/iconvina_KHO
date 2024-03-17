<?php 
    $material = new material;
    $component = new component;
?>
<h1>Vật liệu thô</h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="search_Material" id="search_Material" placeholder="Search"
                oninput="Litsed_Material()">
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
                    <th>Tên</th>
                    <th>Code</th>
                    <th>Số lượng</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody id="tbody_Material">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    <div class="Pagination" id="pagination_Material">

    </div>
</div>
<h1>Component </h1>
<div class="tableComponent">
    <form action="" method="get">
        <div class="searchBox">
            <input type="hidden" name="job" value="QLTC">
            <input type="hidden" name="action" value="phieuchi">
            <input class="searchInput" type="text" name="searchComponent" id="search_Component" placeholder="Search" oninput="Litsed_Component()">
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
                    <th>Tên</th>
                    <th>Code</th>
                    <th>Level</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody id="tbody_Component">

        </tbody>
    </table>
    <div class="Pagination" id="pagination_Component">

</div>
</div>
<script>
var pagenumber_Material = 1;
function getPageMaterial(button) {
    pagenumber_Material = $(button).text();
}
function Litsed_Material() {
    let search_input = document.getElementById('search_Material');
    let search = search_input.value;
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            thongke: 'material',
            search: search,
            page: pagenumber_Material
        },
        success: function(response) {
            let data = response.data;
            let allPage = response.allPage;
            // Clear the tbody content before adding new rows
            $("#tbody_Material").empty();
            $('#pagination_Material').empty();
            let page_quantity = Math.ceil(allPage / 10);;
            for (let i = 0; i < page_quantity; i++) {
                let str = `<button onclick="getPageMaterial(this); Litsed_Material()">${i + 1}</button>`;

                $('#pagination_Material').append(str);
            }
            for (let i = 0; i < data.length; i++) {
                let m = data[i];
                let str = `
                    <tr>
                        <td>${i+(pagenumber_Material-1)*10 + 1}</td>
                        <td>${m['name']}</td>
                        <td>${m['code']}</td>
                        <td>${m['quantity']}</td>
                        <td class="tacvu">
                            <a href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=${m['id']}">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                `;
                // Append the row to the tbody
                $("#tbody_Material").append(str);
            }

        }

    });
}
$(document).ready(function() {
    Litsed_Material(); // Call the function when the page is fully loaded
});

var pagenumber_Component = 1;
function getPageComponent(button) {
    pagenumber_Component = $(button).text();
}
function Litsed_Component() {
    let search_input = document.getElementById('search_Component');
    let search = search_input.value;
    $.ajax({
        type: "POST",
        url: "QLKHO/code/getdata_Kho.php",
        dataType: "JSON",
        data: {
            thongke: 'Component',
            search_Component: search,
            page_Component: pagenumber_Component
        },
        success: function(response) {
            let data = response.data;
            let allPage = response.allPage;
            $("#tbody_Component").empty();
            $('#pagination_Component').empty();
            let page_quantity = Math.ceil(allPage / 5);;
            console.log(page_quantity);
            for (let i = 0; i < page_quantity; i++) {
                let str = `<button onclick="getPageComponent(this); Litsed_Component()">${i + 1}</button>`;
                $('#pagination_Component').append(str);
            }
            for (let i = 0; i < data.length; i++) {
                let m = data[i];
                let str = `
                    <tr>
                        <td>${i+(pagenumber_Component-1)*5 + 1}</td>
                        <td>${m['name']}</td>
                        <td>${m['code']}</td>
                        <td>${m['level']}</td>
                        <td class="tacvu">
                            <a href="admin.php?job=QLKHO&action=thongke&actionChild=ComponentDetail&id_Component_parent=${m['id']}">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                `;
                // Append the row to the tbody
                $("#tbody_Component").append(str);
            }

        }

    });
}
$(document).ready(function() {
    Litsed_Component(); // Call the function when the page is fully loaded
});

</script>