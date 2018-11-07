<?php

//充值
class PayAction extends GlobalAction {

    public function index() {
        $dd = D('PayView');
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
        $map['agent_id'] = $this->to[agent_id];
        $count = $dd->where($map)->field('user_id')->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数   
        $charge = $dd->where($map)->field('user_name,pen_name,type,trade,transaction,money,readmoney,state,time')->limit($Page->firstRow . ',' . $Page->listRows)->order('time desc')->select();
        $this->assign('charge', $charge); // 赋值分页输出
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function add() {
        if ($this->isPost()) {
            $uuu = M('User');
            $user = $uuu->where(array('user_name' => $_POST[user_name], 'web_id' => $this->to[web_id]))->field('user_id,pen_name')->find();
            if (is_array($user)) {
                $data['user_id'] = $user['user_id'];
                $data['pen_name'] = $user['pen_name'];
                $data['agent_id'] = $this->to['agent_id'];
                $data['money'] = $_POST[money];
                $data['beizhu'] = $this->to[pen_name] . '手动充值';
                $data['time'] = date('Y-m-d H:i:s', time());
                $id = M('SystemHandpay')->add($data);
                //用户数据
                $alance = $_POST[money] * 100;
                $datas['alance'] = array('exp', "alance+$alance"); //余额阅读币
                $datas['money'] = array('exp', "money+$_POST[money]"); //总充值
                $isus = $uuu->where(array('user_id' => $user[user_id]))->save($datas);
                if ($isus && $id) {
                    $this->success("充值成功！");
                } else {
                    $this->error("系统错误");
                }
            } else {
                $this->error("没有找到该账户!");
            }
        } else {
            $this->display();
        }
    }

}
