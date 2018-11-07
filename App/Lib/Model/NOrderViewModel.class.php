<?php
   //统计模型
    class NOrderViewModel extends ViewModel {

        public $viewFields = array(
            'NSystemPay' => array('trade','money', 'state', 'time', '_type'=>'LEFT'),
            'NUserInfo' => array('pen_name', 'sex', '_on' => 'NSystemPay.user_id=NUserInfo.user_id', '_type'=>'LEFT'),
            'NAgentChannel' => array('agent_name', 'name', '_on' => 'NSystemPay.channel_id=NAgentChannel.channel_id'),
        );

    }