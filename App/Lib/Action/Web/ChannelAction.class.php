<?php

//渠道
class ChannelAction extends GlobalAction {

    //渠道管理
    public function index() {
        $channel = D('AgentchannelView');
        $keyword = isset($_REQUEST[keyword]) ? trim(urldecode($_REQUEST[keyword])) : '';
        if ($keyword) {
            if ($_REQUEST[search_type] == 1) {
                $where['name'] = array('like', '%'.$keyword.'%');
            } else {
                $where['agent_name'] = array('like', '%'.$keyword.'%');
            }
            $_GET['search_type']  = $_REQUEST[search_type];
            $_GET['keyword'] = $keyword;
        }

        $where['agent_id'] = $this->to[agent_id];
        import('ORG.Util.Page'); // 导入分页类       
        $count = $channel->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数          
        $mychannel = $channel->where($where)->order('channel_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        for ($i = 0; $i < count($mychannel); $i++) {
            $mychannel[$i][type_name] = $this->type_name($mychannel[$i][type_id]);
            // $mychannel[$i][huibao] = ($mychannel[$i][pay_total] - $mychannel[$i][money]) / $mychannel[$i][money] * 100;
            $mychannel[$i][zhuanhua] = round(($mychannel[$i][money_num] / $mychannel[$i][click_total] ),4)*1000;
        }
        $this->assign('mychannel', $mychannel);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
        $this->assign('search_type', $_REQUEST[search_type]);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    private function type_name($type_id) {
        if ($type_id == 0) {
            return "无分类";
        } else {
            $type = M('AgentChannel_type')->field('type_name')->where(array('type_id' => $type_id))->find();
            return $type[type_name];
        }
    }

    //添加
    public function add($book) {
        if ($this->isPost()) {
            //判断渠道名称是否重复
            $res = M('AgentChannel')->where(array('agent_name' => $_POST[agent_name]))->find();
            if ($res) {
                $this->error("渠道账号重复!");
            }

            $web = M('Web')->where(array('web_id' => $this->to[web_id]))->field('web_url')->find();
            $data['agent_id'] = $this->to['agent_id'];
            $data['type_id'] = $_POST[type_id];
            $data['focus'] = $_POST[focus];
            $data['agent_name'] = $_POST[agent_name];
            $data['name'] = $_POST[name];
            $data['num'] = $_POST[nums];
            $data['money'] = $_POST[money];
            $link = "http://" . $web[web_url] . "/chapter/" . $book . "/" . ($_POST[num] + 1);
            $data['link'] = $link;
            $data['time'] = date('Y-m-d H:i:s', time());
            $is = M('AgentChannel')->add($data);
            if ($is) {
                $this->success("添加成功", U('Channel/index'));
            } else {
                $this->error("系统错误");
            }
        } else {
            $webbbok = M('Book')->where(array('book_id' => $book))->field('fu_book')->find();
            if ($webbbok) {
                $content = M('BookContent')->where(array('fu_book' => $webbbok[fu_book], 'the_price' => 0))->field('num,title')->limit(20)->order('num asc')->select();
                $this->assign('webbbok', $webbbok);
                $this->assign('content', $content);
                //渠道分类
                $mytype = M('AgentChannelType')->where(array('agent_id' => $this->to[agent_id]))->select();
                $this->assign('mytype', $mytype);
            } else {
                $this->error("系统出错");
            }
            $this->display();
        }
    }

    //生成方法
    public function generate($bookid, $num) {
        $where['fu_book'] = $bookid;
        $where['num'] = array('elt', $num);
        $content = M('BookContent')->where($where)->field('content_id,num,title')->limit(20)->order('num asc')->select();
        $conn = M('BookContents');
        for ($i = 0; $i < count($content); $i++) {
            $mycon = $conn->where(array('content_id' => $content[$i][content_id]))->find();
            $content[$i][content] = str_replace("\n", "</p><p>", str_replace(" ", "", $mycon[content]));
        }

        $this->assign('content', $content);
        $this->display();
    }

//============================================================
    //渠道分类
    public function type() {
        $mytype = M('AgentChannelType')->where(array('agent_id' => $this->to[agent_id]))->select();
        $this->assign('mytype', $mytype);
        $this->display();
    }

    //添加渠道分类
    public function typeadd() {
        if ($this->isPost()) {
            $data['agent_id'] = $this->to['agent_id'];
            $data['type_name'] = $_POST[type_name];
            $mytype = M('AgentChannelType')->add($data);
            if ($mytype) {
                $this->success("添加成功", U('Channel/type'));
            } else {
                $this->error('系统错误');
            }
        } else {
            $this->assign('title', "添加");
            $this->display();
        }
    }

    //渠道修改
    public function typesave($pid) {
        if ($this->isPost()) {
            $mytype = M('AgentChannelType')->where(array('type_id' => $pid))->save(array('type_name' => $_POST[type_name]));
            if ($mytype) {
                $this->success("修改成功", U('Channel/type'));
            } else {
                $this->error('系统错误');
            }
        } else {
            $this->assign('title', "修改");
            $mytype = M('AgentChannelType')->where(array('type_id' => $pid))->find();
            $this->assign('mytype', $mytype);
            $this->display('typeadd');
        }
    }

    //渠道删除
    public function deleteanget($channel_id) {
        $u = M('AgentChannel');
        $num = $u->where(array('channel_id' => $channel_id))->delete();
        if ($num) {
            echo 1;
        } else {
            $this->error("删除失败");
        }
    }
        //修改
    public function edit($channel_id) {
        if ($this->isPost()) {
            $data['type_id'] = $_POST[type_id];
            $data['name'] = $_POST[name];
            $data['agent_name'] = $_POST[agent_name];
            // $data['link'] = $link;
            // $data['time'] = date('Y-m-d H:i:s', time());
            $is = M('AgentChannel')->where(array('channel_id' => $channel_id))->save($data);
            if ($is) {
                $this->success("修改成功", U('Channel/index'));
            } else {
                $this->error("系统错误");
            }
        } else {
            $channel = M('AgentChannel')->field('channel_id, agent_name,name, type_id')->find($channel_id);
            if ($channel) {
                $this->assign('channel', $channel);
                //渠道分类
                $mytype = M('AgentChannelType')->where(array('agent_id' => $this->to[agent_id]))->select();
                $this->assign('mytype', $mytype);
            } else {
                $this->error("系统出错");
            }
            $this->display();
        }
    }

    //显示渠道分类         
    public function xianshi($pids) {
        $channel = M('AgentChannel');
        $where['type_id'] = $pids;
        $mychannel = $channel->where($where)->order('time desc')->select();
        for ($i = 0; $i < count($mychannel); $i++) {
            $mychannel[$i][huibao] = ($mychannel[$i][pay_total] - $mychannel[$i][money]) / $mychannel[$i][money] * 100;
        }
        $this->assign('mychannel', $mychannel);
        $this->display();
    }

    //去除渠道分类
    public function delete($id) {
        $channel = M('AgentChannel')->where(array('channel_id' => $id))->save(array('type_id' => 0));
        if ($channel) {
            echo 1;
        } else {
            echo 2;
        }
    }

    //渠道统计
    public function tongji($pid) {
        $where['type_id'] = $pid;
        $where['agent_id'] = $this->to['agent_id'];
        $result = M('AgentChannel')->where($where)->field('time,pay_total,reg_total,click_total,money,name')->order('pay_total')->select();

        $click = array();
        $reg = array();
        $total = array();
        foreach ($result as $value) {
            $name[] = $value['name'];
            $reg[] = (int) $value['reg_total'];
            $click[] = (int) $value['click_total'];
            $total[] = (int) $value['pay_total'];
        }

        $data = array(
            array('name' => '注册数', 'val' => json_encode($reg)),
            array('name' => '点击数', 'val' => json_encode($click)),
            array('name' => '总充值', 'val' => json_encode($total)),
        );
        $this->assign('name', $name);
        $this->assign('data', $data);
        $channel = M("AgentChannelType")->where($where)->find();
        $this->assign('channel', $channel);
        $this->display();
    }

    //批量搜索渠道名称并导出xls文件    
    public function batchSearch()
    {
        if ($this->isPost()) {
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 3145728; // 设置附件上传大小
            $upload->allowExts = array('xls'); // 设置附件上传类型
            $upload->savePath = './Upload/search/new-work'; // 设置附件上传目录
            
            if (!$upload->upload()) {// 上传错误提示错误信息
                $this->error($upload->getErrorMsg());
            } else {// 上传成功 获取上传文件信息                
                $info = $upload->getUploadFileInfo();
                $this->readXls($info[0]['savepath'] . $info[0]['savename']);
                
            }
        } else {
            $this->index();
        }
    }
    
    //读取上传文件内容
    protected function readXls($file)
    {
        Vendor('Exc.oleread'); //加载类
        Vendor('Exc.reader'); //加载类
        $data = new Spreadsheet_Excel_Reader();
        $data->setOutputEncoding('CP936');
        $data->read($file);
        error_reporting(E_ALL ^ E_NOTICE);
        
        $filename = '阅明-渠道用户(' . date('Y.m.d') . ')';
        
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");  
        header("Content-Disposition:attachment;filename=".$filename.".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo iconv("UTF-8", "GB2312", ("账户" . "\t" . '渠道名称' . "\t" . '推广费(RMB)'  . "\t"  . '总充值(RMB)'  . "\t" . '推广数量(注册人数)'  . "\t" . '点击量(点击人数)'  . "\t" . '充值数量(笔数)'  . "\t" . '充值/点击(千分比)')) . "\n";
        
        
        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
            $agent_name = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][1], 'UTF-8', 'GBK'));//账户
            //$penname = trim(mb_convert_encoding($data->sheets[0]['cells'][$i][2], 'UTF-8', 'GBK'));//渠道名称            
            
