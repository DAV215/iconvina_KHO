<?php 
    $id_Prod_CMD = $_GET['id_cmd'];
    //PCMD production command 
    $PCMD_BasicInfo = production_cmd::get_1row('*', "id = $id_Prod_CMD");
    $id_Component_parent = $PCMD_BasicInfo['id_component'];
    $manager = $PCMD_BasicInfo['receiver'];
    if (isset($_POST['save_jobfor_member'])) {
        foreach ($_POST['name_staff'] as $index => $nameStaff) {
            if($nameStaff != '' && isset($nameStaff )){
                $nameJob = $_POST['name_job'][$index];
                $id_jobchild = $_POST['id_jobchild'][$index];
                $id_staff = user::getAll(' * ', "fullname  = '$nameStaff' ")[0]['id'];
                $start = $_POST['start'][$index];
                $finish = $_POST['finish'][$index];
                $percentOfAll = preg_replace('/%/', '', $_POST['percent_ofall'][$index]);
                if(isset($id_jobchild) && $id_jobchild != ''){
                    if(prod_cmd_job_child::check_exist($id_jobchild)){
                        $array = array(
                            'name' => $nameJob,
                            'id_staff' => $id_staff,
                            'start' => $start,
                            'finish' => $finish,
                            'percent_ofall' => $percentOfAll,
                            
                        );
                        prod_cmd_job_child::update($array, "id = $id_jobchild");
                    }else 
                    prod_cmd_job_child::addNew($id_Prod_CMD, $nameJob, $manager, $id_staff, $start, $finish, $percentOfAll, 0);
                    
                }else{
                    prod_cmd_job_child::addNew($id_Prod_CMD, $nameJob, $manager, $id_staff, $start, $finish, $percentOfAll, 0);
                }
            }
        }
        echo "<meta http-equiv='refresh' content='0'>";

    }
    if(isset($_POST['import_component_internal'])){

        if (!Super_detail::getAll('*', "`type` = 'Component' AND `id_component` = $id_Component_parent")) {
            echo "<script>alert('Cập nhập thông tin chi tiết của sản phẩm trước khi Nhập nội bộ !');</script>";
            echo "<script>window.setTimeout(function() { window.location.href = 'admin.php?job=QLKHO&action=thongke&actionChild=ComponentDetail&id_Component_parent=$id_Component_parent'; }, 1000);</script>";
            exit();
        }

        else{
            $id_user_import = $_POST['id_user_import'];
            $id_Prod_CMD = $_POST['id_Prod_CMD'];
            $id_Component_parent = $_POST['id_Component_parent'];
            $quantity = $_POST['quantity'];
            $note = $_POST['note'];

            $R = new Record_KHO_SUPERDETAIL;
            $R_quantity = $R::checkQuantity_C($quantity, $id_Component_parent);
            $super_detail = Super_detail::getAll('*', " `id_component` = $id_Component_parent ")[0];
            $R::addNew($super_detail['id'],'Nhập nội bộ', $R_quantity['old'], $R_quantity['new'], $_SESSION['userINFO']['fullname']);

            import_component_internal::addNew($id_user_import, $id_Component_parent, $note, $id_Prod_CMD, $quantity);
            info_component::update_quantity($quantity, $id_Component_parent);
            $total_quantity = import_component_internal::sum_quantity($id_Prod_CMD);
            production_cmd::update_set("completed_quantity = completed_quantity + $total_quantity", "id = $id_Prod_CMD");
            echo "<meta http-equiv='refresh' content='0'>";
        }


    }
    // echo import_component_internal::sum_quantity($id_Prod_CMD);
?>
<h1><?php echo $PCMD_BasicInfo['name']  ?></h1>
<h1>Số lượng: <?php echo $PCMD_BasicInfo['quantity']  ?></h1>
<h2>DeadLine: <?php echo $PCMD_BasicInfo['deadline']  ?> - Ưu tiên: <?php echo $PCMD_BasicInfo['priority']  ?> </h2>
<button onclick="chart_prod_cmd.expandAll().fit()">expandAll</button>
<button onclick="chart_prod_cmd.collapseAll().fit()">collapseAll</button>
<button onclick="chart_prod_cmd.exportImg()">Export Current</button>
<button onclick="chart_prod_cmd.exportImg({full:true})">Export Full</button>
<button onclick="downloadPdf(chart_prod_cmd)">Export PDF</button>
<div class="chart-container"></div>

