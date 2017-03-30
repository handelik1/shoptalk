<?php

 /*************************************************************************************
  This is the group panel refresh routine. A registered user has requested that the
  groups page be refreshed.
  We check the given credentials against existing records and either return a new page
  or return an error message.

  We check the following things for every login request:

  1.  Does password hash plus salt equal username's password hash plus salt?
  2.  Is this user already logged in?  --  What to do then???
  ************************************************************************************/

 session_start();

 require_once('../dbutils.php');								#  Contains DB helper functions
 require_once('../../interface/account/account.php');

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
																#  Number of members to group together
 $c = intval(preg_replace("~[^0-9]~", '', htmlentities($_POST['c'], ENT_QUOTES)));
																#  Group name
 $gname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['gname'], ENT_QUOTES));
 $gmembers = array();											#  To contain user names

 debug_log_inputs('account-rfshgroup', $_POST);

 if(strcmp($send_request, 'DoubleMintDoubleMintGUM') == 0)		#  Run only if request came from JS
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

     /* This differs from the accounts-page-rendering script in that we only want to return
        the groups list, which will be re-drawn in place. */

     $shoptalk_account = new Account();
     $shoptalk_account->fname = $fname;							#  Set this user's first name
     $shoptalk_account->lname = $lname;							#  Set this user's last name
     $shoptalk_account->uname = $sys_uname;						#  Set this user's screen name
     $shoptalk_account->institution = $institution;				#  Set this user's institution
     $shoptalk_account->time_zone = $time_zone;					#  Set this user's time zone
     $shoptalk_account->visible_panel = SHOPTALK_GROUP;			#  Set active tab
     $shoptalk_account->query_groups($kp, $link);				#  Get this user's groups

     $outputstring  = 'ok|';									#  Build the output string
     $outputstring .= $shoptalk_account->draw($kp, $link);		#  Render the Account panels

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('account-rfshgroup', $outputstring);

 print $outputstring;

 ?>