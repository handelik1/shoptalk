<?php

 /*************************************************************************************
  Class definition for the bulletin posts roster in ShopTalk
  ************************************************************************************/

 class PostDirectory
   {
     public $post_author;						#  Array of posts created by user
     public $post_contributor;					#  Array of posts to which user has contributed
     public $post_invitation;					#  Array of posts to which user has access
     public $post_public;						#  Array of posts accessible to the public

     public function __construct()
       {
         $this->post_host = array();			#  Initialize arrays
         $this->post_contributor = array();
         $this->post_invitation = array();
         $this->post_public = array();
       }

     public function draw($kp = null, &$link = null)
       {
         $str  = '<div id="postdir_header" class="col-sm-8">';
         $str .=   '<div class="container-fluid">';
         $str .=     '<div class="row">';
         $str .=       '<div>';
         $str .=         '<h2 class="white">Bulletins</h2>';
         $str .=       '</div>';
         $str .=       '<div id="postdir">';	#  Set up the container now and fill it in later
         if($kp === null || $link === null)
           $str .=       '<p>Loading...</p>';
         else
           {
             $str .=     '<ul class="nav nav-tabs">';
             $str .=     '<li id="postdir_tab" class="active"><a href="javascript:;">Directory</a></li>';
             $str .=     '</ul>';
												#  Directory header
             $str .=     '<table id="bulletins" class="table table-striped">';
             $str .=       '<thead>';
             $str .=         '<tr>';
             $str .=           '<th>Title</th>';
             $str .=           '<th>Author</th>';
             $str .=           '<th>Latest Activity</th>';
             $str .=         '</tr>';
             $str .=       '</thead>';
             $str .=       '<tbody>';
             $str .=         $this->list_authored_posts($kp, $link);
             $str .=         $this->list_contributor_posts($kp, $link);
             $str .=         $this->list_invited_posts($kp, $link);
             $str .=         $this->list_public_posts($kp, $link);
             $str .=       '</tbody>';
             $str .=     '</table>';
           }
         $str .=       '</div>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';
												#  Build these modals so that they can exist
         $str .= $this->create_post_modal();	#  when needed

         return $str;
       }

     //  Retrieve a list of posts from the DB: posts which the user ($kp) has authored
     //  Build a string from them
     public function list_authored_posts($kp, &$link)
       {
         $query  = 'SELECT * FROM posts';		#  Build query for posts created by user with $kp
         $query .= ' WHERE author = '.$kp;		#  Meaning that $kp created the ORIGINAL post!
         $query .= ' AND parent = NULL';
         $query .= ' ORDER BY latest_read DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             array_push($this->post_author, array('kp' => intval($row['kp']),
                                                  'title' => $row['title'],
                                                  'author' => intval($row['author']),
                                                  'last_active' => $this->format_date(intval($row['time_stamp']))
                                                 ));
           }

         $str = '';
         for($i = 0; $i < count($this->post_author); $i++)
           {
             $str .= '<tr id="post'.$this->post_author[$i]['kp'].'" class="shop-talk-author-post">';
             									#  Display post title
			 $str .=   '<td onclick="readPost('.$this->post_author[$i]['kp'].');">';
             $str .=        $this->post_author[$i]['title'].'</td>';
             									#  Display authored-by
			 $str .=   '<td onclick="readPos('.$this->post_author[$i]['kp'].');"><i>You</i></td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="readPos('.$this->post_author[$i]['kp'].');">';
             $str .=        $this->post_author[$i]['last_active'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     //  Retrieve a list of posts from the DB: posts to which the user ($kp) has contributed
     //  Build a string from them
     public function list_contributor_posts($kp, &$link)
       {
         $query  = 'SELECT * FROM posts';		#  Build query for posts created by user with $kp
         $query .= ' WHERE author = '.$kp;		#  Meaning that $kp DID NOT create the original post,
         $query .= ' AND NOT parent = NULL';	#  But DID contribute a reply
         $query .= ' ORDER BY latest_read DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             array_push($this->post_contributor, array('kp' => intval($row['kp']),
                                                       'title' => $row['title'],
                                                       'author' => intval($row['author']),
                                                       'last_active' => $this->format_date(intval($row['time_stamp']))
                                                      ));
           }
												#  Replace author-user KP with user-name
         for($i = 0; $i < count($this->post_invitation); $i++)
           {
             $query = 'SELECT uname FROM users WHERE kp = '.$this->post_invitation[$i]['author'].';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
             $this->post_invitation[$i]['author'] = $ret['uname'];
           }

         $str = '';
         for($i = 0; $i < count($this->post_contributor); $i++)
           {
             $str .= '<tr id="post'.$this->post_contributor[$i]['kp'].'" class="shop-talk-contributor-post">';
             									#  Display post title
			 $str .=   '<td onclick="readPost('.$this->post_contributor[$i]['kp'].');">';
             $str .=        $this->post_contributor[$i]['title'].'</td>';
             									#  Display authored-by
			 $str .=   '<td onclick="readPos('.$this->post_contributor[$i]['kp'].');"><i>You</i></td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="readPos('.$this->post_contributor[$i]['kp'].');">';
             $str .=        $this->post_contributor[$i]['last_active'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     //  Retrieve a list of posts from the DB: posts to which the user ($kp) has been given access
     //  Build a string from them
     public function list_invited_posts($kp, &$link)
       {
         $query  = 'SELECT * FROM posts';		#  Query for session open specifically to user with $kp
         $query .= ' WHERE FIND_IN_SET('.$kp.', access)';
         $query .= ' ORDER BY latest_read DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             array_push($this->post_invitation, array('kp' => intval($row['kp']),
                                                      'title' => $row['title'],
                                                       'author' => intval($row['author']),
                                                       'last_active' => $this->format_date(intval($row['time_stamp']))
                                                     ));
           }
												#  Replace author-user KP with user-name
         for($i = 0; $i < count($this->post_invitation); $i++)
           {
             $query = 'SELECT uname FROM users WHERE kp = '.$this->post_invitation[$i]['author'].';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
             $this->post_invitation[$i]['author'] = $ret['uname'];
           }

         $str = '';
         for($i = 0; $i < count($this->post_invitation); $i++)
           {
             $str .= '<tr id="post'.$this->post_invitation[$i]['kp'].'" class="shop-talk-invited-post">';
             									#  Display post title
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['title'].'</td>';
             									#  Display hosted-by
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['author'].'</td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['last_active'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     //  Retrieve a list of posts from the DB: posts accessible to all
     //  Build a string from them
     public function list_public_posts($kp, &$link)
       {
         $query  = 'SELECT * FROM posts';		#  Query for cat 3
         $query .= ' WHERE access = NULL';		#  NULL string means public access
         $query .= ' ORDER BY latest_read DESC;';
         $result = mysql_query($query, $link);
         if($result == false)
           {
             mysql_close($link);
             croak('dbread');
           }
         while($row = mysql_fetch_assoc($result, MYSQL_ASSOC))
           {
             array_push($this->post_public, array('kp' => intval($row['kp']),
                                                  'title' => $row['title'],
                                                  'author' => intval($row['author']),
                                                       'last_active' => $this->format_date(intval($row['time_stamp']))
                                                 ));
           }

												#  Replace host-user KP with user-name
         for($i = 0; $i < count($this->post_public); $i++)
           {
             $query = 'SELECT uname FROM users WHERE kp = '.$this->post_public[$i]['host'].';';
             $result = mysql_query($query, $link);
             if($result == false)
               {
                 mysql_close($link);
                 croak('dbread');
               }
             $ret = mysql_fetch_assoc($result, MYSQL_ASSOC);
             $this->post_public[$i]['author'] = $ret['uname'];
           }

         $str = '';
         for($i = 0; $i < count($this->post_public); $i++)
           {
             $str .= '<tr id="post'.$this->post_invitation[$i]['kp'].'" class="shop-talk-public-post">';
             									#  Display post title
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['title'].'</td>';
             									#  Display hosted-by
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['host'].'</td>';
             									#  Display session duration (so far)
			 $str .=   '<td onclick="readPost('.$this->post_invitation[$i]['kp'].');">';
             $str .=        $this->post_invitation[$i]['duration'].'</td>';

             $str .= '</tr>';
           }
         return $str;
       }

     //  Build the post-creation modal
     public function create_post_modal()
       {
         $str  = '<div class="modal fade" id="newpost-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
         $str .=   '<div class="credential-panel" id="newpost-panel">';
         $str .=     '<div class="credential-form" id="newpost-form">';
         $str .=       '<h2 class="sign-in font-24 modal-header mail_subject">Post New Bulletin</h2>';
         $str .=       '<table class = "table post-table">';
         $str .=       '<tr><td class = "quick-set-title"><label class="credential-label" style="margin-top: 0px">Bulletin Title:</label></td>';
         $str .=       '<td><input class="newpost-credential" id="newpost_title" type="text"></td></tr>';
         $str .=       '<br/>';

         $str .=       '<tr><td class = "quick-set"><label class="credential-label">Quick Set-up: </label>';
         $str .=       '<a href="./docs/post_policy.html#leader" target="_blank" class = "help-button">[?]</a></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button post-button"';
         $str .=         ' id="newpost_qset_leader" value="Thread Leader" onclick="quickPostSet_leader();"></td></tr>';

         $str .=       '<br/>';

         $str .=       '<tr><td class = "quick-set"><label class="credential-label">Quick Set-up: </label>';
         $str .=       '<a href="./docs/post_policy.html#equals" target="_blank" class = "help-button">[?]</a></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button post-button"';
         $str .=         ' id="newpost_qset_equal" value="Equal Standing" onclick="quickPostSet_democratic();"></td></tr>';

         $str .=       '<br/>';

         $str .=       '<tr><td><label class="credential-label">Custom Settings: </label></td>';
         $str .=       '<td><input type="submit" class="btn-default register-button post-button"';
         $str .=         ' id="newpost_create" value="Next" onclick="postSetupPage(1);"></td></tr>';
         $str .=      '</table>';
         $str .=     '</div>';
         $str .=   '</div>';
         $str .= '</div>';

         return $str;
       }

     //  Retrieve a list of posts from the DB:
     //  Build a string from them
     public function format_date($unix_time)
       {
         $retstr = gmdate('j F Y, G:i:s', $unix_time);
         return $retstr;
       }
   }

 ?>