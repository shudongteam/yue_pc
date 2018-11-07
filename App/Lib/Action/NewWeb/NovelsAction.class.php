<?php

//小说管理
class NovelsAction extends GlobalAction {

    //列表
    public function index() {
        $model = D('NBooksView');
        $order = I('order_by');
        $gender = I('gender');
        $is_new = I('is_new');
        $is_all = I('is_all');
        $q = I('q');
        $order_by = 'desc';
        if (!$order) {
            $order = 'NBookPaidan.nums '.$order_by;
        } else {
            if (strpos($order, "asc")) {
                $order_by = 'asc';
            }
            $order = 'NBookPaidan.nums '.$order_by;
        }
        if($gender){
            if ($gender == 1) {
                $where['gender'] = 1;
            } else {
                $where['gender'] = 2;
            }
        }
        if ($is_new) {
            $order.=",book_id DESC";
            $where['time'] = array('gt', date('Y-m-d', strtotime('-10 day')));
        }
        if (!$gender && !$is_new) {
            $is_all = 1;
        }
        if ($q) {
            $q = str_replace(array("%", "_", "&"), array("\%", "\_", "\&"), $q);
            $where['book_name'] = array('like', '%'.$q.'%');
        }
        $where['fu_book'] = array('exp', " = Book.book_id");
        $where['is_show'] = 1;
        //统计条数开始
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage'); 
        $Page = new MyPage($count);             
        $data = $model->where($where)->field()->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());   
        $this->assign('gender', $gender);   
        $this->assign('is_new', $is_new);   
        $this->assign('is_all', $is_all);   
        $this->assign('order_by', $order_by);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';          
        $this->title = "小说管理";
        $this->display();
    }

    //章节列表
    public function view($id) {
        $where['book_id'] = $id;
        $book = M('book')->where($where)->field('book_id, book_name, fu_book, book_brief, is_show, words, upload_img')->find();
        if (!$book) {
            $this->error("书籍不存在!");
        }
        $where2['fu_book'] = $book['fu_book'];
        $where2['caudit'] = array('neq', 2);

        $data = M('BookContent')->where($where2)->field('content_id, fu_book, num, title, the_price, caudit, time')->limit(20)->select();
        $this->assign('data', $data);
        $this->assign('book', $book);
        $this->assign('flag', $this->auth->check('show_button_link', session('agent_id')));        
        $this->title = "章节列表";
        $this->display();
    }

    //章节内容
    public function api_get_chapter($id) {
        $data = M('BookContents')->field('content_id id, content paragraphs')->find($id);
        $model = M();
        $data = $model->query("SELECT A.title, A.content_id id, B.content paragraphs FROM hezuo_book_content A LEFT JOIN hezuo_book_contents B ON A.content_id = B.content_id WHERE A.content_id={$id}");
        if (isset($data[0])) {
            $data[0]['paragraphs'] = explode("\n", str_replace(" ", "", $data[0]['paragraphs']));
        }
        A('Gongju')->echo_json($data[0]);
    }
   
    //生成文案
    public function content($aid, $fu_book, $num) {
        // $where2['caudit'] = array('neq', 2);
        // $where2['fu_book'] = $fu_book;
        // $where2['num'] = array('lte', $num);
        // $data = M('BookContent')->field('fu_book,num')->where($where);
        $this->assign('article_id', $aid);
        $this->assign('novel_id', $fu_book);
        $this->assign('num', $num);
        $this->assign('next_num', $num+1);
        $this->assign('flag', $this->auth->check('show_button_link', session('agent_id')));        
        $this->display();
    }


    public function api_get_body_templates(){
        $data = M('NTmpBody')->select();
        A('Gongju')->echo_json($data); 
    }

    public function api_get_footer_templates(){
        $data = M('NTmpFooter')->select();
        A('Gongju')->echo_json($data);     
    }

    public function api_get_titles(){
        // $data = array(
        //     'id'=> "153", 
        //     'type'=> "1", 
        //     'category_id'=> "15", 
        //     'title'=> "我的女人,我都不舍得碰,你敢动她一下试试!", 
        //     'created_at'=> "1492175197"
        // );
        $data = M('NTmpTitle')->select();
        A('Gongju')->echo_json($data);
    }

    public function api_get_covers(){
    //     $data = array(
    // "id"=> "7097",
    // "type"=> "1",
    // "category_id"=> "15",
    // "cover_url"=> "http=>//wx4.sinaimg.cn/mw690/006WGNdugy1fjcb7jev0tj30hs0d3mxf.jpg",
    // "created_at"=> "1504950169"
    //     );
        $data = M('NTmpCover')->select();
        A('Gongju')->echo_json($data);
    }

    public function api_get_preview_articles(){
        $num = I('num', '0', 'intval');
        $id = I('current_novel_id', '0', 'intval');
  
        
        $model = M();
        $sql= "SELECT A.title, A.content_id id, B.content paragraphs FROM hezuo_book_content A LEFT JOIN hezuo_book_contents B ON A.content_id = B.content_id WHERE A.fu_book={$id} AND A.num <= {$num} AND caudit <> 2";
        $data = $model->query($sql);
        if (!empty($data)) {
            $i=1;
            foreach ($data as $key => $value) {
                $data[$key]['idx'] = 'x'.$i;
                $data[$key]['paragraphs'] = explode("\n", str_replace(" ", "", $data[$key]['paragraphs']));
                $i++;
            }
        }
        A('Gongju')->echo_json($data);
    }


    // public function api_get_short_article_info(){
    //     // {"id":"46103","title":"\u7b2c\u516d\u7ae0 \u7ed9\u5979\u673a\u4f1a","novel":{"id":"68","title":"\u6076\u9b54\u7d22\u7231","avatar":"https:\/\/ommdq027l.qnssl.com\/novels\/14923344739248.jpg?imageView2\/0\/w\/300\/q\/75"}}
    // }

    public function api_save(){
        $novels_id = I('novels_id', 0, 'intval');
        $description = I('description', 0, 'htmlentities');
        $num = I('num', 0, 'intval');
        $referrer_type = I('referrer_type', 0, 'intval');
        if ($referrer_type == 1) {
            $focus = 13;
        } else {
            $focus = 0;
        }
        $web_id = session('web_id');
        $agent_id = session('agent_id');
        $web_url = $this->get_web_url();
        $link = "http://{$web_url}/chaptertwo/{$novels_id}/{$num}";
        $pen_name = M('NAgent')->where(array('agent_id' => $agent_id))->getField('pen_name');
        $data = array(
            'agent_id'      => $agent_id,
            'focus'         => $focus,
            'agent_name'    => $pen_name,
            'name'          => $description,
            'link'          => $link,
            'time'          => date('Y-m-d H:i:s', time())
        );
        $channel = M('NAgentChannel')->add($data);

        //派单指数+1
        $model = M('NBookPaidan');
        $data = array(
            'nums' => array('exp','nums+1')
        );
        $res = $model->where('book_id='.$novels_id)->save($data);
        if (!$res) {
            $data = array(
                'book_id' => $novels_id,
                'nums' => 1
            );
            $model->add($data);
        }

        //http://w.ymzww.cn/chapter/99605/4.html?agent=1&channel=1957&focus=13
        $link.="?channel={$channel}&agent=".$agent_id."&focus={$focus}";
        $rearr = array(
            "id"=>$channel,
            "url"=>$link
        );
        A('Gongju')->echo_json($rearr);
    }

    //输出图片
    public function proxy(){
        $url = I('url');
        echo file_get_contents($url);
    }
    
    function source(){
        exit;
        $json = '';
        $arr = json_decode($json, true);
        $model = M("NTmpFooter"); 
        foreach ($arr as $key => $value) {
            $data = array(
                'preview_img' => $value['preview_img'],
                'template' => $value['template'],
            );
           $model->add($data);
        }
    }
}
