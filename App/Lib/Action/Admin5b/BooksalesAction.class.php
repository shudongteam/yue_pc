<?php

//作品销售
class BooksalesAction extends GlobalAction {

    public function index($book) {
        if ($this->isPost()) {
            $where['time'] = array(array('egt', $_POST[da]), array('elt', $_POST[xiao]));
            $where['fu_book'] = $book;
            $where['web_id'] = $_POST[web_id];
            $cc = M('CpMoneyday');
            $zong = $cc->where($where)->field('consumption,time')->select();
            for ($i = 0; $i < count($zong); $i++) {
                $money += $zong[$i][consumption];
            }
            $this->assign('da', $_POST[da]);
            $this->assign('xiao', $_POST[xiao]);
            $this->assign('money', $money);
            $this->assign('zong', $zong);
        }
        //书籍
        $isbook = M('Book')->where(array('book_id' => $book))->field('book_name')->find();
        $this->assign('isbook', $isbook);
        //站点
        A('Gongju')->getweb();
        $this->display();
    }

}
