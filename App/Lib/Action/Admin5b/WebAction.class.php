<?php

//站点管理
class WebAction extends GlobalAction {

    //站长
    public function index() {
        if ($this->isPost()) {
            $where['web_name'] = array('like', "%$_POST[keyword]%");
        }
        $ad = M('Web');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $user = $ad->where($where)->field('web_id,master_id,pc,web_name,web_url,all_ps,login_url,automatic,preload,webphone,webqq,weixin,beian')->order('web_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('user', $user);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //添加站点
    public function add() {
        if ($this->isPost()) {
            $web = M('Web');
            $web->create();
            $web_url = trim($_POST['web_url']);
            $web->web_url = $web_url;
            $iss = $web->add();
            if ($iss) {
                $datas['web_id']=$iss;
                $datas['num']=1;
                $datas['type']="首页1号";               
                M('WebBan')->add($datas);
                //创建微信支付文件
                $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Payconf/'.$datas['web_id'].'.php';
                if (!file_exists($file)) {
                    $this->update_wxconf($file, $web_url);
                    $this->success("添加成功", U('Web/index'));
                }
            } else {
                $this->error("添加出错！");
            }
        } else {
            $this->display();
        }
    }

    //站点修改
    public function save($id) {
        $u = M('Web');
        if ($this->isPost()) {
            $u->create();
            $web_url = trim($_POST['web_url']);
            $u->web_url = $web_url;
            $u->where(array('web_id' => $id))->save();
            //判断域名是否修改
            $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Payconf/'.$id.'.php';
            if(include_once($file)) {
                if (strpos(WxPayConfig::Notify, $web_url) === false) {
                    $this->update_wxconf($file, $web_url);
                }
            }
            $this->success("修改成功");
        } else {
            $where['web_id'] = $id;
            $user = $u->where($where)->find();
            if (!is_array($user)) {
                $this->error("没有该站");
            }
            $this->assign('user', $user);
            $this->display();
        }
    }


    //更新写入微信支付配置文件
    public function update_wxconf($file, $web_url){
            $text = "<?php
                class WxPayConfig {
                    //基本信息
                    const APPID = 'wx458729fd34adecf1';
                    const APPSECRET = '9654e0ce321ca9bf659f28f17956784e';
                    const MCHID = '1315396101';
                    const KEY = '22222222222222222222222222222222';
                    //证书目录
                    const SSLCERT_PATH = '../cert/apiclient_cert.pem';
                    const SSLKEY_PATH = '../cert/apiclient_key.pem';
                    //安全验证
                    const CURL_PROXY_HOST = '0.0.0.0';
                    const CURL_PROXY_PORT = 0;
                    //上报信息
                    const REPORT_LEVENL = 1;
                    //提交地址
                    const Submit = 'http://w.ymzww.cn/Personal/Payinterface'; 
                    //反馈地址
                    const Notify = 'http://{$web_url}/Personal/Notify/weixin/';
                    //失败地址
                    const Shibai = 'http://{$web_url}/Personal/Pay/index.html';   
                    //成功地址
                    const Chenggong = 'http://{$web_url}/Personal/Payto/ok/trade/';   
                    //检测地址
                    const Jiance = 'http://{$web_url}/Personal/Payinterface/payresult/trade/';   

                }";
        $result = file_put_contents($file, $text);
        if (!$result) {
            $this->error("配置文件生成失败请检查！");
        }
    }
}
