<?php

//生成日报表
class MoneydayAction extends GlobalAction {

    //日单详细表
    public function index() {
       

        //print_r($_POST);
         $where['cp_id'] = $this->to[cp_id];
         //$where[]
        if ($_REQUEST['time']) {
            $time = $_REQUEST['time'];
            $con['cp_id'] = $this->to[cp_id];
            $con['month'] = array('eq',$time);
            $res = M('CpSettlement')->where($con)->find();
            $this->assign('settle', $res);
            //print_r($res);
            // if(!$res){
            //     $this->assign('mymday', null);
            // }else{}
            $where['time'] = array('like',"%$time%");
                // $cost = $res['cost'];      
        }else{
            $time = date("Y-m",time());
            $con['cp_id'] = $this->to[cp_id];
            $con['month'] = array('eq',$time);
            $res = M('CpSettlement')->where($con)->find();
            $this->assign('settle', $res);
            $where['time'] = array('like',"%$time%");
        }
        if ($_REQUEST['keyword']) {
            $type = $_REQUEST['search_type'];
            if($type==1){
                $where['book_name'] = trim($_REQUEST['keyword']);

            }else{
                $where['author_name'] = trim($_REQUEST['keyword']);
            }

        }
        if ($_REQUEST[nums]) {
            $_GET['nums'] = $_REQUEST[nums];
        }


        import('ORG.Util.Page'); // 导入分页类 
        $mday = D('MycpmoneyView');

        //统计条数开始
        $count = count($mday->where($where)->group('book_name')->select()); // 查询满足要求的总记录数
        $nums = isset($_GET['nums']) ? $_GET['nums'] : 15;//默认显示15条记录
        $Page = new Page($count, $nums); // 实例化分页类 传入总记录数和每页显示的记录数   
        $sales = $mday->where($where)->field('SUM(CpMoneyday.consumption) as tot')->select();//获取销售总额
        //print_r($sales);
        $mymday = $mday->where($where)
        ->field('book_name,author_name,audit,time,buy_total,exceptional_total,SUM(CpMoneyday.consumption) as cs')
        ->group('book_name')
        ->order('cs desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $mymday2 = $mday->where($where)
        ->field('book_name,author_name,audit,time,buy_total,exceptional_total,SUM(CpMoneyday.consumption) as cs')
        ->group('book_name')
        ->order('cs desc')->select();
        for ($i = 0; $i < count($mymday2); $i++) {
                $tcs += $mymday2[$i][cs];
            }
        $othermoney = $res[consumption] - $tcs;
        $booknum = count($mymday2);
        $arr = array();
        foreach ($mymday as $key => $value) {
            $value[cost] = round($res[cost]*(($value[cs]*0.9/100)/$res['money']),2);
            $value[cs] = round(($value[cs]*0.9/100)+($othermoney/$booknum*0.9/100)-$value[cost],2);
            $value[five] = round($value[cs]/2,2);  
            $value[pay] = $value[five];
            $arr[] = $value;
        }
        //echo $mday->getLastSql();
        $this->assign('mymday', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $map['time'] = $_REQUEST['time'];
        // $map['end'] = $_REQUEST['end'];
        $map['nums'] = $_REQUEST['nums'];
        if($_REQUEST['keyword']){
            $map['keyword'] = $_REQUEST['keyword'];
            $map['search_type'] = $_REQUEST['search_type'];
        }
        foreach($map as $key=>$val) {   
            $Page->parameter .= "$key=".$val."&";   
        }    
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
        $this->assign('nums', $_REQUEST[nums]);
        $this->assign('search_type', $_REQUEST[search_type]);
        $this->assign('keyword', $_REQUEST[keyword]);
        // $this->assign('start', $_REQUEST['start']);
        $this->assign('month',$_REQUEST['time']);
        $this->assign('cp_id', $_COOKIE[cp_id]); 
        //$this->assign('sales', round(($res[money]-$res[cost]/2),2));
        //$this->assign('sales2', $sales[0][tot]*0.9/200);
        $this->display();
    }


    //导出所有书籍
    public function bookDump() {
        $filename = '阅明-第三方书籍(' . date('Y.m.d') . ')';
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        $month = $_REQUEST[month];
        // $month = date('Y-m',time());
        // $month = date('Y-m', strtotime($month.' +1 month'));
        $con['cp_id'] = $this->to[cp_id];
        $con['month'] = array('eq',$month);
        $res = M('CpSettlement')->where($con)->find();
        if($res){

        echo iconv("UTF-8", "GB2312", ('书名' . "\t" . '作者' . "\t" . '公司' . "\t" . '类型' . "\t" . '频道' . "\t" . '显示' . "\t" . '状态' . "\t" . '审核' . "\t" . '收费类型'. "\t"  . '销售额（元）'. "\t".'55分成后（元）'."\t".'结算金额（元）'. "\t".'时间')) . "\n";  

        // echo iconv("UTF-8", "GB2312", ("书号" . "\t" . '书名' . "\t" . '作者' . "\t" . '公司' . "\t" . '类型' . "\t" . '频道' . "\t" . '显示' . "\t" . '状态' . "\t" . '审核' . "\t" . '收费类型' . "\t" . '单本收费' . "\t" . '总字数' . "\t" . '现订阅(YMB)' . "\t" . '现打赏(YMB)' . "\t" . '总点击'. "\t" . '网址')) . "\n";  
       
        $bbb = D('MycpmoneyView');
        $where['cp_id'] = $this->to[cp_id];
        $where['time'] = array('like',"%$month%");
        $result = $bbb->where($where)
        ->field('book_name,author_name,audit,type_id,time,buy_total,exceptional_total,vip,gender,is_show,state,SUM(CpMoneyday.consumption) as cs')
        ->group('book_name')
        ->order('cs desc')->select();
        for ($i = 0; $i < count($result); $i++) {
                $tcs += $result[$i][cs];
            }
        $othermoney = $res[consumption] - $tcs;
        $booknum = count($result);
        foreach ($result as $key => $val) {
            //审核                
            switch ($val['audit']) {
                case 2:
                    $audit = '已审核';
                    break;
                case 1:
                    $audit = '未审核';
                    break;
                default:
                    $audit = '不通过';
                    break;
            }
            //收费类型
            switch ($val['vip']) {
                case 0: $vip = '按章';
                    break;
                case 1: $vip = '按本';
                    break;
                case 2: $vip = '免费';
                    break;
            }
            $gender = $val['gender'] == 1 ? '男' : '女';
            $is_show = $val['is_show'] == 1 ? '显示' : '隐藏';
            $state = $val['state'] == 1 ? '连载' : '完本';
            $type = BooktypeAction::mybooktype($val['type_id']);
            $val[cost] = round($res[cost]*(($val[cs]*0.9/100)/$res['money']),2);
            $val[cs] = round(($val[cs]*0.9/100)+($othermoney/$booknum*0.9/100)-$val[cost],2);
            $val[five] = round($val[cs]/2,2);  
            // $five = round($val['cs']*0.9/200,2);
            // $cost = round($res['cost']*($val['cs']*0.9/200)/$res['money'],2);
            //$val['cs'] = $val['cs']*0.9;
            $value = array(
                //$val['book_id'],
                iconv("UTF-8", "GBK", $val['book_name']),
                iconv("UTF-8", "GBK", $val['author_name']),
                iconv("UTF-8", "GBK", $_COOKIE['pen_name']),
                iconv("UTF-8", "GBK", $type),
                iconv("UTF-8", "GBK", $gender),
                iconv("UTF-8", "GBK", $is_show),
                iconv("UTF-8", "GBK", $state),
                iconv("UTF-8", "GBK", $audit),
                iconv("UTF-8", "GBK", $vip),
                $val[cs],
                $val[five],
                $val[five], 
                $month,
                // $val['cs']/200,
                //$val['money'],
                //$val['words'],
                // intval($val['buy_total']),
                // intval($val['exceptional_total']),
                //intval($val['click_total']),
                //'http://www.ymzww.cn/books/' . $val['book_id'] . '.html',
            );
            echo implode("\t", $value) . "\n";
        } 
        }
     }


}
