<?php
class YueluAction extends Action {
    private $cp_id = 33;     //需要修改
    private $cp_name='岳麓';   //需要修改

     //可能需要修改
    public function sign(){
      return $str=md5('pid='.$this->pid.'&'.'key='.$this->key);
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
    


       //这边测试需要修改
       function __construct() {
         parent::__construct();
         $this->cpbook=M("CpBook");    //需要修改
         $this->book=M('Book');        //需要修改
         $this->book_content=M('book_content');//需要修改
         $this->book_contents=M('BookContents');//需要修改
       }

      private  function  getdata($value){
              $value=str_replace("<![CDATA[","",$value);
              $value=str_replace("]]>","",$value); 
              return  $value;
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
        $book = $bbb->where($where)->field('fu_book,book_name,author_name,cp_name,words,new_time,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        // print_r($book);
        // exit();
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display('Interface@Yuelu');
    }

    //测试
    public function  test(){
         $pid=$this->pid;
         $sn =$this->sign();
            //echo "http://inf.9kus.com/0/bookList/pid/".$pid."/sn/".$sn;
         $xml= file_get_contents("http://inf.9kus.com/O/bookList/pid/".$pid."/sn/".$sn);
            // print_r($xml);
            //标记一个中括号表达式的开始。要匹配 [，请使用 \[。
         $xml= str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
         $xml= preg_replace("/[\s]{2,}/","",$xml);
         $rss=$this->xmltoarray($xml);
         print_r($rss['book']);

         //书籍信息
        $pid =$this->pid;
        $sn  =$this->sign();
        $partnerbookid=18193;
        //echo  "http://inf.9kus.com/O/bookInfo/pid/".$pid."/sn/".$sn."/bookId/".$partnerbookid;
        $xml = file_get_contents("http://inf.9kus.com/O/bookInfo/pid/".$pid."/sn/".$sn."/bookId/".$partnerbookid);
        //$xml = preg_replace("/[\s]{2,}/","",$xml);
        $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
        $bookinfo=$this->xmltoarray($xml);

        //print_r($bookinfo);

         $pid =$this->pid;
        $sn  =$this->sign();
        //echo "http://inf.9kus.com/O/chapters/pid/".$pid."/sn/".$sn."/bookId/".$partnerbookid;
        $xml = file_get_contents("http://inf.9kus.com/O/chapters/pid/".$pid."/sn/".$sn."/bookId/".$partnerbookid);
        //$xml = preg_replace("/[\s]{2,}/","",$xml);
        $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
        $chapterlist=$this->xmltoarray($xml);
        print_r( $chapterlist['chapter']);


           $pid =$this->pid;
            $sn  =$this->sign();
            $partner_chapterid=61274;
            //echo "http://inf.9kus.com/O/content/pid/".$pid."/sn/".$sn."/bookId/".$partner_bookid."/id/".$partner_chapterid;
            $xml = file_get_contents("http://inf.9kus.com/O/content/pid/".$pid."/sn/".$sn."/bookId/".$partnerbookid."/id/".$partner_chapterid);
            // $xml = preg_replace("/[\s]{2,}/","",$xml);
            $xml = str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$xml);
            $chapterinfo=$this->xmltoarray($xml);
            //print_r($this->getdata($chapterinfo));
    }
}
