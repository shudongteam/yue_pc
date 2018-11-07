<?php

//订单统计
class OrdersAction extends GlobalAction {

    //订单统计
    public function index() {
        $alt = I('alt');
        $status = I('status', 0, 'intval');
        $from = I('from');
        $to = I('to');
        $q = I('q');
        $channel_id = I('channel_id', 0, 'intval');
        $user_id = I('user_id', 0, 'intval');
        if ($alt == 'excel') {
            $this->excel($from, $to);
            exit;
        }
        $model = D('NOrderView');
        $where = $this->where(array(), 'NSystemPay', 2);
        if ($status) {
            $where['NSystemPay.state'] = $status;
        }
        if ($from) {
            $where['NSystemPay.time'] = array('egt', $from);
        }
        if ($to) {
            $where['NSystemPay.time'] = array('elt', $to);
        }
        if($from && $to){
            $where['NSystemPay.time'] = array(array('egt', $from), array('elt', $to)) ;
        }           
        if ($q) {
            $where['NSystemPay.trade'] = $q;
        }
        if ($channel_id) {
            //判断channel_id 是否存在
            $agent_id = M('NAgentChannel')->where('channel_id='.$channel_id)->getField('agent_id');
            if (session('web_id')) {
                //代理
                if (session('agent_id') == $agent_id) {
                    //相同
                    $where['NSystemPay.channel_id'] = $channel_id;
                } else {
                    //不同
                    $this->requst_agent_id = $agent_id;
                    $this->is_agent("推广链接不存在!");
                    $where['NSystemPay.channel_id'] = $channel_id;                 
                }
            } else {
                //管理员
                $where['NSystemPay.channel_id'] = $channel_id; 
            }
        }
        //判断用户是否是当前代理旗下的
        if ($user_id) {
            $up_agent_id = M('NUser')->where('user_id = ' .$user_id)->getField('agent_id');
            if ($up_agent_id == session('agent_id')) {
                $where['NUserInfo.user_id'] = $up_agent_id;
            } else {
                if (!session('web_id')) {
                    $where['NUserInfo.user_id'] = $up_agent_id;
                } else {
                    $this->is_agent();
                    $where['NUserInfo.user_id'] = $up_agent_id; 
                }
            }
        }
        $count = $model->where($where)->count(); 
        import('ORG.Util.MyPage');
        $Page = new MyPage($count);
        $data = $model->where($where)->field()->order('NSystemPay.id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        // echo $model->getLastSql();
        $this->assign('data', $data);
        $this->assign('page', $Page->show());
        $this->assign('status', $status);
        $this->empty = '<td class="text-center" colspan="100">没有数据</td>';         
        $this->title = '订单管理';
        $this->display();
    }

    //导出已支付订单
    public function excel($from, $to) {
        $where['NSystemPay.time'] = array('egt', $from);
        $where['NSystemPay.time'] = array('elt', $to);
        $where['NSystemPay.state'] = 2;

        $filename = '已支付订单' . $from . '至' . $to;
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo iconv("UTF-8", "GBK", ("商户单号" . "\t" . '用户' . "\t" . '金额' . "\t" . '创建日期' . "\t" . '来源' . "\t" . '代理')) . "\n";  
        $model = D('NOrderView');

        $result = $model->where($where)->order('time desc')->select();
        // echo $model->getLastSql();exit;
        foreach ($result as $key => $val) {
            $value = array(
                '`'.$val['trade'],
                iconv("UTF-8", "GBK", $val['pen_name']),
                $val['money'],
                '`'.$val['time'],
                iconv("UTF-8", "GBK", $val['name']),
                iconv("UTF-8", "GBK", $val['agent_name']),
            );
            echo implode("\t", $value) . "\n";
        }
    }
}