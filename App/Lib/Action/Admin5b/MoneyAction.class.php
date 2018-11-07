<?php

//处理钱
class MoneyAction extends GlobalAction {

    //打款记录
    public function index() {
        $money = M('SystemMoney');
        if ($this->isPost()) {
            $where['pen_name'] = array('like', "%$_POST[keyword]%");
        }
        import('ORG.Util.Page'); // 导入分页类       
        $count = $money->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $money->where($where)->order('time DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('content', $content);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //代理站长打款
    public function agent() {
        $agentsett = M('AgentSettlement');
        if ($this->isPost()) {
            $where['pen_name'] = array('like', "%$_POST[keyword]%");
        }
        $where['state'] = 1;
        import('ORG.Util.Page'); // 导入分页类       
        $count = $agentsett->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $agentsett->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('content', $content);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //站长代理数据添加
    public function agentadd($id) {
        $agentsett = M('AgentSettlement');
        $where['id'] = $id;
        $where['state'] = 1;
        $isfafang = $agentsett->where($where)->find();
        if (is_array($isfafang)) {
            $saves['state'] = 2;
            $agentsett->where($where)->save($saves);
            $data['pen_name'] = $isfafang[pen_name];
            $data['money'] = $isfafang[money];
            $data['type'] = 2;
            $data['time'] = date('Y-m-d H:i:s', time());
            $isok = M('SystemMoney')->add($data);
            if ($isok) {
                $this->success("处理成功！");
            } else {
                $this->error("系统错误");
            }
        } else {
            $this->error("该记录已被处理过！");
        }
    }

    //cp打款
    public function cp() {
        $cpsett = M('CpSettlement');
        if ($this->isPost()) {
            $where['pen_name'] = array('like', "%$_POST[keyword]%");
        }
        $where['state'] = 1;
        import('ORG.Util.Page'); // 导入分页类       
        $count = $cpsett->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $cpsett->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $arr = array();
        foreach ($content as $key => $value) {
           $value['total'] = $value['money'];
           $value['give'] = round(($value['total'] -$value['cost'])/2,2);
           $value['money55'] = $value['give'];
           $arr[] = $value;
        }
        $this->assign('content', $arr);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //cp数据添加
    public function cpadd($id) {
        $cpsett = M('CpSettlement');
        $where['id'] = $id;
        $where['state'] = 1;
        $isfafang = $cpsett->where($where)->find();
        if (is_array($isfafang)) {
            $saves['state'] = 2;
            $cpsett->where($where)->save($saves);
            $data['pen_name'] = $isfafang[pen_name];
            $data['money'] = $isfafang[money];
            $data['type'] = 1;
            $data['time'] = date('Y-m-d H:i:s', time());

            $isok = M('SystemMoney')->add($data);
            if ($isok) {
                $this->success("处理成功！");
            } else {
                $this->error("系统错误");
            }
        } else {
            $this->error("该记录已被处理过！");
        }
    }

//作者打款
    public function author() {
        $authorsett = M('AuthorSettlement');
        $where['state'] = 1;
        if ($this->isPost()) {
            $where['pen_name'] = array('like', "%$_POST[keyword]%");
        }
        import('ORG.Util.Page'); // 导入分页类       
        $count = $authorsett->where($where)->field('id')->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        //输出内容
        $content = $authorsett->where($where)->order('id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('content', $content);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //作者数据添加
    public function authoradd($id) {
        $authorsett = M('AuthorSettlement');
        $where['id'] = $id;
        $where['state'] = 1;
        $isfafang = $authorsett->where($where)->find();
        if (is_array($isfafang)) {
            $saves['state'] = 2;
            $authorsett->where($where)->save($saves);
            $data['pen_name'] = $isfafang[pen_name];
            $data['money'] = $isfafang[money];
            $data['type'] = 3;
            $data['time'] = date('Y-m-d H:i:s', time());

            $isok = M('SystemMoney')->add($data);
            if ($isok) {
                $this->success("处理成功！");
            } else {
                $this->error("系统错误");
            }
        } else {
            $this->error("该记录已被处理过！");
        }
    }

}
