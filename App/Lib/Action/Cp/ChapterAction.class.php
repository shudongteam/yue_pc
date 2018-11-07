<?php

//章节类
class ChapterAction extends GlobalAction {

    public function index($book) {
        //书籍信息
        $isbook = M('Book')->where(array('book_id' => $book))->field('book_id,book_name')->find();
        $this->assign('isbook', $isbook);
        //小说章节列表
        $con = M('BookContent');
        import('ORG.Util.Page'); // 导入分页类
        //统计条数开始
        $where['fu_book'] = $book;
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
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 章 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //添加章节
    public function add($book) {
        if ($this->isPost()) {
            $arr['fu_book'] = $book;
            $arr['title'] = $_POST['title'];
            $arr['content'] = $_POST['content'];
            //是否有留言
            if (!empty($_POST[message])) {
                $arr['message'] = $_POST['message'];
            }
            //定时查看
            if (!empty($_POST[attribute])) {
                $arr['attribute'] = $_POST['attribute'];
            } else {
                $arr['attribute'] = date("Y-m-d H:i:s", time());
            }
            $is = A('Chapterlei')->zengjia($arr, $book);
            if ($is) {
                $this->success('增加成功！');
            } else {
                $this->error("增加失败！");
            }
        } else {
            $isbook = M('Book')->where(array('book_id' => $book))->field('book_id,book_name')->find();
            $this->assign('isbook', $isbook);
            $this->display();
        }
    }

    //判断操作类型
    public function operation() {
        $db = M('BookContent');
        //使用VIP
        if (isset($_POST[okvip])) {
            $value = $_POST[checkbox];
            @$zhi = implode(',', $value);
            $arr = explode(',', $zhi);
            foreach ($arr as $key => $value) {
                $number = $db->where(array('content_id' => $value))->field('number')->find();
                $vip['the_price'] = ceil($number[number] / 1000 * C('Prices'));
                $db->where(array('content_id' => $value))->save($vip);
            }
            $this->success("成功VIP！！");
        }
        //取消VIP
        if (isset($_POST['novip'])) {
            $value = $_POST[checkbox];
            @$zhi = implode(',', $value);
            $arr = explode(',', $zhi);
            foreach ($arr as $key => $value) {
                $vip['the_price'] = 0;
                $db->where(array('content_id' => $value))->save($vip);
            }
            $this->success("成功取消VIP！！");
        }
    }

    //章节删除
    public function del($connkid) {
        $is = A('Chapterlei')->shanchu($connkid);
        if ($is) {
            $this->success('删除成功！');
        } else {
            $this->error('系统错误');
        }
    }

    //章节修改
    public function save($connkid) {

        if ($this->isPost()) {
            $arr['title'] = $_POST[title];
            $arr['content'] = $_POST[content];
            $arr['message'] = $_POST['message'];
            $is = A('Chapterlei')->xiugai($arr, $connkid);
            if ($is) {
                $this->success('修改成功！');
            } else {
                $this->error("修改失败！");
            }
        } else {
            $conn = M('BookContent')->where(array('content_id' => $connkid))->find();
            $neirong = M('BookContents')->where(array('content_id' => $connkid))->find();
            $conn['content'] = $neirong[content];
            $this->assign('conn', $conn);
            $isbook = M('Book')->where(array('book_id' => $conn[fu_book]))->field('book_id,book_name')->find();
            $this->assign('isbook', $isbook);
            $this->display();
        }
    }

    //章节排序
    public function pailie($connkid) {
        $conn = M('BookContent')->where(array('content_id' => $connkid))->field('content_id,fu_book,title')->find();
        if ($this->isPost()) {
            $is = A('Chapterlei')->shijan($connkid, $_POST[conid], $conn[fu_book]);
            if ($is) {
                $this->success("排序成功", U('Chapter/index', array('book' => $is)));
            } else {
                $this->error('系统错误');
            }
        } else {
            $this->assign('conn', $conn);
            $isbook = M('Book')->where(array('book_id' => $conn[fu_book]))->field('book_id,book_name')->find();
            $this->assign('isbook', $isbook);
            A('Gongju')->zhangjie($conn[fu_book]);
            $this->display();
        }
    }


    //获取现在章节字数
    public function trimalls() {
        echo A('Chapterlei')->trimall($_POST[str]);
    }

}
