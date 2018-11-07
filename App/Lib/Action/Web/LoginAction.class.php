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
    public function login() {
        if ($this->isPost()) {
            //  验证码验证是否正确
            if ($_POST['yzm'] != $_SESSION['randcode']) {
                $this->error("验证码错误!!");
                exit();
            }
            $username = str_replace(" ", "", $_POST[username]);
            $pass = md5($_POST[password] . C('ALL_ps'));
            $user = M('Agent');
            $arr = $user->where(array('user_name' => $username, 'user_pass' => $pass))->field('agent_id,web_id,pen_name,user_name,user_pass')->find();
            if (is_array($arr)) {
                $web = M('Web')->where(array('web_id' => $arr[web_id]))->field('web_name')->find();
                cookie('agent_id', $arr[agent_id], time() + 2 * 7 * 24 * 3600);
                cookie('pen_name', $arr[pen_name], time() + 2 * 7 * 24 * 3600);
                cookie('user_name', $arr[user_name], time() + 2 * 7 * 24 * 3600);
                cookie('web_name', $web[web_name], time() + 2 * 7 * 24 * 3600);
                cookie('user_shell', md5($arr[user_name] . $arr[user_pass] . C('ALL_ps')), time() + 2 * 7 * 24 * 3600);
                //通知开关
                cookie('notice', 1);
                header("Location: " . U('Main/index'));
            } else {
                $this->error("您的账户或密码错误！！");
            }
        }
    }

    //退出
    public function exits() {
        cookie('agent_id', null);
        cookie('pen_name', null);
        cookie('user_name', null);
        cookie('web_name', null);
        cookie('user_shell', null);
        header("Location: " . U('Login/index'));
    }

}
