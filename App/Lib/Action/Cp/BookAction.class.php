<?php

//查看作品
class BookAction extends GlobalAction {

    //作品查看
    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        //post查询
        if ($this->isPost()) {
            if (!empty($_POST[keyword])) {
                $where['book_name'] = array('like', "%$_POST[keyword]%");
            }
            if (!empty($_POST[type_id])) {
                $where['type_id'] = $_POST[type_id];
            }
        }
        //get查询
        if (!empty($_GET[keyword])) {
            $_GET[keyword] = urldecode($_GET[keyword]);
            $where['book_name'] = array('like', "%$_GET[keyword]%");
        }
        if (!empty($_GET[type_id])) {
            $where['type_id'] = $_GET[type_id];
        }
        $where['cp_id'] = $this->to[cp_id];
        $where['fu_web'] = 0;
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $book = $bbb->where($where)->field('book_id,book_name,author_name,level,state,vip,money,is_show,audit,words,chapter,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('book', $book);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 本 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //添加作品
    public function add() {
        if ($this->isPOst()) {
            $data['web_id'] = $this->to['web_id'];
            $data['cp_id'] = $this->to['cp_id'];
            $data['edit_id'] = 0;
            $data['book_name'] = $_POST[book_name];
            $data['author_name'] = $_POST[author_name];
            $data['type_id'] = $_POST[type_id];
            $data['gender'] = $_POST[gender];
            $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处
            $data['signing'] = $_POST[signing];
            $data['state'] = $_POST[state];
            $data['vip'] = $_POST[vip];
            $data['money'] = $_POST[money];
            $data['is_show'] = 0;
            $data['audit'] = 1; //未审   
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $data['keywords'] = str_replace($qian, $hou, $_POST['keywords']);
            $data['book_brief'] = $_POST[book_brief];
            $data['time'] = date('Y-m-d H:i:s', time());
            $data['new_time'] = $data['time'];
            $is = A('Booklei')->bookadd($data);
            if ($is == 1) {
                $this->success("添加成功");
            } else {
                $this->error("添加失败");
            }
        } else {
            //作品类型
            $type = BooktypeAction::booktype();
            $this->assign('type', $type);
            $this->display();
        }
    }

    //更新书籍
    public function save($book) {
        if ($this->isPost()) {
            $data['book_name'] = $_POST[book_name];
            $data['author_name'] = $_POST[author_name];
            $data['type_id'] = $_POST[type_id];
            $data['gender'] = $_POST[gender];
            if (is_uploaded_file($_FILES['upload_img']['tmp_name'])) {
                $data['upload_img'] = A('Gongju')->uploaddeal('./Upload/Book/da/'); //上传处
            }
            // $data['vip'] = $_POST[vip];
            // $data['money'] = $_POST[money];
            //处理关键
            $qian = array("，", "；", "、", "，", "，", " ", "　", "：", " ");
            $hou = array("|", "|", "|", "|", "|", "|", "|", "|", "|");
            $data['keywords'] = str_replace($qian, $hou, $_POST['keywords']);
            $data['book_brief'] = $_POST[book_brief];
            $is = M('Book')->where(array('book_id' => $book))->save($data);
            //更新书单
            $datas['state'] = $_POST[state];
            // $datas['signing'] = $_POST[signing];
            M('Book')->where(array('fu_book' => $book))->save($datas);
            if ($is) {
                $this->success("修改成功");
            } else {
                $this->error("ok");
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

    //删除中
    public function delete($book) {
        echo "开发中";
    }

}
