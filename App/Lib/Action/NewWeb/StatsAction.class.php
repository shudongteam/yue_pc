<?php

//数据统计
class StatsAction extends GlobalAction {
    private $cache_key;

    public function _initialize() {
        parent::_initialize();   
        if ($this->requst_agent_id) {
            $this->assign('requst_agent_id', $this->requst_agent_id);
            $this->assign('agent_url', '?agent_id='.$this->requst_agent_id);
        } else {
            $this->assign('agent_url', '');
        }
        $this->cache_key = session('agent_id').'_'.$this->requst_agent_id;
    }

    //订单统计
    public function index() {
        $this->title = '数据统计';
        $this->display();
    }

    //用户统计
    public function members() {
        $this->title = '数据统计';        
        $this->display();
    }


    //获取今天订单数据
    public function api_get_stats_today() {

        // {"paid_amount":0,"paid_order_count":0,"unpaid_order_count":0,"paid_user_count":0,"welth_order_paid_amount":0,"welth_order_paid_count":0,"welth_order_unpaid_count":0,"welth_order_paid_user_count":0,"vip_order_paid_amount":0,"vip_order_paid_count":0,"vip_order_unpaid_count":0,"vip_order_paid_user_count":0,"welth_order_avg_user_paid_amount":0,"welth_order_completion_rate":0,"vip_order_avg_user_paid_amount":0,"vip_order_completion_rate":0}
        $today = date('Y-m-d',time())." 00:00:00";
        $where = $this->where(array('time' => array('egt', $today)));
        $res = $this->get_order_data($where);
        $data = array(
            "paid_amount"                   => $res[0],
            "welth_order_paid_amount"       => $res[1],
            "welth_order_paid_count"        => $res[2],
            "welth_order_unpaid_count"      => $res[3],
            "vip_order_paid_amount"         => $res[4],
            "vip_order_paid_count"          => $res[5],
            "vip_order_unpaid_count"        => $res[6],
            "welth_order_completion_rate"   => $res[7],
            "vip_order_completion_rate"     => $res[8],
        );
        A('Gongju')->echo_json($data);
    }

    //获取其他天订单数据
    public function api_get_stats_otherDay() {
        $res = S('yestoday'.$this->cache_key);
        $res2 = S('monthday'.$this->cache_key);
        $res3 = S('totalday'.$this->cache_key);
  
        // $res = null;
        // $res2 = null;
        // $res3 = null;
        $expired = strtotime(date('Y-m-d',strtotime('+1 day'))) - time();
        $today = date('Y-m-d',time())." 00:00:00";
        if (!$res) {
            //昨天
            $yestoday = date('Y-m-d', strtotime('-1 day'))." 00:00:00";
            $where = $this->where(array('time'=> array(array('egt', $yestoday), array('lt', $today))));
            $res = $this->get_order_data($where);
            S('yestoday'.$this->cache_key, $res, $expired);
        }

        if (!$res2) {
            //本月充值 (不含当日) 
            $oneday = date('Y-m',time())."-01 00:00:00";
            $where = $this->where(array('time' => array(array('egt', $oneday), array('lt', $today))));
            $res2 = $this->get_order_data($where);
            S('monthday'.$this->cache_key, $res2, $expired);
        }

        if (!$res3) {
            //累计充值 (不含当日) 
            $where = $this->where(array('time' => array(array('lt', $today))));
            $res3 = $this->get_order_data($where);
            S('totalday'.$this->cache_key, $res3, $expired);
        }

        $data = array(
            //昨天
            "y_paid_amount"                   => $res[0],//昨日充值
            "y_welth_order_paid_amount"       => $res[1],//普通充值
            "y_welth_order_paid_count"        => $res[2],//已支付
            "y_welth_order_unpaid_count"      => $res[3],//未支付
            "y_vip_order_paid_amount"         => $res[4],//年费VIP会员
            "y_vip_order_paid_count"          => $res[5],//年费VIP会员已支付
            "y_vip_order_unpaid_count"        => $res[6],//年费VIP会员未支付
            "y_welth_order_completion_rate"   => $res[7],//普通完成率
            "y_vip_order_completion_rate"     => $res[8],//VIP完成率

            //月
            "m_paid_amount"                   => $res2[0],
            "m_welth_order_paid_amount"       => $res2[1],
            "m_welth_order_paid_count"        => $res2[2],
            "m_welth_order_unpaid_count"      => $res2[3],
            "m_vip_order_paid_amount"         => $res2[4],
            "m_vip_order_paid_count"          => $res2[5],
            "m_vip_order_unpaid_count"        => $res2[6],
            "m_welth_order_completion_rate"   => $res2[7],
            "m_vip_order_completion_rate"     => $res2[8],

            //总计
            "t_paid_amount"                   => $res3[0],
            "t_welth_order_paid_amount"       => $res3[1],
            "t_welth_order_paid_count"        => $res3[2],
            "t_welth_order_unpaid_count"      => $res3[3],
            "t_vip_order_paid_amount"         => $res3[4],
            "t_vip_order_paid_count"          => $res3[5],
            "t_vip_order_unpaid_count"        => $res3[6],
            "t_welth_order_completion_rate"   => $res3[7],
            "t_vip_order_completion_rate"     => $res3[8],
            );

        A('Gongju')->echo_json($data);
    }


