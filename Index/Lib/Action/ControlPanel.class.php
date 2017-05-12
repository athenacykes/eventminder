<?php

class ControlPanel
{
    private $action;
    private $current;
    private $city;
    private $level;
    private $next;

    function __construct($options = array()) {

        $vars = get_class_vars(get_class($this));

        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
    }

    public function display()
    {
        if(!$this->action){
            $str = $str.'<table class="controlpanel">';
            $str = $str.'<tr><th>'.C('STR_COMMON_OPTION').'</th>';
            $str = $str.'<td class="wide"><a href="'.U('Panel/changepwd').'">'.C('STR_CHANGE_PASSWORD').'</a>'.C('STR_SPLIT');
            $str = $str.'<a href="'.U('Panel/changecity').'">'.C('STR_CHANGE_CITY').'</a>';
            $str = $str.'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_ADMIN_OPTION').'</th>';
            $str = $str.'<td class="wide"><a href="'.U('Panel/userlist').'">'.C('STR_USERLIST').'</a>';
            if(lookupAdmin($this->current)){
                $str = $str.C('STR_SPLIT').'<a href="'.U('Panel/approveuser').'">'.C('STR_APPROVE_USER').'</a>';
                $str = $str.C('STR_SPLIT').'<a href="'.U('Panel/announcement').'">'.C('STR_CHANGE_ANNOUNCEMENT').'</a>';
                $str = $str.C('STR_SPLIT').'<a href="'.U('Panel/adminchecklist').'">'.C('STR_L2_CHECKLIST').'</a>';
            }
            $str = $str.'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_STATS_TITLE').'</th>';
            $str = $str.'<td class="wide"><a href="'.U('Stats/index').'">'.C('STR_STATS_TITLE').'</a>';
            $str = $str.'</td></tr>';
            $str = $str.'</table>';
            return $str;
        }
        else{
            if($this->action == 'approveuser'){
                return $this->approveuser();
            }
            elseif($this->action == 'userlist'){
                return $this->userlist();
            }
            else{
                U('Panel/index', '', '', 1);
            }
        }

    }

    private function approveuser() {
        $str = '';
        $param = 'approve';

        $str = $str.'<table class="userlist">';
        $str = $str.$this->showTableHeader();
        $str = $str.$this->showUserList($param);
        $str = $str.'</table><hr /><p>';
        $str = $str.$this->showFilters();
        $str = $str.'</p>';

        return $str;
    }

    private function userlist() {
        $str = '';
        if((int)lookupAdmin($_SESSION['id']) >= 2)
            $param = 'manage';
        else
            $param = 'list';

        $str = $str.'<table class="userlist">';
        $str = $str.$this->showTableHeader();
        $str = $str.$this->showUserList($param);
        $str = $str.'</table><hr /><p>';
        $str = $str.$this->showFilters();
        $str = $str.'</p>';

        return $str;
    }

    private function showTableHeader() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_USERNAME').'</th>';
        $str = $str.'<th>'.C('STR_REALNAME_SHORT').'</th>';
        $str = $str.'<th>'.C('STR_LEVEL_TITLE').'</th>';
        $str = $str.'<th>'.C('STR_CITY_SHORT').'</th>';
        $str = $str.'<th>'.C('STR_ACTION').'</th></tr>';
        return $str;
    }

    private function showUserList($param) {
        $table = M('user');
        if($param == 'approve')
            $condition['reserved_1'] = 'pending';
        else
            $condition['reserved_1'] = 'normal';


        if(strlen($this->city)>0){
            $condition['city'] = $this->city;
        }

        if(strlen($this->level)>0){
            $condition['level'] = $this->level;
        }

        $startpage = 0;
        if((int)$this->next>0){
            $startpage = $this->next;
        }
        $total = count($table->where($condition)->select());

        $record = $table->where($condition)->order(Array('level' => 'desc', 'convert(city using gbk)' => 'asc', 'convert(fullname using gbk)' => 'asc'))->limit($startpage, C('EVM_PAGE_LIMIT'))->select();
        foreach($record as $value){
            $str = $str.'<tr>';
            $str = $str.'<td class="username">'.$value['username'].'</td>';
            $str = $str.'<td class="fullname"><a href="'.U('userdetail',Array('id' => $value['id'])).'">'.$value['fullname'].'</a></td>';
            $str = $str.'<td class="level">'.formatLevel($value['level']).'</td>';
            // if((int)lookupAdmin($this->current) >= 1 || $value['dci'] == lookupDCI($this->current))
            //     $dci = $value['dci'];
            // else
            //     $dci = C('STR_HIDDEN_SHORT');
            
            $str = $str.'<td class="dci">'.$value['city'].'</td>';
            $str = $str.'<td class="action">'.$this->showAction($param, $value['id']).'</td>';
            $str = $str.'</tr>';

        }
        $str = $str.$this->showSplitPageBar($total, $startpage);
        return $str;
    }

    private function showSplitPageBar($total, $bookmark){
        $str = '';

        if(strlen($this->city)>0){
            $param['city'] = $this->city;
        }

        if(strlen($this->level)>0){
            $param['level'] = $this->level;
        }

        $str = $str.'<tr>';
        $str = $str.'<td colspan="2">';

        if($total == 0){
            $str = $str.C('STR_NO_USER_RESULT');
        }
        else{
            if(!isset($_GET['next']) || ($bookmark+1) <= C('EVM_PAGE_LIMIT') || $bookmark >= $total){
                $str = $str.'<a href="'.U($this->action,$param).'">'.C('STR_EVENTLIST_FIRST_PAGE').'</a>&nbsp;&nbsp;';
            }
            else{
                $param['next'] = $bookmark - C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U($this->action,$param).'">'.C('STR_EVENTLIST_PREV_PAGE').'</a>&nbsp;&nbsp;';
            }

            unset($param['next']);

            if(($bookmark + C('EVM_PAGE_LIMIT')) >= $total){
                $param['next'] = $total - C('EVM_PAGE_LIMIT');
            $str = $str.'<a href="'.U($this->action,$param).'">'.C('STR_EVENTLIST_LAST_PAGE').'</a>';
            }
            else{
                $bookmark_a = isset($_GET['next']) ? $bookmark : 0;
                $param['next'] = $bookmark + C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U($this->action,$param).'">'.C('STR_EVENTLIST_NEXT_PAGE').'</a>&nbsp;&nbsp;';
            }
        }
        $str = $str.'</td>';
        $str = $str.'<td colspan="3">';
        if($total != 0){
            $ubound = (($bookmark + C('EVM_PAGE_LIMIT')) > $total) ? $total : ($bookmark + C('EVM_PAGE_LIMIT'));

            $str = $str.C('STR_USERLIST_CURRENT_PAGE_1').$total.C('STR_USERLIST_CURRENT_PAGE_2');
            $str = $str. ($bookmark+1) . C('STR_USERLIST_CURRENT_PAGE_3'). ($ubound) . C('STR_USERLIST_CURRENT_PAGE_4');
            $str = $str.'</td>';
            $str = $str.'</tr>';   
        }
        return $str;
    }

    private function showFilters(){
        $str = '';
        if(strlen($this->city)>0){
            $param['city'] = $this->city;
        }

        if(strlen($this->level)>0){
            $param['level'] = $this->level;
        }

        $str = $str.'<table class="filter">';
        $str = $str.'<tr>';
        $str = $str.'<th colspan="4">'.C('STR_FILTER').'</th>';
        if(count($param)==0){
            $str = $str.'<tr><td colspan="4" class="wide">'.C('STR_NO_FILTER').'</td></tr>';
        }
        else{
            foreach($param as $key => $value){
                $str = $str.'<tr><th>';
                if($key == 'city'){
                    $str = $str.C('STR_CITY_SHORT').'</th>';
                    $str = $str.'<td colspan="2">'.$value.'</td>';
                    
                }
                else if ($key == 'level'){
                    $str = $str.C('STR_LEVEL_TITLE').'</th>';
                    $str = $str.'<td colspan="2">'.formatLevel($value).'</td>';
                }

                $str = $str.'<td><button onclick="window.location=\''.U($this->action,reduceParam($param,$key)).'\'">'.C('STR_REMOVE_FILTER').'</button></td>';
                $str = $str.'</tr>';

            }
        }
            if($this->action == 'approveuser'){
                $city_list = getFilterUserCityPending();
                $level_list = getFilterLevelPending();

            }elseif($this->action == 'userlist'){
                $city_list = getFilterUserCity();
                $level_list = getFilterLevel();
            }


            $str = $str.'<form action="'.U('filter').'" method="post">';
            $str = $str.'<tr><td><select name="filtercity">';
            $str = $str.'<option value="default">'.C('STR_FILTER_CITY').'</option>';
            foreach($city_list as $value){
                $str = $str.'<option value="'.$value.'">'.$value.'</option>';
            }
            $str = $str.'</select></td>';

            $str = $str.'<td><select name="filterlevel">';
            $str = $str.'<option value="default">'.C('STR_FILTER_LEVEL').'</option>';
            foreach($level_list as $value){
                $str = $str.'<option value="'.$value.'">'.formatLevel($value).'</option>';
            }   
            $str = $str.'</select></td>';

            $str = $str.'<td>';
            $str = $str.'</td>';

            $str = $str.'<td>';
            $str = $str.'</td></tr>';

            $str = $str.'<tr><td class="wide" colspan="4" style="text-align: center;">';
            $str = $str.'<input type="submit" value="'.C('STR_FILTER_SUBMIT').'" name="filtersubmit" id="filtersubmit" /></td></tr>';
            $str = $str.'<input type="hidden" value="'.$param['city'].'" name="origcity" id="origcity" />';
            $str = $str.'<input type="hidden" value="'.$param['level'].'" name="origlevel" id="origlevel" />';
            $str = $str.'<input type="hidden" value="'.$this->action.'" name="action" id="action" />';
            $str = $str.'</form>';
            $str = $str.'</table>';
        return $str;
    }

    private function showAction($action, $id) {
        $str = '';

        if($action == 'approve'){
            $str .= '<button onclick="window.location=\''.U('approveusersubmit',Array('id' => $id)).'\'">'.C('STR_APPROVE').'</button>';
        }
        elseif($action == 'manage'){
            $str .= '<button onclick="window.location=\''.U('userdetail',Array('id' => $id)).'\'">'.C('STR_MANAGE_USER').'</button>';
        }

        return $str;
    }
}

?>