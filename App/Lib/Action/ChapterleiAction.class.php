<?php

//章节公共操作类
class ChapterleiAction extends Action {

    //增加章节
    public function zengjia($arr,$bookid) {
        $conn = M('BookContent');
        $conns = M('BookContents');
        $book = M('Book');
        //获取该书信息
        $is = $book->where(array('book_id' => $bookid))->field('vip')->find();
        //查询最后一章
        $where['fu_book'] = $arr[fu_book];
        $zuobiao = $conn->where($where)->field('num')->order('num desc')->find();
        //获取坐标
        if (is_array($zuobiao)) {
            $arr['num'] = $zuobiao[num] + 1;
        } else {
            $arr['num'] = 1;
        }
        //章节字数
        $arr['number'] = $this->trimall($arr[content]);
        if ($is[vip] != 2) {
            $arr['the_price'] = ceil($arr[number] / 1000 * C('Prices'));
        }
        //上传章节
        $arr['time'] = date('Y-m-d H:i:s', time());
        $cid = $conn->add($arr);
        //插入内容
        $neirongs['content_id'] = $cid;
        $neirongs['content'] = $arr[content];
        $conns->add($neirongs);
        //更新作品信息
        $datas['chapter'] = $arr['num']; //总数多少章
        $datas['words'] = array('exp', "words+$arr[number]");
        $datas['new_time'] = date('Y-m-d H:i:s', time());
        $book->where($where)->save($datas);
        return 1;
    }

    //章节的删除
    public function shanchu($connkid) {
        $content = M('BookContent');
        $book = M('Book');
        //查看该章节是否存在
        $conn = $content->where(array('content_id' => $connkid))->find();
        if (!is_array($conn)) {
            $this->error("章节不存在");
            exit();
        }
        //删除章节
        $content->where(array('content_id' => $connkid))->delete();
        M('BookContents')->where(array('content_id' => $connkid))->delete();
        //更新坐标
        $map['fu_book'] = $conn[fu_book];
        $map['num'] = array('GT', $conn[num]); //查找比该坐标大的数据
        $gengxinid = $content->where($map)->field('content_id')->order('num ASC')->select();
        if (is_array($gengxinid)) {
            $xinzb = $conn[num];
            for ($i = 0; $i < count($gengxinid); $i++) {
                $content->where(array('content_id' => $gengxinid[$i][content_id]))->save(array('num' => $xinzb));
                $xinzb++;
            }
        }
        //更新作品
        $bata['words'] = array('exp', "words-$conn[number]");
        $bata['chapter'] = array('exp', "chapter-1");
        $book->where(array('fu_book' => $conn['fu_book']))->save($bata);
        return 1;
    }

    //章节的修改
    public function xiugai($arr, $connkid) {
        $content = M('BookContent');
        $book = M('Book');
        $iscon = $content->where(array('content_id' => $connkid))->find();
        if (is_array($iscon)) {
            $arr['number'] = $this->trimall($arr[content]);
            if ($iscon[the_price] != 0) {
                $arr['the_price'] = ceil($arr[number] / 1000 * C('Prices'));
            }
            $content->where(array('content_id' => $connkid))->save($arr);
            //内容修改
            M('BookContents')->where(array('content_id' => $connkid))->save(array('content' => $arr['content']));
            //更新书籍信息                          
            $batas['words'] = array('exp', "words-$iscon[number]+$arr[number]");
            $book->where(array('fu_book' => $iscon['fu_book']))->save($batas);
            return 1;
        } else {
            $this->error("系统错误！");
        }
    }

    //章节调整（需要调整的,调整到）
    public function shijan($connkid, $conid, $fu_book) {
        $content = M('BookContent');
        //需要交换的数组
        $jiaohuan = $connkid;
        //在第几章之前
        $zhiqian = $conid;
        //章节内容列表
        $arr = $content->where(array('fu_book' => $fu_book,))->field('content_id,num')->order('num ASC')->select();
        //新数组
        $quchu = array();
        //去除章节内容列表
        for ($id = 0; $id < count($arr); $id++) {
            if ($arr[$id]['content_id'] != $jiaohuan) {
                $quchu[] = $arr[$id]['content_id'];
            }
        }
        //生成新的排序数组
        $gengxin = array();
        for ($i = 0; $i < count($quchu); $i++) {
            if ($quchu[$i] == $zhiqian) {
                $gengxin[] = $jiaohuan;
                $gengxin[] = $zhiqian;
            } else {
                $gengxin[] = $quchu[$i];
            }
        }
        //生成比对数组
        $bidui = array();
        for ($bi = 0; $bi < count($gengxin); $bi++) {
            $bidui[$gengxin[$bi]] = $bi + 1;
        }
        //生成更新方法
        for ($ne = 0; $ne < count($arr); $ne++) {
            if ($arr[$ne]['num'] != $bidui[$arr[$ne]['content_id']]) {
                $content->where(array('content_id' => $arr[$ne]['content_id']))->save(array('num' => $bidui[$arr[$ne]['content_id']]));
            }
        }
        return $fu_book;
    }

