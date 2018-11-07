<?php

//章节类
class YunchapterAction extends GlobalAction {

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
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
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

                //判断当前章节定时更新时间是否晚于上一章节
                $where = array("fu_book" => $book);
                $res = M("BookContent")->where($where)->order('attribute desc')->find(); 
                if ($res) {
                    $res_attr = strtotime($res['attribute']);
                    if (strtotime($arr['attribute']) < $res_attr) {
                        $this->error("定时更新时间请早于上一章节更新时间, 请重新设置!");
                    }
                }

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
        //解锁
        if (isset($_POST['jiesuo'])) {
            $value = $_POST[checkbox];
            @$zhi = implode(',', $value);
            $arr = explode(',', $zhi);
            foreach ($arr as $key => $value) {
                $vip['time'] = date('Y-m-d', strtotime('+1 day'));
                $db->where(array('content_id' => $value))->save($vip);
            }
            $this->success("解锁章节！！");
        }
        //解锁全部本书全部章节
        if (isset($_POST['jiesuoall'])) {
            $bookid = $_POST['fu_book'];
            $vip['time'] = date('Y-m-d', strtotime('+1 day'));
            $db->where(array('fu_book' => $bookid))->save($vip);
            $this->success("解锁章节成功！！");
        }
    }
    //章节查看
    public function look($book, $num) {
            $books = M('Book')->where(array('book_id' => $book))->field('book_id,book_name,chapter')->find();
            $this->assign('books', $books);
            //内容信息
            $content = M('BookContent')->where(array('fu_book' => $book, 'num' => $num))->find();
            $contents = M('BookContents')->field("content")->find($content['content_id']);
            $content['content'] = str_replace("\n", "<br>", str_replace(" ", "&nbsp;", $contents['content'])); //内容部分处理
            $this->assign('content', $content);
            //上一章
            $shang = $content[num] - 1;
            if ($shang <= 0) {
                $shang = 1;
            }
            $this->assign('shang', $shang);
            //下一章
            $xia = $content[num] + 1;
            if ($xia > $books[chapter]) {
                $xia = $books[chapter];
            }
            $this->assign('books', $books);
            $this->assign('xia', $xia);
            $this->display();
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


    //章节全部删除
    public function delAll($bookid) {
            if (!$bookid) {
                $this->error("非法请求");
            }
            $db = M('BookContent');
            //删除全部章节
            $data = $db->where(array('fu_book' => $bookid))->select();
            $BookContentDel = M('BookContentDel');
            if ($data) {
                foreach ($data as $key => $value) {
                    $BookContentDel->add($value);
                }
                $res = $db->where(array('fu_book' => $bookid))->delete();
            } else {
                $this->error("删除失败！！");
            }
            $this->success("删除成功！！");
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
            if ($connkid != $_POST['conid']) {
                $is = A('Chapterlei')->shijan($connkid, $_POST[conid], $conn[fu_book]);
                if ($is) {
                    $this->success("排序成功", U('Chapter/index', array('book' => $is)));
                } else {
                    $this->error('系统错误');
                }
            } else {
                $this->error("调整章节不能一样！");
            }
        } else {
            $this->assign('conn', $conn);
            $isbook = M('Book')->where(array('book_id' => $conn[fu_book]))->field('book_id,book_name')->find();
            $this->assign('isbook', $isbook);
            A('Gongju')->zhangjie($conn[fu_book]);
            $this->display();
        }
    }

    //批量上传章节
    public function piliang($book) {
        if ($this->isPost()) {
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 51457280; // 设置附件上传大小
            $upload->allowExts = array('txt'); // 设置附件上传类型
            $upload->savePath = './Upload/text/'; // 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                //获取默认时间
                $info = $upload->getUploadFileInfo();
                A('Chapterlei')->piliang($info[0]['savename'], $book);
            }
        } else {
            $isbook = M('Book')->where(array('book_id' => $book))->field('book_id,book_name')->find();
            $this->assign('isbook', $isbook);
            $this->display();
        }
    }

    //获取现在章节字数
    public function trimalls() {
        echo A('Chapterlei')->trimall($_POST[str]);
    }

}
