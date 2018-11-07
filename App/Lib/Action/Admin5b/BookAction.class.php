<?php

class BookAction extends GlobalAction {

    //作品查看
    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        A('Gongju')->getauthorizationweb($this->to[web_id]);

        $keyword = isset($_REQUEST[keyword]) ? trim(urldecode($_REQUEST[keyword])) : '';
        if ($keyword) {
            if ($_REQUEST[search_type] == 1) {
                $where['book_name'] = array('like', '%'.$keyword.'%');
            } else {
                $where['author_name'] = array('like', '%'.$keyword.'%');
            }
             $_GET['keyword'] = $keyword;
             $_GET['search_type']  = $_REQUEST[search_type];
        }
        if ($_REQUEST[type_id]) {
            $where['type_id'] = $_REQUEST[type_id];
            $_GET['type_id'] = $_REQUEST[type_id];
        }

        if ($_REQUEST[state]) {
            $where['state'] = $_REQUEST[state];
            $_GET['state'] = $_REQUEST[state];
        }


        //数据处理
        // $bbb = M('Book');
        $bbb = D('BooksView');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $where['web_id'] = $this->to[web_id];
        $where['fu_web'] = 0; //不是授权站书籍   
        $where['author_id'] = array('neq', 0); //不等于0就是有作者真实的书
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $nums = isset($_GET['nums']) ? $_GET['nums'] : 15;//默认显示15条记录
        $Page = new Page($count, $nums); // 实例化分页类 传入总记录数和每页显示的记录数               
        // $book = $bbb->where($where)->field('book_id,book_name,author_name,cp_name,level,signing,state,vip,money,is_show,audit,words,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $book = $bbb->where($where)->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $arr = array();
        $bc = M('BookContent');
        foreach ($book as $key => $value) {
           $where[fu_book] = $value[fu_book]; 
           $data[$key] = $bc->where($where)->field("title")->select();
           $data[$key] = i_array_column($data[$key],'title');
           $arr[$key] = $value;
           $repeat_arr = $this->FetchRepeatInArray($data[$key]);   
            if($repeat_arr){  
              $arr[$key][repeat] = implode("<br>",$repeat_arr);
            }else{  
              $arr[$key][repeat] = "否";    
            }  
        }
        $this->assign('book', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
        $this->assign('nums', $nums);
        $this->assign('search_type', $_REQUEST[search_type]);
        $this->assign('keyword', $keyword);
        $this->assign('type_id', $_REQUEST[type_id]);
        $this->assign('state', $_REQUEST[state]);
        $this->display();
    }
    //数组查找重复元素（返回重复数据）
    public function FetchRepeatInArray($array) {   
    // 获取去掉重复数据的数组   
    $unique_arr = array_unique ( $array );   
    // 获取重复数据的数组   
    $repeat_arr = array_diff_assoc ( $array, $unique_arr );   
    return $repeat_arr;   
    } 

