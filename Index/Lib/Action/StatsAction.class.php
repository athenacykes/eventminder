<?php
class StatsAction extends Action {
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
        $range['start'] = '20150201';
        $range['end'] = todayDate();

        if(I('start'))
            $range['start'] = I('start');
        if(I('end'))
            $range['end'] = I('end');        

        $this->assign('stats', $this->stats($range['start'], $range['end']));
        $this->assign('range', $range);
        logEvent($_SESSION['id'], 'lookup_stats', $range['start'].','.$range['end']);

        $this->display();
    }

    private function stats($dateStart, $dateEnd){
        $str = '';

        $str .= '<table class="eventdetail">';
        $str .= $this->showGeneralHeader();
        $str .= $this->userTotal();
        $str .= $this->userPickupPct();
        $str .= $this->showHeader($dateStart, $dateEnd);
        $str .= $this->eventTotal($dateStart, $dateEnd);
        $str .= $this->eventPickedUp($dateStart, $dateEnd);
        $str .= $this->eventPickupPct($dateStart, $dateEnd);
        $str .= $this->mostHJ($dateStart, $dateEnd);

        // $str .= $this->storeTotal($dateStart, $dateEnd);
        // $str .= $this->storePickupPct($dateStart, $dateEnd);
        $str .= '</table>';

        return $str;
    }

    private function showGeneralHeader(){
        $str .= '<tr>';
        $str .= '<th colspan="2">'.C('STR_STATS_GENERAL').'</th>';
        $str .= '</tr>';

        return $str;
    }

    private function showHeader($dateStart, $dateEnd){
        $str .= '<tr>';
        $str .= '<th colspan="2">'.C('STR_STATS_FROM').formatDate($dateStart).C('STR_STATS_TO').formatDate($dateEnd).'</th>';
        $str .= '</tr>';

        return $str;
    }

    private function eventTotal($dateStart, $dateEnd, $param, $type){
        $events = M('event');
        $condition = Array('event_date' => Array('between', $dateStart.','.$dateEnd));
        if($type){
            array_push($condition, Array('type' =>$type));
        }
        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_EVENT_TOTAL').'</th>';
        $str .= '<td>'.$events->where($condition)->count().'</td>';
        $str .= '</tr>';

        if($param == 1){
            return $events->where($condition)->count();
        }
        else
            return $str;
    }

    private function eventPickedUp($dateStart, $dateEnd, $param, $type){
        $events = M('eventjudge');
        $condition = Array('event_date' => Array('between', $dateStart.','.$dateEnd), 'is_hj' => '1', 'judge_id' => Array('neq', '-1'));
        if($type){
            array_push($condition, Array('type' =>$type));
        }
        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_EVENT_PICKEDUP').'</th>';
        $str .= '<td>'.$events->where($condition)->count().'</td>';
        $str .= '</tr>';

        if($param == 1){
            return $events->where($condition)->count();
        }
        else
            return $str;
    }

    private function eventPickupPct($dateStart, $dateEnd, $param){
        $pickedup = $this->eventPickedUp($dateStart, $dateEnd, 1);
        $total = $this->eventTotal($dateStart, $dateEnd, 1);
        $pct = getPct($pickedup, $total);

        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_EVENT_PICKUPPCT').'</th>';
        $str .= '<td>'.$pct.'%'.'</td>';
        $str .= '</tr>';

        $pickedup1 = $this->eventPickedUp($dateStart, $dateEnd, 1, 'GPT');
        $total1 = $this->eventTotal($dateStart, $dateEnd, 1, 'GPT');
        $pct1 = getPct($pickedup1, $total1);
        $pickedup2 = $this->eventPickedUp($dateStart, $dateEnd, 1, 'PPTQ');
        $total2 = $this->eventTotal($dateStart, $dateEnd, 1, 'PPTQ');
        $pct2 = getPct($pickedup2, $total2);

        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_EVENT_PICKUPDETAIL').'</th>';
        $str .= '<td>GPT: '.$pickedup1.' / '.$total1.' - '.$pct1.'%, ';
        $str .= 'PPTQ: '.$pickedup2.' / '.$total2.' - '.$pct2.'%</td>';
        $str .= '</tr>';

        if($param == 1){
            return $pct;
        }
        else
            return $str;
    }

    private function mostHJ($dateStart, $dateEnd){
        $events = M('eventjudge');
        $condition = Array('event_date' => Array('between', $dateStart.','.$dateEnd), 'is_hj' => '1', 'judge_id' => Array('neq', '-1'));

        $list = $events->where($condition)->field(array('fullname', 'count(is_hj)' => 'num'))->group('fullname')->order(Array('sum(is_hj)' => desc, 'convert(fullname using gbk)' => 'asc'))->select();
        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_HJ_RANKING').'</th>';
        $str .= '<td>';

        $rank = 1;
        $count = 0;
        $value = $list[0]['num'];
        foreach($list as $v){
            $count++;
            if($count > 10)
                break;

            if($v['num'] < $value){
                $rank = $count;
                $value = $v['num'];
            }

            $str .= $rank.'. '.$v['fullname'].', '.$value.C('STR_CALENDAR_EVENT_COUNT').'<br>';

        }
        $str .= '</td>';
        $str .= '</tr>';

        if($param == 1){
            return $pct;
        }
        else
            return $str;
    }

    private function userTotal($param){
        $users = M('user');
        $condition = Array('reserved_1' => 'normal');

        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_USER_TOTAL').'</th>';
        $str .= '<td>'.$users->where($condition)->count().'</td>';
        $str .= '</tr>';

        if($param == 1){
            return $users->where($condition)->count();
        }
        else
            return $str;
    }

    private function userPickupPct($param){
        $events = M('eventjudge');
        $condition = Array('judge_id' => Array('neq', '-1'));
        $pickedup = $events->where($condition)->group('fullname')->select();
        $pickedup = count($pickedup);
        $total = $this->userTotal(1);
        $pct = getPct($pickedup, $total);

        $str .= '<tr>';
        $str .= '<th>'.C('STR_STATS_USER_PICKUPPCT').'</th>';
        $str .= '<td>'.$pct.'%'.'</td>';
        $str .= '</tr>';

        if($param == 1){
            return $pct;
        }
        else
            return $str;
    }

    private function blankRow(){
        $str .= '<tr>';
        $str .= '<th colspan="2"></th>';
        $str .= '</tr>';

        return $str;
    }

}
?>