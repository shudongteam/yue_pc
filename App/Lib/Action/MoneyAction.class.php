<?php

//用户的全勤稿费
class MoneyAction extends Action {

    //生成
    public function add($book,$books) {
        $yue = $_POST[yue];
        $this->assign('dangqin', $yue); //显示标签时间
        $shijan = $this->shijan($yue); //查询这个月多少天
        $zhangjie = $this->zhangjie($book, $yue); //查询这个月章节总数
        if (is_array($zhangjie)) {//判断是否有章节
            $riqi = $yue . "-01"; //初始化时间
            $shuzu = array(); //创建大数组
            //循环日期查询
            for ($i = 1; $i <= $shijan; $i++) {
                $shuzu[$i] = $this->chaxun($zhangjie, $riqi);
                $riqi = date('Y-m-d', strtotime($riqi . "+1 day"));
            }
            //获取稿费公式
            $gongshi = $this->gaofei($books);
            //显示统计格式
            $this->tongji($gongshi, $shuzu);
        } else {
            $this->success("本月没有章节更新！");
            exit();
        }
        $this->assign('books', $books);
    }

    //获取月份有多少天
    private function shijan($yue) {
        //统计这个月多少天
        $d = strtotime("$yue");
        $tianshu = date('t', $d);
        return $tianshu;
    }

    //查询月份数据返回数组
    private function zhangjie($book, $yue) {
        $where['fu_book'] = $book;
        $where['time'] = array(array('gt', "$yue-01 00:00:00"), array('lt', "$yue-31 23:59:59"));
        $con = M('BookContent');
        $cao = $con->where($where)->field('title,number,time')->order('time asc')->select();
        return $cao;
    }

    //章节日期
    private function chaxun($zhangjie, $riqi) {
        $fankui = array();
        $fankui['riqi'] = $riqi;
        for ($i = 0; $i < count($zhangjie); $i++) {
            if (strstr($zhangjie[$i][time], $riqi)) {
                $fankui['zongzishu'] = $fankui['zongzishu'] + $zhangjie[$i][number];
            }
        }
        $fankui['zhangjie'] = "无";
        return $fankui;
    }

    //统计有多少钱
    private function tongji($gongshi, $shuzu) {
        $zongjieyu = 0;
        for ($i = 1; $i <= count($shuzu); $i++) {
            if ($shuzu[$i][zongzishu] >= $gongshi[zishu]) {
                $shuzu[$i][queshi] = 0; //字数缺失
                $shuzu[$i][dangri] = $shuzu[$i][zongzishu] * $gongshi[danjia]; //算出当日总价格
                $zongjieyu = $zongjieyu + $shuzu[$i][dangri];
                $shuzu[$i][jieyu] = $zongjieyu;
            } else {
                $shuzu[$i][queshi] = $gongshi[zishu] - $shuzu[$i][zongzishu];
                $shuzu[$i][dangri] = $gongshi[kou];
                $zongjieyu = $zongjieyu + $gongshi[kou];
                $shuzu[$i][jieyu] = $zongjieyu;
            }
        }
        $this->assign('shuzu', $shuzu);
        $tongji['zongjieyu'] = $zongjieyu;
        $this->assign('tongji', $tongji);
    }

    //稿费标准
    private function gaofei($books) {
        $gaofei['danjia'] = $books[welfare] / 1000;
        $gaofei['zishu'] = 5000;
        $gaofei['kou'] = -$books[welfare] * 3;
        return $gaofei;
    }

}
