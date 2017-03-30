/*****************************************************************************************
     A J A X : C H A T
 All functions which update parts of the chat system page without our ever having to leave
 the page. Depending on the values they return from the system, these functions manipulate
 the front-end display themselves.
*****************************************************************************************/

var callComplete_refreshChatDirectory = true;				//  Prevent refresh calls from piling up
var callComplete_refreshChatSession = true;

//////////////////////////////////////////////////////////////////////////////////////////
//   H O M E P A G E : Home chat screen functions

//  Wherever else you are within the system, go to the chat landing page
function stageChat()
  {
    if(DEBUG_VERBOSE)
      console.log('stageChat()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=letZtalkyYynot';				//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/chat/home.php', true);
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
                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously
                    currentStation = SHOPTALK_CHAT;			//  Set section tracker
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

function refreshChatDirectories()
  {
    if(DEBUG_VERBOSE)
      console.log('refreshChatDirectories()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=whoozTalkinWutsssupWhat';		//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/chat/rfshdir.php', true);
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
                    var strlenActive = parseInt(ret[1]);
                    var startString = ret[0].length + 1 +
                                      ret[1].length + 1;	//  Find string offset

                    var activeContent = RecXML.responseText.substring(startString, startString + strlenActive);
                    var archivedContent = RecXML.responseText.substring(startString + strlenActive);

                    var active = document.getElementById('active');
                    var archived = document.getElementById('archived');

                    if(active != null)
                      active.innerHTML = activeContent;
                    if(archived != null)
                      archived.innerHTML = archivedContent;
                  											//  Reset flag
                    callComplete_refreshChatDirectory = true;
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };

    callComplete_refreshChatDirectory = false;				//  Block while call is out
    RecXML.send(params);
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   C H A T : In-session functions

//  Transmit text you've typed to the chat transcript
function say()
  {
    if(DEBUG_VERBOSE)
      console.log('say()');

    var msg = document.getElementById('chat_msg').value;	//  Pull text from input box
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=whatWHATwhat';				//  Gratuitous complication

    if(msg.length > 0)										//  Don't bother with empty strings
      {
        params += '&uname=' + uname;						//  Build parameter string
        params += '&pword=' + pword;
        params += '&m=' + msg;

        RecXML.open("POST", 'obj/sess/chat/say.php', true);
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
                    if(ret[0] == 'error')					//  Error with explanation
                      {
                      }
                    else if(ret[0] == 'denied')				//  Valid (cogent) request, access denied
                      {
                        console.log('You have requested access to a chat for which you do not have permission.');
                      }
                    else if(ret[0] == 'ok')
                      {
                        var transcriptBody = document.getElementById('transcript_table_body');
                        if(transcriptBody != null)
                          transcriptBody.innerHTML += RecXML.responseText.substring(3, RecXML.responseText.length);
                      }
                    else									//  Anything other than what we expect
                      {
                      }
                  }
              }
          };

        document.getElementById('chat_msg').value = '';		//  Blank the field
        RecXML.send(params);								//  Ship the request
      }
  }

function draw()
  {
    if(DEBUG_VERBOSE)
      console.log('draw()');
  }

//  Periodic in-session refresh function: keep requesting fresh transcripts
function heartbeat()
  {
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=ThsIsGrndCtrl';				//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/chat/hb.php', true);
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
                else if(ret[0] == 'denied')					//  Valid (cogent) request, access denied
                  {
                    console.log('You have requested access to a chat for which you do not have permission.');
                  }
                else if(ret[0] == 'ok')
                  {
                    var transcriptBody = document.getElementById('transcript_table_body');
                    if(transcriptBody != null)
                      transcriptBody.innerHTML += RecXML.responseText.substring(3, RecXML.responseText.length);
                  											//  Reset flag
                    callComplete_refreshChatSession = true;
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };

    callComplete_refreshChatSession = false;				//  Block while call is out
    RecXML.send(params);									//  Ship the request
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   C H A T : Adding/Removing members to/from session

//  Add member (or group) to chat session
function addMember()
  {
    if(DEBUG_VERBOSE)
      console.log('addMember()');

    var tableBody = document.getElementById('chatinvite_tbody');
    var chatsession = document.getElementById('invitechatkey').value;
    var invited = [];										//  List of all members to be invited to chat
    var i;

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=YrINVITED';					//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    for(i = 0; i < tableBody.rows.length; i++)
      {
        if(invited.indexOf(tableBody.rows[i].cells[0].innerHTML) < 0)
          invited.push(tableBody.rows[i].cells[0].innerHTML);
      }

    if(invited.length > 0)									//  Don't bother if list is empty
      {
        params += '&s=' + chatsession;						//  Identify this chat session
        params += '&gc=' + invited.length;					//  Add count to POST
        for(i = 0; i < invited.length; i++)					//  Add all members to POST
          params += '&m' + i + '=' + invited[i];

        RecXML.open("POST", 'obj/sess/chat/addmem.php', true);
        RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        RecXML.onreadystatechange = function()
          {
            if(RecXML.readyState == 4 && RecXML.status == 200)
              {
                console.log(RecXML.responseText);

                if(RecXML.responseText == '')				//  Null string
                  {
                  }
                else
                  {
                    var ret = RecXML.responseText.split('|');
                    if(ret[0] == 'error')					//  Error with explanation
                      {
                      }
                    else if(ret[0] == 'ok')
                      {
                      }
                    else									//  Anything other than what we expect
                      {
                      }
                  }
              }
          };

        $('#invitechat-modal').modal('toggle');				//  Drink modal back up
        RecXML.send(params);
      }
  }

