<?php

//代理钱
class MoneyAction extends GlobalAction {

    //代理收款信息
    public function information() {
        $web = M('Agent');
        if ($this->isPost()) {
            $web->create();
            $web->where(array('agent_id' => $this->to[agent_id]))->save();
            $this->success("修改成功");
        } else {
            $myweb = $web->where(array('agent_id' => $this->to[agent_id]))->field('phone,qq,email,name,bank,bank_number,open_account')->find();
            $this->assign('myweb', $myweb);
            $this->display();
        }
    }

    //申请提现
    public function settlement() {
        $where['agent_id'] = $this->to['agent_id'];
        if ($this->isPost()) {
            $agent = M('Agent');
            $agent->startTrans(); //开启事物
            $mywebs = $agent->where($where)->field('money_settlement')->find();
            if (is_array($mywebs) && $mywebs[money_settlement] >= $_POST[money] && is_numeric($_POST[money])) {
                //处理数据
                $myada['money_settlement'] = array('exp', "money_settlement-$_POST[money]");
                $myada['money_has'] = array('exp', "money_has+$_POST[money]");
                $isjia = $agent->where($where)->save($myada);
                //添加记录了
                $data['agent_id'] = $this->to[agent_id];
                $data['pen_name'] = $this->to[pen_name];
                $data['money'] = $_POST[money];
                $data['beizhu'] = $_POST[beizhu];
                $data['time'] = date('Y-m-d H:i:s', time());
                $isok = M('AgentSettlement')->add($data);
                if ($isjia && $isok) {
                    $agent->commit(); //成功则提交  
                    $this->success("申请成功");
                } else {
                    $agent->rollback(); //不成功，则回滚 
                    $this->error("申请失败");
                }
            } else {
                $this->error("您木有那么多钱哦！");
            }
        } else {

            $myweb = M('Agent')->where($where)->field('money_month,money_total,money_settlement,money_has')->find();
            $this->assign('myweb', $myweb);
            $this->display();
        }
    }

    //提现记录
    public function money() {
        $websett = M('AgentSettlement');
        if ($this->isPost()) {
            $where['state'] = $_POST[state];
        }
        $where['agent_id'] = $this->to[agent_id];
        import('ORG.Util.Page'); // 导入分页类       
        $count = $websett->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $websett->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('content', $content);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

}
