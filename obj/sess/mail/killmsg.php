<?php

 /*************************************************************************************
  This is the person-to-person message-deleting routine. A request has been made by a
  registered user to delete a note from either his or her inbox or outbox. We check the
  given credentials against existing records and either remove a row from the messages table,
  set the visible_to_sender flag to FALSE, or return an error.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions
 require_once('../../interface/mail/mailbox.php');
 require_once('../../interface/mail/mailtoolbar.php');

 $send_request = preg_replace("/[^A-Za-z]/", '', $_POST['sendRequest']);

 if(!isset($_SESSION['uname']) || !isset($_SESSION['pword']))	#  If there is no session copy of credentials
   {															#  then we expect to find them in $_POST
     $uname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['uname'], ENT_QUOTES));
     if(strlen($uname) > 64)									#  Trim uname to permissible length
       $uname = substr($uname, 0, 64);

     $pword = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['pword'], ENT_QUOTES));
   }
 else
   {
     $uname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_SESSION['uname'], ENT_QUOTES));
     if(strlen($uname) > 64)									#  Trim uname to permissible length
       $uname = substr($uname, 0, 64);

     $pword = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_SESSION['pword'], ENT_QUOTES));
   }

 $kill_str = preg_replace("~[^0-9\-]~", '', $_POST['kill']);	#  Dash-separated list of message KPs to kill
 																#  Whether we scrub from the requesting user's
 																#  inbox (true) or outbox (false)
 $ibox = (intval(preg_replace("~[^0-1]~", '', $_POST['b'])) == 1);

 debug_log_inputs('mail-killmsg', $_POST);

 if(strcmp($send_request, 'gOneScrappedVamoose') == 0)			#  Run only if request came from JS
   {
     connect_to_db($link);										#  Connect to MySQL

																#  Retrieve salt for uname
     $query = 'SELECT salt FROM users WHERE uname = "'.$uname.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num == 0)
       {
         mysql_close($link);
         die('fatalerror|Incorrect user name or password');		#  Uname not found
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $salt = $ret['salt'];
																#  Compare hashed pword
     $query  = 'SELECT kp FROM users';
     $query .= ' WHERE uname = "'.$uname.'" AND pword = "'.(hash('sha256', $pword)).$salt.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num == 0)
       {
         mysql_close($link);
         die('fatalerror|Incorrect user name or password');		#  Uname not found
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $kp = $ret['kp'];

     $kill = explode('-', $kill_str);							#  Expand kill string
     if($ibox)													#  Request to remove from KP's inbox
       {
         for($i = 0; $i < count($kill); $i++)					#  For every message on kill-list
           {
             $query  = 'SELECT visible_to_sender FROM messages';
             $query .= ' WHERE kp = '.$kill[$i].' AND receiver = '.$kp.';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             if(mysql_num_rows($result) == 1)
               {
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
                 $visible_to_sender = (intval($ret['visible_to_sender']) == 1);
                 if($visible_to_sender)							#  Sender has not thrown away: set bit
                   {
                     $query  = 'UPDATE messages SET visible_to_receiver = FALSE';
                     $query .= ' WHERE kp = '.$kill[$i].';';
                     $result = mysql_query($query, $link);
                     if($result == false)
                       {
                         mysql_close($link);
                         croak('dbupdate');
                       }
                   }
                 else											#  Sender has thrown away: we can, too
                   {
                     $query = 'DELETE FROM messages WHERE kp = '.$kill[$i].';';
                     $result = mysql_query($query, $link);
                     if($result == false)
                       {
                         mysql_close($link);
                         croak('dbdelete');
                       }
                   }
               }
           }
       }
     else														#  Request to remove from KP's outbox
       {
         for($i = 0; $i < count($kill); $i++)					#  For every message on kill-list
           {
             $query  = 'SELECT visible_to_receiver FROM messages';
             $query .= ' WHERE kp = '.$kill[$i].' AND sender = '.$kp.';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             if(mysql_num_rows($result) == 1)
               {
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
                 $visible_to_receiver = (intval($ret['visible_to_receiver']) == 1);
                 if($visible_to_receiver)						#  Receiver has not thrown away: set bit
                   {
                     $query  = 'UPDATE messages SET visible_to_sender = FALSE';
                     $query .= ' WHERE kp = '.$kill[$i].';';
                     $result = mysql_query($query, $link);
                     if($result == false)
                       {
                         mysql_close($link);
                         croak('dbupdate');
                       }
                   }
                 else											#  Receiver has thrown away: we can, too
                   {
                     $query = 'DELETE FROM messages WHERE kp = '.$kill[$i].';';
                     $result = mysql_query($query, $link);
                     if($result == false)
                       {
                         mysql_close($link);
                         croak('dbdelete');
                       }
                   }
               }
           }
       }

     $outputstring = 'ok';

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('mail-killmsg', $outputstring);

 print $outputstring;

 ?>