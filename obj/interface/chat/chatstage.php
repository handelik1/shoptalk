<?php

 /*************************************************************************************
  Class definition for the center-stage chat session div of ShopTalk
  ************************************************************************************/

 class ChatStage
   {
     public function __construct()
       {
       }

     public function draw()
       {
         $str  = '<div class="col-sm-8">';
          																	#  Chat record
         $str .=   '<div class = "container-fluid">';
         $str .=     '<div class = "row">';
         $str .=       '<div class = "col-sm-12">';
         $str .=         '<div class = "chat-area">';
         $str .=           '<p>Text will end up here</p>';
         $str .=         '</div>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
          																	#  Chat entry field
         $str .=   '<div class = "container-fluid">';
         $str .=     '<div class = "row">';
         $str .=       '<form method = "post" action = "#">';
         $str .=         '<div class = "col-sm-10">';
         $str .=           '<input class = "chat-text" type = "text" name = "message"/>';
         $str .=         '</div>';
         $str .=         '<div class = "col-sm-2">';
         $str .=           '<input class = "btn btn-primary" type = "button" name = "submit" value = "Go"/>';
         $str .=         '</div>';
         $str .=       '</form>';
         $str .=     '</div>';
         $str .=   '</div>';

         $str .= '</div>';

         return $str;
       }
   }

 ?>