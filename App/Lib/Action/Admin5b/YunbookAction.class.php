<?php

//运营书籍
class YunbookAction extends GlobalAction {

    //作品查看
    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        A('Gongju')->getauthorizationweb($this->to[web_id]);

        //公司
        $cp = M('Cp')->where('web_id = 4')->field('cp_id,pen_name')->select();
        $this->assign('cp', $cp);

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

        if ($_REQUEST[cp_id]) {
            $where['cp_id'] = $_REQUEST[cp_id];
            $_GET['cp_id'] = $_REQUEST[cp_id];
        }

        $order = 'book_id desc';
        if ($_REQUEST[sort]) {
            if ($_REQUEST[sort] == 1) {
                $order = "BookStatistical.buy_total desc";
            }
            if ($_REQUEST[sort] == 2) {
                $order = "BookStatistical.exceptional_total desc";
            }

            // if ($_REQUEST[sort] == 3) {
            //     $order = "BookStatistical.exceptional_total desc";
            // }
            
            $_GET['sort'] = $_REQUEST[sort];
        }
         if ($_REQUEST[nums]) {
            // $where['cp_id'] = $_REQUEST[cp_id];
            $_GET['nums'] = $_REQUEST[nums];
        }
        //数据处理
        // $bbb = M('Book');
        $bbb = D('ThirdboosView');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        // $where['web_id'] = 4;
        // $where['fu_web'] = 0; //不是授权站书籍       
        // $where['author_id'] = 0; //没有作者的书就是第三方的    
        //$where['_string'] = ' (web_id = 4 AND fu_web = 0)  OR (web_id = 1 AND fu_web = 4) ';
        $where['_string'] = ' (web_id = 4 AND fu_web = 0) ';
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $nums = isset($_GET['nums']) ? $_GET['nums'] : 15;//默认显示15条记录
        $Page = new Page($count, $nums); // 实例化分页类 传入总记录数和每页显示的记录数               
        // $book = $bbb->where($where)->field('book_id,book_name,author_name,cp_name,level,state,vip,money,is_show,audit,words,time')->order('book_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $book = $bbb->where($where)->order($order)
        ->group('fu_book')
        ->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $books=array();
        foreach($book as $k=>$v){

                $data = $bbb->where(array("web_id"=>1,"fu_book"=>$v[fu_book]))->find();
                $data4 = $bbb->where(array("web_id"=>4,"fu_book"=>$v[fu_book]))->find();
                if($data['book_id']!=""){
                    
                    $data['buy_total']= $data['buy_total']+$data4['buy_total'];
                    $data['exceptional_total']= $data['exceptional_total']+$data4['exceptional_total'];
                    $books[]= $data;
                }else{
                    $books[]= $v;
                }
                //echo $books['buy_total'];
                //print_r($v);
               // print_r($data);
            
        }
        //print_r($books);
        // echo $bbb->getLastSql();
        $this->assign('book', $books);
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
        $this->assign('cp_id', $_REQUEST[cp_id]); 
        $this->assign('sort', $_REQUEST[sort]);   
            
