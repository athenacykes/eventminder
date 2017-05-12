<?php
class StoreList
{
    private $city;
    private $judge;
    private $isLogin;
    private $next;

// construction
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

        $str = $str.'<table class="storelist">';
        $str = $str.$this->showTableHeader();
        $str = $str.$this->showContent();
        $str = $str.'</table><hr /><p>';
        $str = $str.$this->showFilters();
        $str = $str.'</p>';

        return $str;
    }

    private function showTableHeader() {
        $str = '';

        $str .= '<tr><th>'.C('STR_STORE_NAME').'</th>';
        $str .= '<th>'.C('STR_CITY_SHORT').'</th>';
        $str .= '<th>'.C('STR_FUTURE_EVENT').'</th>';
        $str .= '<th>'.C('STR_ACTION').'</th></tr>';

        return $str; 
    }

    private function showContent() {
        $str = '';
        $data = M('store');

        if(strlen($this->city)>0){
            $condition['city'] = $this->city;
        }
        
        $startpage = 0;
        if((int)$this->next>0){
            $startpage = $this->next;
        }
        $total = count($data->where($condition)->select());
        $order = Array('convert(city using gbk)' => 'asc', 'convert(name using gbk)' => 'asc');

        $record = $data->where($condition)->order($order)->limit($startpage, C('EVM_PAGE_LIMIT'))->select();

        foreach($record as $value){    	
            $str .= '<tr>';
            $str .= '<td class="name"><a href="'.U('storedetail', Array('store' => $value['id'])).'">'.$value['name'].'</a></td>';
            $str .= '<td class="city">'.$value['city'].'</td>';
            $str .= '<td class="event">'.$this->getStoreFutureEvents($value['id']).'</td>';
            if($this->isLogin != '1')
                $str .= '<td class="action"></td>';
            else
                $str .= '<td class="action">'.$this->buttonFollowUnfo($value['id']).'</td>';
            $str .= '</tr>';

        }
        $str = $str.$this->showSplitPageBar($total, $startpage);
        return $str;
    }

    private function showSplitPageBar($total, $bookmark){
        $str = '';

        if(strlen($this->city)>0){
            $param['city'] = $this->city;
        }

        $str = $str.'<tr>';
        $str = $str.'<td class="name">';

        if($total == 0){
            $str = $str.C('STR_NO_EVENT_RESULT');
        }
        else{
            if(!isset($_GET['next']) || ($bookmark+1) <= C('EVM_PAGE_LIMIT') || $bookmark >= $total){
                $str = $str.'<a href="'.U('storelist',$param).'">'.C('STR_EVENTLIST_FIRST_PAGE').'</a>&nbsp;&nbsp;';
            }
            else{
                $param['next'] = $bookmark - C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('storelist',$param).'">'.C('STR_EVENTLIST_PREV_PAGE').'</a>&nbsp;&nbsp;';
            }

            unset($param['next']);

            if(($bookmark + C('EVM_PAGE_LIMIT')) >= $total){
                $param['next'] = $total - C('EVM_PAGE_LIMIT');
            $str = $str.'<a href="'.U('storelist',$param).'">'.C('STR_EVENTLIST_LAST_PAGE').'</a>';
            }
            else{
                $bookmark_a = isset($_GET['next']) ? $bookmark : 0;
                $param['next'] = $bookmark + C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('storelist',$param).'">'.C('STR_EVENTLIST_NEXT_PAGE').'</a>&nbsp;&nbsp;';
            }
        }
        $str = $str.'</td>';
        $str = $str.'<td colspan="3">';
        if($total != 0){
            $ubound = (($bookmark + C('EVM_PAGE_LIMIT')) > $total) ? $total : ($bookmark + C('EVM_PAGE_LIMIT'));

            $str = $str.C('STR_STORELIST_CURRENT_PAGE_1').$total.C('STR_STORELIST_CURRENT_PAGE_2');
            $str = $str. ($bookmark+1) . C('STR_STORELIST_CURRENT_PAGE_3'). ($ubound) . C('STR_STORELIST_CURRENT_PAGE_4');
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

                $str = $str.'<td><button onclick="window.location=\''.U($this->action,reduceParam($param,$key)).'\'">'.C('STR_REMOVE_FILTER').'</button></td>';
                $str = $str.'</tr>';

            }
        }
            $city_list = getFilterCityStore();

            $str = $str.'<form action="'.U('storefilter').'" method="post">';
            $str = $str.'<tr><td><select name="filtercity">';
            $str = $str.'<option value="default">'.C('STR_FILTER_CITY').'</option>';
            foreach($city_list as $value){
                $str = $str.'<option value="'.$value.'">'.$value.'</option>';
            }
            $str = $str.'</select></td>';


            $str = $str.'<td colspan="3">';
            $str = $str.'</td></tr>';

            $str = $str.'<tr><td class="wide" colspan="4" style="text-align: center;">';
            $str = $str.'<input type="submit" value="'.C('STR_FILTER_SUBMIT').'" name="filtersubmit" id="filtersubmit" /></td></tr>';
            $str = $str.'<input type="hidden" value="'.$param['city'].'" name="origcity" id="origcity" />';
            $str = $str.'</form>';
            $str = $str.'</table>';
        return $str;
    }

    private function getStoreFutureEvents($storeid){
        $data = M('event');
        $condition = Array('store' => $storeid, 'event_date' => Array('egt', todayDate()));
        $count = $data->where($condition)->count();

        if($count == 0)
            $str = C('STR_NO_FUTURE_EVENT');
        else{
            if(!$this->isLogin)
                $str = '<a href="'.U('Index/eventlist', Array('store' => $storeid)).'">'.$count.C('STR_CALENDAR_EVENT_COUNT').'</a>';
            else
                $str = '<a href="'.U('Event/eventlist', Array('store' => $storeid)).'">'.$count.C('STR_CALENDAR_EVENT_COUNT').'</a>';
        }
            
        return $str;
    }

    private function buttonFollowUnfo($storeid){
        $str = '';
        $data = M('judgestore');
        $condition = Array('id' => $storeid, 'judge_id' => $this->judge);
        if($data->where($condition)->find())
            $str = '<button onclick="window.location=\''.U('Store/unfollow', Array('store' => $storeid)).'\'">'.C('STR_REMOVE_FAVSTORE').'</button>';
        else
            $str = '<button onclick="window.location=\''.U('Store/follow', Array('store' => $storeid)).'\'">'.C('STR_FOLLOW_FAVSTORE').'</button>';
        return $str;
    }
}
?>