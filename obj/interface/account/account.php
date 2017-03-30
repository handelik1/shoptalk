<?php

 /*************************************************************************************
  Class definition for the User Account panel object of ShopTalk, which includes both
  the user profile editor and the groups editor. The groups editor required the creation
  of a Group class for convenience (could have been implemented with a hash table, but
  that's messy.) All Groups do is contain information and render themselves for Account.
  ************************************************************************************/
 define('SHOPTALK_PROFILE', 0);
 define('SHOPTALK_GROUP', 1);

 class Group
   {
     public $kp;								#  DB KP of this group definition
     public $gname;								#  Name of the group (e.g. "People from work")
     public $members;							#  Array of user KPs in this group

     public function __construct($k, $name)
       {
         $this->kp = $k;
         $this->gname = $name;
         $this->members = array();
       }

     public function add_member($k, $name)
       {
         $this->members[$k] = $name;			#  $this->members is indexed by user KP
         										#  $name is a String
       }

     public function draw()
       {
         $str  = '<tr data-toggle="collapse" data-target="#groups_accordion_'.$this->kp.'" class="clickable">';
         $str .=   '<td>'.$this->gname.'</td>';
         $str .= '</tr>';
         $str .= '<tr>';
         $str .=   '<td colspan="1">';
         $str .=     '<div id="groups_accordion_'.$this->kp.'" class="collapse">';
         $str .=       '<table id="group_'.$this->kp.'" class="table table-striped">';
         $str .=         '<thead>';
         $str .=         '</thead>';
         $str .=         '<tbody>';
         foreach($this->members as $k => $v)
           {
             $str .=     '<tr>';
             $str .=       '<td>'.$v.'</td>';
             $str .=     '</tr>';
           }
         $str .=         '</tbody>';
         $str .=       '</table>';
         $str .=     '</div>';
         $str .=   '</td>';
         $str .= '</tr>';
         return $str;
       }
   }

 class Account
   {
     public $uname;								#  Username of this account
     public $fname;								#  Human first name
     public $lname;								#  Human last name

     public $institution;						#  Affiliated institution
     public $time_zone;							#  This account's time zone

     public $visible_panel;						#  Indication of which tab is visible

     public $groups;							#  Array of Group objects

     public function __construct()
       {
         $this->visible_panel = SHOPTALK_PROFILE;
         $this->groups = array();
       }

     public function draw($kp = null, &$link = null)
       {
         $str  = '<div id="account_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
         $str .=       '<div>';
         $str .=         '<h3 class = "acc_name font-30">'.$this->fname.' '.$this->lname.'</h3>';
         $str .=         '<h4 class = "acc_user font-24">'.$this->uname.'</h4>';
         $str .=       '</div>';
         $str .=       '<div id="account">';	#  Set up the container now and fill it in later
         if($kp === null || $link === null)
           $str .=       '<p>Loading...</p>';
         else
           {
             $str .=     '<ul class="nav nav-tabs">';
             switch($this->visible_panel)
               {
                 case SHOPTALK_PROFILE:
                   $str .= '<li id="profile_tab" class="active"><a href="javascript:;">Profile</a></li>';
                   $str .= '<li id="groups_tab"><a href="javascript:;" ';
                   $str .=     'onclick="hideProfile(); showGroups();">Groups</a></li>';
                   break;
                 case SHOPTALK_GROUP:
                   $str .= '<li id="profile_tab"><a href="javascript:;" ';
                   $str .=     'onclick="hideGroups(); showProfile();">Profile</a></li>';
                   $str .= '<li id="groups_tab" class="active"><a href="javascript:;">Groups</a></li>';
                   break;
               }
             $str .=     '</ul>';

             $str .= $this->retrieve_profile_edit();
             $str .= $this->retrieve_group_edit();
           }
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->create_group_modal();	#  when needed

         return $str;
       }

     //  Build the create-group modal
     public function create_group_modal()
       {
         $str  = '<div class="modal fade" id="newgroup-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="newgroup-panel">';
         $str .=     '<div class="credential-form" id="newgroup-form">';
         $str .=       '<h2 class="sign-in font-24 modal-header group_subject">Define New Group</h2>';
         $str .=       '<table class = "table group-invite-table">';
         $str .=       '<tr><td class = "group-invite-label"><label class="credential-label" style="margin-top: 0px">Group Label: </label></td>';
         $str .=       '<td class = "group-invite-text"><input class="newgroup-credential" id="newgroup_title" type="text"></td></tr>';
         $str .=       '<tr><td class = "group-invite-label"><label class="credential-label" style="margin-top: 0px">Member: </label></td>';
         $str .=       '<td class = "group-invite-text"><input class="newgroup-credential" id="newgroup_membersearch"';
         $str .=             ' onkeyup="queryUsers(this.value);" type="text"/></td>';
         $str .=       '<td><a href="javascript:;" onclick="addGroupMember();">';
         $str .=         '<img src="./img/plus.png" alt="Add to group"/>';
         $str .=       '</a></td></tr>';
         $str .=      '</br>';
         $str .=       '</table>';
         $str .=       '<div id="newgroup_tagfield">';
         $str .=        '<h4 id = "member-name">Member Name</h4>';
         $str .=         '<div class = "chat-wrapper">';
         $str .=         '<table id="newgroup_members" class="table table-striped">';
         $str .=           '<thead>';
         $str .=             '<tr>';
         $str .=               '<th></th>';		#  Column for "remove" buttons
         $str .=             '</tr>';
         $str .=           '</thead>';
         $str .=           '<tbody id="newgroup_members_tbody">';
         $str .=           '</tbody>';
         $str .=         '</table>';
         $str .=        '</div>';
         $str .=       '</div>';

         $str .=       '<input type="submit" class="btn-default register-button" id="newgroup_save"';
         $str .=             ' value="Define" onclick="defineGroup();">';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
         return $str;
       }

     public function query_groups($kp, &$link)
       {
         $query  = 'SELECT kp, name, members FROM groups';
         $query .= ' WHERE defined_by = '.$kp;
         $query .= ' ORDER BY name ASC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);				#  Some kind of read error
             return;
           }
         if(mysql_num_rows($result) > 0)		#  Are there any groups?
           {
             while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
               {
                 array_push($this->groups, new Group(intval($row['kp']), $row['name']));

                 								#  Retrieve user-names for every group member
                 $members = explode(',', $row['members']);
                 for($i = 0; $i < count($members); $i++)
                   {
                     $query  = 'SELECT uname FROM users';
                     $query .= ' WHERE kp = '.$members[$i].';';
                     $sub_result = mysql_query($query, $link);
                     if($sub_result == false)
                       {
                         mysql_close($link);	#  Some kind of read error
                         return;
                       }
                     $ret = mysql_fetch_assoc($sub_result, MYSQL_ASSOC);
												#  Add to group's member list
                     $this->groups[count($this->groups) - 1]->members[intval($members[$i])] = $ret['uname'];
                   }
               }
           }
       }

     //  Build the profile editing form
     public function retrieve_profile_edit()
       {
         if($this->visible_panel == SHOPTALK_PROFILE)
           $str  = '<div id="profile_panel">';
         else
           $str  = '<div id="profile_panel" hidden>';
         $str .=   '<table class = "table" id = "account-table">';
         $str .=   '<tr><td><h1 id = "acc_settings">Account Settings</h1></tr></td>';
         $str .=   '<tr><td><label class = "font-14 acc_label">First name:</label><input type="text" name="fname" value="'.$this->fname.'"/></td></tr>';
         $str .=   '<tr><td><label style = "margin-left: 2px" class = "font-14 acc_label">Last name:</label><input type="text" name="lname" value="'.$this->lname.'"/></td></tr>';
        $str .=   '<tr><td><label class = "font-14 acc_label">User name:</label><input type="text" name="uname" value="'.$this->uname.'"/></td></tr>';
         $str .=   '<tr><td><label class = "font-14 acc_label">Institution:&nbsp</label><input type="text" name="institution" value="'.$this->institution.'"/></td></tr>';
         $str .=   '<tr><td><label class = "font-14 acc_label">Time zone:&nbsp</label>';
         $str .=   '<select>';
												#  All possible time zones
         $tz = array(0, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10, -11, -12,
                     12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1);
         foreach($tz as $t)
           {
             $str .= '<option value="'.$t.'"';
             if($t == $this->time_zone)
               $str .= ' selected';
             $str .= '>UTC ';
             if($t > 0)
               $str .= '+';
             $str .= $t.'</option>';
           }
         $str .=   '</select>';
         $str .=   '<br/>';
         $str .=   '<input id = "acc_submit" type="submit" onclick="" value="Save Changes">';
         $str .=  '</table>';
         $str .= '</div>';
         return $str;
       }

     //  Build the group directory page
     public function retrieve_group_edit()
       {
         if($this->visible_panel == SHOPTALK_GROUP)
           $str  = '<div id="groups_panel">';
         else
           $str  = '<div id="groups_panel" hidden>';
         $str .=   '<table class="table table-hover">';
         $str .=     '<thead>';
         $str .=       '<tr>';
         $str .=         '<th>Group Name</th>';
         $str .=       '</tr>';
         $str .=     '</thead>';
         $str .=     '<tbody>';
         if(count($this->groups) == 0)
           $str .=     '<tr><td colspan="1"><i>You have no groups defined</i></td></tr>';
         else
           {
             for($i = 0; $i < count($this->groups); $i++)
               $str .= $this->groups[$i]->draw();
           }
         $str .=     '</tbody>';
         $str .=   '</table>';
         $str .= '</div>';
         return $str;
       }
   }

 ?>