<?php
class XinghuoAction extends Action {

    private $cp_id = 61;
    private $cp_name='星火网';
    private $client_id = 10300;
    private $key = '9d4938a21d923deef21aa732f70e2706';

    //这边测试需要修改
       function __construct() {
         parent::__construct();
         $this->cpbook=M("CpBook");    //需要修改
         $this->book=M('Book');        //需要修改
         $this->book_content=M('book_content');//需要修改
         $this->book_contents=M('BookContents');//需要修改
       }

    //作品查看
    public function index($cp) {
        $where['cp_id'] = $cp;
        $where['web_id'] = 4;
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time,state')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Xinghuo');
    }

    public function  test(){
      header('Content-type: text/html;charset=utf-8');
            $bookid = 25207;
          	$chapterid = 2130800;
        print_r($this->booklistapi());
        echo "<hr>";
        print_r($this->getbookinfo($bookid));
        echo "<hr>";
        print_r($this->getchapters($bookid));
        echo "<hr>";
        // print_r($this->update(3381));
        // echo "<hr>";
       print_r($this->getcontent($bookid,$chapterid));
       echo "<hr>";
        exit();
    }
        //书单
       public function  booklistapi(){
        header("Content-type: text/html; charset=utf-8");
        $sign = md5($this->client_id.$this->key);//a2f000ebb67bc0c2f25edb2ce5fc68df
        $booklists = file_get_contents("http://api.xiang5.com/out/getBookList?client_id=".$this->client_id."&sign=".$sign);
        $booklists=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $booklists);
        $booklists = $this->ext_json_decode($booklists);
        //return $booklists->data;
         return $booklists;
       }
       //处理json返回数组
       public function ext_json_decode($str, $mode=false){
       header('Content-type: text/html;charset=utf-8');  
            $str = preg_replace('/([{,])(\s*)([A-Za-z0-9_\-]+?)\s*:/','$1"$3":',$str);
            //$str = str_replace('\'','"',$str);
            $str = str_replace(" ", "", $str);
            $str = str_replace('\t', "", $str);
            $str = str_replace('\r', "", $str);
            $str = str_replace("\l", "", $str);
            // $str = preg_replace('/s+/', '',$str); 
            $str = trim($str,chr(239).chr(187).chr(191));
            
            return json_decode($str, $mode);  
        }

      //获取第三方书籍详情
      public function  getbookinfo($partnerbookid){
        header("Content-type: text/html; charset=utf-8");
        $sign = md5($this->client_id.$this->key.$partnerbookid);
        $bookinfo = file_get_contents("http://api.xiang5.com/out/getBookInfo?client_id=".$this->client_id."&sign=".$sign."&book_id=".$partnerbookid);
        $bookinfo=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $bookinfo);
        $bookinfo = $this->ext_json_decode($bookinfo);
       
        return   $bookinfo;
      }
        //获取章节列表
      private  function  getchapters($partnerbookid){
        header("Content-type: text/html; charset=utf-8");
        $sign = md5($this->client_id.$this->key.$partnerbookid);
        $chapters = file_get_contents("http://api.xiang5.com/out/getVolumeList?client_id=".$this->client_id."&sign=".$sign."&book_id=".$partnerbookid);
        $chapters=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $chapters);
        $chapters = $this->ext_json_decode($chapters);

        return   $chapters[0]->chapterlist;
      }
        //获取章节内容
       private  function  getcontent($partnerbookid,$partnerchapterid){
            header("Content-type: text/html; charset=utf-8");
            $sign = md5($this->client_id.$this->key.$partnerbookid.$partnerchapterid);
            $content =file_get_contents("http://api.xiang5.com/out/getChapterInfo?client_id=".$this->client_id."&sign=".$sign."&book_id=".$partnerbookid."&chapter_id=".$partnerchapterid);
            $content=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $content);
            //$content = preg_replace('/s+/', '',$content); 
            $content = $this->ext_json_decode($content);
            return $content->content;
            //return $content->data->chaptercontent;
      }

     //huo书籍列表
      public function booklist(){
          $booklist=$this->booklistapi();
          //print_r($booklist);die();
          foreach ($booklist as $key => $value) {
            if($key<100){
              $cpbookid=$value->id;   //需要修改
              // echo $cpbookid;//exit;
              // echo "<hr>";
              $bookinfo = $this->getbookinfo($cpbookid);
              $bookname=(string)$bookinfo->name;   //需要修改
              $authorname=(string)$bookinfo->author;  //需要修改
              $bookpic=(string)$bookinfo->cover;//需要修改
              $gender=2;         //1男2女                  //需要修改
              $state=(int)$bookinfo->complete_status=0?1:2;   //1 连载 2完本
              $keywords="";    //需要修改
              $description=(string)$bookinfo->brief;//需要修改
              $gt = (int)$bookinfo->category;
              $type = $this->gettype($gt);
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
              // echo $description;
              // echo "<hr>";
              // echo $type;
              // echo "<hr>";
              // echo $gt;
              // die();
             if(!$this->is_cpcreaete($cpbookid)) {
                   $rid=$this->createcpbook($cpbookid,$bookname,0);
                   if($rid>0&&!$this->is_create($bookname)){
                        //创建主库书籍
                        $rbookid=$this->createbook($bookname,$authorname,$bookpic,$gender,$state,$keywords,$description,$type);
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

        private function createbook($bookname,$author_name,$coverUrl,$gender,$state,$keywords,$book_brief,$type)
       {
            $time=date('Y-m-d H:i:s',time());
            $data['web_id']=4;
            $data['cp_id']=$this->cp_id;
            $data['cp_name']=$this->cp_name;
            $data['book_name']=$bookname;
            $data['author_name']=$author_name;
            $data['type_id']=$type;//这边需要做映射
            $data['gender']=$gender;
            $imgname=time();
            $r=$this->getImage($coverUrl,'./Upload/Book/da',$imgname.'.jpg',$type=0);
            $r=$this->getImage($coverUrl,'./Upload/Book/zhong',$imgname.'.jpg',$type=0);
            $r=$this->getImage($coverUrl,'./Upload/Book/xiao',$imgname.'.jpg',$type=0);
            $data['upload_img']=$r['file_name'];
            $data['state']=$state;
            $data['vip']=0;
            $data['keywords']=$keywords;
            $data['book_brief']=$book_brief;
            $data['audit']=2;
            $data['signing']="SS";
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

      //跟新
       public  function  update($id){
            $cpbook= $this->cpbook->where(array('id' => $id,'cp_id'=>$this->cp_id))->find();
            //先检测本书有没有章节
            $havecount=$this->getchapternums($cpbook['book_id']);
            $apicount=$this->getchapters($cpbook['fubook_id']); 
            //print_r($apicount);die;
            if($havecount==count($apicount)){
              echo  $cpbook[fubook_name]."&nbsp;&nbsp;没有需要更新的内容</br>";
              //exit;
            }elseif($havecount<count($apicount)){
               for($i=$havecount;$i<count($apicount);$i++){
                    $chapterid=$apicount[$i]->id;          //需要修改
                    $cpbookid=$cpbook['book_id'];            //需要修改
                    $title=$apicount[$i]->name;//需要修改
                    // $no=$apicount[$i]->sortNo;//需要修改
                    //$title = "第".$no."章&nbsp;".$title;
                    $content=$this->getcontent($cpbook['fubook_id'],$chapterid);
                    // $content = str_replace(array("<p>","","</p>"),"",$content);
                    // $content = str_replace(array("<br>","<br/>"),"\\r\\n",$content);
                    //$content = strip_tags($content);
                    $wordnum=$this->trimall($content);
                    $price=$this->price($wordnum);
                    // echo "章节id:".$chapterid;
                    // echo "<br>";
                    // echo "书本id:".$cpbookid;
                    // echo "<br>";
                    // echo "章节名:".$title;
                    // echo "<br>";
                    // echo "章节字数:".$wordnum;
                    // echo "<br>";
                    // echo "章节钱数:".$price;
                    // echo "<br>";
                    // echo "章节内容:".$content;
                    // echo "<hr>";
                    // exit;
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

      //分类
    public function gettype($booktype){
           $tytes = array(
              '243' => 1,'100057' => 1,'27' => 1,'573' => 1,

              '52' => 2,'331' => 2,'521' => 2,'522' => 2,'523' => 2,'10007' => 2,
              '100032' => 2,'100033' => 2,'100034' => 2,'100075' => 2,'100094' => 2,'33' => 2,

              '534' => 3,'10008' => 3,'100043' => 3,'100078' => 3,'100097' => 3,'36' => 3,'53' => 3,'361' => 3,

              '10002' => 4,'100014' => 4,'24' => 4,'47' => 4,'245' => 4,'471' => 4,

              '48' => 5,'241' => 5,'481' => 5,'502' => 5,'10003' => 5,'100017' => 5,'100024' => 5,

              '50' => 6,'242' => 6,'501' => 6,'10005' => 6,'100023' => 6,

              '49' => 7,'491' => 7,'493' => 7,'581' => 7,'10004' => 7,'100020' => 7,
              '100022' => 7,'100059' => 7,

              '56' => 8,'100011' => 8,

              '54' => 9,'542' => 9,'584' => 9,'10009' => 9,'100045' => 9,'100062' => 9,

              '58' => 10,'251' => 10,'252' => 10,'253' => 10,'254' => 10,'582' => 10,
              '583' => 10,'585' => 10,'100013' => 10,'100060' => 10,'100061' => 10,'100063' => 10,

              '22' => 11,'51' => 11,'503' => 11,'510' => 11,'511' => 11,'515' => 11,'101'=>11,
              '518' => 11,'10006' => 11,'100025' => 11,'100027' => 11,'100031' => 11,'100066' => 11,'100068' => 11,

              '232' => 12,'482' => 12,'513' => 12,'100018' => 12,'100029' => 12,'23' => 12,

              '21' => 13,

              '201' => 14,'20' => 14,

              '222' => 16,'221' => 16,

          );
          return isset($booktype) ? $tytes[$booktype] : 11;
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


}
