<?php

//广告模块
class BanAction extends GlobalAction {

    public function index() {
        $where['web_id']= $this->to[web_id];
        $where['pc']= 0;
        $ban = M('WebBan')->where($where)->select();
        $this->assign('ban', $ban);
        $this->display();
    }

    //修改广告位
    public function save($id) {
        $where['id'] = $id;
        $result = M("WebBan")->where($where)->find();
        if ($result) {
            if ($this->isPost()) {
                import('ORG.Net.UploadFile');
                $upload = new UploadFile(); // 实例化上传类
                $upload->maxSize = 1048576; // 设置附件上传大小 1MB
                $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
                $upload->savePath = './Upload/Ban/'; // 设置附件上传目录
                if (!$upload->upload()) {// 上传错误提示错误信息
                    $this->error($upload->getErrorMsg());
                } else {// 上传成功 获取上传文件信息
                    $info = $upload->getUploadFileInfo();
                    $data['pic'] = $info[0]['savename'];
                    $data['link'] = $_POST[link];
                    $ban = M("WebBan")->where($where)->save($data);
                    if ($ban) {
                        $this->success("成功", U("Ban/index"));
                    } else {
                        $this->error("错误");
                    }
                }
            } else {
                $this->assign("ban", $result);
                $this->display();
            }
        } else {
            $this->error("没有找到此广告位!", U("Ban/index"));
        }
    }

}
