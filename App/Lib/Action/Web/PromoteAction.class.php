<?php

//推荐位管理
class PromoteAction extends GlobalAction {

    public function index() {
        $kuan = M('WebPromote');
        $where['pc'] = 0; 
        $quyu = $kuan->where($where)->order('promote_id asc')->select();
        $this->assign('quyu', $quyu);
        $this->display();
    }

    public function xianshi($pids) {
        $tuijian = M('BookPromote');
        $where['web_id'] = $this->to[web_id];
        $where['promote_id'] = $pids;
        $xiaokuai = $tuijian->where($where)->order('xu asc')->select();
        $this->assign('xiaokuai', $xiaokuai);
        $this->display();
    }

    //增加推荐位
    public function add($pid) {
        if ($this->isPost()) {
            $isok = M('Book')->where(array('book_id' => $_POST[book_id]))->field('book_id,book_name,upload_img,book_brief')->find();
            if (is_array($isok)) {
                $data['book_id'] = $isok[book_id];
                $data['book_name'] = $isok[book_name];
                $data['upload_img'] = $isok[upload_img];
                $data['book_brief'] = $isok[book_brief];
            } else {
                $this->error("没有这本书！");
            }

            $data['web_id'] = $this->to[web_id];
            $data['promote_id'] = $pid;
            $data['xu'] = $_POST[xu];
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处理
            }
            if (!empty($_POST[book_brief])) {
                $data['book_brief'] = $_POST[book_brief];
            }
            if (!empty($_POST[book_name])) {
                $data['book_name'] = $_POST[book_name];
            }
            $is = M('BookPromote')->add($data);
            if ($is) {
                $this->success('添加成功', U('Promote/index'));
            } else {
                $this->error('系统错误');
            }
        } else {
            $kuan = M('WebPromote')->where(array('promote_id' => $pid))->find();
            $this->assign('kuan', $kuan);
            $this->display();
        }
    }

    //修改
    public function save($id) {
        $tuijian = M('BookPromote');
        if ($this->isPost()) {
            $data['xu'] = $_POST[xu];
            $data['book_id'] = $_POST[book_id];
            $data['book_name'] = $_POST[book_name];
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处理
            }
            if (!empty($_POST[book_brief])) {
                $data['book_brief'] = $_POST[book_brief];
            }
            $is = M('BookPromote')->where(array('id' => $id))->save($data);
            if ($is) {
                $this->success('修改成功', U('Promote/index'));
            } else {
                $this->error('系统错误');
            }
        } else {
            $istuijan = $tuijian->where(array('id' => $id))->find();
            $this->assign('istuijan', $istuijan);
            $kuan = M('WebPromote')->where(array('promote_id' => $istuijan[promote_id]))->find();
            $this->assign('kuan', $kuan);
            $this->display();
        }
    }

    //删除
    public function delete($id) {
        $is = M('BookPromote')->where(array('id' => $id))->delete();
        if ($is) {
            echo 1;
        } else {
            echo 2;
        }
    }
}
