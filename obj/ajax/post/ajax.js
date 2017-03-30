/*****************************************************************************************
     A J A X : P O S T
 All functions which update parts of the post/bulletin board system page without our ever
 having to leave the page. Depending on the values they return from the system, these
 functions manipulate the front-end display themselves.
*****************************************************************************************/

var postDrawingCode = '';									//  Used for drawing instructions

//////////////////////////////////////////////////////////////////////////////////////////
//   H O M E P A G E : Home post screen functions

//  Wherever else you are within the system, go to the chat landing page
function stagePosts()
  {
    if(DEBUG_VERBOSE)
      console.log('stagePosts()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=hearYEEHEARyee';				//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/post/home.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            if(RecXML.responseText == '')					//  Null string
              {
              }
            else
              {
                var ret = RecXML.responseText.split('|');
                if(ret[0] == 'error')						//  Error with explanation
                  {
                  }
                else if(ret[0] == 'ok')
                  {
                    postDrawingCode = '';					//  Clear drawing command buffer

                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously
                    currentStation = SHOPTALK_POSTS;		//  Set section tracker
                    disableDrawing();
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   B U L L E T I N - B O A R D : Session set-up, creation, and connection functions

//  User is typing, user-names AND this user's groups (if any) should be recommended accordingly
function queryUsersGroups(qstring)
  {
    if(DEBUG_VERBOSE)
      console.log('queryUsersGroups()');

    if(qstring.length > 0)									//  Don't bother if string is empty!
      {
        var RecXML = new XMLHttpRequest();					//  IE 7+, Firefox, Chrome, Opera, Safari
        var params = 'sendRequest=rEEchOUTtuchSumune';		//  Gratuitous complication
        params += '&uname=' + uname;						//  Build parameter string
        params += '&pword=' + pword;
        params += '&q=' + qstring;							//  Attach query string
        params += '&ig=1';									//  Include this user's defined groups in suggestions

        RecXML.open("POST", 'obj/sess/account/qusergroup.php', true);
        RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        RecXML.onreadystatechange = function()
          {
            if(RecXML.readyState == 4 && RecXML.status == 200)
              {
                if(RecXML.responseText == '')				//  Null string
                  {
                  }
                else
                  {
                    var ret = RecXML.responseText.split('|');
                    if(ret.length > 1)						//  Only bother is SOMETHING came back
                      {
                        if(ret[0] == 'error')				//  Error with explanation
                          {
                          }
                        else if(ret[0] == 'ok')
                          {
                            var field = document.getElementById('newshare_membersearch');
                            if(field != null)
                              field.value = ret[1];
                          }
                        else								//  Anything other than what we expect
                          {
                          }
                      }
                  }
              }
          };
        RecXML.send(params);
      }
  }

//  Create a new post with "Democratic" settings and conditions: meaning that should the post's
//  creator ever wish to make this post a public resource, he or she must get the permission
//  by vote of all who contributed.
function quickPostSet_democratic()
  {
    if(DEBUG_VERBOSE)
      console.log('quickPostSet_democratic()');

    $('#newpost-modal').modal('toggle');					//  Drink modal back up immediately

    var postTitle = document.getElementById('newpost_title').value;
    var params = 'sendRequest=settumUPsettumMUP';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    params += '&t=' + postTitle;							//  Title of session to be created
    params += '&l=0';										//  There is no session leader
    params += '&ac=0';										//  Count of members given access
    params += '&kc=0';										//  Count of session keywords (if any)

    createPost(params);
  }

//  Create a new post with "Session Leader" settings and conditions: meaning that should the post's
//  creatror ever wish to make this post a public resource, he or she NEED NOT get the permission
//  of all who contributed.
function quickPostSet_leader()
  {
    if(DEBUG_VERBOSE)
      console.log('quickPostSet_leader()');

    $('#newpost-modal').modal('toggle');					//  Drink modal back up immediately

    var postTitle = document.getElementById('newpost_title').value;
    var params = 'sendRequest=settumUPsettumMUP';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    params += '&t=' + postTitle;							//  Title of session to be created
    params += '&l=1';										//  Session creator acts as session leader
    params += '&ac=0';										//  Count of members given access
    params += '&kc=0';										//  Count of session keywords (if any)

    createPost(params);
  }

//  Load page 'i' of settings into modal
function postSetupPage(i)
  {
    if(DEBUG_VERBOSE)
      console.log('postSetupPage(' + i + ')');
  }

//  Retrieves settings from input fields and packs them up as parameters for createChat() request
function createCustomPost()
  {
/*
    var i;
    var params = 'sendRequest=settumUPsettumMUP';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    params += '&t=' + chatTitle;							//  Title of session to be created
    params += '&l=' + creatorIsLeader;						//  Whether the session creator acts as session leader
    params += '&ac=' + ;										//  Count of members given access
    params += '&i' + i + '=' + ;							//  Each member's screen name
    params += '&kc=';										//  Count of session keywords (if any)
    params += '&kw' + i + '=' + ;							//  Each keyword
*/
  }

//  Assumes parameters have been packaged elsewhere
function createPost(params)
  {
    if(DEBUG_VERBOSE)
      console.log('createPost( ... )');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    RecXML.open("POST", 'obj/sess/post/create.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            if(RecXML.responseText == '')					//  Null string
              {
              }
            else
              {
                var ret = RecXML.responseText.split('|');
                if(ret[0] == 'error')						//  Error with explanation
                  {
                  }
                else if(ret[0] == 'ok')
                  {
                    										//  Rewrite page asynchronously (cut past 'ok|')
                    //document.body.innerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                    //currentStation = SHOPTALK_IN_SESSION;
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   E R R O R - H A N D L I N G : Error handling functions for person-to-person messages
