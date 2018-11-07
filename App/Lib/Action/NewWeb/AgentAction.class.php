<?php

//代理管理
class AgentAction extends GlobalAction {

    //代理管理
    public function index() {
        if (session('web_id')) {
            //代理
            if ($this->requst_agent_id) {
                $this->is_agent();
                $where['agent_id'] = $this->requst_agent_id;
            } else {
                $where['fu_agent'] = session('agent_id');
            }
        } else {
            //管理员
            if ($this->requst_agent_id) {
                $where['agent_id'] = $this->requst_agent_id;
            } else {
                $where['fu_agent'] = 0;   
            }
        }
        $model = M('NAgent');
        if ($agent_id) {
            $where['agent_id']  = $agent_id;
        }
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data = $model->where($where)->field()->order('agent_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        //添加按钮判断
        $this->assign('flag', $this->auth->check('show_button_agent', session('agent_id')));
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';         
        $this->title='代理管理';        
        $this->display();
    }   

    //代理信息编辑
    public function api_save(){
        $arr = (array)json_decode($_REQUEST['arr']);

        if($arr['payment_account_type']=="bank"){
            $arr['payment_account_type']=1;
        }else if($arr['payment_account_type']=="alipay"){
            $arr['payment_account_type']=2;
        }else{
            $arr['payment_account_type']=3;
        }
        $arr['open_account']=$arr['card_bank_branch'];
        $arr['bank_number']=$arr['card_number'];
        $arr['bank']=$arr['card_bank_name']; 
        $arr['bank_city']=$arr['card_bank_city'];
        $arr['bank_province']=$arr['card_bank_province'];
        $arr['bank_number']=$arr['card_number'];
        $arr['proportion']= floatval($arr['proportion']) > 0.9 ? 0.9 : floatval($arr['proportion']);
        $arr['password'] = md5(C('ALL_ps').$arr['password']);        
        //print_r($arr);
        $message = array('info' => false, 'msg' =>'');
        $model = M('NAgent');
        $NAdmin = M('NAdmin');
        $web_id = session('web_id');
        if ($arr['agent_id']) {
            if (!$web_id) {
                //如果不是管理员, 查看agent_id 是不是一级代理旗下的二级代理
                $fu_agent = $model->where('agent_id='.$arr['agent_id'])->getField('fu_agent');
                if ($fu_agent != $arr['agent_id']) {
                    $message['msg'] = '非法请求!';
                }
                A('Gongju')->echo_json($message);
                exit;
            }
            $re = $model->where("agent_id=".$arr['agent_id'])->save($arr);
            //修改admin 表 pen_name 和password
            $res = $NAdmin->where("id=".$arr['agent_id'])->save($arr);
        } else {
            $user_name = $NAdmin->field('id')->where(array('user_name' => $arr[user_name]))->find();
            if ($user_name) {
                $message['msg'] = '用户名已存在!';
                A('Gongju')->echo_json($message);
                exit;
            }
            $arr['time'] = date('Y-m-d H:i:s', time());
            $power = array();
            if (!$web_id) {
                #管理员添加 一级代理
                $web_id = $model->max('agent_id') + 1;
                $arr['fu_agent'] = 0;
                $arr['is_first'] = 1;
                $arr['web_id'] = $web_id;
                $arr['web_url'] = 'wap'.$web_id.'.nw.kyueyun.com';
                $power['group_id'] = 2;  
            } else {
                # 一级代理添加 二级代理
                $arr['web_id'] = $web_id; 
                $arr['is_first'] = 0;                
                $arr['fu_agent'] = session('agent_id'); 
                $arr['web_url'] = 'wap'.$web_id.'.nw.kyueyun.com';
                $power['group_id'] = 3;                
            }
            $res = $NAdmin->add($arr);
            // echo $NAdmin->getLastSql();
            $arr['agent_id'] = $res;          
            $re = $model->add($arr);
            // echo $model->getLastSql();  
            //分配权限
            $power['uid'] = $arr['agent_id'];
            M('NAuthGroupAccess')->add($power);   

            //创建打款账户记录表
            $data = array(
                'agent_id'      => $arr['agent_id'],
                'web_id'        => $arr['web_id'],
                'is_first'      => $arr['is_first'],
                'time'          => $arr['time']
            );
            M('NAgentMoney')->add($data);
        }

        if ($re && $res) {
            $message['info'] = true;
        }
        A('Gongju')->echo_json($message);
    }

    public function edit(){
        //print_r($_GET[_URL_][3]);
        //
        $this->assign('userId',$_GET[_URL_][3]);
        $this->title='代理编辑';
        $this->display();
    }


    public function api_get() {
        //根据agentid在n_agent获取代理商信息
        //echo $_GET['id'];exit;
        $userId = $_GET['id'];
        $data = M('NAgent')->where("agent_id=".$userId)->find();
        header("Content-Type:application/json");
        if(strpos($data['card_holder_name'],"公司")==false){
            $data['is_company']=0;
        }else{
            $data['is_company']=1;
        }
        if($data['payment_account_type']==1){
            $data['payment_account_type']= "bank";
         }else if($data['payment_account_type']==2){
            $data['payment_account_type']= "alipay";
         }else{
            $data['payment_account_type']= "weixin";
         }
        $data['card_number'] = $data['bank_number'];
        $data['card_bank_name'] = $data['bank'];
        $data['card_bank_city'] = $data['bank_city'];
        $data['card_bank_province'] = $data['bank_province'];
        $data['card_bank_branch'] = $data['open_account'];
        echo json_encode($data);
            // $a = array(
            //     "id"=> "28428",
            //     "username"=> "cessss",
            //     "type"=> "agent",
            //     "role"=> null,
            //     // "affiliate_level"=> "0",
            //     "site_domain"=> null,
            //     "site_name"=> null,
            //     "subscribe_url"=> null,
            //     "kefu_weixin"=> null,
            //     "upline_uid"=> "28365",
            //     "channel_uid"=> "28365",
            //     "admin_uid"=> "15",
            //     "commission_rate"=> "0.80",
            //     "inviter_uid"=> null,
            //     "inviter_reward_rate"=> null,
            //     "nickname"=> "昵称山东省的的xxx",
            //     "email"=> null,
            //     "avatar"=> null,
            //     "create_time"=> "2017-10-18 13:03:48",
            //     "created_by_id"=> "28365",
            //     "lastdt"=> "2017-10-18 13:03:48",
            //     "mobile"=> null,
            //     "payment_account_type"=> "bank",
            //     "card_holder_name"=> "持卡人张三",
            //     "card_number"=> "100000",
            //     "card_bank_name"=> "中国银行",
            //     "card_bank_province"=> "江苏",
            //     "card_bank_city"=> "123123",
            //     "card_bank_branch"=> "123123",
            //     "is_company"=> 0,
            //     "alipay_account_number"=> null,
            //     "alipay_account_name"=> null,
            //     "weixin_account"=> null,
            //     "weixin_nickname"=> null,
            //     "entpay_app_id"=> null,
            //     "entpay_openid"=> "",
            //     "entpay_realname"=> "",
            //     "status"=> "active",
            //     "activated_at"=> null,
            //     "deactivated_at"=> null,
            //     "remark"=> "",
            //     "auto_withdraw"=> 1,
            //     "follow_mode"=> "2",
            //     "auto_withdraw_type"=> "1",
            //     "payment_gateway"=> "1",
            //     "is_pay_money"=> "1"
            // );

            // header("Content-Type:application/json");
            // echo json_encode($a);
        }

    //代理列表
    public function api_search(){
        if (session('web_id')) {
            //代理
            $where['fu_agent'] = session('agent_id');
        } else {
            //管理员
            $where['fu_agent'] = 0;   
        }
        $q = I('q');
        if ($q) {
            $where['pen_name'] = $q;
        }
        $model = M('NAgent');
        $data = $model->where($where)->order('id desc')->field('agent_id id, pen_name nickname,user_name username')->select();
        // $arr = array(
        //     'id' => 1,
        //     // 'value' => 'sdfsdf',
        //     'username' => 'sdfsdf',
        //     'nickname' => 'sdfsdf',
        //     // 'text' => '123123',
        //     // 'name' => '123123',
        // );
        // echo json_encode(array($arr));
        A('Gongju')->echo_json($data);
    }


    public function api_reset_password() {
        if (session('web_id')) {
            //代理
            if ($this->requst_agent_id) {
                $this->is_agent();
                $where['agent_id'] = $this->requst_agent_id;
            }
        } else {
            //管理员
            $where['agent_id'] = $this->requst_agent_id;
        }
        $model = M('NAgent');
        $new_password = mt_rand(100000, 999999);
        $arr = array('password' => md5(C('ALL_ps').$new_password));
        $res = $model->where($where)->save($arr); 
        // echo $model->getLastSql();
        $re = M('NAdmin')->where(array('id' => $this->requst_agent_id ))->save($arr);  
        $data['new_password'] = $new_password;
        if (!$res || !$re) {
            $data['new_password'] = '密码重置失败!';
        }
        A('Gongju')->echo_json($data);   
    }
}
