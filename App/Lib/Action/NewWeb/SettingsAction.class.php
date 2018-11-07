<?php

//公众号设置
class SettingsAction extends GlobalAction {
    private $NWeixin;
    private $NWeixinMenu;
    private $weixin_data;
    private $menu_data;

    public function _initialize() {
        parent::_initialize();
        $this->NWeixin = M('NWeixin');
        $this->NWeixinMenu = M('NWeixinMenu');

        $this->weixin_data = $this->NWeixin->where($this->where())->find();
        if ($this->weixin_data) {
            $this->menu_data = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id']))->order('sort_order asc')->select();
        } else {
            $this->menu_data = false;
        }
    }

    public function mp() {
        $this->title='公众号设置';        
        $this->display();
    }

    //获取公众号信息
    public function api_get_mp() {

        A('Gongju')->echo_json($this->weixin_data);
    }

    //设置公众号
    public function api_save_mp() {
        // $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
// var_dump($postStr);exit;
        // $info = false;
        // if ($postStr) {
            // $data = json_decode($postStr, true);
            $arr = array(
                'app_id'            => $_POST['app_id'],
                'app_secret'        => $_POST['app_secret'],
                'kefu_qrcode_url'   => $_POST['kefu_qrcode_url'],
                'username'          => $_POST['username'],
                'nickname'          => $_POST['nickname'],
                'raw_id'            => $_POST['raw_id'],
                'time'              => date('Y-m-d H:i:s', time()),
                'update_time'       => date('Y-m-d H:i:s', time()),
                'web_id'            => session('web_id'),
            );
            if (isset($this->weixin_data['weixin_id'])) {
                $res = $this->NWeixin->where(array('weixin_id' => $this->weixin_data['weixin_id']))->save($arr);
            } else {
                $arr['agent_id'] = session('agent_id');
                $res = $this->NWeixin->add($arr);
            }
        // }
        if (!$res) {
            // $info = true;
            echo $this->NWeixin->getLastSql();
            $this->error("保存失败！");
        }
        //生成网站配置文件
            $agent_id = session('agent_id'); 
             $sql = "SELECT 
                    agent.agent_id master_id, 
                    agent.web_id, agent.pen_name web_name, 
                    agent.mobile webphone, 
                    agent.qq, 
                    agent.automatic,
                    weixin.kefu_qrcode_url weixin
                    FROM `hezuo_n_agent` agent LEFT JOIN `hezuo_n_weixin` weixin 
                    ON agent.agent_id = weixin.agent_id 
                    WHERE agent.agent_id = $agent_id";

            $myweb = M('')->query($sql);
            if ($myweb) {
                $web_url = 'wap'.$agent_id.'.nw.kyueyun.com';
                $website=array (
                    'web_url' => $web_url,
                    'all_ps' => 'hezuo',
                    'login_url' => 'm.kyueyun.com',
                    'preload' => 'NewWap',
                  //  'webqq' => '1437940177',
                    'beian' => '赣ICP备18001577号-2',
                   // 'qq_id' => '101200146',
                  //  'qq_secret' => 'e1fca15c14e3a0f1e7b7d5d00c2e4779',
                  //  'wb_id' => '1175818843',
                  //  'wb_secret' => 'e78ee3a6a64601ae05b942d3ca96db1f',
                    'wx_id' => 'wx458729fd34adecf1',
                    'wx_secret' => '9654e0ce321ca9bf659f28f17956784e'
              );
                // echo dirname($_SERVER['DOCUMENT_ROOT']);exit;
                //存在这个网站进行生成
                $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Website/NewWap/'.$web_url.'.php';
                $text = '<?php $website=' . var_export(array_merge($myweb[0], $website), true) . ';';
                if (false !== fopen($file, 'w+')) {
                    file_put_contents($file, $text);
                    $info = true;
                    //清除首页-排行缓存, 清除新书和限免缓存
                    $Top_index_9 = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/NewWap/'.session('web_id').'/Top_index_9.html';
                    $Top_index_10 = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/NewWap/'.session('web_id').'/Top_index_10.html';
                    $index = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/NewWap/'.session('web_id').'/index.html';
                    $ranking = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/NewWap/'.session('web_id').'/Rankinglist.html';
                    file_exists($Top_index_9) ? @unlink($Top_index_9): ''; 
                    file_exists($Top_index_10) ? @unlink($Top_index_10): ''; 
                    file_exists($Top_index_10) ? @unlink($Top_index_10): ''; 
                    file_exists($index) ? @unlink($index): ''; 
                    file_exists($ranking) ? @unlink($ranking): ''; 
                } else {
                    $info = false;
                }
            } else {
                $info = false;
            }
            if (!$info) {
                $this->error("保存失败@2！");
            } else {
                $this->success("保存成功！");
            }
    }

