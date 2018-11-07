<?php

//权限管理
class PermissionAction extends GlobalAction {

    //权限分配
    public function index() {
        $admin = M('Admin')->order('id desc')->select();
        $this->assign('admin', $admin);
        $this->display();
    }

    // 添加角色
    public function add() {
        if ($this->isPost()) {
            $admin = M('Admin');
            $yanzhen = $admin->where(array('user_name' => $_POST[user_name]))->field('id')->find();
            if (is_array($yanzhen)) {
                $this->error("账户已经存在！");
                exit();
            }
            foreach ($_POST['checkbox'] as $key => $value) {
                $data['position_list'].=$value.',';
            }
            $data['web_id'] = $_POST['web_id'];
            $data['user_name'] = $_POST['user_name'];
            $data['pen_name'] = $_POST['pen_name'];
            $data['user_pass'] = md5($_POST['user_pass'] . C('ALL_ps'));
            $is = $admin->add($data);
            if ($is) {
                $this->success("添加成功", U('Permission/index'));
            } else {
                $this->error("失败");
            }
        } else {
            //所有站点
            A("Gongju")->getweb();
            $this->display();
        }
    }
    //权限修改
    public function save($id) {
        $admin = M('Admin');
        if ($this->isPost()) {
            $data['pen_name'] = $_POST[pen_name];
            foreach ($_POST['checkbox'] as $key => $value) {
                $data['position_list'].=$value.',';
            }
            $data['web_id'] = $_POST[web_id];
            $is = $admin->where(array('id' => $id))->save($data);
            if ($is) {
                $this->success("修改成功", U('Permission/index'));
            } else {
                $this->error("系统错误");
            }
        } else {
            $myadmin = $admin->where(array('id' => $id))->find();
            //所有站点
            A("Gongju")->getweb();
            $this->assign('myadmin', $myadmin);
            $this->display();
        }
    }

    //删除角色
    public function delete(){
        $admin = M('Admin');
        if($_COOKIE[user_name]=="admin233"){
            $del = $admin->where(array('id' => $_GET['id']))->delete();
            if($del){
                echo 1;
            }
        }else{
            echo "暂无权限，请联系技术";
        }
    }
}
?>

