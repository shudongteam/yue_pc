<?php

//书籍公共操作类
class BookleiAction extends Action {

    //作品的添加
    public function bookadd($data) {
        $book = M('Book');
        $bang = M('BookStatistical'); //作品榜单
        //添加书籍表
        $bookid = $book->add($data);
        //添加榜单
        $bangdan = $bang->add(array('book_id' => $bookid));
        $isok = $book->where(array('book_id' => $bookid))->save(array('fu_book' => $bookid));
        if ($bookid && $bangdan && $isok) {
            return 1;
        } else {
            return 2;
        }
    }

    //下载书籍
    public function download($book) {
        //查询数据信息
        $where['book_id'] = $book;
        $Books = M('Book');
        $bookname = $Books->where($where)->field('book_name,book_brief')->find();
        //查询内容信息
        $con = M('bookContent');
        $where['fu_book'] = $book;
        $content = $con->where($where)->field('content_id,title,num')->order('num ASC')->select();
        //下载书籍生成书籍名字
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=$bookname[book_name].doc");
        echo "书名《" . html_entity_decode($bookname['book_name']) . "》\r\n\r\n";
        echo "作品简介:\r\n";
        echo html_entity_decode($bookname['book_brief']) . "\r\n\r\n\r\n";
        echo "正文\r\n\r\n\r\n";
        foreach ($content as $value) {
            echo "###" . html_entity_decode($value['title']) . "\r\n\r\n\r\n";
            $condition['content_id'] = $value['content_id'];
            $con1 = M('bookContents')->where($condition)->field('content')->find();
            $con1['content'] = html_entity_decode($con1['content']);
            echo $con1['content'] . "\r\n\r\n\r\n";
        }
    }

    //书籍删除方法
    public function shanchu($book) {
        
    }

    //下载书籍
    public function bookDump($where, $type) {
        $filename = '阅明-'.$type.'书籍(' . date('Y.m.d') . ')';
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo iconv("UTF-8", "GB2312", ("书号" . "\t" . '书名' . "\t" . '作者' . "\t" . '公司' . "\t" . '类型' . "\t" . '频道' . "\t" . '显示' . "\t" . '状态' . "\t" . '审核' . "\t" . '收费类型' . "\t" . '单本收费' . "\t" . '总字数' . "\t" . '现订阅(YMB)' . "\t" . '现打赏(YMB)' . "\t" . '总点击'. "\t". '总收藏'. "\t" . '网址')) . "\n";  
        $aa = D('BooksDumpView');

        $result = $aa->where($where)->order('time desc')->select();
        // echo $aa->getLastSql();exit;
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
            $value = array(
                $val['book_id'],
                iconv("UTF-8", "GBK", $val['book_name']),
                iconv("UTF-8", "GBK", $val['author_name']),
                iconv("UTF-8", "GBK", $val['cp_name']),
                iconv("UTF-8", "GBK", $type),
                iconv("UTF-8", "GBK", $gender),
                iconv("UTF-8", "GBK", $is_show),
                iconv("UTF-8", "GBK", $state),
                iconv("UTF-8", "GBK", $audit),
                iconv("UTF-8", "GBK", $vip),
                $val['money'],
                $val['words'],
                intval($val['buy_total']),
                intval($val['exceptional_total']),
                intval($val['click_total']),
                intval($val['collection_total']),
                'http://www.ymzww.cn/books/' . $val['book_id'] . '.html',
            );
            echo implode("\t", $value) . "\n";
        }  
    }
}
