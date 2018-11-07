<?php

    //作品
    class AgentchannelViewModel extends ViewModel {

        public $viewFields = array(
            'Agent' => array('agent_id','user_name','pen_name'),
            'AgentChannel' => array('agent_id','channel_id','name','type_id','focus','num','money','agent_name','money_num','click_total','reg_total','pay_total','link','time','_on' => 'Agent.agent_id=AgentChannel.agent_id'),
        );

    }