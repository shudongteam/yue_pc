<?php

//我的结算单
class FinancialAction extends GlobalAction {

    //我的结算单
    public function index() {
        //每日账单
        $from = I('from');
        $to = I('to');
        $order = 'time desc';
        if ($from) {
            $where['time'] = array('egt', $from);
            $order = '';
        }
        if ($to) {
            $where['time'] = array('elt', $to);
            $order = '';            
        }
        if($from && $to){
            $where['time'] = array(array('egt', $from), array('elt', $to)) ;
        }
        //type = 1 隐藏申请提现
        //账户详情

        $agent_id = session('agent_id');
        $model = M('NMoneyTongji');
        $model2 = M('NAgentMoney');
        import('ORG.Util.MyPage');
        //代理URL
        $agent_url = '';
        //申请提现/提现记录 按钮显示:1都不显示, 2显示提现记录, 3全部显示 
        $is_show = 1;
        if (session('web_id')) {
            if (session('is_first')) {
                //一级代理
                if ($this->requst_agent_id) {
                    //查询二级代理
                    $this->is_agent();
                    $agent_id = $where['agent_id'] = $this->requst_agent_id;
                    //每日账单
                    $count = $model->where($where)->count(); 
                    $Page = new MyPage($count);
                    $data = $model->where($where)->field('id,money_common_day,day_pays,day_vip_pays,day_no_pays,day_vip_no_pays,money_vip_day,time,proportion')->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();  
                    $agent_url = '?agent_id='.$this->requst_agent_id;
                    $is_show = 2;
                } else {
                    //所有代理每日账单汇总
                    $where['web_id'] = session('web_id');
                    $count = $model->where($where)->count('DISTINCT time'); 
                    $Page = new MyPage($count);
                    $data = $model->where($where)->field('id,sum(money_common_day) money_common_day, sum(day_pays) day_pays, sum(day_vip_pays) day_vip_pays, sum(day_no_pays) day_no_pays, sum(day_vip_no_pays) day_vip_no_pays, sum(money_vip_day) money_vip_day,time,proportion')->group('time')->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                    $is_show = 3;
                }

            } else {
                //二级代理
                $where['agent_id'] = $agent_id;
                //每日账单
                $count = $model->where($where)->count(); 
                $Page = new MyPage($count);
                $data = $model->where($where)->field('id,money_common_day,day_pays,day_vip_pays,day_no_pays,day_vip_no_pays,money_vip_day,time,proportion')->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $is_show = 3;                
            }
            //账户详情
            $account = $model2->where(array('agent_id' => $agent_id))->find(); 
        } else {
            //管理员
            if ($this->requst_agent_id) {
                $where['web_id'] = $this->requst_agent_id;
                $count = $model->where($where)->count('DISTINCT time'); 
                $Page = new MyPage($count);
                $data = $model->where($where)->field('id,sum(money_common_day) money_common_day, sum(day_pays) day_pays, sum(day_vip_pays) day_vip_pays, sum(day_no_pays) day_no_pays, sum(day_vip_no_pays) day_vip_no_pays, sum(money_vip_day) money_vip_day,time,proportion')->group('time')->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();

                $account =  $model2->where(array('agent_id' => $this->requst_agent_id))->find();
                $agent_url = '?agent_id='.$this->requst_agent_id;
                $is_show = 2;              
            } else {
                $count = $model->count('DISTINCT time');
                $Page = new MyPage($count);                
                $data = $model->field('id,sum(money_common_day) money_common_day, sum(day_pays) day_pays, sum(day_vip_pays) day_vip_pays, sum(day_no_pays) day_no_pays, sum(day_vip_no_pays) day_vip_no_pays, sum(money_vip_day) money_vip_day,time,proportion')->group('time')->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
                
                $account = $model2->field('sum(self_money) self_money,sum(agent_money) agent_money,sum(fc_self_money) fc_self_money,sum(fc_agent_money) fc_agent_money,(self_money + agent_money - fc_self_money - fc_agent_money) alance,sum(wait_draw) wait_draw,sum(total_draw) total_draw')->where(array('is_first' => 1))->find();
            }
        }   
        // echo $model->getLastSql();
        // echo $model2->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());  
        $this->assign('account', $account);
        //代理列表是否显示判断
        $this->assign('flag', $this->auth->check('show_button_jiesuan', session('agent_id')));
        $this->assign('agent_url', $agent_url);
        $this->assign('is_show', $is_show);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';     
        $this->title = '我的结算单';
        $this->display();
    }


