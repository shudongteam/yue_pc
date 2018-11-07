<?php

//自动回复
class AutomaticAction extends GlobalAction {

    //显示
    public function index() {
        $auto = M('WeixinAutomatic');
        import('ORG.Util.Page');
        if ($this->isPost()) {
            $where['title'] = array('like', "%$_POST[keyword]%");
        }
        $where['web_id'] = $this->to[web_id];
        $count = $auto->where($where)->count(); // 查询满足要求的总记录数   
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数 
        $automatic = $auto->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('automatic', $automatic);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //添加
    public function add() {
        if ($this->isPost()) {
            $auto = M('WeixinAutomatic');
            $fcusx = $auto->where(array('title' => trim($_POST[title]), 'web_id' => $this->to[web_id]))->find();
            if (!is_array($fcusx)) {
                $data['web_id'] = $this->to[web_id];
                $data['title'] = trim($_POST[title]);
                $data['content'] = trim($_POST[content]);
                $is = $auto->add($data);

                if ($is) {
                    $this->success("添加成功!", U('Automatic/index'));
                } else {
                    $this->error("系统错误");
                }
            } else {
                $this->error("标题已经存在");
            }
        } else {
            $this->assign("title", '添加');
            $this->display();
        }
    }

    //修改
    public function save($id) {
        $auto = M('WeixinAutomatic');
        $automatic = $auto->where(array('id' => $id))->find();
        if ($auto) {
            if ($this->isPost()) {
                $fcusx = $auto->where(array('title' => trim($_POST[title]), 'id' => array('neq', $id), 'web_id' => $this->to[web_id]))->find();
                if (!is_array($fcusx)) {
                    $data['title'] = trim($_POST[title]);
                    $data['content'] = trim($_POST[content]);
                    $is = $auto->where(array('id' => $id))->save($data);
                    if ($is) {
                        $this->success("修改成功!", U('Automatic/index'));
                    } else {
                        $this->error("系统错误");
                    }
                } else {
                    $this->error("标题已经存在");
                }
            } else {
                $this->assign("automatic", $automatic);
                $this->assign("title", '修改');
                $this->display('add');
            }
        } else {
            $this->error("没有找到对应数据!!");
        }
    }

    //删除
    public function delete($id) {
        $fcus = M('WeixinAutomatic')->where(array('id' => $id))->delete();
        if ($fcus) {
            echo 1;
        } else {
            echo 2;
        }
    }

}