            if($agent_name)
            {
                $value = array();
                $agentChannel = M('AgentChannel');
                $result = $agentChannel->where(array('agent_name' => $agent_name))->field('name,money,pay_total,reg_total,click_total,money_num')->select();
                // echo $agentChannel->getLastSql();
                $count = count($result);
                $arr = $result[0] ? $result[0] : array();
                $proportion = 0;
                if($arr && ($count == 1))
                {
                    if($arr[money_num] ==0 || $arr[click_total]==0){
                        $proportion = 0;
                    }else{    
                        $proportion = round($arr[money_num]/$arr[click_total],4)*1000;             
                    }
                    
                    // $shujus = explode("-", $arr[pen_name]);
                    // $arr[mingchen] = $shujus[0];
                    // $arr[qudao] = $shujus[1];
                    // $arr[shijian] = $shujus[2];
                    
                    $value = array(
                                        iconv("UTF-8", "GBK", trim($agent_name)),
                                        iconv("UTF-8", "GBK", trim($arr['name'])),
                                        iconv("UTF-8", "GBK", trim($arr['money'])),
                                        iconv("UTF-8", "GB2312", $arr['pay_total']),
                                        iconv("UTF-8", "GB2312", $arr['reg_total']),
                                        iconv("UTF-8", "GB2312", $arr['click_total']),
                                        iconv("UTF-8", "GB2312", $arr['money_num']),
                                        $proportion,
                                        );
                     
                } elseif ($arr && $count) {
                    $value = array(
                                        iconv("UTF-8", "GBK", $agent_name),
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        iconv("UTF-8", "GB2312", '记录重复'),
                                        );
            } else {
                $value = array(
                                        iconv("UTF-8", "GBK", $agent_name),
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        '',
                                        'error',
                                        );
                }
                
                if ($value) {
                    echo implode("\t", $value)  . "\n";
                }
            } 
        }
    }


    public function get_short_url($link){
        echo A('Gongju')->get_short_url($link);
    }