    //上传方法
    public function piliang($name, $book_id) {
        //准备工作
        $book = M('Book');
        $con = M('BookContent');
        $conns = M('BookContents');
        $id = 0; //ID号
        $content = NULL; //内容
        $zongzishu = 0; //总字数
        $nums = 1; //时间
        //查询最后一章
        $where['fu_book'] = $book_id;
        $zuobiao = $con->where($where)->field('num,time')->order('num desc')->find();
        //获取坐标和时间
        if (is_array($zuobiao)) {
            $xuhao = $zuobiao[num] + 1;
            $chushitaime = $zuobiao[time];
        } else {
            $xuhao = 1;
            $chushitaime = date('Y-m-d H:i:s', time());
        }
        //开始开始批量处理
        $fp = @fopen('Upload/text/' . $name, 'r');
        if ($fp) {
            while (!feof($fp)) {
                $bruce = fgets($fp);
                $title = strstr($bruce, "###");
                if ($title) {
                    if ($id) {
                        $number = $this->trimall($content);
                        $the_price = ceil($number / 1000 * C('Prices'));
                        $con->where(array('content_id' => $id))->save(array('number' => $number, 'the_price' => $the_price));
                        //插入内容
                        $neirongs['content_id'] = $id;
                        $neirongs['content'] = trim($content);
                        $conns->add($neirongs);
                        $zongzishu = $zongzishu + $number;
                        $content = NULL;
                    }
                    $title = str_replace("###", "", trim($title)); //标题
                    //准备章节插入数据
                    $data['fu_book'] = $book_id;
                    $data['num'] = $xuhao;
                    $data['title'] = $title;
                    $data['attribute'] = date('Y-m-d H:i:s', strtotime("$chushitaime+$nums minute")); //发布时间
                    $data['time'] = $data['attribute'];
                    $id = $con->add($data); //添加作品章节内容
                    $nums = $nums + 3;
                    $xuhao++;
                } else {
                    $content = $content . $bruce;
                }
            }
            if ($id) {
                $number = $this->trimall($content);
                $the_price = ceil($number / 1000 * C('Prices'));
                $con->where(array('content_id' => $id))->save(array('number' => $number, 'the_price' => $the_price));
                //插入内容
                $neirongs['content_id'] = $id;
                $neirongs['content'] = trim($content);
                $conns->add($neirongs);
                $zongzishu = $zongzishu + $number;
                $content = NULL;
                //更新书籍表格
                $datas['chapter'] = $xuhao - 1;
                $datas['words'] = array('exp', "words+$zongzishu");
                $datas['new_time'] = date('Y-m-d H:i:s', time());
                $book->where(array('fu_book' => $book_id))->save($datas);
            }
        } else {
            echo "开打失败:Upload/text/$name";
            exit();
        }
        $this->success("上传成功", U('Chapter/index', array('book' => $book_id)));
    }

    //留言验证
    public function transgress_keyword($content) {
        //定义处理违法关键字的方法  
        $key = M('SystemKeys')->where(array('id' => 1))->find();
        $keyword = explode(",", $key[key]);
        for ($i = 0; $i < count($keyword); $i ++) {    //根据数组元素数量执行for循环  
            //应用substr_count检测文章的标题和内容中是否包含敏感词  
            if (substr_count($content, $keyword [$i]) > 0) {
                $m = $m . $keyword[$i] . ',';
            }
        }
        return $m;              //返回变量值，根据变量值判断是否存在敏感词  
    }

    //字数统计函数
    public function trimall($str) {//删除空格
        $qian = array(" ", "　", "\t", "\n", "\r");
        $hou = array("", "", "", "", "");
        $str = str_replace($qian, $hou, $str);
        $str = mb_convert_encoding($str, 'GBK', 'UTF-8');
        preg_match_all("/[" . chr(0xa1) . "-" . chr(0xff) . "]{2}/", $str, $m);
        $mu = count($m[0]);
        unset($str);
        unset($m);
        return $mu;
    }

}