    //更新书籍
    public function save($book) {
        if ($this->isPost()) {
            $data['book_name'] = $_POST[book_name];
            $data['author_name'] = $_POST[author_name];
            $data['type_id'] = $_POST[type_id];
            $data['gender'] = $_POST[gender];
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处
            }
            $data['vip'] = $_POST[vip];
            $data['money'] = $_POST[money];
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $data['keywords'] = str_replace($qian, $hou, $_POST['keywords']);
            $data['book_brief'] = $_POST[book_brief];
            $is = M('Book')->where(array('book_id' => $book))->save($data);
            //更新书单
            $datas['level'] = $_POST[level];
            $datas['state'] = $_POST[state];
            $datas['is_show'] = $_POST[is_show];
            $datas['signing'] = $_POST[signing];
            M('Book')->where(array('fu_book' => $book))->save($datas);
            //判断是否字数提交
            if($_POST[ctotal]){
                $ct = $_POST[ctotal];
                $oct = M('BookStatistical')->where(array('book_id' => $book))->field("click_total")->find();
                if($ct!=$oct[click_total]){
                    $data[book_id] = $book;
                    $data[click_total] = $ct;
                    M('BookStatistical')->where(array('book_id' => $book))->save($data);
                }
            }
            if($_POST[stotal]){
                $st = $_POST[stotal];
                $ost = M('BookStatistical')->where(array('book_id' => $book))->field("collection_total")->find();
                if($st!=$oct[collection_total]){
                    $data[book_id] = $book;
                    $data[collection_total] = $st;
                    M('BookStatistical')->where(array('book_id' => $book))->save($data);
                }
            }
            //更新作品大纲
            M('BookOutline')->add(array('book_id' => $book, 'outline' => $_POST['outline']), array(), true);
            if ($is) {
                $this->success("修改成功");
            } else {
                $this->error("ok");
            }
        } else {
            //作品类型
            $type = BooktypeAction::booktype();
            $this->assign('type', $type);
            $isbook = M('Book')->where(array('book_id' => $book))->find();
            $totalclick = M('BookStatistical')->where(array('book_id' => $book))->field("click_total")->find();
            $totalcollection = M('BookStatistical')->where(array('book_id' => $book))->field("collection_total")->find();
            //查询作品大纲
            $outline = M('BookOutline')->where(array('book_id' => $book))->getField('outline');
            $this->assign('isbook', $isbook);
            $this->assign('outline', $outline);
            $this->assign('totalclick', $totalclick[click_total]);
            $this->assign('totalcollection', $totalcollection[collection_total]);
            $this->display();
        }
    }

    //删除中
    public function delete($book) {
        $where['fu_book'] = $book;
        $res = M('BookContent')->where($where)->find();
        if ($res) {
            echo "请先删除该书下的章节";   
        } else {
            M('book')->delete($book);
            echo 1;
        }
    }

    //销售情况
    public function sales($book) {
        //书籍信息
        $isbook = M('Book')->where(array('book_id' => $book))->field('book_name')->find();
        $this->assign('isbook', $isbook);
        //统计信息
        $sales = D('TongjiView')->where(array('fu_book' => $book))->select();
        $this->assign('sales', $sales);
        $this->display();
    }

    //添加书单
    public function webbook() {
        if ($this->isPost()) {
            $web = M('Web');
            $myweb = $web->where(array('web_id' => $_POST[web_id]))->field('web_id,web_name')->field('web_id')->find();
            if (is_array($myweb)) {
                //准备模型
                $book = M('book');
                $bang = M('BookStatistical'); //作品榜单
                $value = $_POST[checkbox];
                @$zhi = implode(',', $value);
                $arr = explode(',', $zhi);
                foreach ($arr as $key => $value) {
                    //查询是否已经授权了
                    $isbook = $book->where(array('web_id' => $_POST[web_id], 'fu_book' => $value))->field('book_id')->find();
                    if (is_array($isbook)) {
                        $this->error("书号$value 已经授权了");
                    } else {
                        //查询书籍进行添加
                        $mybok = $book->where(array('book_id' => $value))->find();
                        unset($mybok['book_id']); //删除书籍表中的关联值
                    //   if($mybok[is_show]==1){
                            $mybok['fu_web'] = $mybok[web_id];
                            $mybok['web_id'] = $_POST[web_id];
                            $bookid = $book->add($mybok);
                            //添加榜单
                            $bang->add(array('book_id' => $bookid));
                    //  }
                    }
                }
                $this->success("授权结束");
            } else {
                $this->error("没有站点");
            }
        }
    }

    //查看全勤
    public function money($book) {
        //查看作品信息
        $isbook = M('Book')->where(array('book_id' => $book))->field('book_id,author_id,book_name')->find();
        if ($this->isPost() && $_POST[welfare]) {
            $isbook['welfare'] = $_POST[welfare]; //价格
            A('Money')->add($book, $isbook);
        }
        $this->assign('yue', $_POST[yue]);
        $this->assign('welfare', $_POST[welfare]);
        $this->assign('isbook', $isbook);
        $this->display();
    }

    //生成稿费
    public function addmoney($book) {
        $books = M('Book')->where(array('book_id' => $book))->field('book_id,author_id,book_name,author_name')->find();
        if ($this->isPost()) {
            if ($books[author_id] != 0) {
                $data['author_id'] = $books['author_id'];
                $data['book_id'] = $books['book_id'];
                $data['pen_name'] = $books['author_name'];
                $data['book_name'] = $books['book_name'];
                $data['consumption'] = $_POST['consumption'];
                $data['welfare'] = $_POST['welfare'];
                $data['money'] = $_POST['money'];
                $data['beizhu'] = $_POST['beizhu'];
                $data['time'] = date('Y-m-d H:i:s', time());
                $isok = M('AuthorSettlement')->add($data);
                if ($isok) {
                    $this->success("ok");
                } else {
                    $this->success("系统错误");
                }
            } else {
                $this->success("这个是第三方授权书籍没有办法结算稿费", U('Book/addmoney', array('book' => $book)), 10);
            }
        } else {
            $this->assign('books', $books);
            $this->display();
        }
    }

    //查看作者稿费
    public function chakan($book) {
        $author = M('AuthorSettlement');
        $where['book_id'] = $book;
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $author->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $books = $author->where($where)->field()->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('books', $books);
        $isbooks = M('Book')->where(array('book_id' => $book))->field('book_name')->find();
        $this->assign('isbooks', $isbooks);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //稿件下载
    public function download($book) {
        A('Booklei')->download($book);
    }

    //导出所有书籍
    public function bookDump() {
        $where['web_id'] = $this->to[web_id];
        $where['fu_web'] = 0; //不是授权站书籍   
        $where['author_id'] = array('neq', 0); //不等于0就是有作者真实的书
        A('Booklei')->bookDump($where, "本站");
    }

    //批量搜索书名并导出xls文件    
    public function batchSearch() {
        if ($this->isPost()) {
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 3145728; // 设置附件上传大小
            $upload->allowExts = array('xls'); // 设置附件上传类型
            $upload->savePath = './Upload/search/'; // 设置附件上传目录

            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息                
                $info = $upload->getUploadFileInfo();
                $this->readXls($info[0]['savepath'] . $info[0]['savename']);
            }
        } else {
            $this->index();
        }
    }

    //读取上传文件内容
    protected function readXls($file) {
        Vendor('Exc.oleread'); //加载类
        Vendor('Exc.reader'); //加载类
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP936');
        $data->read($file);
        error_reporting(E_ALL ^ E_NOTICE);

        $filename = '阅明-作品书籍(' . date('Y.m.d') . ')';

        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo iconv("UTF-8", "GB2312", ("书号" . "\t" . '书名' . "\t" . '作者' . "\t" . '公司' . "\t" . '类型' . "\t" . '频道' . "\t" . '显示' . "\t" . '状态' . "\t" . '审核' . "\t" . '收费类型' . "\t" . '单本收费' . "\t" . '总字数' . "\t" . '现订阅(YMB)' . "\t" . '现打赏(YMB)' . "\t" . '总点击'. "\t" . '网址')) . "\n";  
        for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
            $booknme = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][1], 'UTF-8', 'GBK')); //书名
            $author_name = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][2], 'UTF-8', 'GBK')); //作者
            //echo $booknme;
            //die;
            if ($booknme) {
                $aa = D('BooksView');
                $where = array(
                    // 'Book.web_id' => $this->to[web_id],
                    'Book.fu_web' => 0,
                    'book_name' => "$booknme",
                    'author_name' => array('like', "%$author_name%")
                );

                $result = $aa->where($where)->select();
                $arr = $result[0] ? $result[0] : array();
                $count = count($result);
                if ($arr && ($count == 1)) {
                    //审核                
                    switch ($arr['audit']) {
                        case 2:
                            $audit = '已审核';
                            break;
                        case 1:
                            $audit = '未审核';
                            break;
                        default:
                            $audit = '不通过';
                            break;
                    }

                    //收费类型
                    switch ($arr['vip']) {
                        case 0: $vip = '按章';
                            break;
                        case 1: $vip = '按本';
                            break;
                        case 2: $vip = '免费';
                            break;
                    }

                    $value = array(
                        $arr['fu_book'],
                        iconv("UTF-8", "GBK", $arr['book_name']),
                        iconv("UTF-8", "GBK", $arr['author_name']),
                        iconv("UTF-8", "GBK", $arr['cp_name']),
                        iconv("UTF-8", "GBK", BooktypeAction::mybooktype($arr['type_id'])),
                        iconv("UTF-8", "GBK", (($arr['gender'] == 1) ? '男' : '女')),
                        iconv("UTF-8", "GBK", (($arr['is_show'] == 1) ? '显示' : '隐藏')),
                        iconv("UTF-8", "GBK", (($arr['state'] == 1) ? '连载' : '完本')),
                        iconv("UTF-8", "GBK", $audit),
                        iconv("UTF-8", "GBK", $vip),
                        $arr['money'],
                        $arr['words'],
                        $arr['buy_total'],
                        $arr['exceptional_total'],
                        $arr['click_total'],
                        'http://www.ymzww.cn/books/' . $arr['book_id'] . '.html',
                    );
                } elseif ($arr && $count) {
                    $value = array(
                        iconv("UTF-8", "GBK", ''),
                        iconv("UTF-8", "GBK", $booknme),
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        iconv("UTF-8", "GBK", '记录重复'),
                        iconv("UTF-8", "GBK", '记录重复'),
                    );
                } else {
                    $value = array(
                        iconv("UTF-8", "GBK", ''),
                        iconv("UTF-8", "GBK", $booknme),
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        'error',
                        'error',
                        '',
                    );
                }

                echo implode("\t", $value) . "\n";
            }
        }
    }

    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path   待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    public function delphp($book, $delDir = FALSE) {
    $path =  dirname($_SERVER['DOCUMENT_ROOT']) . '/newpc/App/Runtime/Temp/'.$book;
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
         $this->success('缓存清除成功', '/Admin5b/Book/index',3);
        if ($delDir)
            return rmdir($path);
    }else {
        $this->error('缓存文件不存在！','/Admin5b/Book/index',3);
        // if (file_exists($path)) {
        //     return unlink($path);
        // } else {
        //     return FALSE;
        // }
    }
    }

}