    //获取最近30天(不包含今天)数据,缓存时间为一天
    public function api_get_daily_stats() {
        // echo 'thirtyday'.$this->cache_key;
            $data = S('thirtyday'.$this->cache_key);
             // $data = null;
            $expired = strtotime(date('Y-m-d',strtotime('+1 day'))) - time();
            if (!$data) {
                //今天
                $today = date('Y-m-d',time());
                //30天前
                $lastday = date('Y-m-d', strtotime('-30 day'));


                $where['time'] = array(array('egt', $lastday), array('lt', $today));
                $money = M('NMoneyTongji');
                if (session('web_id')) {
                    if ($this->requst_agent_id) {
                        $this->is_agent();
                        $where['agent_id'] = $this->requst_agent_id; 
                    } else {
                        $where['agent_id'] = session('agent_id'); 
                    }  
                    $res = $money->where($where)->field('id,money_common_day,day_pays,day_vip_pays,day_no_pays,day_vip_no_pays,money_vip_day,time')->order('id DESC')->select();
                    // echo $money->getLastSql();        
                } else {
                    if ($this->requst_agent_id) {
                        $where['agent_id'] = $this->requst_agent_id;
                        $res = $money->where($where)->field('id,money_common_day,day_pays,day_vip_pays,day_no_pays,day_vip_no_pays,money_vip_day,time')->order('id DESC')->select();
                    } else {
                        $res = $money->where($where)->field('id,sum(money_common_day) money_common_day, sum(day_pays) day_pays, sum(day_vip_pays) day_vip_pays, sum(day_no_pays) day_no_pays, sum(day_vip_no_pays) day_vip_no_pays, sum(money_vip_day) money_vip_day,time')->group('time')->order('id DESC')->select();
                    }  
                    // echo $money->getLastSql();
                }    
                $data = array();
                foreach ($res as $key => $value) {
                    $data[$key] = array(
                        'id'                                 => $value['id'],
                        'date'                               => strtotime($value['time']),
                        //每天总充值金额
                        'paid_amount'                        => ($value['money_common_day'] + $value['money_vip_day']) * 100,
                        //每天充值成功笔数
                        'paid_order_count'                   => $value['day_pays'] + $value['day_vip_pays'],
                        //每天充值失败笔数
                        'unpaid_order_count'                 => $value['day_no_pays'] + $value['day_vip_no_pays'],
                        //普通充值金额
                        'welth_order_paid_amount'            => $value['money_common_day'] * 100,
                        //普通充值成功笔数
                        'welth_order_paid_count'             => $value['day_pays'],
                        //普通充值失败笔数
                        'welth_order_unpaid_count'           => $value['day_no_pays'],
                        //VIP充值金额
                        'vip_order_paid_amount'              => $value['money_vip_day'] * 100,
                        //VIP充值成功笔数
                        'vip_order_paid_count'               => $value['day_vip_pays'],
                        //VIP充值失败笔数
                        'vip_order_unpaid_count'             => $value['day_vip_no_pays'],
                        //普通成功率, 成功/总笔数
                        'welth_order_completion_rate'        => 0,
                        //VIP充值成功率, 成功/总笔数
                        'vip_order_completion_rate'          => 0,
                    );
                }
                S('thirtyday'.$this->cache_key, $data, $expired);
            }
        A('Gongju')->echo_json($data);
    }

