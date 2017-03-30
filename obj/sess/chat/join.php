<?php

 /*************************************************************************************
  This is the chat session joining routine. A registered user has requested participation
  in an existing chat session.

  We check to make sure that:

  1.  This user is who he/she claims to be.
  2.  This user has permission to join this chat.

  If the request is granted, new page contents are returned.

  If the request fails for some reason, the user is given an error message.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions
 require_once('../../interface/chat/chat.php');
 require_once('../../interface/chat/chatsessionbar.php');
 require_once('../../interface/nav.php');

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

 debug_log_inputs('chat-join', $_POST);

 if(strcmp($send_request, 'LEMMEIN') == 0)						#  Run only if request came from JS
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
																#  Scrub chat session ID
     $i = intval(preg_replace("~[^0-9]~", '', htmlentities($_POST['i'], ENT_QUOTES)));

     $query  = 'SELECT kp FROM chats';							#  Does user actually have access
     $query .= ' WHERE kp = '.$i;								#  to this session?
     $query .= ' AND (session_leader = '.$kp;					#  Session KP is as claimed and
     $query .=       ' OR FIND_IN_SET('.$kp.', access)';		#  user is leader or is invited,
     $query .=       ' OR access IS NULL);';					#  or session is public.
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     if(mysql_num_rows($result) == 1)							#  ACCESS GRANTED !!
       {
         $shoptalk_nav = new Nav();								#  Build a new in-session chat page
         $shoptalk_chat = new Chat($i);
         $shoptalk_chatsessbar = new ChatSessionToolbar($i);

         $outputstring  = 'ok|';								#  Build the output string
         $outputstring .= '<div id="homepage">';
         $outputstring .=   '<div class="container-fluid">';
         $outputstring .=     '<div class="row">';
         $outputstring .=       $shoptalk_nav->draw();			#  Render the Nav
         														#  Render the Chatbox
         $outputstring .=       $shoptalk_chat->draw_fetch($kp, $link);
         $outputstring .=       $shoptalk_chatsessbar->draw();	#  Render the Chatbox Toolbar
         $outputstring .=     '</div>';
         $outputstring .=   '</div>';
         $outputstring .= '</div>';
       }
     else														#  DENIED !!
       $outputstring  = 'denied';								#  Return an error message

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('chat-join', $outputstring);

 print $outputstring;

 ?>