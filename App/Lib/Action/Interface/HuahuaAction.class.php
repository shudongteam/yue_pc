<?php
//看书网采集接口
set_time_limit(0); 
class HuahuaAction extends Action {
    private $partnernewbook=array(120549,2008294);//新书
    private $partneroldbook=array(189726,186216,27706,100382,91563,50701,90251,54185,46287,57355);//老站存在的连载老书，需要采集
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
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        // print_r($book);
        // exit();
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Kanshuwang');
    }
    public function booklist(){
        $xml = file_get_contents("http://hezuo.kanshu.cn/offer/booklist.php?cono=100330");
        // $rss = simplexml_load_string($xml);
        $xml= str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
        $xml= preg_replace("/[\s]{2,}/","",$xml);
        $rss=$this->xmltoarray($xml);
        $partner_book=M('CpBook');
        $re = array();
        $rs = $partner_book->where(array('cp_id'=>16))->field('fubook_id')->select();
        foreach ($rs as $key => $value) {
            foreach ($value as $kk => $vv) {              
                $re[]=$vv; 
            }
        }
        $rss = $rss[book];
        // echo "<pre>";
        // print_r($rss);
        // die();
        foreach ($rss as $key => $value) {
                if(in_array($value['id'],$this->partnernewbook)){
                    // echo $value['id'];
                    // die();
                    if(!in_array($value['id'],$re)){
                        $data['fubook_id'] = (int)$value['id'];
                        $data['cp_id'] = 16;
                        $data['type'] = 0;
                        $data['fubook_name'] = $value['bookName'];
                        $data['fu_num'] = 0;
                        $data['fu_time'] = date('Y-m-d H:i:s',time());
                        $data['book_id'] = 0;
                        $data['num'] = 0;
                        $data['book_time'] = date('Y-m-d H:i:s',time());
                        $data['time'] = date('Y-m-d H:i:s',time());
                        $rid=$partner_book->add($data);
                        if($rid>0){
                            $this->books($value['id']);
                            echo "新书".$value['bookName']."采集完";
                            echo "<br>";
                        }
                    }else{
                        $bookid = M('CpBook')->where(array('cp_id'=>16,'fubook_id'=>$value['id']))->field('book_id')->find();
                        $this->chapterlist($value['id'],$bookid['book_id']);
                        echo $value['bookName']."已更新至最新章节";
                        echo "<br>";
                    }
                }
        }
    }

     public   function  up(){
          $mycpbook = $this->cpbook->where(array('cp_id' => $this->cp_id))->order('id desc')->select();
        //  echo  $this->cpbook->getLastSql();
          $this->assign('mycpbook', $mycpbook);
          $this->display();
      }
      
    public function books($partnerbookid){
            $xml = file_get_contents("http://hezuo.kanshu.cn/offer/bookinfo.php?cono=100330&bookid=$partnerbookid");
            // $xml = file_get_contents("http://hezuo.kanshu.cn/offer/bookinfo.php?cono=100330&bookid=120549");
            $xml= str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $xml= preg_replace("/[\s]{2,}/","",$xml);
            $rss=$this->xmltoarray($xml);
            // echo "<pre>";
            // print_r($rss[bookinfo]);
            // die();
            $bookinfo = $rss[bookinfo];
            $time=time();
            $r=$this->getImage($bookinfo['imagePath'],'./Upload/Book/da',$time.'.jpg',$type=0);//封面
            $r=$this->getImage($bookinfo['imageMidPath'],'./Upload/Book/zhong',$time.'.jpg',$type=0);
            $r=$this->getImage($bookinfo['imageMinPath'],'./Upload/Book/xiao',$time.'.jpg',$type=0);
            $datab[web_id] = 4;
            $datab[fu_web] = 0;
            $datab[cp_id] = 16;
            $datab[cp_name] = "看书";
            $datab[author_id] = 0;
            $datab[edit_id] = 0;
            $datab[book_name] = $bookinfo['bookName'];
            $datab[author_name] = $bookinfo['author'];
            $datab[type_id] = 1;//分类 默认1
            $datab[gender] = 1;//男女频
            $datab[upload_img] = $r['file_name'];//封面 
            $datab[signing] = "C";
            $datab[state] = $bookinfo['bookStatus']==0?1:2;
            $datab[vip] = 0;
            $datab[is_show] = 1;
            $datab[audit] = 2;
            $datab[chapter] = 0;
            $qian =  array("，", "；", "、", "，", "，"," ","　","："," ",";"," ","（","）",",");
            $hou  =  array("|" , "|" , "|" , "|" , "|" , "|" , "|" , "|" , "|" ,"|","|","|","|","|");
            $keywords = str_replace($qian, $hou,$bookinfo['keyWord']);
            $datab[keywords] = $keywords;
            $datab[book_brief] = $bookinfo['detail'];
            $datab[words] = $bookinfo['size'];
            $datab[time] = date('Y-m-d H:i:s',time());
            $datab[new_time] = date('Y-m-d H:i:s',time());
            // echo "<pre>";
            // print_r($datab);
            // exit();
            $book=M('Book');
            $rbookid = $book->add($datab);
            $partner_book=M('CpBook');
            if($rbookid>0){
                $partner_book->where(array('fubook_id'=>$partnerbookid,'cp_id'=>16))->save(array('book_id'=>$rbookid));
                $book->where(array('book_id'=>$rbookid))->save(array('fu_book'=>$rbookid));
                $bang = M('BookStatistical'); //作品榜单
                //添加榜单
                $bang->add(array('book_id' => $rbookid));
                $this->chapterlist($partnerbookid,$rbookid); 
            }
        }
     /*
        *章节列表
        */
      public function  chapterlist($partnerbookid,$bookid){
        // echo $partnerbookid;
        // die();
        $xml = file_get_contents("http://hezuo.kanshu.cn/offer/getchapterlist.php?cono=100330&bookid=".$partnerbookid);
        //$xml = file_get_contents("http://hezuo.kanshu.cn/offer/getchapterlist.php?cono=100330&bookid=120549");
        $xml = preg_replace("/[\s]{2,}/","",$xml);
        $chapterlist=$this->xmltoarray($xml);
        // echo "<pre>";
        // print_r($chapterlist[chapter]);
        // die();
        $cpcontent = M('CpContent');
        $bookcontent = M('BookContent');
        $contents = array();
        $contents=$cpcontent->where(array('cp_id'=>16,'partner_bookid'=>$partnerbookid))->field('partner_contentid')->select();
        // $rs = $partner_book->field('fubook_id')->select();
        foreach ($contents as $key => $value) {
            foreach ($value as $kk => $vv) {              
                $re[]=$vv; 
            }
        }
        // echo "<pre>";
        // print_r($contents);
        // echo "<hr>";
        // die();
        foreach ($chapterlist[chapter] as $key => $value) {
            if(!in_array($value[chapterId],$re)){
                    // echo "<br>";
                    // echo $value[chapterId];
                   $data['cp_id']=16;
                   $data['partner_bookid']=(int)$partnerbookid;
                   $data['fubook_id']=(int)$bookid;
                   $data['num']=$key+1;
                   $data['partner_contentid']=(int)$value['chapterId'];
                   $data['content_id']=0;
                   $data['book_time']=date('Y-m-d H:i:s',time());
                   $data['time']=date('Y-m-d H:i:s',time());
                   // echo "<hr>";
                   // print_r($data);
                   $rr = $cpcontent->add($data);
                   if($rr>0){
                       $dataz['fu_book']=(int)$bookid;
                       $dataz['num']=$key+1;
                       $dataz['title']=$value[chapterName];
                       $dataz['number']=(int)$value['chapterSize'];
                       $dataz['the_price']=$key+1>20?$this->price($value['chapterSize']):0;
                       $dataz['attribute']=date('Y-m-d H:i:s',time());
                       $dataz['time']=date('Y-m-d H:i:s',time());
                       // echo "<hr>";
                       // print_r($data);
                       $rrr = $bookcontent->add($dataz);
                       if($rrr>0){
                            M('CpContent')->where(array('partner_contentid' => $value['chapterId']))->save(array('content_id'=>$rrr));
                            $this->chapter($partnerbookid,$value['chapterId'],$rrr,$bookid,$value['chapterSize']); 
                       }
                   }
               }
        }
    }
    //内容
    public function chapter($partnerbookid,$chapterid,$contentid,$bookid,$chaptersize){
        // echo "http://hezuo.kanshu.cn/offer/getcontent.php?cono=100330$bookid=120549&chapterid=27898012";
        // die();
        $xml = file_get_contents("http://hezuo.kanshu.cn/offer/getcontent.php?cono=100330&bookid=$partnerbookid&chapterid=$chapterid");
        //$xml = file_get_contents("http://hezuo.kanshu.cn/offer/getcontent.php?cono=100330&bookid=120549&chapterid=27898012");
        $xml = preg_replace("/[\s]{2,}/","",$xml);
        $content = $this->xmltoarray($xml);
        // print_r($content[chapter][content]);
        // die();
        $bookcontent=M('BookContents');
        $data[content_id] = $contentid;
        $data[content] = $content[chapter][content];
        $res=$bookcontent->add($data);
        if ($res>0) {
            //更新字数
            $datas['chapter'] = array('exp', "chapter+1"); //总数多少章
            $datas['words'] = array('exp', "words+$chaptersize");
            $book = M('Book');
            $book->where(array('book_id' => $bookid))->save($datas);
            $datac[fu_num] = array('exp', "fu_num+1"); //总数多少章
            $datac[num] = array('exp', "num+1"); //总数多少章
            $cpbook = M('CpBook')->where(array('book_id' => $bookid))->save($datac);
            M('CpContent')->where(array('fubook_id' => $bookid))->save(array('type'=>1));
        }
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
}
