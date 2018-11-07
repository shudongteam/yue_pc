<?php

//APi
// class MainAction extends GlobalAction {
class ApiAction extends Action
{
   public function getBook(){

       $url = "http://www.shengwenkj.com/Admin5b/Shengwen/book";
       $books = json_decode(file_get_contents($url),true);
       $book=M('Book');
       $book_statistical=M('BookStatistical');
       foreach ($books as $k=>$v){
           $res= $book->where(['fu_book'=>$v['book_id']])->find();
           if($res){
               echo "该书籍重复";
           }else{
               $data=[
                   'fu_book'  =>$v['book_id'],
                   'web_id'   =>1,
                   'fu_web'   =>1,
                   'cp_id'    =>1,
                   'cp_name' =>'上饶市盛文科技有限公司',
                   'book_name' =>$v['book_name'],
                   'author_name' =>$v['author_name'],
                   'type_id'   =>$v['type_id'],
                   'gender'   =>$v['gender'],
                   'upload_img'=>$v['upload_img'],
                   'signing'   =>'已签约',
                   'state'   =>$v['state'],
                   'vip'     =>$v['vip'],
                   'money'  =>$v['money'],
                   'is_show' =>$v['is_show'],
                   'audit'  =>$v['audit'],
                   'chapter' =>$v['chapter'],
                   'keywords' =>$v['keywords'],
                   'book_brief' =>$v['book_brief'],
                   'words'   =>$v['words'],
                   'time'   =>$v['time'],
                   'new_time' =>$v['time']
               ];
               $id=$book->add($data);
               if($id){
                   $book_statistical->add(['book_id'=>$id]);
                   echo "添加书籍".$v['book_name']."成功<br/>";
               }
           }

       }
   }

   public function getContent($id){
         $url="http://www.shengwenkj.com/Admin5b/Shengwen/content/id/".$id;
         $title = json_decode(file_get_contents($url),true);
         $content=M('BookContent');
         $contents=M('BookContents');
       foreach($title as $k=>$v){
           $res=$content->where(['cp_id'=>$v['content_id']])->find();
           if($res){
               echo "该章节重复";
           }else{
               $data=[
                   'cp_id' =>$v['content_id'],
                   'fu_book' =>$v['book_id'],
                   'num'   =>$v['num'],
                   'title' =>$v['title'],
                   'number'  =>$v['number'],
                   'clicknum'  =>$v['clicknum'],
                   'the_price'  =>$v['the_price'],
                   'dycs'    =>$v['dycs'],
                   'time'   =>$v['time']
               ];
              $idss= $content->add($data);
              if($idss){
                  $url1="http://www.shengwenkj.com/Admin5b/Shengwen/contents/id/".$v['content_id'];
                  $des = json_decode(file_get_contents($url1),true);
                  $aaaa['content_id']=$idss;
                  $aaaa['content']=$des['content'];
                  $contents->add($aaaa);
                  echo "添加".$v['title']."成功<br/>";

              }

           }
       }
   }

}
