<?php

//作者管理
class AuthorAction extends GlobalAction {

    public function index() {
        $ad = M('User');
        if ($this->isPost()) {
            if ($_POST[type] == 1) {
                $where['user_name'] = array('like', "%$_POST[keyword]%");
            } elseif($_POST[type] == 2) {
                $where['pen_name'] = array('like', "%$_POST[keyword]%");
            } else {
                $where['ldentity_card'] = $_POST[keyword];
            }
        }
        //get到数据
        if ($_GET[keyword]) {
            if ($_GET[type] == 1) {
                $where['user_name'] = array('like', "%$_GET[keyword]%");
            } elseif($_GET[type] == 2) {
                $where['pen_name'] = array('like', "%$_GET[keyword]%");
            } else {
                $where['ldentity_card'] = $_GET[keyword];
            }
        }
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $where['uid'] = 1; //作者
        $where['web_id'] = $this->to[web_id];
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $user = $ad->where($where)->field('user_id,web_id,user_name,pen_name,vote,vipvote,integral,alance,money,registration_time')->order('registration_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($user as $key => $value) {
            $user[$key][mem_vip] = LevelAction::paylevel($value[integral]);
        }
        $this->assign('user', $user);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //详情
    public function lnformation($id) {
        $u = M('User');
        if ($this->isPost()) {
            $u->create();
            $u->where(array('user_id' => $id))->save();
            $this->success("修改成功");
        } else {
            $where['user_id'] = $id;
            $user = $u->where($where)->find();
            if (!is_array($user)) {
                $this->error("没有该账户");
            }
            $user[mem_vip] = LevelAction::paylevel($user[integral]);
            $this->assign('user', $user);
            $this->display();
        }
    }

    //密码初始化
    public function pass($id) {
        $user = M('User');
        $myuser = $user->where(array('user_id' => $id))->field('web_id')->find();
        if (!empty($myuser[web_id])) {
            $all = M('Web')->where(array('web_id' => $myuser[web_id]))->field('all_ps')->find();
            if (is_array($all)) {
                $data['user_pass'] = md5("123456" . $all[all_ps]);
                $is = $user->where(array('user_id' => $id))->save($data);
                if ($is) {
                    $this->success("初始化成功！！");
                } else {
                    $this->success("初始化失败！！");
                }
            } else {
                $this->error("没有找到常量");
            }
        } else {
            $this->error("用户不存在");
        }
    }

    //留言回复
    public function reply($id) {

        if($this->isPost()){
            $mess=M('UserMessage');
            $data['user_id']=$id;
            $data['type']=1;
            $data['title']=$_POST[title];
            $data['content']=$_POST[content]." <font color=red>回复编辑-</font>".$this->to[pen_name];
            $data['time']=date('Y-m-d H:i:s', time());
            $isok=M('UserMessage')->add($data);
            if($isok){
                  $this->success("回复成功");
            } else {
                  $this->error("系统错误");
            }
            
        } else {
            $user = M('User')->where(array('user_id'=>$id))->field('user_id,pen_name')->find();
            $this->assign('user',$user);
            $this->display();
        }
    }
            //留言查看
    public function message($id) {
        $mes = M('UserMessage');
        import('ORG.Util.Page'); // 导入分页类
        //统计条数开始
        $where['user_id'] = $id;
        $u = M('User');
        $this->assign('user', $u->where($where)->field('pen_name')->find());
        $count = $mes->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数    
        $message = $mes->where($where)->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('message', $message);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
}