<div id="tbl_taolenhsanxuat" class="tableComponent tabcontent" style="padding-right:10%;">

    <h1>Thống kê vật tư:</h1>
    <input type="range" name="choose_quantity_component" id="choose_quantity_component" min="01"
        max="<?php echo $PCMD_BasicInfo['quantity'] - $PCMD_BasicInfo['completed_quantity']; ?>" onchange="get_BOM_hidden_miss_M_of_C_PROD_CMD(<?php echo $id_Prod_CMD ?>, <?php echo $id_Component_parent ?>,
                 '#tbl_BOM_PROD_CMD', this.value); updateQuantityDisplay(this.value); 
                treeMap_DMNL_Prod_CMD(<?php echo $id_Component_parent  ?>, this.value); get_BOM_to_export(<?php echo $id_Prod_CMD ?>, <?php echo $id_Component_parent ?>,
                 '#tbl_BOM_PROD_CMD', this.value);
                 ">

    <h2>Số lượng dự toán: <span class="show_choose_quantity_component"></span></h2>
    <span>(Max = Số lượng yêu cầu - Số lượng đã sản xuất)</span>
    <script>
    function updateQuantityDisplay(value) {
        document.querySelectorAll('.show_choose_quantity_component').forEach(element => {
    element.innerText = value;
});

    }
    </script>

    <table class="stripe hover display order-column row-border  " style="width:100%; margin: 0;" id="tbl_BOM_PROD_CMD">
        <thead>
            <tr class="">
                <div class="">
                    <th>Số thứ tự</th>
                    <th>Loại</th>
                    <th>Tên</th>
                    <th>Code</th>
                    <th>Số lượng</th>
                    <th>Số lượng đã lấy</th>
                    <th>Số lượng chênh lệch</th>
                    <th>Số lượng trong kho</th>
                    <th>Tác vụ</th>
                </div>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <button onclick="export_excel('#tbl_BOM_PROD_CMD')">Xuất
        EXCEL</button>
    <button onclick="modal_form_export_material_from_PROD()">Tạo phiếu xuất kho</button>
    <button onclick="modal_form_import_material_from_PROD()">Tạo phiếu trả đồ</button>
    <form action="admin.php?job=QLKHO&action=thongke&actionChild=export" method="post" id="modal_form_export_material" class="modal" style="min-width: 1000px">
        <h2>Tạo lệnh xuất kho: </h2>
        <h2>Số lượng dự toán: <span class="show_choose_quantity_component"></span></h2>
        <div class="big inforForm">
                <h2>Material</h2>
                <div class="bodyofForm Material" id="table_material_CT">
                </div>
                <h2>Component</h2>
                <div class="bodyofForm Component" id="table_component_CT">
                </div>
                <div class="bodyofForm Material">
                    <div class="sub " style="display:flex;     width: 50%;">
                    <h3>Mục đích xuất kho:</h3>
                        <select name="purpose" id="" onchange=" fill_suggset_CMD()">
                            <option value="Bán hàng">Bán hàng</option>
                            <option value="Sản xuất nội bộ">Sản xuất nội bộ</option>
                        </select>
                        <h3>Tiêu đề xuất kho:</h3>
                        <input type="text" name="name_export" id="name_export" list="ALL_data_PROD_CMD" style="width:100%;">
                        <datalist id="ALL_data_PROD_CMD">

                        </datalist>
                        <h3>Ghi chú:</h3>
                        <textarea name="note" id="" cols="30" rows="10"></textarea>
                    </div>
                    <div class="sub " style="display:flex;     width: 50%;">
                        <button name="save_export">Lưa phiếu xuất</button>
                    </div>
                </div>

            </div>

        <button class="btn_common" style="width:auto; border-radius: 10px; margin-top: 10px; " type="button"
            onclick="">Tạo
            lệnh</button>
        <a href="#" rel="modal:close">Close</a>
    </form>
    <form action="" method="post" id="modal_form_import_material" class="modal" style="min-width: 1000px">
        <h2>Tạo lệnh nhập kho: </h2>
        <h2>Số lượng dự toán: <span class="show_choose_quantity_component"></span></h2>
        <div class="big inforForm">
                <h2>Material</h2>
                <div class="bodyofForm Material" id="table_material_CT">
                </div>
                <h2>Component</h2>
                <div class="bodyofForm Component" id="table_component_CT">
                </div>
                <div class="bodyofForm Material">
                    <div class="sub " style="display:flex;     width: 50%;">
                    <h3>Mục đích xuất kho:</h3>
                        <select name="purpose" id="" onchange=" fill_suggset_CMD()">
                            <option value="Bán hàng">Bán hàng</option>
                            <option value="Sản xuất nội bộ">Sản xuất nội bộ</option>
                        </select>
                        <h3>Tiêu đề xuất kho:</h3>
                        <input type="text" name="name_export" id="name_export" list="ALL_data_PROD_CMD" style="width:100%;">
                        <datalist id="ALL_data_PROD_CMD">

                        </datalist>
                        <h3>Ghi chú:</h3>
                        <textarea name="note" id="" cols="30" rows="10"></textarea>
                    </div>
                    <div class="sub " style="display:flex;     width: 50%;">
                        <button name="save_export">Lưa phiếu xuất</button>
                    </div>
                </div>

            </div>

        <button class="btn_common" style="width:auto; border-radius: 10px; margin-top: 10px; " type="button"
            onclick="">Tạo
            lệnh</button>
        <a href="#" rel="modal:close">Close</a>
    </form>
    <h1>Thông tin lệnh</h1>
    <div class="detail">
        <fieldset class="info" style="width: 100%; padding: 28px;">
            <legend>Tiến độ chi tiết:</legend>
            <table class="stripe hover display order-column row-border  " style="width:100%" id="table_jobchild">
                <thead>
                    <tr class="">

                        <th>STT</th>
                        <th>Tên việc</th>
                        <th>Nhân viên</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Thời gian còn lại</th>
                        <th>Tỷ lệ chiếm dụng</th>
                        <th>Tiến độ</th>

                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </fieldset>
    </div>
    <div class="detail">
        <fieldset class="info" style="width: 60%;">
            <legend>Thông tin cơ bản</legend>
            <div class="Info_tab">
                <div class="tab">
                    <button type="button" class="tablinks button2" onclick="change_tab(event, 'info_PROD_CMD_Detail')"
                        id="defaultOpen">Thông tin chi tiết</button>
                    <button type="button" class="tablinks button2" onclick="change_tab(event, 'job_divison')">Phân chia
                        công việc</button>
                    <button type="button" class="tablinks button2" onclick="change_tab(event, 'import_note')">Làm phiếu
                        nhập kho</button>
                </div>
                <div class="input_class tab_container" id="info_PROD_CMD_Detail">
                    <form action="" method="post" id="production_cmd_form_update">
                        <input type="hidden" name="id_component" value="<?php echo $id_Component_parent ?>">
                        <input type="hidden" name="addBy" value="<?php echo $_SESSION['userINFO']['fullname'] ?>">
                        <fieldset>
                            <legend>Tên lệnh sản xuất:</legend>
                            <input required type="text" name="name_production_cmd" id=""
                                value="<?php echo $PCMD_BasicInfo['name']  ?>" disabled>
                        </fieldset>
                        <fieldset>
                            <legend>Mức độ ưu tiên: <?php echo $PCMD_BasicInfo['priority']?></legend>
                        </fieldset>
                        <div style="width:100%; display: flex; flex-direction: row;">
                            <fieldset style="width:50%">
                                <legend>Số lượng yêu cầu:</legend>
                                <h1> <?php echo $PCMD_BasicInfo['quantity']?></h1>
                            </fieldset>
                            <fieldset style="width:50%">
                                <legend>Đã xong:</legend>
                                <h1> <?php echo $PCMD_BasicInfo['completed_quantity']?></h1>
                            </fieldset>
                        </div>
                        <fieldset>
                            <legend>Người phụ trách:</legend>
                            <input required type="text" name="manager" list="staff"
                                value="<?php echo $PCMD_BasicInfo['receiver']  ?>" disabled>
                            <datalist id="staff">

                            </datalist>
                        </fieldset>

                        <fieldset>
                            <legend>DeadLine:</legend>
                            <input required type="datetime-local" name="deadline"
                                value="<?php echo $PCMD_BasicInfo['deadline']  ?>" disabled>
                        </fieldset>
                        <fieldset>
                            <legend>Ghi chú:</legend>
                            <textarea name="note_production_cmd" id="" cols="30" rows="10"
                                style="width:99%; background:#ccc; color:black;" disabled>note</textarea>
                        </fieldset>
                    </form>

                </div>
                <div class="input_class tab_container" id="job_divison">
                    <fieldset>
                        <legend>Người cùng tham gia:</legend>
                        <input type="search" name="" id="searchStaff" list="staff">
                        <button type="button" class="btn_common" onclick="addMember()"><i
                                class="fa-solid fa-plus"></i></button>
                        <input required type="text" name="member[]" list="staff" id="member"
                            value="<?php echo implode(", ", json_decode($PCMD_BasicInfo['member'], true)); ?>">
                        <button onclick="update_member_prod_cmd('#member', '<?php echo $id_Prod_CMD ?>')">Save</button>
                    </fieldset>
                    Phân chia công việc
                    <div class="big inforForm" style="justify-content:center; margin: 0;">
                        <form action="" method="post">
                            <div class="bodyofForm Material" id="table_division_job">

                            </div>
                            <datalist id="ALL_member_in_prod_cmd">
                            </datalist>
                            <button style="min-width: 200px; width: 50%;" name="save_jobfor_member">Lưu</button>

                        </form>
                    </div>

                </div>
                <div class="input_class tab_container" id="import_note">
                    <form action="" method="POST">
                        <input type="hidden" name="id_Prod_CMD" value="<?php echo $id_Prod_CMD ?>">
                        <input type="hidden" name="id_Component_parent" value="<?php echo $id_Component_parent ?>">
                        <input type="hidden" name="id_user_import" value="<?php echo $_SESSION['userINFO']['id'] ?>">
                        <fieldset>
                            <legend>Tên sản phẩm:</legend>
                            <input value="<?php echo component::get_info($id_Component_parent)['name']?>" disabled>

                        </fieldset>
                        <fieldset>
                            <legend>Số lượng:</legend>
                            <input name="quantity" type="number" min="0"
                                max="<?php echo $PCMD_BasicInfo['quantity']-$PCMD_BasicInfo['completed_quantity']; ?>"
                                required>


                        </fieldset>
                        <fieldset>
                            <legend>Thời gian: </legend>
                            <input name="time_import" type="datetime-local" required>

                        </fieldset>
                        <fieldset>
                            <legend>Ghi chú:</legend>
                            <textarea name="note" id="" cols="30" rows="10" required></textarea>

                        </fieldset>
                        <div class="big inforForm" style="justify-content:center; margin: 0;">
                            <button style="min-width: 200px; width: 50%;" name="import_component_internal">Lưu</button>

                        </div>
                    </form>


                </div>
            </div>

        </fieldset>
        <fieldset class="info" style="width: 40%;">
            <legend>Thông tin chi tiết</legend>
            <div style="display: flex; flex-direction: row;">
                <div class="input_class" style="    align-items: center;">
                    <h2>Tiến độ tổng:</h2>
                    <div class="h"
                        style="display: flex; justify-content: space-around; align-items: center; width: 90%; flex-direction:column;">
                        <span style="margin: 0 20px;"
                            id="progress_PCMD"><?php echo isset($PCMD_BasicInfo['progress_realtime'])?$PCMD_BasicInfo['progress_realtime']:0;  ?>
                            %</span>
                    </div>
                </div>
                <div class="input_class" style="    align-items: center;">
                    <h2>Sản lượng:</h2>
                    <div class="h"
                        style="display: flex; justify-content: space-around; align-items: center; width: 90%; flex-direction:column;">
                        <span style="margin: 0 20px;"
                            id="progress_PCMD"><?php echo $PCMD_BasicInfo['completed_quantity'].'/'.$PCMD_BasicInfo['quantity'] ;  ?></span>
                    </div>
                </div>
            </div>

            <div class="input_class big inforForm" style="align-items: flex-start;">
                <input type="hidden" name="id_prod_cmd" value="<?php echo $id_Prod_CMD ?>">
                <input type="hidden" name="id_user" value="<?php echo $_SESSION['userINFO']['id'] ?>">
                <h4>Update tiến độ của bản thân:</h4>
                <select name="job_child_prod_cmd" id="job_child_prod_cmd" required>
                    <option value="1">dfd</option>
                </select>
                <input type="number" name="process_ofself">
                <button onclick="update_process(this, '.input_class' );">Update tiến độ</button>
            </div>
        </fieldset>
    </div>
