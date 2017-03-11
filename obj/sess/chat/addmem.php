<?php

 /*************************************************************************************
  This is the chat access-granting routine. A registered user who has control over a
  chat session has requested that another user or other users be given access to this
  session.

  We make sure of the following:
  1.  User is a legitimate user
  2.  Chat session is legitimate chat session
  3.  User is host or leader of this chat
  4.  Invited user is legitimate user
  5.  Invited user does not already have access to this chat

  If the request succeeds, the chat session's DB row is updated to include the KP of the
  invited user(s).

  If the request fails for some reason, the user is given an error message.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions

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

 if(strcmp($send_request, 'YrINVITED') == 0)					#  Run only if request came from JS
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
     															#  Build list of members to give access
     $session_add = intval(preg_replace("~[^0-9]~", '', htmlentities($_POST['s'], ENT_QUOTES)));
     $guest_count = intval(preg_replace("~[^0-9]~", '', htmlentities($_POST['gc'], ENT_QUOTES)));
     $members = array();

     for($i = 0; $i < $guest_count; $i++)
       {
         if(isset($_POST['m'.$i]))								#  A string
           {
             $query  = 'SELECT kp FROM users';
             $query .= ' WHERE uname = "'.preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '',
                                                       htmlentities($_POST['m'.$i], ENT_QUOTES)).'";';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             if(mysql_num_rows($result) == 1)					#  If member exists
               {
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
                 array_push($members, intval($ret['kp']));		#  Add KP to array
               }
           }
       }

     /*********************************************************************************************/
     $query  = 'SELECT session_leader, access FROM chats';		#  Does this user have access to the
     $query .= ' WHERE kp = '.$session_add;						#  claimed chat session?
     $query .= ' AND session_leader = '.$kp.';';				#  ONLY SESSION LEADER CAN INVITE
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     if(mysql_num_rows($result) == 0)							#  User described invalid session
       {
         mysql_close($link);
         croak('baddata');
       }
     else if(mysql_num_rows($result) != 1)						#  Shouldn't happen
       {
         mysql_close($link);
         croak('dbmismatch');
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $session_leader = intval($ret['session_leader']);			#  Save for comparison
     $tmp = explode(',', $ret['access']);
     $already_access = array();
     array_push($already_access, $session_leader);
     for($i = 0; $i < count($tmp); $i++)
       {
         $x = intval($tmp[$i]);
         if(!in_array($x, $already_access))
           array_push($already_access, $x);
       }

     /*********************************************************************************************/
     $members = array_diff($members, $already_access);			#  Make sure no duplicates are added
     $mem_str = implode(',', $members);
     $query  = 'UPDATE chats SET access = "'.$mem_str.'"';
     $query .= ' WHERE kp = '.$session_add.';';
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
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>