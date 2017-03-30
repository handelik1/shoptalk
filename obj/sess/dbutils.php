<?php

 /*************************************************************************************
  Database utility script, chock-full of helper functions!
  ************************************************************************************/

 $current_time = time();

 $SESS_DEBUGGING = true;

 #  Connect to database for "ShopTalk" in a way that was serviceable for years, never bothering anybody.
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

 #  Connect to database for "ShopTalk" in the particular way that PHP 5 wants us to do it.
 #  Sigh... we have to do it this way in the chat creation script because only this way allows
 #  us to insert a row and immediately retrieve the new KP for that row. The old method, despite
 #  having been serviceable for years, only offers work-arounds to this problem.
 function mysqli_connect_db(&$link)
   {
     $link = new mysqli('tgamato.net', 'shoptalk', '123shoptalk!', 'shoptalk');
     if(mysqli_connect_errno())
       croak('dbconnect');
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

 function debug_log_inputs($script, &$args)
   {
     global $SESS_DEBUGGING, $current_time;
     if($SESS_DEBUGGING)
       {
         $arr = array();
         foreach($args as $k => $v)
           $arr[$k] = $v;

         if(!array_key_exists('uname', $arr))
           $arr['uname'] = $_SESSION['uname'];

         if(!array_key_exists('pword', $arr))
           $arr['pword'] = $_SESSION['pword'];

         $fh = fopen('../../debug/'.$script.'.'.$current_time.'.in', 'w');
         foreach($arr as $k => $v)
           fwrite($fh, ''.$k."\t".$v."\n");
         fclose($fh);
       }
   }

 function debug_log_outputs($script, $output)
   {
     global $SESS_DEBUGGING, $current_time;
     if($SESS_DEBUGGING)
       {
         $fh = fopen('../../debug/'.$script.'.'.$current_time.'.out', 'w');
         fwrite($fh, ''.$output."\n");
         fclose($fh);
       }
   }

 ?>