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

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $time = isset($_POST['start'])?$_POST['start']:date("Y-m-d H:i:s");
        $deadline = $_POST['deadline'];
        $note_production_cmd = $_POST['note_production_cmd'];
        $addBy = $_POST['addBy'];
        $id_component = $_POST['id_component'];
        $quantity_production = $_POST['quantity_production'];
         production_cmd::addNew($id_component, $name_production_cmd, $deadline, null, $addBy, 
         $manager, $note_production_cmd, $members_json, $priority_range,$time, $quantity_production);
         echo true;
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
    //update process job child of PROD CMD
    if(isset($_POST['update_process_jobChild_Prod_CMD'])){
        $id_prod_cmd = $_POST['data']['id_prod_cmd'];
        $job_child_prod_cmd = $_POST['data']['job_child_prod_cmd'];
        $progress = $_POST['data']['progress'];

        prod_cmd_job_child::update(array('progress' => $progress), "  `id` = $job_child_prod_cmd");

        $temp_all = prod_cmd_job_child::getAll('*', " id_production_cmd = $id_prod_cmd ");
        $sum_progress = 0;
        foreach($temp_all as $row){
            $sum_progress += ($row['percent_ofall']*$row['progress'])/100;
        }
        production_cmd::update(array('progress_realtime' => $sum_progress), "  `id` = $id_prod_cmd");
        echo $sum_progress;
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
    ////////// Vật tư BOM - Ẩn vật liệu Component thiếu:
    if(isset($_POST['get_BOM_hidden_miss_M_of_C'])){
        $id_parent  =  $_POST['id_parent'];
        echo json_encode(component::sum_vattu_CnM(component::Vattu_CnM_hidden($id_parent)), JSON_UNESCAPED_UNICODE);
    }
    if(isset($_POST['get_BOM_hidden_miss_M_of_C_in_PROD_CMD'])){
        $id_prod  =  $_POST['id_prod'];
        $id_Component_parent  =  $_POST['id_Component_parent'];
        $quantity_component = $_POST['quantity_component'];
        echo json_encode(component::thongke_Vattu_Component_in_ProdCMD2(component::Vattu_CnM_hidden($id_Component_parent), $id_prod, $quantity_component), JSON_UNESCAPED_UNICODE);
    }
    // if(isset($_POST['treeMap_DMNL'])){
    //     $id_component_parent  =  $_POST['id_component_parent'];
    //     $component = new component;
    //     $name = $component->get_info($id_component_parent)['name'];
    //     $jsonOutput = [
    //         "name" => $name,
    //         "children" => $component->convertToJSON($id_component_parent)
    //     ];    
    //     echo json_encode($jsonOutput, JSON_PRETTY_PRINT);
    // }
    if(isset($_POST['treeMap_DMNL'])){
        $id_component_parent  =  $_POST['id_component_parent'];
        $component = new component;
        $parent = $component->get_info($id_component_parent);
        $result[] = ['id' => $parent['id'],'name' => $parent['name'], 'quantity' => $parent['quantity_ofChild'], 'parentId' => null ];
        foreach ($component-> testDEQUY_3_MAIN_8t4($id_component_parent) as $row) {
            $result[] = $row;
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
?>