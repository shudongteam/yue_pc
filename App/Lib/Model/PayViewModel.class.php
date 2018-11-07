<?php

    //充值查看
    class PayViewModel extends ViewModel {

        public $viewFields = array(
            'SystemPay' => array('id','user_id', 'agent_id', 'web_id', 'channel_id', 'monthly', 'type', 'trade', 'transaction', 'money', 'readmoney', 'state', 'statistical', 'time'),
            'User' => array('user_name', 'pen_name', '_on' => 'SystemPay.user_id=User.user_id'),
        );

    }
    