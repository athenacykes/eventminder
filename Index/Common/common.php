<?php
// for testing
function printArray($array){
	dump($array, 1, '<pre>', 0);
}

// Validate Length between minlength and maxlength
function validateLength($str, $minlength, $maxlength){
    if((strlen($str)>=$maxlength) || (strlen($str)<$minlength) )
        return false;
    else
        return true;
}

// Validate Length lesser than length
function validateLength2($str, $length){
    if((strlen($str)>=$length) || (strlen($str) == 0))
        return false;
    else
        return true;
}

// validate DCI number between 4 and 10
function validateDCI($str){
    if((strlen($str)>10) || (strlen($str)<4) || (!ctype_digit($str)) )
        return false;
    else
        return true;
}

// validate string match
function validateMatch($str1, $str2){
    if(!strcmp($str1,$str2))
        return true;
    else
        return false;
}

// validate if user exists
function validateExist($str, $type){
    $query = M('user');
    $condition[$type] = $str;
    $exist = $query->where($condition)->select();
        
    if(count($exist)>0)
        return true;
    else
        return false;
}

// validate if user exclusive exists
function validateExclusiveExist($str, $type, $id){
    $query = M('user');
    $condition[$type] = $str;
    $condition['id'] = Array('neq', $id);
    $exist = $query->where($condition)->select();
        
    if(count($exist)>0)
        return true;
    else
        return false;
}

// validate register question
function validateQuestion($id, $a){
    $query = M('regquestion');
    $condition['id'] = $id;
    $answer = $query->where($condition)->select();

    if(strcmp($answer[0][answer],$a))
        return false;
    else
        return true;
}

// validate formatted date
function validateDate($str){
    if((strlen($str) != 8) || (!ctype_digit($str)))
            return false;
    $year = (int)substr($str,0,4);
    $month = (int)substr($str,4,2);
    $day = (int)substr($str,6,2);
    if(($year<1970) || ($year>2038) || ($month < 1) || ($month > 12) || ($day < 1) || ($day > 31))
        return false;
    if(($month == 4) || ($month == 6) || ($month == 9) || ($month == 11)){
            if($day == 31)
                return false;
            }
    if($month == 2){
            if(($year % 4) == 0){
                if($day > 29)
                    return false;
            }
            else{
                if($day > 28)
                    return false;
            }
        }
    return true;

}

// Lookup Judge ID
function lookupJudge($id){
	$data = M('user');
	if((int)$id>=1){
		$condition['id'] = (int)$id;
		$judge = $data->where($condition)->find();
		if(count($judge) > 0){
			return $judge['id'];
		}
		else{
			return false;
		}
	}
	else return false;
}


// Lookup Judge Fullname by ID
function lookupJudgeById($judge_id){
	$data = M('user');
	if((int)$judge_id == -1){
		return C('STR_EVENTLIST_NOJUDGE');
	}
	elseif((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['fullname'];
	}
	else
		return false;
}

// Lookup user admin level
function lookupAdmin($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['role_admin'];
	}
	else return false;
}

// Lookup judge level
function lookupLevel($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['level'];
	}
	else return false;
}

// Lookup user activation status
function lookupStatus($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['reserved_1'];
	}
	else return false;
}

// Lookup judge DCI
function lookupDCI($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['dci'];
	}
	else return false;
}

// Lookup Judge City
function lookupJudgeCity($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['city'];
	}
	else return false;
}

// Lookup judge last login time
function lookupLastLogin($judge_id){
	$data = M('user');
	if((int)$judge_id>=1){
		$condition['id'] = (int)$judge_id;
		$judge = $data->where($condition)->find();
		return $judge['lastlogin_time'];
	}
	else return false;
}

// Lookup event HJ
function lookupHJ($event_id){
	$data = M('eventstaff');
	if((int)$event_id>=1){
		$condition['event_id'] = (int)$event_id;
		$condition['is_hj'] = 1;
		$judge = $data->where($condition)->find();
		return $judge['judge_id'];
	}
	else return false;
}

