<?php
   //统计模型
    class NBooksViewModel extends ViewModel {

        public $viewFields = array(
            'Book' => array('book_id','fu_book','web_id','cp_id','author_id', 'chapter', 'book_name', 'is_show', 'state','vip', 'upload_img', 'gender', 'time', 'new_time', '_type'=>'LEFT'),
            'NBookPaidan' => array('IFNULL(NBookPaidan.nums, 0)' => 'rank', '_on' => 'Book.fu_book=NBookPaidan.book_id'),
        );

    }