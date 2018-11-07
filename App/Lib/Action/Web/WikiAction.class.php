<?php

//公众号
class WikiAction extends GlobalAction {

    public function index() {
        //每个站点最多10个公众号
        $ad = M('Wiki');
        $where['web_id'] = $this->to['web_id'];
        $wiki = $ad->where($where)->select();

        $count = count($wiki); // 查询满足要求的总记录数 

        $this->assign('count', $count);
        $this->assign('wikis', $wiki);
        $this->display();
    }

    //关注后消息
    public function subscribe($id) {
        $wiki = M('Wiki')->where(array('wiki_id' => $id, 'web_id' => $this->to['web_id']))->find();
        //file_put_contents("a.txt",$wiki);
        if ($wiki) {
            if ($this->isPost()) {
                $result = M('Wiki')->where(array('wiki_id' => $id, 'web_id' => $this->to['web_id']))->save(array('content' => trim($_POST['content'])));
                if ($result) {
                    $this->success("修改成功", U('Wiki/index'));
                } else {
                    $this->error("系统错误");
                }
            } else {
                $this->assign('wiki', $wiki);
                $this->display();
            }
        } else {
            $this->error("找不到此公众号信息");
        }
    }

    //公众号下自定义菜单
    public function menu($id) {
        $wikiMenu = M('WikiMenu');

        $wikiMenuResult = $wikiMenu->where(array('wiki_id' => $id, 'parent_id' => -1))->select();
        $result = array();

        foreach ($wikiMenuResult as $value) {
            $result[] = $value;
            $name = $value['name'];
            $childWiki = $wikiMenu->where(array('parent_id' => $value['menu_id'], 'wiki_id' => $id))->select();
            foreach ($childWiki as $val) {
                $result[] = array(
                    'menu_id' => $val['menu_id'],
                    'wiki_id' => $val['wiki_id'],
                    'parent_id' => $val['parent_id'],
                    'sort_order' => $val['sort_order'],
                    'name' => $name . ' > ' . $val['name'],
                );
            }
        }

        $this->getWiki($id);
        import('ORG.Util.Page'); // 导入分页类
        $page = new Page($count, 30);

        $page->setConfig('theme', "<div class='mpage'><div class='mpagel'>%prePage%%upPage%%linkPage%%downPage%%nextPage%</div><div class='mpager'>[总数 <span>%totalRow%</span> 篇] [当前 <span>%nowPage%</span>/%totalPage% 页]</div></div>");

        $this->assign('page', $page->show()); // 赋值分页输出

        $this->assign('wikiMenu', $result);
        $this->display();
    }

    //返回公众号信息
    protected function getWiki($id) {
        $wiki = M('Wiki')->where(array('wiki_id' => $id))->find();
        $this->assign('wiki', $wiki);
    }

    //主菜单最多三个,每个菜单子菜单最多五个
    protected function menuCount($id, $parentId, $mid) {
        if ($mid) {
            $count = M('WikiMenu')->where(array('wiki_id' => $id, 'parent_id' => $parentId, 'menu_id' => array('neq' => $mid)))->count();
        } else {
            $count = M('WikiMenu')->where(array('wiki_id' => $id, 'parent_id' => $parentId))->count();
        }

        if ($parentId == -1) {
            if ($count == 3) {
                return '失败! 一级菜单最多可以设置三个';
            }
        } else {
            if ($count == 5) {
                return '失败! 此主菜单下最多可设置五个二级菜单';
                ;
            }
        }

        //如果为子菜单则清除对应一级菜单响应类型
//    	if ($parentId != -1) {
//    		M('WikiMenu')->where(array('menu_id' => $parentId))->save(array('url' => '', 'type' => '', 'key' => '', 'content' => ''));
//    	}

        return false;
    }

