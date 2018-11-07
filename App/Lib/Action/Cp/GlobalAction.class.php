<?php

    //权限判断
    class GlobalAction extends Action {

        protected $to = 0;

        public function _initialize() {
            $this->usel_shell($_COOKIE['cp_id'], $_COOKIE['user_shell']);
            $this->assign("to", $this->to);
        }

        //权限验证页
        private function usel_shell($cp_id, $shell) {
            $User = M('Cp');
            $where['cp_id'] = $cp_id;
            $isUser = $User->field('`cp_id`,`web_id`,`user_name`,`pen_name`,`user_pass`')->where($where)->find();
            $shell2 = md5($isUser['user_name'] . $isUser['user_pass'] . C('ALL_ps'));
            if ($shell == $shell2) {
                $this->to = $isUser;
            } else {
                header("Location: " . U('Login/index'));
            }
        }

    }
