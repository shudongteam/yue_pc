<?php

//阅明本站书籍接口
//开发者死神 请勿删除
class YuemingAction extends Action {

    //作品列表
    public function booklist() {
        //统计条数开始
        $where['web_id'] = 1; //阅明本站
        $where['fu_web'] = 0; //不是授权站书籍   
        $books = M('Book')->where($where)->field('book_id,fu_book,book_name')->order('book_id desc')->select();
        echo json_encode($books);
    }

    //书籍信息
    public function books($bookid) {
        $where['book_id'] = $bookid;
        $books = M('Book')->where($where)->find();
        echo json_encode($books);
    }

    //章节列表
    public function showclist($fubook) {
        $con = M('BookContent')->where(array('fu_book' => $fubook))->order('num asc')->select();
        echo json_encode($con);
    }

    //内容信息
    public function content($conid) {
        $con = M('BookContents')->where(array('content_id' => $conid))->find();
        echo json_encode($con);       
    }

}