// Lookup Event ID
function lookupEvent($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$event = $data->where($condition)->find();
		if(count($event) > 0){
			return $event['id'];
		}
		else{
			return false;
		}
	}
	else return false;
}

function lookupEventName($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$event = $data->where($condition)->find();
		if(count($event) > 0){
			return $event['name'];
		}
		else{
			return false;
		}
	}
	else return false;
}

// Return past event grace period
function gracePeriod($date){
	$grace = strtotime($date);
	$grace = $grace + 1300000;
	return date('Ymd', $grace);
}

// Lookup Event Level Requirement
function lookupLevelReq($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$type = $data->where($condition)->find();
		if($type['type'] == 'GPT'){
			$reqlevel = 1;
		}elseif($type['type'] == 'PPTQ'){
			$reqlevel = 2;
		}
		elseif($type['type'] == 'RPTQ'){
			$reqlevel = 3;
		}else{
			$reqlevel = 0;
		}
		return $reqlevel;
	}
	else return false;
}

// Lookup Event Date by event id
function lookupEventDate($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$eventdate = $data->where($condition)->find();
		return $eventdate['event_date'];
	}
	else return false;
}

// Lookup Event Champion by event id
function lookupEventChampion($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$eventdate = $data->where($condition)->find();
		return $eventdate['reserved_1'];
	}
	else return false;
}

// Lookup event type by event id
function lookupEventType($event_id){
	$data = M('event');
	if((int)$event_id>=1){
		$condition['id'] = (int)$event_id;
		$eventdate = $data->where($condition)->find();
		return $eventdate['type'];
	}
	else return false;
}

// Lookup event id by event name and date
function lookupEventByName($name, $date, $city){
	$data = M('event');
	if(strlen($name)>0 && validateDate($date)){
		$condition['event_date'] = $date;
		$condition['name'] = Array('eq', $name);
		if(strlen($city)>0)
			$condition['city'] = Array('eq', $city);
		if($event = $data->where($condition)->find()){
			return $event['id'];
		}
		else
			return false;
	}
}

// lookup announcement
function lookupAnnouncement(){
	$data = M('announcement');
	if($a = $data->where('id=1')->find()){
		$arr['title'] = formatDate($a['last_modify_date']).' - '.lookupJudgeById($a['last_modify_judge']);
		$arr['content'] = $a['textbody'];

		return $arr;
	}
	else
		return false;
}

// lookup if custom event
function isCustomEvent($event_id){
	$data = M('customevent');
	if((int)$event_id>=1){
		$condition = Array('event_id' => $event_id);
		if($event = $data->where($condition)->find()){
			return true;
		}
		else
			return false;
	}
}

function lookupCustomEventCreator($event_id){
	$data = M('customevent');
	if((int)$event_id>=1){
		$condition = Array('event_id' => $event_id);
		if($event = $data->where($condition)->find()){
			return $event['judge_id'];
		}
		else
			return false;
	}
}

// lookup if custom event's creator
function isCustomEventCreator($event_id, $judge_id){
	$data = M('customevent');
	if((int)$event_id>=1 && (int)$judge_id>=1){
		$condition = Array('event_id' => $event_id, 'judge_id' => $judge_id);
		if($data->where($condition)->find()){
			return true;
		}
		else
			return false;
	}
}

// lookup if exceeds custom event limit
function isExceedCustomLimit($judge_id){
	$data = M('customevent');
	if((int)$judge_id>=1){
		$monthly_limit = 3;

		$year = substr(todayDate(),0,4);
		$month = substr(todayDate(),4,2);
		$current_month = $year.$month;

		$condition = Array('judge_id' => $judge_id, 'create_date' => Array('like', $current_month.'%'));
		if(count($data->where($condition)->select()) >= $monthly_limit && (int)lookupAdmin($judge_id) == 0){
			return true;
		}
		else
			return false;
	}
}

