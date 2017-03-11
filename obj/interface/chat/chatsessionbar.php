<?php

 /*************************************************************************************
  Class definition for the right-hand chat IN-SESSION toolbar of ShopTalk
  ************************************************************************************/

 class ChatSessionToolbar
   {
     public $sess_kp;							#  KP of the current session

     public function __construct($k)
       {
         $this->sess_kp = $k;
       }

     public function draw()
       {
         $str  = '<div class="col-sm-2">';
         $str .=   '<div class="sidebar-nav-right">';
         $str .=     '<div class="navbar navbar-default navbar-style" role="navigation">';
         $str .=       '<div class="nav">';
         $str .=         '<a class = "brand font-24 block brand-color">Tools</a>';
         $str .=         '<ul class="nav navbar-nav center">';
         $str .=           '<li><a href="javascript:;" onclick="popInvitePanel('.$this->sess_kp.')" class = "font-22 tool-item">Invite Member</a></li>';
         $str .=           '<li><a href="javascript:;" onclick="popExpelPanel('.$this->sess_kp.')" class = "font-22 tool-item">Expel Member</a></li>';
         $str .=           '<li><a href="javascript:;" class = "font-22 tool-item">Item 3</a></li>';
         $str .=         '</ul>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';

         return $str;
       }
   }

 ?>