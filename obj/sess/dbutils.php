<?php

 /*************************************************************************************
  Database utility script, chock-full of helper functions!
  ************************************************************************************/

 $current_time = time();

 #  Connect to database for "ShopTalk"
 function connect_to_db(&$link)
   {
     $link = mysql_connect('tgamato.net', 'shoptalk', '123shoptalk!');
     if($link == false)
       croak('dbconnect');
     if(mysql_select_db('shoptalk', $link) == false)
       {
         mysql_close($link);
         croak('dbselect');
       }
   }

 #  Error explanations: should be helpful, without revealing too much
 function croak($err)
   {
     $str = 'error|';
     switch($err)
       {
         case 'baddata':    $str .= 'Invalid data';
                        	break;
         case 'dbconnect':  $str .= 'Unable to connect to database';
                        	break;
         case 'dbdelete':   $str .= 'Unable to clear database';
                        	break;
         case 'dbinsert':   $str .= 'Unable to write to database';
                        	break;
         case 'dbread':     $str .= 'Unable to read from database';
                        	break;
         case 'dbselect':   $str .= 'Unable to select database';
                        	break;
         case 'dbupdate':   $str .= 'Unable to update database';
                        	break;
         case 'dbwrite':    $str .= 'Unable to write to database';
                        	break;
         case 'gametype':   $str .= 'Invalid game variation requested';
                        	break;
         case 'notfounddb': $str .= 'Data not found';
                        	break;
         case 'max_sessions': $str .= 'The maximum number of sessions has already been reached. Please try again later.';
                        	break;
         case 'unexpecteddb': $str .= 'Unexpected database error';
                        	break;
         default:           $str .= $err;
       }
     die($str);
   }

 ?>