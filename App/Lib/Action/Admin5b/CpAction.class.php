<?php

//cp
class CpAction extends GlobalAction {

    //显示cp
    public function index() {
        $ad = M('Cp');
        import('ORG.Util.Page'); // 导入分页类
        if ($this->isPost()) {
            $where[$_POST['search']] = array('like', "%$_POST[keyword]%");
        }
        $where['web_id'] = 4;
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $cp = $ad->where($where)->field('cp_id,user_name,pen_name,phone,qq,email')->order('cp_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('cp', $cp);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //添加cp
    public function add() {
        if ($this->isPost()) {
            $cp = M('Cp');
            $yanzhen = $cp->where(array('user_name' => $_POST[user_name]))->field('cp_id')->find();
            if (is_array($yanzhen)) {
                $this->error("cp已经存在");
                exit();
            }
            $data = $_POST;
            $data['web_id'] = 4;
            $data['user_pass'] = md5($_POST['user_pass'] . C('ALL_ps'));
            $result = M("Cp")->add($data);
            if ($result) {
                $this->success("添加成功", U('Cp/index'));
            } else {
                $this->error("添加失败!");
            }
        } else {
            $this->display();
        }
    }

    //查看信息
    public function lnformation($id) {
        $c = M("Cp");
        if ($this->isPost()) {
            $data = $_POST;
            $result = $c->where(array('cp_id' => $id))->save($data);
            if ($result) {
                $this->success("修改成功", U('Cp/index'));
            } else {
                $this->error("修改失败!");
            }
        } else {
            $cp = $c->where(array('cp_id' => $id))->find();
            $this->assign("cp", $cp);
            $this->display();
        }
    }

    //初始化密码
    public function pass($id) {
        $cp = M('Cp')->where(array('cp_id' => $id))->find();
        if (!empty($cp[cp_id])) {
            $data['user_pass'] = md5('123456' . C('ALL_ps'));
            $is = M('Cp')->where(array('cp_id' => $id))->save($data);
            if ($is) {
                $this->success("初始化成功！！");
            } else {
                $this->success("初始化失败！！");
            }
        } else {
            $this->error("CP用户不存在");
        }
    }

}
