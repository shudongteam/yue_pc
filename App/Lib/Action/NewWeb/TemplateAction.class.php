<?php

//首页
class TemplateAction extends GlobalAction {

    public function msg(){
        $model = M('NWeixinTask');
        $where['type'] = 1;
        $where['agent_id'] = session('agent_id');
        $count = $model->where($where)->count();
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data =  $model->where($where)->field()->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';
        $this->title = '模版消息';
        $this->display();
    }

   public function edit(){
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        if ($postStr) {
            $data = json_decode($postStr, true);
            // {"id":null,"name":"123123","template_id":"F0RM3ufVplboPQY9elL18_r_4DyuGO2_yYiPTSWKFyQ","url":"123123","scheduled_at":"2018-02-27 16:00","fields":"first=qweqwe&product=qweqweqw&price=e&time=qweqwe&remark=qweqwe&","fields_color":"first_color=#005656&product_color=#66FFFF&price_color=#CC0056&time_color=#66FF81&remark_color=#00AC00&"}
            /*
                {
                    "template_id": "3ZtUIQs16ePFfG96aDys_iQx7wkb0bMGEjpB9hs2OI0",
                    "url": "http://www.cfread.com/",
                    "data": {
                        "content": {
                            "value": "è®¤å¾æåå¦",
                            "color": "#000"
                        }
                    }
                }

                array(5) {
                  ["first"]=>
                  string(6) "qweqwe"
                  ["product"]=>
                  string(8) "qweqweqw"
                  ["price"]=>
                  string(1) "e"
                  ["time"]=>
                  string(6) "qweqwe"
                  ["remark"]=>
                  string(6) "qweqwe"
                }
            */

            $task_name = $data['name'];
            $timing_time = $data['scheduled_at'];
            parse_str($data['fields'], $fields_arr);
            parse_str($data['fields_color'], $fields_color_arr);
            $new_arr = array();
            foreach ($fields_arr as $key => $value) {
                $new_arr[$key]['value'] = $value;
                $new_arr[$key]['color'] = $fields_color_arr[$key.'_color'];
            }
            $content = array(
                'template_id' => $data['template_id'],
                'url'         => $data['url'],
                'data'        => $new_arr
            );
            $json_content = json_encode($content);
            if (isset($data['test_member_id'])) {
                // 测试发送模版消息
                #查询用户uid
                $openid = M('NUser')->where(array('user_id' => $data['test_member_id']))->getField('openid');
                $content['touser'] = $openid;
                //获取token
                $weixin = M('NWeixin')->where($this->where())->find();
                $appid = $weixin['app_id'];
                $secret = $weixin['app_secret'];
                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($appid) . '&secret=' . trim($secret);
                $result = $this->curlPost($url);
                if (!isset($result['access_token'])) {
                    die('获取token失败!');
                }
                $token = $result['access_token'];
                $post_data = json_encode($content);
                $fp = fsockopen('api.weixin.qq.com', 80, $error, $errstr, 5);
                $http = "POST /cgi-bin/message/template/send?access_token={$token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post_data) . "\r\nConnection:close\r\n\r\n$post_data\r\n\r\n";
                fwrite($fp, $http);
                fclose($fp);

            } else {
                $weixin_id = M('NWeixin')->where($this->where())->getField('weixin_id');
                $arr = array(
                    'agent_id'            => session('agent_id'),
                    'weixin_id'           => $weixin_id,
                    'task_name'           => $task_name,
                    'type'                => 1,
                    'state'               => 2,
                    'content'             => $json_content,
                    'timing_time'         => $timing_time ? $timing_time: date('Y-m-d H:i:s', time()),
                );
                $res = M('NWeixinTask')->add($arr);
                if (!$res) {
                    //echo M('NWeixinTask')->getLastSql();
                    $this->error("保存失败！");
                }
            }
        } else {
            $model = M('NWeixinTmplist');
            $where['agent_id'] = session('agent_id');
            $data =  $model->where($where)->order('id desc')->select();
            if (!$data) {
                $data = $this->get_tmp_list();
                // var_dump($data);
                $this->assign('msg', $data['msg']);
                $data = $data['template_list'];
            }
            $this->assign('data', $data);
            $this->assign('json_data', json_encode($data));
            $this->title = '模版消息添加';
            $this->display();   
        }

    }    

    public function kefu(){
        $model = M('NWeixinTask');
        $where['type'] = 2;
        $where['agent_id'] = session('agent_id');
        $count = $model->where($where)->count();
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data =  $model->where($where)->field()->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';
        $this->title = '客服消息';
        $this->display();
    }