</div>

<div class="chat_box_parrent">
    <button onclick="toggleVisibility_flex('#chat'); 
scrollToBottom(document.querySelector('.chatbox-container'));
    "><img width="48" height="48" src="https://img.icons8.com/color/48/facebook-messenger--v1.png"
            alt="facebook-messenger--v1" /></button>
    <div class="chatbox" style="display:none;" id="chat">
        <div class="chatbox-container">
            <div class="chat-element ofuser">
                <div class="chat-info">
                    <span>Name - 12:33:00 23-09-2024</span>
                </div>
                <div class="chat-comment">
                    <div class="chat-content">
                        Tỷ số dễ xảy ra nhất cho chiến thắng của Việt Nam là 1-0, xác suất 11,34%", trang
                        thể thao Anh nhận định. "Sau đó cũng là các tỷ số nghiêng về chủ nhà như 2-1
                        (9,49%), hay 2-0 (9,06%).
                    </div>
                    <div class="progress">
                        Tiến độ: 90%
                    </div>
                </div>
            </div>
            <div class="chat-element">
                <div class="chat-info">
                    <span>Name - 12:33:00 23-09-2024</span>
                </div>
                <div class="chat-comment">
                    <div class="chat-content">
                        Tỷ số dễ xảy ra nhất cho chiến thắng của Việt Nam là 1-0, xác suất 11,34%", trang
                        thể thao Anh nhận định. "Sau đó cũng là các tỷ số nghiêng về chủ nhà như 2-1
                        (9,49%), hay 2-0 (9,06%).
                    </div>
                    <div class="progress">
                        Tiến độ: 90%
                    </div>
                </div>
            </div>
            <div class="chat-element">
                <div class="chat-info">
                    <span>Name - 12:33:00 23-09-2024</span>
                </div>
                <div class="chat-comment">
                    <div class="chat-content">
                        Tỷ số dễ xảy ra nhất cho chiến thắng của Việt Nam là 1-0, xác suất 11,34%", trang
                        thể thao Anh nhận định. "Sau đó cũng là các tỷ số nghiêng về chủ nhà như 2-1
                        (9,49%), hay 2-0 (9,06%).
                    </div>
                    <div class="progress">
                        Tiến độ: 90%
                    </div>
                </div>
            </div>
        </div>
        <div class="chatbox-actionbar">

            <input type="hidden" name="id_Prod_CMD" value="<?php echo $id_Prod_CMD ?>">
            <input type="hidden" name="id_user" value="<?php echo $_SESSION['userINFO']['id'] ?>">
            <input name="content" type="text" placeholder="Nội dung" style="width: 80%">
            <input name="progress" type="hidden" placeholder="Tiến độ" style="width: 20%">
            <button type="submit"
                onclick="chat_send(this,'.chatbox-actionbar' ); chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>, <?php echo $_SESSION['userINFO']['id'] ?>)"><i
                    class="fa-solid fa-paper-plane"></i></button>
            <button
                onclick=" chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>,<?php echo $_SESSION['userINFO']['id'] ?> )">Refesh</button>
        </div>
    </div>
