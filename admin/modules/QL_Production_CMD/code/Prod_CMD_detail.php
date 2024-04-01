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

?>

<div id="tbl_taolenhsanxuat" class="tableComponent tabcontent">
    <?php
                    $component = new component;
                        $component->testDEQUY_2($id_Component_parent); 
                        echo "<h1>Thống kê vật tư:</h1>";
                        ?>
    <table class="data_table" id="tbl_BOM">
        <thead>
            <tr class="headerTable">
                <div class="rowTitle">
                    <th>Số thứ tự</th>
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
        <tbody id="tbody_Component">
            <?php 
                                        $i = 0;
                                        foreach ($component->thongke_Vattu_Component_in_ProdCMD($component->testDEQUY_thongke($id_Component_parent), $id_Prod_CMD) as $row) {
                                            // echo $row['id'] . $row['name'] . '-SL:' . $row['quantity'] . '</br>';
                                            $i++;
                                            $quantity_need = $row['quantity'];
                                            $quantity_exist = material::get_info_Material($row['id'])['quantity'];
                                            $diff = 1-floatval(($quantity_need / $quantity_exist));
                                            switch ($diff) {
                                                case ($diff< 0.1):
                                                    $color_background = 'red';
                                                    $color_text = 'black';
                                                    break;
                                                case ( $diff < 0.3):
                                                    $color_background = 'yellow';
                                                    $color_text = 'black';
                                                    break;
                                                default:
                                                    $color_text = 'ccc';
                                                    $color_background = 'rgba(0, 0, 0, 0.2)';
                                                    break;
                                            }
                                            ?>
            <tr style="background: <?php echo $color_background ?>  ; color: <?php echo $color_text ?>  ;"
                <?php echo $diff?>>
                <td><?php echo $i ?></td>
                <td><?php echo $row['name']  ?></td>
                <td><?php echo $row['code']  ?></td>
                <td><?php echo $quantity_need  ?></td>
                <td><?php echo $row['quantity_geted'] ?></td>
                <td><?php echo $quantity_need -$row['quantity_geted'] ?></td>
                <td><?php echo $quantity_exist  ?></td>
                <td class="tacvu">
                    <a
                        href="admin.php?job=QLKHO&action=thongke&actionChild=MaterialDetail&id_material=<?php echo $row['id']  ?>">
                        Chi tiết
                    </a>
                </td>
            </tr>
            <?php
                                        }
                                    ?>

        </tbody>
    </table>
    <button onclick="export_excel('#tbl_BOM')">Xuất
        EXCEL</button>
    <h1>Thông tin lệnh</h1>
    <div class="detail">
        <fieldset class="info" style="width: 85%; padding: 28px;">
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
            </div>

        </fieldset>
        <fieldset class="info" style="width: 25%;">
            <legend>Thông tin chi tiết</legend>
            <div class="input_class" style="    align-items: center;">
            <h2>Tiến độ tổng:</h2>
                <div class="h" style="display: flex; justify-content: space-around; align-items: center; width: 90%; flex-direction:column;">
                    <span
                        id="progress_PCMD"><?php echo isset($PCMD_BasicInfo['progress_realtime'])?$PCMD_BasicInfo['progress_realtime']:0;  ?>
                        %</span>
                </div>
            </div>
            <div class="input_class">
                <h4>Update tiến độ của bản thân:</h4>
                <input type="number" name="process_ofself">
            </div>
        </fieldset>
    </div>
</div>

<div class="chat_box_parrent">
    <button onclick="toggleVisibility_flex('#chat'); 
scrollToBottom(document.querySelector('.chatbox-container'));
    "><img width="48" height="48"
            src="https://img.icons8.com/color/48/facebook-messenger--v1.png" alt="facebook-messenger--v1" /></button>
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
<script src="../asset/js/KHO/prod_cmd.js"></script>
<script src="../asset/js/export_excel.js"></script>
<script src="../asset/js/ex/src/jquery.table2excel.js"></script>


<script>
setInterval(function() {
    chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>, <?php echo $_SESSION['userINFO']['id'] ?>);
}, 2500);
setInterval(function() {
    getALL_prod_cmd_jobchild(<?php echo $id_Prod_CMD ?>);
}, 10000);
chat_get_prod_cmd(<?php echo $id_Prod_CMD ?>, <?php echo $_SESSION['userINFO']['id'] ?>)

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
</script>