<?php
class HonghuaAction extends Action {
    private  $cp_id=23;
    private $url="https://api.xhhread.com/api";
    private $appkey="8aada6395b5c3d6d015b5c3d6d220001";
    private $cp_name='小红花';

    function __construct() {
         parent::__construct();
         $this->cpbook=M("CpBook");
         $this->book=M('Book');
         $this->book_content=M('book_content');
         $this->book_contents=M('BookContents');
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
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time,state')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        // print_r($book);
        // exit();
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Honghua');
    }

      

    public  function  test(){
        //列表
       //  $caiji=$this->url."/books?appkey=".$this->appkey;
       //  $listjson = file_get_contents($caiji);
       //  $con= (Array)json_decode($listjson);
       // print_r( $con);die;
        //书籍属性

    $partnerbookid='8aada63958aee2640158af4c0f6900f0';
        //章节列表
     /*   $partnerbookid='8aada639586b50a40158850e2a700224';
        $bookinfo=$this->getbookinfo($partnerbookid);
        print_r( $bookinfo);*/
        $arr=$this->getchapters($partnerbookid);
        print_r($arr);
        //内容
    }

     private  function  getdata($value){
        $value=str_replace("<![CDATA[","",$value);
        $value=str_replace("]]>","",$value); 
        return  $value;
      }
      //采集图书列表
        public function booklist(){
          $booklist=$this->booklistapi();
          //print_r($booklist);exit;
          foreach ($booklist as $key => $value) {
            $value=(Array)$value;
            if($key<1000){   
            $cpbookid=(string)$value['storyid'];
              $bookinfo=$this->getbookinfo($cpbookid); 
              $bookname=(string)$this->getdata($bookinfo['name']); 
              $authorname=(string)$this->getdata($bookinfo['authorname']);
              $bookpic=(string)$this->getdata($bookinfo['cover']);
              $gender=2;         //1男2女
              $state=(int) $bookinfo['isfinish']==0?1:2;;   //1 连载 2完本
              $keywords=(string)'其他';
              $description=(string)$this->getdata($bookinfo['outline']);
              $type = (string)$this->getdata($bookinfo['categoryname']);
              $word = (int)$this->getdata($bookinfo['words']);
              // $chapters = (int)$this->getdata($bookinfo['chapters']);
              // echo $cpbookid;
              // echo "<br>";
              // echo $bookname;
              // echo "<br>";
              // echo $authorname;
              // echo "<br>";
              // echo $bookpic;
              // echo "<br>";
              // echo $gender;
              // echo "<br>";
              // echo $state;
              // echo "<br>";
              // echo $keywords;
              // echo "<br>";
              // echo $type;
              // echo "<br>";
              // echo $word;
              // echo "<br>";
              // echo $description;
              // echo "<hr>";
             if(!$this->is_cpcreaete($cpbookid)) {
                   $rid=$this->createcpbook($cpbookid,$bookname,0);
                   if($rid>0&&!$this->is_create($bookname)){
                        //创建主库书籍
                        $rbookid=$this->createbook($bookname,$authorname,$bookpic,$gender,$state,$keywords,$description,$type,$word);
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

      //入库规则
      private  function createcpbook($partnerbookid,$partnerbookname,$type){
            $time=date('Y-m-d H:i:s',time());
            $data['fubook_id'] = $partnerbookid;
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
      //创建图书
        private function createbook($bookname,$author_name,$coverUrl,$gender,$state,$keywords,$book_brief,$type,$word)
       {
            $time=date('Y-m-d H:i:s',time());
            $data['web_id']=4;
            $data['cp_id']=$this->cp_id;
            $data['cp_name']=$this->cp_name;
            $data['book_name']=$bookname;
            $data['author_name']=$author_name;
            $data['type_id']=$this->gettype($type);//这边需要做映射
            // $data['type_id']=1;//这边需要做映射
            $data['gender']=$gender;
            $data['words'] = $word;
            //$data['chapter'] = $chapters;
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
       //更新cpbook表book_id
      private  function  updatecpbook($partnerbookid,$rbookid){ 
           $this->cpbook->where(array('cp_id'=>$this->cp_id,'fubook_id'=>$partnerbookid))->save(array('book_id'=>$rbookid));
      }

      //已采集章节数
      public  function  updatechapternum($id,$bookid){
            $num=$this->getchapternums($bookid);
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('num'=>$num));  
      }
      //接口章节数
      public  function  updateapinum($id,$partner_bookid){
            $num=count($this->getchapters($partner_bookid));
            $cpbook = $this->cpbook->where(array('id' => $id, 'cp_id' =>$this->cp_id))->save(array('fu_num'=>$num));  
      }
      //获取书单列表
       public function  booklistapi(){
         $caiji=$this->url."/books?appkey=".$this->appkey;
         $listjson = file_get_contents($caiji);
         return  $con= (Array)json_decode($listjson);
       }

        //获取第三方书籍详情
      public function  getbookinfo($partnerbookid){
          $caiji=$this->url."/books?appkey=".$this->appkey;
          $listjson = file_get_contents($caiji);
          $con= json_decode($listjson);
        //  print_r( $con);
          foreach ($con as $key => $value) {
          //  print_r(expression);
          // echo   $value->storyid;
             if($value->storyid==$partnerbookid){
                  return   (Array)$con[$key];
             }
          }
          die("获取书籍信息失败");
      }
      //获取章节列表信息
      private  function  getchapters($partnerbookid){
        $caiji = $this->url."/chapters/".$partnerbookid."?appkey=".$this->appkey."&page=1&returnCondition=1";
        $arr = file_get_contents($caiji);
        $arr = json_decode($arr,true);
        $amount = $arr[condition][totalPageNum];
        $chapterlist=array();
        for ($i=1; $i <= $amount; $i++) { 
            $newcaiji = $this->url."/chapters/".$partnerbookid."?appkey=".$this->appkey."&page=".$i."&returnCondition=1";
            $con = file_get_contents($newcaiji);
            $a = json_decode( $con,true);
            foreach ($a['datas'] as $key => $value) {
               $chapterlist[]=$value;
            }
        }
        return $chapterlist;
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
      //已采集图书章节更新
        public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();
            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
          //  print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  $cpbook[fubook_name]."&nbsp;&nbsp;没有需要更新的内容</br>";
              //exit;
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                    $chapterid=$apicount[$i]['chapterid'];
                    $cpbookid=$cpbook['book_id'];
                    $title=$this->getdata($apicount[$i]['name']);
                    $content=$apicount[$i]['content'];
                    $wordnum=$apicount[$i]['words'];
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
      //价格计算
     public function  price($number){
       return  floor($number/1000*6);
     }

      //获取分类
    public function gettype($booktype){
           $tytes = array(
              '科幻·奇幻' => 5,
              '玄幻·武侠' => 7,
              '古言·现言' => 13,
              '惊悚·灵异' => 1,
              '都市·异能' => 11,
              '悬疑·推理' => 1,
          );
          return $tytes[$booktype];
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
