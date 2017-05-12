<?php
class Champions
{
    private $season;
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

        $str = $str.'<table class="championlist">';
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
        $str = $str.'<th>'.C('STR_CHAMPION_NAME').'</th>';
        $str = $str.'<th>'.C('STR_CHAMPION_DCI').'</th>';
        $str = $str.'<th>'.C('STR_EVENT_NAME').'</th>';
        $str = $str.'</tr>';

        return $str; 
    }

    private function showContent() {
        $str = '';
        $condition = Array();
        $data = M('event');

        $condition['type'] = 'PPTQ';
        $condition['event_date'] = Array('gt', todayDate()-10000);
        $condition['reserved_1'] = Array('neq', '');
        if(strlen($this->season)>0){
            $condition['name'] = Array('like', '%'.$this->season.'%');
        }
        
        $startpage = 0;
        if((int)$this->next>0){
            $startpage = $this->next;
        }
        $total = count($data->where($condition)->select());

        $record = $data->where($condition)->order('event_date desc')->limit($startpage, C('EVM_PAGE_LIMIT'))->select();

        foreach($record as $value){
        	if(strlen($value['reserved_1']) > 0){
        		$champion = explode(',',$value['reserved_1'],2);
        	}
        	else{
        		$champion = Array(C('STR_CHAMPION_NULL'), C('STR_HIDDEN_SHORT'));
        	}
        	
            $str = $str.'<tr>';
            $str = $str.'<td class="name">'.$champion[0].'</td>';
            $str = $str.'<td class="dci">'.$champion[1].'</td>';
            $str = $str.'<td class="event"><a href="'.U('eventdetail',Array('id' => $value['id'])).'">'.$value['name'].'</a></td>';
            $str = $str.'</tr>';

        }
        $str = $str.$this->showSplitPageBar($total, $startpage);
        return $str;
    }

    private function showSplitPageBar($total, $bookmark){
        $str = '';

        if(strlen($this->season)>0){
            $param['season'] = $this->season;
        }

        $param['season'] = str_replace('#', 'NO', $param['season']);

        $str = $str.'<tr>';
        $str = $str.'<td class="wide" colspan="2">';

        if($total == 0){
            $str = $str.C('STR_NO_EVENT_RESULT');
        }
        else{
            if(!isset($_GET['next']) || ($bookmark+1) <= C('EVM_PAGE_LIMIT') || $bookmark >= $total){
                $str = $str.'<a href="'.U('championlist',$param).'">'.C('STR_EVENTLIST_FIRST_PAGE').'</a>&nbsp;&nbsp;';
            }
            else{
                $param['next'] = $bookmark - C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('championlist',$param).'">'.C('STR_EVENTLIST_PREV_PAGE').'</a>&nbsp;&nbsp;';
            }

            unset($param['next']);

            if(($bookmark + C('EVM_PAGE_LIMIT')) >= $total){
                $param['next'] = $total - C('EVM_PAGE_LIMIT');
            $str = $str.'<a href="'.U('championlist',$param).'">'.C('STR_EVENTLIST_LAST_PAGE').'</a>';
            }
            else{
                $bookmark_a = isset($_GET['next']) ? $bookmark : 0;
                $param['next'] = $bookmark + C('EVM_PAGE_LIMIT');
                $str = $str.'<a href="'.U('championlist',$param).'">'.C('STR_EVENTLIST_NEXT_PAGE').'</a>&nbsp;&nbsp;';
            }
        }
        $str = $str.'</td>';
        $str = $str.'<td>';
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
        if(strlen($this->season)>0){
            $param['season'] = $this->season;
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
                if($key == 'season'){
                    $str = $str.C('STR_SEASON').'</th>';
                    $str = $str.'<td colspan="2">'.$value.'</td>';
                }

                $str = $str.'<td><button onclick="window.location=\''.U('championlist',Array()).'\'">'.C('STR_REMOVE_FILTER').'</button></td>';
                $str = $str.'</tr>';

            }
        }
            $season_list = getFilterSeason();

            $str = $str.'<form action="'.U('championfilter').'" method="post">';
            $str = $str.'<tr><td><select name="filterseason">';
            $str = $str.'<option value="default">'.C('STR_FILTER_SEASON').'</option>';
            foreach($season_list as $value){
                $str = $str.'<option value="'.$value.'">'.$value.'</option>';
            }
            $str = $str.'</select></td>';


            $str = $str.'<td colspan="3">';
            $str = $str.'</td></tr>';

            $str = $str.'<tr><td class="wide" colspan="4" style="text-align: center;">';
            $str = $str.'<input type="submit" value="'.C('STR_FILTER_SUBMIT').'" name="filtersubmit" id="filtersubmit" /></td></tr>';
            $str = $str.'<input type="hidden" value="'.$param['season'].'" name="origseason" id="origseason" />';
            $str = $str.'</form>';
            $str = $str.'</table>';
        return $str;
    }

}
?>