// Format date into YYYYmmdd
function formatDate($date){
	if(validateDate($date)){
		$year = substr($date,0,4);
		$month = substr($date,4,2);
		$day = substr($date,6,2);
		return $year.C('STR_YEAR').$month.C('STR_MONTH').$day.C('STR_DAY');
	}
	else
		return C('STR_FORMAT_DATE_FAIL');
}

// Format Pickup Judge
function formatPickupJudge($name){
	if($name != NULL){
		return $name;
	}
	else
		return C('STR_EVENTLIST_NOJUDGE');
}

// Format Judge Level string
function formatLevel($level){
	if((int)$level == 0){
		return C('STR_UNCERTIFIED');
	}
	elseif((int)$level == 1){
		return C('STR_LEVEL_1');
	}
	elseif((int)$level == 2){
		return C('STR_LEVEL_2');
	}
	elseif((int)$level == 3){
		return C('STR_LEVEL_3');
	}
	else return false;
}

// Format Event Type string
function formatEventType($type){
	if($type == C('STR_GPT')){
		return C('STR_GPT_LONG');
	}
	elseif($type == C('STR_PPTQ')){
		return C('STR_PPTQ_LONG');
	}
	elseif($type == C('STR_RPTQ')){
		return C('STR_RPTQ_LONG');
	}
	elseif($type == C('STR_OTHER')){
		return C('STR_OTHER_LONG');
	}
	else return false;
}

// Format Event format string
function formatEventFormat($format){
	if($format == C('STR_STANDARD')){
		return C('STR_STANDARD_LONG');
	}
	elseif($format == C('STR_MODERN')){
		return C('STR_MODERN_LONG');
	}
	elseif($format == C('STR_LEGACY')){
		return C('STR_LEGACY_LONG');
	}
	elseif($format == C('STR_LIMITED') || $format == C('STR_SEALED')){
		return C('STR_LIMITED_LONG');
	}
	elseif($format == C('STR_MIXED')){
		return C('STR_MIXED_LONG');
	}
	elseif($format == C('STR_OTHER')){
		return C('STR_OTHER_LONG');
	}
	else return false;
}

// Get top 20 date list for future events
function getFilterDate($top){
	if(!$top)
		$top=20;

	$data = M('event');
	$date_set = Array();
	$condition = Array('event_date' => Array('egt', todayDate()));
	$result = $data->where($condition)->group('event_date')->limit($top)->order('event_date asc')->select();
	foreach($result as $value)
		array_push($date_set, $value['event_date']);

	return $date_set;
}

// Get top 20 date list for past events
function getFilterDatePast($top){
	if(!$top)
		$top=20;

	$data = M('event');
	$date_set = Array();
	$condition = Array('event_date' => Array('elt', todayDate()));
	$result = $data->where($condition)->group('event_date')->limit($top)->order('event_date desc')->select();
	foreach($result as $value)
		array_push($date_set, $value['event_date']);

	return $date_set;
}

// Get event city filter
function getFilterCity(){
	$data = M('event');
	$city_set = Array();
	$result = $data->group('city')->order(Array('convert(city using gbk)' => 'asc'))->select();
	foreach($result as $value)
		array_push($city_set, $value['city']);

	return $city_set;
}

function getFilterCityStore(){
	$data = M('store');
	$city_set = Array();
	$result = $data->group('city')->order(Array('convert(city using gbk)' => 'asc'))->select();
	foreach($result as $value)
		array_push($city_set, $value['city']);

	return $city_set;
}

function getFilterRegion(){
	$data = M('georegion');
	$region_set = Array();
	$result = $data->group('region')->order('id asc')->select();
	foreach($result as $value)
		array_push($region_set, $value);

	return $region_set;
}

