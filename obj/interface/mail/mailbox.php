<?php

 /*************************************************************************************
  Class definition for the center-stage mailbox/homepage div of ShopTalk
  ************************************************************************************/
 define('SHOPTALK_INBOX', 0);
 define('SHOPTALK_OUTBOX', 1);

 class Mailbox
   {
     public $uname;								#  Username of this account
     public $fname;								#  Human first name
     public $lname;								#  Human last name
     public $inbox_arr;							#  Array of message-info arrays
     public $outbox_arr;						#  Array of message-info arrays
     public $visible_box;						#  Indication of which box is visible
     public $time_zone;							#  This mailbox's time zone

     public function __construct()
       {
         $this->inbox_arr = array();			#  Initialize array
         $this->outbox_arr = array();			#  Initialize array
         $this->visible_box = SHOPTALK_INBOX;	#  Inbox displayed by default
         $this->time_zone = 0;					#  Default to Greenwich Mean Time
       }

     public function set_uname($uname)
       {
         $this->uname = $uname;
       }

     public function set_fname($fname)
       {
         $this->fname = $fname;
       }

     public function set_lname($lname)
       {
         $this->lname = $lname;
       }

     public function set_timezone($z)
       {
         $this->time_zone = $z;
       }

     public function set_visible_inbox()
       {
         $this->visible_box = SHOPTALK_INBOX;
       }

     public function set_visible_outbox()
       {
         $this->visible_box = SHOPTALK_OUTBOX;
       }

     public function draw($kp = null, &$link = null)
       {
         $str  = '<div id="mailbox_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
         $str .=       '<div>';
         $str .=         '<h2 class="white">Welcome, '.$this->fname.'!</h2>';
         $str .=       '</div>';
         $str .=       '<div id="mailbox">';	#  Set up the container now and fill it in later
         if($kp === null || $link === null)
           $str .=       '<p>Loading...</p>';
         else
           {
             $str .=     '<ul class="nav nav-tabs">';
             if($this->visible_box == SHOPTALK_INBOX)
               $str .=     '<li id="inbox_tab" class="active"><a href="javascript:;">Inbox</a></li>';
             else
               $str .=     '<li id="inbox_tab"><a href="javascript:;" onclick="hideOutbox(); showInbox();">Inbox</a></li>';
             if($this->visible_box == SHOPTALK_OUTBOX)
               $str .=     '<li id="outbox_tab" class="active"><a href="javascript:;">Outbox</a></li>';
             else
               $str .=     '<li id="outbox_tab"><a href="javascript:;" onclick="hideInbox(); showOutbox();">Outbox</a></li>';
             $str .=     '</ul>';
             $str .=     $this->retrieve_inbox($kp, $link);
             $str .=     $this->retrieve_outbox($kp, $link);
           }
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->compose_mail_modal();	#  when needed
         $str .= $this->message_viewer_modal();
         $str .= $this->delete_mail_modal();

         return $str;
       }

     //  Build the message viewer modal
     public function message_viewer_modal()
       {
         $str  = '<div class="modal fade" id="viewmail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="viewmail-panel">';
         $str .=     '<div class="credential-form" id="viewmail-form">';
												#  Message Subject goes here
         $str .=       '<h2 id="viewmail_subject" class="sign-in font-24 modal-header mail_subject"></h2>';
         $str .=       '<hr style="margin-top: 0px"></hr>';
												#  To or From goes here
         $str .=       '<label id="viewmail_label" class="credential-label" style="margin-top: 0px"></label>';
												#  Recipient or Sender goes here
         $str .=       '<input id="viewmail_username" class="reg-credential" type="text" readonly>';

         $str .=       '<label class="credential-label">Date:</label>';
												#  Send date goes here
         $str .=       '<input id="viewmail_date" class="reg-credential" type="text" readonly>';

         $str .=       '<textarea class="viewmail-textarea" rows="8" cols="50" id="viewmail_body" readonly></textarea>';

         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     //  Build the composition modal
     public function compose_mail_modal()
       {
         $str  = '<div class="modal fade" id="newmail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="newmail-panel">';
         $str .=     '<div class="credential-form" id="newmail-form">';
         $str .=       '<h2 class="sign-in font-24 modal-header mail_subject" id = "modal-header">New Message</h2>';
         $str .=       '</br>';

         $str .=       '<label class="credential-label" style="margin-top: 0px">To:</label>';
         $str .=       '<input class="newmail-credential" id="newmail_receiver" type="text">';

         $str .=       '<label class="credential-label">Subject:</label>';
         $str .=       '<input class="newmail-credential" id="newmail_subject" type="text">';

         $str .=       '<label class="credential-label">Message:</label>';
         $str .=       '<textarea class="newmail-textarea" rows="8" cols="50" id="newmail_body"></textarea>';

         $str .=       '<input type="submit" class="btn-default register-button" id="newmail_send" value="Send" onclick="sendMail();">';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     //  Build the mail deletion modal
     public function delete_mail_modal()
       {
         $str  = '<div class="modal fade" id="delmail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="delmail-panel">';
         $str .=     '<div class="credential-form" id="delmail-form">';
												#  Delete Selected Message/Delete Selected Messages goes here
         $str .=       '<h2 id="delmail_header" class="sign-in font-24 modal-header mail_subject"></h2>';
         $str .=       '<hr style="margin-top: 0px"></hr>';
												#  "Are you sure?" message... goes here
         $str .=       '<label id="delmail_label" class="credential-label" style="margin-top: 0px"></label>';
												#  Delete button goes here
         $str .=       '<input type="submit" class="btn-default register-button" id="delmail_submit" value="Delete" onclick="deleteMail();">';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     //  Given a user's KP and an established link, retrieve the user's
     //  person-to-person inbox.
     //  UNLESS AN ERROR OCCURS, THIS FUNCTION DOES NOT CLOSE THE LINK!
     public function retrieve_inbox($kp, &$link)
       {
         $query  = 'SELECT * FROM messages';
         $query .= ' WHERE receiver = '.$kp.' AND visible_to_receiver = TRUE';
         $query .= ' ORDER BY time_stamp DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);				#  Some kind of read error
             $str = '';
             return $str;
           }
												#  Inbox header
         if($this->visible_box == SHOPTALK_INBOX)
           $str= '<table id="inbox" class="table table-striped">';
         else
           $str= '<table id="inbox" class="table table-striped" hidden>';
         $str .=   '<thead>';
         $str .=     '<tr>';
         $str .=       '<th class="white">From</th>';
         $str .=       '<th class="white">Name</th>';
         $str .=       '<th class="white">Subject</th>';
         $str .=       '<th class="white">Received</th>';
         $str .=       '<th></th>';				#  Column for check marks
         $str .=     '</tr>';
         $str .=   '</thead>';
         $str .=   '<tbody>';

         if(mysql_num_rows($result) > 0)		#  Are there any messages?
           {
             $unique_senders = array();			#  Build list of unique senders.

             # People typically hear from the same correspondents several times, so let's
             # minimize name look-ups by first building a list of all an inbox's senders.

             while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
               {
                 $date = $this->format_date(intval($row['time_stamp']) + $this->time_zone * 3600);

                 array_push($this->inbox_arr, array('msgid' => intval($row['kp']),
                                                    'date' => $date,
                                                    'sender' => intval($row['sender']),
                                                    'subject' => $row['subject'],
                                                    'read' => (intval($row['been_read']) == 1) ));

                 if(!array_key_exists(intval($row['sender']), $unique_senders))
                   {
                     $unique_senders[intval($row['sender'])] = array('uname' => '',
                                                                     'fname' => '',
                                                                     'lname' => '');
                   }
               }
												#  Here is where we match unique user KPs
												#  to the names which will appear on the
												#  "envelope."
             foreach($unique_senders as $k => $v)
               {
                 $query  = 'SELECT uname, first_name, last_name FROM users';
                 $query .= ' WHERE kp = '.$k.';';
                 $result = mysql_query($query, $link);
                 if($result == false)
                   {
                     mysql_close($link);		#  Some kind of read error
                     $str = '';
                     return $str;
                   }
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
             									#  Store username and human name under the
             									#  same KP so that $unique_senders has
             									#  key: 'kp' => value: uname,
             									#                      first_name,
             									#                      last_name
                 $unique_senders[$k]['uname'] = $ret['uname'];
                 $unique_senders[$k]['fname'] = $ret['first_name'];
                 $unique_senders[$k]['lname'] = $ret['last_name'];
               }

             for($i = 0; $i < count($this->inbox_arr); $i++)
               {
                 #  Each of these rows is a clickable link to view the message.
                 #  (EXCEPT the checkbox at the end--hence, each <TD> has to be clickable)
                 if($this->inbox_arr[$i]['read'])
                   $str .= '<tr id="imsg'.$this->inbox_arr[$i]['msgid'].'" class="shop-talk-read-msg">';
                 else
                   $str .= '<tr id="imsg'.$this->inbox_arr[$i]['msgid'].'" class="shop-talk-unread-msg">';
												#  Show message FROM user
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->inbox_arr[$i]['msgid'].', true);">';
												#  Display sender information
                 $str .=        $unique_senders[ $this->inbox_arr[$i]['sender'] ]['uname'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->inbox_arr[$i]['msgid'].', true);">';
                 $str .=        $unique_senders[ $this->inbox_arr[$i]['sender'] ]['fname'].' ';
                 $str .=        $unique_senders[ $this->inbox_arr[$i]['sender'] ]['lname'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->inbox_arr[$i]['msgid'].', true);">';
                 $str .=        $this->inbox_arr[$i]['subject'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->inbox_arr[$i]['msgid'].', true);">';
                 $str .=        $this->inbox_arr[$i]['date'].'</td>';
                 $str .=   '<td><input type="checkbox" id="cb_imsg'.$this->inbox_arr[$i]['msgid'].'" ';
                 $str .=        'value="'.$this->inbox_arr[$i]['msgid'].'"></td>';

                 $str .= '</tr>';
               }
           }

         $str .=   '</tbody>';
         $str .= '</table>';

         return $str;
       }

     //  Given a user's KP and an established link, retrieve the user's
     //  person-to-person outbox.
     //  UNLESS AN ERROR OCCURS, THIS FUNCTION DOES NOT CLOSE THE LINK!
     public function retrieve_outbox($kp, &$link)
       {
         $query  = 'SELECT * FROM messages';
         $query .= ' WHERE sender = '.$kp.' AND visible_to_sender = TRUE';
         $query .= ' ORDER BY time_stamp DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);				#  Some kind of read error
             $str = '';
             return $str;
           }
												#  Outbox header
         if($this->visible_box == SHOPTALK_OUTBOX)
           $str= '<table id="outbox" class="table table-striped">';
         else
           $str= '<table id="outbox" class="table table-striped" hidden>';
         $str .=   '<thead>';
         $str .=     '<tr>';
         $str .=       '<th class="white">To</th>';
         $str .=       '<th class="white">Name</th>';
         $str .=       '<th class="white">Subject</th>';
         $str .=       '<th class="white">Sent</th>';
         $str .=       '<th></th>';				#  Column for checkmarks
         $str .=     '</tr>';
         $str .=   '</thead>';
         $str .=   '<tbody>';

         if(mysql_num_rows($result) > 0)		#  Are there any messages?
           {
             $unique_receivers = array();		#  Build list of unique senders.

             # People typically hear from the same correspondents several times, so let's
             # minimize name look-ups by first building a list of all an outbox's receivers.

             while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
               {
                 $date = $this->format_date(intval($row['time_stamp']) + $this->time_zone * 3600);

                 array_push($this->outbox_arr, array('msgid' => intval($row['kp']),
                                                     'date' => $date,
                                                     'receiver' => intval($row['receiver']),
                                                     'subject' => $row['subject'],
                                                     'read' => (intval($row['been_read']) == 1) ));

                 if(!array_key_exists(intval($row['receiver']), $unique_receivers))
                   {
                     $unique_receivers[intval($row['receiver'])] = array('uname' => '',
                                                                         'fname' => '',
                                                                         'lname' => '');
                   }
               }
												#  Here is where we match unique user KPs
												#  to the names which will appear on the
												#  "envelope."
             foreach($unique_receivers as $k => $v)
               {
                 $query  = 'SELECT uname, first_name, last_name FROM users';
                 $query .= ' WHERE kp = '.$k.';';
                 $result = mysql_query($query, $link);
                 if($result == false)
                   {
                     mysql_close($link);		#  Some kind of read error
                     $str = '';
                     return $str;
                   }
                 $ret = mysql_fetch_array($result, MYSQL_ASSOC);
             									#  Store username and human name under the
             									#  same KP so that $unique_senders has
             									#  key: 'kp' => value: uname,
             									#                      first_name,
             									#                      last_name
                 $unique_receivers[$k]['uname'] = $ret['uname'];
                 $unique_receivers[$k]['fname'] = $ret['first_name'];
                 $unique_receivers[$k]['lname'] = $ret['last_name'];
               }

             for($i = 0; $i < count($this->outbox_arr); $i++)
               {
                 #  Each of these rows is a clickable link to view the message.
                 #  (EXCEPT the checkbox at the end--hence, each <TD> has to be clickable)
                 $str .= '<tr id="omsg'.$this->outbox_arr[$i]['msgid'].'" class="shop-talk-msg">';
												#  Show message FROM user
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->outbox_arr[$i]['msgid'].', false);">';
												#  Display sender information
                 $str .=        $unique_receivers[ $this->outbox_arr[$i]['receiver'] ]['uname'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->outbox_arr[$i]['msgid'].', false);">';
                 $str .=        $unique_receivers[ $this->outbox_arr[$i]['receiver'] ]['fname'].' ';
                 $str .=        $unique_receivers[ $this->outbox_arr[$i]['receiver'] ]['lname'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->outbox_arr[$i]['msgid'].', false);">';
                 $str .=        $this->outbox_arr[$i]['subject'].'</td>';
				 $str .=   '<td data-toggle="modal" data-target="#viewmail-modal" ';
                 $str .=       'onclick="readMail('.$this->outbox_arr[$i]['msgid'].', false);">';
                 $str .=        $this->outbox_arr[$i]['date'].'</td>';
                 $str .=   '<td><input type="checkbox" id="cb_omsg'.$this->outbox_arr[$i]['msgid'].'" ';
                 $str .=        'value="'.$this->outbox_arr[$i]['msgid'].'"></td>';

                 $str .= '</tr>';
               }
           }

         $str .=   '</tbody>';
         $str .= '</table>';

         return $str;
       }

     //  Format a UNIX timestamp as a date for envelop information
     public function format_date($unix_time)
       {
         $retstr = gmdate('j F Y, G:i:s', $unix_time);
         return $retstr;
       }
   }

 ?>