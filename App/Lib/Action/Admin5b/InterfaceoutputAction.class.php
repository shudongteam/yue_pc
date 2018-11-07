<?php

//输出接口
class InterfaceoutputAction extends GlobalAction {

    //作品查看
    public function index() {
        //作品类型
        $type = BooktypeAction::booktype();
        $this->assign('type', $type);
        $map['web_id'] = array('eq', 4);
        $map['type'] = 1;
        $result = M("Cp")->where($map)->field('pen_name,cp_id')->select();
        //post查询
        if ($this->isPost()) {
            if (!empty($_POST[keyword])) {
                $_POST[keyword] = trim($_POST[keyword]);
                if ($_POST[search_type] == 1) {
                    $where['book_name'] = array('like', "%$_POST[keyword]%");
                } elseif($_POST[search_type] == 2) {
                    $where['author_name'] = array('like', "%$_POST[keyword]%");
                }else{
                    $where['cp_name'] = array('like', "%$_POST[keyword]%");
                }
            }
            if (!empty($_POST[type_id])) {
                $where['type_id'] = $_POST[type_id];
            }

            if (!empty($_POST[state])) {
                $where['state'] = $_POST[state];
            }
        }
        //get提交
        if ($this->isGet()) {
            if (!empty($_GET[keyword])) {
                $_GET[keyword] = trim(urldecode($_GET[keyword]));
                if ($_GET[search_type] == 1) {
                    $where['book_name'] = array('like', "%$_GET[keyword]%");
                } elseif($_GET[search_type] == 2) {
                    $where['author_name'] = array('like', "%$_GET[keyword]%");
                }else{
                    $where['cp_name'] = array('like', "%$_GET[keyword]%");
                }
            }
            if (!empty($_GET[type_id])) {
                $where['type_id'] = $_GET[type_id];
            }

            if (!empty($_GET[state])) {
                $where['state'] = $_GET[state];
            }
        }
        //数据处理
        $bbb = M('Book');
        import('ORG.Util.Page'); // 导入分页类       
        //统计条数开始
        // $where['web_id'] = $this->to[web_id];
        $where['fu_web'] = 0; //不是授权站书籍   
        // $where['author_id'] = array('neq', 0); //不等于0就是有作者真实的书
        $where['is_show'] = array('eq', 1); //不等于0就是有作者真实的书
        $count = $bbb->where($where)->count(); // 查询满足要求的总记录数
        $nums = isset($_GET['nums']) ? $_GET['nums'] : 15;//默认显示15条记录
        $Page = new Page($count, $nums); // 实例化分页类 传入总记录数和每页显示的记录数  
    
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出             
        $book = $bbb->where($where)->field('book_id,book_name,author_name,cp_name,level,signing,state,vip,money,is_show,audit,words,time')->order('time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select(); 
        $this->assign('topweb', $result);
        $this->assign('count', $count);
        $this->assign('nums', $nums);
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('book', $book);
        $this->display();
    }
//输出接口授权书籍
    public function books() {
        if ($this->isPost()) {
            if ($_POST[checkbox]) {
                //准备站点
                $isok = M('Cp')->where(array('cp_id' => $_POST[cp_id]))->find();
                //print_r($isok);
                if (!is_array($isok)) {
                    $this->error("没有该CP");
                    exit();
                }
                //准备模型
                $book = M('book');
                $cpbook = M('CpBook');
                $value = $_POST[checkbox];
                @$zhi = implode(',', $value);
                $arr = explode(',', $zhi);
                foreach ($arr as $key => $value) {
                    //查询是否已经授权了
                    $isbook = $cpbook->where(array('cp_id' => $_POST[cp_id], 'book_id' => $value))->field('book_id')->find();
                    if (is_array($isbook)) {
                        $this->error("书号$value 已经授权了");
                    } else {
                        $bookinfo = $book->where(array('fu_book'=>$value))->field('book_name,chapter,time,new_time')->find();
                        $data['cp_id'] = $_POST[cp_id];
                        $data['type'] = 1;
                        $data['fubook_name'] = $bookinfo[book_name];
                        $data['fubook_id'] = $value;
                        $data['fu_num'] = $bookinfo[chapter];
                        $data['fu_time'] = $bookinfo[time];
                        $data['book_id'] = $value;
                        $data['num'] = $bookinfo[chapter];
                        $data['book_time'] = $bookinfo[new_time];
                        $data['time'] = date('Y-m-d H:i:s', time());
                        $cpbook->add($data);
                    }
                }
                $this->success("授权结束");
            } else {
                $this->error("请选择授权书籍！");
            }
        }
    }
}