    //添加自定义菜单
    public function addmenu($id) {
        if ($this->isPost()) {

            //主菜单最多三个,每个菜单子菜单最多五个
            if (!($meg = $this->menuCount($id, $_POST['parent_id']))) {

                $wikiMenu = M('WikiMenu');
                $wiki = M('Wiki')->where(array('wiki_id' => $id))->find();
                $maxId = $wikiMenu->order('menu_id desc')->find();

                $data['web_id'] = $this->to['web_id'];
                $data['wiki_id'] = $id;
                $data['name'] = trim($_POST[name]);
                $data['parent_id'] = ($_POST[parent_id]);
                $data['url'] = ($_POST[url]);
                $data['type'] = ($_POST[type]);

                $data['key'] = (($_POST[type] == 'click') || ($_POST[type] == 'click_news')) ? ('Click' . (($maxId['menu_id']) + 1)) : '';
                if ($_POST[type] == 'click_promote') {
                    $data['key'] = 'promote_' . $_POST['promote_id'];
                }
                if ($data['type'] == 'click_news') {
                    $data['content'] = $this->getContent();
                } else {
                    $data['content'] = $_POST[content];
                }

                $data['sort_order'] = ($_POST[sort_order]);

                $uid = $wikiMenu->add($data);

                if ($uid > 0) {
                    $this->updatetime($id);
                    $this->success("添加成功", U('Wiki/menu', array('id' => $id)));
                } else {
                    $this->error("系统错误");
                }
            } else {
                $this->error($meg);
            }
        } else {
            $this->getWiki($id);
            $this->getWikiMenuType();

            $this->getWikiMenuParent($id);

            $this->assign('title', '添加');
            $this->assign('button', '确认添加');

            $this->getPromote();
            $this->display();
        }
    }

    //获取图文信息
    protected function getContent() {
        $img = array();
        $newImage = array();
        $files = array();
        //上传图片数组重新组
        if (count(array_filter($_FILES['pic']['tmp_name'])) > 1) {
            foreach ($_FILES['pic'] as $key => $value) {
                $i = 0;
                $newVal = array();
                ;
                foreach ($value as $k => $val) {
                    if (($val && ($key != 'error')) || (($key == 'error') && ($val < 1))) {
                        $newVal[$i] = $val;
                        $img[$k] = $i;
                        $i++;
                    }
                }
                if ($newVal || $key == 'error') {
                    $newImage[$key] = $newVal;
                }
            }
        } else {
            foreach ($_FILES['pic'] as $key => $value) {
                $i = 0;
                $newVal = 0;
                foreach ($value as $k => $val) {
                    if ($val && ($key != 'error')) {
                        $newVal = $val;
                        $img[$k] = $i;
                        $i++;
                    }
                }

                if ($newVal || $key == 'error') {
                    $newImage[$key] = $newVal;
                }
            }
        }

        if ($newImage['tmp_name']) {
            $_FILES['pic'] = $newImage;
            $urls = './Upload/weixin/';
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 3145728; // 设置附件上传大小
            $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
            $upload->savePath = $urls; // 设置附件上传目录
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息
                $imgs = $upload->getUploadFileInfo();
            }
        }

        $returnData = array();
        foreach ($_POST['piccontent'] as $key => $value) {
            //如果有上传图片，则覆盖并上传文件


            $returnData[] = array(
                'title' => trim($value['title']),
                'description' => trim($value['description']),
                'img' => $imgs[$img[$key]]['savename'] ? $imgs[$img[$key]]['savename'] : $value['img'],
                'url' => trim($value['url']),
            );
        }

