<?php
class  YueduAction  extends  Action{

     private  $cp_id=19;
     private  $cp_name='阅读小说网';
       public function index($cp) {
        $where['cp_id'] = $this->cp_id;
        $where['web_id'] = 4;
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time,state')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        // print_r($book);
        // exit();
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Yuedu');
    }

      function __construct() {
         parent::__construct();
         $this->cpbook=M("CpBook");
         $this->book=M('Book');
         $this->book_content=M('book_content');
         $this->book_contents=M('BookContents');
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
 
       //入库规则
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


       public function  test(){
       	    $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp");
            $xml= preg_replace("/[\s]{2,}/","",$xml);
            $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $rss=$this->xmltoarray($xml);
            foreach ($rss['bookid'] as $key => $value) {
            	echo  $this->getdata($value);

            }
             $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp?bookid=14443");
                 $xml= preg_replace("/[\s]{2,}/","",$xml);
                 $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
                 $bookinfo=$this->xmltoarray($xml);
                 print_r($bookinfo);

          //章节列表

                 $xml= file_get_contents("http://api.yuedunovel.com/yuemingChapList.asp?bookid=14332");
                 $xml= preg_replace("/[\s]{2,}/","",$xml);
                 $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
                 $chapterlist=$this->xmltoarray($xml);

                 print_r($chapterlist);


            //内容
              $xml= file_get_contents("http://api.yuedunovel.com/yuemingChapContent.asp?bookid=14332&chapid=347417");
         //   $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp?bookid=$partnerbookid");
           // $xml= preg_replace("/[\s]{2,}/","",$xml);
             $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $chapterinfo=$this->xmltoarray($xml);
        echo    $detail=(string)$this->getdata($chapterinfo['content']) ;

       }

       private  function  getdata($value){
          $value=str_replace("<![CDATA[","",$value);
          $value=str_replace("]]>","",$value); 
          return  $value;
       }

       //huo书籍列表
      public function booklist(){
          $booklist=$this->booklistapi();
         
          foreach ($booklist as $key => $value) {
            if($key<100){
              $cpbookid=$this->getdata($value);
            $bookinfo=$this->getbookinfo($cpbookid); 
              $bookname=(string)$this->getdata($bookinfo['bookname']); 
             $authorname=(string)$this->getdata($bookinfo['authorname']);
            $bookpic=(string)$this->getdata($bookinfo['coverpath']);
             $gender=2;         //1男2女
              $state=(int) $bookinfo['bookstatus']==0?1:2;;   //1 连载 2完本
             $keywords=(string)'阅读';
            $description=(string)$this->getdata($bookinfo['intro']);
             if(!$this->is_cpcreaete($cpbookid)) {
                   $rid=$this->createcpbook($cpbookid,$bookname,0);
                   if($rid>0&&!$this->is_create($bookname)){
                        //创建主库书籍
                        $rbookid=$this->createbook($bookname,$authorname,$bookpic,$gender,$state,$keywords,$description);
                        if($rbookid){
                          //去更新采集表 书籍id
                                $this->updatecpbook($cpbookid,$rbookid);
                               echo  $bookname."&nbsp;&nbsp;创建成功<br>";
                        }
                   }elseif($rid>0&&$b=$this->is_create($bookname)){
                                //更新已创建书籍
                                $this->updatecpbook($cpbookid,$b);
                                //更新章节数量
                                $this->updatechapternum($rid,$b);
                                $this->updateapinum($rid,$cpbookid);
                   }
             }else{
                echo $bookname."&nbsp;&nbsp;已采集<br>"; 
             }  
             }
            }
             echo "<p style='color:red'>采集完毕</p>";
      }

      private  function  updatecpbook($partnerbookid,$rbookid){ 
           $this->cpbook->where(array('cp_id'=>$this->cp_id,'fubook_id'=>$partnerbookid))->save(array('book_id'=>$rbookid));
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


          public function  booklistapi(){
	     	  $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp");
              $xml= preg_replace("/[\s]{2,}/","",$xml);
              $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
              $rss=$this->xmltoarray($xml);
              //print_r($rss);
             return  $rss['bookid'];
          }

       private function createbook($bookname,$author_name,$coverUrl,$gender,$state,$keywords,$book_brief)
       {
            $time=date('Y-m-d H:i:s',time());
            $data['web_id']=4;
            $data['cp_id']=$this->cp_id;
            $data['cp_name']=$this->cp_name;
            $data['book_name']=$bookname;
            $data['author_name']=$author_name;
            $data['type_id']=1;//这边需要做映射
            $data['gender']=$gender;
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

      private  function  getchapternums($bookid){
        return    $this->book_content->where(array('fu_book'=>$bookid))->count();
      }

        public   function  up(){
          $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id))->order('id desc')->select();
        //  echo  $this->cpbook->getLastSql();
          $this->assign('mycpbook', $mycpbook);
          $this->display();
      }

      public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();
            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
          //  print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  "$cpbook[fubook_name] 没有需要更新的内容</br>";
              //exit;
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                    $chapterid=$apicount[$i]['chapterid'];
                    $cpbookid=$cpbook['book_id'];
                    $title=$this->getdata($apicount[$i]['chaptername']);
                    $content=$this->getcontent($cpbook['fubook_id'],$chapterid);
                    $wordnum=$this->trimall($content);
                   $price=$this->price($wordnum);
                   $this->insertcon($cpbookid,$title,$wordnum,$price,$content,$id);
               }
                $this->updatechapternum($id,$cpbook['book_id']);
            echo "$cpbook[fubook_name] 更新结束</br>";
            }
           
      }

         //章节入库规则
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

      public  function  updatechapternum($id,$bookid){
            $num=$this->getchapternums($bookid);
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('num'=>$num));  
      }

      public  function  updateapinum($id,$partner_bookid){
            $num=count($this->getchapters($partner_bookid));
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('fu_num'=>$num));  
      }

            //获取第三方书籍详情
      public function  getbookinfo($partnerbookid){
      	 $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp?bookid=$partnerbookid");
         $xml= preg_replace("/[\s]{2,}/","",$xml);
         $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
         $bookinfo=$this->xmltoarray($xml);
        return  $bookinfo;
      }

       private  function  getchapters($partnerbookid){
               $xml= file_get_contents("http://api.yuedunovel.com/yuemingChapList.asp?bookid=$partnerbookid");
                 $xml= preg_replace("/[\s]{2,}/","",$xml);
                 $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
                 $chapterlist=$this->xmltoarray($xml);
	         return $chapterlist['chapters']['chapter'];
      }

       private  function  getcontent($partner_bookid,$partner_chapterid){
             $xml= file_get_contents("http://api.yuedunovel.com/yuemingChapContent.asp?bookid=$partner_bookid&chapid=$partner_chapterid");
         //   $xml= file_get_contents("http://api.yuedunovel.com/yuemingBookList.asp?bookid=$partnerbookid");
           // $xml= preg_replace("/[\s]{2,}/","",$xml);
             $xml=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $chapterinfo=$this->xmltoarray($xml);
          $detail=(string)$this->getdata($chapterinfo['content']) ;
           return   $detail=(string)$detail;
      }
      

     public function  price($number){
       return  floor($number/1000*6);
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


    

















}
?>