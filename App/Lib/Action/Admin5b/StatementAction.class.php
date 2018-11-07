<?php

//充值
class StatementAction extends GlobalAction {

    public function index() {
        $dd = D('StatementView');
        import('ORG.Util.Page'); // 导入分页类
        if ($this->isPost()) {
            if ($_POST[type] == 1) {
                $map['user_name'] = array('like', "%$_POST[keyword]%");
            } elseif ($_POST[type] == 2) {
                $map['pen_name'] = array('like', "%$_POST[keyword]%");
            } else {
                $map['trade'] = array('like', "%$_POST[keyword]%");
            }
        }
        // $map['agent_id'] = $this->to[agent_id];
        $map['state'] = 2;
        $count = $dd->where($map)->field('user_id')->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数   
        $charge = $dd->where($map)->field('user_name,web_name,web_url,webqq,weixin,pen_name,type,trade,transaction,money,readmoney,state,time')->limit($Page->firstRow . ',' . $Page->listRows)->order('time desc')->select();
        $this->assign('charge', $charge); // 赋值分页输出
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
}
