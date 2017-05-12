<?php
class Listing
{
    private $date;
    private $city;
    private $region;
    private $store;
    private $judge;
    private $next;
    private $type;
    private $isLogin;
    private $past;

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
        $str = '';

        $str = $str.'<table class="eventlist">';
        $str = $str.$this->showTableHeader();
        $str = $str.$this->showContent();
        $str = $str.'</table><hr /><p>';
        $str = $str.$this->showFilters();
        $str = $str.'</p>';

        return $str;
    }

    private function showTableHeader() {
        $str = '';

        $str = $str.'<tr>';
        $str = $str.'<th>'.C('STR_EVENT_NAME').'</th>';
        $str = $str.'<th>'.C('STR_CITY_SHORT').'</th>';
        $str = $str.'<th>'.C('STR_DATE').'</th>';
        $str = $str.'<th>'.C('STR_PICKUP_JUDGE').'</th>';
        $str = $str.'</tr>';

        return $str; 
    }

    private function showContent() {
        $str = '';
        $condition = Array();
        $data = M('eventjudge');

        $condition['is_hj'] = 1;

        if((int)$this->past >= 1){
            $condition['event_date'] = Array('elt', todayDate());
        }else{
            $condition['event_date'] = Array('egt', todayDate());
        }

        if(strlen($this->date)>0 && validateDate($this->date)){
            $condition['event_date'] = $this->date;
        }

        if(strlen($this->city)>0){
            $condition['city'] = $this->city;
        }

        if(strlen($this->type)>0){
            $condition['type'] = $this->type;
        }

        if(strlen($this->region)>0){
            $condition['georegion'] = $this->region;
        }

        if(strlen($this->store)>0){
            $condition['store'] = $this->store;
        }

        if(strlen($this->judge)>0){
            $condition['judge_id'] = $this->judge;
            unset($condition['is_hj']);
        }

        $startpage = 0;
        if((int)$this->next>0){
            $startpage = $this->next;
        }
        
        $total = $data->where($condition)->count();
        if((int)$this->past >= 1){
            $record = $data->where($condition)->order(array('event_date' => 'desc', 'convert(city using gbk)' => 'asc', 'type' => 'desc', 'pickup_date' => 'desc'))->limit($startpage, C('EVM_PAGE_LIMIT'))->select();
        }elseif((int)$this->past == -1){
            $condition = reduceParam($condition, 'event_date');
            $total = $data->where($condition)->count();
            $record = $data->where($condition)->order(array('event_date' => 'asc', 'convert(city using gbk)' => 'asc', 'type' => 'desc', 'pickup_date' => 'desc'))->limit($startpage, C('EVM_PAGE_LIMIT'))->select();
        }
        else{
            $record = $data->where($condition)->order(array('event_date' => 'asc', 'convert(city using gbk)' => 'asc', 'type' => 'desc', 'pickup_date' => 'desc'))->limit($startpage, C('EVM_PAGE_LIMIT'))->select();
        }

        foreach($record as $value){
            $cell_css['wide'] = 'wide';
            $cell_css['city'] = 'city';
            $cell_css['thin'] = 'thin';
            $cell_css['judge'] = 'judge';

            if(isCustomEvent($value['id'])){
                foreach($cell_css as $k => $v)
                    $cell_css[$k] = 'alt'.$v;
            }

            $str = $str.'<tr>';
            $str = $str.'<td class="'.$cell_css['wide'].'"><a href="'.U('eventdetail',Array('id' => $value['id'])).'">'.$value['name'].'</a></td>';
            $str = $str.'<td class="'.$cell_css['city'].'">'.$value['city'].'</td>';
            $str = $str.'<td class="'.$cell_css['thin'].'">'.$value['event_date'].'</td>';
            // $str = $str.'<td class="'.$cell_css['judge'].'">';
            if($value['judge_id'] == (-1)){
                $str = $str.'<td class="'.$cell_css['judge'].'">';
                $str = $str.C('STR_EVENTLIST_NOJUDGE');
            }
            else{
                $cell_css['judge'] = 'down';
                $str = $str.'<td class="'.$cell_css['judge'].'">';
                if($condition['is_hj']==1){
                    $str = $str.$value['fullname'];
                }
                else{
                    $str = $str.lookupJudgeById(lookupHJ($value['id']));
                }
            }
            $str = $str.'</td>';
            $str = $str.'</tr>';

        }
        $str = $str.$this->showSplitPageBar($total, $startpage);
        return $str;
    }

    private function showSplitPageBar($total, $bookmark){
        $str = '';

        if(strlen($this->date)>0){
            $param['date'] = $this->date;
        }
        if(strlen($this->city)>0){
            $param['city'] = $this->city;
        }
        if(strlen($this->type)>0){
            $param['type'] = $this->type;
        }
        if(strlen($this->judge)>0){
            $param['judge'] = $this->judge;
        }
        if(strlen($this->region)>0){
            $param['region'] = $this->region;
        }
        if(strlen($this->store)>0){
            $param['store'] = $this->store;
        }
        if(strlen($this->past)>0){
            $param['past'] = $this->past;
        }
        //C('EVM_PAGE_LIMIT')
        $str = $str.'<tr>';
        $str = $str.'<td class="wide">';

        if($total == 0){
            $str = $str.C('STR_NO_EVENT_RESULT');
        }
        else{
            if(!isset($_GET['next']) || ($bookmark+1) <= C('EVM_PAGE_LIMIT') || $bookmark >= $total){
                $str = $str.'<a href="'.U('eventlist',$param).'">'.C('STR_EVENTLIST_FIRST_PAGE').'</a>&nbsp;&nbsp;';
            }
            else{
                $param['next'] = $bookmark - C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('eventlist',$param).'">'.C('STR_EVENTLIST_PREV_PAGE').'</a>&nbsp;&nbsp;';
            }

            unset($param['next']);

            if(($bookmark + C('EVM_PAGE_LIMIT')) >= $total){
                $param['next'] = $total - C('EVM_PAGE_LIMIT');
            $str = $str.'<a href="'.U('eventlist',$param).'">'.C('STR_EVENTLIST_LAST_PAGE').'</a>';
            }
            else{
                $bookmark_a = isset($_GET['next']) ? $bookmark : 0;
                $param['next'] = $bookmark + C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('eventlist',$param).'">'.C('STR_EVENTLIST_NEXT_PAGE').'</a>&nbsp;&nbsp;';
            }
        }
        $str = $str.'</td>';
        $str = $str.'<td colspan="3">';
        if($total != 0){
            $ubound = (($bookmark + C('EVM_PAGE_LIMIT')) > $total) ? $total : ($bookmark + C('EVM_PAGE_LIMIT'));

            $str = $str.C('STR_EVENTLIST_CURRENT_PAGE_1').$total.C('STR_EVENTLIST_CURRENT_PAGE_2');
            $str = $str. ($bookmark+1) . C('STR_EVENTLIST_CURRENT_PAGE_3'). ($ubound) . C('STR_EVENTLIST_CURRENT_PAGE_4');
            $str = $str.'</td>';
            $str = $str.'</tr>';   
        }
        return $str;
    }

    private function showFilters(){
        $str = '';
        if(strlen($this->date && validateDate($this->date))>0){
            $param['date'] = $this->date;
        }
        if(strlen($this->city)>0){
            $param['city'] = $this->city;
        }
        if(strlen($this->judge)>0){
            $param['judge'] = $this->judge;
        }
        if(strlen($this->type)>0){
            $param['type'] = $this->type;
        }
        if(strlen($this->past)>0){
            $param['past'] = $this->past;
        }
        if(strlen($this->region)>0){
            $param['region'] = $this->region;
        }
        if(strlen($this->store)>0){
            $param['store'] = $this->store;
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
                if($key == 'date'){
                    $str = $str.C('STR_DATE').'</th>';
                    $str = $str.'<td colspan="2">'.formatDate($value).'</td>';
                    
                }
                else if ($key == 'city'){
                    $str = $str.C('STR_CITY_SHORT').'</th>';
                    $str = $str.'<td colspan="2">'.$value.'</td>';
                }
                else if ($key == 'region'){
                    $str = $str.C('STR_REGION').'</th>';
                    $str = $str.'<td colspan="2">'.lookupRegionById($value).'</td>';
                }
                else if ($key == 'store'){
                    $str = $str.C('STR_STORE').'</th>';
                    $str = $str.'<td colspan="2">'.lookupStoreById($value).'</td>';
                }
                else if ($key == 'judge'){
                    $str = $str.C('STR_JUDGE').'</th>';
                    $str = $str.'<td colspan="2">'.lookupJudgeById($value).'</td>';
                }
                else if ($key == 'type'){
                    $str = $str.C('STR_EVENT_TYPE').'</th>';
                    $str = $str.'<td colspan="2">'.formatEventType($value).'</td>';
                }
                else if ($key == 'past'){
                    $str = $str.C('STR_CONDITION').'</th>';
                    if((int)$value > 0){
                        $str = $str.'<td colspan="2">'.C('STR_PAST_EVENT_SHORT').'</td>';
                    }
                    else{
                        $str = $str.'<td colspan="2">'.C('STR_ALL_EVENT_SHORT').'</td>';
                    }
                }

                $str = $str.'<td><button onclick="window.location=\''.U('eventlist',reduceParam($param,$key)).'\'">'.C('STR_REMOVE_FILTER').'</button></td>';
                $str = $str.'</tr>';

            }
        }
            if((int)$this->past >= '1'){
                $date_list = getFilterDatePast(20);
            }else{
                $date_list = getFilterDate(20);
            }
            $judge_list = getFilterJudge();
            $city_list = getFilterCity();
            $type_list = getFilterType();
            $region_list = getFilterRegion();

            $str = $str.'<form action="'.U('filter').'" method="post">';
            // $str = $str.'<tr><td><select name="filterdate">';
            // $str = $str.'<option value="default">'.C('STR_FILTER_DATE').'</option>';
            // foreach($date_list as $value){
            //     $str = $str.'<option value="'.$value.'">'.formatDate($value).'</option>';
            // }
            // $str = $str.'</select></td>';
            $str = $str.'<tr><td><select name="filterregion">';
            $str = $str.'<option value="default">'.C('STR_FILTER_REGION').'</option>';
            foreach($region_list as $value){
                $str = $str.'<option value="'.$value['id'].'">'.$value['region'].'</option>';
            }   
            $str = $str.'</select></td>';

            $str = $str.'<td><select name="filtertype">';
            $str = $str.'<option value="default">'.C('STR_FILTER_TYPE').'</option>';
            foreach($type_list as $value){
                $str = $str.'<option value="'.$value.'">'.$value.'</option>';
            }   
            $str = $str.'</select></td>';

            $str = $str.'<td><select name="filterjudge">';
            $str = $str.'<option value="default">'.C('STR_FILTER_JUDGE').'</option>';
            foreach($judge_list as $value){
                $str = $str.'<option value="'.$value['id'].'">'.$value['text'].'</option>';
            }   
            $str = $str.'</select></td>';

            $str = $str.'<td><select name="filtercity">';
            $str = $str.'<option value="default">'.C('STR_FILTER_CITY').'</option>';
            foreach($city_list as $value){
                $str = $str.'<option value="'.$value.'">'.$value.'</option>';
            }
            $str = $str.'</select></td></tr>';
            $str = $str.'<tr><td class="wide" colspan="4" style="text-align: center;">';
            $str = $str.'<input type="submit" value="'.C('STR_FILTER_SUBMIT').'" name="filtersubmit" id="filtersubmit" /></td></tr>';
            $str = $str.'<input type="hidden" value="'.$param['date'].'" name="origdate" id="origdate" />';
            $str = $str.'<input type="hidden" value="'.$param['store'].'" name="origstore" id="origstore" />';
            $str = $str.'<input type="hidden" value="'.$param['region'].'" name="origregion" id="origregion" />';
            $str = $str.'<input type="hidden" value="'.$param['city'].'" name="origcity" id="origcity" />';
            $str = $str.'<input type="hidden" value="'.$param['judge'].'" name="origjudge" id="origjudge" />';
            $str = $str.'<input type="hidden" value="'.$param['type'].'" name="origtype" id="origtype" />';
            $str = $str.'<input type="hidden" value="'.$param['past'].'" name="origpast" id="origpast" />';
            $str = $str.'</form>';
            $str = $str.'</table>';
        return $str;
    }

}
?>