<?php

 /*************************************************************************************
  This is the person-to-person message display request routine. A request has been made
  by a registered user to view a note either sent to or sent by this same user. We check
  the given credentials against existing records and either retrieve a row in the messages
  table or return an error.

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

 $msgid = preg_replace("~[^0-9]~", '', $_POST['m']);			#  Message id requested...
 																#  ...by user who is sender/receiver
 $requester_receiver = (intval(preg_replace("~[^0-1]~", '', $_POST['b'])) == 1);

 debug_log_inputs('mail-readmail', $_POST);

 if(strcmp($send_request, 'GrenZZrGneGne') == 0)				#  Run only if request came from JS
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
     $query  = 'SELECT kp, time_zone FROM users';
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
     $kp = $ret['kp'];											#  Requesting user's KP
     $timezone = intval($ret['time_zone']);						#  Requesting user's time zone

     if($requester_receiver)									#  Lookup in user's INBOX
       {
         $query  = 'SELECT sender, time_stamp, subject, content';
         $query .= ' FROM messages WHERE receiver = '.$kp.' AND kp = '.$msgid.';';
       }
     else														#  Lookup in user's OUTBOX
       {
         $query  = 'SELECT receiver, time_stamp, subject, content';
         $query .= ' FROM messages WHERE sender = '.$kp.' AND kp = '.$msgid.';';
       }
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     if(mysql_num_rows($result) != 1)
       {
         mysql_close($link);
         croak('bad_input');
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $subject = $ret['subject'];								#  Message subject
     $label = ($requester_receiver) ? 'From:' : 'To:';			#  Label
     $other_party = ($requester_receiver) ? $ret['sender'] : $ret['receiver'];
     $date = intval($ret['time_stamp']) + $timezone * 3600;		#  Make time-zone relative
     $msg = $ret['content'];									#  Message content

     $query = 'SELECT uname, first_name, last_name FROM users WHERE kp = '.$other_party.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $ret = mysql_fetch_array($result, MYSQL_ASSOC);
     $other_party = $ret['uname'];
     $other_party_fname = $ret['first_name'];
     $other_party_lname = $ret['last_name'];

     $mailbox = new Mailbox();									#  Only created for date formatting
     $date = $mailbox->format_date($date);						#  Format date

     /* All variables are now where we want them. Mark the retrieved message as read, */
     /* and update the user's last_access time stamp. */

																#  Update message's "read" status
     $query  = 'UPDATE messages SET been_read = TRUE';
     $query .= ' WHERE kp = '.$msgid.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }
																#  Update user's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     $outputstring  = 'ok|';
     $outputstring .= $subject.'|';								#  1: Subject
     $outputstring .= $label.'|';								#  2: To/From
     $outputstring .= $other_party.'|';							#  3: Sender/Receiver
     $outputstring .= $date.'|';								#  4: Date
     $outputstring .= $msg;										#  5: Message contents

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('mail-readmail', $outputstring);

 print $outputstring;

 ?>