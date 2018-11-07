<?php

//代理管理
class AgentAction extends GlobalAction {

    //代理首页
    public function index() {
        if ($this->isPost()) {
            $where[$_POST['search']] = array('like', "%$_POST[keyword]%");
        }
        $ad = M('Agent');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $where['fu_agent'] = $this->to['agent_id']; //代理
        $where['web_id'] = $this->to[web_id];
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $user = $ad->where($where)->field('agent_id,web_id,user_name,pen_name,money_month,money_total,money_settlement,money_has,phone,qq,email')->order('agent_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('user', $user);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('search', $_POST['search']); // 赋值分页输出
        $this->display();
    }

    //添加代理
    public function add() {
        if ($this->isPost()) {
            $user_name = trim($_POST[user_name]);
            if (!empty($user_name)) {
                $web = M('Agent');
                $yanzhen = $web->where(array('user_name' => $_POST[user_name]))->field('agent_id')->find();
                if (is_array($yanzhen)) {
                    $this->error("代理已经存在");
                    exit();
                }
                if($_POST['proportion']>=1 && $_POST['proportion']<9){
                    $proportion = $_POST['proportion'];
                }else{
                    $proportion = 8;
                }
                $data['fu_agent'] = $this->to['agent_id']; //代理
                $data['user_name'] = $user_name;
                $data['pen_name'] = $_POST['pen_name'];
                $data['web_id'] = $this->to[web_id];
                $data['user_pass'] = md5($_POST['user_pass'] . C('ALL_ps'));
                $data['proportion'] = $proportion;
                $data['phone'] = $_POST['phone'];
                $data['qq'] = $_POST['qq'];
                $data['email'] = $_POST['email'];
                $data['name'] = $_POST['name'];
                $data['bank'] = $_POST['bank'];
                $data['bank_number'] = $_POST['bank_number'];
                $data['open_account'] = $_POST['open_account'];
                $is = $web->add($data);
                if ($is) {
                    $this->success("添加成功", U('Agent/index'));
                } else {
                    $this->error("添加失败");
                }
            } else {
                $this->error("账户名称不可为空");
            }
        } else {
            $this->display();
        }
    }
    //修改代理信息
    public function save($id) {
        $u = M('Agent');
        if ($this->isPost()) {
            $u->create();
            $isok = $u->where(array('agent_id' => $id))->save();
            if ($isok) {
                $this->success("修改成功");
            } else {
                $this->error("修改失败");
            }
        } else {
            $where['agent_id'] = $id;
            $user = $u->where($where)->find();
            if (!is_array($user)) {
                $this->error("没有该站");
            }
            $this->assign('user', $user);
            $this->display();
        }
    }
    //修改密码()
    public function pass($id) {
        $web = M('Agent')->where(array('agent_id' => $id))->find();
        if (!empty($web[agent_id])) {
            $data['user_pass'] = md5("123456" . C('ALL_ps'));
            $is = M('Agent')->where(array('agent_id' => $id))->save($data);
            if ($is) {
                $this->success("初始化成功！！");
            } else {
                $this->success("初始化失败！！");
            }
        } else {
            $this->error("用户不存在");
        }
    }
    //删除代理
    public function delete($id) {
        $u = M('Agent');
        $num = $u->where(array('agent_id'=>$id))->delete();
        if($num){
            echo 1;
        }else{
            $this->error("删除失败");
        }
    }
}
