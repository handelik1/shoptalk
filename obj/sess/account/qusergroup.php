<?php

 /*************************************************************************************
  This is the user-name lookup routine. A request has been made by a registered user to
  find user names matching (at least in part) some string.
  We check the given credentials against existing records and either return a list or an
  error message.

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

 $qstring = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['q'], ENT_QUOTES));

 if(strcmp($send_request, 'rEEchOUTtuchSumune') == 0)			#  Run only if request came from JS
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
     $query  = 'SELECT kp, first_name, last_name, uname, institution, time_zone FROM users';
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
     $fname = $ret['first_name'];
     $lname = $ret['last_name'];
     $sys_uname = $ret['uname'];
     $institution = $ret['institution'];
     $time_zone = intval($ret['time_zone']);
																#  Update user's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     $query  = 'SELECT uname FROM users WHERE uname LIKE "%'.$qstring.'%"';
     $query .= ' ORDER BY CHAR_LENGTH(uname) ASC;';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     if(mysql_num_rows($result) > 0)							#  Are there any groups?
       {
         $outputstring = 'ok|';									#  Build the output string
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))	#  Only get first one
           {
             $outputstring .= $row['uname'];
             break;
           }
       }
     else
       $outputstring = 'ok';									#  No results (but correct functionality)

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>