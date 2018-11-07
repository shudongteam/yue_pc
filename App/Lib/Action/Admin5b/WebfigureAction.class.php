<?php

//站点折线图
class WebfigureAction extends GlobalAction {

    public function index() {
        $tenday = date('Y-m-d', strtotime('-10 days'));
        $now = date('Y-m-d', strtotime('-1 days'));
        $result = M("AgentMoneyday")->group('web_id,time')->field('web_id,time,sum(money_total) as total')->order('time')->where(array('time' => array('BETWEEN', "$tenday,$now"), 'type' => array('neq', 3)))->select();
        $data = array();
        foreach ($result as $value) {
            $key = $value['web_id'];
            //如果没有定义且时间大于当前10天日期; 前一段时间没有数据，则前面数据附值为0        	
            if ((!isset($data[$key]) && ($value['time'] > $tenday)) || ((end($data[$key]) > 0) && (end($data[$key]) < $value['time']))) {
                $data[$key] = ($this->setNullData($value['time'], $data[$key]));
            }

            $data[$key][$value['time']] = (int) $value['total'];
        }


        $web = M('Web')->where(array('uid' => 1))->field('web_id,web_name')->select();
        $arr = array();

        foreach ($web as $value) {
            $key = $value['web_id'];
            $arr[$key]['name'] = $value['web_name'];
            //如果某个时间到目前没有数据全附值
            if (count($data[$key]) < 10) {
                $val = $this->setNullData($now, $data[$key]);
            } else {
                $val = $data[$key];
            }

            $arr[$key]['value'] = json_encode($this->getVal($val));
        }

        $timess = array_map(function ($time) {
            return date('Y-m-d', $time);
        }, range(strtotime($tenday), strtotime($now), 86400));

        $this->assign('timess', $timess);
        $this->assign('lastday', end($timess));

        $this->assign('arr', $arr);
        $this->display();
    }

    private function getVal($data) {
        $returndata = array();
        foreach ($data as $value) {
            $returndata[] = $value;
        }

        return $returndata;
    }

    //为没有数据的时间段附值为0
    public function setNullData($enddate, $data) {
        //如果第十天的那天没有数据，则从0开始附值
        if (!$data) {
            $startdate = date('Y-m-d', strtotime('-11 days'));
        } else {
            $startdate = end(array_keys($data));
        }

        $count = (strtotime($enddate) - strtotime($startdate)) / 60 / 60 / 24;

        for ($i = 1; $i <= $count; $i++) {
            $key = date('Y-m-d', strtotime("+$i days", strtotime($startdate)));
            $data[$key] = 0;
        }

        return $data;
    }

}
