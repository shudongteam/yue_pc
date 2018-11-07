<?php

    //作品
    class BooksInterfaceViewModel extends ViewModel {

        public $viewFields = array(
            'CpBook' => array('fubook_name','book_id'),
            'Book' => array('chapter','book_brief','type_id','new_time','author_name','_on' => 'Book.book_id=CpBook.book_id'),
        );
    }