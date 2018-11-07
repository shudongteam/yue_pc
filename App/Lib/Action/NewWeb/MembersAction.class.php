<?php

// 用户查询
class MembersAction extends GlobalAction {

    public function index() {
        $this->display();
    }

    //用户统计
    public function members() {
        $this->display();
    }

    //用户查询
    public function search() {
        // $user_id = I('user_id', 0, 'intval');
        $user_id = I('user_id');
        $model = D('NUserView');
        if (session('web_id')) {
            $where['NUser.web_id'] = session('web_id');
        }
        $where['_string'] = ' (NUser.user_id = "'.$user_id.'")  OR (NUser.user_name = "'.$user_id.'") OR (NUserInfo.pen_name = "'.$user_id.'")';
        $data = $model->where($where)->find();
        // echo $model->getLastSql();
        $this->assign('user_id', $user_id);
        $this->assign('data', $data);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>'; 
        $this->title = '用户查询';
        $this->display();
    }
}
