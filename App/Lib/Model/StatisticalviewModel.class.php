<?php
   //榜单结算
    class StatisticalviewModel extends ViewModel{
      public $viewFields = array(
            'Book' => array('book_id','book_name','author_name','is_show','state','audit','words','time'),
            'BookStatistical' => array('click_day','click_weeks','click_month','click_total','collection_day','collection_weeks','collection_month','collection_total','buy_day','buy_weeks','buy_month','buy_total','exceptional_day','exceptional_weeks','exceptional_month','exceptional_total','vote_day','vote_weeks','vote_month','vote_total','vipvote_day','vipvote_weeks','vipvote_month','vipvote_total','_on' =>"Book.book_id=BookStatistical.book_id" ),    
        );
    }
