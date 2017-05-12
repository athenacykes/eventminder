<?php
class EventAction extends Action {
    Public function _initialize() {
        if(!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
            $this->redirect('Index/login');
        }
        if(time() - (int)lookupLastLogin($_SESSION['id']) >= 604800){
            session_destroy();
            $this->redirect('Index/index');
        }
    }

    public function logout(){
        logEvent($_SESSION['id'], 'logout', $_SERVER['HTTP_USER_AGENT']);
        session_destroy();
        $this->redirect('Index/index');
    }

    public function index(){
        import("@.Action.Calendar");
        $params = array();
        if (isset($_GET['year']) && isset($_GET['month'])) {
            $params = array(
                'year' => I('year'),
                'month' => I('month'),
                'city' => I('city'),
        );
        }

        $cal = new Calendar($params);
        $this->assign('calendar',$cal->display());
        $this->assign('announcement', lookupAnnouncement());
        $this->display();
    }

    public function add(){
            $this->display();
    }

    public function modify(){
        if(lookupEvent(I('event')) && ((int)lookupAdmin($_SESSION['id']) >= 1 || isCustomEventCreator(I('event'), $_SESSION['id']))){
            $event_id = I('event');
            $event_object = M('event');

            $event = $event_object->where(Array('id' => $event_id))->find();
            $this->assign('event',$event);
            $this->assign('selecttype',Array($event['type'] => 'selected'));
            $this->assign('selectformat',Array($event['format'] => 'selected'));

            $this->display();
        }
        else{
            U('/Event/index', '', '', 1);
        }

    }

