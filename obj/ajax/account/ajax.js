/*****************************************************************************************
     A J A X:
 All functions which update parts of the page without our ever having to leave the page.
 Depending on the values they return from the system, these functions manipulate the
 front-end display themselves.
*****************************************************************************************/

//////////////////////////////////////////////////////////////////////////////////////////
//   L O G G E D - I N : Existing users logging in

//  Checks credentials, updates DB, and returns the user home page
function login()
  {
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=sWoRdFiSH';					//  Gratuitous complication
    uname = document.getElementById('login_uname').value;
    pword = document.getElementById('login_pword').value;
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    if(DEBUG_VERBOSE)
      console.log('login(' + document.getElementById('login_uname').value + ')');
															//  Disable the submission button
															//  while call is out
    var submitButton = document.getElementById('login_submit');
    submitButton.setAttribute('disabled', true);

    RecXML.open("POST", 'obj/sess/account/login.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            var notice = document.getElementById('login_notice');
            var submitbttn = document.getElementById('login_submit');

            if(RecXML.responseText == '')					//  Null string
              {
                notice.innerHTML = 'Server error: please try again later';
                submitbttn.removeAttribute('disabled');		//  Restore trigger
              }
            else
              {
                var ret = RecXML.responseText.split('|');

                if(ret[0] == 'error')						//  Error with explanation
                  {
                    notice.innerHTML = ret[1];
                    submitbttn.removeAttribute('disabled');	//  Restore trigger
                  }
                else if(ret[0] == 'welcome')
                  {
                    if(DEBUG_VERBOSE)
                      console.log('Login successful !!');

                    notice.innerHTML = '';					//  Clear this, in case it had content
                    document.body.removeAttribute('class');
                    document.body.removeAttribute('onload');
                    document.body.removeAttribute('style');
                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously

                    currentStation = SHOPTALK_MAILBOX;		//  Set flag
                    window.setTimeout(loop, REFRESH_INTERVAL);
                    										//  BEGIN THE LOOP !!!

                  }
                else										//  Anything other than what we expect
                  {
                    notice.innerHTML = 'Server error: please try again later';
                    submitbttn.removeAttribute('disabled');	//  Restore trigger
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  A logged-in user has hit refresh on his or her browser. Since they are already logged in,
//  test the "currentStation" variable to find out which page should be re-rendered. (This
//  re-rendering will check the uname and pword variables again, of course.)
function refresh()
  {
    if(DEBUG_VERBOSE)
      console.log('refresh()');

    switch(currentStation)
      {
        case SHOPTALK_ACCOUNT: stageAccount();
                               break;
        case SHOPTALK_CHAT:    stageChat();
                               break;
        case SHOPTALK_POSTS:   stagePost();
                               break;
        default:               currentStation = SHOPTALK_MAILBOX;
                               stageHome();
      }
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   R E G I S T R A T I O N : Account registration for new users

//  Checks credentials, updates DB, and returns the user home page
function reqacc()
  {
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=icanHaZ';						//  Gratuitous complication
    uname = document.getElementById('reqacc_uname').value;
    pword = document.getElementById('reqacc_pword').value;
    														//  Build parameter string
    params += '&fname=' + document.getElementById('reqacc_firstname').value;
    params += '&lname=' + document.getElementById('reqacc_lastname').value;
    params += '&email=' + document.getElementById('reqacc_email').value;
    params += '&inst=' + document.getElementById('reqacc_institution').value;
    params += '&uname=' + uname;
    params += '&pword=' + pword;
    params += '&cnfpword=' + document.getElementById('reqacc_confirmpword').value;

    if(DEBUG_VERBOSE)
      console.log('reqacc(' + document.getElementById('reqacc_uname').value + ')');
															//  Disable the submission button
															//  while call is out
    var submitButton = document.getElementById('reqacc_submit');
    submitButton.setAttribute('disabled', true);

    RecXML.open("POST", 'obj/sess/account/reqacc.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            var notice = document.getElementById('reqacc_notice');
            var submitbttn = document.getElementById('reqacc_submit');

            if(RecXML.responseText == '')					//  Null string
              {
                notice.innerHTML = 'Server error: please try again later';
                submitbttn.removeAttribute('disabled');		//  Restore trigger
              }
            else
              {
                var ret = RecXML.responseText.split('|');

                if(ret[0] == 'error')						//  Error with explanation
                  {
                    notice.innerHTML = ret[1];
                    submitbttn.removeAttribute('disabled');	//  Restore trigger
                  }
                else if(ret[0] == 'welcome')
                  {
                    if(DEBUG_VERBOSE)
                      console.log('Account request granted !!');

                    notice.innerHTML = '';					//  Clear this, in case it had content
                    document.body.removeAttribute('class');
                    document.body.removeAttribute('onload');
                    document.body.removeAttribute('style');
                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously

                    mailbox();								//  Now go get my mail!
                  }
                else										//  Anything other than what we expect
                  {
                    notice.innerHTML = 'Server error: please try again later';
                    submitbttn.removeAttribute('disabled');	//  Restore trigger
                  }
              }
          }
      };
    RecXML.send(params);
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   L O G - O U T : Log out

//  Call the script which logs the user out (destroys the session file and updates the DB)
function logout()
  {
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=leightRLZRZ';					//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    if(DEBUG_VERBOSE)
      console.log('logout()');

    RecXML.open("POST", 'obj/sess/account/logout.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            if(DEBUG_VERBOSE)
              console.log('Logout successful !!');
          }
      };
    RecXML.send(params);
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   H O M E P A G E : Home screen functions
//  *** NOTE that this routine actually directs to the MAIL MODULE!
//  Wherever else you are within the system, return to the landing page
function stageHome()
  {
    if(DEBUG_VERBOSE)
      console.log('stageHome()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=oTHrngeE';					//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/account/home.php', true);
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
                    document.body.removeAttribute('onload');
                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously

                    currentStation = SHOPTALK_MAILBOX;		//  Set section tracker
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
//   P R O F I L E : Edit account profile and groups

function stageAccount()
  {
    if(DEBUG_VERBOSE)
      console.log('stageAccount()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=HSaCWDTKDTKTLFOut';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/account/editacc.php', true);
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
															//  Cut past header-pipe
                    document.body.innerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                    currentStation = SHOPTALK_ACCOUNT;		//  Set section tracker
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

function refreshProfile()
  {
  }

function refreshGroups()
  {
    if(DEBUG_VERBOSE)
      console.log('refreshGroups()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=DoubleMintDoubleMintGUM';		//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/account/rfshgroup.php', true);
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
															//  Cut past header-pipe
                    var accountDiv = document.getElementById('account_header');
                    if(accountDiv != null)
                      accountDiv.outerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

function defineGroup()
  {
    if(DEBUG_VERBOSE)
      console.log('defineGroup()');

    var groupName = document.getElementById('newgroup_title').value;
    var tableBody = document.getElementById('newgroup_members_tbody');
    var groupMembers = [];									//  List of all members in requested group
    var i;

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=TheseFragmentsIHaveShored';	//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    for(i = 0; i < tableBody.rows.length; i++)
      {
        if(groupMembers.indexOf(tableBody.rows[i].cells[0].innerHTML) < 0)
          groupMembers.push(tableBody.rows[i].cells[0].innerHTML);
      }

    if(groupMembers.length > 0 && groupName.length > 0)		//  Don't bother if list or name is empty
      {
        params += '&gname=' + groupName;					//  Add group name
        params += '&c=' + groupMembers.length;				//  Add count to POST
        for(i = 0; i < groupMembers.length; i++)			//  Add all members to POST
          params += '&m' + i + '=' + groupMembers[i];

        RecXML.open("POST", 'obj/sess/account/defgroup.php', true);
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
                      										//  Drink modal back up
                        $('#newgroup-modal').modal('toggle');
                        refreshGroups();					//  Refresh and redraw the groups
                      }
                    else									//  Anything other than what we expect
                      {
                      }
                  }
              }
          };
        RecXML.send(params);
      }
  }

//  User is typing, user-names should be recommended accordingly
function queryUsers(qstring)
  {
    if(DEBUG_VERBOSE)
      console.log('queryUsers()');

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
                            var field = document.getElementById('newgroup_membersearch');
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

//////////////////////////////////////////////////////////////////////////////////////////
//   E R R O R - H A N D L I N G : Error handling functions
