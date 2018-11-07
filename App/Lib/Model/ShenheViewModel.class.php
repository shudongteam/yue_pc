<?php
   //审核模型
    class ShenheViewModel extends ViewModel {

        public $viewFields = array(
            'BookContent' => array('content_id','fu_book','title','num','number','time'),
            'Book' => array('book_id', 'author_id','book_name','signing', '_on' => 'BookContent.fu_book=Book.book_id'),           
        );

    }
    