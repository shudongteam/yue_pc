<?php

//友情链接
class FriendlinkAction extends GlobalAction {

    public function index() {
        $friend = D('FriendView');
        if ($this->isPost()) {
            $con['name'] = array('like', "%$_POST[keyword]%");
        }
        $con['web_id']= $this->to[web_id];
        import('ORG.Util.Page');
        $count = $friend->where($con)->count(); // 查询满足要求的总记录数   
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数           
        $alllink = $friend->where($con)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('alllink', $alllink);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //增加
    public function add() {
        if ($this->isPost()) {
            $system_link = M('WebLink');
            $_POST['web_id']= $this->to[web_id];
            $rid = $system_link->add($_POST);
            if ($rid > 0) {
                $this->success('增加成功', U('Friendlink/index'));
            } else {
                $this->error('增加失败');
            }
        } else {
            $this->assign('title',"新增");
            $this->display();
        }
    }

    //修改
    public function lnformation($id) {
        $where['id'] = $id;
        if ($this->isPost()) {
            $system_link = M('WebLink');
            $rid = $system_link->where($where)->save($_POST);
            if ($rid > 0) {
                $this->success('修改成功', U('Friendlink/index'));
            } else {
                $this->error('修改失败');
            }
        } else {
            $links = M('WebLink')->where($where)->find();
            $this->assign('title',"修改");
            $this->assign('friendlink', $links);
            $this->display('add');
        }
    }

    //操作
    public function delete($id) {
        $system_link = M('WebLink');
        $rid = $system_link->where(array('id' => $id))->delete();
        if ($rid > 0) {
            echo 1;
        } else {
            echo 2;
        }
    }

}
