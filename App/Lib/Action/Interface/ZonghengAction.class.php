<?php
class  ZonghengAction   extends  Action{
	    private  $cp_id=17;
      private  $secret='yu5rchxb7xp09yeq';
      private  $apikey='zcs42ibgk9';
      private  $cp_name='纵横';
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
        $this->display('Interface@zongheng');
    }

      private function sign($data){
        ksort($data);
        foreach ($data as $key => $value) {
            $str.=$key.$value;
        }
        $this->secret.$str.$this->secret;
        return  $str=strtoupper(md5($this->secret.$str.$this->secret));
      }

      public function dealkeywords(){
        if(' '){echo  1;}
        $keywords="残唐  五代 杨行密  吕方   淮南  天下节度   ";
        $arr=explode(' ', trim($keywords));
        print_r($arr);
        $keystr="";
        foreach ($arr as $key => $value) {
           $value=trim($value,' ');
           $value=str_replace(' ', '', $value);
          
          if($value){
             $keystr.=trim($value,'　').',';
          } 

        }
        echo  substr($keystr,0,-1) ;
      }

	  //huo书籍列表
      public function booklist(){
      	header("Content-type:text/html;charset=utf-8");
        $data['api_key']=$this->apikey;
        $data['method']='api.books';
        $sig=$this->sign($data);
       // echo  "http://api.zongheng.com/commonrest?method=api.books&api_key=".$this->apikey.'&sig='.$sig;
        $booklistjson=  file_get_contents("http://api.zongheng.com/commonrest?method=api.books&api_key=".$this->apikey.'&sig='.$sig);
        $rss=json_decode($booklistjson);
        $booklist=$rss->result;
        $num = count($booklist);
        // echo $num;
        // echo "<hr>";
        //print_r($booklist);exit;
        // for($i=0;$i<$num;$i++){
        //   // echo $booklist[$i];
        //   // echo "<br>";
        //    $bookinfo=$this->getbookinfo($booklist[$i]); 
        //    //print($bookinfo);exit;
        //    if (!$this->is_cpcreaete($booklist[$i])){
        //          $rid=$this->createcpbook($bookinfo->bookId,$bookinfo->bookName,0);
        //          if($rid>0&&!$this->is_create($bookinfo->bookName)){
        //               $keywords=$bookinfo->keywords;
        //               $description=$bookinfo->description;
        //               //创建主库书籍
        //               $rbookid=$this->createbook($bookinfo->bookName,$bookinfo->authorName,$bookinfo->coverUrl,1,$bookinfo->status,$keywords,$description);
        //               if($rbookid){
        //                 //去更新采集表 书籍id
        //                       echo "书籍采集成功";
        //                       $this->updatecpbook($bookinfo->bookId,$rbookid);
        //               }
        //          }elseif($rid>0&&$b=$this->is_create($bookinfo->bookName)){
        //                       //更新已创建书籍
        //                       $this->updatecpbook($bookinfo->bookId,$b);
        //                       //更新章节数量
        //                       $this->updatechapternum($rid,$b);
        //                       $this->updateapinum($rid,$bookinfo->bookId);
        //          }
        //    }else{
        //         echo $bookinfo->bookName."已采集<br>"; 
        //      } 
        // }
        foreach ($booklist as $key => $value) {
          if($key<1000){
           $bookinfo=$this->getbookinfo($value); 
           //print($bookinfo);exit;
           if (!$this->is_cpcreaete($value)) {
                 $rid=$this->createcpbook($bookinfo->bookId,$bookinfo->bookName,0);
                 if($rid>0&&!$this->is_create($bookinfo->bookName)){
                      $keywords=$bookinfo->keywords;
                      $description=$bookinfo->description;
                      //创建主库书籍
                      $rbookid=$this->createbook($bookinfo->bookName,$bookinfo->authorName,$bookinfo->coverUrl,1,$bookinfo->status,$keywords,$description);
                      if($rbookid){
                        //去更新采集表 书籍id
                              echo  $bookinfo->bookName."&nbsp;&nbsp;书籍采集成功<br>";
                              $this->updatecpbook($bookinfo->bookId,$rbookid);
                      }
                 }elseif($rid>0&&$b=$this->is_create($bookinfo->bookName)){
                              //更新已创建书籍
                              $this->updatecpbook($bookinfo->bookId,$b);
                              //更新章节数量
                              $this->updatechapternum($rid,$b);
                              $this->updateapinum($rid,$bookinfo->bookId);
                 }
           }else{
                echo $bookinfo->bookName."&nbsp;&nbsp;已采集<br>"; 
             } 
         }  
        }
         echo "<p style='color:red'>采集完毕</p>";
       }
      
       public function  up(){   
          $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id))->order('id desc')->select();
        // $mycpbook = M('cp_book  as  cp')->field('status')->join('book  as  b  on cp.book_id = b.book_id')->where(array("b.status"  =>  1,"cp.cp_id"=>$this->cp_id))->select();
        //   print_r($mycpbook);
          $this->assign('mycpbook', $mycpbook);
          $this->display();
       }

       //创建书籍
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

       //获取第三方书籍详情
      public function  getbookinfo($partnerbookid){
      	$data1['api_key']=$this->apikey;
        $data1['method']='book';
        $data1['book_id']=$partnerbookid;
        $sig=$this->sign($data1);
        $xml= file_get_contents("http://api.zongheng.com/commonrest?method=book&book_id=$partnerbookid&api_key=".$this->apikey.'&sig='.$sig);
        // echo "http://api.zongheng.com/commonrest?method=book&book_id=$partnerbookid&api_key=".$this->apikey.'&sig='.$sig;
        $bookinfo=json_decode($xml);
        //print_r($bookinfo->result);
        return  $bookinfo->result;
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
            }
            // else{
            //   echo  "创建cp书籍出错";
            // }
      		
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

      private  function  getchapternums($bookid){
        return  	$this->book_content->where(array('fu_book'=>$bookid))->count();
      }

   //   private  function  update

      public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();
            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
            $num = count($apicount);
            // echo "书本id:".$cpbook[fubook_id];
            // echo "<br>";
            // echo "数据库已有章节:".$havecount;
            // echo "<br>";
            // echo "接口章节:".$num;
            // echo "<br>";
            // echo "<hr>";exit;
          //  print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  "$cpbook[fubook_name] 没有需要更新的内容</br>";
              //exit;
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                 $content=$this->getcontent($cpbook['fubook_id'],$apicount[$i]->chapterId);
                 $this->insertcon($cpbook['book_id'],$apicount[$i]->chapterName,$apicount[$i]->wordNum,$this->price($apicount[$i]->wordNum),$content,$id);
               }
           
            $this->updatechapternum($id,$cpbook['book_id']);
            $this->updateapinum($id,$cpbook['fubook_id']);
            echo "$cpbook[fubook_name] 更新结束</br>";
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

      private  function  getcontent($partner_bookid,$partner_chapterid){
           $data1['api_key']=$this->apikey;
           $data1['method']='chapter';
           $data1['book_id']=$partner_bookid;
           $data1['chapter_id']=$partner_chapterid;
           $sig=$this->sign($data1);
           $content=file_get_contents("http://api.zongheng.com/commonrest?method=chapter&book_id=$partner_bookid&chapter_id=$partner_chapterid&api_key=".$this->apikey.'&sig='.$sig);
           $detail=json_decode($content);
           $detail=(string)$detail->result->content;
           return  $this->fill($detail);
      }
       
      private  function  getchapters($partnerbookid){
           $data1['api_key']=$this->apikey;
           $data1['method']='chapter.dir';
           $data1['book_id']=$partnerbookid;
           $sig=$this->sign($data1);
           $xml= file_get_contents("http://api.zongheng.com/commonrest?method=chapter.dir&book_id=$partnerbookid&api_key=".$this->apikey.'&sig='.$sig);
           $rss=json_decode($xml);
           return $rss->result;
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

    public function  price($number){
       return  floor($number/1000*6);
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


     public  function  fill($str){
        $str=stripslashes($str) ;
        $str=str_replace("</p>", "\n",$str);
        return  str_replace("<p>", "",$str);
     }



}



?>