    //提现记录
    public function withdraw() {
        $from = I('from');
        $to = I('to');
        $where = $this->where(array(), '', 1);
        $order = 'time desc';
        if ($from) {
            $where['time'] = array('egt', $from);
            $order = '';
        }
        if ($to) {
            $where['time'] = array('elt', $to);
            $order = '';            
        }
        if($from && $to){
            $where['time'] = array(array('egt', $from), array('elt', $to)) ;
        }        
        $model = M('NAgentWithdraw');
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data = $model->where($where)->field()->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show()); 
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';        
        $this->title = '提现记录';
        $this->display();
    }

    //提现申请
    public function api_withdraw_requests() {
        $where['agent_id'] = session('agent_id');
        $account =  M('NAgentMoney')->where($where)->find();
        $data['can_withdraw'] = false;
        $data['message'] = '';
        if ($account) {
            if ($account['alance'] > 0 ) {
                //最近一次提现时间
                $where['time'] = array('egt', date('Y-m-d', time()));
                $res = M('NAgentWithdraw')->field('id, time')->where($where)->find();
                if ($res) {
                   $data['message'] = '您再'.$res['time'].'已申请过提现,请明天再申请!'; 
                } else {
                   $data['can_withdraw'] = true; 
                   $data['can_withdraw_amount'] = $account['alance'] * 100; 
                }
            } else {
                $data['message'] = '余额不足';
            }
        }
        A('Gongju')->echo_json($data);
    }


    //处理提现
    public function api_create_withdraw_request() {
        $where['agent_id'] = session('agent_id');
        $pen_name = M('NAgent')->where($where)->getField('pen_name');
        $model = M('NAgentMoney');
        $account =  $model->where($where)->find();
        $data['alance'] = 0;
        $data['wait_draw'] = array('exp', "wait_draw + $account[alance]");
        $data['time'] = date('Y-m-d H:i:s', time());
        $model->where('id='.$account['id'])->save($data);

        $arr = array(
            'agent_id'      => $where['agent_id'],
            'agent_name'    => $pen_name,
            'web_id'        => session('web_id'),
            'is_first'      => session('is_first'),
            'money'         => $account['alance'],
            'state'         => 1,
            'remark'        => '',
            'update_time'   => $data['time'],
            'time'          => $data['time'],
        );
        M('NAgentWithdraw')->add($arr);
    }

    //代理打款
    function affiliate_withdraw() {
       // echo $this->requst_agent_id;
        $web_id = session('web_id');
        $agent_url = '';         
        if ($web_id) {
            //代理
            if ($this->requst_agent_id) {
                $this->is_agent();
                $where['agent_id'] = $this->requst_agent_id;
                $agent_url = '&agent_id='.$this->requst_agent_id;
            } else {
                $where['is_first'] = 0;
                $where['web_id'] = session('agent_id');
            }            
        } else {
            //管理员
            if ($this->requst_agent_id) {
                $where['agent_id'] = $this->requst_agent_id;
                $agent_url = '&agent_id='.$this->requst_agent_id;
            } else {
                $where['is_first'] = 1;
            }
        }
       
        $account =  M('NAgentMoney')->where($where)->field('sum(wait_draw) wait_draw, sum(total_draw) total_draw')->find();
        $from = I('from');
        $to = I('to');
        $state  = I('state', 1);
        $where['state'] = $state;
        $order = 'time desc';
        if ($from) {
            $where['time'] = array('egt', $from);
            $order = '';
        }
        if ($to) {
            $where['time'] = array('elt', $to);
            $order = '';            
        }
        if($from && $to){
            $where['time'] = array(array('egt', $from), array('elt', $to)) ;
        }        
        $model = M('NAgentWithdraw');
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data = $model->where($where)->field()->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());      
        $this->assign('account', $account);
        $this->assign('state', $state);
        $this->assign('agent_url', $agent_url);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';       
        $this->title = '代理打款';
        $this->display();  
    }

    //处理代理打款
    public function api_create_affiliate_withdraw() {
        if (session('is_web')) {
            $this->is_agent();
        }
        $where['agent_id'] = $this->requst_agent_id;
        // $where['agent_id'] =23;
        $model = M('NAgentWithdraw');
        $res = $model->where($where)->order('time DESC')->find();
        $data['can_withdraw'] = false;           
        $data['message'] = '成功标记打款'; 
        if ($res) {
            $model->where($where)->save(array('state' => 2, 'update_time' => date('Y-m-d H:i:s', time())));
            $model2 = M('NAgentMoney');
            $account =  $model2->where($where)->find();
            $model2->where('id='.$account['id'])->save(array('wait_draw' => array("exp", "wait_draw-$res[money]"),'total_draw' => array("exp", "total_draw+$res[money]")));
            $data['can_withdraw'] = true;            
        } else {
           $data['message'] = '非法请求:打款记录不存在'; 
        }
        A('Gongju')->echo_json($data);        
    }    
}
