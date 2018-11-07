<?php

//首页
class MainAction extends GlobalAction {

    //首页
    public function index() {
        $this->display();
    }

    //菜单
    public function menu() {
        $this->assign("fu_agent", $this->to['fu_agent']);
        $this->display();
    }

    //主页
    public function main() {
        $this->tongji();
        //干掉客户经理
        // if ($this->to[fu_agent] != 0) {
        //     $this->mycontact($this->to[fu_agent]);
        // }
        
        $notice = cookie('notice');
        $content = '';
        if ($notice == 1) {
            $res =  M('SystemAnnouncement')->order('id desc')->find();
            if ($res) {
                $content = $res['content'];
            }
        }
        $this->assign('content', $content);
        $this->assign('notice', $notice);
        $this->display();
    }

    public function show_notice(){
            $res =  M('SystemAnnouncement')->order('id desc')->find();
            echo $res['content'];
             cookie('notice', null);
    }

    private function tongji() {
        $tongjiming = "tongjiweb" . $this->to[agent_id];
        $tongji = S($tongjiming);
        if ($tongji) {
            $this->assign("tongji", $tongji);
        } else {
            $map['agent_id'] = $this->to['agent_id'];
            //money_month 是统计错误的需要从新查询
            $agent_model = M('Agent'); 
            $agent = $agent_model->where($map)->field('money_total,money_settlement,fu_agent')->find();
            $tongji['money_total'] = $agent['money_total'];
            $tongji['money_settlement'] = $agent['money_settlement'];

            //本月充值
            $a_id = $map['agent_id'];
            $m = M(); 
            $sql = "SELECT ifnull(sum(money),0) money FROM hezuo_agent_moneyday WHERE DATE_FORMAT( time, '%Y%m' ) = DATE_FORMAT( NOW() , '%Y%m' ) and agent_id =  $a_id ";
            $arr = $m->query($sql);
            $tongji['money_month'] = $arr[0]['money'];
            if ($tongji['fu_agent'] == 0) {
                #查提成
                $agents = $agent_model->where('fu_agent='.$a_id)->field('agent_id')->select();
                foreach ($agents as $key => $value) {
                    $sql = "SELECT ifnull(sum(money), 0) money FROM hezuo_agent_moneyday WHERE DATE_FORMAT( time, '%Y%m' ) = DATE_FORMAT( NOW() , '%Y%m' ) and agent_id =  $value[agent_id] ";
                    $arr = $m->query($sql);
                    $tongji['money_month'] += $arr[0]['money'];
                }
            }
            //查询今日充值
            $tongji['money_day'] = 0;
            $shijian = date('Y-m-d', time());
            $map['statistical'] = 0; //0没有统计1统计过了作废
            $map['time'] = array('like', "%$shijian%");
            $map['state']=2;
            $pay = M('SystemPay')->where($map)->field('money')->select();
            for ($i = 0; $i < count($pay); $i++) {
                $tongji['money_day'] += $pay[$i]['money'];
            }
            S($tongjiming, $tongji, 3600);
            $this->assign("tongji", $tongji);
        }
    }

    //客户经理
    private function mycontact($fu_agent) {
        $isagent = M('Agent')->where(array('agent_id' => $fu_agent))->field('pen_name,phone,qq,email')->find();
        $this->assign('isagent', $isagent);
    }

    //删除统计缓存
    public function newtongji() {
        $tongjiming = "tongjiweb" . $this->to[agent_id];
        echo S($tongjiming, NULL);
    }

    //修改密码
    public function pass() {
        if ($this->isPost()) {
            $user = M('Agent');
            $arr = $user->where(array('agent_id' => $this->to['agent_id']))->find();
            if ($arr) {
                if ($arr['user_pass'] != md5(trim($_POST['old_passwd']) . C('ALL_ps'))) {
                    $this->error("修改失败,原始密码输入错误!");
                } else {
                    $user->where(array('agent_id' => $this->to['agent_id']))->save(array('user_pass' => md5(trim($_POST['new_passwd']) . C('ALL_ps'))));
                    $this->success("修改成功! 请重新登录!", U('Login/index'));
                }
            } else {
                $this->error("账号不存在！！");
            }
        } else {
            $this->display();
        }
    }

    //清除缓存页
    public function remove() {
        $this->display();
    }

    //清除首页-排行缓存
    public function del() {
        $index = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/'.$this->to[web_id].'/index.html';
        $ranking = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/'.$this->to[web_id].'/Rankinglist.html';
        if (file_exists($index) && file_exists($ranking)) {
            // echo 1;
            @unlink($index);
            @unlink($ranking);
            $this->success("清除成功！");
        } else {
            $this->error("缓存文件不存在！");
        }
    }

    //清除站点缓存
    public function delSite() {
        $web = M('Web')->field('web_url')->find($this->to[web_id]);
        if (isset($web['web_url'])) {
            if ($web['web_url'] != '') {
                $web_url = $web['web_url'];
                $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Website/'.$web_url.'.php';
                if (file_exists($file)) {
                    @unlink($file);
                    $this->success("清除成功！");
                    exit;
                }    
            }
        }
        $this->error("缓存文件不存在！");
    }


    //清除新书和限免缓存
    public function delNewFree() {
        $Top_index_9 = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/'.$this->to[web_id].'/Top_index_9.html';
        $Top_index_10 = dirname($_SERVER['DOCUMENT_ROOT']) . '/wap/Html_Cache/'.$this->to[web_id].'/Top_index_10.html';
        if (file_exists($Top_index_9) && file_exists($Top_index_10)) {
            @unlink($Top_index_9);
            @unlink($Top_index_10);
            $this->success("清除成功！");
        } else {
            $this->error("缓存文件不存在！");
        }
    }
}
