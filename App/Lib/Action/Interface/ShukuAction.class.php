<?php
    /**
     * Project Form object, extends ThinkPHP [ WE CAN DO IT JUST THINK IT ]
     * 开发商：南京阅明文化传播有限公司
     * Author：娄亚非<815643475@qq.com> 2017-8-5
     * 内 容 ：书库接口
     */
class ShukuAction extends Action {
    public function index($cp) {
        $keyword = isset($_REQUEST[keyword]) ? trim(urldecode($_REQUEST[keyword])) : '';
        if ($keyword) {
            $where['fubook_name'] = array('like', '%'.$keyword.'%');
            $_GET['keyword'] = $keyword;
        }

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
    private $key = "3f0753b6246ffec9ccbb788e7de258e4";
    private $cp_id = 11;
    public function booklist($key, $cpid) {
        //KEY值是否正确
        $this->keys($key, $cpid);
        $cp = D('BooksInterfaceView');
        $con['CpBook.cp_id'] = $cpid;
        $cpbook = $cp->where($con)->select();
        if (is_array($cpbook)) {
            for ($i = 0; $i < count($cpbook); $i++) {
                $arr[$i][bookid] = $cpbook[$i][book_id];
                $arr[$i]['bookname'] = $cpbook[$i]['fubook_name'];
                $arr[$i]['chapter'] = $cpbook[$i]['chapter'];
            }
            //print_r($arr);
            $arr['code'] = array('booklist' => 'correct'); 
            echo(json_encode($arr));             
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
            $arr[cname] = $this->booktype($books[type_id]); //作品类型
            $arr[bookpic] = "http://www.ymzww.cn/Upload/Book/zhong/" . $books[upload_img]; //图片地址
            $arr[bksize] = $books[words]; //作品字数
            $arr[writestatus] = $books[state]; //作品状态 1连载 2完本
            $arr[zzjs] = $books[book_brief]; //作品简介
            $arr[createtime] = $books[time]; //创建时间
            $arr[gender] = $books[gender]; //男女频
            $arr[keywords] = $books[keywords]; //关键字
            $arr[truename] = $truename[name]; //作者真是姓名
            $arr['code'] = array('books' => 'correct'); 
            echo(json_encode($arr));
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
                $arr[$i][num] = $conn[$i][num]; //章节排序
                $arr[$i][create_date] = $conn[$i][attribute]; //章节排序
            }
            //print_r($arr);
            $arr['code'] = array('showclist' => 'correct'); 
            echo(json_encode($arr));
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
            $find = array("&lsquo;","&rsquo;","&ldquo;","&rdquo;","&hellip;");
            $content['content'] = str_replace($find, "",$content['content'] );
            $arr[content] = $content[content]; //创建时间        
            //print_r($arr);
            $arr['code'] = array('content' => 'correct'); 
            echo(json_encode($arr));
            //die;            
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

    //小说类型
    private function booktype($booktype) {
        $tytes = array(
            '1' => 14,
            '2' => 10,
            '3' => 11,
            '4' => 17,
            '5' => 17,
            '6' => 17,
            '7' => 17,
            '8' => 3,
            '9' => 8,
            '10' => 2,
            '11' => 17,
            '12' => 17,
            '13' => 1,
            '14' => 17,
            '15' => 5,
            '16' => 17,
            '17' => 17,
        );
        return $tytes[$booktype];
    }

}