    public function addevent(){
        if(empty($_POST))
            U('/Event/add', '', '', 1);

        if(!validateLength2(I('eventname'),100))
            $message = C('STR_EVENTADD_ERROR_NAME');
        else if (!validateLength2(I('eventcity'),20))
            $message = C('STR_EVENTADD_ERROR_CITY');
        else if (!validateLength2(I('eventlocation'),100))
            $message = C('STR_EVENTADD_ERROR_LOCATION');
        else if (!validateDate(I('eventdate')))
            $message = C('STR_EVENTADD_ERROR_DATE');
        else if (isExceedCustomLimit($_SESSION['id']))
            $message = C('STR_EVENTADD_ERROR_EXCEED');
        else
        {
            $table = M('event');
            $data['name'] = I('eventname');
            $data['city'] = I('eventcity');
            $s = explode(' ',I('eventname'),3);
            if($s[0] == 'PPTQ' || $s[0] == 'GPT' || $s[0] == 'RPTQ'){
                $data['associated_city'] = $s[1];
                $data['store'] = lookupStoreByName($s[2], $data['city']);
            }
            $data['georegion'] = lookupRegionByCity(I('eventcity'));
            $data['location'] = I('eventlocation');
            $data['event_date'] = I('eventdate');
            $data['month'] = substr(I('eventdate'),0,6);
            $data['type'] = I('eventtype');
            $data['format'] = I('eventformat');
            $data['description'] = I('eventdescription');
            $data['reserved_2'] = 'custom';

            if($id = $table->data($data)->add()){

                $table_staff = M('eventstaff');
                $data_staff['event_id'] = $id;
                $data_staff['judge_id'] = -1;
                $data_staff['is_hj'] = 1;
                $data_staff['pickup_date'] = '19700101';
                $table_staff->data($data_staff)->add();

                $table_custom = M('customevent');
                $data_custom['event_id'] = $id;
                $data_custom['judge_id'] = $_SESSION['id'];
                $data_custom['create_date'] = todayDate();
                $table_custom->data($data_custom)->add();

                logEvent($_SESSION['id'], 'addevent', $data['name'].','.$data['event_date']);
                $message = C('STR_EVENTADD_SUCCESS');
            }
                
            else
                $message = C('STR_DB_FAILURE');
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function modifyevent(){
        if(empty($_POST))
            U('/Event/eventlist', '', '', 1);
        if((int)lookupAdmin($_SESSION['id']) < 1 && !isCustomEventCreator(I('eventid'), $_SESSION['id']))
            U('/Event/eventlist', '', '', 1);
        if(!lookupEvent(I('eventid')))
            U('/Event/eventlist', '', '', 1);

        if(!validateLength2(I('eventname'),100))
            $message = C('STR_EVENTADD_ERROR_NAME');
        else if (!validateLength2(I('eventcity'),20))
            $message = C('STR_EVENTADD_ERROR_CITY');
        else if (!validateLength2(I('eventlocation'),100))
            $message = C('STR_EVENTADD_ERROR_LOCATION');
        else if (!validateDate(I('eventdate')))
            $message = C('STR_EVENTADD_ERROR_DATE');
        else
        {
            $table = M('event');

            $data['name'] = I('eventname');
            $data['city'] = I('eventcity');
            $s = explode(' ',I('eventname'),3);
            if($s[0] == 'PPTQ' || $s[0] == 'GPT' || $s[0] == 'RPTQ'){
                $data['associated_city'] = $s[1];
                $data['store'] = lookupStoreByName($s[2], $data['city']);
            }
            $data['georegion'] = lookupRegionByCity(I('eventcity'));
            $data['georegion'] = lookupRegionByCity(I('eventcity'));
            $data['location'] = I('eventlocation');
            $data['event_date'] = I('eventdate');
            $data['month'] = substr(I('eventdate'),0,6);
            $data['type'] = I('eventtype');
            $data['format'] = I('eventformat');
            $data['description'] = I('eventdescription');

            if($table->where(Array('id' => I('eventid')))->save($data)){
                logEvent($_SESSION['id'], 'modifyevent', I('eventid').','.$data['name'].','.$data['event_date']);
                $message = C('STR_EVENTMODIFY_SUCCESS');
            }
                
            else
                $message = C('STR_DB_FAILURE');
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function championlist() {
        import("@.Action.Champions");
        $params = Array();

        if(isset($_GET['season'])){
            $params = array_merge($params, Array('season' => I('season')));
            $params['season'] = str_replace('NO', '#', $params['season']);
        }else{
            $params = array_merge($params, Array('season' => getFilterSeason()[0]));
        }
        
        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));

        $listing = new Champions($params);

        $this->assign('listing',$listing->display());
        $this->display();
    }

    public function championfilter(){
        $filterseason = I('filterseason');
        $origseason = I('origseason');

        $param = Array();
        if($filterseason!='default'){
            $param['season'] = $filterseason;
        } else {
            $param['season'] = $origseason;
        }

        $param['season'] = str_replace('#', 'NO', $param['season']);

        U('/Event/championlist',$param,'',1);
    }

    public function champion(){
        if(lookupHJ(I('event')) == $_SESSION['id'] && lookupEventType(I('event')) == 'PPTQ'){
            $table = M('event');
            $event = $table->where(Array('id' => I('event')))->find();
            $this->assign('event',$event);
            $this->display();
        }
        else{
            U('/Event/index', '', '', 1);
        }

    }

    public function addchampion(){
        if(empty($_POST) || !lookupHJ(I('eventid')) == $_SESSION['id'] || !lookupEventType(I('eventid')) == 'PPTQ')
            U('/Event/eventlist', Array('past' => '1'), '', 1);

        if(!validateLength(I('championname'),2,30))
            $message = C('STR_CHAMPION_ERROR_NAME');
        elseif (!validateDCI(I('championdci')))
            $message = C('STR_CHAMPION_ERROR_DCI');
        else
        {
            $table = M('event');
            $condition = Array('id' => I('eventid'));
            $str = str_replace(',','',I('championname')).','.I('championdci');
            
            $data['reserved_1'] = $str;

            if($table->where($condition)->save($data)){
                logEvent($_SESSION['id'], 'addchampion', lookupEventName(I('eventid')).','.lookupEventDate(I('eventid')));
                $message = C('STR_CHAMPION_SUCCESS');
            }             
            else
                $message = C('STR_DB_FAILURE');
        }
        $this->assign('event_id',I('eventid'));
        $this->assign('message',$message);
        $this->display();
    }

    public function eventlist() {
        import("@.Action.Listing");
        $params = Array();
        if(isset($_GET['date']))
            $params = array_merge($params, Array('date' => I('date')));
        if(isset($_GET['city']))
            $params = array_merge($params,Array('city' => I('city')));
        if(isset($_GET['judge']))
            $params = array_merge($params,Array('judge' => I('judge')));
        if(isset($_GET['type']))
            $params = array_merge($params,Array('type' => I('type')));
        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));
        if(isset($_GET['region']))
            $params = array_merge($params,Array('region' => I('region')));
        if(isset($_GET['store']))
            $params = array_merge($params,Array('store' => I('store')));
        if(isset($_GET['past'])){
            if(I('past') == -1){
                $this->assign('pastevent',C('STR_ALL_EVENT'));
            }else{
                $this->assign('pastevent',C('STR_PAST_EVENT'));
            }

            $params = array_merge($params,Array('past' => I('past')));
        }   

        $params = array_merge($params, Array('isLogin' => '1'));

        $listing = new Listing($params);
        
        $this->assign('listing',$listing->display());
        $this->display();
    }

    public function eventdetail() {
        import("@.Action.Details");
        if(isset($_GET['id']))
            $params = Array("id" => I('id'));
        else
            $this->redirect('index');

        $params = array_merge($params, Array('isLogin' => '1', 'current' => $_SESSION['id']));

        $details = new Details($params);
        $this->assign('details',$details->display());
        $this->display();
    }

    public function storedetail() {
        if(isset($_GET['store']))
            U('Store/storedetail', Array('store' => I('store')), '', 1);
        else
            $this->redirect('index');
    }

    public function filter(){
        $filterdate = I('filterdate');
        $filtercity = I('filtercity');
        $filterjudge = I('filterjudge');
        $filtertype = I('filtertype');
        $filterregion = I('filterregion');
        $origdate = I('origdate');
        $origstore = I('origstore');
        $origcity = I('origcity');
        $origjudge = I('origjudge');
        $origtype = I('origtype');
        $origregion = I('origregion');
        $origpast = I('origpast');

        $param = Array();
        if($filterdate!='default' && validateDate($filterdate)){
            $param['date'] = $filterdate;
        } else {
            $param['date'] = $origdate;
        }
        if($filtercity!='default'){
            $param['city'] = $filtercity;
        } else {
            $param['city'] = $origcity;
        }
        if($filterjudge!='default'){
            $param['judge'] = $filterjudge;
        } else {
            $param['judge'] = $origjudge;
        }
        if($filtertype!='default'){
            $param['type'] = $filtertype;
        } else {
            $param['type'] = $origtype;
        }
        if($filterregion!='default'){
            $param['region'] = $filterregion;
        } else {
            $param['region'] = $origregion;
        }
        $param['past'] = $origpast;
        $param['store'] = $origstore;

        U('/Event/eventlist',$param,'',1);
    }

