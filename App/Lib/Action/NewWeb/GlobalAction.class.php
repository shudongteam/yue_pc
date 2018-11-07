<?php

//权限判断
class GlobalAction extends Action {

        protected $auth;
        protected $requst_agent_id;


        public function _initialize() {
            //登录状态判断
            $uid = session('agent_id');
            if(!$uid) {
               $this->error("请重新登录", U('Login/index'));
            }
            //权限判断
            import('ORG.Util.Auth');
            $this->auth = new Auth();
            if(!$this->auth->check(MODULE_NAME.'/'.ACTION_NAME, $uid)){
               $this->error('你没有权限');
            }

            //加载菜单
            $menu_key = '';
            //微信key
            $weixin_tps = '';
            if (session('web_id')) {
                if (session('is_first')) {
                    $menu_key = 'menu_one';
                    //判断是否设置了微信
                    if (F('weixin_key_'.$uid) == false) {
                        $weixin = M('NWeixin')->where($this->where())->find();
                        if ($weixin) {
                            F('weixin_key_'.$uid, 2);
                        } else {
                          $weixin_tps = '<div id="invalid-mp-settings-hint" style="display:none;border-top:0;border-left:0;border-right:0;margin:0" class="alert alert-warning"> 
     <i class="fa fa-warning"></i>您的公众号配置不完整，推广前请务必配置好公众号信息。 
     <a href="/NewWeb/Settings/mp">立即配置</a>
    </div>';
                        }
                    }
                } else {
                    $menu_key = 'menu_two'; 
                }
            } else {
                $menu_key = 'menu_admin';
            }
            //$menu = F($menu_key);
            if (!$menu) {
                $model = M();
                $sql = "SELECT C.name, C.title, C.icon FROM `hezuo_n_auth_group_access` A  LEFT JOIN hezuo_n_auth_group B ON  A.group_id = B.id LEFT JOIN hezuo_n_auth_rule C ON  find_in_set(C.id, B.rules) WHERE A.uid = {$uid} AND  C.is_menu = 1 AND C.`status` = 1";
                $menu = $model->query($sql);
               F($menu_key, $menu);
            }
            $this->assign('menu', $menu);
            $this->assign('weixin_tps', $weixin_tps);

            //URL中是否含有代理参数
            $requst_agent_id = I('agent_id', 0, 'intval');
            $this->requst_agent_id = $requst_agent_id;
        }


        /**
         * 管理员/一级代理/二级代理查询条件组装(管理员可以查询全部)
         * @param  array   $arr  查询条件
         * @param  string  $key  数据库名.查询条件
         * @param  integer $type 类型:1只看自己的数据, 2查看自己和旗下二级代理的数据
         * @return array
         */
        public function where($arr = array(), $key= '', $type = 1){
            $web_id = session('web_id');//0是管理员其他数值为代理
            $agent_id = session('agent_id');//管理员或代理的ID
            $is_first = session('is_first');//1是一级代理其他数值不是
            if ($type == 1) {
                if ($web_id) {
                    if ($this->requst_agent_id) {
                        $this->is_agent(); 
                        $agent_id = $this->requst_agent_id;                   
                    }
                    $arr[$key ? $key.'.agent_id' : 'agent_id'] = $agent_id; 
                } else {
                    if ($this->requst_agent_id) {
                        $arr[$key ? $key.'.agent_id': 'agent_id'] = $this->requst_agent_id;
                    }
                }
            } elseif ($type == 2) {
                if ($web_id) {
                    if ($is_first) {
                        $arr[$key.'.web_id'] = $web_id;
                    } else {
                        $arr[$key.'.agent_id'] = $agent_id;
                    }
                }
            } 

            // else {
            //     if ($web_id) {
            //         if ($this->requst_agent_id) {
            //             $this->is_agent();
            //             $arr['agent_id'] = $this->requst_agent_id; 
            //         } else {
            //             $arr['agent_id'] = $agent_id; 
            //         }                    
            //     } else {
            //         if ($this->requst_agent_id) {
            //             $arr['agent_id'] = $this->requst_agent_id;
            //         }
            //     }
            // }
            return $arr;
        }


        public function is_agent($msg = "代理不存在!") {
            $map = array(
                'fu_agent' => session('agent_id'),
                'agent_id' => $this->requst_agent_id,
            );
            $res = M('NAgent')->where($map)->find();
            if (!$res) {
                $this->error("非法请求:".$msg);
            }
        }

        public function get_web_url(){
            return M('NAgent')->where('agent_id='.session('agent_id'))->getField('web_url');
        }
}
