<?php
class Details
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
            $this->redirect('eventlist');
    }

    public function display()
    {
        $str = '';
        $str = $str.'<table class="eventdetail">';
        $str = $str.$this->showDetail();
        $str = $str.$this->showCommonOption();
        $str = $str.$this->showAdminOption();
        $str = $str.'</table>';
        return $str;
    }
    private function showDetail() {
        $str = '';
        $data = M('eventjudge');
        $condition = Array('id' => $this->id, 'is_hj' => '1');
        $detail = $data->where($condition)->find();
        if(count($detail)==0){
            U('eventlist', '', '', 1);
        }
        else{
            if(isCustomEvent($this->id)){
                $str = $str.'<tr><th>'.C('STR_CAUTION').'</th>';
                $str = $str.'<td class="alt">'.C('STR_CAUTION_CUSTOM_1').lookupJudgeById(lookupCustomEventCreator($this->id)).C('STR_CAUTION_CUSTOM_2').'</td></tr>';
            }
            $str = $str.'<tr><th>'.C('STR_EVENT_NAME').'</th>';
            $str = $str.'<td>'.$detail['name'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_CITY').'</th>';
            $str = $str.'<td>'.$detail['city'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_STORE').'</th>';
            $str = $str.'<td><a href="'.U('storedetail', Array('store' => $detail['store'])).'">'.lookupStoreById($detail['store']).'</a></td></tr>';
            $str = $str.'<tr><th>'.C('STR_EVENT_DATE').'</th>';
            $str = $str.'<td>'.formatDate($detail['event_date']).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_PICKUP_JUDGE').'</th>';
            $str = $str.'<td>'.formatPickupJudge($detail['fullname']).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_EVENT_LOCATION').'</th>';
            $str = $str.'<td>'.$detail['location'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_EVENT_TYPE').'</th>';
            $str = $str.'<td>'.formatEventType($detail['type']).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_EVENT_FORMAT').'</th>';
            $str = $str.'<td>'.formatEventFormat($detail['format']).'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_DESCRIPTION').'</th>';
            $str = $str.'<td>'.$detail['description'].'</td></tr>';

            $condition_fj = Array('id' => $this->id, 'is_hj' => '0');
            $detail_fj = $data->where($condition_fj)->select();

            if(count($detail_fj)!=0){
                $str = $str.'<tr><th>'.C('STR_OTHER_JUDGE').'</th>';
                $str = $str.'<td>';
                foreach($detail_fj as $value){
                    $str = $str.$value['fullname'].C('STR_SPLIT_SEMICOLON');
                }
                $str = $str.'</td></tr>';
            }   
            
        }
        
        return $str;
    }

    private function showCommonOption() {
        $str = '';
        $data = M('eventjudge');
        $condition = Array('id' => $this->id, 'judge_id' => $this->current);
        $status = $data->where($condition)->find();

        if((int)lookupEventDate($this->id) <= (int)todayDate() && lookupEventType($this->id) == 'PPTQ'){
            $str = $str.'<tr><th>'.C('STR_CHAMPION').'</th>';
            if(strlen(lookupEventChampion($this->id)) == 0){
                if(!$this->isLogin)
                    $str = $str.'<td>'.C('STR_CHAMPION_NULL').'</td></tr>';
                else
                    $str = $str.'<td><a href="'.U('/Event/action', Array('action' => 'champion', 'eventid' => $this->id)).'">'.C('STR_FILL_CHAMPION').'</a></td></tr>';
            }
            else{
                $str = $str.'<td>'.lookupEventChampion($this->id).'</td></tr>';
            }
        }

        $str = $str.'<tr><th>'.C('STR_COMMON_OPTION').'</th>';
        if(!$this->isLogin){
            $str = $str.'<td><a href="'.U('/Index/login').'">'.C('STR_LOGIN_REMINDER').'</a></td></tr>';
        }
        else{
            $str = $str.'<td>';


            if(count($status)>0){
                $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_WITHDRAW').lookupEventName($this->id).'\')" href="'.U('/Event/action', Array('action' => 'withdraw', 'eventid' => $this->id)).'">'.C('STR_WITHDRAW').'</a>';
            }
            else{
                $condition_hj = Array('id' => $this->id, 'is_hj' => '1');
                $status_hj = $data->where($condition_hj)->find();
                if($status_hj['judge_id']>=1){
                    $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_FOLLOWUP').lookupEventName($this->id).'\')" href="'.U('/Event/action', Array('action' => 'followup', 'eventid' => $this->id)).'">'.C('STR_FOLLOWUP').'</a>';
                }
                else{
                    $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_PICKUP').lookupEventName($this->id).'\')" href="'.U('/Event/action', Array('action' => 'pickup', 'eventid' => $this->id)).'">'.C('STR_PICKUP').'</a>';
                }
            }
            $str = $str.'</td></tr>';
        }
       
        return $str;
    }

    private function showAdminOption() {
        $str = '';

        if((int)lookupAdmin($this->current) >= 1 || isCustomEventCreator($this->id, $_SESSION['id'])){
            $str = $str.'<tr><th>'.C('STR_ADMIN_OPTION').'</th>';
            $str = $str.'<td>';
            $str = $str.'<a href="'.U('/Event/action', Array('action' => 'admin_modify', 'eventid' => $this->id)).'">'.C('STR_EVENTADMIN_MODIFY').'</a>';
            $str = $str.C('STR_SPLIT');
            $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_EVENT_DELETE').lookupEventName($this->id).'\')" href="'.U('/Event/action', Array('action' => 'admin_delete', 'eventid' => $this->id)).'">'.C('STR_EVENTADMIN_DELETE').'</a>';
            $str = $str.C('STR_SPLIT');
            $str = $str.'<a onclick="return confirm(\''.C('STR_CONFIRM_EVENT_RESET').lookupEventName($this->id).'\')" href="'.U('/Event/action', Array('action' => 'admin_reset', 'eventid' => $this->id)).'">'.C('STR_EVENTADMIN_RESET').'</a>';
            $str = $str.'</td></tr>';


        }

        return $str;
    }
}
?>