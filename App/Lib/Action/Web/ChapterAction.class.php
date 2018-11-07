<?php

//章节类
class ChapterAction extends GlobalAction {

    public function index($book) {
        //书籍信息
        $isbook = M('Book')->where(array('book_id' => $book))->field('book_id,book_name,fu_book')->find();
        $this->assign('isbook', $isbook);
        //小说章节列表
        $con = M('BookContent');
        import('ORG.Util.Page'); // 导入分页类
        //统计条数开始
        $where['fu_book'] = $isbook[fu_book];
        $count = $con->where($where)->field('content_id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数        
        //输出内容
        $content = $con->where($where)->field('content_id,fu_book,num,title,number,clicknum,the_price,dycs,time,attribute')->order('num ASC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        for ($i = 0; $i < count($content); $i++) {
            if ($content[$i]['the_price'] == 0) {
                $content[$i]['the_price'] = "免费";
            }
            $content[$i][arup] = floor($content[$i][dycs] / $content[$i][clicknum] * 100);
        }
        $this->assign("content", $content);    // 赋值数据集 
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
        $this->display();
    }
}
