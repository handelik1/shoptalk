<?php

 /*************************************************************************************
  Class definition for the right-hand chat session toolbar of ShopTalk
  ************************************************************************************/

 class ChatToolbar
   {
     public function __construct()
       {
       }

     public function draw()
       {
         $str  = '<div class="col-sm-2">';
         $str .=   '<div class="sidebar-nav-right">';
         $str .=     '<div class="navbar navbar-default" role="navigation">';
         $str .=       '<div class="nav">';
         $str .=         '<a class = "brand font-24 block brand-color">Tools</a>';
         $str .=         '<ul class="nav navbar-nav center">';
         $str .=           '<li><a href="#">New Session</a></li>';
         $str .=           '<li><a href="#">Join Session</a></li>';
         $str .=           '<li><a href="#">Item 3</a></li>';
         $str .=           '<li><a href="#">Item 4</a></li>';
         $str .=         '</ul>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';

         return $str;
       }
   }

 ?>