</div>
<form action="" method="post" id="modal_form_add_JOB" class="modal">
    <h2>Tạo lệnh sản xuất: <h2 id="name_ofJob_add"></h2>
    </h2>
    <fieldset>
        <legend>Tên công việc</legend>
        <input type="text" name="name_production_cmd"
            style="width:100%;border: none;border-bottom: 1px solid #ff7e7e;outline: none;">
        <input type="hidden" name="id_component">
        <input type="hidden" name="addBy" value="<?php echo $_SESSION['userINFO']['fullname'] ?>">

    </fieldset>
    <fieldset style="display:flex;">
        <legend>Thời gian</legend>
        <input type="datetime-local" name="start">
        <legend> Kết thúc</legend>
        <input type="datetime-local" name="deadline">
    </fieldset>
    <div style="display: flex; flex-direction: row">
        <fieldset style="width: 50%">
            <legend>Số lượng</legend>
            <input type="number" name="quantity_production"
                style="width:100%;border: none;border-bottom: 1px solid #ff7e7e;outline: none;">
        </fieldset>
        <fieldset style="width: 50%">
            <legend>Quản lý</legend>
            <input type="text" name="manager" list="manager"
                style="width:100%;border: none;border-bottom: 1px solid #ff7e7e;outline: none;">
            <datalist id="manager">

            </datalist>
        </fieldset>
    </div>
    <fieldset>
        <legend>Mức độ ưu tiên:</legend>
        <input required type="range" min="0" max="5" name="priority_range" id="">
    </fieldset>
    <button class="btn_common" style="width:auto; border-radius: 10px; margin-top: 10px; " type="button"
        onclick="create_cmd()">Tạo
        lệnh</button>
    <a href="#" rel="modal:close">Close</a>
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />
<script src="../asset/js/KHO/prod_cmd.js"></script>
<script src="../asset/js/export_excel.js"></script>
<script src="../asset/js/ex/src/jquery.table2excel.js"></script>


