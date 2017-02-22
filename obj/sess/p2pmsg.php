<?php

 /*************************************************************************************
  This is the person-to-person message-sending routine. A request has been made by a
  registered user to send a note to another registered user. We check the given credentials
  against existing records and either store a row in the messages table or return an error.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('./dbutils.php');									#  Contains DB helper functions
 require_once('../interface/chat/chatstage.php');
 require_once('../interface/chat/chattoolbar.php');
 require_once('../interface/nav.php');
 require_once('../interface/mail/mailbox.php');
 require_once('../interface/mail/mailtoolbar.php');

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

 $receiver = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['r'], ENT_QUOTES));
 if(strlen($receiver) > 64)										#  Trim receiver's name to permissible length
   $receiver = substr($receiver, 0, 64);

 $subject = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['s'], ENT_QUOTES));
 if(strlen($subject) > 64)										#  Trim subject to permissible length
   $subject = substr($subject, 0, 64);

 $message = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['m'], ENT_QUOTES));

 if(strcmp($send_request, 'ConversationHearts') == 0)			#  Run only if request came from JS
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
       die('fatalerror|Incorrect user name or password');		#  Uname not found
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
       die('fatalerror|Incorrect user name or password');		#  Uname not found
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $skp = $ret['kp'];

     $query  = 'SELECT kp FROM users';							#  Retrieve receiver's name
     $query .= ' WHERE uname = "'.$receiver.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num == 0)
       die('error|Unknown recipient');							#  Unknown recipient
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $rkp = $ret['kp'];

     $query  = 'INSERT INTO messages(sender, receiver, time_stamp, subject, content)';
     $query .= ' VALUES('.$skp.', '.$rkp.', '.$current_time.',';
     $query .= ' "'.$subject.'", "'.$message.'");';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbinsert');
       }
																#  Update sender's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$skp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     $outputstring = 'ok';

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('bad_input');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>