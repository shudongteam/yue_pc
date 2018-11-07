<?php
    //书丛接口
    class ShucongAction extends Action {
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
        private $cp_id=43;
        private $key='e7c5f7b77d520913cfa2a4e8b187cc61';
        //小说列表
        public function booklist($key,$cpid) {
           $this->keys($key,$cpid);
           $book = D('BooksInterfaceView');
           $books = $book->where(array('CpBook.cp_id' => $this->cp_id))->select();
            if (is_array($books)) {
                header("Content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
                echo "<document>";
                echo "<items>";
                foreach ($books as $key => $value) {
                    $k = $key+1;
                    if($value[book_id]){
                      $lastupdate =strtotime($value[new_time]);
                      $type = $this->booktype($value[type_id]);
                      echo "<item id=\"$k\">";
                      echo "<bookid>$value[book_id]</bookid>";//书号
                      echo "<title><![CDATA[$value[fubook_name]]]></title>";//书名
                      echo "<comment><![CDATA[$value[book_brief]]]></comment>";//简介
                      echo "<category><![CDATA[$type]]></category>";//分类
                      echo "<lastupdate>$lastupdate]</lastupdate>";//最后时间戳
                      echo "<author><![CDATA[$value[author_name]]]></author>";//作者名称
                      echo "<isvip>1</isvip>";//0免费1VIP
                      echo "<url><![CDATA[http://www.ktread.com/Interface/Shucong/books/bookid/$value[book_id]]]></url>";//书籍信息URL
                      echo "</item>";
                    }
                    
            }
                echo "</items>";
	            echo "</document>";
            }
        }

        //书籍信息
        public function books($key,$cpid,$bookid) {
            $this->keys($key,$cpid);
            $cvs = M('Book');
            $books = $cvs->where(array('fu_book' => $bookid))->field('fu_book,book_name,type_id,upload_img,book_brief,author_name,time,new_time,words,state')->find();
            if (is_array($books)) {
                $state = $books[state]==1?0:1;
                $time = strtotime($books[time]);
                $lasttime = strtotime($books[new_time]);
                $type = $this->booktype($books[type_id]);
                header("Content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
                echo "<document>";
                echo "<info>";
                echo "<title><![CDATA[$books[book_name]]]></title>";//书名
                echo "<bookid>$books[fu_book]</bookid>";//书号
                echo "<category><![CDATA[$type]]></category>";//分类http://www.ymzww.cn/Upload/Book/zhong/5982beaba48db.jpg
                echo "<image_big><![CDATA[http://www.ymzww.cn/Upload/Book/da/$books[upload_img]]]></image_big>";//大封面
                echo "<image_small><![CDATA[http://www.ymzww.cn/Upload/Book/zhong/$books[upload_img]]]></image_small>";//小封面
                echo "<comment><![CDATA[$books[book_brief]]]></comment>";//简介
                echo "<author><![CDATA[$books[author_name]]]></author>";//作者
                echo "<postdate>$time</postdate>";//发布时间戳
                echo "<lastupdate>$lasttime</lastupdate>";//最后时间戳
                echo "<size>$books[words]</size>";//总字数
                echo "<fullflag>$state</fullflag>";//0连载，1完本
                echo "<isvip>1</isvip>";//0公共，1收费
                echo "<chaptersurl><![CDATA[http://www.ktread.com/Interface/Shucong/showclist/bookid/$books[fu_book]]]></chaptersurl>";//章节列表
                echo "</info>";
                echo "</document>";
            }
        }
        //章节信息
        public function showclist($key,$cpid,$bookid) {
            $this->keys($key,$cpid);
            $bcon = M('BookContent');
            $con[fu_book] = $bookid;
            $con[attribute] = array('lt',date('Y-m-d H:i:s'));
            $books = $bcon->where($con)->field('content_id,num,title,number,attribute')->order('content_id asc')->select();
            // echo "<pre>";
            // print_r($books);
            // die();
            if (is_array($books)) {
                header("Content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
                echo "<document>";
                echo "<items>";
                foreach ($books as $key => $value) {
                    $isvip = $value['num']>20?1:0;
                    $time = strtotime($value[attribute]); 
                    echo  "<item>";
                    echo  "<cid>$value[content_id]</cid>";//章节号，唯一标识
                    echo  "<isvip>$isvip</isvip>";//0免费，1收费
                    echo  "<postdate> $time </postdate>";//发布时间戳
                    echo  "<chaptername><![CDATA[$value[title]]]></chaptername>";//卷名
                    echo  "<chaptertype>0</chaptertype>";//类型：0章节，1卷
                    echo  "<chapterurl><![CDATA[http://www.ktread.com/Interface/Shucong/content/bookid/$bookid/cid/$value[content_id]]]></chapterurl>";//章节内容url
                    echo  "</item>";
                }
                echo "</items>";
                echo "</document>";
            }
        }
        //章节内容
        public function content($key,$cpid,$bookid,$cid) {
            $this->keys($key,$cpid);
            $condetail=M('BookContents')->join('hezuo_book_content ON hezuo_book_content.content_id = hezuo_book_contents.content_id')->where(array('hezuo_book_contents.content_id'=>$cid))->field('hezuo_book_contents.content,hezuo_book_content.title')->find();
            if ($condetail) {
                $content = html_entity_decode($condetail[content]);
                header("Content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>";
                echo "<document>";
                echo "<chaptertitle><![CDATA[$condetail[title]]]></chaptertitle>";//章节名称
                echo "<chaptercontent><![CDATA[$content]]></chaptercontent>";//章节内容
                echo "</document>";
            }
        }
        //小说类型
        private function booktype($booktype) {
            $tytes = array(
                '1' => "悬疑",
                '2' => "历史",
                '3' => "军事",
                '4' => "玄幻",
                '5' => "奇幻",
                '6' => "仙侠",
                '7' => "武侠",
                '8' => "科幻",
                '9' => "游戏",
                '10' => "同人",
                '11' => "都市",
                '12' => "校园",
                '13' => "言情",
                '14' => "穿越",
                '15' => "重生",
                '16' => "豪门",
                '17' => "职场",
            );
            return $tytes[$booktype];
        }
        //验证
        protected function keys($key,$cpid){
          if($cpid!=$this->cp_id || $key!=$this->key){
            $json_arr['code']  = "9999";
            $json_arr['error'] = array('cpid' =>'error','key' =>'error');
            echo (json_encode($json_arr));
            exit();
          }
        }
    }
    
?>