    //公众号接入
    public function integrate() {
        $agent_id = session('agent_id');
        $this->assign('agent_id', $agent_id);
        $this->assign('weixin_id', @$this->weixin_data['weixin_id']);
        $this->assign('web_url', "wap".session('agent_id').".nw.ymzww.cn");
        $this->title='公众号接入';    
        $this->display();
    }

    //菜单自动生成
    public function api_generate_menu() {
        $agent_id = session('agent_id');
        $weixin_id = I('get.weixin_id');

        $data = $this->weixin_data;
        if (!$data) {
            $this->error("没有绑定微信账号");
        }
        $appid = $data['app_id'];
        $secret = $data['app_secret'];
        //得到access_token
        $data = $this->curl("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret");
        if (!isset($data['access_token'])) {
            $this->error("获取微信token失败!");
        }
        $access_token = $data['access_token'];
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
        $arr = array(
            'button' => array(
                array(
                    'name' => urlencode("阅读记录") ,
                    'type' => 'view',
                    "url" => "http://wap".$agent_id.".nw.ymzww.cn/Bookcase/reading.html",
                    ),
                array(
                    'name' => urlencode("好书推荐") ,
                    'sub_button' => array(
                        array(
                            'name' => urlencode("首页搜书") ,
                            'type' => 'view',
                            "url" => "http://wap".$agent_id.".nw.ymzww.cn",
                        ) ,
                        array(
                            'name' => urlencode("小说排行") ,
                            'type' => 'view',
                            "url" => "http://wap".$agent_id.".nw.ymzww.cn/Rankinglist/click.html",
                        ) ,
                        array(
                            'name' => urlencode("精品女频") ,
                            'type' => 'click',
                            "key" => "jingpinnvpin",
                        ) ,
                        array(
                            'name' => urlencode("我的书架") ,
                            'type' => 'view',
                            "url" => "http://wap".$agent_id.".nw.ymzww.cn/Bookcase/index.html",
                        ),                        
                    )
                ),                
                array(
                    'name' => urlencode("用户中心") ,
                    'sub_button' => array(                      
                        array(
                            'name' => urlencode("个人中心") ,
                            'type' => 'view',
                            "url" => "http://wap".$agent_id.".nw.ymzww.cn/User/info.html",
                        ),                        
                        array(
                            'name' => urlencode("我要充值") ,
                            'type' => 'view',
                            "url" => "http://wap".$agent_id.".nw.ymzww.cn/Pay/index.html",
                        ),
                        array(
                            'name' => urlencode("粉丝福利") ,
                            'type' => 'click',
                            "key" => "fuli",
                        ),
                        array(
                            'name' => urlencode("联系客服") ,
                            'type' => 'click',
                            "key" => "kefus",
                        ),
                    )
                )
            )
        );
        $jsondata = urldecode(json_encode($arr));
        $redata = $this->curl($url, $jsondata, 1);
        //清空数据 插入默认菜单数据
        $menu_data = $this->menu_data;
        if ($menu_data) {
            $this->NWeixinMenu->where(array("weixin_id" => $this->weixin_data['weixin_id']))->delete();
        }

        $i = 1;
        foreach ($arr['button'] as $key => $value) {
            $data = array(
                'weixin_id'     => $this->weixin_data['weixin_id'], 
                'web_id'        => session('web_id'),
                'name'          => urldecode($value['name']),
                'parent_id'     => 0,
                'url'           => isset($value['url']) ? $value['url'] : '',
                'type'          => isset($value['type']) ? $value['type'] : '',
                'key'           => '',
                'content'       => '',
                'sort_order'    => $i,
            );
            $parent_id = $this->NWeixinMenu->add($data);
            $i++;
            if (isset($value['sub_button'])) {
                #有二级菜单
                $j = 1;
                foreach ($value['sub_button'] as $k => $val) {
                    $data = array(
                        'weixin_id'     => $this->weixin_data['weixin_id'], 
                        'web_id'        => session('web_id'),
                        'name'          => urldecode($val['name']),
                        'parent_id'     => $parent_id,
                        'url'           => isset($val['url']) ? $val['url'] : '',
                        'type'          => isset($val['type']) ? $val['type'] : '',
                        'key'           => isset($val['key']) ? $val['key'] : '',
                        'content'       => '',
                        'sort_order'    => $j,
                    );
                    $this->NWeixinMenu->add($data);
                    $j++;
                }
            }
        }
        if ($redata['errcode'] == 0) {
            $this->success("菜单还原成功");
        } else {
            $this->error("菜单还原失败");
        }

    }

