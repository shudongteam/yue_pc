<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |         lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------

class MyPage {
    
    // 分页栏每页显示的页数
    public $rollPage = 7;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 分页URL地址
    public $url     =   '';
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow    ;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =    array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
    // 默认分页变量名
    protected $varPage;

    /**
     * 架构函数
     * @access public
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
    public function __construct($totalRows,$listRows='',$parameter='',$url='') {
        $this->totalRows    =   $totalRows;
        $this->parameter    =   $parameter;
        $this->varPage      =   C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!empty($listRows)) {
            $this->listRows =   intval($listRows);
        }
        $this->totalPages   =   ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages    =   ceil($this->totalPages/$this->rollPage);
        $this->nowPage      =   !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if($this->nowPage<1){
            $this->nowPage  =   1;
        }elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage  =   $this->totalPages;
        }
        $this->firstRow     =   $this->listRows*($this->nowPage-1);
        if(!empty($url))    $this->url  =   $url; 

                //翻页样式
        $this->setConfig('theme', "<div class=\"pager\"><span class=\"pager-summary\">共 <em>%totalRow%</em> 条记录, 每页 <em>20</em> 条, 共 <em>%totalPage%</em> 页</span><span><input type=\"text\" class=\"page-input\" style=\"width:40px\" value=\"%nowPage%\"><button class=\"pager-jump\" type=\"button\">跳转</button></span><ul class=\"pagination\">%first%%prePage%%upPage%%linkPage%%downPage%%nextPage%%end%</ul></div>");

    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            //总记录小于每页记录数,不显示分页
            if ($this->totalRows < $this->listRows) {
                unset($this->config[$name]);
            } else {
                $this->config[$name]    =   $value;
            }
        }
    }

    /**
     * 分页显示输出
     * @access public
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p              =   $this->varPage;
        $nowCoolPage    =   ceil($this->nowPage/$this->rollPage);

        // 分析分页参数
        if($this->url){
            $depr       =   C('URL_PATHINFO_DEPR');
            $url        =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
        }else{
            if($this->parameter && is_string($this->parameter)) {
                parse_str($this->parameter,$parameter);
            }elseif(is_array($this->parameter)){
                $parameter      =   $this->parameter;
            }elseif(empty($this->parameter)){
                unset($_GET[C('VAR_URL_PARAMS')]);
                $var =  !empty($_POST)?$_POST:$_GET;
                if(empty($var)) {
                    $parameter  =   array();
                }else{
                    $parameter  =   $var;
                }
            }
            $parameter[$p]  =   '__PAGE__';
            $url            =   U('',$parameter);
        }
        //上下翻页字符串
        $upRow          =   $this->nowPage-1;
        $downRow        =   $this->nowPage+1;
        if ($upRow>0){
            $upPage     =   "<li class='prev page'><a href='".str_replace('__PAGE__',$upRow,$url)."' data-ci-pagination-page='{$upRow}' rel='prev'>‹ </a></li>";
        }else{
            $upPage     =   '';
        }

        if ($downRow <= $this->totalPages){
            $downPage   =   "<li class='next page'><a href='".str_replace('__PAGE__',$downRow,$url)."' data-ci-pagination-page='{$downRow}' rel='prev'> ›</a></li>";
        }else{
            $downPage   =   '';
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst   =   '';
            $prePage    =   '';
        }else{
            // $preRow     =   $this->nowPage-$this->rollPage;
            // $prePage    =   "<a href='".str_replace('__PAGE__',$preRow,$url)."' >上".$this->rollPage."页</a>";
            // $theFirst   =   "<a href='".str_replace('__PAGE__',1,$url)."' >".$this->config['first']."</a>";
            $theFirst   =   "<li class='prev page'><a href='".str_replace('__PAGE__',1,$url)."' data-ci-pagination-page='1' rel='start'>« 首页</a></li>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage   =   '';
            $theEnd     =   '';
        }else{
            // $nextRow    =   $this->nowPage+$this->rollPage;
            $theEndRow  =   $this->totalPages;
            // $nextPage   =   "<a href='".str_replace('__PAGE__',$nextRow,$url)."' >下".$this->rollPage."页</a>";
            // $theEnd     =   "<a href='".str_replace('__PAGE__',$theEndRow,$url)."' >".$this->config['last']."</a>";
            $theEnd     =   "<li class='next page'><a href='".str_replace('__PAGE__',$theEndRow,$url)."' data-ci-pagination-page='{$theEndRow}' >末页 »</a></li>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page       =   ($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<li class='page'><a href='".str_replace('__PAGE__',$page,$url)."' data-ci-pagination-page='{$page}'>".$page."</a></li>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<li class='active'><a>".$page."</a></li>";
                }
            }
        }
        $pageStr     =   str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }

}
