<?php
class StoreDetail
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
            U('storelist','','',1);
    }

    public function display()
    {
        $str = '';
        $str = $str.'<table class="eventdetail">';
        $str = $str.$this->showDetail();
        $str = $str.$this->showCommonOption();
        $str = $str.$this->showAdminOption();
        $str = $str.'<tr><th></th>';
        $str = $str.'<td><a href="'.U('storelist').'">'.C('STR_BACK_TO_STORELIST').'</a></td></tr>';
        $str = $str.'</table>';
        return $str;
    }
    private function showDetail() {
        $str = '';
        $data = M('store');
        $condition = Array('id' => $this->id);
        $detail = $data->where($condition)->find();
        if(count($detail)==0){
            U('storelist', '', '', 1);
        }
        else{
            $str = $str.'<tr><th>'.C('STR_STORE_NAME').'</th>';
            $str = $str.'<td>'.$detail['name'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_CITY').'</th>';
            $str = $str.'<td>'.$detail['city'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_STORE_LOCATION').'</th>';
            $str = $str.'<td>'.$detail['location'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_STORE_CONTACT').'</th>';
            $str = $str.'<td>'.$detail['contact'].'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_STORE_TRAVELGUIDE').'</th>';
            $str = $str.'<td>'.$this->getTravelGuide().'</td></tr>';
            $str = $str.'<tr><th>'.C('STR_FUTURE_EVENT').'</th>';
            $str = $str.'<td>'.$this->getRecentEvent().'</td></tr>';
        }
        
        return $str;
    }

    private function showCommonOption() {
        $str = '';
        
        $str = $str.'<tr><th>'.C('STR_COMMON_OPTION').'</th>';
        if(!$this->isLogin){
            $str = $str.'<td><a href="'.U('Index/login').'">'.C('STR_LOGIN_REMINDER').'</a></td></tr>';
        }
        else{
            $str = $str.'<td>';
            $data = M('judgestore');
            $condition = Array('id' => $this->id, 'judge_id' => $this->current);
            $status = $data->where($condition)->find();
            if(count($status)>0){
                $str = $str.'<a href="'.U('Store/unfollow', Array('store' => $this->id)).'">'.C('STR_REMOVE_FAVSTORE').'</a>';
            }
            else{
                $str = $str.'<a href="'.U('Store/follow', Array('store' => $this->id)).'">'.C('STR_FOLLOW_FAVSTORE').'</a>';
            }
            $str = $str.'</td></tr>';
        }
       
        return $str;
    }

    private function showAdminOption() {
        $str = '';

        if((int)lookupAdmin($this->current) >= 1){
            $str = $str.'<tr><th>'.C('STR_ADMIN_OPTION').'</th>';
            $str = $str.'<td>';
            $str = $str.'<a href="'.U('Store/modify', Array('store' => $this->id)).'">'.C('STR_STORE_MODIFY').'</a>';
            $str = $str.'</td></tr>';
        }

        return $str;
    }

    private function getTravelGuide(){
        $str = '';
        $data = M('travelguide');
        $condition = Array('store' => $this->id);
        
        if($guide = $data->where($condition)->find()){
            //$str = $guide['content'];
            $str = str_replace(PHP_EOL,'<br />',$guide['content']);
            if($this->isLogin == '1')
                $str .= '<hr />'.C('STR_TRAVELGUIDE_AUTHOR_1').lookupJudgeById($guide['author']).C('STR_TRAVELGUIDE_AUTHOR_2').C('STR_SPLIT').'<a href="'.U('Store/modifytravelguide', Array('store' => $this->id)).'">'.C('STR_MODIFY_TRAVELGUIDE').'</a>';
        }
        else{
            if($this->isLogin == '1')
                $str = '<a href="'.U('Store/addtravelguide', Array('store' => $this->id)).'">'.C('STR_ADD_TRAVELGUIDE').'</a>';
            else
                $str = C('STR_NO_TRAVELGUIDE');
        }

        return $str;
    }

    private function getRecentEvent(){
        $str = '';
        $data = M('event');
        $condition = Array('store' => $this->id, 'event_date' => Array('egt', todayDate()));
        $event = $data->where($condition)->order('event_date asc')->select();

        if(count($event) == 0)
            $str = C('STR_NO_FUTURE_EVENT');
        else{
            foreach($event as $value){
                if(!$this->isLogin)
                    $str .= '<a href="'.U('Index/eventdetail', Array('id' => $value['id'])).'">'.$value['type'].' '.$value['associated_city'].', '.formatDate($value['event_date']).'</a><br />';
                else
                    $str .= '<a href="'.U('Event/eventdetail', Array('id' => $value['id'])).'">'.$value['type'].' '.$value['associated_city'].', '.formatDate($value['event_date']).'</a><br />';
            }
        }
            
        return $str;
    }
}
?>