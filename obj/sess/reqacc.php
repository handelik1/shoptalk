<?php

 /*************************************************************************************
  This is the account creation routine. A new user has submitted information necessary
  to request an account. We check these data against existing records and either create
  the new account or return an error message.

  We check the following things for every request:

  1.  Does password meet our requirements for length and variety?
      - At least 8 characters
      - At least 1 alphabetic character
      - At least 1 digit
  2.  Do password and confirm-password match?
  3.  Is the given email a valid email?
  4.  Is the requested user name spoken for already?
  5.  Do we already have an account credited to the given email?
  ************************************************************************************/

 session_start();

 require_once('./dbutils.php');									#  Contains DB helper functions
 require_once('../interface/chat/chatstage.php');
 require_once('../interface/chat/chattoolbar.php');
 require_once('../interface/nav.php');
 require_once('../interface/mail/mailbox.php');
 require_once('../interface/mail/mailtoolbar.php');

 $send_request = preg_replace("/[^A-Za-z]/", '', $_POST['sendRequest']);
																#  Consider cases like "Jean-Paul",
																#  "Marie France", "Jos&#233; Ma&#241;uel"
 $firstname = preg_replace('/[^A-Za-z0-9\-\s\&\#\;]/', '', htmlentities($_POST['fname']));
 $lastname = preg_replace('/[^A-Za-z0-9\-\s\&\#\;]/', '', htmlentities($_POST['lname']));

 $email = preg_replace('/[^0-9A-Za-z\.\@\-\_]/', '', $_POST['email']);
																#  Consider cases like "Montclair State University",
																#  "M.S.U.", "Rutgers, Camden", "University of Texas: Austin"
 $institution = preg_replace('~[^A-Za-z0-9\-\s\&\#\;\.\:]~', '', htmlentities($_POST['inst']));

 $uname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['uname'], ENT_QUOTES));
 if(strlen($uname) > 64)										#  Trim uname to permissible length
   $uname = substr($uname, 0, 64);

 $pword = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['pword'], ENT_QUOTES));

 $cnfpword = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['cnfpword'], ENT_QUOTES));

 if(strcmp($send_request, 'icanHaZ') == 0)						#  Run only if request came from JS
   {
     if(strlen($pword) < 8 ||
        strlen(preg_replace('/[^0-9]/', '', $pword)) < 1 ||
        strlen(preg_replace('/[^A-Za-z]/', '', $pword)) < 1 )
       die('error|Password must meet security criteria');		#  Password no good

     if(strcmp($pword, $cnfpword) != 0)
       die('error|Password must match confirmation');			#  Passwords not equal

     if(strlen(filter_var($email, FILTER_VALIDATE_EMAIL)) == 0)
       die('error|Please enter a valid email address');			#  Email no good

     connect_to_db($link);										#  Connect to MySQL

     $query = 'SELECT kp FROM users WHERE uname = "'.$uname.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num > 0)
       die('error|This user name is already spoken for');		#  Username is in use already

     $query = 'SELECT kp FROM users WHERE email = "'.$email.'";';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbread');
       }
     $num = mysql_num_rows($result);
     if($num > 0)												#  This email is already a member
       die('error|This email already has a ShopTalk account registered. Please check your records and sign in.');

     /*  Success!  By this point, we have cleared all the hurdles and can create the account */

     $salt = hash('sha256', uniqid(rand(), true));				#  Compute the salt

     $query  = 'INSERT INTO users(uname, pword, salt, created, ';
     $query .= 'first_name, last_name, institution, email, last_access, logged_in) ';
     $query .= 'VALUES("'.$uname.'", ';							#  Store uname
     $query .= '"'.(hash('sha256', $pword)).$salt.'", ';		#  Store hashed, salted pword
     $query .= '"'.$salt.'", ';									#  Store salt
     $query .= $current_time.', ';								#  Store account creation time
     $query .= '"'.$firstname.'", "'.$lastname.'", ';
     $query .= '"'.$institution.'", "'.$email.'", '.$current_time.', TRUE);';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbinsert');
       }

     $shoptalk_nav = new Nav();
     $shoptalk_mailbox = new Mailbox();
     $shoptalk_mailbox->set_fname($firstname);					#  Needed for greeting
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