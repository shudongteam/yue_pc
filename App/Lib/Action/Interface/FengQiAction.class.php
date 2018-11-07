<?php
    /**
     * 说明：风起采集接口
     * Project Form object, extends ThinkPHP [ WE CAN DO IT JUST THINK IT ]
     * 开发商：南京阅明文化传播有限公司
     */
    /*
    *书籍列表 http://api.fqzww.com/ymzww/bookList 
    *章节列表 http://api.fqzww.com/ymzww/bookChapter?book_id=647
    *章节内容 http://api.fqzww.com/ymzww/chapterContent?book_id=647&chapter_id=260136
    */
class FengQiAction extends Action {

      private  $cp_id=44;
      private  $cp_name='风起';
      private  $cpbook;
      private  $book;
      private  $book_content;
      private  $book_contents;

       function __construct() {
         parent::__construct();
         $this->cpbook=M("cp_book");
         $this->book=M('Book');
         $this->book_content=M('book_content');
         $this->book_contents=M('BookContents');
       }

       private  function  getdata($value){
              $value=str_replace("<![CDATA[","",$value);
              $value=str_replace("]]>","",$value); 
              return  $value;
      }

       //测试
       public function  test(){
         $content=file_get_contents("http://api.fqzww.com/ymzww/chapterContent?book_id=647&chapter_id=260136");
          $content = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$content);
          $con=$this->xmltoarray($content);
          // $con=$this->xmltoarray($content);
            //$con =  $this->getdata($con[chapter][content]);
           // $content = simplexml_load_string($content);
         //$con = json_decode($content);
         //->chapter->content;
           