<script>
// setInterval(function() {
//     chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>, <?php echo $_SESSION['userINFO']['id'] ?>);
// }, 2500);
// setInterval(function() {
//     getALL_prod_cmd_jobchild(<?php echo $id_Prod_CMD ?>);
// }, 10000);
chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>, <?php echo $_SESSION['userINFO']['id'] ?>)
getALL_prod_cmd_jobchild(<?php echo $id_Prod_CMD ?>);
getALL_prod_cmd_datalist('#ALL_data_PROD_CMD');

function scrollToBottom(div) {
    div.scrollTop = div.scrollHeight;
}
document.getElementById("defaultOpen").click();

function change_tab(event, nameTab) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab_container");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = 'none';
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("active", "");
    }
    document.getElementById(nameTab).style.display = "block";
    event.currentTarget.className += " active";
}
fillmember_list_divJOB('#member', '#ALL_member_in_prod_cmd');
getALL_prod_cmd_jobchild(<?php echo $id_Prod_CMD ?>)
getALL_division_job(<?php echo $id_Prod_CMD ?>)
get_BOM_hidden_miss_M_of_C_PROD_CMD(<?php echo $id_Prod_CMD ?>, <?php echo $id_Component_parent ?>,
    '#tbl_BOM_PROD_CMD', 1);
