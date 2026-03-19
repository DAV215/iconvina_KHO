<?php 
    $order = new Order;
    if(isset($_POST['save_new_order'])){
        $id_client = $_POST['id_client'];
        $name = $_POST['order_name'];
        $code = 'DH_'.$order->getAll(" 1 ORDER BY id DESC LIMIT 1 ")[0]['id']+1;
        $start_day = $_POST['start_day'];
        $finish_day = $_POST['finish_day'];
        $priority = $_POST['priority'];
        $note = $_POST['note'];
        $order->addNew(array('id_client' => $id_client, 'code' =>$code , 'name' => $name, 'start_day' => $start_day,'finish_day' => $finish_day, 'priority' => $priority, 'note' => $note));
    }
?>
<h1>Tạo đơn hàng:</h1>
<div class="detail" style="width:90%;">
    <fieldset class="info input_class tab_container" style="width:100%; display:flex; ">
        <legend>Thông tin cơ bản bên mua:</legend>
        <form action="" method="POST">
            <fieldset>
                <legend>Tên đơn hàng:</legend>
                <input type="text" name="order_name" required>
            </fieldset>
            <fieldset>
                <legend>Cá nhân đại diện:</legend>
                <input type="text" name="id_client" list="represent_user" required>
                <datalist name="" id="represent_user"></datalist>
            </fieldset>
            <div style="display: flex; width: 70%;">
                <fieldset style=" width: 30%;">
                    <legend>Ngày bắt đầu:</legend>
                    <input type="datetime-local" name="start_day" required>
                </fieldset>
                <fieldset style=" width: 30%;">
                    <legend>Ngày kết thúc:</legend>
                    <input type="datetime-local" name="finish_day" required>
                </fieldset>
                <fieldset style=" width: 30%;">
                    <legend>Mức ưu tiên:</legend>
                    <input type="range" name="priority" min="0" max="5" required>
                </fieldset>
            </div>
            <fieldset>
                <legend>Ghi chú:</legend>
                <textarea name="note" id="" cols="20" rows="5" style="max-width:95%;"></textarea>
            </fieldset>
            <div class="inforForm">
                <button name="save_new_order">Bổ sung thông tin chi tiết</button>
            </div>
        </form>

    </fieldset>
</div>
<div class="detail" style="width:90%;">
    <fieldset class="info input_class tab_container" style="width:100%; display:flex; ">
        <legend>Thông tin chi tiết:</legend>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="userFormm">
                <div class="mainForm">
                    <div class="big inforForm">
                        <h2>Material</h2>
                        <table style="display:inline-table; width:100%;" class="tbl_common_ver1">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="m_tbl">

                                <tr class="m_order">
                                    <td>1</td>
                                    <td><input type="text" onkeydown="addROW_(event, 'm_order', 'm_tbl')"></td>
                                    <td><input type="number" onkeydown="addROW_(event, 'm_order', 'm_tbl')"></td>
                                    <td><input type="datetime-local" value="<?php echo date('Y-m-d\TH:i'); ?>"></td>
                                    <td><input type="datetime-local"></td>
                                    <td onclick="delROW_(this,'.m_order')">X</td>
                                </tr>
                            </tbody>
                        </table>

                        <h2>Component</h2>
                        <table style="display:inline-table; width:100%;" class="tbl_common_ver1">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="c_tbl">
                                <tr class="c_order">
                                    <td>1</td>
                                    <td><input type="text" onkeydown="addROW_(event, 'c_order', 'c_tbl')"></td>
                                    <td><input type="number" onkeydown="addROW_(event, 'c_order', 'c_tbl')"></td>
                                    <td><input type="datetime-local" value="<?php echo date('Y-m-d\TH:i'); ?>"></td>
                                    <td><input type="datetime-local"></td>
                                    <td onclick="delROW_(this,'.c_order')">X</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
        </form>
    </fieldset>
</div>