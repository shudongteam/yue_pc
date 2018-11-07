<?php

//图书管理
class BooksAction extends GlobalAction {

    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        //post查询
        if ($this->isPost()) {
            if (!empty($_POST[keyword])) {
                $where['book_name'] = array('like', "%$_POST[keyword]%");
            }
            if (!empty($_POST[type_id])) {
                $where['type_id'] = $_POST[type_id];
            }
            if (!empty($_POST[gender])) {
                $where['gender'] = $_POST[gender];
            }
            if (!empty($_POST[level])) {
                $where['level'] = $_POST[level];
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
        if (!empty($_GET[gender])) {
            $where['gender'] = $_GET[gender];
        }        
        if (!empty($_GET[level])) {
            $where['level'] = $_GET[level];
        }        
        $where['web_id'] = $this->to[web_id];
        $where['is_show'] = 1;
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $webbook = $bbb->where($where)->field('book_id,book_name,author_name,state,vip,money,words,level,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('webbook', $webbook);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

}
