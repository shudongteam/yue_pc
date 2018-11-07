<?php

//用户帮助
class HelpAction extends GlobalAction {

    public function index() {
        $help = D('MyhelpView');
        if ($this->isPost()) {
            $where['title'] = array('like', "%$_POST[keyword]%");
        }
        $where['web_id']= $this->to[web_id];
        import('ORG.Util.Page');
        $count = $help->where($where)->count(); // 查询满足要求的总记录数   
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数 
        $arr = $help->where($where)->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('arr', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出

        $this->display();
    }

    //发布帮助
    public function add() {
        if ($this->isPost()) {
            $system = M('WebHelp');
            if (get_magic_quotes_gpc()) {
                $data['content'] = stripslashes($_POST[content]); //内容
            } else {
                $data['content'] = $_POST[content]; //内容
            }
            $data['web_id']= $this->to[web_id];
            $data['time'] = date('y-m-d H:i:s', time());
            $data = array_merge($data, $_POST);
            $rid = $system->add($data);
            if ($rid > 0) {
                $this->success('发布成功', U('Help/index'));
            } else {
                $this->error('发布失败');
            }
        } else {
            $this->assign('title', "添加");
            $this->display();
        }
    }

    //删除帮助
    public function delete($id) {
        $system_announcement = M('WebHelp');
        $rid = $system_announcement->where(array('id' => $id))->delete();
        if ($rid > 0) {
            echo 1;
        } else {
            echo 2;
        }
    }

    //更新帮助
    public function lnformation($id) {
        $system_announcement = M('WebHelp');
        $one = $system_announcement->where(array('id' => $id))->find();
        $this->assign('one', $one);
        if ($this->isPost()) {
            if (get_magic_quotes_gpc()) {
                $data['content'] = stripslashes($_POST[content]); //内容
            } else {
                $data['content'] = $_POST[content]; //内容
            }
            $data = array_merge($data, $_POST);
            $rid = $system_announcement->where(array('id' => $id))->save($data);
            if ($rid > 0) {
                $this->error('修改成功', U('Help/index'));
            } else {
                $this->error('修改失败');
            }
        }
        $this->assign('title', "修改");
        $this->display('add');
    }

}
