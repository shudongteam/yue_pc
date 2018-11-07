<?php
//看书网老书采集接口
set_time_limit(0); 
class Kanshuwang1Action extends Action {
    private $partneroldbook=array(189726,186216,27706,100382,91563,50701,90251,54185,46287,57355);//老站存在的连载老书，需要采集
    public function booklist(){
      header("Content-type:text/html;charset=utf-8");
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
            if(in_array($value['id'],$this->partneroldbook)){
                $bookname = M('Book')->where(array('book_name'=>$value['bookName'],'web_id'=>4))->find();
                if ($bookname) {
                    $this->chapterlist($value['id'],$bookname['fu_book']);
                }
                echo $value['bookName']."  已采集至最新章节";
                echo "<br>";
                }
            }
        }
     /*
        *章节列表
        */
      public function  chapterlist($partnerbookid,$bookid){
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
                $chapter = M('Book')->field('chapter')->where(array('fu_book'=>$bookid))->find();
                if ($chapter[chapter]<=$key) {
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
            // $cpbook = M('CpBook')->where(array('book_id' => $bookid))->save($datac);
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
