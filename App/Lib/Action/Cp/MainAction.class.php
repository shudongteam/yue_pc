<?php

//首页
class MainAction extends GlobalAction {

    public function index() {
        $this->display();
    }

    //菜单
    public function menu() {
        $this->display();
    }

    //主页
    public function main() {
        $ann = M('SystemAnnouncement');
        $where['type'] = 2;
        import('ORG.Util.Page'); // 导入分页类   
        $count = $ann->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        $con = $ann->where($where)->field('id,title,name,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('con', $con);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //查看公告
    public function chakan($id) {
        $ann = M('SystemAnnouncement')->where(array('id' => $id))->find();
        $this->assign('ann', $ann);
        $this->display();
    }

    //修改密码
    public function pass() {
        $user = M('Cp');
        if ($this->isPost()) {
            $shell = md5($_POST[old_passwd] . C('ALL_ps'));
            $pass = md5($_POST[new_passwd] . C('ALL_ps'));
            if ($shell == $this->to['user_pass']) {
                $data['user_pass'] = $pass;
                $cp_id = $this->to['cp_id'];
                $user->where(array('cp_id' => $cp_id))->save($data);
                cookie('cp_id', null);
                cookie('pen_name', null);
                cookie('user_name', null);
                cookie('user_shell', null);
                $this->success("密码修改成功请重新登录！", U('Login/index'));
            } else {
                $this->error('对不起修改密码没有成功请在输入您原先的密码！！');
            }
        } else {
            $this->display();
        }
    }

    //修改资料
    public function information() {
        $cps = M('Cp');
        if ($this->isPost()) {
            $cps->create();
            $cps->where(array('cp_id' => $this->to[cp_id]))->save();
            $this->success("修改成功");
        } else {
            $user = $cps->where(array('cp_id' => $this->to[cp_id]))->find();
            $this->assign('user', $user);
            $this->display();
        }
    }

}
