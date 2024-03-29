<?php 
    include('..\QLNS\getdataUser.php');
    include('..\QLKHO\code\getdata_Kho.php');

    if(isset($_POST['name_production_cmd'])){
        $name_production_cmd = $_POST['name_production_cmd'];
        $priority_range = $_POST['priority_range'];
        $manager = $_POST['manager'];
        $members = isset($_POST['member']) ? $_POST['member'] : array(); // Get the member array
        
        // If members array is not empty, implode and encode it
        $members_json = !empty($members) ? json_encode($members, JSON_UNESCAPED_UNICODE) : '[]';
        
        $deadline = $_POST['deadline'];
        $note_production_cmd = $_POST['note_production_cmd'];
        $addBy = $_POST['addBy'];
        $id_component = $_POST['id_component'];
         production_cmd::addNew($id_component, $name_production_cmd, $deadline, null, $addBy, $manager, $note_production_cmd, $members_json, $priority_range);
         echo json_encode('Đã tạo lệnh thành công !', JSON_UNESCAPED_UNICODE);
    }
    // if(isset($_POST['getALL_prod_cmd'])){
    //     $getPage = isset($_POST['pagenumber_Prods_cmd'])?$_POST['pagenumber_Prods_cmd']:1;
    //     $start_el = ($getPage-1)*5;
    //     if(isset($_POST['search_prod_cmd']) && $_POST['search_prod_cmd'] != null){
    //         $search = $_POST['search_prod_cmd'];
    //         $all = production_cmd::getAll('*', "   ((name LIKE '%" . $search . "%'  ) OR (receiver LIKE '%" . $search . "%'  ) OR (deadline LIKE '%" . $search . "%'  ))");
    //         $all_eachPage = production_cmd::getAll('*', "   ((name LIKE '%" . $search . "%'  ) OR (receiver LIKE '%" . $search . "%'  ) OR (deadline LIKE '%" . $search . "%'  )) LIMIT $start_el, 5");
    //         $quantity_el = count($all);
    //         echo json_encode(array('data' => $all_eachPage, 'quantity_el' => $quantity_el));
    //     } else {
    //         $all = production_cmd::getAll('*', " 1 ");
    //         $all_eachPage = production_cmd::getAll('*', " 1  LIMIT $start_el, 5");
    //         $quantity_el = count($all);
    //         echo json_encode(array('data' => $all_eachPage, 'quantity_el' => $quantity_el));
    //     }
    // }
    if(isset($_POST['getALL_prod_cmd'])){
        $all = production_cmd::getAll('*', " 1 ");
        $all_eachPage = production_cmd::getAll('*', " 1  ");
        $quantity_el = count($all);
        echo json_encode(array('data' => $all_eachPage, 'quantity_el' => $quantity_el));
    }
    ////UPDATE MEMBER _PRO
    if(isset($_POST['update_member_prod_cmd'])){
        $members = [];
        $members[] = isset($_POST['member']) ? $_POST['member'] : array(); 
        $members_json = !empty($members) ? json_encode($members, JSON_UNESCAPED_UNICODE) : '[]';
        $id_prod_cmd = isset($_POST['id_prod_cmd']) ? $_POST['id_prod_cmd'] : ''; // Get the member array
        production_cmd::update(array('member' => $members_json), " id = $id_prod_cmd");
    }
    //getALL_prod_cmd_jobchild
    if(isset($_POST['getALL_prod_cmd_jobchild'])){
        $id = $_POST['id_prod_cmd'];
        $temp_all = prod_cmd_job_child::getAll('*', " id_production_cmd = $id ");
        $all = [];
        foreach($temp_all as $row){
            $id_staff = $row['id_staff'];
            $name_staff = user::getAll('*', " `id`  = $id_staff")[0]['fullname'];

            $all[] = array(
                'id_jobchild' => $row['id'],
                'id_production_cmd' => $row['id_production_cmd'],
                'name' => $row['name'],
                'name_staff' => $name_staff,
                'id_manager' => $row['id_manager'],
                'id_staff' => $row['id_staff'],
                'start' => $row['start'],
                'finish' => $row['finish'],
                'percent_ofall' => $row['percent_ofall'],
                'progress' => $row['progress'],
            );
        }
        echo json_encode($all);                                             
    }
    //del job child
    if(isset($_POST['del_jobchild'])){
        $id  = $_POST['id_jobchild'];
        prod_cmd_job_child::delete("id = $id");
    }
    //API IMPORT
    if(isset($_POST['getAll_import_note'])){
        $all = import_material::getAll('*', " 1 ");
        foreach ($all as $row) {
            $total[] = import_material::total($row['id']);
        }
        $result = array('data' => $all, 'total' => $total);
        echo json_encode($result);
    }
    if(isset($_POST['get_import_note_detail'])){
        $id = $_POST['id_imp_deatail'];
        $all = import_material_detail::getAll('*', " `id_import` = $id ");
        foreach ($all as $row) {
            $data[] = array(
                'id' => $row['id_material'],
                'name' =>material::get_info_Material($row['id_material'])['name'],
                'quantity' => $row['quantity'],
                'price' => $row['import_price']
            );
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    //API EXPORT
    if(isset($_POST['getAll_export_note'])){
        $all = export_material::getAll('*', " 1 ");
        foreach ($all as $row) {
            $total[] = export_material::total($row['id']);
        }
        $result = array('data' => $all, 'total' => $total);
        echo json_encode($result);
    }
    if(isset($_POST['get_export_note_detail'])){
        $id = $_POST['id_exp_detail'];
        $all = export_material_detail::getAll('*', " `id_export` = $id ");
        $total = 0;
        foreach ($all as $row) {
            if($row['type_prod'] == 'Material'){
                $into_money = $row['quantity']*$row['price'];
                $all_prods[] = array(
                    'type' => 'Material',
                    'id' => $row['id_material'],
                    'name' =>material::get_info_Material($row['id_material'])['name'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'into_money' =>  number_format($into_money)
                );
            }
            elseif($row['type_prod'] == 'Component'){
                $into_money = $row['quantity']*$row['price'];
                $all_prods[] = array(
                    'type' => 'Component',
                    'id' => $row['id_component'],
                    'name' =>component::get_info($row['id_component'])['name'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'into_money' =>  number_format($into_money)
                );
            }
            $total += $into_money;
        }
        $result = array('data' => $all_prods, 'total' => number_format($total));
        echo json_encode($result);
    }
?>