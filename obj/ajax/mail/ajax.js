/*****************************************************************************************
     A J A X : M A I L
 All functions which update parts of the mail system page without our ever having to leave
 the page. Depending on the values they return from the system, these functions manipulate
 the front-end display themselves.
*****************************************************************************************/

var callComplete_refreshMailbox = true;						//  Prevent refresh calls from piling up

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

    RecXML.open("POST", 'obj/sess/mail/checkmail.php', true);
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
                    if(mbox != null)
                      mbox.outerHTML = RecXML.responseText.substring(3, RecXML.responseText.length);

                    callComplete_refreshMailbox = true;		//  Reset flag
                  }
                else										//  Anything other than what we expect
                  {
                  }
              }
          }
      };

    callComplete_refreshMailbox = false;					//  Block while call is out
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

    RecXML.open("POST", 'obj/sess/mail/p2pmsg.php', true);
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

    RecXML.open("POST", 'obj/sess/mail/readmail.php', true);
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

                    if(viewmailSubject != null)
                      viewmailSubject.innerHTML = ret[1];	//  1: Subject
                    if(viewmailLabel != null)
                      viewmailLabel.innerHTML = ret[2];		//  2: To/From
                    if(viewmailUsername != null)
                      viewmailUsername.value = ret[3];		//  3: Sender/Receiver
                    if(viewmailDate != null)
                      viewmailDate.value = ret[4];			//  4: Date
                    										//  5: Message contents
                    if(viewmailBody != null)
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

    RecXML.open("POST", 'obj/sess/mail/killmsg.php', true);
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
//   E R R O R - H A N D L I N G : Error handling functions for person-to-person messages
