<?php

    //作品
    class BooksViewModel extends ViewModel {
        public $viewFields = array(
            'Book' => array('book_id','fu_book', 'book_name', 'author_name', 'type_id', 'gender', 'state', 'vip', 'money', 'is_show', 'audit',  'words', 'cp_name', 'signing', 'level', 'time'),
            'BookStatistical' => array('buy_total','exceptional_total', 'click_total', '_on' => 'Book.fu_book=BookStatistical.book_id'),
        );
    }