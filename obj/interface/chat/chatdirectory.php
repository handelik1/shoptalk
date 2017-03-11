<?php

 /*************************************************************************************
  Class definition for the active chat session roster in ShopTalk
  ************************************************************************************/
 define('SHOPTALK_ACTIVE', 0);
 define('SHOPTALK_ARCHIVED', 1);

 class ChatDirectory
   {
     public $chat_host;							#  Array of ACTIVE chat sessions hosted by user
     public $chat_invitation;					#  Array of ACTIVE chat sessions to which user has been invited
     public $chat_public;						#  Array of ACTIVE chat sessions open to public

     public $chat_archived_host;				#  Array of ARCHIVED chat sessions hosted by user
     public $chat_archived_invitation;			#  Array of ARCHIVED chat sessions to which user has been invited
     public $chat_archived_public;				#  Array of ARCHIVED chat sessions open to public

     public $visible_panel;						#  Indication of which panel is visible

     public function __construct()
       {
         $this->chat_host = array();			#  Initialize arrays
         $this->chat_invitation = array();
         $this->chat_public = array();

         $this->chat_archived_host = array();
         $this->chat_archived_invitation = array();
         $this->chat_archived_public = array();

         $this->visible_panel = SHOPTALK_ACTIVE;#  Active session listing displayed by default
       }

     public function draw($kp = null, &$link = null)
       {
         $str  = '<div id="chatdir_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
         $str .=       '<div>';
         $str .=         '<h2>Chat Sessions</h2>';
         $str .=       '</div>';
         $str .=       '<div id="chatdir">';	#  Set up the container now and fill it in later
         if($kp === null || $link === null)
           $str .=       '<p>Loading...</p>';
         else
           {
             $str .=     '<ul class="nav nav-tabs">';
             if($this->visible_panel == SHOPTALK_ACTIVE)
               $str .=     '<li id="active_tab" class="active"><a href="javascript:;">Active</a></li>';
             else
               $str .=     '<li id="active_tab"><a href="javascript:;" onclick="hideArchivedChat(); showActive();">Active</a></li>';
             if($this->visible_panel == SHOPTALK_ARCHIVED)
               $str .=     '<li id="archivedchat_tab" class="active"><a href="javascript:;">Archived</a></li>';
             else
               $str .=     '<li id="archivedchat_tab"><a href="javascript:;" onclick="hideActive(); showArchivedChat();">Archived</a></li>';
             $str .=     '</ul>';

             $str .= $this->retrieve_active_chats($kp, $link);
             $str .= $this->retrieve_archived_chats($kp, $link);
           }
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->create_chat_modal();	#  when needed

         return $str;
       }

     //  Build the chat-creation modal
     public function create_chat_modal()
       {
         $str  = '<div class="modal fade" id="newchat-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="newchat-panel">';
         $str .=     '<div class="credential-form" id="newchat-form">';
         $str .=       '<h2 class="sign-in font-24 modal-header mail_subject">New Chat Session</h2>';
         $str .=       '<table class = "table chat-table">';
         $str .=       '<tr><td class = "quick-set-title"><label class="credential-label" style="margin-top: 0px">Session Title:</label></td>';
         $str .=       '<td><input class="newchat-credential" id="newchat_title" type="text"></td></tr>';
         $str .=       '<br/>';

         $str .=       '<tr><td class = "quick-set"><label class="credential-label">Quick Set-up: </label>';
         $str .=       '<a href="./docs/session_policy.html#leader" target="_blank" class = "help-button">[?]</a></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button chat-button"';
         $str .=         ' id="newchat_qset_leader" value="Session Leader" onclick="quickSet_leader();"></td></tr>';

         $str .=       '<br/>';

         $str .=       '<tr><td class = "quick-set"><label class="credential-label">Quick Set-up: </label>';
         $str .=       '<a href="./docs/session_policy.html#equals" target="_blank" class = "help-button">[?]</a></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button chat-button"';
         $str .=         ' id="newchat_qset_equal" value="Equal Standing" onclick="quickSet_democratic();"></td></tr>';

         $str .=       '<br/>';

         $str .=       '<tr><td><label class="credential-label">Custom Settings: </label></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button chat-button"';
         $str .=         ' id="newchat_create" value="Next" onclick="chatSetupPage(1);"></td></tr>';
         $str .=      '</table>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     /*********************************************************************************************
          Active Chat Session functions
      *********************************************************************************************/

     public function retrieve_active_chats($kp, &$link, $head = true)
       {
         if($head)
           {
             if($this->visible_panel == SHOPTALK_ACTIVE)
               $str = '<table id="active" class="table table-striped">';
             else
               $str = '<table id="active" class="table table-striped" hidden>';
           }
         else
           $str = '';
         $str .=   '<thead>';					#  Directory header
         $str .=     '<tr>';
         $str .=       '<th>Title</th>';
         $str .=       '<th>Host</th>';
         $str .=       '<th>Duration</th>';
         $str .=       '<th>Population</th>';
         $str .=     '</tr>';
         $str .=   '</thead>';
         $str .=   '<tbody>';
         $str .=     $this->list_active_hosted_chats($kp, $link);
         $str .=     $this->list_active_invited_chats($kp, $link);
         $str .=     $this->list_active_public_chats($kp, $link);
         $str .=   '</tbody>';
         if($head)
           $str .= '</table>';

         return $str;
       }

     public function list_active_hosted_chats($kp, &$link)
       {
         $query  = 'SELECT * FROM chats';		#  Build query for sessions LEAD by user with $kp
         $query .= ' WHERE session_leader = '.$kp.' ORDER BY time_stamp DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             $query = 'SELECT kp FROM users WHERE logged_in = TRUE AND in_session = '.$row['kp'].';';
             $sub_result = mysql_query($query, $link);
             if($sub_result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $num = mysql_num_rows($sub_result);

             array_push($this->chat_host, array('kp' => intval($row['kp']),
                                                'title' => $row['title'],
                                                'host' => intval($row['host']),
                                                'duration' => $this->format_time(intval($row['time_stamp']) - intval($row['created'])),
                                                'leader' => -1,
                                                'pop' => $num
                                               ));
           }

         $str = '';
         for($i = 0; $i < count($this->chat_host); $i++)
           {
             $str .= '<tr id="cs'.$this->chat_host[$i]['kp'].'" class="shop-talk-hosted-chat">';
             									#  Display chat session title
			 $str .=   '<td onclick="joinChat('.$this->chat_host[$i]['kp'].');">';
             $str .=        $this->chat_host[$i]['title'].'</td>';
             									#  Display hosted-by
			 $str .=   '<td onclick="joinChat('.$this->chat_host[$i]['kp'].');"><i>You</i></td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="joinChat('.$this->chat_host[$i]['kp'].');">';
             $str .=        $this->chat_host[$i]['duration'].'</td>';
             									#  Display session attendance
			 $str .=   '<td onclick="joinChat('.$this->chat_host[$i]['kp'].');">';
             $str .=        $this->chat_host[$i]['pop'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     public function list_active_invited_chats($kp, &$link)
       {
         $query  = 'SELECT * FROM chats';		#  Query for session open specifically to user with $kp
         $query .= ' WHERE FIND_IN_SET('.$kp.', access)';
         $query .= ' ORDER BY time_stamp DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             $query = 'SELECT kp FROM users WHERE logged_in = TRUE AND in_session = '.$row['kp'].';';
             $sub_result = mysql_query($query, $link);
             if($sub_result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $num = mysql_num_rows($sub_result);

             array_push($this->chat_invitation, array('kp' => intval($row['kp']),
                                                      'title' => $row['title'],
                                                      'host' => intval($row['host']),
                                                      'duration' => (intval($row['time_stamp']) - intval($row['created'])),
                                                      'leader' => -1,
                                                      'pop' => $num
                                                     ));
           }
												#  Replace host-user KP with user-name
         for($i = 0; $i < count($this->chat_invitation); $i++)
           {
             $query = 'SELECT uname FROM users WHERE kp = '.$this->chat_invitation[$i]['host'].';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
             $this->chat_invitation[$i]['host'] = $ret['uname'];
           }

         $str = '';
         for($i = 0; $i < count($this->chat_invitation); $i++)
           {
             $str .= '<tr id="cs'.$this->chat_invitation[$i]['kp'].'" class="shop-talk-invited-chat">';
             									#  Display chat session title
			 $str .=   '<td onclick="joinChat('.$this->chat_invitation[$i]['kp'].');">';
             $str .=        $this->chat_invitation[$i]['title'].'</td>';
             									#  Display hosted-by
			 $str .=   '<td onclick="joinChat('.$this->chat_invitation[$i]['kp'].');">';
             $str .=        $this->chat_invitation[$i]['host'].'</td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="joinChat('.$this->chat_invitation[$i]['kp'].');">';
             $str .=        $this->chat_invitation[$i]['duration'].'</td>';
             									#  Display session attendance
			 $str .=   '<td onclick="joinChat('.$this->chat_invitation[$i]['kp'].');">';
             $str .=        $this->chat_invitation[$i]['pop'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     public function list_active_public_chats($kp, &$link)
       {
         $query  = 'SELECT * FROM chats';		#  Query for cat 3
         $query .= ' WHERE access = NULL';		#  NULL string means public access
         $query .= ' ORDER BY time_stamp DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             $query = 'SELECT kp FROM users WHERE logged_in = TRUE AND in_session = '.$row['kp'].';';
             $sub_result = mysql_query($query, $link);
             if($sub_result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $num = mysql_num_rows($sub_result);

             array_push($this->chat_public, array('kp' => intval($row['kp']),
                                                  'title' => $row['title'],
                                                  'host' => intval($row['host']),
                                                  'duration' => (intval($row['time_stamp']) - intval($row['created'])),
                                                  'leader' => -1,
                                                  'pop' => $num
                                                 ));
           }

												#  Replace host-user KP with user-name
         for($i = 0; $i < count($this->chat_public); $i++)
           {
             $query = 'SELECT uname FROM users WHERE kp = '.$this->chat_public[$i]['host'].';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
             $this->chat_public[$i]['host'] = $ret['uname'];
           }

         $str = '';
         for($i = 0; $i < count($this->chat_public); $i++)
           {
             $str .= '<tr id="cs'.$this->chat_public[$i]['kp'].'" class="shop-talk-public-chat">';
             									#  Display chat session title
			 $str .=   '<td onclick="joinChat('.$this->chat_public[$i]['kp'].');">';
             $str .=        $this->chat_public[$i]['title'].'</td>';
             									#  Display hosted-by
			 $str .=   '<td onclick="joinChat('.$this->chat_public[$i]['kp'].');">';
             $str .=        $this->chat_public[$i]['host'].'</td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="joinChat('.$this->chat_public[$i]['kp'].');">';
             $str .=        $this->chat_public[$i]['duration'].'</td>';
             									#  Display session attendance
			 $str .=   '<td onclick="joinChat('.$this->chat_public[$i]['kp'].');">';
             $str .=        $this->chat_public[$i]['pop'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     /*********************************************************************************************
          Archived Chat Session functions
      *********************************************************************************************/

     public function retrieve_archived_chats($kp, &$link, $head = true)
       {
         if($head)
           {
             if($this->visible_panel == SHOPTALK_ARCHIVED)
               $str = '<table id="archived" class="table table-striped">';
             else
               $str = '<table id="archived" class="table table-striped" hidden>';
           }
         else
           $str = '';
         $str .=   '<thead>';					#  Directory header
         $str .=     '<tr>';
         $str .=       '<th>Title</th>';
         $str .=       '<th>Host</th>';
         $str .=       '<th>Last Activity</th>';
         $str .=       '<th>Population</th>';
         $str .=     '</tr>';
         $str .=   '</thead>';
         $str .=   '<tbody>';
         $str .=     $this->list_archived_hosted_chats($kp, $link);
         $str .=     $this->list_archived_invited_chats($kp, $link);
         $str .=     $this->list_archived_public_chats($kp, $link);
         $str .=   '</tbody>';
         if($head)
           $str .= '</table>';

         return $str;
       }

     public function list_archived_hosted_chats($kp, &$link)
       {
       }

     public function list_archived_invited_chats($kp, &$link)
       {
       }

     public function list_archived_public_chats($kp, &$link)
       {
       }

     /*********************************************************************************************
          Date & Time Formatting
      *********************************************************************************************/

     //  Format a UNIX timestamp as a date for envelop information
     public function format_time($seconds)
       {
         $minutes = 0;
         $hours = 0;

         if($seconds > 60)
           {
             $minutes = round($seconds / 60);
           }
         if($minutes > 60)
           {
             $hours = round($minutes / 60);
           }

         return $hours.':'.$minutes;
       }
   }

 ?>