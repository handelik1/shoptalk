<?php

 /*************************************************************************************
  Class definition for a single online utterance or drawing event in ShopTalk
  ************************************************************************************/
 class LiveEvent
   {
     public $msg;								#  The message (string) or drawing instruction
     public $speaker;							#  UNAME of the speaker (string)
     public $time_stamp;						#  UNIX time of the utterance
     public $is_text;							#  Boolean: whether text (true) / drawing (false)

     public function __construct($m, $s, $t, $x)
       {
         $this->msg = $m;
         $this->speaker = $s;
         $this->time_stamp = $t;
         $this->is_text = $x;
       }

     public function draw()
       {
         if($this->is_text)
           $str = '<tr class="shoptalk-chattext">';
         else
           $str = '<tr class="shoptalk-chatdraw">';
         $str .= '<td class="shoptalk-chatspeaker">'.$this->speaker.'</td>';
         $str .= '<td class="shoptalk-chatmessage">'.$this->msg.'</td>';
         $str .= '</tr>';

         return $str;
       }
   }

 /*************************************************************************************
  Class definition for the active chat session window in ShopTalk
  ************************************************************************************/
 class Chat
   {
     public $session;							#  KP of the chat session

     public function __construct($s)
       {
         $this->session = $s;
       }

     public function draw($kp = null, &$link = null)
       {
         $str  = '<div id="chat_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
												#  Transcript and Drawing
         $str .=       '<div class="container-fluid">';
         $str .=         '<div class="row">';
         $str .=           '<div class="col-sm-12">';
         										#  The Canvas
         $str .=             '<div id="blackboard" class="drawing-area">';
         $str .=               '<canvas id="canvas"></canvas>';
         $str .=             '</div>';
         										#  The Transcript
         $str .=             '<div id="chat_transcript" class="chat-area">';
         $str .=               '<table id="transcript_table" class="table">';
         $str .=                 '<thead>';
         $str .=                   '<tr><th class="shoptalk-chat-speaker-col"></th>';
         $str .=                   '<th class="shoptalk-chat-text-col"></th>';
         $str .=                 '</tr></thead>';
         $str .=                 '<tbody id="transcript_table_body">';
         										#  Text ends up here as a table row
         $str .=                 '</tbody>';
         $str .=               '</table>';
         $str .=             '</div>';
         $str .=           '</div>';
         $str .=         '</div>';
         $str .=       '</div>';
												#  Typing
         $str .=       '<div class="container-fluid">';
         $str .=         '<div class="row">';
         $str .=           '<div class="col-sm-10">';
         $str .=             '<input class="chat-text" id="chat_msg" type="text" name="message"/>';
         $str .=           '</div>';
         $str .=           '<div class="col-sm-2">';
         $str .=             '<input class="btn btn-primary" onclick="say();" type="button" value="Go"/>';
         $str .=           '</div>';
         $str .=         '</div>';
         $str .=       '</div>';

         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->invite_member_modal();	#  when needed
         $str .= $this->expel_member_modal();

         return $str;
       }

     //  Fetch the forgone transcript and write it into the chat
     public function draw_fetch($kp = null, &$link = null)
       {
         $str  = '<div id="chat_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
												#  Transcript and Drawing
         $str .=       '<div class="container-fluid">';
         $str .=         '<div class="row">';
         $str .=           '<div class="col-sm-12">';
         										#  The Canvas
         $str .=             '<div id="blackboard" class="drawing-area">';
         $str .=               '<canvas id="canvas"></canvas>';
         $str .=             '</div>';
         										#  The Transcript
         $str .=             '<div class = "chat-transcript-header-row"></div>';
         $str .=             '<div id="chat_transcript" class="chat-area">';
         $str .=               '<table id="transcript_table" class="table">';
         $str .=                 '<thead>';
         $str .=                   '<tr>';
         $str .=                     '<th class="shoptalk-chat-speaker-col"></th>';
         $str .=                     '<th class="shoptalk-chat-text-col"></th>';
         $str .=                 '</tr></thead>';
         $str .=                 '<tbody id="transcript_table_body">';
         										#  Text ends up here as a table row
         $str .=                 $this->draw_transcript_addition($link, 0);
         $str .=                 '</tbody>';
         $str .=               '</table>';
         $str .=             '</div>';
         $str .=           '</div>';
         $str .=         '</div>';
         $str .=       '</div>';
												#  Typing
         $str .=       '<div class="container-fluid">';
         $str .=         '<div class="row">';
         $str .=           '<div class="col-sm-10">';
         $str .=             '<input class="chat-text" id="chat_msg" type="text" name="message"/>';
         $str .=           '</div>';
         $str .=           '<div class="col-sm-2">';
         $str .=             '<input class="btn btn-primary" onclick="say();" type="button" value="Go"/>';
         $str .=           '</div>';
         $str .=         '</div>';
         $str .=       '</div>';

         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->invite_member_modal();	#  when needed
         $str .= $this->expel_member_modal();

         return $str;
       }

     public function invite_member_modal()
       {
         $str  = '<div class="modal fade" id="invitechat-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="invitechat-panel">';
         $str .=     '<div class="credential-form" id="invitechat-form">';
         $str .=       '<h2 class="sign-in font-24 modal-header chat_subject">Grant Chat Access</h2>';
         $str .=       '<table class = "table chat-invite-table">';
         $str .=       '<tr><td class = "chat-invite-label"><label class="credential-label" style="margin-top: 0px">User: </label></td>';
         $str .=       '<td class = "chat-invite-text"><input class="invitechat-credential" id="invitechat_membersearch"';
         $str .=             ' onkeyup="queryInviteUsers(this.value);" type="text"/></td>';
         $str .=       '<td><a href="javascript:;" onclick="addChatInviteMember();">';
         $str .=         '<img src="./img/plus.png" alt="Invite"/>';
         $str .=       '</a></td></tr>';
         $str .=       '<br/>';

         $str .=       '<tr><td class = "chat-invite-label"><label class="credential-label" style="margin-top: 0px">Group: </label></td>';
         $str .=       '<td class = "chat-invite-text"><input class="invitechat-credential" id="invitechat_groupsearch"';
         $str .=             ' onkeyup="queryInviteGroups(this.value);" type="text"/></td>';
         $str .=       '<td><a href="javascript:;" onclick="addChatInviteGroup();">';
         $str .=         '<img src="./img/plus.png" alt="Invite group"/>';
         $str .=       '</a></td></tr>';
         $str .=       '</table>';
         $str .=       '<div id="chatinvite_tagfield">';
         $str .=        '<h4 id = "member-name">Member Name</h4>';
         $str .=         '<div class = "chat-wrapper">';
         $str .=          '<table id="chatinvite" class="table table-striped">';
         $str .=            '<thead>';
         $str .=              '<tr>';
         $str .=                '<th></th>';		#  Column for "remove" buttons
         $str .=              '</tr>';
         $str .=            '</thead>';
         $str .=              '<tbody id="chatinvite_tbody">';
         $str .=              '</tbody>';
         $str .=         '</table>';
         $str .=        '</div>';
         $str .=       '</div>';

         $str .=       '<input id="invitechatkey" type="hidden" value="'.$this->session.'"/>';

         $str .=       '<input type="submit" class="btn-default register-button" id="invitechat_save"';
         $str .=             ' value="Invite" onclick="addMember();">';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     public function expel_member_modal()
       {
         $str  = '<div class="modal fade" id="expelchat-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="expelchat-panel">';
         $str .=     '<div class="credential-form" id="expelchat-form">';
         $str .=       '<h2 class="sign-in font-24">Revoke Chat Access</h2>';
         $str .=       '<hr style="margin-top: 0px"></hr>';

         $str .=       '<input type="submit" class="btn-default register-button" id="expelchat_save"';
         $str .=             ' value="Expel" onclick="removeMember();">';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     public function draw_transcript_addition(&$link, $time)
       {
         $str = '';								#  Build transcript addition string

         $unique_speakers = array();			#  Minimize the number of uname lookups!
         $arr = $this->retrieve_past($link, $time);

         for($i = 0; $i < count($arr); $i++)
           {
             $x = intval($arr[$i]->speaker);
             if(!array_key_exists($x, $unique_speakers))
               {
                 $query  = 'SELECT uname FROM users WHERE kp = '.$x.';';
                 $result = mysql_query($query, $link);
                 if($result == false)
                   {
                     mysql_close($link);
                     croak('dbread');
                   }
                 $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
                 $unique_speakers[$x] = $ret['uname'];
                 $arr[$i]->speaker = $ret['uname'];
               }

             $arr[$i]->speaker = $unique_speakers[$x];

             $str .= $arr[$i]->draw();
           }

         return $str;
       }

     //  Retrieve all events from this session past given '$time'
     public function retrieve_past(&$link, $time)
       {
         $arr = array();

         $query  = 'SELECT * FROM live_events';
         $query .= ' WHERE time_stamp >= '.$time;
         $query .= ' AND chat = '.$this->session;
         $query .= ' ORDER BY time_stamp ASC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
           										#  Initially, 'speaker' will be an int stored as a String!
             array_push($arr, new LiveEvent( $row['content'],
                                             $row['speaker'],
                                             $row['time_stamp'],
                                             (strcmp($row['is_text'], '1') == 0)
                                           ));
           }

         return $arr;
       }
   }

 ?>