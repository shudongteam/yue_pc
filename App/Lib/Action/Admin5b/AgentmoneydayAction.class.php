<?php

//代理账单
class AgentmoneydayAction extends GlobalAction {

    //代理账单管理
    public function index() {
        // $agemoney = D('AgentmoneyView');
        // import('ORG.Util.Page'); // 导入分页类   
        // if ($this->isPost()) {
        //     $where['time'] = $_POST['time'];
        // }
        // if ($_GET[time]) {
        //     $where['time'] = $_GET['time'];
        // }
        // //统计条数开始
        // $where['type'] = array('neq',3);
        // $count = $agemoney->where($where)->count(); // 查询满足要求的总记录数
        // $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        // $money = $agemoney->where($where)->field()->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // $web_id = M('Agent')->where(array('fu_agent'=>0))->field('pen_name,web_id')->select();
        // $this->assign('web_id',$web_id);
        // $this->assign('money', $money);
        // //翻页样式
        // $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        // $show = $Page->show(); // 分页显示输出
        // $this->assign('page', $show); // 赋值分页输出
        // //统计数据
        // $this->tongji();
        // $this->display();

    

        $where = '';
        if ($_REQUEST[time]) {
            $where = "AND A.time = '".$_REQUEST['time']."'";
            $_GET['time'] = $_REQUEST[time];
        }

        $data2 = $this->get_data($where,1);
        import('ORG.Util.Page'); // 导入分页类   
        $Page = new Page(count($data2), 15);    
        $data2 = array_slice($data2, $Page->firstRow, $Page->listRows);
        $this->assign('money', $data2);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        //统计数据
        $this->tongji();
        $this->display();
    }

    //生成账单
    public function add($time) {
        if ($this->isPost()) {
            $time = $_POST[time];
            if (!$time) {
                $this->error("时间不能为空！");
                exit();
            }
            $agent = M('Agent');
            $moneyday = M('AgentMoneyday');
            $pays = M('SystemPay');
            $agent->startTrans(); //开启事物
            $moneyday->startTrans(); //开启事物
            $pays->startTrans(); //开启事物
            //查询充值
            $map['state'] = 2; //成功的
            $map['statistical'] = 0; //0没有统计1统计过了作废
            $map['time'] = array('like', "%$time%");
            $pay = $pays->where($map)->field('agent_id,web_id,money')->select();

            if (is_array($pay)) {
                //数据合并
                $item = array();
                foreach ($pay as $k => $v) {
                    if (!isset($item[$v['agent_id']])) {
                        $item[$v['agent_id']] = $v;
                    } else {
                        $item[$v['agent_id']]['money'] += $v['money'];
                    }
                }

                //更新数据
                $reslut = TRUE;
                $ere = $pays->where($map)->save(array('statistical' => 1));
                //查询更新添加
                foreach ($item as $key => $value) {
                    $myweb = $agent->where(array('agent_id' => $value[agent_id]))->field('agent_id,fu_agent,web_id,pen_name,proportion,money_month,money_total,money_settlement')->find();
                    // echo $agent->getLastSql();
                    // @file_put_contents("moneyday.txt", $agent->getLastSql().PHP_EOL, FILE_APPEND);
                    if (is_array($myweb)) {
                        $mymoney = ceil($value['money'] * $myweb[proportion] / 10); //代理收入
                        $myadas = array();
                        $myada['money_month'] = array('exp', "money_month+$mymoney");
                        $myada['money_total'] = array('exp', "money_total+$mymoney");
                        $myada['money_settlement'] = array('exp', "money_settlement+$mymoney");
                        $isjia = $agent->where(array('agent_id' => $value[agent_id]))->save($myada);
                        //添加收入记录
                        $data['agent_id'] = $value[agent_id];
                        $data['web_id'] = $value[web_id];
                        if ($myweb[fu_agent]) {
                            $data['type'] = 2; //二级代理
                        } else {
                            $data['type'] = 1; //一级代理
                        }
                        $data['proportion'] = $myweb[proportion];
                        $data['money_total'] = $value['money'];
                        $data['money'] = $mymoney;
                        $data['time'] = $time;
                        $isok = $moneyday->add($data);
                        //判断代理类型
                        if ($myweb[fu_agent] != 0 && $myweb[proportion] < 9) {//分成必须小于9成
                            //一级代理提成
                            $yifenqian = $value['money'] / 10; //每一分多少钱
                            $shengyu = 10 - $myweb[proportion] - 1; //剩余份数
                            $zzmoney = ceil($yifenqian * $shengyu);
                            //提成
                            $myadas = array();
                            $myadas['money_month'] = array('exp', "money_month+$zzmoney");
                            $myadas['money_total'] = array('exp', "money_total+$zzmoney");
                            $myadas['money_settlement'] = array('exp', "money_settlement+$zzmoney");
                            $isti = $agent->where(array('agent_id' => $myweb[fu_agent]))->save($myadas);
                            //添加收入记录
                            $dataa['agent_id'] = $myweb[fu_agent];
                            $dataa['web_id'] = $myweb[web_id];
                            $dataa['type'] = 3;
                            $dataa['proportion'] = 10 - 1 - $myweb[proportion];
                            $dataa['money_total'] = $value['money'];
                            $dataa['money'] = $zzmoney;
                            $dataa['time'] = $time;
                            $isook = $moneyday->add($dataa);
                            if (!$isjia && !$isok && !$ere && !$isti && !$isook) {
                                $reslut = FALSE;
                                // echo 1111;
                            }
                        } else {
                            if (!$isjia && !$isok && !$ere) {
                                $reslut = FALSE;                            
                                // echo 2222;
                            }
                        }
                    } else {
                        $reslut = FALSE;
                        echo $value[agent_id];
                    }
                }
                //是否正确
                if ($reslut) {
                    $agent->commit(); //成功则提交  
                    $moneyday->commit(); //成功则提交 
                    $pays->commit(); //成功则提交 
                    $this->success("收工");
                } else {
                    $agent->rollback(); //不成功，则回滚 
                    $moneyday->rollback(); //不成功，则回滚 
                    $pays->rollback(); //不成功，则回滚 
                    $this->error("生成失败");
                }
            } else {
                $this->error("没有数据");
            }
        } else {
            $this->display();
        }
    }

//数据统计
    public function tongji() {
        $tongji = S('tongjis');
        if ($tongji) {
            $this->assign("tongji", $tongji);
        } else {
            $tongji['money_day'] = 0;
            $tongji['money_zuo'] = 0;
            //查询今日充值
            $shijian = date('Y-m-d', time());
            $map['state'] = 2; //成功的
            $map['time'] = array('like', "%$shijian%");
            $pays = M('SystemPay');
            $pay = $pays->where($map)->field('money,web_id,agent_id')->select();
            for ($i = 0; $i < count($pay); $i++) {
                $tongji['money_day'] += $pay[$i]['money'];
            }
            $pay=NULL;
            //昨日数据
            $shijianer = date("Y-m-d", strtotime("-1 day"));
            $map['state'] = 2; //成功的
            $map['time'] = array('like', "%$shijianer%");
            $pay = $pays->where($map)->field('money,web_id,agent_id')->select();
            for ($i = 0; $i < count($pay); $i++) {
                $tongji['money_zuo'] += $pay[$i]['money'];
            }
            S('tongjis', $tongji, 3600);
            $this->assign("tongji", $tongji);
        }
    }

