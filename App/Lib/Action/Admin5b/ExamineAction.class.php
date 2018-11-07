<?php

//稿件审核
class ExamineAction extends GlobalAction {

    //作品审核
    public function index() {
        $bookcp = M('Book');
        //post
        if ($this->isPost()) {
            if (!empty($_POST[keyword])) {
                if ($_POST[search_type] == 1) {
                    $where['book_name'] = array('like', "%$_POST[keyword]%");
                } else {
                    $where['author_name'] = array('like', "%$_POST[keyword]%");
                }
            }
        }
        //get
        if ($this->isGet()) {
            if (!empty($_GET[keyword])) {
                if ($_GET[search_type] == 1) {
                    $where['book_name'] = array('like', "%$_GET[keyword]%");
                } else {
                    $where['author_name'] = array('like', "%$_GET[keyword]%");
                }
            }
        }
        $where['web_id'] = $this->to['web_id'];
        $where['fu_web'] = 0; //没有授权站
        $where['audit'] = 1;
        import('ORG.Util.Page');
        $count = $bookcp->where($where)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数 
        $arr = $bookcp->where($where)->field('book_id,book_name,author_name,author_id,state,vip,money,words,time,type_id')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($arr as $key => $value) {
            $arr[$key][type_name] = A('Booktype')->mybooktype($value[type_id]);
        }
        $this->assign('arr', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //下载
    public function xiazai($book) {
        A('Booklei')->download($book);
    }

    //通过该书
    public function tonguo($book) {
        $data['edit_id'] = $this->to[user_id];
        $data['audit'] = 2;
        $data['is_show'] = 1;
        M('Book')->where(array('book_id' => $book))->save($data);
        M('BookContent')->where(array('fu_book' => $book))->save(array('caudit' => 1));
        echo 1;
    }

    //不通过该书
    public function butonguo($book) {
        M('Book')->where(array('book_id' => $book))->save(array('audit' => 3));
        echo 1;
    }

    //查看内容
    public function view($book) {
        //查询数据信息
        $where['book_id'] = $book;
        $Books = M('Book');
        $bookname = $Books->where($where)->field('book_name,book_brief')->find();
        $this->assign('bookname', $bookname);
        //查询内容信息
        $con = M('bookContent');
        $cons = M('bookContents');
        $where['fu_book'] = $book;
        $content = $con->where($where)->field('content_id,title,num')->order('num ASC')->select();

        for ($i = 0; $i < count($content); $i++) {
            $contents = $cons->where(array('content_id' => $content[$i][content_id]))->find();
            $contents['content'] = str_replace("\n", "</p><p>", str_replace(" ", "", $contents[content]));
            $content[$i][content] = $contents[content];
        }
        $this->assign('content', $content);
        $this->display();
    }

    //章节审核
    public function chapter() {
        //post
        if ($this->isPost()) {
            if (!empty($_POST[book_name])) {
                $where['Book.book_name'] = array('like', "%$_POST[book_name]%");
            }
            if (!empty($_POST[signing])) {
                $where['Book.signing'] = array('like', "%$_POST[signing]%");
            }
        }
        $caoni = D('ShenheView');
        import('ORG.Util.Page');
        $where['BookContent.caudit'] = 0; //章节没有审核
        $where['Book.audit'] = 2; //作品已经审核过了
        $where['Book.web_id'] = $this->to[web_id];
        $where['Book.fu_web'] = 0; //是本站的书        
        $count = $caoni->where($where)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数 
        $arr = $caoni->where($where)->order('BookContent.time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('arr', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //章节通过不通过
    public function shenhe($id, $caozuo) {
        if ($caozuo == 1) {
            $dfsfs = M('BookContent');
            $isok = $dfsfs->where(array('content_id' => $id))->save(array('caudit' => 1));
            if ($isok) {
                echo 1;
            } else {
                echo 2;
            }
        } elseif ($caozuo == 2) {
            $isok = M('BookContent')->where(array('content_id' => $id))->save(array('caudit' => 2));
            if ($isok) {
                echo 1;
            } else {
                echo 2;
            }
        }
    }

}
