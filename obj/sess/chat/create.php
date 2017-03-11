<?php

 /*************************************************************************************
  This is the chat session creation routine. A registered user has requested that a new
  chat session be created and provided the required information.

  If the request succeeds, the DB is updated, and this script returns the markup for a
  chat session page to the front-end for display.

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

 if(strcmp($send_request, 'settumUPsettumMUP') == 0)			#  Run only if request came from JS
   {
     /*********************************************************************************************/
     mysqli_connect_db($link);									#  Connect to MySQL... in the mysqli way:
     															#  See note in dbutils.
     /*********************************************************************************************/
																#  Retrieve salt for uname
     $query = 'SELECT salt FROM users WHERE uname = "'.$uname.'";';
     $result = $link->query($query);
     if($result == false)
       {
         $link->close();
         croak('dbread');
       }
     if($link->affected_rows == 0)
       {
         $link->close();
         die('fatalerror|Incorrect user name or password');		#  Uname not found
       }
     $ret = $result->fetch_array(MYSQLI_ASSOC);
     $salt = $ret['salt'];
																#  Compare hashed pword
     $query  = 'SELECT kp FROM users';
     $query .= ' WHERE uname = "'.$uname.'" AND pword = "'.(hash('sha256', $pword)).$salt.'";';
     $result = $link->query($query);
     if($result == false)
       {
         $link->close();
         croak('dbread');
       }
     if($link->affected_rows == 0)
       {
         $link->close();
         die('fatalerror|Incorrect user name or password');		#  Uname not found
       }
     $ret = $result->fetch_array(MYSQLI_ASSOC);
     $kp = $ret['kp'];
     $fname = $ret['first_name'];
     $time_zone = intval($ret['time_zone']);
																#  Update user's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$kp.';';
     $result = $link->query($query);
     if($result == false)
       {
         $link->close();
         croak('dbupdate');
       }

     /*********************************************************************************************/
																#  Clean up received variables
     $session_title = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['t'], ENT_QUOTES));
     if(strlen($session_title) == 0)							#  Untitled sessions get default names
       $session_title = '<i>[Untitled]</i>';
     $session_as_leader = (strcmp(preg_replace('/[^0-1]/', '', $_POST['l']), '1') == 0);
																#  Number of access grants
     $session_access_count = intval(preg_replace("~[^0-9]~", '', $_POST['ac']));
     $session_access = array();									#  To contain user KPs

     for($i = 0; $i < $session_access_count; $i++)				#  Iterate over claimed members,
       {														#  make sure they're all real.
         if(isset($_POST['i'.$i]))								#  Claimed element actually present?
           {
             $candidate = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '',
                                       htmlentities($_POST['i'.$i], ENT_QUOTES));
             $query = 'SELECT kp FROM users WHERE uname = "'.$candidate.'";';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             if(mysql_num_rows($result) == 1)
               {
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
                 $ckp = intval($ret['kp']);						#  Candidate KP
                 if($kp != $ckp)								#  Disallow granting SELF access
                   array_push($session_access, $ckp);			#  (cuz you're the creator, duh)
               }
           }
       }
																#  Number of keywords
     $session_keyword_count = intval(preg_replace("~[^0-9]~", '', $_POST['kc']));
     $session_keywords = array();								#  To contain strings

     for($i = 0; $i < $session_keyword_count; $i++)				#  Iterate over claimed keywords.
       {
         if(isset($_POST['kw'.$i]))
           {
             $candidate = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '',
                                       htmlentities($_POST['kw'.$i], ENT_QUOTES));
             if(strlen($candidate) > 0)
               array_push($session_keywords, $candidate);
           }
       }

     $session_access_str = implode(',', $session_access);		#  Convert arrays into CSV Strings
     $session_keywords_str = implode(',', $session_keywords);

     /*********************************************************************************************/
																#  Build query to create session row
     $query  = 'INSERT INTO chats(title, host, created, session_leader, access, time_stamp, keywords)';
     $query .= ' VALUES(';
     $query .=          '"'.$session_title.'", ';				#  Session title
     $query .=          $kp.', ';								#  Session creator
     $query .=          $current_time.', ';						#  Time created
     if($session_as_leader)
       $query .=        $kp.', ';								#  Creator is leader
     else
       $query .=        'NULL, ';								#  Session is democracy
     if(strlen($session_access_str) > 0)
       $query .=        '"'.$session_access.'", ';				#  Session access
     else
       $query .=        'NULL, ';
     $query .=          $current_time;							#  Time stamp (same as time created)
     if(strlen($session_keywords_str) > 0)
       $query .=        ', '.$session_keywords_str.');';		#  Keywords
     else
       $query .=      ', NULL);';
     $result = $link->query($query);
     if($result == false)
       {
         $lin->close();
         croak('dbinsert');
       }
       															#  ***THIS*** is why we needed the mysqli!
     $new_sess_kp = $link->insert_id;							#  Save KP of newly inserted row

     /*********************************************************************************************/

	 $query  = 'UPDATE users SET in_session = '.$new_sess_kp;	#  Build query to commit user to session
	 $query .= ' WHERE kp = '.$kp.';';
     $result = $link->query($query);
     if($result == false)
       {
         $link->close();
         croak('dbupdate');
       }

     /*********************************************************************************************/

     $link->close();											#  Now we've got to close the mysqli
     connect_to_db($link);										#  and re-open it as a regular link
																#  because all the object routines
																#  expect this. Is this totally
																#  counter-intuitive? Cuz it is!
																#  Blame gratuitous updates!
     /*********************************************************************************************/
																#  User is now IN the chat just created.
     $shoptalk_nav = new Nav();									#  Build the replacement page
     $shoptalk_chat = new Chat($new_sess_kp);
     $shoptalk_chatsessbar = new ChatSessionToolbar($new_sess_kp);

     $outputstring  = 'ok|';									#  Build the output string
     $outputstring .= '<div id="homepage">';
     $outputstring .=   '<div class="container-fluid">';
     $outputstring .=     '<div class="row">';
     $outputstring .=       $shoptalk_nav->draw();				#  Render the Nav
     $outputstring .=       $shoptalk_chat->draw($kp, $link);	#  Render the Chatbox
     $outputstring .=       $shoptalk_chatsessbar->draw();		#  Render the Chatbox Toolbar
     $outputstring .=     '</div>';
     $outputstring .=   '</div>';
     $outputstring .= '</div>';

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 print $outputstring;

 ?>