    public function action() {
        $action = I('action');
        $event_id = I('eventid');

        if($action == 'withdraw'){
            $data = M('eventjudge');
            $condition = Array('id' => $event_id, 'judge_id' => $_SESSION['id']);
            $status = $data->where($condition)->find();
            if(count($status)>0 && (int)todayDate()<=(int)gracePeriod(lookupEventDate($event_id))){
                if($status['is_hj'] == '1'){
                    $update = M('eventstaff');
                    $update->where(Array('event_id' => $event_id))->delete();
                    $fix['event_id'] = $event_id;
                    $fix['judge_id'] = -1;
                    $fix['is_hj'] = 1;
                    $fix['pickup_date'] = todayDate();
                    $update->add($fix);

                    logEvent($_SESSION['id'], 'withdraw', lookupEventName($event_id).','.lookupEventDate($event_id));
                }
                else{
                    $update = M('eventstaff');
                    $update->where(Array('event_id' => $event_id, 'judge_id' => $_SESSION['id'], 'is_hj' => '0'))->delete();
                    logEvent($_SESSION['id'], 'withdraw', lookupEventName($event_id).','.lookupEventDate($event_id));
                }
            }

        }
        elseif($action == 'followup'){
            if(lookupHJ($event_id) != -1){
                $update = M('eventstaff');
                $status = $update->where(Array('event_id' => $event_id, 'judge_id' => $_SESSION['id']))->find();
                if(count($status) == 0 && (int)todayDate()<=(int)gracePeriod(lookupEventDate($event_id))){
                    $fix['event_id'] = $event_id;
                    $fix['judge_id'] = $_SESSION['id'];
                    $fix['is_hj'] = 0;
                    $fix['pickup_date'] = todayDate();
                    $update->add($fix);

                    logEvent($_SESSION['id'], 'followup', lookupEventName($event_id).','.lookupEventDate($event_id));
                }
            }

        }elseif($action == 'pickup'){
            if(lookupHJ($event_id) == -1 && lookupLevel($_SESSION['id'])>=lookupLevelReq($event_id) && (int)todayDate()<=(int)gracePeriod(lookupEventDate($event_id))){

                $update = M('eventstaff');
                $update->where(Array('event_id' => $event_id))->delete();
                $fix['event_id'] = $event_id;
                $fix['judge_id'] = $_SESSION['id'];
                $fix['is_hj'] = 1;
                $fix['pickup_date'] = todayDate();
                $update->add($fix);
                
                logEvent($_SESSION['id'], 'pickup', lookupEventName($event_id).','.lookupEventDate($event_id));
            }
            
        }
        elseif($action == 'champion'){
            if(lookupHJ($event_id) == $_SESSION['id'] && lookupEventType($event_id) == 'PPTQ'){
                U('/Event/champion', Array('event' => $event_id), '', 1);
            }
        }
        elseif($action == 'admin_modify'){
            if((int)lookupAdmin($_SESSION['id']) >= 1 || isCustomEventCreator($event_id, $_SESSION['id'])){
                U('/Event/modify', Array('event' => $event_id), '', 1);
            }
        }
        elseif($action == 'admin_delete'){
            if((int)lookupAdmin($_SESSION['id']) >= 1 || isCustomEventCreator($event_id, $_SESSION['id'])){
                $dest = M('event');
                $deletedevent = M('deletedevent');
                $e = $dest->where(Array('id' => $event_id))->find();
                $e['original_id'] = $e['id'];
                $e = reduceParam($e,'id');
                logEvent($_SESSION['id'], 'delete_event', lookupEventName($event_id).','.lookupEventDate($event_id));
                $deletedevent->add($e);

                $update = M('eventstaff');
                $update->where(Array('event_id' => $event_id))->delete();
                $dest->where(Array('id' => $event_id))->delete();


            }
        
        }elseif($action == 'admin_reset'){
            if((int)lookupAdmin($_SESSION['id']) >= 1 || isCustomEventCreator($event_id, $_SESSION['id'])){
                $update = M('eventstaff');
                $update->where(Array('event_id' => $event_id))->delete();
                $fix['event_id'] = $event_id;
                $fix['judge_id'] = -1;
                $fix['is_hj'] = 1;
                $fix['pickup_date'] = todayDate();
                $update->add($fix);
                logEvent($_SESSION['id'], 'reset_event', lookupEventName($event_id).','.lookupEventDate($event_id));
            }
            
        }else{

        }

        $this->redirect('/Event/eventdetail', Array('id' => $event_id));
    }


    public function import(){
        if((int)lookupAdmin($_SESSION['id']) >= 2){
            $this->display();
        }
        else{
            U('/Event/index', '', '', 1);
        }
    }

