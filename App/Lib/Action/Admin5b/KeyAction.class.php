<?php

//关键字管理
class KeyAction extends GlobalAction {

    //显示
    public function index() {
        $result = M('SystemKeys')->find();
        $keys = $result['key'];
        $this->assign('keys', $keys);
        $this->display();
    }

    //更新
    public function save() {
        if ($this->isPost()) {
            $isok = M('SystemKeys')->where(array('id' => 1))->save(array('key' => $_POST[keys]));
            if ($isok) {
                $this->success("修改成功！");
            } else {
                $this->error("错误");
            }
        }
    }

}
