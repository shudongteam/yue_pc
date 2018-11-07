<?php

//推荐位管理
class PromoteAction extends GlobalAction {

    public function index() {
        $promote_id = I('promote_id');
        $where = array();
        if ($promote_id) {
            $where['promote_id'] = $promote_id;
        }
        $type = M('NBookPromoteType')->select();
        $data = M('NBookPromote')->where($where)->order('xu ASC')->limit(20)->select();
        $this->assign('type', $type);
        $this->assign('data', $data);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';
        $this->title = '推荐位';
        $this->display();
    }

    public function edit() {
        $id = I('id', 0, 'intval');
        $model = M('NBookPromote');
        $where['id'] = $id;
        $data = $model->where($where)->find();
        $type = M('NBookPromoteType')->select();
        if ($this->isPost()) {
            $isok = M('Book')->where(array('book_id' => $_POST[book_id]))->field('book_id,book_name,upload_img,book_brief')->find();
            if (is_array($isok)) {
                $data['book_id'] = $isok[book_id];
                $data['book_name'] = $_POST[book_name] ? $_POST[book_name]:$isok[book_name];
                $data['upload_img'] = $isok[upload_img];
                $data['book_brief'] = $isok[book_brief];
                $data['xu'] = $_POST[xu];
                $data['promote_id'] = $_POST[promote_id];
                //如果有上传图片 使用上传的图片
                if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                    $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处理
                }

            } else {
                $this->error("没有这本书！");
            }
            if ($id) {
                $is = $model->where("id = ".$id)->save($data);
            } else {
                $is = $model->add($data);
            }
            
            if ($is) {
                $this->success('添加成功', U('Promote/index'));
            } else {
                echo $model->getLastSql();
                //$this->error('系统错误');
            }
        } else {
            $this->assign('data', $data);
            $this->assign('type', $type);            
            $this->title = '推荐位编辑';
            $this->display();
        }
    }

    //删除
    public function delete($id) {
        $is = M('NBookPromote')->where(array('id' => $id))->delete();
        if ($is) {
            $this->success("删除成功");
        } else {
            $this->error("删除失败");            
        }
    }
}
