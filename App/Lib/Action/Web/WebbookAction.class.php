<?php

//作品管理
class WebbookAction extends GlobalAction {

    //作品查看
    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        $where['web_id'] = $this->to[web_id];
        $keyword = isset($_REQUEST[keyword]) ? urldecode(trim($_REQUEST[keyword])) : '';
        if ($keyword) {
            $where['book_name'] = array('like', '%'.$keyword.'%');
            $_GET['keyword'] = $keyword;
        }

        if ($_REQUEST[type_id]) {
            $where['type_id'] = $_REQUEST[type_id];
            $_GET['type_id'] = $_REQUEST[type_id];
        }
        if ($_REQUEST[gender]) {
            $where['gender'] = $_REQUEST[gender];
            $_GET['gender'] = $_REQUEST[gender];            
        }
        if ($_REQUEST[level]) {
            $where['level'] = $_REQUEST[level];
            $_GET['level'] = $_REQUEST[level];   
        }              

        if (isset($_REQUEST[is_show]) && $_REQUEST[is_show]!="") {
            $where['is_show'] = $_REQUEST[is_show];
            $_GET['is_show'] = $_REQUEST[is_show];   
        } else {
            // $where['is_show'] = 1;
            // $_GET['is_show'] = 1;   
        }            

        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $webbook = $bbb->where($where)->field('book_id,fu_book,book_name,author_name,is_show,gender,state,vip,money,words,level,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('webbook', $webbook);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);        
        $this->assign('keyword', $keyword);
        $this->assign('type_id', $_REQUEST[type_id]);
        $this->assign('gender', $_REQUEST[gender]);    
        $this->assign('level', $_REQUEST[level]);    
        $this->assign('is_show', $_REQUEST[is_show]); 
        $this->display();
    }

    //修改
    public function save($book) {
        if ($this->isPost()) {
            $data['book_name'] = $_POST[book_name];
            $data['author_name'] = $_POST[author_name];
            $data['type_id'] = $_POST[type_id];
            $data['gender'] = $_POST[gender];
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处
            }
            $data['is_show'] = $_POST[is_show];
            $data['vip'] = $_POST[vip];
            $data['money'] = $_POST[money];
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $data['keywords'] = str_replace($qian, $hou, $_POST['keywords']);
            $data['book_brief'] = $_POST[book_brief];
            $is = M('Book')->where(array('book_id' => $book))->save($data);
            if ($is == 1) {
                $this->success("更新成功");
            } else {
                $this->error("系统错误");
            }
        } else {
            //作品类型
            $type = BooktypeAction::booktype();
            $this->assign('type', $type);
            $isbook = M('Book')->where(array('book_id' => $book))->find();
            $this->assign('isbook', $isbook);
            $this->display();
        }
    }

    //删除书单信息
    public function delete($book) {
        echo "开发中";
    }

    //销售情况
    public function sales($book) {
        //统计信息
        $sales = D('TongjiView')->where(array('book_id' => $book))->find();
        $this->assign('sales', $sales);
        $this->display();
    }

    public function qrcode($book, $name){
            $web = M('Web')->where(array('web_id' => $this->to[web_id]))->field('web_url')->find();
            $data['agent_name'] = $this->to['pen_name']."-".$name."-".date('YmdHis');
            $data['name'] = '无';
            $data['agent_id'] = $this->to['agent_id'];
            $data['type_id'] = 0;
            $data['focus'] = 0;
            $data['num'] = 0;
            $data['money'] = 0;
            $link = "http://" . $web[web_url] . "/chapter/" . $book . "/" . 1;
            $data['link'] = $link;
            $data['time'] = date('Y-m-d H:i:s', time());
            $is = M('AgentChannel')->add($data);
            $link.= '.html?agent_id='.$this->to['agent_id']."&channel=".$is;
            if ($is) {
                echo A('Gongju')->get_short_url($link);
                // echo $link;
            } else {
                echo 2;
            }
    }
}
