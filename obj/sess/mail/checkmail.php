<?php

 /*************************************************************************************
  This is the mail refresh routine. A request has been made to re-display a registered
  user's mailbox. We check the given credentials against existing records and either
  setup the page or return an error message.

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
 																#  Whether we scrub from the requesting user's
 																#  inbox (true) or outbox (false)
 $ibox = (intval(preg_replace("~[^0-1]~", '', $_POST['b'])) == 1);

 debug_log_inputs('mail-checkmail', $_POST);

 if(strcmp($send_request, 'DeathTaxzNmAIL') == 0)				#  Run only if request came from JS
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
     $query  = 'SELECT kp, first_name, time_zone FROM users';
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

     /* This differs from the homepage-rendering script in that we only want to return
        the mailbox, which will be re-drawn in place. */

     $shoptalk_mailbox = new Mailbox();
     $shoptalk_mailbox->set_fname($fname);						#  Needed for greeting
     $shoptalk_mailbox->set_timezone($time_zone);				#  Needed for dating messages
     if($ibox)
       $shoptalk_mailbox->set_visible_inbox();
     else
       $shoptalk_mailbox->set_visible_outbox();

     $outputstring  = 'ok|';									#  Build the output string
     $outputstring .= $shoptalk_mailbox->draw($kp, $link);		#  Render the Mailbox

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('mail-checkmail', $outputstring);

 print $outputstring;

 ?>