    //删除统计缓存
    public function newtongji() {
        echo S('tongjis', NULL);
    }
    //搜索代理
    public function search() {
        // $agemoney = D('AgentmoneyView');
        // import('ORG.Util.Page'); // 导入分页类  
        // $searchname = isset($_REQUEST['searchname']) ? trim($_REQUEST['searchname']) : ''; 
        // if($searchname){
        //     $where['web_id'] = $searchname;
        //     $_GET['searchname'] = $searchname;
        // }
        // $where['type'] = array('neq',3);
        // //统计条数开始
        // $count = $agemoney->where($where)->count(); // 查询满足要求的总记录数
        // $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        // $money = $agemoney->where($where)->field()->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // $web_id = M('Agent')->where(array('fu_agent'=>0))->field('pen_name,web_id')->select();
        // $this->assign('web_id',$web_id);
        // $this->assign('money', $money);
        // //翻页样式
        // $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        // $show = $Page->show(); // 分页显示输出
        // $this->assign('page', $show); // 赋值分页输出
        // $this->assign('searchname', $searchname);
        // //统计数据
        // $this->tongji();
        // $this->display();

        $searchname = isset($_REQUEST['searchname']) ? trim($_REQUEST['searchname']) : ''; 
        $where = '';
        if($searchname){
            $where = "AND A.web_id = '".$searchname."'";
            $_GET['searchname'] = $searchname;
        }
        $data2 = $this->get_data($where, 2);

        import('ORG.Util.Page'); // 导入分页类   
        $Page = new Page(count($data2), 15);    
        $data2 = array_slice($data2, $Page->firstRow, $Page->listRows);
        $this->assign('money', $data2);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('searchname', $searchname);
        //统计数据
        $this->tongji();
        $this->display();

    }
    //导出所有账单
    public function moneyDump() {
        if(isset($_POST[da]) && isset($_POST[xiao])){
            $where['time'] = array(array('egt', $_POST[da]), array('elt', $_POST[xiao]));
            $where['web_id'] = isset($_REQUEST['cpsname']) ? trim($_REQUEST['cpsname']) : ''; 
        }
        $where['type'] = array('neq',3);
        A('Moneylei')->moneyDump($where);
    }


    private function get_data($where, $flag){
        $data = M('Agent')->where(array('fu_agent'=>0))->field('pen_name,web_id')->select();
        $this->assign('web_id',$data);
        $model = new model();
        if($where){
            if ($flag == 2) {
                $group_by = "group by A.time";
            } else {
                $group_by = "group by A.web_id";
            }
            $sql = "SELECT B.web_id, B.pen_name, sum(A.money_total) money_total, sum(A.money) money, A.time, A.proportion FROM `hezuo_agent_moneyday` A left join `hezuo_agent` B ON A.web_id = B.web_id WHERE A.type<>3 AND B.fu_agent = 0 {$where} {$group_by} order by A.time desc";
        } else {
            $sql = "SELECT B.web_id, B.pen_name, sum(A.money_total) money_total, sum(A.money) money, A.time, A.proportion FROM `hezuo_agent_moneyday` A left join `hezuo_agent` B ON A.web_id = B.web_id WHERE A.type<>3 AND B.fu_agent = 0 group by A.time, B.web_id order by A.time desc";
        }
        $data = $model->query($sql); 
        foreach($data as &$dataList){
          $dataList['type'] = 1;
        }
        return $data;
    }
}
