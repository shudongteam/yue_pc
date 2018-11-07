<?php

//站点折线图
class WebfigureAction extends GlobalAction {

    public function index() {
        $webid = $this->to['web_id'];
        $tenday = date('Y-m-d', strtotime('-10 days'));
        $now = date('Y-m-d', strtotime('-1 days'));
        $result = M("AgentMoneyday")->where(array('web_id' => $webid, 'time' => array('egt', $tenday), 'type' => array('neq', 3)))->group('time')->field('web_id,time,sum(money_total) as total')->order('time')->select();
        foreach ($result as $value) {
            $key = $value['web_id'];
            //如果没有定义且时间大于当前10天日期; 前一段时间没有数据，则前面数据附值为0

            if ((!isset($data) && ($value['time'] > $tenday)) || ($lasttime < $value['time'])) {
                if ($lasttime) {
                    $lasttime = date('Y-m-d', strtotime('+1 days', strtotime($lasttime)));
                }
                $data = ($this->setNullData($value['time'], $lasttime, $data));
            }
            $data[] = (int) $value['total'];
            $lasttime = $value['time'];
        }

        $web = M('Web')->where(array('web_id' => $webid))->field('web_id,web_name')->find();

        if (count($data) < 10) {
            $data = $this->setNullData($now, $lasttime, $data);
        }

        $arr = json_encode($data);

        $timess = array_map(function ($time) {
            return date('m-d', $time);
        }, range(strtotime($tenday), strtotime($now), 86400));
        $this->assign('timess', $timess);
        $this->assign('lasttime', end($timess));

        $this->assign('arr', $arr);
        $this->assign('web', $web);
        $this->assign('tenday', str_replace('-', ', ', $tenday));
        $this->display();
    }

    //为没有数据的时间段附值为0
    public function setNullData($date, $lastdate, $data) {

        //如果第十天的那天没有数据，则从0开始附值
        if ($lastdate < 1) {
            $lastdate = date('Y-m-d', strtotime('-10 days'));
        }
        $count = (strtotime($date) - strtotime($lastdate)) / 86400;

        for ($i = 1; $i <= $count; $i++) {
            $data[] = 0;
        }
        return $data;
    }

}