// Get event type filter
function getFilterType(){
	$data = M('event');
	$type_set = Array();
	$result = $data->group('type')->order('type asc')->select();
	foreach($result as $value)
		array_push($type_set, $value['type']);

	return $type_set;
}

// get user city filter 
function getFilterUserCity(){
	$data = M('user');
	$city_set = Array();
	$result = $data->where(Array('reserved_1' => 'normal'))->group('city')->order(Array('convert(city using gbk)' => 'asc'))->select();
	foreach($result as $value)
		array_push($city_set, $value['city']);

	return $city_set;
}

// get user city filter (pending)
function getFilterUserCityPending(){
	$data = M('user');
	$city_set = Array();
	$result = $data->where(Array('reserved_1' => 'pending'))->group('city')->order(Array('convert(city using gbk)' => 'asc'))->select();
	foreach($result as $value)
		array_push($city_set, $value['city']);

	return $city_set;
}

// get user level filter
function getFilterLevel(){
	$data = M('user');
	$level_set = Array();
	$result = $data->where(Array('reserved_1' => 'normal'))->group('level')->order('level desc')->select();
	foreach($result as $value)
		array_push($level_set, $value['level']);

	return $level_set;
}

// get user level filter (pending)
function getFilterLevelPending(){
	$data = M('user');
	$level_set = Array();
	$result = $data->where(Array('reserved_1' => 'pending'))->group('level')->order('level desc')->select();
	foreach($result as $value)
		array_push($level_set, $value['level']);

	return $level_set;
}

// get judge filter
function getFilterJudge(){
	$data = M('user');
	$judge_set = Array();
	array_push($judge_set, Array('id' => -1, 'text' => C('STR_EVENTLIST_NOJUDGE')));
	$result = $data->where(Array('reserved_1'=>'normal'))->order('level desc, city asc, fullname asc')->select();
	foreach($result as $value){
		$str = $value['fullname'].','.$value['city'].','.$value['level'].C('STR_LEVEL_SHORT');
		$pack = Array('id' => $value['id'], 'text' => $str);
		array_push($judge_set, $pack);
	}

	return $judge_set;
}

// get season filter (temporary)
function getFilterSeason(){
	$season_set = Array();

	array_push($season_set, 'Dublin');
	array_push($season_set, 'Honululu');
	array_push($season_set, 'Sydney');
	array_push($season_set, 'Madrid');
	array_push($season_set, 'Atlanta');
	array_push($season_set, 'Milwaukee');
	array_push($season_set, 'Vancouver');

	return $season_set;
}

// get today date
function todayDate(){
	$year = date('Y');
    $month = date('m');
    $day = date('d');

    return $year.$month.$day;
}

// reduce a parameter
function reduceParam($param,$key){
    $params = $param;
    unset($params[$key]);

    return $params;
}

// export excel (placeholder)
function exportExcel($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $_SESSION['loginAccount'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8   
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
          }             
        }  
        
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output'); 
        exit;   
    }

// import excel
function importExcel($file){ 
        if(!file_exists($file)){ 
            return array("error"=>0,'message'=>'file not found!');
        }
        Vendor("PHPExcel.PHPExcel.IOFactory"); 
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);
         try{
            $PHPReader = $objReader->load($file);
            
         }catch(Exception $e){}
        if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach($allWorksheets as $objWorksheet){
            $sheetname=$objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow();//how many rows
            $highestColumn = $objWorksheet->getHighestColumn();//how many columns
            $allColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname; 
            $array[$i]["Cols"] = $allColumn; 
            $array[$i]["Rows"] = $allRow; 
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){ 
                $row = array(); 
                for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;                
                    $cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
                    $bfCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
                    $col = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col.$currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();
                    if(substr($value,0,1)=='='){
                        return array("error"=>0,'message'=>'can not use the formula!');
                        exit;
                    }
                    // if($cell->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC){
                    //     $cellstyleformat=$cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat();
                    //     $formatcode=$cellstyleformat->getFormatCode();
                    //     if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                    //         $value=gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));
                    //     }else{
                    //         $value=PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
                    //     }                
                    // }
                    if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
                        $temp = $value;
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
                        $value=$arr[$currentRow-1][$currentColumn];
                    }elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
                        $value=$temp;
                    }
                    $row[$currentColumn] = $value; 
                } 
                $arr[$currentRow] = $row; 
            } 
            $array[$i]["Content"] = $arr; 
            $i++;
        } 
        spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
        unset($objWorksheet); 
        unset($PHPReader); 
        unset($PHPExcel); 
        unlink($file); 
        return array("error"=>1,"data"=>$array); 
    }

