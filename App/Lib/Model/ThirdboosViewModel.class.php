<?php

    //作品
    class ThirdboosViewModel extends ViewModel {
        public $viewFields = array(
            'Book' => array('book_id','fu_book','web_id','book_name', 'author_name', 'type_id', 'gender', 'state', 'vip', 'money', 'is_show', 'audit',  'words', 'cp_name', 'signing', 'level', 'time','_type'=>'LEFT'),
            'BookStatistical' => array('sum(BookStatistical.buy_total)' => 'buy_total', 'sum(BookStatistical.exceptional_total)' => 'exceptional_total', 'click_total', '_on' => 'Book.book_id=BookStatistical.book_id'),
        );
    }