    //获取订单数据
    private function get_order_data($where = array()){
        $money = M('NSystemPay');
        $res = $money->where($where)->select();
        // echo $money->getLastSql();
        $data = array();
        $paid_amount = 0;//今日充值
        $welth_order_paid_amount = 0;//普通充值
        $welth_order_paid_count = 0;//已支付
        $welth_order_unpaid_count = 0;//未支付
        $vip_order_paid_amount = 0;//年费VIP会员
        $vip_order_paid_count = 0;//年费VIP会员已支付
        $vip_order_unpaid_count = 0;//年费VIP会员未支付
        $welth_order_completion_rate = 0;//普通完成率
        $vip_order_completion_rate = 0;//VIP完成率
        if($res){
            foreach ($res as $key => $value) {
                if ($value['state'] == 2) {
                    $paid_amount += $value['money'];
                    if ($value['is_members'] == 1) {
                        $vip_order_paid_count++;
                        $vip_order_paid_amount += $value['money']; 
                    } else {
                        $welth_order_paid_count++;
                        $welth_order_paid_amount += $value['money'];
                    }
                } else {
                    if ($value['is_members'] == 1) {
                        $vip_order_unpaid_count++;
                    } else {
                        $welth_order_unpaid_count++;
                    }
                }
            }
            //vip 总充值笔数(成功+失败)
            $vip_total_count = $vip_order_unpaid_count+$vip_order_paid_count;
            //成功率, 成功/总笔数
            $vip_order_completion_rate = $vip_total_count ? $vip_order_paid_count/$vip_total_count : 0;
            //普通 总充值笔数(成功+失败)
            $welth_total_count = $welth_order_unpaid_count+$welth_order_paid_count;
            //成功率 总充值笔数(成功+失败)
            $welth_order_completion_rate = $welth_total_count ? $welth_order_paid_count/$welth_total_count : 0;

        }
        $data = array(
            $paid_amount*100,
            $welth_order_paid_amount*100,
            $welth_order_paid_count,
            $welth_order_unpaid_count,
            $vip_order_paid_amount*100,
            $vip_order_paid_count,
            $vip_order_unpaid_count,
            $welth_order_completion_rate,
            $vip_order_completion_rate,
        );
        return $data;        
    }

    //今日新增用户
    public function api_get_members_today() {
        //{"new_member_count":0,"subscribed_count":0,"male_count":0,"female_count":0,"paid_count":0,"subscribe_rate":0,"pay_rate":0}
        $today = date('Y-m-d',time())." 00:00:00";
        $where = $this->where(array('hezuo_n_user.time' => array('egt', $today)), 'hezuo_n_user');
        $res = $this->get_member_data($where);
        $data = array(
            "new_member_count"  => $res[0],
            "subscribed_count"  => $res[1],
            "male_count"        => $res[2],
            "female_count"      => $res[3],
            "paid_count"        => $res[4],
        );

        A('Gongju')->echo_json($data);
    }

    //获取其他天订单数据
    public function api_get_members_otherDay() {
        $res = S('yestoday_user'.$this->cache_key);
        $res2 = S('monthday_user'.$this->cache_key);
        $res3 = S('totalday_user'.$this->cache_key);
        // $res = '';
        // $res2 = '';
        // $res3 = '';
        $expired = strtotime(date('Y-m-d',strtotime('+1 day'))) - time();
        $today = date('Y-m-d',time())." 00:00:00";
        if (!$res) {
            //昨天
            $yestoday = date('Y-m-d', strtotime('-1 day'))." 00:00:00";
            $where = $this->where(array('hezuo_n_user.time' => array(array('egt', $yestoday), array('lt', $today))), 'hezuo_n_user');
            // var_dump($where);
            $res = $this->get_member_data($where);
            // var_dump($res);
            // echo 'yestoday_user'.$this->cache_key;
            S('yestoday_user'.$this->cache_key, $res, $expired);
        }

        if (!$res2) {
            //本月充值 (不含当日) 
            $oneday = date('Y-m',time())."-01 00:00:00";
            $where2 = $this->where(array('hezuo_n_user.time' => array(array('egt', $oneday), array('lt', $today))),'hezuo_n_user');
            $res2 = $this->get_member_data($where2);
            S('monthday_user'.$this->cache_key, $res2, $expired);
        }

        if (!$res3) {
            //累计充值 (不含当日) 
            $where3 = $this->where(array('hezuo_n_user.time' => array(array('lt', $today))), 'hezuo_n_user');
            $res3 = $this->get_member_data($where3);
            S('totalday_user'.$this->cache_key, $res3, $expired);
        }

        $data = array(
            //昨天
            "y_new_member_count"  => $res[0],
            "y_subscribed_count"  => $res[1],
            "y_male_count"        => $res[2],
            "y_female_count"      => $res[3],
            "y_paid_count"        => $res[4],

            //月
            "m_new_member_count"  => $res2[0],
            "m_subscribed_count"  => $res2[1],
            "m_male_count"        => $res2[2],
            "m_female_count"      => $res2[3],
            "m_paid_count"        => $res2[4],

            //总计
            "t_new_member_count"  => $res3[0],
            "t_subscribed_count"  => $res3[1],
            "t_male_count"        => $res3[2],
            "t_female_count"      => $res3[3],
            "t_paid_count"        => $res3[4],
            );

        A('Gongju')->echo_json($data);
    }

