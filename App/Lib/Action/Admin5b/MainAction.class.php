<?php

//首页
class MainAction extends GlobalAction {

    //首页
    public function index() {
        $this->display();
    }

    //菜单
    public function menu() {
        //该站名字
        $myweb = M('Web')->where(array('web_id' => $this->to[web_id]))->field('web_name')->find();
        $this->assign('myweb', $myweb);
        $this->display();
    }

    //主页
    public function main() {
        $this->display();
    }

    //密码修改
    public function pass() {
        if ($this->isPost()) {
            $user = M('Admin');
            $arr = $user->where(array('id' => $this->to['id']))->find();
            if ($arr) {
                if ($arr['user_pass'] != md5(trim($_POST['old_passwd']) . C('ALL_ps'))) {
                    $this->error("修改失败,原始密码输入错误!");
                } else {
                    $user->where(array('id' => $this->to['id']))->save(array('user_pass' => md5(trim($_POST['new_passwd']) . C('ALL_ps'))));
                    $this->success("修改成功! 请重新登录!", U('Login/index'));
                }
            } else {
                $this->error("账号不存在！！");
            }
        } else {
            $this->display();
        }
    }

    //清除首页缓存
    public function remove() {
        $this->display();
    }

    //清除缓存
    public function del() {
        $index = dirname($_SERVER['DOCUMENT_ROOT']) . '/newpc/Html_Cache/index/index.html';
        $ranking = dirname($_SERVER['DOCUMENT_ROOT']) . '/newpc/Html_Cache/Rankinglist/Rankinglist.html';
        if (file_exists($index) && file_exists($ranking)) {
            // echo 1;
            @unlink($index);
            @unlink($ranking);
            $this->success("清除成功！");
        } else {
            $this->error("缓存文件不存在或！");
        }
    }

    //活动
    public function active(){
        $this->display();
    } 

    //活动开启或关闭
    public function do_active(){
        $this->display();
    } 
}
