<?php
class Calendar
{
    private $year;
    private $month;
    private $weeks;
    private $city;

    function __construct($options = array()) {
        $this->year = date('Y');
        $this->month = date('m');
        $this->weeks = array(C('STR_SUNDAY'),C('STR_MONDAY'),C('STR_TUESDAY'),C('STR_WEDNESDAY'),C('STR_THURSDAY'),C('STR_FRIDAY'),C('STR_SATURDAY'));
        
        $vars = get_class_vars(get_class($this));

        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
    }
     
    public function display()
    {
        $str = $str.'<table class="calendar">';
        $str = $str.$this->showChangeDate();
        $str = $str.$this->showWeeks();
        $str = $str.$this->showDays($this->year,$this->month);
        $str = $str.'</table>';
        return $str;
    }
     
    private function showWeeks()
    {
        $str = '';

        $str = $str.'<tr>';
        foreach($this->weeks as $title)
        {
            $str = $str.'<th>'.$title.'</th>';
        }
        $str = $str.'</tr>';

        return $str;
    }
     
    private function showDays($year, $month)
    {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $starDay = date('w', $firstDay);
        $days = date('t', $firstDay);
 
        $str = '';

        $str = $str.'<tr class="days">';
        for ($i=0; $i<$starDay; $i++) {
            $str = $str.'<td>&nbsp;</td>';
        }
         
        for ($j=1; $j<=$days; $j++) {
            $i++;
            
            $str = $str.'<td class="eventday" style="background: url(\'/eventminder/Public/images/numbers/'.$j.'.gif\');">';
            $str = $str.$this->eventMinder($year,$month,$j,$this->city);
            $str = $str.'</td>';
            if ($i % 7 == 0) {
                $str = $str.'</tr><tr class="days">';
            }
        }

        $str = $str.'</tr>';

        return $str;
    }
     
    private function showChangeDate()
    {
        $url=U();

        $str = '';

        $str = $str.'<tr>';
        $str = $str.'<td><a href="'.$url.'/'.$this->preYearUrl($this->year,$this->month).'">'.'<<'.'</a></td>';
        $str = $str.'<td><a href="'.$url.'/'.$this->preMonthUrl($this->year,$this->month).'">'.'<'.'</a></td>';
        $str = $str.'<td colspan="3" style="text-align: center;"><form>';
         
        $str = $str.'<select name="year" onchange="window.location=\''.$url.'/year/\'+this.options[selectedIndex].value+\'/month/'.$this->month.'\'">';
        for($ye=1970; $ye<=2038; $ye++) {
            $selected = ($ye == $this->year) ? 'selected' : '';
            $str = $str.'<option '.$selected.' value="'.$ye.'">'.$ye.'</option>';
        }
        $str = $str.'</select>';
        $str = $str.'<select name="month" onchange="window.location=\''.$url.'/year/'.$this->year.'/month/\'+this.options[selectedIndex].value+\'\'">';
        
        for($mo=1; $mo<=12; $mo++) {
            $selected = ($mo == $this->month) ? 'selected' : '';
            $str = $str.'<option '.$selected.' value="'.$mo.'">'.$mo.'</option>';
        }
        $str = $str.'</select>';        
        $str = $str.'</form></td>';        
        $str = $str.'<td style="text-align: right;"><a href="'.$url.'/'.$this->nextMonthUrl($this->year,$this->month).'">'.'>'.'</a></td>';
        $str = $str.'<td style="text-align: right;"><a href="'.$url.'/'.$this->nextYearUrl($this->year,$this->month).'">'.'>>'.'</a></td>';        
        $str = $str.'</tr>';

        return $str;
    }
     
    private function preYearUrl($year,$month)
    {
        $year = ($this->year <= 1970) ? 1970 : $year - 1 ;
         
        return 'year/'.$year.'/month/'.$month;
    }
     
    private function nextYearUrl($year,$month)
    {
        $year = ($year >= 2038)? 2038 : $year + 1;
         
        return 'year/'.$year.'/month/'.$month;
    }
     
    private function preMonthUrl($year,$month)
    {
        if ($month == 1) {
            $month = 12;
            $year = ($year <= 1970) ? 1970 : $year - 1 ;
        } else {
            $month--;
        }        
        
        return 'year/'.$year.'/month/'.$month;
    }
     
    private function nextMonthUrl($year,$month)
    {
        if ($month == 12) {
            $month = 1;
            $year = ($year >= 2038) ? 2038 : $year + 1;
        }else{
            $month++;
        }
        return 'year/'.$year.'/month/'.$month;
    }

    private function eventMinder($year,$month,$day,$city){
        $events = M('event');
        $cursor = $year;

        if((int)$month <= 9)
            $cursor = $cursor.'0'.(int)$month;
        else
            $cursor = $cursor.$month;

        if((int)$day <= 9)
            $cursor = $cursor.'0'.(int)$day;
        else
            $cursor = $cursor.$day;

        $condition['event_date'] = $cursor;

        if($city != NULL)
            $condition['city'] = $city;

        $result = $events->where($condition)->select();
        if(count($result) > 0)
            $str = '<a href="'.U('eventlist',array('date' => $cursor)).'" class="event">'.count($result).C('STR_CALENDAR_EVENT_COUNT').'</a>';
        else
            $str = '';
        return $str;

    }
}

?>
