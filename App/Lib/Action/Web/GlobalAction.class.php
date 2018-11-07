<?php

    //权限判断
    class GlobalAction extends Action {

        protected $to = 0;

        public function _initialize() {
            $this->usel_shell($_COOKIE['agent_id'], $_COOKIE['user_shell']);
            $this->assign("to", $this->to);
        }

        //权限验证页
        private function usel_shell($id, $shell) {
            $agent = M('Agent');
            $where['agent_id'] = $id;
            $isUser = $agent->field('`agent_id`,`fu_agent`,`web_id`,`user_name`,`pen_name`,`user_pass`')->where($where)->find();
            $shell2 = md5($isUser['user_name'] . $isUser['user_pass'] . C('ALL_ps'));
            if ($shell == $shell2) {
                $this->to = $isUser;
            } else {
                header("Location: " . U('Login/index'));
            }
        }

    }
