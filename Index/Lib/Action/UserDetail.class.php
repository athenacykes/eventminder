<?php
class UserDetail
{
    private $id;
    private $isLogin;
    private $current;
     
    function __construct($options = array()) {
         
        $vars = get_class_vars(get_class($this));
        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
        if((int)$this->id<=0)
            $this->redirect('userdetail');
    }

    public function display()
    {
        $str = '';
        $str = $str.'<table class="userdetail">';
        $str = $str.$this->showDetail();
        $str = $str.$this->showCommonOption();
        $str = $str.$this->showAdminOption();
        $str = $str.'</table>';
        return $str;
    }
    private function showDetail() {
        $str = '';
        $data = M('user');
        $condition = Array('id' => $this->id);
        $detail = $data->where($condition)->find();
        if(count($detail)==0){
            U('userlist', '', '', 1);
        }
        else{
            $str = $str.'<tr><th>'.C('STR_USERNAME').'</th>';
            $str = $str.'<td>'.$detail['username'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_REALNAME').'</th>';
            $str = $str.'<td>'.$detail['fullname'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_CITY').'</th>';
            $str = $str.'<td>'.$detail['city'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_JUDGELEVEL').'</th>';
            $str = $str.'<td>'.formatLevel($detail['level']).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_DCI_NUMBER').'</th>';
            if((int)lookupAdmin($this->current) >= 1 || $detail['dci'] == lookupDCI($this->current))
                $dci = $detail['dci'];
            else
                $dci = C('STR_HIDDEN');
            $str = $str.'<td>'.$dci.'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_LASTLOGIN').'</th>';
            $lastlogin = strlen($detail['lastlogin_time']) == 0 ? 1 : $detail['lastlogin_time'];
            $str = $str.'<td>'.date('Y-m-d H:m:s', $lastlogin).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_USER_STATUS').'</th>';
            if($detail['reserved_1'] == 'pending')
                $user_status = C('STR_PENDING');
            elseif($detail['reserved_1'] == 'normal')
                $user_status = C('STR_NORMAL');
            $str = $str.'<td>'.$user_status.'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_ASSOCIATED_EVENTS').'</th>';
            $str = $str.'<td><a href="'.U('Event/eventlist', Array('judge' => $this->id, 'past' => '-1')).'">'.C('STR_ASSOCIATED_EVENTS_1').lookupJudgeById($this->id).C('STR_ASSOCIATED_EVENTS_2').'</a></td></tr>';
            }   
        
        return $str;
    }

    private function showCommonOption() {
        $str = '';
 
        return $str;
    }

    private function showAdminOption() {
        $str = '';

        if((int)lookupAdmin($this->current) >= 2){
            $str = $str.'<tr><th>'.C('STR_ADMIN_OPTION').'</th>';
            $str = $str.'<td>';
            $str = $str.'<a href="'.U('/Panel/action', Array('action' => 'user_modify', 'id' => $this->id)).'">'.C('STR_EDIT_USER').'</a>';
            $str = $str.C('STR_SPLIT');
            $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_RESET_PWD').lookupJudgeByID($this->id).'\')" href="'.U('/Panel/action', Array('action' => 'user_resetpwd', 'id' => $this->id)).'">'.C('STR_RESET_USER_PWD').'</a>';
            $str = $str.C('STR_SPLIT');
            if(lookupStatus($this->id) == 'pending')
                $str = $str.'<a href="'.U('/Panel/action', Array('action' => 'user_activate', 'id' => $this->id)).'">'.C('STR_ACTIVATE_USER').'</a>';
            elseif(lookupStatus($this->id) == 'normal')
                $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_DEACTIVATE_USER').lookupJudgeByID($this->id).'\')" href="'.U('/Panel/action', Array('action' => 'user_deactivate', 'id' => $this->id)).'">'.C('STR_DEACTIVATE_USER').'</a>';
            $str = $str.'</td></tr>';


        }

        return $str;
    }
}
?>