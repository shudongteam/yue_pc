<?php

//友情连接
class FriendViewModel extends ViewModel {

    public $viewFields = array(
        'WebLink' => array('id', 'web_id', 'name', 'link'),
        'Web' => array('web_name', '_on' => 'WebLink.web_id=Web.web_id'),
    );

}
