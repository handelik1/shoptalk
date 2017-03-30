<?php

 /*************************************************************************************
  Class definition for the right-hand mailbox toolbar of ShopTalk
  ************************************************************************************/

 class MailToolbar
   {
     public function __construct()
       {
       }

     public function draw()
       {
         $str  = '<div class="col-sm-2">';
         $str .=   '<div class="sidebar-nav-right">';
         $str .=     '<div class="navbar navbar-default navbar-style" role="navigation">';
         $str .=       '<div class="nav">';
         $str .=         '<a class="brand font-26 block brand-color">Tools</a>';
         $str .=         '<ul class="nav navbar-nav center">';
         $str .=           '<li><a data-toggle="modal" data-target="#newmail-modal" class="font-22 tool-item">New Message</a></li>';
         $str .=           '<li><a href="javascript:;" class = "font-22 tool-item">Search</a></li>';
         $str .=           '<li><a data-toggle="modal" data-target="#delmail-modal" onclick="setupDeleteMail();" class = "font-22 tool-item">Delete</a></li>';
         $str .=         '</ul>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';

         return $str;
       }
   }

 ?>