<?php

//代理账单管理
class AgentmoneyViewModel extends ViewModel {

    public $viewFields = array(
        'AgentMoneyday' => array('id', 'agent_id', 'web_id', 'type','proportion','money_total', 'money', 'time'),
        'Agent' => array('agent_id', 'pen_name', '_on' => 'AgentMoneyday.agent_id=Agent.agent_id'),
    );

}