//  Remove member (or group) from chat session
function removeMember()
  {
    if(DEBUG_VERBOSE)
      console.log('removeMember()');
  }

//  User is typing, user-names should be recommended accordingly
function queryInviteUsers(qstring)
  {
    if(DEBUG_VERBOSE)
      console.log('queryInviteUsers()');

    if(qstring.length > 0)									//  Don't bother if string is empty!
      {
        var RecXML = new XMLHttpRequest();					//  IE 7+, Firefox, Chrome, Opera, Safari
        var params = 'sendRequest=rEEchOUTtuchSumune';		//  Gratuitous complication
        params += '&uname=' + uname;						//  Build parameter string
        params += '&pword=' + pword;
        params += '&q=' + qstring;							//  Attach query string

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
                            var field = document.getElementById('invitechat_membersearch');
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

//  User is typing, user-defined groups should be recommended accordingly
function queryInviteGroups(qstring)
  {
    if(DEBUG_VERBOSE)
      console.log('queryInviteGroups()');

    if(qstring.length > 0)									//  Don't bother if string is empty!
      {
        var RecXML = new XMLHttpRequest();					//  IE 7+, Firefox, Chrome, Opera, Safari
        var params = 'sendRequest=YooHooRUNNINGCREW';		//  Gratuitous complication
        params += '&uname=' + uname;						//  Build parameter string
        params += '&pword=' + pword;
        params += '&q=' + qstring;							//  Attach query string

        RecXML.open("POST", 'obj/sess/chat/qgroup.php', true);
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
                            var field = document.getElementById('invitechat_groupsearch');
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

//////////////////////////////////////////////////////////////////////////////////////////
//   C H A T : Session set-up, creation, and connection functions

//  Request to join chat KP i
function joinChat(i)
  {
    if(DEBUG_VERBOSE)
      console.log('joinChat(' + i + ')');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=LEMMEIN';						//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    params += '&i=' + i;

    RecXML.open("POST", 'obj/sess/chat/join.php', true);
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
                else if(ret[0] == 'denied')					//  Valid (cogent) request, access denied
                  {
                    console.log('You have requested access to a chat for which you do not have permission.');
                  }
                else if(ret[0] == 'ok')
                  {
                    										//  Rewrite page asynchronously (cut past 'ok|')
                    document.body.innerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                    currentStation = SHOPTALK_IN_SESSION;
                    enableDrawing();
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  Create a new session with "Democratic" settings and conditions: meaning that should the session's
//  creatror ever wish to make this chat a public resource, he or she must get the permission of all
//  who contributed.
function quickSet_democratic()
  {
    if(DEBUG_VERBOSE)
      console.log('quickSet_democratic()');

    $('#newchat-modal').modal('toggle');					//  Drink modal back up immediately

    var chatTitle = document.getElementById('newchat_title').value;
    var params = 'sendRequest=settumUPsettumMUP';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    params += '&t=' + chatTitle;							//  Title of session to be created
    params += '&l=0';										//  There is no session leader
    params += '&ac=0';										//  Count of members given access
    params += '&kc=0';										//  Count of session keywords (if any)

    createChat(params);
  }

//  Create a new session with "Session Leader" settings and conditions: meaning that should the
//  session's creatror ever wish to make this post a public resource, he or she NEED NOT get the
//  permission of all who contributed.
function quickSet_leader()
  {
    if(DEBUG_VERBOSE)
      console.log('quickSet_leader()');

    $('#newchat-modal').modal('toggle');					//  Drink modal back up immediately

    var chatTitle = document.getElementById('newchat_title').value;
    var params = 'sendRequest=settumUPsettumMUP';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    params += '&t=' + chatTitle;							//  Title of session to be created
    params += '&l=1';										//  Session creator acts as session leader
    params += '&ac=0';										//  Count of members given access
    params += '&kc=0';										//  Count of session keywords (if any)

    createChat(params);
  }

//  Load page 'i' of settings into modal
function chatSetupPage(i)
  {
    if(DEBUG_VERBOSE)
      console.log('chatSetupPage(' + i + ')');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=heartheyregonnacancelLatin';	//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    params += '&p=' + i;

    RecXML.open("POST", 'obj/sess/chat/setuppage.php', true);
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
                    var prospectiveTitle = document.getElementById('newchat_title').value;
                    var chatCreateTable = document.getElementById('chatcreate_table');
                    if(chatCreateTable != null)
                      {
                        var i;
                        var str;
                      										//  Pull existing rows after session title
                        while(chatCreateTable.rows.length > 1)
                          chatCreateTable.deleteRow(chatCreateTable.rows.length - 1);

                        str = chatCreateTable.innerHTML;	//  Save table text
                        for(i = 1; i < ret.length; i++)		//  Insert replacement rows from incoming page
                          {
                            if(i < ret.length - 1)
                              str += ret[i] + '<br/>';
                            else
                              str += ret[i];
                          }
                        chatCreateTable.innerHTML = str;	//  Rewrite page asynchronously

                        if(prospectiveTitle.length > 0)		//  Restore string (if one existed)
                          document.getElementById('newchat_title').value = prospectiveTitle;
                      }
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  Retrieves settings from input fields and packs them up as parameters for createChat() request
function createCustomChat()
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
function createChat(params)
  {
    if(DEBUG_VERBOSE)
      console.log('createChat( ... )');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    RecXML.open("POST", 'obj/sess/chat/create.php', true);
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
                    document.body.innerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                    currentStation = SHOPTALK_IN_SESSION;
                    enableDrawing();
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
