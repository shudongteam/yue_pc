<?php

//渠道管理-生成推广链接
class LinksAction extends GlobalAction {

    
    public function index() {
        $q = I('q');
        $where = $this->where(array(), '', 1);
        if ($q) {
            $q = str_replace(array("%", "_", "&"), array("\%", "\_", "\&"), $q);
            $where['name'] = array('like', '%'.$q.'%');
        }     
        $model = M('NAgentChannel');
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage'); 
        $Page = new MyPage($count);             
        $data = $model->where($where)->field()->order('channel_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $book = M('Book');
        // $book_content = M('BookContent');
        if ($data) {
            foreach ($data as $key => $value) {
                $book_name = '';
                $num_id = '';
                $res = preg_match('/(\d+)\/(\d+)/', $value['link'], $match);
                $book_id = isset($match[1]) ? $match[1] : 0;
                $num_id = isset($match[2]) ? $match[2] : 0;
                if ($book_id) {
                    $book_name = $book->where('book_id='.$book_id)->getField('book_name');
                }
                $data[$key]['book_name'] = $book_name;
                $data[$key]['book_id'] = $book_id;
                $data[$key]['num_id'] = $num_id;
                $data[$key]['link'] = $value['link']."?channel={$value[channel_id]}&agent={$value[agent_id]}&focus={$value[focus]}";
            }
        }
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';        
        $this->title = '渠道管理';
        $this->display();
    }

    public function del($id) {
        $model = M('NAgentChannel');
        $where['channel_id'] = $id;
        $where['agent_id'] = session('agent_id');
        $is = $model->where($where)->delete();
        if ($is) {
            $this->success("删除成功");
        } else {
            $this->error("删除失败");
        }

    }
}
