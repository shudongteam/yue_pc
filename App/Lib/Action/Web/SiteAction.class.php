<?php

//站点配置
class SiteAction extends GlobalAction {

    //站点配置
    public function index() {
        if ($this->isPost()) {
            $is = M("Web")->where(array('web_id' => $this->to['web_id']))->save($_POST);
            if ($is) {
                $this->success("修改成功!");
            } else {
                $this->error("修改失败");
            }
        } else {
            $web = M("Web")->where(array('web_id' => $this->to['web_id']))->find();
            $this->assign('web', $web);
            $this->display();
        }
    }

    //二维码
    public function code() {
        if ($this->isPost()) {
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $upfile = $_FILES["upload_img"];
                //获取数组里面的值 
                $name = $upfile["name"]; //上传文件的文件名 
                $type = $upfile["type"]; //上传文件的类型 
                $size = $upfile["size"]; //上传文件的大小 
                $tmp_name = $upfile["tmp_name"]; //上传文件的临时存放路径 
                //判断是否为图片 
                switch ($type) {
                    case 'image/pjpeg':$okType = true;
                        break;
                    case 'image/jpeg':$okType = true;
                        break;
                    case 'image/png':$okType = true;
                        break;
                }
            }
            if ($okType) {
                $error = $upfile["error"]; //上传后系统返回的值 
                //把上传的临时文件移动到up目录下面 
                move_uploaded_file($tmp_name, './Upload/code/' . $this->to[web_id] . '.jpg');
                if ($error == 0) {
                    $this->success("上传成功");
                } elseif ($error == 1) {
                    $this->error("超过了文件大小，在php.ini文件中设置");
                } elseif ($error == 2) {
                    $this->error("超过了文件的大小MAX_FILE_SIZE选项指定的值");
                } elseif ($error == 3) {
                    $this->error("文件只有部分被上传");
                } elseif ($error == 4) {
                    $this->error("没有文件被上传");
                } else {
                    $this->error("上传文件大小为0");
                }
            } else {
                $this->error("请上传jpg或png格式的图片");
            }
        } else {
            $this->display();
        }
    }
    
    public function webuploader() {
        if ($this->isPost()) {
            $fileName = $this->to[web_id];
            $msg = '';
            // echo $_FILES['upload_img']["tmp_name"][0];
            // var_dump($_FILES);exit;

            foreach ($_FILES['upload_img']['tmp_name'] as $key => $value) {
                if ($_FILES['upload_img']['size'][$key] > 1048576) {
                    $this->error("上传失败:每个图片最大为1MB");
                }
                if ($value) {
                    $virtualPath = './Upload/code/' . $fileName . '-sj' . ($key+1). '.jpg';
                    move_uploaded_file($value, $virtualPath);
                }
            }

            $this->success("上传成功");

        } else {
            $this->display();
        }
    }
}
