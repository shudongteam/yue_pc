<?php

//公告显示
class AnnouncementAction extends GlobalAction {

    public function index() {
        $ann = M('SystemAnnouncement');
        // $where['type'] = 1;
        import('ORG.Util.Page'); // 导入分页类   
        $count = $ann->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数 
        $con = $ann->where($where)->field('id,title,content,name,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('con', $con);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
    public function chakan($id){
        $ann = M('SystemAnnouncement')->where(array('id'=>$id))->find();
        $this->assign('ann',$ann);
        $this->display();
        
    }
}
