<?php

//各种常用查询
class GongjuAction extends Action {

    //获取一级代理（站长）    
    public function getweb() {
        $web = M('Web')->field('web_id,web_name')->select();
        $this->assign("webs", $web);
    }

    //获取授权站
    public function getauthorizationweb($web_id) {
        $map['web_id'] = array('neq', $web_id);
        $result = M("Web")->where($map)->field('web_id,web_name')->select();
        $this->assign('topweb', $result);
    }

    //每个站的cp是多少
    public function cps($web_id) {
        $where['web_id'] = $web_id;
        $cp = M('Cp')->where($where)->field('cp_id,pen_name')->select();
        $this->assign('cps', $cp);
    }

//查询是类型
    public function gettype($type) {
        $types = BooktypeAction::booktype();
        foreach ($types as $key => $value) {
            if ($value == $type) {
                return $key;
            }
        }
        return 11; //完全没有找到
    }

//查询频道
    public function getpingdao($gender) {
        if ($gender == "男频") {
            return 1;
        } else {
            return 2;
        }
    }

//查询状态
    public function getstate($state) {
        if ($state == "连载") {
            return 1;
        } else {
            return 2;
        }
    }

    //显示所有章节
    public function zhangjie($book) {
        $where['fu_book'] = $book;
        $con = M('BookContent')->where($where)->field('content_id,title')->order('num ASC')->select();
        $this->assign('con', $con);
    }

    //上传封面
    public function uploaddeal($urls) {
        import('ORG.Net.UploadFile');
        $upload = new UploadFile(); // 实例化上传类
        $upload->maxSize = 1048576; // 设置附件上传大小 1MB
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        $upload->savePath = $urls; // 设置附件上传目录
        if (!$upload->upload()) {// 上传错误提示错误信息
            $this->error($upload->getErrorMsg());
        } else {// 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            $url = "Upload/Book/da/" . $info[0][savename];
            $this->resizeToFile2($url, 200, 280, "Upload/Book/zhong/" . $info[0][savename], 100);
            $this->resizeToFile2($url, 75, 100, "Upload/Book/xiao/" . $info[0][savename], 100);
            return $info[0]['savename'];
        }
    }

//图片缩小后保存
    public function resizeToFile2($sourcefile, $dest_x, $dest_y, $targetfile, $jpegqual) {

        $picsize = getimagesize("$sourcefile");
        $source_x = $picsize[0];
        $source_y = $picsize[1];
        $arr = explode(".", $sourcefile);
        $ext = "";
        if (isset($arr[count($arr) - 1])) {
            $ext = $arr[count($arr) - 1];
            $ext = strtolower($ext);
        }
        if ($ext == "jpg" or $ext == "jpeg") {
            $source_id = imageCreateFromJPEG("$sourcefile");
        } elseif ($ext == "gif") {
            $source_id = imagecreatefromgif("$sourcefile");
        }
        $target_id = imagecreatetruecolor($dest_x, $dest_y);
        $target_pic = imagecopyresampled($target_id, $source_id, 0, 0, 0, 0, $dest_x, $dest_y, $source_x, $source_y);
        imagejpeg($target_id, "$targetfile", $jpegqual);
        return true;
    }

    //批量上书
    public function excels($name) {
        Vendor('Exc.oleread'); //加载类
        Vendor('Exc.reader'); //加载类
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP936');
        $data->read("Upload/xls/$name");
        error_reporting(E_ALL ^ E_NOTICE);
        echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {

            $xuhao = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][1], 'UTF-8', 'GBK')); //序号
//如果#号就结束该书
            if ($xuhao == "#") {
                echo "<a href=\"" . U('Yunbook/index') . "\">返回</a>";
                exit();
            }
            $arr = array();
            $arr['web_id'] = $this->to['web_id'];
            $arr['cp_id'] = $_POST[cp_id];
            $arr['edit_id'] = $this->to[id];
            $arr['book_name'] = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][2], 'UTF-8', 'GBK')); //书名
            $arr['author_name'] = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][3], 'UTF-8', 'GBK')); //笔名
            $arr['type_id'] = $this->gettype(trim(mb_convert_encoding($data->sheets[0]['cells'][$i][4], 'UTF-8', 'GBK'))); //类型
            $arr['gender'] = $this->getpingdao(trim(mb_convert_encoding($data->sheets[0]['cells'][$i][5], 'UTF-8', 'GBK'))); //频道
            $arr['signing'] = $_POST[signing];
            $arr['state'] = $this->getstate(trim(mb_convert_encoding($data->sheets[0]['cells'][$i][6], 'UTF-8', 'GBK'))); //状态
            $arr['vip'] = $_POST[vip];
            $arr['money'] = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][7], 'UTF-8', 'GBK')); //价格
            $arr['is_show'] = $_POST[is_show];
            $arr['audit'] = 2; //已审核    
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $arr['keywords'] = str_replace($qian, $hou, trim(mb_convert_encoding($data->sheets[0]['cells'][$i][8], 'UTF-8', 'GBK')));
            $arr['book_brief'] = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][9], 'UTF-8', 'GBK')); //简介
            $arr['time'] = date('Y-m-d H:i:s', time());
            $arr['new_time'] = $arr['time'];
            $is = A('Booklei')->bookadd($arr);
            if ($is == 1) {
                echo $xuhao . "." . $arr['book_name'] . "成功<br />";
            } else {
                echo $xuhao . "." . $arr['book_name'] . "失败<br />";
                exit();
            }
        }
    }

    //生成短URL
    public function get_short_url($url){
        $api = 'http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long='.urlencode($url);
        $data = file_get_contents($api);
        $new_url = '';
        if ($data) {
            $arr = json_decode($data, true);
            return isset($arr[0]['url_short']) ? $arr[0]['url_short'] : '';
        }
    }

    //输出JSON
    public function echo_json($data){
        header('Content-Type:appliction/json');
        echo json_encode($data);
    }    
}
