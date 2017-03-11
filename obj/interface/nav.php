<?php

 /*************************************************************************************
  Class definition for the left-hand nav bar of ShopTalk.
  This item is always on stage and acts as the main control organ for the system.
  ************************************************************************************/

 class Nav
   {
     public function __construct()
       {
       }

     public function draw()
       {
         $str  = '<div class="col-sm-2">';
         $str .=   '<div class="sidebar-nav">';
         $str .=     '<div class="navbar navbar-default navbar-style" role="navigation">';
         $str .=       '<div class="navbar-header">';
         $str .=         '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">';
         $str .=           '<span class="sr-only">Toggle navigation</span>';
         $str .=           '<span class="icon-bar"></span>';
         $str .=           '<span class="icon-bar"></span>';
         $str .=           '<span class="icon-bar"></span>';
         $str .=         '</button>';
         $str .=         '<span class="visible-xs navbar-brand" onclick="stageHome();">ShopTalk</span>';
         $str .=       '</div>';
         $str .=       '<div class="navbar-collapse collapse sidebar-navbar-collapse">';
         $str .=         '<ul class="nav navbar-nav">';
         $str .=           '<li id="nav_home_li" onclick="stageHome();"><a id="nav-home" class="brand font-26">ShopTalk</a></li>';
         $str .=           '<li id="nav_chat_li" onclick="stageChat();"><a id="nav-chat" class = "font-18">Chat</a></li>';
         $str .=           '<li id="nav_posts_li" onclick="stagePosts();"><a id="nav-post" class = "font-18">Bulletin</a></li>';
/*
         $str .=           '<li class="dropdown">';
         $str .=             '<a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>';
         $str .=             '<ul class="dropdown-menu">';
         $str .=               '<li><a href="#">Profile</a></li>';
         $str .=               '<li><a href="#">Groups</a></li>';
         $str .=               '<li><a href="#">Settings</a></li>';
         $str .=               '<li class="divider"></li>';
         $str .=               '<li><a href="#">Publications</a></li>';
         $str .=               '<li><a href="#">Archives</a></li>';
         $str .=             '</ul>';
         $str .=           '</li>';
*/
         $str .=           '<li id="nav_acct_li" onclick="stageAccount();"><a id="nav-acct" class = "font-18">Account</a></li>';
/*
         $str .=           '<li class="dropdown">';
         $str .=             '<a href="#" class="dropdown-toggle" data-toggle="dropdown">Search <b class="caret"></b></a>';
         $str .=             '<ul class="dropdown-menu">';
         $str .=               '<li><input class="reg-credential" type="text"/></li>';
         $str .=               '<li><input type="submit" class="" id="search_submit" value="Search" onclick="search();"/></li>';
         $str .=               '<li class="divider"></li>';
         $str .=               '<li><label><input type="checkbox" id="nav_search_chat" value="chat_cb"> Chat sessions</label></li>';
         $str .=               '<li><label><input type="checkbox" id="nav_search_post" value="post_cb"> Bulletins</label></li>';
         $str .=               '<li><label><input type="checkbox" id="nav_search_group" value="group_cb"> Groups</label></li>';
         $str .=               '<li><label><input type="checkbox" id="nav_search_users" value="users_cb"> Users</label></li>';
         $str .=             '</ul>';
         $str .=           '</li>';
*/
         $str .=           '<li id="nav_logout_li" onclick="logout();"><a id="nav-post" class = "font-18">Sign out</a></li>';
         $str .=         '</ul>';
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }
   }

 ?>