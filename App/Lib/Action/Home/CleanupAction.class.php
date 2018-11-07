<?php

//自动数据清理
class CleanupAction extends Action {

    //清除日数据
    public function daydel() {
        //统计cp销售
        $this->cpmoney();
        echo "日执行：" . date('Y-m-d H:i:s', time()) . "\r\n";
    }

    //清除周数据
    public function weeksdel() {
        //清理书单数据
        M('BookStatistical')->query("UPDATE __TABLE__ SET `click_weeks` =  '0',`collection_weeks`='0',`buy_weeks` =  '0',`exceptional_weeks`='0',`vote_weeks` =  '0',`vipvote_weeks` =  '0'");
        echo "周执行：" . date('Y-m-d H:i:s', time()) . "\r\n";
    }

    //清除月数据
    public function monthdel() {
        //清理书单数据
        M('BookStatistical')->query("UPDATE __TABLE__ SET `click_month` =  '0',`collection_month`='0',`buy_month` =  '0',`exceptional_month`='0',`vote_month` =  '0',`vipvote_month` =  '0'");
        echo "月执行：" . date('Y-m-d H:i:s', time()) . "\r\n";
    }

    //统计cp钱
    private function cpmoney() {
        $tongji = D('TongjiView');
        $cpday = M('CpMoneyday');
        $where['buy_day'] = array('gt', 0);
        $mytongji = $tongji->where($where)->field('web_id,book_id,fu_book,cp_id,author_id,buy_day,exceptional_day')->select();
        for ($i = 0; $i < count($mytongji); $i++) {
            $data = array();
            $data['web_id'] = $mytongji[$i][web_id];
            $data['fu_book'] = $mytongji[$i][fu_book];
            $data['book_id'] = $mytongji[$i][book_id];
            $data['cp_id'] = $mytongji[$i][cp_id];
            $data['author_id'] = $mytongji[$i][author_id];
            $data['consumption'] = $mytongji[$i][buy_day] + $mytongji[$i][exceptional_day];
            $data['time'] = date('Y-m-d', strtotime('-1 day'));
            $cpday->add($data);
        }
        //清理书单数据
        M('BookStatistical')->query("UPDATE __TABLE__ SET `click_day` =  '0',`collection_day`='0',`buy_day` =  '0',`exceptional_day`='0',`vote_day` =  '0',`vipvote_day` =  '0'");
    }

}
