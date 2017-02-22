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

    RecXML.open("POST", 'obj/sess/login.php', true);
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
        case SHOPTALK_MAILBOX: stageHome();
                               break;
        case SHOPTALK_CHAT:    stageChat();
                               break;
        case SHOPTALK_POSTS:   stagePost();
                               break;
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

    RecXML.open("POST", 'obj/sess/reqacc.php', true);
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

    RecXML.open("POST", 'obj/sess/logout.php', true);
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

//  Wherever else you are within the system, return to the landing page
function stageHome()
  {
    if(DEBUG_VERBOSE)
      console.log('stageHome()');

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=oTHrngeE';					//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;

    RecXML.open("POST", 'obj/sess/home.php', true);
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
//   C H A T : Chat session functions

//  Wherever else you are within the system, return to the chat page
function stageChat()
  {
    if(DEBUG_VERBOSE)
      console.log('stageChat()');
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   P O S T S : Post/Message Board functions

//  Wherever else you are within the system, return to the post page
function stagePost()
  {
    if(DEBUG_VERBOSE)
      console.log('stagePost()');
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   P E R S O N - T O - P E R S O N : Message functions

//  Refresh the contents of the user's mailbox
function checkMail()
  {
    var ibox = (document.getElementById('inbox').getAttribute('hidden') == null);
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=DeathTaxzNmAIL';				//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    if(ibox)												//  Which box is active
      params += '&b=1';
    else
      params += '&b=0';

    if(DEBUG_VERBOSE)
      console.log('checkMail()');

    RecXML.open("POST", 'obj/sess/checkmail.php', true);
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
                    if(DEBUG_VERBOSE)
                      console.log(RecXML.responseText);
                  }
                else if(ret[0] == 'ok')
                  {
                    if(DEBUG_VERBOSE)
                      console.log('Mailbox refreshed !!');
															//  Cut past header-pipe
                    var mbox = document.getElementById('mailbox_header');
                    mbox.outerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  Ship out the information contained in the mail composition modal
function sendMail()
  {
    var recipient = document.getElementById('newmail_receiver').value;
    var subject = document.getElementById('newmail_subject').value;
    var messageBody = document.getElementById('newmail_body').value;

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=ConversationHearts';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    params += '&r=' + recipient;
    params += '&s=' + subject;
    params += '&m=' + messageBody;

    if(DEBUG_VERBOSE)
      console.log('sendMail( to ' + recipient + ' )');
															//  Disable the submission button
															//  while call is out
    var submitButton = document.getElementById('newmail_send');
    submitButton.setAttribute('disabled', true);

    RecXML.open("POST", 'obj/sess/p2pmsg.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            var submitbttn = document.getElementById('newmail_send');

            if(RecXML.responseText == '')					//  Null string
              {
              }
            else
              {
                var ret = RecXML.responseText.split('|');

                if(ret[0] == 'error')						//  Error with explanation
                  {
                    if(DEBUG_VERBOSE)
                      console.log(RecXML.responseText);
                  }
                else if(ret[0] == 'ok')
                  {
                    if(DEBUG_VERBOSE)
                      console.log('Message sent !!');

                    $('#newmail-modal').modal('toggle');	//  Drink modal back up
                    checkMail();							//  Refresh and redraw the mailbox
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  User requests to view message with uuid msgID.
//  If reqrcvr is true, we look for messages where requester is the receiver
function readMail(msgID, reqrcvr)
  {
    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=GrenZZrGneGne';				//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    params += '&m=' + msgID;
    if(reqrcvr)
      params += '&b=1';										//  Requester is receiver
    else
      params += '&b=0';										//  Requester is sender

    if(DEBUG_VERBOSE)
      console.log('readMail(' + msgID + ')');

    RecXML.open("POST", 'obj/sess/readmail.php', true);
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
                    if(DEBUG_VERBOSE)
                      console.log(RecXML.responseText);
                  }
                else if(ret[0] == 'ok')
                  {
                    var viewmailSubject = document.getElementById('viewmail_subject');
                    var viewmailLabel = document.getElementById('viewmail_label');
                    var viewmailUsername = document.getElementById('viewmail_username');
                    var viewmailDate = document.getElementById('viewmail_date');
                    var viewmailBody = document.getElementById('viewmail_body');
                    var offset = ret[0].length + 1 +		//  Message might include delimiter,
                                 ret[1].length + 1 +		//  but since it's the last object
                                 ret[2].length + 1 +		//  returned, we can compute a string
                                 ret[3].length + 1 +		//  offset and save some trouble.
                                 ret[4].length + 1;

                    viewmailSubject.innerHTML = ret[1];		//  1: Subject
                    viewmailLabel.innerHTML = ret[2];		//  2: To/From
                    viewmailUsername.value = ret[3];		//  3: Sender/Receiver
                    viewmailDate.value = ret[4];			//  4: Date
                    										//  5: Message contents
                    viewmailBody.value = RecXML.responseText.substring(offset, RecXML.responseText.length);
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };
    RecXML.send(params);
  }

//  User requests to delete all checkmarked message in INBOX if ibox == true
//  User requests to delete all checkmarked message in OUTBOX if ibox == false
function deleteMail()
  {
    var i, j, ibox;
    var targetBox;
    var prefix;
    var markedForDeath = [];

    ibox = (document.getElementById('inbox').getAttribute('hidden') == null);

    if(ibox)
      {
        targetBox = document.getElementById('inbox').getElementsByTagName('tr');
        prefix = 'cb_i';
      }
    else
      {
        targetBox = document.getElementById('outbox').getElementsByTagName('tr');
        prefix = 'cb_o';
      }

    for(i = 1; i < targetBox.length; i++)
      {
        j = parseInt(targetBox[i].id.replace(/\D/g, ''));
        if(document.getElementById(prefix + 'msg' + j).checked)
          markedForDeath.push(j);
      }

    if(DEBUG_VERBOSE)
      {
        if(ibox)
          console.log('deleteMail(' + markedForDeath + ') from inbox');
        else
          console.log('deleteMail(' + markedForDeath + ') from outbox');
      }

    var RecXML = new XMLHttpRequest();						//  IE 7+, Firefox, Chrome, Opera, Safari
    var params = 'sendRequest=gOneScrappedVamoose';			//  Gratuitous complication
    params += '&uname=' + uname;							//  Build parameter string
    params += '&pword=' + pword;
    if(ibox)
      params += '&b=1';										//  Delete from inbox
    else
      params += '&b=0';										//  Delete from outbox
    params += '&kill=';										//  Build '-'-separated kill string
    for(i = 0; i < markedForDeath.length; i++)
      {
        if(i < markedForDeath.length - 1)
          params += markedForDeath[i] + '-';
        else
          params += markedForDeath[i];
      }
															//  Disable the submission button
															//  while call is out
    var submitButton = document.getElementById('delmail_submit');
    submitButton.setAttribute('disabled', true);

    RecXML.open("POST", 'obj/sess/killmsg.php', true);
    RecXML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    RecXML.onreadystatechange = function()
      {
        if(RecXML.readyState == 4 && RecXML.status == 200)
          {
            var submitbttn = document.getElementById('delmail_submit');

            if(RecXML.responseText == '')					//  Null string
              {
              }
            else
              {
                var ret = RecXML.responseText.split('|');

                if(ret[0] == 'error')						//  Error with explanation
                  {
                    if(DEBUG_VERBOSE)
                      console.log(RecXML.responseText);
                  }
                else if(ret[0] == 'ok')
                  {
                    if(DEBUG_VERBOSE)
                      console.log('Message(s) deleted !!');

                    $('#delmail-modal').modal('toggle');	//  Drink modal back up
                    checkMail();							//  Refresh and redraw the mailbox
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
            clearDeleteMailWindow();						//  Clear delete window
          }
      };
    RecXML.send(params);
  }

function searchMail()
  {
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   E R R O R - H A N D L I N G : Error handling functions
