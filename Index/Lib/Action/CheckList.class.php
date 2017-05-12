<?php

class CheckList
{
    private $id;


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
            $str = $str.'<table class="checklist">';
            $str = $str.'<tr><th>'.C('STR_REALNAME_SHORT').'</th>';
            $str = $str.'<td class="wide">'.lookupJudgeById($this->id);
            $str = $str.'</td><th>'.C('STR_COMMON_OPTION').'</td></tr>';
            $str = $str.$this->showPrimaryMentor();
            $str = $str.$this->showSanctionedEvents();
            $str = $str.$this->showTeamworkCompREL();
            $str = $str.$this->showReviews();
            $str = $str.$this->showEventReport();
            $str = $str.$this->showL2P();
            $str = $str.'</table>';
            return $str;
    }

    private function showPrimaryMentor() {
        $item = M('l2checklist');

        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_PRIMARY_MENTOR').'</th>';

        if($mentor = $item->where(Array('judge_id' => $this->id, 'item_type' => 'mentor'))->order('id desc')->find())
            $primarymentor = $mentor['item_subject'];
        else
            $primarymentor = C('STR_CHECKLIST_MENTOR_VACANT');

        $str = $str.'<td class="wide">'.$primarymentor.'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'mentor').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showSanctionedEvents() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_SANCTIONED_EVENTS').'</th>';
        $str = $str.'<td class="wide">'.$this->getSanctionedEvents().'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'event').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showTeamworkCompREL() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_TEAMWORK_COMP_REL').'</th>';
        $str = $str.'<td class="wide">'.$this->getTeamworkCompREL().'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'teamwork').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showReviews() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_REVIEW').'</th>';
        $str = $str.'<td class="wide">'.$this->getReviews().'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'review').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showEventReport() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_EVENT_REPORT').'</th>';
        $str = $str.'<td class="wide">'.$this->getEventReport().'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'report').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showL2P() {
        $str = '';
        $str = $str.'<tr><th>'.C('STR_CHECKLIST_L2P').'</th>';
        $str = $str.'<td class="wide">'.$this->getL2P().'</td>';
        $str = $str.'<th>'.$this->showAction($this->id, 'l2p').'</td>';
        $str = $str.'</tr>';
        return $str;
    }

    private function showAction($id, $type) {
        $str .= '<button onclick="window.location=\''.U('Panel/additem',Array('id' => $id, 'type' => $type)).'\'">'.C('STR_CHECKLIST_ADD_ITEM').'</button>';

        return $str;
    }

    private function getSanctionedEvents(){
        $event = M('eventjudge');
        $condition['judge_id'] = $this->id;
        $condition['event_date'] = Array('between', Array($this->getDatePrev6month(todayDate()), todayDate()));
        $result = $event->where($condition)->order('event_date desc')->limit('6')->select();

        $otherevent = M('l2checklist');
        $condition2['judge_id'] = $this->id;
        $condition2['item_type'] = 'event';
        $condition2['item_date'] = Array('between', Array($this->getDatePrev6month(todayDate()), todayDate()));
        $result2 = $otherevent->where($condition2)->order('item_date desc')->limit('6')->select();
        $i = 0;
        $str = '';
        foreach($result as $value){
            $str .= '<a href="'.U('Event/eventdetail', Array('id' => $value['id'])).'">'.$value['name'].', '.formatDate($value['event_date']).'</a><br />';
            $i++;
        }
        foreach($result2 as $value){
            if($i >= 6) break;
            $str .= $value['item_subject'].', '.formatDate($value['item_date']).'&nbsp;<a onclick="return confirm(\''.C('STR_CHECKLIST_CONFIRM_DELETE').'\')" href="'.U('Panel/deleteitem', Array('id' => $value['id'])).'">'.C('STR_CHECKLIST_DELETE_ITEM').'</a><br />';
        }
        if($i == 0)
            return C('STR_CHECKLIST_NO_EVENT');
        else
            return $str;
    }

    private function getTeamworkCompREL(){
        $event = new Model();
        $result = $event->query('select * from `evm_eventjudge` where judge_id='.$this->id.' and event_date between '.$this->getDatePrev6month(todayDate()).' and '.todayDate().' and id not in(select id from `evm_eventjudge` group by id having count(*)=1) order by event_date desc limit 3');

        $otherevent = M('l2checklist');
        $condition2['judge_id'] = $this->id;
        $condition2['item_type'] = 'teamwork';
        $condition2['item_date'] = Array('between', Array($this->getDatePrev6month(todayDate()), todayDate()));
        $result2 = $otherevent->where($condition2)->order('item_date desc')->limit('6')->select();
        $i = 0;
        $str = '';
        foreach($result as $value){
            $str .= '<a href="'.U('Event/eventdetail', Array('id' => $value['id'])).'">'.$value['name'].', '.formatDate($value['event_date']).'</a><br />';
            $i++;
        }
        foreach($result2 as $value){
            if($i >= 3) break;
            $str .= $value['item_subject'].', '.formatDate($value['item_date']).'&nbsp;<a onclick="return confirm(\''.C('STR_CHECKLIST_CONFIRM_DELETE').'\')" href="'.U('Panel/deleteitem', Array('id' => $value['id'])).'">'.C('STR_CHECKLIST_DELETE_ITEM').'</a><br />';
        }
        if($i == 0)
            return C('STR_CHECKLIST_NO_EVENT');
        else
            return $str;
    }


    private function getReviews(){
        $otherevent = M('l2checklist');
        $condition2['judge_id'] = $this->id;
        $condition2['item_type'] = 'review';
        $result2 = $otherevent->where($condition2)->order('item_date desc')->limit('3')->select();
        $i = 0;
        $str = '';
        foreach($result2 as $value){
            $str .= $value['item_subject'].', '.$value['item_body'].', '.formatDate($value['item_date']).'&nbsp;<a onclick="return confirm(\''.C('STR_CHECKLIST_CONFIRM_DELETE').'\')" href="'.U('Panel/deleteitem', Array('id' => $value['id'])).'">'.C('STR_CHECKLIST_DELETE_ITEM').'</a><br />';
            $i++;
        }
        if($i == 0)
            return C('STR_CHECKLIST_NO_REVIEW');
        else
            return $str;
    }

    private function getEventReport(){
        $otherevent = M('l2checklist');
        $condition2['judge_id'] = $this->id;
        $condition2['item_type'] = 'report';
        $result2 = $otherevent->where($condition2)->order('item_date desc')->limit('3')->select();
        $i = 0;
        $str = '';
        foreach($result2 as $value){
            $str .= '<a href="'.$value['item_body'].'">'.$value['item_subject'].'</a>'.', '.formatDate($value['item_date']).'&nbsp;<a onclick="return confirm(\''.C('STR_CHECKLIST_CONFIRM_DELETE').'\')" href="'.U('Panel/deleteitem', Array('id' => $value['id'])).'">'.C('STR_CHECKLIST_DELETE_ITEM').'</a><br />';
            $i++;
        }
        if($i == 0)
            return C('STR_CHECKLIST_NO_REPORT');
        else
            return $str;
    }

    private function getL2P(){
        $otherevent = M('l2checklist');
        $condition2['judge_id'] = $this->id;
        $condition2['item_type'] = 'l2p';
        $result2 = $otherevent->where($condition2)->order('item_date desc')->limit('3')->select();
        $i = 0;
        $str = '';
        foreach($result2 as $value){
            $str .= $value['item_subject'].', '.$value['item_body'].'%, '.formatDate($value['item_date']).'&nbsp;<a onclick="return confirm(\''.C('STR_CHECKLIST_CONFIRM_DELETE').'\')" href="'.U('Panel/deleteitem', Array('id' => $value['id'])).'">'.C('STR_CHECKLIST_DELETE_ITEM').'</a><br />';
            $i++;
        }
        if($i == 0)
            return C('STR_CHECKLIST_NO_L2P');
        else
            return $str;
    }

    private function getDatePrev6month($date){
        $datenow = strtotime(substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2).' 09:00:00');
        $date6m = $datenow - 183*86400;
        return date('Ymd',$date6m);
    }
}

?>