            //$content = simplexml_load_string($content);
          //$con = $content->chapter->content;
         // $xml = file_get_contents("http://api.fqzww.com/ymzww/chapterContent?book_id=647&chapter_id=260136");
         // $con = $this->getdata($xml);
         // $con = json_decode($xml);
          //$detail=json_decode($xml);
           //$detail=(string)$detail->content;
         //$list=simplexml_load_string($xml);
         //$con = (string)$list->chapter->content;
          //$con = json_decode($con);
           // $list=$this->xmltoarray($xml);
           // $list = $list[chapter][content];
         // print_r($con);
         $contents = $con['chapter']['content']['p'];
         foreach($contents as $k=>$v){
            $str .=$v."<br>"; 
         }
         echo $str;
         //print_r($con['chapter']['content']['p']);
       }
    //作品查看
    public function index($cp) {
        $where['cp_id'] = $cp;
        $where['web_id'] = 4;
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $book = $bbb->where($where)->field('book_id,book_name,author_name,cp_name,state,is_show,chapter,words,time,state')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@FengQi');
    }

      //获取书单列表
       public function  booklistapi(){
        header("Content-type:text/html;charset=utf-8");
         $xml= file_get_contents("http://api.fqzww.com/ymzww/bookList");
         // print_r($xml);
         //标记一个中括号表达式的开始。要匹配 [，请使用 \[。
         $xml= str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
         $xml= preg_replace("/[\s]{2,}/","",$xml);
         $rss=$this->xmltoarray($xml);
         //print_r($rss);exit;
         return  $rss['item'];
       }
       //获取章节列表信息
       private  function  getchapters($partner_bookid){
           //$time=$this->datetime();
           $xml = file_get_contents("http://api.fqzww.com/ymzww/bookChapter?book_id=".$partner_bookid);
           $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
           $chapterlist=$this->xmltoarray($xml);
           //print_r($chapterlist);exit;
           return $chapterlist['chapter'];
      }
      //获取章节内容
        private  function  getcontent($partner_bookid,$partner_chapterid){
            //$time=$this->datetime();
            $content=file_get_contents("http://api.fqzww.com/ymzww/chapterContent?book_id=".$partner_bookid."&chapter_id=".$partner_chapterid);
            $content = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$content);
            $con=$this->xmltoarray($content);
             $contents = $con['chapter']['content']['p'];
         foreach($contents as $k=>$v){
            $str .=$v."<br>"; 
         }
         return $str;
          // 	$content = simplexml_load_string($content);
         	// return (string)$content->chapter->content;
            // $chapterinfo=simplexml_load_string($xml);
            //print($chapterinfo);
      }

        //huo书籍列表
      public function booklist(){
          $booklist=$this->booklistapi();
         //print_r($booklist);exit;
          foreach ($booklist as $key => $value) {
            if($key<100){
              $cpbookid=$value['book_id'];   //需要修改   
              $bookname=(string)$this->getdata($value['title']);   //需要修改
              $authorname=(string)$this->getdata($value['author']);  //需要修改
              $bookpic=(string)$value['imageLink'];//需要修改
              $gender=2;         //1男2女                  //需要修改
              $state=(int) $value['over']==0?1:2;;   //1 连载 2完本
              $keywords=(string)$value['tag'];    //需要修改
              $description=(string)$this->getdata($value['comment']);//需要修改
              $word = $value['word'];
              $type = (string)$this->getdata($value['type']);
              //echo $word;exit;
             if(!$this->is_cpcreaete($cpbookid)) {
                   $rid=$this->createcpbook($cpbookid,$bookname,0);
                   if($rid>0&&!$this->is_create($bookname)){
                        //创建主库书籍
                        $rbookid=$this->createbook($bookname,$authorname,$bookpic,$gender,$state,$keywords,$description,$type,$word);
                        //echo $rbookid;exit;
                        if($rbookid){
                          //去更新采集表 书籍id
                                $this->updatecpbook($cpbookid,$rbookid);
                                echo  $bookname."创建成功<br>";
                        }
                   }elseif($rid>0&&$b=$this->is_create($bookname)){
                                //更新已创建书籍
                                $this->updatecpbook($cpbookid,$b);
                                //更新章节数量
                                $this->updatechapternum($rid,$b);
                                $this->updateapinum($rid,$cpbookid);
                   }
             }else{
                echo $bookname."已采集<br>"; 
             }
             }
            }
            echo "<p style='color:red'>采集完毕</p>";
      }

      //检测采集书单里面是否有这本书
      private function is_cpcreaete($partnerbookid){
            $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id, 'fubook_id' =>$partnerbookid))->find();
            if(is_array($mycpbook)){
                return    $mycpbook['id'];
            }else{
                return   0;
            }
      }

      //判断这本书是不是新书
      private  function is_create($bookname){
        $isbook=$this->book->where(array('fu_web'=>0,'cp_id'=>$this->cp_id,'book_name'=>$bookname))->find();
        if(is_array($isbook)){
         return    $isbook['book_id'];
        }else{
          return   0;
        }
      }

      //入cpbook库
      private  function createcpbook($partnerbookid,$partnerbookname,$type){
            $time=date('Y-m-d H:i:s',time());
            $data['fubook_id'] = (int)$partnerbookid;
                $data['cp_id'] = $this->cp_id;
                $data['type'] = $type;
                $data['fubook_name'] = $partnerbookname;
                $data['fu_num'] = 0;
                $data['fu_time'] =$time;
                $data['book_id'] = 0;
                $data['num'] = 0;
                $data['book_time']=$time;
                $data['time'] = $time;
                $rid=$this->cpbook->add($data);
            if($rid){
              $this->updateapinum($rid,$partnerbookid);
              return  $rid;
            }else{
              echo  "创建cp书籍出错";
            }
            
      }
      //入book库
      private function createbook($bookname,$author_name,$coverUrl,$gender,$state,$keywords,$book_brief,$type,$word)
       {
            $time=date('Y-m-d H:i:s',time());
            $data['web_id']=4;
            $data['cp_id']=$this->cp_id;
            $data['cp_name']=$this->cp_name;
            $data['book_name']=$bookname;
            $data['author_name']=$author_name;
            $data['type_id']=$this->gettype($type);//这边需要做映射
            $data['gender']=$gender;
            $data['words'] = $word;
            $imgname=time();
            $r=$this->getImage($coverUrl,'./Upload/Book/da',$imgname.'.jpg',$type=0);
            $r=$this->getImage($coverUrl,'./Upload/Book/zhong',$imgname.'.jpg',$type=0);
            $r=$this->getImage($coverUrl,'./Upload/Book/xiao',$imgname.'.jpg',$type=0);
            $data['upload_img']=$r['file_name'];
            $data['state']=$state;
            $data['vip']=0;
            $data['signing']="SS";
            $data['keywords']=$keywords;
            $data['book_brief']=$book_brief;
            $data['audit']=2;
            $data['time']= $time;
            $data['new_time']= $time;
            $data['is_show']=1;
            $rid=$this->book->add($data);
            $bang = M('BookStatistical'); //作品榜单
            $bangdan = $bang->add(array('book_id' => $rid));
            $isok =$this->book->where(array('book_id' => $rid))->save(array('fu_book' =>$rid));
            //api里面的章节
            return  $rid;
       }
       //更新cpbook的book_id
      private  function  updatecpbook($partnerbookid,$rbookid){ 
           $this->cpbook->where(array('cp_id'=>$this->cp_id,'fubook_id'=>$partnerbookid))->save(array('book_id'=>$rbookid));
      }
      //cpbook的更新章节数
      public  function  updatechapternum($id,$bookid){
            $num=$this->getchapternums($bookid);
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('num'=>$num));  
      }
      //cpbook接口章节数
      public  function  updateapinum($id,$partner_bookid){
            $num=count($this->getchapters($partner_bookid));
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('fu_num'=>$num));  
      }
    
       public   function  up(){    
          $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id))->order('id desc')->select();
          //  echo  $this->cpbook->getLastSql();
          //print_r($mycpbook);die;
          $this->assign('mycpbook', $mycpbook);
          $this->display();
      }

        public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();

            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
            $num = count($apicount);
            // $this->updatechapternum($id,$cpbook['book_id']);
            // $this->updateapinum($id,$cpbook['fubook_id']);
             // echo "已更新章节:".$havecount;
             // echo "<br>";
             // echo "接口章节数:".$num;
             // echo "<hr>";
             //echo "<br>";
            // print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  $cpbook[fubook_name]."&nbsp;&nbsp;没有需要更新的内容</br>";
              //exit;
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                   $chapterid=$apicount[$i]['chapter_id'];
                   $cpbookid=$cpbook['book_id'];
                   $title=(string)$this->getdata($apicount[$i]['chapter_title']);
                   $content=$this->getcontent($cpbook['fubook_id'],$chapterid);
                   //$content = html_entity_decode($content1, ENT_QUOTES, 'UTF-8');
                  // $content = str_replace(array("<p>","","</p>")," char(10)",$content1);
                   //$content = strip_tags ($content);
                   $wordnum=$this->trimall($content);
                   $price=$this->price($wordnum); 
                   // echo $cpbookid;
                   // echo "<br>";
                   // echo $title;
                   // echo "<br>";
                   // echo $wordnum;
                   // echo "<br>";
                   // echo $price;
                   // echo "<br>";
                   // echo $content;
                   // echo "<br>";
                   // echo $id;
                   // echo "<br>";
                   // exit;
                   $this->insertcon($cpbookid,$title,$wordnum,$price,$content,$id);                
               }
              $this->updatechapternum($id,$cpbook['book_id']);
            $this->updateapinum($id,$cpbook['fubook_id']);
            echo $cpbook[fubook_name]."&nbsp;&nbsp;更新结束</br>";
            }
            
      }

       private  function  getchapternums($bookid){
        return    $this->book_content->where(array('fu_book'=>$bookid))->count();
      }

      private  function datetime(){
        return    date('YmdHis',time());
      }

      private  function insertcon($bookid,$title,$numbers,$price,$content,$id){
          $time=$time=date('Y-m-d H:i:s',time());
          $data['fu_book'] = $bookid;
          //获取当前最大章节
          //$num=$this->book_content->where(array('fu_book'=>$bookid))->max('num');
          $num=$this->book_content->where(array('fu_book'=>$bookid))->count();
          $data['num'] = $num+1;
          $data['title'] =$title;
          $data['number'] = $numbers;
          $price=$num>20?$price:0;
          $data['the_price'] =$price;
          $data['caudit'] = 2;
          $data['attribute'] = $time;
          $data['time'] = $time;
          $iscon = $this->book_content->add($data);
          if($iscon){
            $iscons = $this->book_contents->add(array('content_id' => $iscon, 'content' =>$content));
          }
           if ($iscon && $iscons) {
                $this->book->where(array('book_id' => $bookid))->save(array('chapter' => array('exp', "chapter+1"), 'words' => array('exp', "words+$numbers")));
                $re = $this->book->where(array('book_id' => $bookid))->find();
                $chapter = $re['chapter'];
                $words = $re['words'];
                $this->assign_book_update($chapter,$words,$bookid);
              //  $this->cpbook->where(array('id' => $id))->save(array('fu_num' => array('exp', "fu_num+1")));
           } else {
            //    $cpboks->where(array('id' => $id))->save(array('termination' => 1));
                 echo $id . "<font color=red>章节更新出错:请技术进行检查</font></br>";
                exit();
           }
      }

      //连载授权书章节字数更新
      public function assign_book_update($chapter,$words,$bookid){
          $data=$this->book->where(array('fu_book'=>$bookid))->select();
          foreach($data as $k=>$v){
             $this->book->where(array('book_id' => $v['book_id']))->save(array('chapter' => $chapter, 'words' => $words));
          }
      }

      //字数统计函数
    private  function trimall($str) {//删除空格
        $qian = array(" ", "　", "\t", "\n", "\r");
        $hou = array("", "", "", "", "");
        $str = str_replace($qian, $hou, $str);
        $str = mb_convert_encoding($str, 'GBK', 'UTF-8');
        preg_match_all("/[" . chr(0xa1) . "-" . chr(0xff) . "]{2}/", $str, $m);
        $mu = count($m[0]);
        unset($str);
        unset($m);
        return $mu;
    }

    private function xmltoarray($xml) {
        $arr = $this->xml_to_array ( $xml );
        $key = array_keys ( $arr );
         return $arr [$key [0]];
    }
    private  function xml_to_array($xml) {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all ( $reg, $xml, $matches )) {
          $count = count ( $matches [0] );
          $arr = array ();
          for($i = 0; $i < $count; $i ++) {
            $key = $matches [1] [$i];
            $val = $this->xml_to_array ( $matches [2] [$i] ); // 递归
            if (array_key_exists ( $key, $arr )) {
              if (is_array ( $arr [$key] )) {
                if (! array_key_exists ( 0, $arr [$key] )) {
                  $arr [$key] = array (
                      $arr [$key] 
                  );
                }
              } else {
                $arr [$key] = array (
                    $arr [$key] 
                );
              }
              $arr [$key] [] = $val;
            } else {
              $arr [$key] = $val;
            }
          }
          return $arr;
        } else {
          return $xml;
        }
      }

	 /* 
	     *功能：php完美实现下载远程图片保存到本地 
	     *参数：文件url,保存文件目录,保存文件名称，使用的下载方式 
	     *当保存文件名称为空时则使用远程文件原来的名称 
	  */ 
	  private function getImage($url,$save_dir='',$filename='',$type=0){ 
	     if(trim($url)==''){ 
	        return array('file_name'=>'','save_path'=>'','error'=>1); 
	     } 
	     if(trim($save_dir)==''){ 
	        $save_dir='./'; 
	     } 
	     if(trim($filename)==''){//保存文件名 
	        $ext=strrchr($url,'.'); 
	        if($ext!='.gif'&&$ext!='.jpg'){ 
	            return array('file_name'=>'','save_path'=>'','error'=>3); 
	        } 
	        $filename=time().$ext; 
	     } 
	     if(0!==strrpos($save_dir,'/')){ 
	        $save_dir.='/'; 
	     } 
	     //创建保存目录 
	     if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){ 
	        return array('file_name'=>'','save_path'=>'','error'=>5); 
	     } 
	     //获取远程文件所采用的方法  
	     if($type){ 
	        $ch=curl_init(); 
	        $timeout=5; 
	        curl_setopt($ch,CURLOPT_URL,$url); 
	        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
	        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
	        $img=curl_exec($ch); 
	        curl_close($ch); 
	     }else{ 
	        ob_start();  
	        readfile($url); 
	        $img=ob_get_contents();  
	        ob_end_clean();  
	     } 
	    //$size=strlen($img); 
	    //文件大小  
	     $fp2=@fopen($save_dir.$filename,'a'); 
	     fwrite($fp2,$img); 
	     fclose($fp2); 
	     unset($img,$url); 
	     return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0); 
	    } 
	    /*
	     *计算钱数
	     */
	     public function  price($number){
	       return  floor($number/1000*6);
	     }
	     //分类
	 	public function gettype($booktype){
	    	   $tytes = array(
	            '玄幻' => 4,
	            '都市' => 11,
	            '历史' => 2,
	            '科幻' => 8,
	            '灵异' => 5,
	            '游戏' => 9,
	            '体育' => 3,
	            '现代言情' => 13,
	            '古代言情' => 13,
	            '幻想言情' => 13,
	            '青春校园' => 12,
	        );
	        return $tytes[$booktype];
          // '1' => "悬疑",
          //   '2' => "历史",
          //   '3' => "军事",
          //   '4' => "玄幻",
          //   '5' => "奇幻",
          //   '6' => "仙侠",
          //   '7' => "武侠",
          //   '8' => "科幻",
          //   '9' => "游戏",
          //   '10' => "同人",
          //   '11' => "都市",
          //   '12' => "校园",
          //   '13' => "言情",
          //   '14' => "穿越",
          //   '15' => "重生",
          //   '16' => "豪门",
          //   '17' => "职场",
	    }

     /*
        *章节列表
        */
      public function  chapterlist($partnerbookid,$bookid){
        header("Content-type:text/html;charset=utf-8");
        $xml = file_get_contents("http://api.fqzww.com/ymzww/bookChapter?book_id=".$partnerbookid);
        //$xml = preg_replace("/[\s]{2,}/","",$xml);
        $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
        $chapterlist=$this->xmltoarray($xml);
        // print_r($chapterlist['chapter']);
        // die();
        $partner_content=M('BookContent1');
        $num=$partner_content->where(array('fu_book'=>$bookid))->max('num');
        if($num>0){
          foreach ($chapterlist['chapter']  as $key => $value) {
            if($value['chapter_id']>0){
                 $data['fu_book']=(int)$bookid;
                 $data['num']=$num+1;
                 $data['title']=$this->getdata($value['chapter_title']);
                 $data['number']=(int)$value['words'];
                 $data['the_price']=$num+1>20?$this->price($value['words']):0;
                 $data['caudit']=1;
                 $data['attribute']=1;
                 $data['attribute']=date('Y-m-d H:i:s',$value['chapter_date']);
                 $data['time']=date('Y-m-d H:i:s',$value['chapter_date']);
                 $contentid=$partner_content->add($data);
                 if($contentid>0){
                  $this->chapter($bookid,$partnerbookid,$value['chapter_id'], $contentid,$value['words']);
                 }
            }
          }
        }else{
          foreach ($chapterlist['chapter']  as $key => $value) {
            if($value['chapter_id']>0){
                 $data['fu_book']=(int)$bookid;
                 $data['num']=$key+1;
                 $data['title']=$this->getdata($value['chapter_title']);
                 $data['number']=(int)$value['words'];
                 $data['the_price']=$key+1>20?$this->price($value['words']):0;
                 $data['caudit']=1;
                 $data['attribute']=1;
                 $data['attribute']=date('Y-m-d H:i:s',$value['chapter_date']);
                 $data['time']=date('Y-m-d H:i:s',$value['chapter_date']);
                 $contentid=$partner_content->add($data);
                 if($contentid>0){
                  $this->chapter($bookid,$partnerbookid,$value['chapter_id'], $contentid,$value['words']);
                 }
            }
          }
         }
       }
      /*
        *章节内容
        */
       public function chapter($bookid,$partner_bookid,$partner_chapterid,$contentid,$chaptersize){
            //计算当前章节的num
            $xml = file_get_contents("http://api.fqzww.com/ymzww/chapterContent?book_id=".$partner_bookid."&chapter_id=".$partner_chapterid);
            //$xml = file_get_contents("http://api.fqzww.com/ymzww/chapterContent?book_id=647&chapter_id=260136");
            // $xml = preg_replace("/[\s]{2,}/","",$xml);
            $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $chapterinfo=$this->xmltoarray($xml);
            $content = $chapterinfo[chapter][content][p];
            foreach ($content as $key => $value) {
              $contents.=$value."\r\n"; 
            }
            // echo "<hr>";
            // print_r($contents);
            // exit();
            $bookcontent=M('BookContents1');
            $data[content_id] = $contentid;
            $data[content] = $contents;
            $res=$bookcontent->add($data);
            if ($res) {
            //更新字数
            $datas['chapter'] = array('exp', "chapter+1"); //总数多少章
            $datas['words'] = array('exp', "words+$chaptersize");
            $book = M('Book1');
            $book->where(array('book_id' => $bookid))->save($datas);
            $datac[fu_num] = array('exp', "fu_num+1"); //总数多少章
            $datac[num] = array('exp', "num+1"); //总数多少章
            $cpbook = M('CpBook1')->where(array('book_id' => $bookid))->save($datac);
            }
       }
}