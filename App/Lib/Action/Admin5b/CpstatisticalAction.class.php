<?php

//cp财务统计
class CpstatisticalAction extends GlobalAction {

    function index() {
       header("Content-type: text/html; charset=utf-8");
        if ($this->isPost()) {
            $cpinfo = M('Cp')->where(array("cp_id"=>$_POST[cp_id]))->find();
            $pen_name = $cpinfo[pen_name];
        	$month = $_POST['month'];
        	$where['time'] = array('like',"%$month%");
            $where['cp_id'] = $_POST[cp_id];
            $cc = M('CpMoneyday');
            $zong = $cc->where($where)->field('consumption,time')->select();
            for ($i = 0; $i < count($zong); $i++) {
                $money += $zong[$i][consumption];
            }
            $ymdata= file_get_contents("http://www.ymbook.cn/Home/Ymput/ymoutput?pen_name=".$pen_name."&month=".$month);
            // $ju = "http://www.ymbook.cn/Home/Ymput/ymoutput?pen_name=$pen_name&month=$month";
            // echo $ju;
            $ymdata = json_decode($ymdata);
            //print_r($ymdata);
            $ymmoney = $ymdata->money;//缘梦
            $dddata= file_get_contents("http://www.dadishumeng.com/Home/Ddput/ddoutput?pen_name=".$pen_name."&month=".$month);
            $dddata = json_decode($dddata);
            $ddmoney = $dddata->money;//大地
            //print_r($dddata);
            $tmoney = $money + $ymmoney +$ddmoney;
            $money9 = round($tmoney*0.9/100,2);
            $money955 = round($tmoney*0.9/200,2);
            $this->assign('ymmoney', $ymmoney);
            $this->assign('ddmoney', $ddmoney);
            $this->assign('tmoney', $tmoney);
            $this->assign('month', $_POST[month]);
            $this->assign('money', $money);
            $this->assign('money9', $money9);
            $this->assign('money955', $money955);
            $mycp = M('Cp')->where(array('cp_id' => $_POST[cp_id]))->field('cp_id,pen_name')->find();
            $this->assign('mycp', $mycp);
        }
        //调用工具
        $cp = M('Cp')->where("type=0")->field('cp_id,pen_name')->select();
        $this->assign('cps', $cp);
        $this->display();
    }

    //添加
    public function add($cp,$month) {
        if ($this->isPost()) {
            $mycp = M('Cp')->where(array('cp_id' => $cp))->field('cp_id,pen_name')->find();
            $num = M('CpSettlement')->where(array('cp_id' => $cp,'month'=>$month))->count();
            if ($mycp&&$num==0) {

                $data['cp_id'] = $cp;
                $data['pen_name'] = $mycp[pen_name];
                $data['consumption'] = $_POST[consumption];
                $data['money'] = $_POST[money];
                if($_POST[cost]){
                   $data['cost'] = $_POST[cost]; 
                }else{
                    $data['cost'] = 0;
                }  
                $data['beizhu'] = $_POST[beizhu];
                $data['month'] = $month;
                $data['time'] = date('Y-m-d H:i:s', time());
                //print($data);exit;
                $isok = M('CpSettlement')->add($data);
                if ($isok) {
                    $this->success("添加成功");
                } else {
                    $this->error("添加错误");
                }
            } else{
            	$this->error("账单已存在，请勿重复添加");
            }
            // else {
            //     $this->error("没有该cp");
            // }
        }
    }

}
