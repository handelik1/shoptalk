<?php

 /*************************************************************************************
  This is the account login routine. An established user has submitted credentials.
  We check these data against existing records and either setup the welcome page or
  return an error message.

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

 $uname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['uname'], ENT_QUOTES));
 if(strlen($uname) > 64)										#  Trim uname to permissible length
   $uname = substr($uname, 0, 64);

 $pword = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['pword'], ENT_QUOTES));

 if(strcmp($send_request, 'sWoRdFiSH') == 0)					#  Run only if request came from JS
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
       die('error|Incorrect user name or password');			#  Uname not found
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $salt = $ret['salt'];
																#  Compare hashed pword
     $query  = 'SELECT kp, first_name, last_name, logged_in FROM users';
     $query .= ' WHERE uname = "'.$uname.'" AND pword = "'.(hash('sha256', $pword)).$salt.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num == 0)
       die('error|Incorrect user name or password');			#  Uname not found
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $kp = $ret['kp'];
     $fname = $ret['first_name'];
     $lname = $ret['last_name'];
     $logged_in = (intval($ret['logged_in']) == 1);

     $query  = 'UPDATE users SET logged_in = TRUE,';			#  Set logged_in flag in DB
     $query .= ' last_access = '.$current_time;					#  Update access timestamp in DB
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     $shoptalk_nav = new Nav();
     $shoptalk_mailbox = new Mailbox();
     $shoptalk_mailbox->set_fname($fname);						#  Needed for greeting
     $shoptalk_mailtoolbar = new MailToolbar();

     $outputstring  = 'welcome|';								#  Build the output string

     $outputstring .= '<div id="homepage">';
     $outputstring .=   '<div class="container-fluid">';
     $outputstring .=     '<div class="row">';
     $outputstring .=       $shoptalk_nav->draw();				#  Render the Nav
     $outputstring .=       $shoptalk_mailbox->draw($kp, $link);#  Render the Mailbox
     $outputstring .=       $shoptalk_mailtoolbar->draw();		#  Render the Mailbox Toolbar
     $outputstring .=     '</div>';
     $outputstring .=   '</div>';
     $outputstring .= '</div>';

     $_SESSION['uname'] = $uname;								#  Set session copy of uname
     $_SESSION['pword'] = $pword;								#  Set session copy of pword
     $_SESSION['logged_in'] = true;								#  Set session flag

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('bad_input');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>