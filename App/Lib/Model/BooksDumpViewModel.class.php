<?php

    //作品
    class BooksDumpViewModel extends ViewModel {

        public $viewFields = array(
            'Book' => array('book_id','fu_book', 'book_name', 'author_name', 'type_id', 'gender', 'state', 'vip', 'money', 'is_show', 'audit',  'words', 'cp_name', '_type' => 'left'),
            'BookStatistical' => array('buy_total','exceptional_total', 'click_total','collection_total', '_on' => 'Book.fu_book=BookStatistical.book_id'),
        );
    }