        return serialize($returnData);
    }

    //修改最后修改时间
    public function updatetime($id) {
        M('Wiki')->where(array('wiki_id' => $id))->save(array('update_time' => date('Y-m-d H:i:s', time())));
    }

    //修改自定义菜单
    public function updatemenu($id, $mid) {
        if ($this->isPost()) {
            //主菜单最多三个,每个菜单子菜单最多五个
            if (!($meg = $this->menuCount($id, $_POST['parent_id'], $mid))) {
                $aa = M('WikiMenu');

                $data['name'] = trim($_POST[name]);
                if (isset($_POST[parent_id])) {
                    $data['parent_id'] = ($_POST[parent_id]);
                }

                $data['url'] = ($_POST[url]);
                if (isset($_POST[type])) {
                    $data['type'] = ($_POST[type]);
                }


                if ($data['type'] == 'click_news') {
                    $data['content'] = $this->getContent();
                } else {
                    $data['content'] = $_POST[content];
                }

                if ($data['type'] == 'click_promote') {
                    $data['key'] = 'promote_' . $_POST['promote_id'];
                }

                $data['sort_order'] = ($_POST[sort_order]);

                $aa->where(array('menu_id' => $mid, 'wiki_id' => $id))->save($data);
                $this->updatetime($id);
                $this->success("修改成功", U('Wiki/menu', array("id" => $id)));
            } else {
                $this->error($meg);
            }
        } else {
            $this->getWiki($id);
            $this->getWikiMenuType();

            $wikiMeun = M('WikiMenu')->where(array('menu_id' => $mid, 'wiki_id' => $id))->find();

            $hasChild = M('WikiMenu')->where(array('parent_id' => $mid, 'wiki_id' => $id))->count();

            if (($wikiMeun['parent_id'] == -1) && !($hasChild)) {
                $this->getWikiMenuParent($id, $mid);
            } else {
                $this->getWikiMenuParent($id);
            }

            //如果类型为图文
            $count = 0;
            if ($wikiMeun['type'] == 'click_news') {
                $content = unserialize($wikiMeun['content']);
                $this->assign('content', $content);
                $wikiMeun['content'] = '';
                $count = count($content);
            }

            if ($wikiMeun['type'] == 'click_promote') {
                $arr = explode('_', $wikiMeun['key']);
                $this->assign('promote_id', $arr[1]);
            }
            $this->getPromote();

            $this->assign('count', $count);
            $this->assign('hasChild', $hasChild);

            $this->assign('wikiMeun', $wikiMeun);


            $this->assign('title', '修改');
            $this->assign('button', '确认修改');

            $this->display('addmenu');
        }
    }

    //删除自定义菜单
    public function delmenu($id, $mid) {
        $is = M('WikiMenu')->where(array('wiki_id' => $id, 'menu_id' => $mid))->delete();
        if ($is) {
            $this->updatetime($id);
            $this->success("删除成功", U('Wiki/menu', array('id' => $id)));
        } else {
            $this->error("删除失败");
        }
    }

    //一级菜单
    public function getWikiMenuParent($id, $mid = 0) {
        if ($mid) {
            $result = M('WikiMenu')->where(array('wiki_id' => $id, 'parent_id' => array('eq', -1), 'menu_id' => array('neq', $mid)))->select();
        } else {
            $result = M('WikiMenu')->where(array('wiki_id' => $id, 'parent_id' => array('eq', -1)))->select();
        }

        $this->assign('wikiMenus', $result);
    }

    //自定义菜单类型, val 对应https://mp.weixin.qq.com/wiki/13/43de8269be54a0a6f64413e4dfa94f39.html
    protected function getWikiMenuType() {
        $types = array(
            array('val' => '', 'name' => '无'),
            array('val' => 'view', 'name' => '链接'),
            array('val' => 'click', 'name' => '推送文本消息'),
            array('val' => 'click_news', 'name' => '推送图文消息'),
            array('val' => 'click_promote', 'name' => '推送推荐位消息'),
        );
        $this->assign('types', $types);
    }

    //修改公众号
    public function update($id) {

        $wiki = M('Wiki');
        if ($this->isPost()) {

            if (!empty($_POST[appid])) {   // 如果有信息传送过来  即调用该脚本
                $count = $wiki->where(array('appid' => trim($_POST[appid]), 'wiki_id' => array('neq', $id)))->find();         // 获取数据条数

                if (is_array($count)) {
                    $this->error("该账户已存在");
                }
            }
            $data['web_id'] = ($this->to['web_id']);
            $data['user'] = trim($_POST[user]);
            $data['name'] = trim($_POST[name]);
            $data['passwd'] = trim($_POST[passwd]);
            $data['administrator'] = trim($_POST[administrator]);
            $data['appid'] = trim($_POST[appid]);
            $data['secret'] = trim($_POST[secret]);
            $data['original_id'] = trim($_POST[original_id]);

            $wiki->where(array('wiki_id' => $id))->save($data);
            $this->updatetime($id);
            $this->success("修改成功", U('Wiki/index'));
        } else {

            $webs = M("SystemWeb")->select();
            $result = M('Wiki')->where(array('wiki_id' => $id))->find();

            if (!is_array($result)) {
                $this->error("没有该账户");
            }
            $this->assign('wiki', $result);
            $this->assign('webs', $webs);

            $this->assign('title', '修改');
            $this->assign('button', '确认修改');

            $this->display('add');
        }
    }

    //新增公众号
    public function add() {
        if ($this->isPost()) {

            $wiki = M('Wiki');
            if (!empty($_POST[appid])) {   // 如果有信息传送过来  即调用该脚本
                $count = $wiki->where(array('appid' => trim($_POST[appid])))->find();         // 获取数据条数
                if (is_array($count)) {
                    $this->error("该账户已存在");
                }
            }

            $data['web_id'] = ($this->to['web_id']);
            $data['user'] = trim($_POST[user]);
            $data['name'] = trim($_POST[name]);
            $data['passwd'] = trim($_POST[passwd]);
            $data['administrator'] = trim($_POST[administrator]);
            $data['appid'] = trim($_POST[appid]);
            $data['secret'] = trim($_POST[secret]);
            $data['original_id'] = trim($_POST[original_id]);

            $data['time'] = date('Y-m-d H:i:s', time());


            $uid = $wiki->add($data);

            if ($uid > 0) {
                $this->success("添加成功", U('Wiki/index'));
            } else {
                $this->error("系统错误");
            }
        } else {
            $webs = M("SystemWeb")->select();
            $this->assign('webs', $webs);
            $this->assign('title', '添加');
            $this->assign('button', '确认添加');
            $this->display();
        }
    }

    //同步菜单
    public function uploadmenu($id) {
        $wiki = M('Wiki')->where(array('wiki_id' => $id))->find();
        $token = $this->getToken($wiki['appid'], $wiki['secret']);
        $result = $this->curlPost("https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $token);
        // print_r($token);
        // die();
        if ($token) {
            $wikiMenu = M('WikiMenu');
            $wikiMenuResult = $wikiMenu->where(array('wiki_id' => $id, 'parent_id' => -1))->order('sort_order')->select();
            $data = array();
            foreach ($wikiMenuResult as $k => $value) {
                $childMenu = $wikiMenu->where(array('wiki_id' => $id, 'parent_id' => $value['menu_id']))->order('sort_order')->select();
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

            $newdata = array(
                array(
                    'name' => urlencode("好书推荐"),
                    'sub_button' => array(
                        array(
                            'name' => urlencode("首页搜书"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Index/index/web/1.html",
                        ),
                        array(
                            'name' => urlencode("往期精彩"),
                            'type' => 'view',
                            "url" => "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzIyNzQ0MTM5OA==&scene=124#wechat_redirect",
                        ),
                        array(
                            'name' => urlencode("小说排行"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Rankinglist/index/web/1.html",
                        ),
                        array(
                            'name' => urlencode("精品女频"),
                            'type' => 'click',
                            "key" => "jingpinnvpin",
                        ),
                        array(
                            'name' => urlencode("热销古言"),
                            'type' => 'click',
                            "key" => "rexiaoguyan",
                        ),
                    )
                ),
                array(
                    'name' => urlencode("继续阅读"),
                    'sub_button' => array(
                        array(
                            'name' => urlencode("继续阅读"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Bookcase/reading/web/1.html",
                        ),
                        array(
                            'name' => urlencode("粉丝福利"),
                            'type' => 'click',
                            "key" => "fuli",
                        ),
                        array(
                            'name' => urlencode("我的书架"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Bookcase/index/web/1.html",
                        ),
                        array(
                            'name' => urlencode("个人中心"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Personal/index/web/1.html",
                        )
                    )
                ),
                array(
                    'name' => urlencode("充值"),
                    'sub_button' => array(
                        array(
                            'name' => urlencode("我要充值"),
                            'type' => 'view',
                            "url" => "http://w.ymbook.cn/Pay/index/web/1.html",
                        ),
                        array(
                            'name' => urlencode("联系客服"),
                            'type' => 'click',
                            "key" => "kefus",
                        ),
                    )
                )
            );

//			    $data = $newdata;
//			    foreach ($newdata as $k => $value)
//			    {
//			    	$subbutton = array();
//			    	if ($value['sub_button']) {
//			    		foreach ($value['sub_button'] as $key => $sub_button)
//			    		{
//			    			$subbutton[$key] = $sub_button;
//			    			$subbutton[$key]['name'] = urlencode($sub_button['name']);
//			    		}
//			    	}
//			    	$data[$k] = $value;
//			    	$data[$k]['name'] = urlencode($value['name']);
//			    	$data[$k]['sub_button'] = $subbutton;
//			    }

            if ($data) {
                $msg = $this->curlPost('https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . trim($token), urldecode(json_encode(array('button' => $data))), true);
                // print_r($msg);
                // exit();
                if ($msg['errmsg'] == 'ok') {
                    M('Wiki')->where(array('wiki_id' => $id))->save(array('upload_time' => date('Y-m-d H:i:s', time())));
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

    //获取token
    public function getToken($appid, $secret) {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($appid) . '&secret=' . trim($secret);
        $result = $this->curlPost($url);
        // print_r($result);
        // die();
        if ($token = $result['access_token']) {
            return $token;
        }
    }

    public function curlPost($url, $data = array(), $post = false) {
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

    //获得推荐位
    public function getPromote() {
        $kuan = M('WebPromote');
        $where['pc'] = 0;
        $promote = $kuan->where($where)->order('promote_id asc')->select();
        // var_dump($promote);exit;
        $this->assign('promote', $promote);
    }

    //查看推荐位是否有内容
    public function checkPromote($promote_id) {
        $bookpromote = M('bookPromote');
        $where['web_id'] = $this->to['web_id'];
        $where['promote_id'] = $promote_id;
        $num = $bookpromote->where($where)->count();
        if (!$num) {
            echo 0;
            exit;
        }
        echo 1;
    }

    //模板消息查看
    public  function tempmessage($id) {
        $tempmessage = M('WikiTempmessage');
        $where = array("wiki_id" => $id, "status" => 1);
        $count = $tempmessage->where($where)->count(); // 查询满足要求的总记录数
        import('ORG.Util.Page'); // 导入分页类
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数    
        $data = $tempmessage->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('time desc')->select();
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");

        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('data', $data);
        $this->assign('id', $id);
        $this->display();
    }

    //模板消息添加
    public function addTempMessage($id){
        if ($this->isPost()) {
            $this->sendMessage($id, $_POST);
        } else {
            $tempmessage = M('WikiTempmessage');
            $where = array("wiki_id" => $id, "status" => "2");
            $data = $tempmessage->where($where)->find();
            $this->assign("id", $id);
            $this->assign("data", $data);
            $this->display();     
        }
    }    

    //模板消息保存
    public function saveTempMessage($id){
        if ($this->isPost()) {
            $tempmessage = M('WikiTempmessage');
            $where = array("wiki_id" => $id, "status" => 2);
            $res = $tempmessage->where($where)->find();
            $data = array(
                    "wiki_id"       => $id,
                    'type'          => $_POST[type],
                    'remark'        => $_POST[remark],
                    'temp_id'       => $_POST[temp_id],
                    'title'         => $_POST[title],
                    'content'       => $_POST[content],
                    'url'           => $_POST[url],
                    'title_color'   => $_POST[title_color],
                    'type_color'    => $_POST[type_color],
                    'content_color' => $_POST[content_color],
                    'remark_color'  => $_POST[remark_color], 
                    "status"        => 2,
                    "time"          => date('Y-m-d H:i:s', time()),
                    "sub_time"      => $_POST[sub_time],
            );
            if ($res) {
                $flag = $tempmessage->where("id = ".$res['id'])->save($data); 
            } else {
               $flag = $tempmessage->add($data); 
            }
            
            if ($flag) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 2;
        }
    }

    //模板消息修改
    public function editTempMessage($id, $wiki_id){
        if ($this->isPost()) {
            $this->editSendMessage($id, $wiki_id, $_POST);
        } else {
            $tempmessage = M('WikiTempmessage');
            $data = $tempmessage->find($id);
            $this->assign("id", $id);
            $this->assign("wiki_id", $wiki_id);
            $this->assign("data", $data);
            $this->display();     
        }
    } 

    //发送消息
    public function sendMessage($id, $data) {
        // $startTime = microtime(true);
        //忽略用户等待
        ignore_user_abort(true);
        //长时间执行 300秒
        // set_time_limit(300); 
        // @ini_set('memory_limit','128M');
        $wiki = M('Wiki')->find($id);
        if (!$wiki) {
            echo "公众号不存！";
            exit;
        }

        $WikiTempmessage = M("WikiTempmessage");
        //取最新一条记录
        $res = $WikiTempmessage->where(array('wiki_id' => $id, 'status' => 1))->order('id desc')->find();
        if ($res) {
            //设置了定时时间
            if ($data['sub_time'] && $data['sub_time'] != '0000-00-00 00:00:00') {
                //记录时间大于等于设置的时间
                $sub_time = substr($data['sub_time'], 0, 10);
                if(strtotime($res['time']) >= strtotime($sub_time)){
                    echo "今天已发送过模板消息!";
                    exit;
                } else {
                    if(strtotime($res['sub_time']) >= strtotime($sub_time) || strtotime($res['sub_time']) >= strtotime(date('Y-m-d', time())) && $res['sub_time'] !== '0000-00-00 00:00:00'){
                        echo $res['sub_time']."日已预约过模板消息!";
                        exit;
                    }
                }
            } else {
                //没设置定时时间
                //时间大于等于当前凌晨时间, 说明今天已经发过
                if(strtotime($res['time']) >= strtotime(date('Y-m-d', time()))){
                    echo "今天已发送过模板消息!";
                    exit;
                }
            }        
        }


        //发送结果插入数据库
        $data['wiki_id'] = $id;
        $data['time'] = date('Y-m-d H:i:s', time());
        $data['status'] = 1;
        $result = $WikiTempmessage->add($data);

        //获取token,  open_id
        $token = $this->getToken($wiki['appid'], $wiki['secret']);
        if(!$token){
            echo "token 获取失败!";
            exit;
        }
        $open_ids = $this->get_openId($token);

        //将信息提交到远程redis
        $data['token'] = $token;
        $data['open_ids'] = $open_ids;
        // echo json_encode($data);
        // exit;
        $this->curlPost('http://211.149.206.233:8090/rpush.php?id='.$id.'&sub_time='.urlencode($data['sub_time']), json_encode($data), 1);

        if ($result) {
            echo 1;
        }
    }

    //修改发送消息
    public function editSendMessage($id, $wiki_id, $data) {
        $wiki = M('Wiki')->find($wiki_id);
        if (!$wiki) {
            echo "公众号不存！";
            exit;
        }
       
        //发送结果插入数据库
        $data['wiki_id'] = $wiki_id;
        $data['time'] = date('Y-m-d H:i:s', time());
        $result = M("WikiTempmessage")->where(array("id" => $id))->save($data);

        //获取token,  open_id
        $token = $this->getToken($wiki['appid'], $wiki['secret']);
        if(!$token){
            echo "token 获取失败!";
            exit;
        }
        $open_ids = $this->get_openId($token);

        //将信息提交到远程redis
        $data['token'] = $token;
        $data['open_ids'] = $open_ids;
        $this->curlPost('http://211.149.206.233:8090/edit.php?id='.$wiki_id.'&sub_time='.urlencode($data['sub_time']), json_encode($data), 1);

        if ($result) {
            echo 1;
        }
    }

    //获取用户opneID
    public function get_openId($token){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token;
        $data = $this->curlPost($url);
        if (empty($data)) {
            // $this->error("获取用户opneID失败！");
            echo "获取用户opneID失败！";
            exit;
        }
        $result = $data['data']['openid'];
        if ($data['total'] > 10000) {
            $page = ceil($data['total'] / 10000);
            $next_openid = $data['next_openid'];
            $temp = array();
            $openid_str = "";
            for ($i=1; $i < $page ; $i++) { 
                $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token.'&next_openid='.$next_openid;
                $data2 = $this->curlPost($url);
                $next_openid = $data2['next_openid'];
                $openid_str .= join(',', $data2['data']['openid']) . ",";
                unset($data2);
            }
            $openid_str = rtrim($openid_str, ",");
            $result = array_merge($data['data']['openid'], explode(',', $openid_str));
        }
        return $result;
    }

}
