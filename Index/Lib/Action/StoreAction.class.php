<?php
class StoreAction extends Action {
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

        $this->assign('favstore', $this->favstore());
        
        $this->display();
    }

    public function storelist(){
        import("@.Action.StoreList");
        $params = array();

        if(isset($_GET['next']))
            $params = array_merge($params,Array('next' => I('next')));
        if(isset($_GET['city']))
            $params = array_merge($params,Array('city' => I('city')));

        $params = array_merge($params, Array('isLogin' => '1'));
        $params = array_merge($params, Array('judge' => $_SESSION['id']));

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

        $params = array_merge($params, Array('isLogin' => '1', 'current' => $_SESSION['id']));

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

    public function follow(){
        if(isset($_GET['store'])){
            $store = M('store');
            $condition = Array('id' => I('store'));
            if($store->where($condition)->find()){
                $favstore = M('favstore');
                $condition_fav = Array('judge_id' => $_SESSION['id'], 'store_id' => I('store'));
                if($favstore->where($condition_fav)->find()){

                }
                else{
                    $fav['judge_id'] = $_SESSION['id'];
                    $fav['store_id'] = I('store');
                    $favstore->add($fav);
                    logEvent($_SESSION['id'], 'followstore', I('store').','.lookupStoreById(I('store')));
                }
            }

        }
            
        U('Store/index', '', '', 1);
    }

    public function unfollow(){
        if(isset($_GET['store'])){
            $store = M('store');
            $condition = Array('id' => I('store'));
            if($store->where($condition)->find()){
                $favstore = M('favstore');
                $condition_fav = Array('judge_id' => $_SESSION['id'], 'store_id' => I('store'));
                if($favstore->where($condition_fav)->find()){
                    $favstore->where($condition_fav)->delete();
                    logEvent($_SESSION['id'], 'unfollowstore', I('store').','.lookupStoreById(I('store')));
                }
            }

        }
            
        U('Store/index', '', '', 1);
    }

    public function addtravelguide(){
        if(!isset($_GET['store']))
            U('Store/index', '', '', 1);
        $data = M('travelguide');
        $condition = Array('store' => I('store'));
        if($data->where($condition)->find())
            U('Store/storedetail', Array('store' => I('store')), '', 1);
        $store_data = M('store');
        $condition_fav = Array('id' => I('store'));
        if($store = $store_data->where($condition_fav)->find()){
            $this->assign('store', $store);
            $this->display();
        }else
            U('Store/index', '', '', 1);    
    }

    public function addtravelguidedone(){
        if(empty($_POST))
            U('/Store/storelist', '', '', 1);

        $data = M('travelguide');
        $condition = Array('store' => I('store'));
        if($data->where($condition)->find())
            U('Store/storedetail', Array('store' => I('store')), '', 1);
        $store_data = M('store');
        $condition_fav = Array('id' => I('store'));
        if($store = $store_data->where($condition_fav)->find()){

            $tg['store'] = I('store');
            $tg['content'] = I('content');
            $tg['author'] = $_SESSION['id'];

            $data->add($tg);
            logEvent($_SESSION['id'], 'addtravelguide', I('store').','.lookupStoreById(I('store')));
            U('Store/storedetail', Array('store' => I('store')), '', 1);   
        }else
            U('Store/index', '', '', 1);   
    }

    public function modifytravelguide(){
        if(!isset($_GET['store']))
            U('Store/index', '', '', 1);

        $store_data = M('store');
        $condition_fav = Array('id' => I('store'));
        if($store = $store_data->where($condition_fav)->find()){
            $data = M('travelguide');
            $condition = Array('store' => I('store'));
            if($tg = $data->where($condition)->find()){
                $this->assign('tg', $tg);
                $this->assign('store', $store);
                $this->display();
            }else
                U('Store/storedetail', Array('store' => I('store')), '', 1);
        }else
            U('Store/index', '', '', 1);   
    }

    public function modifytravelguidedone(){
        if(empty($_POST))
            U('/Store/storelist', '', '', 1);

        $store_data = M('store');
        $condition_fav = Array('id' => I('store'));
        if($store = $store_data->where($condition_fav)->find()){
            $data = M('travelguide');
            $condition = Array('store' => I('store'));
            if($data->where($condition)->find()){
                $tg['content'] = I('content');
                $tg['author'] = $_SESSION['id'];
                $data->where(Array('store' => I('store')))->save($tg);
                logEvent($_SESSION['id'], 'modifytravelguide', I('store').','.lookupStoreById(I('store')));
            }
            
            U('Store/storedetail', Array('store' => I('store')), '', 1); 
        }else
            U('Store/index', '', '', 1);   
    }

    // TODO in future version !!!!
    public function modify(){
        U('Store/storedetail', Array('store' => I('store')), '', 1);
    }

    private function favstore(){
        $str = '';

        $str .= $this->favstoreheader();
        $str .= $this->favstorecontent();
        $str .= $this->favstorefooter();

        return $str;
    }

    private function favstoreheader(){
        $str = '';

        $str .= '<table class="storelist">';
        $str .= '<tr><th>'.C('STR_STORE_NAME').'</th>';
        $str .= '<th>'.C('STR_CITY_SHORT').'</th>';
        $str .= '<th>'.C('STR_FUTURE_EVENT').'</th>';
        $str .= '<th>'.C('STR_ACTION').'</th></tr>';

        return $str;
    }

    private function favstorecontent(){
        $str = '';
        $data = M('judgestore');
        $condition = Array('judge_id' => $_SESSION['id']);
        $order = Array('convert(city using gbk)' => 'asc', 'convert(name using gbk)' => 'asc');
        $favstore = $data->where($condition)->order($order)->select(); 
        
        if(count($favstore) > 0){
            foreach($favstore as $value){
                $str .= '<tr>';
                $str .= '<td class="name"><a href="'.U('storedetail', Array('store' => $value['id'])).'">'.$value['name'].'</a></td>';
                $str .= '<td class="city">'.$value['city'].'</td>';
                $str .= '<td class="event">'.$this->getStoreFutureEvents($value['id']).'</td>';
                $str .= '<td class="action">'.$this->buttonUnfollow($value['id']).'</td>';
                $str .= '</tr>';
            }
        }
        else{
            $str .= '<tr><td class="name" colspan="4">'.C('STR_NO_FOLLOW_STORE').'</td></tr>';
        }
        return $str;
    }

    private function favstorefooter(){
        $str = '';

        $str .= '<tr><th colspan="4"><a href="'.U('storelist').'">'.C('STR_FOLLOW_MORE_STORE').'</a></th></tr>';
        $str .= '</table>';
        return $str;
    }

    private function getStoreFutureEvents($storeid){
        $data = M('event');
        $condition = Array('store' => $storeid, 'event_date' => Array('egt', todayDate()));
        $count = $data->where($condition)->count();

        if($count == 0)
            $str = C('STR_NO_FUTURE_EVENT');
        else
            $str = '<a href="'.U('Event/eventlist', Array('store' => $storeid)).'">'.$count.C('STR_CALENDAR_EVENT_COUNT').'</a>';
        return $str;
    }

    private function buttonUnfollow($storeid){
        $str = '<button onclick="window.location=\''.U('unfollow', Array('store' => $storeid)).'\'">'.C('STR_REMOVE_FAVSTORE').'</button>';
        return $str;
    }
}
