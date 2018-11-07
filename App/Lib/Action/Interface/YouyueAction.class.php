<?php
class YouyueAction extends Action {
    //作品查看
    public function index($cp) {
        $where['cp_id'] = $cp;
        //数据处理
        $bbb = M('CpBook');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $cpbook = $bbb->where($where)->field('id,fubook_name,book_id,book_time,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('cpbook', $cpbook);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
    //作品类表
    private $key = "a6064e33e5506c66375b0991dcc1fd5c";
    private $cp_id = 40;
    public function booklist($key, $cpid) {
        //KEY值是否正确
        $this->keys($key, $cpid);
        $cpbook = M('CpBook')->join('hezuo_book ON hezuo_cp_book.book_id = hezuo_book.book_id')->where(array('hezuo_cp_book.cp_id' => $cpid))->select();
        if (is_array($cpbook)) {
            for ($i = 0; $i < count($cpbook); $i++) {
                $arr[$i]['bookid'] = $cpbook[$i]['book_id'];
                $arr[$i]['bookname'] = $cpbook[$i]['book_name'];
                $arr[$i]['lastupdate'] = $cpbook[$i]['new_time'];
                $arr[$i]['chapter'] = $cpbook[$i]['chapter'];
            }
            $json_arr['code'] = "0000";
            $json_arr['data'] = $arr;
            echo(json_encode($json_arr,true));        
            //print_r($arr);
            exit();
        } else {
            $json['code'] = array('booklist' => 'error'); //书单没有
            echo(json_encode($json));
            exit();
        }
    }

    //书籍信息
    public function books($key, $cpid, $bookid) {
        //KEY值是否正确
        $this->keys($key, $cpid);
        //书籍授权验证
        $this->authorization($cpid,$bookid);
        $cvs = M('Book');
        $books = $cvs->where(array('fu_book' => $bookid))->find();
        $user = M('User');
        $truename = $user ->where(array('user_id' =>$books[author_id]))->find();
        //print_r($truename[name]);
        if (is_array($books)) {
            $arr[bookid] = $books[fu_book]; //书籍ID
            $arr[bookname] = $books[book_name]; //书籍名字
            $arr[authorname] = $books[author_name]; //作者名字
            $arr[cname] = $books[type_id]; //作品类型
            $arr[bookpic] = "http://www.ymzww.cn/Upload/Book/zhong/" . $books[upload_img]; //图片地址
            $arr[bksize] = $books[words]; //作品字数
            $arr[writestatus] = $books[state]; //作品状态 1连载 2完本
            $arr[zzjs] = $books[book_brief]; //作品简介
            $arr[createtime] = $books[time]; //创建时间
            $arr[gender] = $books[gender]; //男女频
            $arr[keywords] = $books[keywords]; //关键字
            $arr[truename] = $truename[name]; //作者真是姓名
            $json_arr['code'] = "0000";
            $json_arr['data'] = $arr;
            echo(json_encode($json_arr,true));        
            //print_r($arr);
            exit();
        } else {
            $json['code'] = array('books' => 'error'); //书籍没有
            echo(json_encode($json));
            exit();
        }
    }

    //章节列表
    public function showclist($key, $cpid, $bookid) {
        //KEY值是否正确
        $this->keys($key, $cpid);
        //书籍授权验证
        $this->authorization($cpid,$bookid);
        $where['fu_book'] = $bookid;
        //$where['attribute'] = array('lt',date('y-m-d H:i:s', time()));
        $conn = M('BookContent')->where($where)->order("num asc")->select();
        if (is_array($conn)) {
            for ($i = 0; $i < count($conn); $i++) {
                $arr[$i][chapterid] = $conn[$i][content_id]; //章节ID
                $arr[$i][chaptername] = $conn[$i][title]; //章节名字
                $arr[$i][number] = $conn[$i][number]; //章节字数
                $arr[$i][isvip] = $conn[$i][num]<21?1:0; //是否VIP
                $arr[$i][num] = $conn[$i][num]; //章节排序
                $arr[$i][time] = $conn[$i][attribute]; //章节排序
            }
            $json_arr['code'] = "0000";
            $json_arr['data'] = $arr;
            echo(json_encode($json_arr,true));        
            exit();
        } else {
            $json['code'] = array('showclist' => 'error'); //书籍没有
            echo(json_encode($json));
            exit();
        }
    }

    //章节内容
    public function content($key, $cpid, $bookid, $chapterid) {
        //KEY值是否正确
        $this->keys($key, $cpid);
        //书籍授权验证
        $this->authorization($cpid,$bookid);
        $content = M('BookContents')->where(array('content_id'=>$chapterid))->field('content')->find();; //读取内容
        if ($content) {
            $arr[content] = $content[content]; //创建时间
            $json_arr['code'] = "0000";
            $json_arr['data'] = $arr;
            echo(json_encode($json_arr,true));        
            exit();
        } else {
            $json['code'] = array('content' => 'error'); //章节没有
            echo(json_encode($json));
            exit();
        }
    }
    /*
     *返还VIP开始章节
     */
    public function vipcontent($key, $cpid, $bookid){
        //KEY值是否正确
        $this->keys($key, $cpid);
        //书籍授权验证
        $this->authorization($cpid,$bookid);
        $con['book_id'] = $bookid; 
        $arr = M('book_vip')->where($con)->select();
        if (is_array($arr)) {
            //print_r($arr);
            echo(json_encode($arr));
            exit();
        } else {
            $json['code'] = array('showclist' => 'error'); //书籍没有
            echo(json_encode($json));
            exit();
        }
    }
    //书籍授权验证
    protected function authorization($cpid,$bookid) {
        $where['book_id'] = $bookid;
        $where['cp_id'] = $cpid;
        $isok = M('CpBook')->where($where)->find();
        //print_r($isok);
        if (!is_array($isok)) {
            $json['code'] = array('books' => 'error'); //书籍授权不存在
            echo(json_encode($json));
            exit();
        }
    }

    //key进行判断 cp号
    protected function keys($key, $cpid) {
        if($key!==$this->key && $cp_id ===$this->cp_id){
            $json['error'] = array('key' => 'on'); //值错误
            echo(json_encode($json));
            exit();
        }
    }
}
