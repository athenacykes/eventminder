<?php
class IndexAction extends Action {
    Public function _initialize() {
        if(isset($_SESSION['id']) && isset($_SESSION['username'])) {
            if(time() - (int)lookupLastLogin($_SESSION['id']) < 604800)
                $this->redirect('Event/index');
            else{
                session_destroy();
            }
        }
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

    public function register(){
        $question = M('regquestion');
        $id = rand(1,$question->count());
        $condition['id'] = $id; 
        $regquestion=$question->where($condition)->select();

        $this->assign('regid',$regquestion[0]['id']);
        $this->assign('regquestion',$regquestion[0]['question']);

        $this->display();
    }

    public function reguser(){
        if(empty($_POST))
            U('register', '', '', 1);

        if(!validateLength(I('username'),4,16))
            $message = C('STR_REG_ERROR_USERNAME_LENGTH');
        else if (validateExist(I('username'),'username'))
            $message = C('STR_REG_ERROR_USERNAME_EXIST');
        else if (!validateLength(I('usercity'),2,20))
            $message = C('STR_REG_ERROR_CITY');
        else if (!validateLength(I('userpassword'),6,20))
            $message = C('STR_REG_ERROR_PASSWORD_LENGTH');
        else if (!validateMatch(I('userpassword'),I('confirmpassword')))
            $message = C('STR_REG_ERROR_PASSWORD_MATCH');
        else if (!validateLength(I('fullname'),2,20))
            $message = C('STR_REG_ERROR_NAME_LENGTH');
        else if (validateExist(I('fullname'),'fullname'))
            $message = C('STR_REG_ERROR_NAME_EXIST');
        else if (!validateDCI(I('userdci')))
            $message = C('STR_REG_ERROR_DCI');
        else if (validateExist(I('userdci'),'dci'))
            $message = C('STR_REG_ERROR_DCI_EXIST');
        else if (!validateQuestion(I('regid'),I('useranswer')))
            $message = C('STR_REG_ERROR_VALIDATE');
        else
        {
            $table = M('user');
            $data['username'] = I('username');
            $data['city'] = I('usercity');
            $data['level'] = I('userlevel');
            $data['password'] = MD5(I('userpassword'));
            $data['fullname'] = I('fullname');
            $data['dci'] = I('userdci');
            $data['role_admin'] = '0';
            $data['reserved_1'] = 'pending';

            if($table->data($data)->add())
                $message = C('STR_REG_SUCCESS');
            else
                $message = C('STR_DB_FAILURE');
        }

        $this->assign('message',$message);
        $this->display();
    }

    public function login(){
        $this->display();
    }

    public function auth(){
        $username = I('username');
        $pwd = I('password', '', 'md5');

        $user = M('user')->where(array('username' => $username))->find();

        if(!$user || $user['password'] != $pwd) {
            $this->error(C('STR_LOGIN_ERROR_INVALID'));
        }
        
        if($user['reserved_1'] == 'pending') {
            $this->error(C('STR_LOGIN_ERROR_PENDING'));
        }

        $data = array(
            'id' => $user['id'],
            'lastlogin_ip' => get_client_ip(),
            'lastlogin_time' => time(),
            );

        M('user')->save($data);

        session('id', $user['id']);
        session('username', $user['username']);
        session('fullname', $user['fullname']);
        session('logintime', date('Y-m-d H:i:s', $user['lastlogin_time']));
        session('loginip', $user['lastlogin_ip']);

        logEvent($user['id'], 'login', $_SERVER['HTTP_USER_AGENT']);

        U('/Event/index','','',1);
    }

    public function eventdetail() {
        import("@.Action.Details");
        if(isset($_GET['id']))
            $params = Array("id" => I('id'));
        else
            $this->redirect('index');

        $params = array_merge($params, Array('isLogin' => '0'));

        $details = new Details($params);
        $this->assign('details',$details->display());
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
        if(isset($_GET['region']))
            $params = array_merge($params,Array('region' => I('region')));
        if(isset($_GET['store']))
            $params = array_merge($params,Array('store' => I('store')));
        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));
        if(isset($_GET['past'])){
            $this->assign('pastevent',C('STR_PAST_EVENT'));
            $params = array_merge($params,Array('past' => I('past')));
        }              

        $params = array_merge($params, Array('isLogin' => '0'));

        $listing = new Listing($params);

        $this->assign('listing',$listing->display());
        $this->display();
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

        U('/Index/eventlist',$param,'',1);
    }

    public function storelist(){
        import("@.Action.StoreList");
        $params = array();

        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));
        if(isset($_GET['city']))
            $params = array_merge($params,Array('city' => I('city')));

        $storelist = new StoreList($params);
        $this->assign('storelist',$storelist->display());
        $this->display();
    }

    public function storedetail() {
        import("@.Action.StoreDetail");
        if(isset($_GET['store']))
            $params = Array('id' => I('store'));
        else
            $this->redirect('storelist');

        $details = new StoreDetail($params);
        $this->assign('details',$details->display());
        $this->display();
    }

    public function storefilter(){
        $filtercity = I('filtercity');
        $origcity = I('origcity');

        $param = Array();
        if($filterseason!='default'){
            $param['city'] = $filtercity;
        } else {
            $param['city'] = $origcity;
        }

        U('storelist',$param,'',1);
    }
}