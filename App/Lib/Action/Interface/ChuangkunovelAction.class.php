<?php
class ChuangkunovelAction extends Action {
     private $cp_id = 24;
     private $cp_name='创酷';
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
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time,state')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        // print_r($book);
        // exit();
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Chuangkunovel');
    }

    public function  test(){
         print_r($this->booklistapi());
          //书籍属性
          $partnerbookid=1149;
           //列表
          print_r($this->getbookinfo( $partnerbookid));
         
          print_r($this->getchapters( $partnerbookid));
          $partner_chapterid=107359;
           
          print_r($this->getcontent( $partnerbookid,$partner_chapterid));
    }

      function __construct() {
         parent::__construct();
         $this->cpbook=M("CpBook");
         $this->book=M('Book');
         $this->book_content=M('book_content');
         $this->book_contents=M('BookContents');
       }
   
       //huo书籍列表
      public function booklist(){
          $booklist=$this->booklistapi();
          //print_r($booklist);exit;
          foreach ($booklist as $key => $value) {
            if($key<1000){
              $cpbookid=$value['bookid'];
              $bookinfo=$this->getbookinfo($cpbookid);
              //print_r($bookinfo);exit; 
              $bookname=(string)$bookinfo['name']; 
              $authorname=(string)$bookinfo['author'];
              $bookpic=(string)$bookinfo['coverImg'];
              $gender=2;         //1男2女
              $state=(int) $bookinfo['progress']==0?1:2;;   //1 连载 2完本
              $keywords=(string)'其他';
              $description=(string)$bookinfo['description'];
              $cat=$this->gettype($bookinfo['category'])?$this->gettype($bookinfo['category']):13;
             if(!$this->is_cpcreaete($cpbookid)) {
                   $rid=$this->createcpbook($cpbookid,$bookname,0);
                   if($rid>0&&!$this->is_create($bookname)){
                        //创建主库书籍
                        $rbookid=$this->createbook($bookname,$authorname,$bookpic,$gender,$state,$keywords,$description,$cat);
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

        private function createbook($bookname,$author_name,$coverUrl,$gender,$state,$keywords,$book_brief,$cat)
       {
            $time=date('Y-m-d H:i:s',time());
            $data['web_id']=4;
            $data['cp_id']=$this->cp_id;
            $data['cp_name']=$this->cp_name;
            $data['book_name']=$bookname;
            $data['author_name']=$author_name;
            $data['type_id']=$cat;//这边需要做映射
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
            $sql=$this->book->getLastSql();
            if( !$rid){die("error:$sql");}
            $bang = M('BookStatistical'); //作品榜单
            $bangdan = $bang->add(array('book_id' => $rid));
            $isok =$this->book->where(array('book_id' => $rid))->save(array('fu_book' =>$rid));
            //api里面的章节
            if($rid){
              return  $rid;
            }
           
            die("error:$sql");
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

       //判断这本书是不是新书
      private  function is_create($bookname){
        $isbook=$this->book->where(array('fu_web'=>0,'cp_id'=>$this->cp_id,'book_name'=>$bookname))->find();
        if(is_array($isbook)){
         return    $isbook['book_id'];
        }else{
          return   0;
        }
      }

      private  function  updatecpbook($partnerbookid,$rbookid){ 
           $this->cpbook->where(array('cp_id'=>$this->cp_id,'fubook_id'=>$partnerbookid))->save(array('book_id'=>$rbookid));
      }

      public  function  updatechapternum($id,$bookid){
            $num=$this->getchapternums($bookid);
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('num'=>$num));  
      }

      public  function  updateapinum($id,$partner_bookid){
            $num=count($this->getchapters($partner_bookid));
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('fu_num'=>$num));  
      }

      public   function  up(){
          $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id))->order('id desc')->select();
        //  echo  $this->cpbook->getLastSql();
          $this->assign('mycpbook', $mycpbook);
          $this->display();
      }

      private  function  getchapternums($bookid){
           return    $this->book_content->where(array('fu_book'=>$bookid))->count();
      }

        public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();
            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
          //  print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  $cpbook[fubook_name]."&nbsp;&nbsp;没有需要更新的内容</br>";
              //exit;
              //count($apicount)
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                    $chapterid=$apicount[$i]['chapterId'];
                    $cpbookid=$cpbook['book_id'];
                    $title=$apicount[$i]['title'];
                    $content=$this->getcontent($cpbook['fubook_id'],$chapterid)?$this->getcontent($cpbook['fubook_id'],$chapterid):'内容有误';
                    $wordnum=$this->trimall($content);
                    $price=$this->price($wordnum);
                    $this->insertcon($cpbookid,$title,$wordnum,$price,$content,$id);
               }
                $this->updateapinum($id,$cpbook['fubook_id']);
            $this->updatechapternum($id,$cpbook['book_id']);
            echo $cpbook[fubook_name]."&nbsp;&nbsp;更新结束</br>";
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
          $sql=$this->book_content->getLastSql();
          if($iscon){
            $iscons = $this->book_contents->add(array('content_id' => $iscon, 'content' =>$content));
          }else{
            die("error:$sql");
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

       public function  booklistapi(){
          $con = file_get_contents("http://www.acoolread.com/api/yueming/list.php");
          $c = json_decode($con,true);
          //print_r($c);
           return  $c['data']['books'];
       }

      //获取第三方书籍详情
      public function  getbookinfo($partnerbookid){
          $con= file_get_contents("http://www.acoolread.com/api/yueming/info.php?bookid=$partnerbookid");
          $bookinfo = json_decode($con,true);
        return   $bookinfo['data'];
      }

      private  function  getchapters($partnerbookid){
        $arr = file_get_contents("http://www.acoolread.com/api/yueming/chapter.php?bookid=$partnerbookid");
        $arr = json_decode($arr,true);
        $chapterlist = $arr[data][volumes][0];
        return $chapterlist['chapters'];
      }

       private  function  getcontent($partner_bookid,$partner_chapterid){
      //  echo  "http://www.acoolread.com/api/yueming/contentinfo.php?bookid=$partner_bookid&chapid=$partner_chapterid";
            $con= file_get_contents("http://www.acoolread.com/api/yueming/contentinfo.php?bookid=$partner_bookid&chapid=$partner_chapterid");
            $chapterinfo= json_decode($con,true);
          // print_r($chapterinfo);
            return $chapterinfo['data']['content'];
      }

       public function gettype($booktype){
           $tytes = array(
            '1' => 4,
            '2' => 4,
            '3' => 4,
            '4' => 4,
            '5' => 4,
            '6' => 4,
            '7' => 7,
            '8' => 7,
            '9' => 7,
            '10' =>7,
            '11' => 7,
            '12' => 7,
            '13' => 11,
            '14' => 11,
            '15' => 11,
            '16' => 11,
            '17' => 11,
            '18' => 11,
            '19' => 11,
            '20' => 2,
            '21' => 2,
            '22' => 2,
            '23' => 2,
            '24' => 1,
            '25' => 1,
            '26' => 10,
            '27' => 10,
            '28' => 10,
            '29' => 13,
            '30' => 13,
            '31' => 13,
            '32' => 13,
            '33' => 13,
            '34' => 13,
            '35' => 13,
            '36' => 13,
            '37' => 13,
            '38' => 1,
            '39' => 1,
            '40' => 10,
            '41' => 10,
            '42' => 13,
            '43' => 1,
            '44' => 7,
            '45' => 13,
            '46' => 11,
        );
        return $tytes[$booktype];
    }

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
