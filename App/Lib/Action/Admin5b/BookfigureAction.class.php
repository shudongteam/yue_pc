<?php

//作品折线图
class BookfigureAction extends GlobalAction {

    public function sales($book) {
        //查询三十天数据
        $shijian = date("Y-m-d", strtotime("-30 day"));
        $day = M('CpMoneyday');
        $map['time'] = array('egt', $shijian);
        $map['fu_book'] = $book;
        $shuju = $day->where($map)->group('web_id,time')->field('sum(consumption) as total, time,web_id')->order('time desc')->select();
        $data = array();
        foreach ($shuju as $value) {
            $data[$value['time']][$value['web_id']] = $value['total'];
        }
        $web = M('Web')->field('web_id,web_name')->select();
        $newdata = array();
        $times = array();
        foreach ($web as $value) {
            $val = array();
            $times = array();
            $webid = $value['web_id'];
            for ($i = 30; $i > 0; $i--) {
                $date = date("Y-m-d", strtotime("-$i day"));
                $times[] = date('m-d', strtotime($date));
                $val[] = (int) ($data[$date][$webid] ? $data[$date][$webid] : 0);
            }
            $newdata[] = array('web_name' => $value['web_name'], 'value' => json_encode($val));
        }

        $this->assign('web', $newdata);
        $this->assign('timess', $times);
        $books = M('Book')->where(array('book_id' => $book))->field("book_name")->find();
        $this->assign('books', $books);
        $this->display();
    }

}
