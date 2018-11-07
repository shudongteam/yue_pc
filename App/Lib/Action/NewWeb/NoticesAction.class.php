<?php

//通知公告
class NoticesAction extends GlobalAction {
 
    //公告列表
    public function index() {
        $model = M('NNotice');
        import('ORG.Util.MyPage');
        $count = $model->count();   
        $Page = new MyPage($count);          
        $data = $model->field('id,title,content,name,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //添加按钮判断
        $this->assign('flag', $this->auth->check('show_button_notice', session('agent_id')));
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        $this->title = '通知公告';        
        $this->display();
    }

    //公告列表数据
    public function api_get_notice($id) {
        $data = M('NNotice')->find($id);
        A('Gongju')->echo_json($data);
    }

    //公告编辑
    public function edit() {
        $id = I('id', 0, 'intval');
        if ($id) {
            $arr = (array)json_decode($_REQUEST[arr]);

        }
        $this->title = '公告编辑';
        $this->assign('id', $id);        
        $this->display();
    }

    //通知数据接口
    public function api_get($id) {
        $model = M('NNotice');
        $data = $model->find($id);   
        A('Gongju')->echo_json($data);
    }

    //通知数据接口
    public function api_save() {
        $model = M('NNotice');
        $postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '';
        $info = false;
        if ($postStr) {
            $post = json_decode($postStr, true);
            $id =  $post['id'];
            $arr['time'] = date('Y-m-d H:i:s', time());
            $arr['title'] = $post['title'];
            $arr['content'] = $post['content'];
            if ($id) {
               $res = $model->where("id=".$id)->save($arr); 
            } else {
                $res = $model->add($arr);
            }
            if ($res) {
                $info = true;
            }
        }
        echo $info;
    }

    public function delete() {
        $id = I('id', 0, 'intval');
        $res = M('NNotice')->where('id='.$id)->delete();
        if ($res) {
            $this->success('删除成功！');
        } else {
            $this->success('系统错误，删除失败！');
        }
    }
}
