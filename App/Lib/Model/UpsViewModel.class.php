<?php

    class UpsViewModel extends ViewModel {

        public $viewFields = array(
        	'Book' => array('state','fu_web','web_id','cp_id'), 
        	'CpBook' => array('id','type','_on' => 'Book.fu_book=CpBook.book_id'), 
        	'Cp'=>array('user_name','_on'=>'Book.cp_id=Cp.cp_id'),
        );

    }