    //获取新增用户数据
    private function get_member_data($where = array()) {

        $Model = M('NUser');
        $res = $Model->join(' hezuo_n_user_info ON hezuo_n_user.user_id = hezuo_n_user_info.user_id')->where($where)->field('hezuo_n_user_info.user_id, hezuo_n_user_info.sex')->select();
        // echo $Model->getLastSql();
        // $res = $user->where($where)->field('user_id, sex')->select();
        //总数
        $new_member_count = 0;
        //未知
        $subscribed_count = 0;
        //男性
        $male_count = 0;
        //女性
        $female_count = 0;

        $user_ids = '';
        foreach ($res as $value) {
            $new_member_count++;
            if ($value['sex'] == 1) {
                $male_count++;
            } elseif($value['sex'] == 2){
                $female_count++;
            } else {
                $subscribed_count++;
            }
            $user_ids .= $value['user_id'].",";
        }
        //支付人数
        $paid_count = (int)M('NSystemPay')->where(array('user_id' => array('in', rtrim($user_ids, ',')), 'state' => 2))->count('DISTINCT user_id');
        // echo M('NSystemPay')->getLastSql();
        $data = array(
            $new_member_count,
            $subscribed_count,
            $male_count,
            $female_count,
            $paid_count,
        );
        // var_dump($data);
        return $data;
    }

    //获取30天用户数据
    public function api_get_members_stats() {
        //{"id":"2787151","user_id":"23414","upline_uid":"15797","user_type":"agent","date":"1507564800","new_member_count":"0","subscribed_count":"0","paid_count":"0","male_count":"0","female_count":"0","created_at":"1507657029","subscribe_rate":0,"new_member_diff":"0","new_member_inc_rate":"0.0000","pay_rate":0},
        $data = S('thirtyday_user'.$this->cache_key);
        // $data =null;
        $expired = strtotime(date('Y-m-d',strtotime('+1 day'))) - time();
        if (!$data) {
            //今天
            $today = date('Y-m-d',time());
            //30天前
            $lastday = date('Y-m-d', strtotime('-30 day'));

            $where['time'] = array(array('egt', $lastday), array('lt', $today));
            $user = M('NUserTongji');
            $type == 0;
            if (session('web_id')) {
                if ($this->requst_agent_id) {
                    $this->is_agent();
                    $where['agent_id'] = $this->requst_agent_id; 
                } else {
                    $where['agent_id'] = session('agent_id'); 
                }  
                $res = $user->where($where)->field('reg,man,woman,unknow,pays,time')->order('id DESC')->select();                
            } else {
                if ($this->requst_agent_id) {
                    $where['agent_id'] = $this->requst_agent_id;
                    $res = $user->where($where)->field('reg,man,woman,unknow,pays,time')->order('id DESC')->select();                
                } else {
                    $res = $user->where($where)->field('sum(reg) reg, sum(man) man, sum(woman) woman, sum(unknow) unknow, sum(pays) pays,time')->group('time')->order('id DESC')->select();
                }                 
            }     
            // echo $user->getLastSql();
            $data = array();
            foreach ($res as $key => $value) {
                $data[$key] = array(
                    "date"              => strtotime($value['time']),
                    "new_member_count"  => $value['reg'],
                    "subscribed_count"  => $value['unknow'],
                    "male_count"        => $value['man'],
                    "female_count"      => $value['woman'],
                    "paid_count"        => $value['pays'],
                );
            }
            S('thirtyday_user'.$this->cache_key, $data, $expired);
        }
        A('Gongju')->echo_json($data);
    }
}
