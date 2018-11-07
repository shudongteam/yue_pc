<?php

//收款信息
class ProfileAction extends GlobalAction {

    //初始化
    public function _initialize(){
        parent::_initialize();
        $where = array(
            'agent_id' => session('agent_id'),
        );
        $data = M('NAgent')->where($where)->find();
        $this->assign('data', $data);
    }
    
    //个人资料
    public function index() {

        $this->title = '个人资料';        
        $this->display();
    }   

    //收款信息
    public function payment() {
        $this->title = '收款信息';
        $this->display();
    }    

    //修改密码页面
    public function password() {
        $this->title = '修改密码';
        $this->display();
    }

    //修改密码方法
    public function api_update_password() {
        $where = array(
            'agent_id' => session('agent_id')
        );
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        $info = false;
        if ($postStr) {
            $post = json_decode($postStr, true);
            $model = M('NAdmin');
            $data = $model->where($where)->find();
            $old_password = md5(C('ALL_ps').$post['old_password']);
            $new_password = md5(C('ALL_ps').$post['new_password']);
            if ($data['password'] == $old_password) {
                $arr = array('password' => $new_password);
                $res = $model->where($where)->save($arr); 
                // echo $model->getLastSql();
                if ($res) {
                    $info = true;
                }
            }
        }
        echo $info;
    }


}