    //微信菜单
    public function menu() {
        $wikiMenuResult = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'parent_id' => 0))->select();
        $result = array();
        foreach ($wikiMenuResult as $value) {
            $result[] = $value;
            $name = $value['name'];
            $childWiki = $this->NWeixinMenu->where(array('parent_id' => $value['menu_id'], 'weixin_id' => $this->weixin_data['weixin_id']))->select();
            foreach ($childWiki as $val) {
                $result[] = array(
                    'menu_id' => $val['menu_id'],
                    'weixin_id' => $val['weixin_id'],
                    'parent_id' => $val['parent_id'],
                    'sort_order' => $val['sort_order'],
                    'name' => $name . ' > ' . $val['name'],
                );
            }
        }
        $this->assign('data', $result);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';
        $this->title='微信菜单';        
        $this->display();
    }

    //删除自定义菜单
    public function delete($id) {
        $parent_id = $this->NWeixinMenu->where(array('menu_id' => $id))->getField('parent_id');
        if ($parent_id == 0) {
            $is = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'menu_id' => $id))->delete();
            $this->NWeixinMenu->where(array('parent_id' => $id))->delete();
        } else {
            $is = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'menu_id' => $id))->delete();
        }
        if ($is) {
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }

    //删除自定义菜单
    public function edit() {
        //根据菜单id 获取某个菜单信息
        $id = I('get.id', 0, 'intval');
        $data = $this->NWeixinMenu->where(array('menu_id' => $id))->find();
    
        //响应类型
        $types = array(
            array('val' => 'view', 'name' => '链接'),
            array('val' => 'click', 'name' => '文本'),
            array('val' => 'click_promote', 'name' => '推荐位'),
        );
        
        //获取一级菜单
        if ($data['parent_id']) {
            //修改页面 获取除自己以外的一级菜单
            //查找子元素个数, 如果为0, 可以选择更换上级菜单,可以选择更换响应类型, 如果>0,不能更换菜单和响应类型
            $hasChild = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'parent_id' => $id))->count();
            $parent_menu = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'],'parent_id' => 0, 'menu_id' => array('neq', $id)))->select();
        } else {
            //添加页面 获取所有一级菜单
            $parent_menu = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'],'parent_id' => 0))->select();
            $hasChild = 0;
        }

        $promote_id = 0;
        if ($data['type'] == 'click_promote') {
            $arr = explode('_', $data['key']);
            $promote_id = $arr[1];
        }

        //获取推荐位
        $promote =  M('NBookPromoteType')->order('promote_id asc')->select();
        
        if ($this->isPost()) {
            //主菜单最多三个,每个菜单子菜单最多五个
            $data['name'] = trim($_POST['name']);
            $data['parent_id'] = isset($_POST['parent_id']) ? $_POST['parent_id'] : 0;
            $data['type'] = isset($_POST['type']) ? $_POST['type'] : '';
            $data['url'] = isset($_POST['url']) ? $_POST['url'] : '';
            $data['content'] = isset($_POST['content']) ? $_POST['content'] : '';
            if ($data['type'] == 'click_promote') {
                $data['key'] = 'promote_' . $_POST['promote_id'];
            }
            $data['sort_order'] = trim($_POST['sort_order']);
            $data['web_id'] = session('web_id');
            $data['weixin_id'] = $this->weixin_data['weixin_id'];
            
            if ($id) {
                //修改
                $count = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'],'parent_id' => $data['parent_id'], 'menu_id' => array('neq' => $id)))->count();
                if ($data['parent_id'] == 0) {
                    if ($count == 3) {
                        $this->error('失败! 一级菜单最多可以设置三个');
                    }
                } else {
                    if ($count == 5) {
                        $this->error('失败! 此主菜单下最多可设置五个二级菜单');
                    }
                }
                $this->NWeixinMenu->where(array('menu_id' => $id))->save($data);
                $this->success("修改成功", U('Settings/menu'));        
            } else {
                //增加
                $count = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'],'parent_id' => $data['parent_id']))->count();
                if ($data['parent_id'] == 0) {
                    if ($count == 3) {
                        $this->error('失败! 一级菜单最多可以设置三个');
                    }
                } else {
                    if ($count == 5) {
                        $this->error('失败! 此主菜单下最多可设置五个二级菜单');
                    }
                }
                if ($data['type'] == 'click') {
                    $maxId = $this->NWeixinMenu->order('menu_id desc')->limit(1)->find();
                    $data['key'] = 'Click' . (($maxId['menu_id']) + 1);
                }
                $uid = $this->NWeixinMenu->add($data);
                if ($uid > 0) {
                    $this->success("添加成功", U('Settings/menu'));
                } else {
                    $this->error("系统错误");
                }
            }
        } else {
            $this->assign('data', $data);
            $this->assign('types', $types);          
            $this->assign('parent_menu', $parent_menu);
            $this->assign('hasChild', $hasChild);
            $this->assign('promote', $promote);
            $this->assign('promote_id', $promote_id);      
            $this->title='微信菜单编辑'; 
            $this->display();
        }
    }


    private function curl($url, $data = array(), $post = false) {
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


    //同步菜单
    public function uploadmenu() {
        $data = $this->weixin_data;
        if (!$data) {
            $this->error("错误! 此账号没有找到菜单");
        }
        $appid = $data['app_id'];
        $secret = $data['app_secret'];
        //得到access_token
        $data = $this->curl("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret");
        if (!isset($data['access_token'])) {
            $this->error("错误! 得到access_token获取失败");
        }
        $token = $data['access_token'];
        //$result = $this->curlPost("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $token);
        // print_r($token);
        // die();
        if ($token) {
            $wikiMenuResult = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'parent_id' => 0))->order('sort_order')->select();
            $data = array();
            foreach ($wikiMenuResult as $k => $value) {
                $childMenu = $this->NWeixinMenu->where(array('weixin_id' => $this->weixin_data['weixin_id'], 'parent_id' => $value['menu_id']))->order('sort_order')->select();
                $child = array();
                foreach ($childMenu as $key => $val) {
                    $types = explode('_', $val['type']);
                    $childType = $types[0];

                    $child[$key] = array('name' => urlencode(trim($val['name'])), 'type' => $childType);
                    switch ($childType) {
                        case 'view':
                            $child[$key]['url'] = $val['url'];
                            break;
                        case 'click':
                            $child[$key]['key'] = $val['key'];
                            break;
                    }
                }

                $data[$k]['name'] = urlencode(trim($value['name']));
                if ($child) {
                    $data[$k]['sub_button'] = $child;
                } elseif ($value['type']) {
                    $types = explode('_', $value['type']);
                    $type = $types[0];
                    if (($type == 'click') && $value['key']) {
                        $data[$k]['key'] = $value['key'];
                        $data[$k]['type'] = $type;
                    } elseif (($type == 'view') && $value['url']) {
                        $data[$k]['url'] = $value['url'];
                        $data[$k]['type'] = $type;
                    }
                }
            }

            if ($data) {
                $msg = $this->curl('https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . trim($token), urldecode(json_encode(array('button' => $data))), true);
                if ($msg['errmsg'] == 'ok') {
                    $this->success("菜单同步成功!请取消关注再关注才可看到其效果!");
                } else {
                    $this->error("同步失败");
                }
            } else {
                $this->error("错误! 此账号没有找到菜单");
            }
        } else {
            $this->error("错误! 请检查 AppID 和 AppSecret 是否正确!");
        }
    }
    //关键字消息回复列表页
    public function automessage() {
        $auto = M('NWeixinAutomatic');
        import('ORG.Util.MyPage');
        if ($this->isPost()) {
            $where['title'] = array('like', "%$_POST[keyword]%");
        }
        $where['web_id'] = $_SESSION[agent_id];
        $count = $auto->where($where)->count(); // 查询满足要求的总记录数   
        $Page =new MyPage($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数 
        $automatic = $auto->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('automatic', $automatic);
        //翻页样式
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->title='消息回复';        
        $this->display();
    }
    //添加关键字回复
    public function addreply(){
         if ($this->isPost()) {
            $auto = M('NWeixinAutomatic');
            $fcusx = $auto->where(array('title' => trim($_POST[title]), 'web_id' => $_SESSION[agent_id]))->find();
            if (!is_array($fcusx)) {
                $data['web_id'] = $_SESSION[agent_id];
                $data['title'] = trim($_POST[title]);
                $data['content'] = trim($_POST[content]);
                $is = $auto->add($data);

                if ($is) {
                    $this->success("添加成功!", U('Settings/automessage'));
                } else {
                    $this->error("系统错误");
                }
            } else {
                $this->error("标题已经存在");
            }
        } 
        else {
            //$this->assign("title", '添加');
            $this->display();
        }
    }

    //删除回复消息
    public function del_mess($id) {
        $auto = M('NWeixinAutomatic');
        $is = $auto->where(array('id' => $id))->delete();
        if ($is) {
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }
    }

    //修改回复消息
    public function editmess($id){
        $auto = M('NWeixinAutomatic');
        $automatic = $auto->where(array('id' => $id))->find();
        //print_r($automatic);
        if ($this->isPost()) {
                $data['id'] = $id;
                $data['web_id'] = $_SESSION[agent_id];
                $data['title'] = trim($_POST[title]);
                $data['content'] = trim($_POST[content]);
                $is = $auto->save($data);
                if ($is) {
                    $this->success("修改成功!", U('Settings/automessage'));
                } else {
                    $this->error("系统错误");
                }          
        }else{
            $this->assign('automatic',$automatic);
            $this->display();
        } 
        
    }
}