    public function importaction(){
        if(isset($_FILES["import"]) && ($_FILES["import"]["error"] == 0) && (int)lookupAdmin($_SESSION['id']) >= 2){
            import('ORG.Net.UploadFile');
            $upload = new UploadFile();
            $upload->savePath =  './Upload/';
            $info = $upload->uploadOne($_FILES["import"]);

            $temptable = M('tempevent');
            $temptable->where('1=1')->delete();
            //result = importExcel($_FILES["import"]["tmp_name"]);
            $result = importExcel(str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']).'Upload/'.$info[0]['savename']);

            if($result["error"] == 1){
                $add_event = Array();
                $delete_event = Array();
                $excel_data = $result["data"][0]["Content"];
                foreach($excel_data as $k=>$v){
                    $name = $this->importEventName($v[0], $v[9]).' '.$this->importStoreName($v[2]);
                    $s = explode(' ',$name,3);
                    $associated_city = $s[1];
                    $city = str_replace(C('STR_TRUNCATE_CITY'),'',$v[3]);
                    $store = lookupStoreByName($s[2], $city);
                    //seasonal replace
                    $name = str_replace('#2of2016', 'Madrid', $name);
                    $associated_city = str_replace('#2of2016', 'Madrid', $associated_city);
                    $georegion = lookupRegionByCity($city);
                    $location = $v[10];
                    $event_date = excelTime($v[1]);
                    $month = substr($event_date,0,6);
                    $type = $this->importEventType($v[9]);
                    $format = $v[4];
                    $description = $v[11].' '.$v[12];
                    $op_log = todayDate().','.lookupJudgeByID($_SESSION['id']);

                    $a = Array(
                            'name' => $name,
                            'city' => $city,
                            'associated_city' => $associated_city,
                            'store' => $store,
                            'georegion' => $georegion,
                            'location' => $location,
                            'event_date' => $event_date,
                            'month' => $month,
                            'type' => $type,
                            'format' => $format,
                            'description' => $description,
                            'reserved_2' => $op_log
                            );
                    if($v[7] == 'PND'){
                        array_push($add_event, $a);
                    }elseif($v[7] == 'CNC'){
                        array_push($delete_event, $a);
                    }
                }
                $str = '<table class="eventlist">';
                $str .= '<tr><th colspan="3">'.C('STR_IMPORT_DELETE_LIST').'</th></tr>';

                foreach($delete_event as $value){
                    if(lookupEventByName($value['name'], $value['event_date'])){
                        foreach($value as $ke => $va)
                            $temp[$ke] = $va;
                        $temp['action'] = 'delete';
                        $temp['original_id'] = lookupEventByName($value['name'], $value['event_date'], $value['city']);
                        $str .= '<tr><td class="wide">'.$temp['name'].'</td>';
                        $str .= '<td class="city">'.$temp['city'].'</td>';
                        $str .= '<td class="thin">'.$temp['event_date'].'</td></tr>';
                        $temptable->add($temp);
                        $temp = Array();
                    }
                }
                $str .= '<tr><th colspan="3">'.C('STR_IMPORT_ADD_LIST').'</th></tr>';

                foreach($add_event as $value){
                    if(lookupEventByName($value['name'], $value['event_date'], $value['city'])){

                    }else
                    {
                        foreach($value as $ke => $va)
                            $temp[$ke] = $va;
                        $temp['action'] = 'add';
                        $str .= '<tr><td class="wide">'.$temp['name'].'</td>';
                        $str .= '<td class="city">'.$temp['city'].'</td>';
                        $str .= '<td class="thin">'.$temp['event_date'].'</td></tr>';
                        $temptable->add($temp);
                        $temp = Array();
                    }
                }
                $str .= '<form action="'.U('/Event/importdone').'" method="post">';
                $str .= '<tr><td colspan="3"><input type="submit" value="'.C('STR_IMPORT').'" name="uploadsubmit" id="uploadsubmit"';
                
                if(count($temptable->where('1=1')->select())==0)
                    $str .= 'disabled';
                $str .=  ' />';
                $str .= '<input type="hidden" value="confirm" name="uploadconfirm" id="uploadconfirm" />';
                $str .= '<a href="javascript:history.back(-1);">'.C('STR_BACK').'</a>';
                $str .= '</table>';

                $this->assign('importlist',$str);
                $this->display();
            }
        }
        else{
            $this->error(C('STR_UPLOAD_ERROR'));
        }

    }

    public function importdone(){
        if(I('uploadconfirm') == 'confirm' && (int)lookupAdmin($_SESSION['id']) >= 2){
            $source = M('tempevent');
            $dest = M('event');
            $dest_staff = M('eventstaff');
            $deletedevent = M('deletedevent');

            $import = $source->where(Array('action' => 'delete'))->select();
            foreach($import as $value){
                $e = $dest->where(Array('id' => $value['original_id']))->find();
                $e['original_id'] = $e['id'];
                $e = reduceParam($e,'id');
                $deletedevent->add($e);

                $dest_staff->where(Array('event_id' => $value['original_id']))->delete();
                $dest->where(Array('id' => $value['original_id']))->delete();
            }

            $import = $source->where(Array('action' => 'add'))->select();
            foreach($import as $value){
                if(!validateLength2($value['name'],100))
                    $message = C('STR_EVENTADD_ERROR_NAME');
                else if (!validateLength2($value['city'],20))
                    $message = C('STR_EVENTADD_ERROR_CITY');
                else if (!validateLength2($value['location'],100))
                    $message = C('STR_EVENTADD_ERROR_LOCATION');
                else if (!validateDate($value['event_date']))
                    $message = C('STR_EVENTADD_ERROR_DATE');
                else
                {
                    $data['name'] = $value['name'];
                    $data['city'] = $value['city'];
                    $data['associated_city'] = $value['associated_city'];
                    $data['store'] = $value['store'];
                    $data['georegion'] = $value['georegion'];
                    $data['location'] = $value['location'];
                    $data['event_date'] = $value['event_date'];
                    $data['month'] = $value['month'];
                    $data['type'] = $value['type'];
                    if($value['type'] == 'Sealed')
                        $data['type'] = 'Limited';
                    $data['format'] = $value['format'];
                    $data['description'] = $value['description'];
                    $data['reserved_2'] = $value['reserved_2'];

                    if($id = $dest->data($data)->add()){
                        $data_staff['event_id'] = $id;
                        $data_staff['judge_id'] = -1;
                        $data_staff['is_hj'] = 1;
                        $data_staff['pickup_date'] = '19700101';
                        $dest_staff->data($data_staff)->add();
                        $message = C('STR_EVENTIMPORT_SUCCESS');
                        logEvent($_SESSION['id'], 'import', $data['name'].','.$data['event_date']);
                    }
                    else
                        $message = C('STR_DB_FAILURE');
                }

                $this->assign('message',$message);
                $this->display();
            }
        }
        else
            U('/Event/index', '', '', 1);
    }

    public function cleanup(){
        if((int)lookupAdmin($_SESSION['id']) >= 2){
            $table = M('event');
            $duplicated_pptq = $table->where(Array('event_date' => Array('gt',todayDate()), 'type' => 'PPTQ'))->group('store, associated_city')->having('count(*)>1')->select();
            $duplicated_gpt = $table->where(Array('event_date' => Array('gt',todayDate()), 'type' => 'GPT'))->group('store, month')->having('count(*)>1')->select();

            $str = '<table class="storelist"><tr><th>'.C('STR_STORE_NAME').'</th><th>'.C('STR_CITY_SHORT').'</th><th>'.C('STR_SUSPECTED_DUPLICATE').'</th></tr>';
            if(count($duplicated_pptq) > 0 || count($duplicated_gpt > 0)){
                foreach($duplicated_pptq as $value){
                    $str .= '<tr><td class="middle"><a href="'.U('Store/storedetail', Array('store' => $value['store'])).'">'.lookupStoreById($value['store']).'</a></td>';
                    $str .= '<td class="city">'.$value['city'].'</td>';
                    $str .= '<td class="name">'.$value['type'].' '.$value['associated_city'].', '.formatDate($value['event_date']).'</td></tr>';
                }
                foreach($duplicated_gpt as $value){
                    $str .= '<tr><td class="middle"><a href="'.U('Store/storedetail', Array('store' => $value['store'])).'">'.lookupStoreById($value['store']).'</a></td>';
                    $str .= '<td class="city">'.$value['city'].'</td>';
                    $str .= '<td class="name">'.$value['type'].' '.$value['associated_city'].', '.formatDate($value['event_date']).'</td></tr>';
                }
            }
            else{
                $str .= '<tr><td colspan="2">'.C('STR_NO_DUPLICATED').'</td></tr>';
            }
            $str .= '</table>';
            
            $this->assign('dis',$str);
            $this->display();
        }
        else{
            U('/Event/index', '', '', 1);
        }
    }

    public function resetregion(){
        logEvent($_SESSION['id'], 'resetregion', 'resetregion');
        resetRegion();
    }

    // public function repairSeason(){
    //     $events = M('event');

    //     $allevent = $events->order('id desc')->where(Array('associated_city' => '#1of'))->select();
    //     foreach($allevent as $value){
    //         $data['name'] = str_replace('#1of', '#1of2016', $value['name']);
    //         $data['associated_city'] = str_replace('#1of', '#1of2016', $value['associated_city']);
    //         $events->where(Array('id' => $value['id']))->save($data);
    //         echo '<p>updated season data for id='.$value['id'].', name='.$value['name'].' as #1of2016</p>';
    //     }
    // }

    public function fillmonth(){
        $events = M('event');

        $allevent = $events->order('id desc')->where('1=1')->select();
        foreach($allevent as $value){
            $data['month'] = substr($value['event_date'],0,6);
            $events->where(Array('id' => $value['id']))->save($data);
            echo '<p>updated month data for id='.$value['id'].', name='.$value['name'].' as '.$data['month'].'</p>';
            
        }
        logEvent($_SESSION['id'], 'fillmonth', 'fillmonth');
    }

    public function fillstore(){
        $events = M('event');
        $stores = M('store');

        $allevent = $events->order('id desc')->where('store=0')->select();
        foreach($allevent as $value){
            $a = explode(' ',$value['name'],3);
            if($a[0] == 'PPTQ' || $a[0] == 'GPT' || $a[0] == 'RPTQ'){
                $e = Array();
                if(!($stores->where(Array('name' => $a[2]))->find())){
                    $s['name'] = $a[2];
                    $s['city'] = $value['city'];
                    $s['georegion'] = lookupRegionByCity($value['city']);
                    $s['location'] = $value['location'];
                    $s['contact'] = $value['description'];
                    $e['store'] = $stores->add($s);
                    echo '<p>added store id='.$e['store'].', name='.$s['name'].'</p>';
                }
                else{
                    $s = $stores->where(Array('name' => $a[2]))->find();
                    echo '<p>store id='.$s['id'].', name='.$s['name'].' exists!</p>';
                    $e['store'] = $s['id'];
                }
                $e['associated_city'] = $a[1];
                $events->where(Array('id' => $value['id']))->save($e);
            }
        }
        logEvent($_SESSION['id'], 'fillstore', 'fillstore');
    }

    public function calibrateseason(){
        $events = M('event');
        $condition = Array('type' => 'PPTQ', 'associated_city' => '#1of2016', 'event_date' => Array('between', '20150822,20151122'));

        $allevent = $events->order('id desc')->where($condition)->select();
        foreach($allevent as $value){
            $data['name'] = str_replace('#1of2016', '#2of2016', $value['name']);
            $data['associated_city'] = '#2of2016';
            $events->where(Array('id' => $value['id']))->save($data);
            logEvent($_SESSION['id'], 'calibrate', $data['name'].','.$value['event_date']);
            echo '<p>updated season data for id='.$value['id'].', name='.$value['name'].' as '.$data['associated_city'].'</p>';
            
        }
        
    }

    public function importJSONstore(){
        if((int)lookupAdmin($_SESSION['id']) >= 2 || is_numeric(I('id'))){
            $store = M('store');
            $condition = Array('id' => I('id'));

            if($storelist = $store->where($condition)->find()){
                if(strlen($storelist['orgid']) > 0){
                    $this->importJSONbyId($storelist['orgid'], $storelist['businessid']);
                }
            }
        }
        else{
            U('/Event/index', '', '', 1);
        }
    }

    public function importJSON(){
        if((int)lookupAdmin($_SESSION['id']) >= 2){
            $store = M('store');
            $event = M('event');
            $eventstaff = M('eventstaff');
            $condition = 'orgid is not null AND reserved_1 != "inactive"';
            $storelist = $store->where($condition)->order('id asc')->select();

            foreach($storelist as $value){
                // $this->importJSONbyId($value['orgid'], $value['businessid']);
                $returnObj = $this->getEventList((int)$value['businessid'], (int)$value['orgid']);
                $eventsAtVenue = $returnObj->d->Result->EventsAtVenue;
                // $eventsNotAtVenue = $returnObj->d->Result->EventsNotAtVenue;

                if($eventsAtVenue == NULL){
                    echo 'orgid='.$value['orgid'].', addrid='.$value['businessid'].' does not exist';
                    logEvent($_SESSION['id'], 'import_json_error', 'orgid='.$value['orgid'].', addrid='.$value['businessid'].' does not exist');
                }
                elseif($eventsAtVenue[0]->Id == -99){
                    echo 'please update storeid='.$this->lookupStoreByOrgID($value['orgid']).' '.lookupStoreByID($this->lookupStoreByOrgID($value['orgid'])).', orgid='.$value['orgid'].', addrid='.$value['businessid'].' has changed.';
                    logEvent($_SESSION['id'], 'import_json_error', 'please update storeid='.$this->lookupStoreByOrgID($value['orgid']).', orgid='.$value['orgid'].', addrid='.$value['businessid'].' has changed.' );
                }else
                foreach($eventsAtVenue as $eventObj){
                    if($eventObj->EventTypeCode == "GT" || $eventObj->EventTypeCode == "PPTQ"){
                        $a['city'] = $eventObj->Address->City;
                        $name = $this->importTypeAssociatedCity($eventObj->Name);
                        $a['associated_city'] = $name['associated_city'];
                        $a['associated_year'] = $name['associated_year'];
                        $a['type'] = $name['type'];
                        $a['name'] = $name['type'].' '.$name['associated_city'].' '.$this->importStoreName($eventObj->OrganizationName);
                        $a['store'] = lookupStoreByName($this->importStoreName($eventObj->OrganizationName), $a['city']);
                        $a['georegion'] = lookupRegionByCity($a['city']);
                        $a['location'] = $eventObj->Address->Line1;
                        $a['event_date'] = date("Ymd",substr($eventObj->StartDate,6,10));
                        $a['month'] = substr($a['event_date'],0,6);
                        $a['format'] = $this->importFormatJSON($eventObj->PlayFormatCode);
                        $a['description'] = $eventObj->Email.' '.$eventObj->PhoneNumber;
                        $a['sanction_number'] = $eventObj->SanctionNumber;
                        $a['reserved_2'] = todayDate().','.lookupJudgeByID($_SESSION['id']);

                        if(lookupEventByName($a['name'], $a['event_date'], $a['city'])){
                            //echo '<p>'.$a['name'].' on '.$a['event_date'].' exists!</p>';
                        }else
                        {
                            if($id = $event->data($a)->add()){
                            $data_staff['event_id'] = $id;
                            $data_staff['judge_id'] = -1;
                            $data_staff['is_hj'] = 1;
                            $data_staff['pickup_date'] = '19700101';
                            $eventstaff->data($data_staff)->add();
                            echo '<p>Successfully imported '.$a['name'].' on '.$a['event_date'].'!</p>';
                            logEvent($_SESSION['id'], 'import_json', $a['name'].','.$a['event_date']);
                        }
                        else{
                            echo '<p>Failed to import '.$a['name'].' on '.$a['event_date'].'!</p>';
                            logEvent($_SESSION['id'], 'database_error', implode(",",$a));
                        }
                        }
                    }
                }
            }
        }
        else{
            U('/Event/index', '', '', 1);
        }
    }

    public function jsontest(){
        if((int)lookupAdmin($_SESSION['id']) >= 2 || is_numeric(I('id'))){
            // $store = M('store');
            // $condition = Array('id' => I('id'));

            // if($storelist = $store->where($condition)->find()){
            //     if(strlen($storelist['orgid']) > 0){
            //         $this->importJSONtest($storelist['orgid'], $storelist['businessid']);
            //     }
            // }
            $this->importJSONtest(11486, 374366);
        }
        else{
            U('/Event/index', '', '', 1);
        }
    }

    private function importJSONtest($orgid, $addrid){
            $returnObj = $this->getEventTest((int)$addrid, (int)$orgid);
            $eventsAtVenue = $returnObj->d->Result->EventsAtVenue;
            // $eventsNotAtVenue = $returnObj->d->Result->EventsNotAtVenue;

            dump($eventsAtVenue);die;
            if($eventsAtVenue == NULL){
                logEvent($_SESSION['id'], 'import_json_error', 'orgid='.$orgid.', addrid='.$addrid.' does not exist');
            }
            elseif($eventsAtVenue[0]->Id == -99){
                echo 'please update storeid='.$this->lookupStoreByOrgID($orgid).' '.lookupStoreById($orgid).', orgid='.$orgid.', addrid='.$addrid.' has changed.';
                logEvent($_SESSION['id'], 'import_json_error', 'please update storeid='.$this->lookupStoreByOrgID($orgid).', orgid='.$orgid.', addrid='.$addrid.' has changed.' );
            }else
            foreach($eventsAtVenue as $eventObj){
                // if($eventObj->EventTypeCode == "GT" || $eventObj->EventTypeCode == "PPTQ"){
                    $a['city'] = $eventObj->Address->City;
                    $name = $this->importTypeAssociatedCity($eventObj->Name);
                    $a['associated_city'] = $name['associated_city'];
                    $a['associated_year'] = $name['associated_year'];
                    $a['type'] = $name['type'];
                    $a['name'] = $name['type'].' '.$name['associated_city'].' '.$this->importStoreName($eventObj->OrganizationName);
                    $a['store'] = lookupStoreByName($this->importStoreName($eventObj->OrganizationName), $a['city']);
                    $a['georegion'] = lookupRegionByCity($a['city']);
                    $a['location'] = $eventObj->Address->Line1;
                    $a['event_date'] = date("Ymd",substr($eventObj->StartDate,6,10));
                    $a['month'] = substr($a['event_date'],0,6);
                    $a['format'] = $this->importFormatJSON($eventObj->PlayFormatCode);
                    $a['description'] = $eventObj->Email.' '.$eventObj->PhoneNumber;                        
                    $a['sanction_number'] = $eventObj->SanctionNumber;
                    $a['reserved_2'] = todayDate().','.lookupJudgeByID($_SESSION['id']);

                    // dump($eventObj);
                    // dump($a);
                    // }
                }
    }

    private function getEventTest($businessAddressId, $organizationId){
        $current_time = time()."000";
        $post_template = Array(
            "language" => "en_us",
            "request" => Array(
                    "BusinessAddressId" => $businessAddressId,
                    "OrganizationId" => $organizationId,
                    "EventTypeCodes" => Array("GT", "PPTQ", "RPTQ"),
                    "PlayFormatCodes" => Array(),
                    "ProductLineCodes" => Array(),
                    "LocalTime" => "/Date(".$current_time.")/",
                    "EarliestEventStartDate" => null,
                    "LatestEventStartDate" => null
                )
            );
        $url = "http://locator.wizards.com/Service/LocationService.svc/GetLocationDetails";
        $post_template = json_encode($post_template);
        list($return_code, $return_content) = $this->http_post_data($url, $post_template);
        echo($return_content);

        return json_decode($return_content);
    }


    private function importJSONbyId($orgid, $addrid){
            $event = M('event');
            $eventstaff = M('eventstaff');

            $returnObj = $this->getEventList((int)$addrid, (int)$orgid);
            $eventsAtVenue = $returnObj->d->Result->EventsAtVenue;
            // $eventsNotAtVenue = $returnObj->d->Result->EventsNotAtVenue;

            foreach($eventsAtVenue as $eventObj){
                if($eventObj->EventTypeCode == "GT" || $eventObj->EventTypeCode == "PPTQ"){
                    $a['city'] = $eventObj->Address->City;
                    $name = $this->importTypeAssociatedCity($eventObj->Name);
                    $a['associated_city'] = $name['associated_city'];
                    $a['associated_year'] = $name['associated_year'];
                    $a['type'] = $name['type'];
                    $a['name'] = $name['type'].' '.$name['associated_city'].' '.$this->importStoreName($eventObj->OrganizationName);
                    $a['store'] = lookupStoreByName($this->importStoreName($eventObj->OrganizationName), $a['city']);
                    $a['georegion'] = lookupRegionByCity($a['city']);
                    $a['location'] = $eventObj->Address->Line1;
                    $a['event_date'] = date("Ymd",substr($eventObj->StartDate,6,10));
                    $a['month'] = substr($a['event_date'],0,6);
                    $a['format'] = $this->importFormatJSON($eventObj->PlayFormatCode);
                    $a['description'] = $eventObj->Email.' '.$eventObj->PhoneNumber;                        $a['sanction_number'] = $eventObj->SanctionNumber;
                    $a['reserved_2'] = todayDate().','.lookupJudgeByID($_SESSION['id']);

                        if(lookupEventByName($a['name'], $a['event_date'], $a['city'])){
                            echo '<p>'.$a['name'].' on '.$a['event_date'].' exists!</p>';
                        }else
                        {
                            if($id = $event->data($a)->add()){
                            $data_staff['event_id'] = $id;
                            $data_staff['judge_id'] = -1;
                            $data_staff['is_hj'] = 1;
                            $data_staff['pickup_date'] = '19700101';
                            $eventstaff->data($data_staff)->add();
                            echo '<p>Successfully imported '.$a['name'].' on '.$a['event_date'].'!</p>';
                            logEvent($_SESSION['id'], 'import_json_by_id', $a['name'].','.$a['event_date']);
                        }
                        else
                            echo '<p>Failed to import '.$a['name'].' on '.$a['event_date'].'!</p>';
                        }
                    }
                }
    }

    private function getEventList($businessAddressId, $organizationId){
        $current_time = time()."000";
        $post_template = Array(
            "language" => "en_us",
            "request" => Array(
                    "BusinessAddressId" => $businessAddressId,
                    "OrganizationId" => $organizationId,
                    "EventTypeCodes" => Array("FM", "DDCAS", "MGD", "GP", "GT", "MLP", "PPTQ", "PR", "QT", "RPTQ", "TG"),
                    "PlayFormatCodes" => Array(),
                    "ProductLineCodes" => Array(),
                    "LocalTime" => "/Date(".$current_time.")/",
                    "EarliestEventStartDate" => null,
                    "LatestEventStartDate" => null
                )
            );
        $url = "http://locator.wizards.com/Service/LocationService.svc/GetLocationDetails";
        $post_template = json_encode($post_template);
        list($return_code, $return_content) = $this->http_post_data($url, $post_template);
        // echo($return_content);

        return json_decode($return_content);
    }


    private function http_post_data($url, $data_string) {  
  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Content-Type: application/json; charset=utf-8',  
            'Content-Length: ' . strlen($data_string))  
        );  