    public function kefu_edit(){
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        if ($postStr) {
            $data = json_decode($postStr, true);
            $type = $data['type'];
            $task_name = $data['name'];
            $timing_time = $data['scheduled_at'];
            $state = 2;
            if ($type == 1) {
                $arr['msgtype'] = 'text';
                $arr['text']['content'] = $data['content'];
            } else {
                $arr['msgtype'] = 'news';
                $arr['news'] = $data['param'];
            }
            if (isset($data['test_member_id'])) {
                /*
                {
                    "touser":"OPENID",
                    "msgtype":"text",
                    "text":
                    {
                         "content":"Hello World"
                    }
                }

                {
                    "touser":"OPENID",
                    "msgtype":"news",
                    "news":{
                        "articles": [
                         {
                             "title":"Happy Day",
                             "description":"Is Really A Happy Day",
                             "url":"URL",
                             "picurl":"PIC_URL"
                         },
                         {
                             "title":"Happy Day",
                             "description":"Is Really A Happy Day",
                             "url":"URL",
                             "picurl":"PIC_URL"
                         }
                         ]
                    }
                }
                */
                // 测试客服消息
                #查询用户uid
                $openid = M('NUser')->where(array('user_id' => $data['test_member_id']))->getField('openid');
                $arr['touser'] = $openid;
                //获取token
                $weixin = M('NWeixin')->where($this->where())->find();
                $appid = $weixin['app_id'];
                $secret = $weixin['app_secret'];
                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($appid) . '&secret=' . trim($secret);
                $result = $this->curlPost($url);
                if (!isset($result['access_token'])) {
                    die('获取token失败!');
                }
                $token = $result['access_token'];
                // $token = '7_nEO4GKEi-ZcdkxM1wW8uUEU5yMMOoXPwlKIF1MnllxIJAISDs5-PbW3rC7T1TqBg1fiXLATsnOjWz4CDdMZ8He5gNo3cUXwKRgMpHIFRn7P7RZ3cM5L5ICPFu0rkQ2YFWAbC1fvcTrA49CFjCJWeAHAQXK';
                $post_data = json_encode($arr, JSON_UNESCAPED_UNICODE);
                
                $fp = fsockopen('api.weixin.qq.com', 80, $error, $errstr, 5);
                $http = "POST /cgi-bin/message/custom/send?access_token={$token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($post_data) . "\r\nConnection:close\r\n\r\n$post_data\r\n\r\n";
                fwrite($fp, $http);
                $ret = '';
                while (!feof($fp)){
                    $ret .= fgets($fp, 1024);
                }
                echo $ret;
                fclose($fp);
            } else {
                //将中文处理
                if ($type == 1) {
                    $match = explode("\n", $data['content']);
                    $aa = array();
                    foreach ($match as $key => $value) {
                        // echo $value;
                            $aa[] = preg_replace_callback("/([\x{4e00}-\x{9fa5}]+)/u", array($this,'toUrlencode'), $value);
                    }
                    $arr['text']['content'] = join("\n", $aa);
                } else {
                    foreach ($data['param']['articles'] as $key => $value) {
                        $data['param']['articles'][$key]['title'] = preg_replace_callback("/([\x{4e00}-\x{9fa5}]+)/u", array($this,'toUrlencode'), $value['title']);
                    }
                    $arr['news'] = $data['param'];
                }
                $content = json_encode($arr);
                $weixin_id = M('NWeixin')->where($this->where())->getField('weixin_id');
                $arr = array(
                    'agent_id'            => session('agent_id'),
                    'weixin_id'           => $weixin_id,
                    'task_name'           => $task_name,
                    'type'                => 2,
                    'state'               => $state,
                    'content'             => $content,
                    'timing_time'         => $timing_time ? $timing_time: date('Y-m-d H:i:s', time()),
                );
                $res = M('NWeixinTask')->add($arr);
                if (!$res) {
                    //echo M('NWeixinTask')->getLastSql();
                    $this->error("保存失败！");
                }
            }
        }
        $this->title = '客服消息添加';
        $this->display();
    }

    public function kefu_del(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'agent_id' => session('agent_id'));
        $res = M('NWeixinTask')->where($where)->delete();
        if ($res) {
            echo 1;
        } else {
            echo 2;
        }
    }

    public function msg_del(){
        $id = I('id', 0, 'intval');
        $where = array('id' => $id, 'agent_id' => session('agent_id'));
        $res = M('NWeixinTask')->where($where)->delete();
        if ($res) {
            echo 1;
        } else {
            echo 2;
        }
    }


    //获取模版列表
    public function get_tmp_list() {
        
        //获取token
        $weixin = M('NWeixin')->where($this->where())->find();
        // $appid = 'wxcb65ed75049d5657';
        // $secret = 'd0da16fe09d9ca55be67cda9f44a97bf';
        $appid = $weixin['app_id'];
        $secret = $weixin['app_secret'];
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($appid) . '&secret=' . trim($secret);
        $result = $this->curlPost($url);
        $msg = 0;
        if (!isset($result['access_token'])) {
            $msg = 1;
            //$this->error('获取token失败!');
        } else {
            //获取模版列表
            $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='.$result['access_token'];
            $result = $this->curlPost($url);
            // echo json_encode($result);exit;
            if (!isset($result['template_list'])) {
                $msg  = 2;
            } else {
                //存入数据库
                $model = M('NWeixinTmplist');
                $where['agent_id'] = session('agent_id');
                $model->where($where)->delete();
                foreach ($result['template_list'] as $key => $value) {
                    $data = array(
                        'agent_id'    => $where['agent_id'],
                        'title'       => $value['title'],
                        'template_id' => $value['template_id'],
                        'content'     => str_replace("\n", "\\n", $value['content']),
                    );
                    $out['template_list'][$key] = $data;
                    $model->add($data);
                }
            }
        }
        $out['msg'] = $msg;
        return  $out;
    }


    private function curlPost($url, $data = array(), $post = false) {
        $curl = curl_init();

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 600);

        $result = curl_exec($curl);

        if (curl_errno($curl))
            echo '<pre><b>错误:</b><br />' . curl_error($curl);

        curl_close($curl);

        return json_decode($result, 'array');
    }

    private  function toUrlencode($match) {
        
        return urlencode($match[0]);
    } 
}
