<?php

 /*************************************************************************************
  This is the chat session creation routine. A registered user has requested that a new
  chat session be created but has not yet provided all the required information. This
  routine fetches the p-th page of the in-depth chat-creation window.

  If the request succeeds, new modal window content is returned to the front end.

  If the request fails for some reason, the user is given an error message.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions
 require_once('../../interface/chat/chatdirectory.php');

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

 debug_log_inputs('chat-setuppage', $_POST);

 if(strcmp($send_request, 'heartheyregonnacancelLatin') == 0)	#  Run only if request came from JS
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
																#  Update user's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     /*********************************************************************************************/
     $i = intval(preg_replace('/[^0-9]/', '', $_POST['p']));	#  Clean up received variables

     $shoptalk_chatdirectory = new ChatDirectory();

     $outputstring  = 'ok|';									#  Build the output string
     $outputstring .= $shoptalk_chatdirectory->page($i);

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('chat-setuppage', $outputstring);

 print $outputstring;

 ?>