        ob_start();  
        curl_exec($ch);  
        $return_content = ob_get_contents();  
        ob_end_clean();  
  
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
        return array($return_code, $return_content);  
    }

    private function importEventName($str, $type){
        $eventname = str_replace('-','',$str);
        $eventname = explode(' ', $eventname);
        $shortname = '';

        if($type == 'PPTQ'){
            $shortname = 'PPTQ ';
            $i = 5;
            while(!is_numeric($eventname[$i])){
                $shortname .= $eventname[$i];
                if($eventname[$i] == 'of')
                    $shortname .= $eventname[$i+1];
                $i++;
            }

        }
        elseif($type == 'GT'){
            $shortname = 'GPT ';
            $i = 4;
            while(!is_numeric($eventname[$i])){
                $shortname .= $eventname[$i];
                $i++;
            }
        }
        return $shortname;

    }

    private function importTypeAssociatedCity($name){
        $eventname = str_replace('- ','',$name);
        $eventname = explode(' ', $eventname);
        $event = Array();

        if($eventname[1] == 'Preliminary'){
            $event['type'] = 'PPTQ';
            $i = 5;
            while(!is_numeric($eventname[$i])){
                $event['associated_city'] .= $eventname[$i];
                $i++;
            }
            $event['associated_year'] = $eventname[$i];
        }
        elseif($eventname[1] == 'Grand'){
            $event['type'] = 'GPT';
            $i = 4;
            while(!is_numeric($eventname[$i])){
                $event['associated_city'] .= $eventname[$i];
            $i++;
            }
            $event['associated_year'] = $eventname[$i];
        }
        return $event;

    }

    private function importFormatJSON($format){
        if($format == 'STANDARD'){
            return 'Standard';
        }
        elseif($format == 'MODERN'){
            return 'Modern';
        }
        elseif($format == 'LEGACY'){
            return 'Legacy';
        }
        elseif($format == 'SEALED'){
            return 'Limited';
        }
        else return false;
    }

    private function importStoreName($str){
        $storename = explode('/', $str);
        return $storename[0];
    }

    private function importEventType($type){
        if($type == 'PPTQ'){
            return 'PPTQ';
        }elseif($type == 'GT'){
            return 'GPT';
        }
    }

    private function lookupStoreByOrgID($orgid){
        $data = M('store');
        if(strlen($orgid)>0){
            $condition['orgid'] = Array('eq', $orgid);
            if($s = $data->where($condition)->find()){
                return $s['id'];
            }
            else
                return 0;
        }
        return false;
    }

    // private function expUser(){//导出Excel
    //     $xlsName  = "User";
    //     $xlsCell  = array(
    //         array('id','账号序列'),
    //         array('account','登录账户'),
    //         array('nickname','账户昵称')
    //     );
    //     $xlsModel = M('Post');
    //     $xlsData  = $xlsModel->Field('id,account,nickname')->select();
    //     $this->exportExcel($xlsName,$xlsCell,$xlsData);
    // }

}