//渠道日收入统计
    public function daymoney(){
        import('ORG.Util.Page'); // 导入分页类 
        $sp = M('system_pay');
        $M = M();  
        //判断是否搜索 
        if (isset($_POST['time'])) {
            $channel_id = $_REQUEST['channel_id'];
            // $time ="time ='".$_REQUEST['time']."'and";
            $time ="and time like".'"'.'%'.$_REQUEST['time'].'%'.'"';
            $sql2 ="SELECT DATE_FORMAT(time,'%Y-%m-%d') as day,channel_id, sum(money) money FROM hezuo_system_pay where channel_id = $channel_id and state=2  $time GROUP BY  day";
            $arr =$M->query($sql2);//获取总记录数
            $count = count($arr);
            $Page = new Page($count, 10);
            $first = $Page->firstRow;
            $end = $Page->listRows;
            $sql ="SELECT DATE_FORMAT(time,'%Y-%m-%d') as day,channel_id, sum(money) money FROM hezuo_system_pay where  channel_id = $channel_id and state=2  $time GROUP BY  day limit $first,$end";
         }else{
            $channel_id = $_REQUEST['channel_id'];
            $sql2 ="SELECT DATE_FORMAT(time,'%Y-%m-%d') as day,channel_id, sum(money) money FROM hezuo_system_pay where channel_id = $channel_id and state=2  GROUP BY  day";
            $arr =$M->query($sql2);//获取总记录数
            $count = count($arr);
            $Page = new Page($count, 10);
            $first = $Page->firstRow;
            $end = $Page->listRows;
            $sql ="SELECT DATE_FORMAT(time,'%Y-%m-%d') as day,channel_id, sum(money) money FROM hezuo_system_pay where channel_id = $channel_id and state=2  GROUP BY  day  limit $first,$end";
         }
        $data = $M->query($sql);
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); 
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('count', $count);
        $this->assign('channel_id',$channel_id);
        $this->assign("daymoney",$data);
        $this->display();
    }


}
