<?php

class WebbookAction extends GlobalAction {

    //显示所有站点
    public function web() {
        if ($this->isPost()) {
            $where['web_name'] = array('like', "%$_POST[keyword]%");
        }
        $ad = M('Web');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $user = $ad->where($where)->field('web_id,master_id,pc,web_name,web_url,all_ps,login_url,automatic,preload,webphone,webqq,weixin,beian')->order('web_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('user', $user);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //作品查看
    public function index($web) {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        $where['web_id'] = $web;
        //post查询
        if ($this->isPost()) {
            if (!empty($_POST[keyword])) {
                $where['book_name'] = array('like', "%$_POST[keyword]%");
            }
            if (!empty($_POST[type_id])) {
                $where['type_id'] = $_POST[type_id];
            }
        }
        //get查询
        if (!empty($_GET[keyword])) {
            $_GET[keyword] = urldecode($_GET[keyword]);
            $where['book_name'] = array('like', "%$_GET[keyword]%");
        }
        if (!empty($_GET[type_id])) {
            $where['type_id'] = $_GET[type_id];
        }
        $where['fu_web'] = $this->to[web_id];
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $webbook = $bbb->where($where)->field('book_id,fu_book,book_name,author_name,is_show,state,vip,money,words,level,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('webbook', $webbook);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //删除授权书单信息
    public function delete($book) {
        $u = M('Book');
        $num = $u->where(array('book_id' => $book))->delete();
        if ($num) {
            echo 1;
        } else {
            $this->error("删除失败");
        }
    }

}
