<?php
class UpinterAction extends Action {
// /alidata/www/admin/App/Lib/Action/Interface/Upinter/ubook
//public $url = "/alidata/www/admin/App/Lib/Action/Interface/";
//更新接口连载图书
	public function ubook(){
		//采集，连载
		$where["CpBook.type"] = 0;
		//$where["Book.state"] = 1;
		//$where["Book.fu_web"] = 0;
		$where["Book.web_id"] = array("eq",4);
		$where["Book.state"] = array("eq",1);
		$where["Book.fu_web"] = array("eq",0);
		$where["Book.cp_id"] = array("neq",0);
		$where["cp_id"] = array("neq",17);
		$data = D('UpsView')->where($where)->field("id,cp_id,user_name")->order("cp_id asc")->select();
		//echo D('UpsView')->getlastsql();die();
		//print_r($data);exit();
		set_time_limit(0);
		foreach ($data as $key => $value) {
			if($value[cp_id]==17){
				// $zongheng = A("Interface/Zongheng");
				// $zongheng->update($value[id]);
			 }
			elseif($value[cp_id]==18){
				$qiangwei = A("Interface/Qiangwei");
				$qiangwei->update($value[id]);
			}elseif($value[cp_id]==20){
				$zhonghuanovel = A("Interface/Zhonghuanovel");
				$zhonghuanovel->update($value[id]);
			}elseif($value[cp_id]==22){
				$jiukunovel = A("Interface/Jiukunovel");
				$jiukunovel->update($value[id]);
			}elseif($value[cp_id]==23){
				$honghua = A("Interface/Honghua");
				$honghua->update($value[id]);
			}elseif($value[cp_id]==24){
				$chuangkunovel = A("Interface/Chuangkunovel");
				$chuangkunovel->update($value[id]);
			}elseif($value[cp_id]==25){
				$huayu = A("Interface/Huayu");
				$huayu->update($value[id]);
			}elseif($value[cp_id]==26){
				$guijiejie = A("Interface/Guijiejie");
				$guijiejie->update($value[id]);
			}elseif($value[cp_id]==29){
				// $hongshu = A("Interface/Hongshu");
				// $hongshu->update($value[id]);
			}elseif($value[cp_id]==35){
				$wanzhong = A("Interface/Wanzhong");
				$wanzhong->update($value[id]);
			}elseif($value[cp_id]==44){
				// $fengqi = A("Interface/Fengqi");
				// $fengqi->update($value[id]);
			}elseif($value[cp_id]==46){
				$huahua = A("Interface/Huahua");
				$huahua->update($value[id]);
			}elseif($value[cp_id]==47){
				$hongshun = A("Interface/Hongshun");
				$hongshun->update($value[id]);
			}elseif($value[cp_id]==51){
				$weiyue = A("Interface/Weiyue");
				$weiyue->update($value[id]);
			}elseif($value[cp_id]==52){
				$pinshu = A("Interface/Pinshu");
				$pinshu->update($value[id]);
			}elseif($value[cp_id]==53){
				$aileyue = A("Interface/Aileyue");
				$aileyue->update($value[id]);
			}elseif($value[cp_id]==55){
				// $jinglun = A("Interface/Jinglun");
				// $jinglun->update($value[id]);
			}elseif($value[cp_id]==56){
				$dasheng = A("Interface/Dasheng");
				$dasheng->update($value[id]);
			}elseif($value[cp_id]==57){
				$qiwen = A("Interface/Qiwen");
				$qiwen->update($value[id]);
			}elseif($value[cp_id]==60){
				$luochen = A("Interface/Luochen");
				$luochen->update($value[id]);
			}elseif($value[cp_id]==61){
				$xinghuo = A("Interface/Xinghuo");
				$xinghuo->update($value[id]);
			}elseif($value[cp_id]==62){
				$lechuang = A("Interface/Lechuang");
				$lechuang->update($value[id]);
			}
			
		}
		echo "更新完毕";exit();
	}


	public function test(){
		//采集，连载
		$where["type"] = array("eq",0);
		$where["web_id"] = array("eq",4);
		$where["state"] = array("eq",1);
		$where["fu_web"] = array("eq",0);
		$where["cp_id"] = array("neq",19);
		$where["cp_id"] = array("neq",16);
		$where["cp_id"] = array("neq",0);
		$data = D('UpsView')->where($where)->field("id,cp_id")->order("cp_id asc")->select();
		print_r($data);exit();die();
		
	}


}