get_BOM_to_export(<?php echo $id_Prod_CMD ?>, <?php echo $id_Component_parent ?>,
'#tbl_BOM_PROD_CMD', 1);
treeMap_DMNL_Prod_CMD(<?php echo $id_Component_parent  ?>, 1)
</script>
<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-org-chart@2"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.0.0/build/d3-flextree.js"></script>
<script src="https://unpkg.com/html2canvas@1.1.4/dist/html2canvas.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
<script>
function downloadPdf(chart) {
    chart.exportImg({
        save: false,
        full: true,
        onLoad: (base64) => {
            var pdf = new jspdf.jsPDF();
            var img = new Image();
            img.src = base64;
            img.onload = function() {
                pdf.addImage(
                    img,
                    'JPEG',
                    5,
                    5,
                    595 / 3,
                    ((img.height / img.width) * 595) / 3
                );
                pdf.save('chart.pdf');
            };
        },
    });
}

function create_cmd() {
    let form = $('#modal_form_add_JOB');
    $.ajax({
        url: "API/API_KHO.php",
        data: form.serialize(), // Send form data directly
        dataType: 'json', // Expect JSON response from the PHP script
        type: 'post',
        success: function(response) {
            if (response == 1) {
                alert('Đã thêm lệnh thành công !');
            }
        },
        error: function(xhr, status, error) {
            // Handle error
            console.error(xhr, status, error);
        }
    });
}
</script>