<?php

//用户帮助
class MyhelpViewModel extends ViewModel {

    public $viewFields = array(
        'WebHelp' => array('id','type', 'web_id', 'title', 'time'),
        'Web' => array('web_name', '_on' => 'WebHelp.web_id=Web.web_id'),
    );

}
