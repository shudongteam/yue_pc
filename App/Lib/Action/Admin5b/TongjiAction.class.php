<?php

//统计管理
class TongjiAction extends GlobalAction {

    public function index() {
        $tongji = S('tongji');
        if ($tongji) {
            $this->assign("tongji", $tongji);
        } else {
            //所有数据
            $result = M("Agent")->field('money_month,money_total,money_settlement,agent_id,web_id,pen_name')->select();

            $data = $agent = $web = array();
            $total['money_month'] = $total['money_total'] = $total['money_settlement'] = $total['money_today'] = 0;
            foreach ($result as $value) {
                $webid = $value['web_id'];
                $agentid = $value['agent_id'];
                $money_month = $value['money_month'];
                $money_total = $value['money_total'];
                $money_settlement = $value['money_settlement'];

                //总统计
                $total['money_month'] += $money_month; //当月
                $total['money_total'] += $money_total; //累计
                $total['money_settlement'] += $money_settlement; //未结算
                //站点
                $web[$webid]['money_month'] += $money_month;
                $web[$webid]['money_total'] += $money_total;
                $web[$webid]['money_settlement'] += $money_settlement;

                //代理
                $agent[$agentid]['money_month'] += $money_month;
                $agent[$agentid]['money_total'] += $money_total;
                $agent[$agentid]['money_settlement'] += $money_settlement;
                $agent[$agentid]['user'] = $value['pen_name'];
            }

            $ss = M("SystemPay");
            $today = $ss->field("money, agent_id, web_id")->where(array('state' => 2, 'statistical' => 0, 'date(time)' => date('Y-m-d')))->select();
            foreach ($today as $value) {
                $web[$value['web_id']]['money_today'] += $value['money'];
                $agent[$value['agent_id']]['money_today'] += $value['money'];
                $total['money_today'] += $value['money'];
            }

            $allweb = array();
            $webs = M('Web')->field('web_id,web_name,web_url')->select();
            foreach ($webs as $value) {
                $webid = $value['web_id'];
                if ($web[$webid]['money_today'] || $web[$webid]['money_month']) {
                    $allweb[$webid] = array(
                        'web_name' => $value['web_name'],
                        'web_url' => $value['web_url'],
                        'money_month' => $web[$webid]['money_month'] ? $web[$webid]['money_month'] : 0,
                        'money_total' => $web[$webid]['money_total'] ? $web[$webid]['money_total'] : 0,
                        'money_settlement' => $web[$webid]['money_settlement'] ? $web[$webid]['money_settlement'] : 0,
                        'money_today' => $web[$webid]['money_today'] ? $web[$webid]['money_today'] : 0,
                    );
                }
            }
            $allagent = array();
            $agents = M('Agent')->field("agent_id, pen_name")->select();
            foreach ($agents as $value) {
                $agentid = $value['agent_id'];
                if ($agent[$agentid]['money_today'] || $agent[$agentid]['money_month']) {
                    $allagent[] = array(
                        'user' => $value['pen_name'],
                        'money_month' => $agent[$agentid]['money_month'] ? $agent[$agentid]['money_month'] : 0,
                        'money_total' => $agent[$agentid]['money_total'] ? $agent[$agentid]['money_total'] : 0,
                        'money_settlement' => $agent[$agentid]['money_settlement'] ? $agent[$agentid]['money_settlement'] : 0,
                        'money_today' => $agent[$agentid]['money_today'] ? $agent[$agentid]['money_today'] : 0,
                    );
                }
            }

            $data = array('total' => $total, 'web' => $allweb, 'agent' => $allagent);
            S('tongji', $data, 3600);
            $this->assign("tongji", $data);
        }
        $this->display();
    }

    //删除统计缓存
    public function newtongji() {
        echo S('tongji', NULL);
    }

}
