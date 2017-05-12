<?php
class PanelAction extends Action {
	Public function _initialize() {
        if(!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            $this->redirect('Index/login');
        }
        if(time() - (int)lookupLastLogin($_SESSION['id']) >= 604800){
            session_destroy();
            $this->redirect('Index/index');
        }
    }

    public function index(){
		import("@.Action.ControlPanel");
        $params = array();

        $params = array_merge($params, Array('current' => $_SESSION['id']));

        $panel = new ControlPanel($params);
        $this->assign('panelcontent',$panel->display());
        $this->display();
    }

    public function approveuser(){
        import("@.Action.ControlPanel");
        $params = array();
        if(isset($_GET['city']))
            $params = array_merge($params, Array('city' => I('city')));
        if(isset($_GET['level']))
            $params = array_merge($params,Array('level' => I('level')));
        $params = array_merge($params, Array('action' => 'approveuser'));
        $params = array_merge($params, Array('current' => $_SESSION['id']));
        if((int)lookupAdmin($_SESSION['id']) >= 1){
            $panel = new ControlPanel($params);
            $this->assign('panelcontent',$panel->display());
            $this->display();
        }else{
            U('Panel/index', '', '', 1);
        }
    }

    public function userlist(){
        import("@.Action.ControlPanel");
        $params = array();
        if(isset($_GET['city']))
            $params = array_merge($params, Array('city' => I('city')));
        if(isset($_GET['level']))
            $params = array_merge($params,Array('level' => I('level')));
        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));
        $params = array_merge($params, Array('action' => 'userlist'));
        $params = array_merge($params, Array('current' => $_SESSION['id']));

        $panel = new ControlPanel($params);
        $this->assign('panelcontent',$panel->display());
        $this->display();

    }

    public function userdetail() {
        import("@.Action.UserDetail");
        if(isset($_GET['id']))
            $params = Array("id" => I('id'));
        else
            $this->redirect('index');

        $params = array_merge($params, Array('isLogin' => '1', 'current' => $_SESSION['id']));

        $details = new UserDetail($params);
        $this->assign('details',$details->display());
        $this->display();
    }

    public function approveusersubmit(){
        if(!isset($_GET['id']) || (int)lookupAdmin($_SESSION['id']) < 1)
            U('Panel/index', '', '', 1);
        else{
            $table = M('user');
            if($user = $table->where(Array('id' => I('id')))->find()){
                $user['reserved_1'] = 'normal';
                $table->where(Array('id' => $user['id']))->save($user);
                logEvent($_SESSION['id'], 'approveuser', I('id').','.lookupJudgeById(I('id')));
                U('Panel/approveuser', '', '', 1);
            }
            else
                U('Panel/index', '', '', 1);
        }
            

    }

    public function filter(){
        $filtercity = I('filtercity');
        $filterlevel = I('filterlevel');
        $origcity = I('origcity');
        $origlevel = I('origlevel');
        $action = I('action');

        $param = Array();
        if($filtercity!='default'){
            $param['city'] = $filtercity;
        } else {
            $param['city'] = $origcity;
        }
        if($filterlevel!='default'){
            $param['level'] = $filterlevel;
        } else {
            $param['level'] = $origlevel;
        }

        U($action,$param,'',1);
    }

    public function action() {
        $action = I('action');
        $id = I('id');

        if($action == 'user_modify'){
            if((int)lookupAdmin($_SESSION['id']) >= 2){
                U('/Panel/modify', Array('id' => $id), '', 1);
            }
        }
        elseif($action == 'user_resetpwd'){
            if((int)lookupAdmin($_SESSION['id']) >= 2){
                $update = M('user');
                $data = $update->where(Array('id' => $id))->find();
                $newpwd['password'] = MD5((int)$data['dci']);
                $update->where(Array('id' => $id))->save($newpwd);
                logEvent($_SESSION['id'], 'resetpwd', $id.','.lookupJudgeById($id));
            }
        
        }elseif($action == 'user_activate'){
            if((int)lookupAdmin($_SESSION['id']) >= 2){
                $update = M('user');
                $status['reserved_1'] = 'normal';
                $update->where(Array('id' => $id))->save($status);
                logEvent($_SESSION['id'], 'approveuser', $id.','.lookupJudgeById($id));
            }
            
        }elseif($action == 'user_deactivate'){
            if((int)lookupAdmin($_SESSION['id']) >= 2){
                $update = M('user');
                $status['reserved_1'] = 'pending';
                $update->where(Array('id' => $id))->save($status);
                logEvent($_SESSION['id'], 'deactivate_user', $id.','.lookupJudgeById($id));
            }
            
        }

        $this->redirect('/Panel/userdetail', Array('id' => $id));
    }

    public function modify(){
        if((int)lookupAdmin($_SESSION['id']) >= 2 && lookupJudge(I('id'))){
            $id = I('id');
            $user_object = M('user');

            $user = $user_object->where(Array('id' => $id))->find();
            $this->assign('user',$user);
            $this->assign('selectlevel',Array($user['level'] => 'selected'));

            $this->display();
        }
        else{
            U('/Panel/index', '', '', 1);
        }
    }

    public function modifyuser(){
        if(empty($_POST) || (int)lookupAdmin($_SESSION['id']) < 2)
            U('/Panel/index', '', '', 1);
        if(!lookupJudge(I('userid')))
            U('/Panel/userlist', '', '', 1);

        if(!validateLength(I('username'),4,16))
            $message = C('STR_EDIT_ERROR_USERNAME_LENGTH');
        elseif (validateExclusiveExist(I('username'),'username',I('userid')))
            $message = C('STR_EDIT_ERROR_USERNAME_EXIST');
        elseif (!validateLength(I('usercity'),2,20))
            $message = C('STR_EDIT_ERROR_CITY');
        elseif (!validateLength(I('fullname'),2,20))
            $message = C('STR_EDIT_ERROR_NAME_LENGTH');
        elseif (validateExclusiveExist(I('fullname'),'fullname',I('userid')))
            $message = C('STR_EDIT_ERROR_NAME_EXIST');
        elseif (!validateDCI(I('userdci')))
            $message = C('STR_EDIT_ERROR_DCI');
        elseif (validateExclusiveExist(I('userdci'),'dci',I('userid')))
            $message = C('STR_EDIT_ERROR_DCI_EXIST');
        else
        {
            $table = M('user');

            $data['username'] = I('username');
            $data['city'] = I('usercity');
            $data['level'] = I('userlevel');
            $data['fullname'] = I('fullname');
            $data['dci'] = I('userdci');

            if($table->where(Array('id' => I('userid')))->save($data)){
                $message = C('STR_USERMODIFY_SUCCESS');
                logEvent($_SESSION['id'], 'modifyuser', I('userid').','.lookupJudgeById(I('userid')));
            }   
            else
                $message = C('STR_DB_FAILURE');
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function changepwd(){
        $this->display();
    }

    public function changepwdsubmit(){
        if(empty($_POST))
            U('changepwd', '', '', 1);

        if(!validateLength(I('newpassword'),6,20))
            $message = C('STR_CP_ERROR_PASSWORD_LENGTH');
        elseif (!validateMatch(I('newpassword'),I('confirmpassword')))
            $message = C('STR_CP_ERROR_PASSWORD_MATCH');
        elseif (!validateDCI(I('dci')))
            $message = C('STR_CP_ERROR_DCI');
        else{
            $table = M('user');
            $condition = Array('id' => $_SESSION['id']);
            $user = $table->where($condition)->find();

            $newpassword = I('newpassword');
            $oldpassword = I('oldpassword');
            $dci = I('dci');

            if(!validateMatch(MD5($oldpassword),$user['password']))
                $message = C('STR_CP_ERROR_PASSWORD_FAIL');
            elseif(!validateMatch($dci, $user['dci']))
                $message = C('STR_CP_ERROR_DCI');
            else{
                if($table->where($condition)->save(Array('password' => MD5($newpassword)))){
                    $message = C('STR_CP_SUCCESS');
                    logEvent($_SESSION['id'], 'changepwd', $_SESSION['id'].','.lookupJudgeById($_SESSION['id']));
                }
                else
                    $message = C('STR_DB_FAILURE');
            }
        }

        $this->assign('message',$message);
        $this->display();

    }

    public function changecity(){
        $this->assign('origcity',lookupJudgeCity($_SESSION['id']));
        $this->display();
    }

    public function changecitysubmit(){
        if(empty($_POST))
            U('changecity', '', '', 1);

        if (!validateLength(I('newcity'),2,20))
            $message = C('STR_CP_ERROR_CITY');
        elseif (!validateDCI(I('dci')))
            $message = C('STR_CP_ERROR_DCI');
        else{
            $table = M('user');
            $condition = Array('id' => $_SESSION['id']);
            $user = $table->where($condition)->find();

            $password = I('password');
            $dci = I('dci');
            $city = I('newcity');

            if(!validateMatch(MD5($password),$user['password']))
                $message = C('STR_CP_ERROR_PASSWORD_FAIL');
            elseif(!validateMatch($dci, $user['dci']))
                $message = C('STR_CP_ERROR_DCI');
            else{
                if($table->where($condition)->save(Array('city' => $city))){
                    $message = C('STR_CP_CITY_SUCCESS');
                    logEvent($_SESSION['id'], 'changecity', $_SESSION['id'].','.lookupJudgeById($_SESSION['id']));
                }
                else
                    $message = C('STR_DB_FAILURE');
            }
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function announcement(){
        if((int)lookupAdmin($_SESSION['id']) < 2)
            U('index', '', '', 1);

        $this->assign('origannouncement',lookupAnnouncement());
        $this->display();
    }

    public function showchecklist(){
        if((int)lookupLevel($_SESSION['id']) != 1)
            U('index', '', '', 1);

        import("@.Action.CheckList");
        $params = array();

        $params = array_merge($params, Array('id' => $_SESSION['id']));

        $checklist = new CheckList($params);
        $this->assign('checklist',$checklist->display());
        $this->display();
    }

    public function adminchecklist(){
        if((int)lookupAdmin($_SESSION['id']) < 2)
            U('index', '', '', 1);

        import("@.Action.CheckList");

        if(is_numeric(I('id')) && lookupLevel(I('id')) == 1){
            $params = array();
            $params = array_merge($params, Array('id' => I('id')));

            $checklist = new CheckList($params);
            $this->assign('checklist',$checklist->display());
        }
        $this->display();
    }

    public function changeannouncementsubmit(){
        if(empty($_POST) || (int)lookupAdmin($_SESSION['id']) < 2)
            U('index', '', '', 1);

        $table = M('user');
        $condition = Array('id' => $_SESSION['id']);
        $user = $table->where($condition)->find();

        $password = I('password');

        if(!validateMatch(MD5($password),$user['password']))
                $message = C('STR_CP_ERROR_PASSWORD_FAIL');
        else{
            $data = M('announcement');
            $arr['textbody'] = I('content');
            $arr['last_modify_date'] = todayDate();
            $arr['last_modify_judge'] = $_SESSION['id'];
            $condition = Array('id' => '1');

            if($data->where($condition)->save($arr)){
                $message = C('STR_CHG_ANNOUNCEMENT_SUCCESS');
                logEvent($_SESSION['id'], 'announcement', $_SESSION['id'].','.lookupJudgeById($_SESSION['id']));
            }
            else
                $message = C('STR_DB_FAILURE');
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function additem(){
        if((int)lookupLevel($_SESSION['id']) != 1 || I('id') != $_SESSION['id'])
            U('index', '', '', 1);

        $field['judge_id'] = I('id');
        $field['item_date'] = C('STR_CHECKLIST_ITEM_DATE');

        if(I('type') == 'mentor'){
            $field['item_type'] = 'mentor';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_MENTOR');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_MENTOR');
            $field['item_body'] = C('STR_CHECKLIST_BODY_MENTOR');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_MENTOR');
            $this->assign('field',$field);
            $this->display();
        }
        elseif(I('type') == 'event'){
            $field['item_type'] = 'event';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_EVENT');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_EVENT');
            $field['item_body'] = C('STR_CHECKLIST_BODY_EVENT');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_EVENT');
            $this->assign('field',$field);
            $this->display();
        }
        elseif(I('type') == 'teamwork'){
            $field['item_type'] = 'teamwork';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_TEAMWORK');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_TEAMWORK');
            $field['item_body'] = C('STR_CHECKLIST_BODY_TEAMWORK');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_TEAMWORK');
            $this->assign('field',$field);
            $this->display();
        }
        elseif(I('type') == 'review'){
            $field['item_type'] = 'review';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_REVIEW');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_REVIEW');
            $field['item_body'] = C('STR_CHECKLIST_BODY_REVIEW');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_REVIEW');
            $this->assign('field',$field);
            $this->display();
        }
        elseif(I('type') == 'report'){
            $field['item_type'] = 'report';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_REPORT');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_REPORT');
            $field['item_body'] = C('STR_CHECKLIST_BODY_REPORT');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_REPORT');
            $this->assign('field',$field);
            $this->display();
        }
        elseif(I('type') == 'l2p'){
            $field['item_type'] = 'l2p';
            $field['subtitle'] = C('STR_CHECKLIST_SUBTITLE_L2P');
            $field['item_subject'] = C('STR_CHECKLIST_SUBJECT_L2P');
            $field['item_body'] = C('STR_CHECKLIST_BODY_L2P');
            $field['item_notice'] = C('STR_CHECKLIST_NOTICE_L2P');
            $this->assign('field',$field);
            $this->display();
        }
        else{
            U('index', '', '', 1);
        }

    }

    public function additemdone(){
        if(empty($_POST))
            U('index', '', '', 1);

        if (!validateDate(I('date')))
            $this->error(C('STR_CHECKLIST_ERROR_DATE'));
        elseif(validatelength2(I('subject')))
            $this->error(C('STR_CHECKLIST_ERROR_CONTENT'));
        elseif((int)lookupLevel($_SESSION['id']) != 1)
            $this->error('level mismatch');
        elseif(I('id') != $_SESSION['id'])
            $this->error('judge mismatch');
        else{
            $table = M('l2checklist');
            $data['judge_id'] = I('id');
            $data['item_type'] = I('type');
            $data['item_date'] = I('date');
            $data['item_subject'] = I('subject');
            $data['item_body'] = I('body');

                if($table->add($data))
                    logEvent($_SESSION['id'], 'addL2c_'.I('type'), $_SESSION['id'].','.lookupJudgeById($_SESSION['id']));
                else
                    $this-error(C('STR_DB_FAILURE'));   
        }

        U('Panel/showchecklist', '', '', 1);
    }

    public function deleteitem(){
        $table = M('l2checklist');
        $condition['id'] = I('id');
        $condition['judge_id'] = $_SESSION['id'];

        if($table->where($condition)->find()){
            $table->where($condition)->delete();
            logEvent($_SESSION['id'], 'deleteL2c_'.I('type'), $_SESSION['id'].','.lookupJudgeById($_SESSION['id']));

        }

        U('Panel/showchecklist', '', '', 1);
    }

}