<?php

 /*************************************************************************************
  This is the chat session HEARTBEAT routine. What's that mean?

  It means that this is the routine which simulates real-time communication by periodically
  fetching any events which have occurred since the registered user last checked.

  We make sure of the following:
  1.  User is a legitimate user
  2.  Chat session is legitimate chat session
  3.  User has access to this chat

  If the request succeeds, everything FROM THIS USER'S latest_session_ptr ONWARD is returned
  to the user.

  If the request fails for some reason, the user is given an error message.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions
 require_once('../../interface/chat/chat.php');

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

 if(strcmp($send_request, 'ThsIsGrndCtrl') == 0)				#  Run only if request came from JS
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
																#  Be it confirmed: user is as claimed.
																#  But does user have access to chat?
     $query  = 'SELECT in_session, latest_session_ptr FROM users';
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $in_session = intval($ret['in_session']);
     $latest_session_ptr = intval($ret['latest_session_ptr']);

     $query  = 'SELECT kp FROM chats';							#  Does user actually have access
     $query .= ' WHERE kp = '.$in_session;						#  to this session?
     $query .= ' AND (session_leader = '.$kp;					#  Session KP is as reported in DB,
     $query .=       ' OR FIND_IN_SET('.$kp.', access)';		#  and user is leader or is invited,
     $query .=       ' OR access IS NULL);';					#  or session is public.
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num == 0)												#  There is no LIVE session match
       {
         mysql_close($link);
         $outputstring = '';									#  Return nothing (??????????????)
       }
     else if($num == 1)
       {
         /*****************************************************************************************/
																#  Catch up the user's transcript
		 $shoptalk_chat = new Chat($in_session);
		 $outputstring  = 'ok|';
		 $outputstring .= $shoptalk_chat->draw_transcript_addition($link, $current_time);
       }
     else														#  This shouldn't happen
       {
         mysql_close($link);
         croak('dbmismatch');
       }

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>