<?php 
    include('..\QLKHO\code\getdata_Kho.php');
    if(isset($_POST['update_chat_Prod_CMD'])){
        // var_dump($_POST['data']);
        $id_Prod_CMD = $_POST['data']['id_Prod_CMD'];
        $id_user = $_POST['data']['id_user'];
        $content = $_POST['data']['content'];
        $progress = $_POST['data']['progress'];
        chat_prod_cmd::addNewChat($id_Prod_CMD, $id_user, $content, $progress, null);
        // production_cmd::update(array('progress_realtime' => $progress), "  `id` = $id_Prod_CMD");
    }
    if(isset($_POST['getdata_chat_prod_cmd'])){
        $id_Prod_CMD = $_POST['id_Prod_CMD'];
        $progress = production_cmd::getAll('*',"  `id` = $id_Prod_CMD" )[0]['progress_realtime'];
        $data_chat = chat_prod_cmd::getAll('*', " id_production_cmd = $id_Prod_CMD  ");
        echo json_encode(array('data_chat' => $data_chat, 'progress' => $progress));
    }
?>