        $this->display();
    }

    //添加作品
    public function add() {
        if ($this->isPOst()) {
            $data['web_id'] = $this->to['web_id'];
            $data['cp_id'] = $_POST[cp_id];
            $iscp = M('Cp')->where(array('cp_id' => $_POST[cp_id]))->field('pen_name')->find();
            if (is_array($iscp)) {
                $data['cp_name'] = $iscp[pen_name];
            } else {
                $this->error("找不到cp");
                exit();
            }
            $data['edit_id'] = $this->to[id];
            $data['book_name'] = $_POST[book_name];
            $data['author_name'] = $_POST[author_name];
            $data['type_id'] = $_POST[type_id];
            $data['gender'] = $_POST[gender];
            $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处
            $data['signing'] = $_POST[signing];
            $data['state'] = $_POST[state];
            $data['vip'] = $_POST[vip];
            $data['money'] = $_POST[money];
            $data['is_show'] = $_POST[is_show];
            $data['audit'] = 2; //已审核    
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $data['keywords'] = str_replace($qian, $hou, $_POST['keywords']);
            $data['book_brief'] = $_POST[book_brief];
            $data['time'] = date('Y-m-d H:i:s', time());
            $data['new_time'] = $data['time'];
            $is = A('Booklei')->bookadd($data);
            if ($is == 1) {
                $this->success("添加成功");
            } else {
                $this->error("添加失败");
            }
        } else {
            //作品类型
            $type = BooktypeAction::booktype();
            $this->assign('type', $type);
            //获取cp
            A('Gongju')->cps(4);
            $this->display();
        }
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
            $datas['cp_id'] = $_POST[cp_id];
            $datas['level'] = $_POST[level];
            $datas['state'] = $_POST[state];
            $datas['is_show'] = $_POST[is_show];
            $datas['signing'] = $_POST[signing];
            M('Book')->where(array('fu_book' => $book))->save($datas);
            if ($is) {
                $this->success("修改成功");
            } else {
                $this->error("ok");
            }
        } else {
            //作品类型
            $type = BooktypeAction::booktype();
            $this->assign('type', $type);
            //获取cp
            A('Gongju')->cps(4);
            $isbook = M('Book')->where(array('book_id' => $book))->find();
            $this->assign('isbook', $isbook);
            $this->display();
        }
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
                        $mybok['fu_web'] = $mybok[web_id];
                        $mybok['web_id'] = $_POST[web_id];
                        $bookid = $book->add($mybok);
                        //添加榜单
                        $bang->add(array('book_id' => $bookid));
                    }
                }
                $this->success("授权结束");
            } else {
                $this->error("没有站点");
            }
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

    //批量隐藏
    public function hides(){

        if($_POST[booksid]){
            $book = M('book');
            $arr = explode(",",$_POST[booksid]);
            if(count($arr)<200){
                foreach ($arr as $k => $v) {
                    $where['is_show'] = 0;
                    $book->where("fu_book=$v")->save($where);
                }
                echo "书本隐藏成功";
            }
        }else{
            echo "请选择书籍";
        }
    }

    //批量上书
    public function piliang() {
        if ($this->isPost()) {
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 31457280; // 设置附件上传大小
            $upload->allowExts = array('xls'); // 设置附件上传类型
            $upload->savePath = './Upload/xls/'; // 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $info = $upload->getUploadFileInfo();
                A('Gongju')->excels($info[0]['savename']);
            }
        } else {
            //调用工具
            A('Gongju')->cps(4);
            $this->display();
        }
    }
    //稿件下载
    public function download($book) {
        A('Booklei')->download($book);
    }

    //导出所有书籍
    public function bookDump() {
        $filename = '阅明-第三方书籍(' . date('Y.m.d') . ')';
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo iconv("UTF-8", "GB2312", ("书号" . "\t" . '书名' . "\t" . '作者' . "\t" . '公司' . "\t" . '类型' . "\t" . '频道' . "\t" . '显示' . "\t" . '状态' . "\t" . '审核' . "\t" . '收费类型' . "\t" . '单本收费' . "\t" . '总字数' . "\t" . '现订阅(YMB)' . "\t" . '现打赏(YMB)' . "\t" . '总点击'. "\t" . '网址')) . "\n";  

        $bbb = D('ThirdboosView');
        $where['_string'] = ' (web_id = 4 AND fu_web = 0) ';
        //$where['_string'] = ' (web_id = 4 AND fu_web = 0)  OR (web_id = 1 AND fu_web = 4) ';
        $result = $bbb->where($where)->order('book_id desc')->group('fu_book')->select();
        // echo $bbb->getLastSql();exit;
        $books=array();
        foreach($result as $k=>$v){
             $data = $bbb->where(array("web_id"=>1,"fu_book"=>$v[fu_book]))->find();
             $data4 = $bbb->where(array("web_id"=>4,"fu_book"=>$v[fu_book]))->find();
                if($data['book_id']!=""){
                	$data['buy_total']= $data['buy_total']+$data4['buy_total'];
                    $data['exceptional_total']= $data['exceptional_total']+$data4['exceptional_total'];
                    $books[]= $data;
                }else{
                    $books[]= $v;
                }
            // if($v[web_id]==4){
            //     $data = $bbb->where(array("web_id"=>1,"fu_book"=>$v[fu_book]))->find();
            //     if(!empty($data)){
            //         $books[]= $data;
            //     }
            // }else{
            //     $books[]= $v;
            // }
        }

        foreach ($books as $key => $val) {
            //审核                
            switch ($val['audit']) {
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
            switch ($val['vip']) {
                case 0: $vip = '按章';
                    break;
                case 1: $vip = '按本';
                    break;
                case 2: $vip = '免费';
                    break;
            }
            $gender = $val['gender'] == 1 ? '男' : '女';
            $is_show = $val['is_show'] == 1 ? '显示' : '隐藏';
            $state = $val['state'] == 1 ? '连载' : '完本';
            $type = BooktypeAction::mybooktype($val['type_id']);
            $value = array(
                $val['book_id'],
                iconv("UTF-8", "GBK", $val['book_name']),
                iconv("UTF-8", "GBK", $val['author_name']),
                iconv("UTF-8", "GBK", $val['cp_name']),
                iconv("UTF-8", "GBK", $type),
                iconv("UTF-8", "GBK", $gender),
                iconv("UTF-8", "GBK", $is_show),
                iconv("UTF-8", "GBK", $state),
                iconv("UTF-8", "GBK", $audit),
                iconv("UTF-8", "GBK", $vip),
                $val['money'],
                $val['words'],
                intval($val['buy_total']),
                intval($val['exceptional_total']),
                intval($val['click_total']),
                'http://www.ymzww.cn/books/' . $val['book_id'] . '.html',
            );
            echo implode("\t", $value) . "\n";
        } 
    }
}
