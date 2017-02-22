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
         $str .=     '<div class="navbar navbar-default navbar-style-inverse" role="navigation">';
         $str .=       '<div class="nav">';
         $str .=         '<a class="brand font-24 block brand-color toolbar-header">Tools</a>';
         $str .=         '<ul class="nav navbar-nav center">';
         $str .=           '<li><a data-toggle="modal" data-target="#newmail-modal">New Message</a></li>';
         $str .=           '<li><a href="javascript:;">Search</a></li>';
         $str .=           '<li><a data-toggle="modal" data-target="#delmail-modal" onclick="setupDeleteMail();">Delete</a></li>';
         $str .=         '</ul>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';

         return $str;
       }
   }

 ?>