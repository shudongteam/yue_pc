<?php

//采书接口
class InterfaceAction extends GlobalAction {

    public function index() {
        $ad = M('Cp');
        import('ORG.Util.Page'); // 导入分页类
        if ($this->isPost()) {
            $where[$_POST['search']] = array('like', "%$_POST[keyword]%");
        }
        if ($_REQUEST[cpname]) {
            $where['pen_name'] = array('like', '%'.$_REQUEST[cpname].'%');
            $_GET['pen_name'] = array('like', '%'.$_REQUEST[cpname].'%');
        }
        $where['web_id'] = 4;
        $count = $ad->where($where)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数               
        $cp = $ad->where($where)->field('cp_id,user_name,pen_name,type,phone,qq,email')->order('cp_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('cp', $cp);
        //翻页样式
        $Page->setConfig('theme', "<div class=\"pageSelect\"> <span>共 <b>%totalRow%</b> 条 当前 <b>%nowPage%</b>/%totalPage% 页</span><div class=\"pageWrap\">%prePage%%upPage%%linkPage%%downPage%%nextPage%</div></div>");
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    //调用接口,查看各家CP书籍
    public function caozuo($cp) {
        $cp_info = M('cp')->find($cp);
        $this->assign('cp_info', $cp_info);
        
        switch ($cp) {
        	case '1':
        		A('Interface/Chenlan')->index($cp);
        		break;
        	case '2':
        		A('Interface/heima')->index($cp);
        		break;
        	case '3':
        		A('Interface/Jingxiang')->index($cp);
        	    break;
        	case '4':
        		A('Interface/Moshang')->index($cp);
        	    break;
        	case '5':
        		A('Interface/Jiuku')->index($cp);
        	    break;
        	case '6':
        		A('Interface/Mairuideng')->index($cp);
        	    break;
        	case '7':
        		A('Interface/Yueji')->index($cp);
        	    break;
        	case '8':
        		A('Interface/Yuyue')->index($cp);
        		break;
        	case '9':
        		A('Interface/Likan')->index($cp);
        		break;
        	case '10':
        		A('Interface/Chuangku')->index($cp);
        	    break;
        	case '11':
        		A('Interface/Shuku')->index($cp);
        	    break;
        	case '12':
        		A('Interface/Weimeng')->index($cp);
        	    break;
        	case '13':
        		A('Interface/Chashui')->index($cp);
        	    break;
        	case '14':
        		A('Interface/Wensheng')->index($cp);
        	    break;
        	case '15':
        		A('Interface/JuNengWan')->index($cp);
        		break;
        	case '16':
        		A('Interface/Kanshuwang')->index($cp);
        		break;
        	case '17':
        		A('Interface/Zongheng')->index($cp);
        	    break;
        	case '18':
        		A('Interface/Qiangwei')->index($cp);
        	    break;
        	case '19':
        		A('Interface/Yuedu')->index($cp);
        	    break;
        	case '20':
        		A('Interface/Zhonghuanovel')->index($cp);
        	    break;
        	case '21':
        		A('Interface/XuanSe')->index($cp);
        	    break;
        	case '22':
        		A('Interface/Jiukunovel')->index($cp);
        		break;
        	case '23':
        		A('Interface/Honghua')->index($cp);
        		break;
        	case '24':
        		A('Interface/Chuangkunovel')->index($cp);
        	    break;
        	case '25':
        		A('Interface/Huayu')->index($cp);
        	    break;
        	case '26':
        		A('Interface/Guijiejie')->index($cp);
        	    break;
        	case '27':
        		A('Interface/Quyue')->index($cp);
        	    break;
        	case '28':
        		A('Interface/Yiqian')->index($cp);
        	    break;
        	case '29':
        		A('Interface/Hongshu')->index($cp);
        	    break;
        	case '30':
        		A('Interface/Lingyun')->index($cp);
        	    break;
        	case '31':
        		A('Interface/Zhongde')->index($cp);
        	    break;
        	case '32':
        		A('Interface/Tianyuedu')->index($cp);
        	    break;
        	case '33':
        		A('Interface/Yuelu')->index($cp);
        	    break;
        	case '34':
        		A('Interface/Yueting')->index($cp);
        	    break;
        	case '35':
        		A('Interface/Wanzhong')->index($cp);
        	    break;
        	case '36':
        		A('Interface/Chuangbie')->index($cp);
        	    break;
        	case '37':
        		A('Interface/Yueshu')->index($cp);
        	    break;
        	case '38':
        		A('Interface/Zhizihua')->index($cp);
        	    break;
        	case '39':
        		A('Interface/Guyuedu')->index($cp);
        	    break;
        	case '40':
        		A('Interface/Youyue')->index($cp);
        	    break;
        	case '41':
        		A('Interface/xiyaolaoshu')->index($cp);
        	    break;
        	case '42':
        		A('Interface/CpCooperate')->index($cp);
        	    break;
        	case '43':
        		A('Interface/Shucong')->index($cp);
        	    break;
        	case '44':
        		A('Interface/FengQi')->index($cp);
        	    break;
            case '45':
                A('Interface/PingZhiyun')->index($cp);
                break;
            case '46':
                A('Interface/Huahua')->index($cp);
                break;
            case '47':
                A('Interface/Hongshun')->index($cp);
                break;
            case '48':
                A('Interface/MiMeng')->index($cp);
                break;
            case '49':
                A('Interface/Guijj')->index($cp);
            break;
            case '50':
                A('Interface/Xiangshu')->index($cp);
            break;
            case '51':
                A('Interface/Weiyue')->index($cp);
            break;
            case '52':
                A('Interface/Pinshu')->index($cp);
            break;
            case '53':
                A('Interface/Aileyue')->index($cp);
            break;
            case '54':
                A('Interface/MunChun')->index($cp);
            break;
            case '55':
                A('Interface/Jinglun')->index($cp);
            break;
            case '56':
                A('Interface/Dasheng')->index($cp);
            break;
            case '57':
                A('Interface/Qiwen')->index($cp);
            break;
            case '60':
                A('Interface/Luochen')->index($cp);
            break;
            case '61':
                A('Interface/Xinghuo')->index($cp);
            break;
            case '62':
                A('Interface/Lechuang')->index($cp);
            break;
            case '65':
                A('Interface/Xinhuo')->index($cp);
            break;
        }
    }
    public function caiji($cp) {
        switch ($cp) {
            case '11':
                A('Interface/Shuku')->index($cp);
                break;
            case '16':
                A('Interface/Kanshuwang')->index($cp);
                break;
            case '17':
                A('Interface/Zongheng')->index($cp);
                break;
            case '18':
                A('Interface/Qiangwei')->index($cp);
                break;
            case '19':
                A('Interface/Yuedu')->index($cp);
                break;
            case '20':
                A('Interface/Zhonghuanovel')->index($cp);
                break;
            case '21':
                A('Interface/XuanSe')->index($cp);
                break;
            case '22':
                A('Interface/Jiukunovel')->index($cp);
                break;
            case '23':
                A('Interface/Honghua')->index($cp);
                break;
            case '24':
                A('Interface/Chuangkunovel')->index($cp);
                break;
            case '25':
                A('Interface/Huayu')->index($cp);
                break;
            case '26':
                A('Interface/Guijiejie')->index($cp);
                break;
            case '27':
                A('Interface/Quyue')->index($cp);
                break;
            case '28':
                A('Interface/Yiqian')->index($cp);
                break;
            case '29':
                A('Interface/Hongshu')->index($cp);
                break;
            case '30':
                A('Interface/Lingyun')->index($cp);
                break;
            case '31':
                A('Interface/Zhongde')->index($cp);
                break;
            case '32':
                A('Interface/Tianyuedu')->index($cp);
                break;
            case '33':
                A('Interface/Yuelu')->index($cp);
                break;
            case '34':
                A('Interface/Yueting')->index($cp);
                break;
            case '35':
                A('Interface/Wanzhong')->index($cp);
                break;
            case '36':
                A('Interface/Chuangbie')->index($cp);
                break;
            case '37':
                A('Interface/Yueshu')->index($cp);
                break;
            case '38':
                A('Interface/Zhizihua')->index($cp);
                break;
            case '39':
                A('Interface/Guyuedu')->index($cp);
                break;
            case '40':
                A('Interface/Youyue')->index($cp);
                break;
            case '41':
                A('Interface/xiyaolaoshu')->index($cp);
                break;
            case '42':
                A('Interface/CpCooperate')->index($cp);
                break;
            case '43':
                A('Interface/Shucong')->index($cp);
                break;
            case '44':
                A('Interface/FengQi')->index($cp);
                break;
            case '46':
                A('Interface/Huahua')->index($cp);
                break;
             case '47':
                A('Interface/Hongshun')->index($cp);
                break;
            case '48':
                A('Interface/MiMeng')->index($cp);
                break;
            case '49':
                A('Interface/Guijj')->index($cp);
            break;
            case'50':
                A('Interface/Xiangshu')->index($cp);
            break;
            case '51':
                A('Interface/Weiyue')->index($cp);
            break;
            case '52':
                A('Interface/Pinshu')->index($cp);
            break;
            case '53':
                A('Interface/Aileyue')->index($cp);
            break;
            case '54':
                A('Interface/MunChun')->index($cp);
            break;
            case '55':
                A('Interface/Jinglun')->index($cp);
            break;
            case '56':
                A('Interface/Dasheng')->index($cp);
            break;
            case '57':
                A('Interface/Qiwen')->index($cp);
            break;
            case '60':
                A('Interface/Luochen')->index($cp);
            break;
            case '61':
                A('Interface/Xinghuo')->index($cp);
            break;
            case '62':
                A('Interface/Lechuang')->index($cp);
            break;
        }
    }
    //输出接口书籍删除
    public function delete($id) {
        $is = M('CpBook')->where(array('id'=>$id))->delete();
        if ($is) {
            $this->success('删除成功！');
            exit();
        } else {
            $this->error('系统错误');
            exit();
        }
    }
}
