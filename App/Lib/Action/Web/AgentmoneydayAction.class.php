<?php

//代理账单
class AgentmoneydayAction extends GlobalAction {
    //代理账单管理
    public function index() {
        $agemoney = M('AgentMoneyday');
        import('ORG.Util.Page'); // 导入分页类   
        $where = "";
        if ($_REQUEST['time']) {
            // $where['time'] = $_GET['time'];
            $where ="am.time ='".$_REQUEST['time']."'and";
            $_GET['time'] = $_REQUEST['time'];
        }
        $agent_id =  $this->to[agent_id];//当前账号agent_id
        //echo $agent_id;
       // $where['agent_id'] = $this->to[agent_id];
       // $arr = M('agent')->where(array("fu_agent"=>$agent_id))->select();//二级代理查询
        //统计条数开始
        $sql2 = "SELECT count(*) as count from hezuo_agent as a inner JOIN hezuo_agent_moneyday AS am ON a.agent_id = am.agent_id where $where a.fu_agent = $agent_id or $where a.agent_id = $agent_id AND am.type <> 3";
        $M = M();
        $count =$M->query($sql2);//获取总记录数
        $Page = new Page($count[0]['count'], 10); // 实例化分页类 传入总记录数和每页显示的记录数   
        $first = $Page->firstRow;
        $end = $Page->listRows;
        $sql = "SELECT * from hezuo_agent as a inner JOIN hezuo_agent_moneyday AS am ON a.agent_id = am.agent_id where $where a.fu_agent = $agent_id   AND am.type <> 3 or $where a.agent_id = $agent_id  AND am.type <> 3 order by time  desc limit $first,$end";
       //  echo $count[0]['count'];
       //  echo"<br>";
       // echo $sql;
       // echo"<br>";
       // echo $sql2;
        $money = $M->query($sql);//查询
        $this->assign('money', $money);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

}