// convert excel time
function excelTime($date) {  
    if(function_exists('GregorianToJD')){  
        if (is_numeric( $date )) {  
        $jd = GregorianToJD( 1, 1, 1970 );  
        $gregorian = JDToGregorian( $jd + intval ( $date ) - 25569 );  
        $date = explode( '/', $gregorian );  
        $date_str = str_pad( $date [2], 4, '0', STR_PAD_LEFT )  
        . str_pad( $date [0], 2, '0', STR_PAD_LEFT )  
        . str_pad( $date [1], 2, '0', STR_PAD_LEFT );  
        return $date_str;  
        }  
    }else{  
        $date=$date>25568?$date+1:25569;  
        /*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/  
        $ofs=(70 * 365 + 17+2) * 86400;  
        $date = date("Ymd",($date * 86400) - $ofs);  
    }  
  return $date;  
}  

//v0.6.0
// check city in region
function lookupRegionByCity($city){
	$data = M('georegion');
	if(strlen($city)>0){
		$condition = Array('citylist' => Array('like', '%'.$city.'%'));
		if($region = $data->where($condition)->find()){
			return $region['id'];
		}
		else
			return '0';
	}
	return false;
}

function lookupRegionById($regionid){
	$data = M('georegion');
	if(strlen($regionid)>0){
		$condition = Array('id' => $regionid);
		if($region = $data->where($condition)->find()){
			return $region['region'];
		}
		else
			return C('STR_UNCATEGORIZED_CITY');
	}
	return false;
}

function lookupStoreByName($store, $city){
	$data = M('store');
	if(strlen($store)>0){
		$condition['name'] = Array('eq', $store);
		if(strlen($city)>0)
			$condition['city'] = Array('eq', $city);
		if($s = $data->where($condition)->find()){
			return $s['id'];
		}
		else
			return 0;
	}
	return false;
}

function lookupStoreById($storeid){
	$data = M('store');
	if(strlen($storeid)>0){
		$condition = Array('id' => $storeid);
		if($s = $data->where($condition)->find()){
			return $s['name'];
		}
		else
			return C('STR_UNRECOGNIZED_STORE');
	}
	return false;
}

function resetRegion(){
	$events = M('event');
	$regions = M('georegion');

	$allevent = $events->where('1=1')->select();
	foreach($allevent as $value){
		$condition = Array('id' => $value['id']);
		$data['georegion'] = lookupRegionByCity($value['city']);
		echo '<p>updated id='.$value['id'].',name='.$value['name'].'</p>';
		$events->where($condition)->save($data);
	}

}

function logEvent($userid, $optype, $opdetail){
	$logbase = M('adminlog');

	$log['user_id'] = $userid;
	$log['user_name'] = lookupJudgeById($userid);
	$log['op_type'] = $optype;
	$log['op_detail'] = $opdetail;
	$log['op_ip'] = get_client_ip();
	$log['op_time'] = date('Y-m-d H:i:s', time());

	$logbase->add($log);
}

function getPct($dividee, $divider){
	$pct = 0;

    if($divider != 0)
        $pct = $dividee / $divider;

    $pct = number_format(100 * $pct, 2, '.', '');
    return $pct;

}

?>