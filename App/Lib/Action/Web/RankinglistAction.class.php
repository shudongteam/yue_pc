<?php

    //榜单结算
    class RankinglistAction extends GlobalAction {

        public function index() {
            import('ORG.Util.Page');
            $stati = D('Statisticalview');
            if(!empty($_POST[book_name])){
                $where[book_name]=$_POST[book_name];
            }
           if(!empty($_GET[type])){
               $paixu="$_GET[type] desc";
               $map['type']=urldecode("$_GET[type]");
            }
            if(!empty($_POST[type])){
                $paixu="$_POST[type] desc";
                $map['type']=urldecode("$_POST[type]");
            }
            if($_POST[type]=='all'||$_GET['type']=='all'){
                 $map=array();
                 $paixu='';
            }
            foreach($map as $key=>$val) {
                $Page->parameter   .=   "$key=".urlencode($val).'&';
            }
            $count = $stati->where($where)->field('book_id')->count(); // 查询满足要求的总记录数
            $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数            
            $isstati = $stati->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order($paixu)->select();
            //翻页样式
            $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
            $show = $Page->show(); // 分页显示输出
            $this->assign('page', $show); // 赋值分页输出     
            $this->assign('isstati',$isstati);
            $this->display();
        }
    }