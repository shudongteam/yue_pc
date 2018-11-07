<?php

//评论管理
class MessageAction extends GlobalAction {

    public function index($book) {
        //书籍信息
        $books = M('Book')->where(array('book_id' => $book))->field('web_id,book_name')->find();
        $this->assign('books', $books);
        //留言信息
        $mesg = D('MessageView');
        $where['z_id'] = 0;
        $where['book_id'] = $book;
        import('ORG.Util.Page'); // 导入分页类    
        $count = $mesg->where($where)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数      
        $message = $mesg->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('top desc,time desc')->select();
        foreach ($message as $key => $value) {
            $zmesg = $mesg->where(array('z_id' => $value['f_id']))->order('time asc')->select();
            $message[$key]['zmesg'] = $zmesg;
        }
        $this->assign('message', $message);
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show);
        $this->display();
    }

    //置顶
    public function topok($id) {
        $mesg = M('BookMessage')->where(array('f_id' => $id))->save(array('top' => 1));
        if ($mesg) {
            $this->success("置顶成功");
        } else {
            $this->error("置顶失败");
        }
    }

    //取消置顶
    public function topno($id) {
        $mesg = M('BookMessage')->where(array('f_id' => $id))->save(array('top' => 0));
        if ($mesg) {
            $this->success("取消置顶成功");
        } else {
            $this->error("取消置顶失败");
        }
    }

    //精华
    public function goodok($id) {
        $mesg = M('BookMessage')->where(array('f_id' => $id))->save(array('good' => 1));
        if ($mesg) {
            $this->success("精华成功");
        } else {
            $this->error("精华失败");
        }
    }

    //取消精华
    public function goodno($id) {
        $mesg = M('BookMessage')->where(array('f_id' => $id))->save(array('good' => 0));
        if ($mesg) {
            $this->success("取消精华成功");
        } else {
            $this->error("取消精华失败");
        }
    }

    //审核通过
    public function auditok($id) {
        $mesg = M('BookMessage')->where(array('f_id' => $id))->save(array('audit' => 2));
        if ($mesg) {
            $this->success("OK");
        } else {
            $this->error("操作失败");
        }
    }


    //删除
    public function del($id) {
        $msg = M('BookMessage');
        $msg->where(array('f_id' => $id))->delete();
        $msg->where(array('z_id' => $id))->delete();
        $this->success("删除成功");
    }

    //删除
    public function zdel($id) {
        $msg = M('BookMessage');
        $msg->where(array('f_id' => $id))->delete();
        $this->success("删除成功");
    }

}
