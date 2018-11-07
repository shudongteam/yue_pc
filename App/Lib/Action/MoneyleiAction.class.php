<?php

//CP账单公共操作类
class MoneyleiAction extends Action {

    //下载账单
    public function moneyDump($where) {
        $name = M('Agent')->where(array('web_id'=>$where[web_id]))->field('pen_name')->find();
        $cpsname = isset($name[pen_name]) ? trim($name[pen_name]) : "CPS";
        // print_r($cpsname);
        // exit();
        $filename = '阅明'.$cpsname.'代理账单-'.'(' . date('Y.m.d') . ')';
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo iconv("UTF-8", "GB2312", ("代理名称" . "\t" . '所属站点' . "\t" . '类型' . "\t" . '分成比例' . "\t" . '总充值' . "\t" . '分成后' . "\t" .'时间')) . "\n";  
        $aa = D('AgentmoneyView');

        $result = $aa->where($where)->order('time desc')->select();
        //echo $aa->getLastSql();
        foreach ($result as $key => $val) {              
            //类型
            switch ($val['type']) {
                case 1: $type = '一级代理';
                    break;
                case 2: $type = '二级代理';
                    break;
                case 3: $type = '提成';
                    break;
            }
            $value = array(
                // $val['book_id'],
                iconv("UTF-8", "GBK", $val['pen_name']),
                iconv("UTF-8", "GBK", $val['web_id']),
                iconv("UTF-8", "GBK", $type),
                iconv("UTF-8", "GBK", $val['proportion']),
                iconv("UTF-8", "GBK", $val['money_total']),
                iconv("UTF-8", "GBK", $val['money']),
                iconv("UTF-8", "GBK", $val['time']),
                // 'http://www.ymzww.cn/books/' . $val['book_id'] . '.html',
            );
            echo implode("\t", $value) . "\n";
        }  
    }
}
