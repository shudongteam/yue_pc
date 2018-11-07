<?php

//用户登录
class LoginAction extends Action {

    public function index() {
        $this->display();
    }

    //验证码
    public function yzm() {
        Vendor('Yzm.yzm');
    }

    //登录验证
    public function doLogin() {
        if ($this->isPost()) {
            //  验证码验证是否正确
            // if ($_POST['yzm'] != $_SESSION['randcode']) {
            //     $this->error("验证码错误!!");
            //     exit();
            // }
            $username = str_replace(" ", "", $_POST['name']);
            $pass = md5(C('ALL_ps').$_POST['password']);
            $arr = M('NAdmin')->where(array('user_name' => $username, 'password' => $pass))->field('id,web_id,is_first,user_name,pen_name')->find();
            // echo $arr['id'];
            // echo $_SESSION['randcode'] = 1111111;exit;
            // var_dump($arr);
            // exit;
            if ($arr) {
                session('agent_id', $arr['id']);
                session('web_id', $arr['web_id']);
                session('user_name', $arr['user_name']);
                session('pen_name', $arr['pen_name']);
                session('is_first', $arr['is_first']);
                $this->success('登录成功',U('Notices/index'));
            } else {
                $this->error("您的账户或密码错误！！");
            }
        }
    }

    //退出
    public function exits() {
        session('agent_id', null);
        session('web_id', null);
        session('pen_name', null);
        session('is_first', null);
        header("Location: " . U('Login/index'));
    }

}
