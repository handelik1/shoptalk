<?php

 /*************************************************************************************
  This is the user group definition routine. A request has been made by a registered user
  to define a group of other ShopTalk users.
  We check the given credentials against existing records, check the validity of all
  user names supplied, and either save a new record to the "groups" table or return an
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
																#  Number of members to group together
 $c = intval(preg_replace("~[^0-9]~", '', htmlentities($_POST['c'], ENT_QUOTES)));
																#  Group name
 $gname = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '', htmlentities($_POST['gname'], ENT_QUOTES));
 $gmembers = array();											#  To contain user names

 debug_log_inputs('account-defgroup', $_POST);

 if(strcmp($send_request, 'TheseFragmentsIHaveShored') == 0)	#  Run only if request came from JS
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
     $query  = 'SELECT kp, uname FROM users';
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
     $kp = intval($ret['kp']);
     $sys_uname = $ret['uname'];
																#  Update user's time stamp
     $query  = 'UPDATE users SET last_access = '.$current_time;
     $query .= ' WHERE kp = '.$kp.';';
     $result = mysql_query($query, $link);
     if($result == false)
       {
         mysql_close($link);
         croak('dbupdate');
       }

     for($i = 0; $i < $c; $i++)									#  Iterate over claimed members,
       {														#  make sure they're all real.
         if(isset($_POST['m'.$i]))								#  Claimed element actually present?
           {
             $candidate = preg_replace("~[^0-9A-Za-z\.\s\&\;\#\/\-\_\,\:\?\!]~", '',
                                       htmlentities($_POST['m'.$i], ENT_QUOTES));
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
                 if($kp != $ckp)								#  Disallow adding SELF to group
                   array_push($gmembers, $ckp);
               }
           }
       }
     if(count($gmembers) > 0)									#  Do not add empty list to DB
       {
         $memstring = '';										#  Build CSV
         for($i = 0; $i < count($gmembers); $i++)
           $memstring .= $gmembers[$i].',';
         $memstring = substr($memstring, 0, -1);				#  Snip last comma

         $query  = 'INSERT INTO groups(defined_by, name, members)';
         $query .= ' VALUES('.$kp.', "'.$gname.'", "'.$memstring.'");';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbinsert');
           }
       }

     $outputstring = 'ok';

     mysql_close($link);										#  Close the link to DB
   }
 else
   croak('baddata');

 $_SESSION['last_access'] = $current_time;						#  Set its last access time

 debug_log_outputs('account-defgroup', $outputstring);

 print $outputstring;

 ?>