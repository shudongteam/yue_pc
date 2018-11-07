<?php

//结算
class SettlementAction extends GlobalAction {

    //结算记录
    public function index() {
        $cpsett = M('CpSettlement');
        if (($this->isPost() && $time = $_POST['time']) || ($time = $_GET['time'])) {
            $where['month'] = $time;
        }
        $where['cp_id'] = $this->to[cp_id];
        import('ORG.Util.Page'); // 导入分页类       
        $count = $cpsett->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $cpsett->where($where)->order('month DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $arr = array();
        foreach ($content as $key => $value) {
           $value['total'] = $value['money']-$value['cost'];
           $value['money55'] = round($value['total']/2,2);
           $value['give'] = $value['money55'];
           $arr[] = $value;
        }
        $this->assign('content', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }


     /**生成月结算单
         * 导出文件
         * @return string
         */
        public function export()
        {
            //print_r($_GET);exit;
            $give = $_GET['give'];
            $month = str_replace("-", "年", $_GET['month']);
            $month = $month."月";
            $cp = M('cp');
            $info = $cp->where(array("cp_id"=>$_COOKIE[cp_id]))->find();
            if($info[name]&&$info[bank]&&$info[bank_number]&&$info[open_account]){
                $file_name   = "南京阅明文化传播有限公司付款结算单-".date("Y-m-d H:i:s",time());
                $file_suffix = "xls";
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=$file_name.$file_suffix");
                //$month = $_GET['month'];
               
                if($_GET['give']<=0){
                    $zwgive ="零";
                    $give =0;
                }else{
                    $zwgive = $this->num_to_rmb($_GET['give']);
                }          
                $this->assign("month",$month);
                $this->assign("give",$give);
                $this->assign("zwgive",$zwgive);
                $this->assign("info",$info);
            }else{
                $this->error('请完善收款信息',U('Main/information'));
            }        
            $this->display();
        }

        /***数字金额转换成中文大写金额的函数
        *String Int $num 要转换的小写数字或小写字符串
        **return 大写字母*小数位为两位**/
        public function num_to_rmb($num){    
            $c1 = "零壹贰叁肆伍陆柒捌玖";    
        $c2 = "分角元拾佰仟万拾佰仟亿";    //精确到分后面就不要了，所以只留两个小数位    
        $num = round($num, 2);     //将数字转化为整数    
        $num = $num * 100;    if (strlen($num) > 10) {        return "金额太大，请检查";    }     $i = 0;    $c = "";    while (1) {        if ($i == 0) {            //获取最后一位数字            
        $n = substr($num, strlen($num)-1, 1);        } else {            $n = $num % 10;        }        //每次将最后一位数字转化为中文        
        $p1 = substr($c1, 3 * $n, 3);        $p2 = substr($c2, 3 * $i, 3);        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {            $c = $p1 . $p2 . $c;        } else {            $c = $p1 . $c;        }        $i = $i + 1;        //去掉数字最后一位了        
        $num = $num / 10;        $num = (int)$num;        //结束循环        
        if ($num == 0) {            break;        }     }    $j = 0;    $slen = strlen($c);    while ($j < $slen) {        //utf8一个汉字相当3个字符        
        $m = substr($c, $j, 6);        //处理数字中很多0的情况,每次循环去掉一个汉字“零”        
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {            $left = substr($c, 0, $j);            $right = substr($c, $j + 3);            $c = $left . $right;            $j = $j-3;            $slen = $slen-3;        }         $j = $j + 3;    }     //这个是为了去掉类似23.0中最后一个“零”字    
        if (substr($c, strlen($c)-3, 3) == '零') {        $c = substr($c, 0, strlen($c)-3);    }    //将处理的汉字加上“整”    
        if (empty($c)) {        return "零元整";    }else{        return $c . "整";    }}



}
