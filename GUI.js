/*****************************************************************************************
     G U I:
 This is the script for various interface initializations and updates.
*****************************************************************************************/

const MARKER_BLACK = "#000000";
const MARKER_GREEN = "#008150";
const MARKER_RED = "#FA2D27";
const MARKER_BLUE = "#2F5DA6";
const MARKER_ORANGE = "#FD8A03";
const MARKER_PURPLE = "#C83771";

var penDown = false;											//  Whether user is drawing
var drawingInstructionBuffer = [];								//  Cache drawing instructions to post
var context = null;
var markerColor = MARKER_RED;

//  Attach event handlers to carousel and password tooltip popup
function begin()
  {
    $("#myCarousel").swiperight(function()
      {
        $("#myCarousel").carousel('prev');
      });
    $("#myCarousel").swipeleft(function()
      {
        $("#myCarousel").carousel('next');
      });

    $(function()
      {
        $("#password").focus(function()
          {
            $('[data-toggle="popover"]').popover()
          });
      });

    $('.passpop').popover().click(function()
      {
        setTimeout(function()
          {
            $('.passpop').popover('hide');
          }, 3000);
      });
  }

//  This function gets triggered whenever the screen size changes, either because we altered
//  the screen or because some one viewing the page on a mobile device rotated that device.
//  In any case, we've got to figure out which elements to hide/reveal now, given the new dimensions.
function layoutMode(m)
  {
/*
    switch(m)
      {
        case '': 												//  Smartphone view
                 var elem = document.getElementById('nav_home_li');
                 elem.setAttribute('hidden', true);				//  Hide the left-hand nav homepage item
                 break;
      }
*/
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   D R A W I N G

function enableDrawing()
  {
    var Canvas = document.getElementById('canvas');
    if(Canvas != null)
      {
        context = Canvas.getContext("2d");

        Canvas.addEventListener('mousedown', startDraw, false);
        Canvas.addEventListener('touchstart', startDraw, false);

        Canvas.addEventListener('mousemove', moveDraw, false);
        Canvas.addEventListener('touchmove', moveDraw, false);

        Canvas.addEventListener('mouseup', stopDraw, false);
        Canvas.addEventListener('touchend', stopDraw, false);

        Canvas.addEventListener('mouseleave', stopDraw, false);
      }
  }

function disableDrawing()
  {
    var Canvas = document.getElementById('canvas');
    if(Canvas != null)
      {
        Canvas.removeEventListener('mousedown', startDraw);
        Canvas.removeEventListener('touchstart', startDraw);

        Canvas.removeEventListener('mousemove', moveDraw, false);
        Canvas.removeEventListener('touchmove', moveDraw, false);

        Canvas.removeEventListener('mouseup', stopDraw, false);
        Canvas.removeEventListener('touchend', stopDraw, false);

        Canvas.removeEventListener('mouseleave', stopDraw, false);

        context = null;
      }
  }

function startDraw(e)
  {
    if(!penDown)
      {
        if(DEBUG_VERBOSE)
          console.log('startDraw()');

        penDown = true;
        var nav = $('.sidebar-nav')[0];
        var c = document.getElementById('canvas');
        while(drawingInstructionBuffer.length > 0)				//  Empty the array
          drawingInstructionBuffer.pop();

        drawingInstructionBuffer.push( [ e.pageX * 0.5, e.pageY * 0.5 ] );
      }
  }

function moveDraw(e)
  {
    if(penDown)
      {
        if(DEBUG_VERBOSE)
          console.log('moveDraw()');

        var nav = $('.sidebar-nav')[0];
        var c = document.getElementById('canvas');
        drawingInstructionBuffer.push( [ e.pageX * 0.5, e.pageY * 0.5 ] );
        canvasDraw();
      }
  }

//  Transmit drawing data you've generated on the session whiteboard
function stopDraw(e)
  {
    if(penDown)
      {
        if(DEBUG_VERBOSE)
          console.log('stopDraw()');

        penDown = false;
        if(drawingInstructionBuffer.length > 1)
          draw();												//  Post the drawing event to DB
      }
  }

//  Render the mouse/touch data to the WebGL context
function canvasDraw()
  {
    var i;

    if(context != null)
      {
        context.clearRect(0, 0, context.canvas.width, context.canvas.height);
        context.strokeStyle = markerColor;
        context.lineJoin = "round";
        context.lineWidth = 5;

        for(i = 1; i < drawingInstructionBuffer.length; i++)
          {
            context.beginPath();
            context.moveTo( drawingInstructionBuffer[i - 1][0],
                            drawingInstructionBuffer[i - 1][1] );
            context.lineTo( drawingInstructionBuffer[i][0],
                            drawingInstructionBuffer[i][1] );
            context.closePath();
            context.stroke();
          }
      }
    else
      console.log('CONTEXT IS NULL');
  }

//  Transmit clear drawing signal
function clearDrawingBoard()
  {
    if(context != null)
      {
        context.clearRect(0, 0, context.canvas.width, context.canvas.height);
      }
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   A C C O U N T : T O G G L E S

//  Make the profile editing panel visible
function showProfile()
  {
    if(DEBUG_VERBOSE)
      console.log('showProfile()');

    var profile = document.getElementById('profile_panel');
    var profileTab = document.getElementById('profile_tab');
    var tabLink;
    if(profile != null)
      profile.removeAttribute('hidden');
    if(profileTab != null)
      {
        profileTab.className = 'active';
        tabLink = profileTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the profile editing panel
function hideProfile()
  {
    if(DEBUG_VERBOSE)
      console.log('hideProfile()');

    var profile = document.getElementById('profile_panel');
    var profileTab = document.getElementById('profile_tab');
    var tabLink;
    if(profile != null)
      profile.setAttribute('hidden', true);
    if(profileTab != null)
      {
        profileTab.removeAttribute('class');
        tabLink = profileTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideGroups(); showProfile(); };
      }
  }

//  Make the groups editing panel visible
function showGroups()
  {
    if(DEBUG_VERBOSE)
      console.log('showGroups()');

    var groups = document.getElementById('groups_panel');
    var groupsTab = document.getElementById('groups_tab');
    var tabLink;
    if(groups != null)
      groups.removeAttribute('hidden');
    if(groupsTab != null)
      {
        groupsTab.className = 'active';
        tabLink = groupsTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the groups editing panel
function hideGroups()
  {
    if(DEBUG_VERBOSE)
      console.log('hideGroups()');

    var groups = document.getElementById('groups_panel');
    var groupsTab = document.getElementById('groups_tab');
    var tabLink;
    if(groups != null)
      groups.setAttribute('hidden', true);
    if(groupsTab != null)
      {
        groupsTab.removeAttribute('class');
        tabLink = groupsTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideProfile(); showGroups(); };
      }
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   A C C O U N T : L I S T - B U I L D I N G

function addGroupMember()
  {
    if(DEBUG_VERBOSE)
      console.log('addGroupMember()');

    var tableBody = document.getElementById('newgroup_members_tbody');
    var usernameField = document.getElementById('newgroup_membersearch');
    var candidate = usernameField.value;						//  Not admitted until proven unique to list
    var admittedMembers = [];									//  List of all members in group so far
    var i;
    var newString;												//  Has to be added all at once

    if(candidate.length > 0)									//  Don't bother with an empty string
      {
        for(i = 0; i < tableBody.rows.length; i++)				//  Build list of admitted group members
          {
            if(admittedMembers.indexOf(tableBody.rows[i].cells[0].innerHTML) < 0)
              admittedMembers.push(tableBody.rows[i].cells[0].innerHTML);
          }

        if(admittedMembers.indexOf(candidate) < 0)
          {
            newString  = '<tr>';
            newString += '<td>' + candidate + '</td>';
            newString += '<td>';
            newString += '<a href="javascript:;" onclick="removeGroupMember(' + (admittedMembers.length) + ');">';
            newString += '<img src="./img/minus.png" alt="Remove from group"/>';
            newString += '</a></td>';
            newString += '</tr>';

            tableBody.innerHTML += newString;
          }

        usernameField.value = '';								//  Blank the entry field
      }
  }

function removeGroupMember(i)
  {
    if(DEBUG_VERBOSE)
      console.log('removeGroupMember(' + i + ')');

    var tableBody = document.getElementById('newgroup_members_tbody');
    var admittedMembers = [];									//  List of all members in group so far
    var j;
    var newString = '';											//  To replace whole <tbody>

    for(j = 0; j < tableBody.rows.length; j++)					//  Build list of admitted group members
      {
        if(admittedMembers.indexOf(tableBody.rows[j].cells[0].innerHTML) < 0)
          admittedMembers.push(tableBody.rows[j].cells[0].innerHTML);
      }

    admittedMembers.splice(i, 1);								//  Remove the i-th element

    for(j = 0; j < admittedMembers.length; j++)					//  Rebuild table from edited list
      {
        newString += '<tr>';
        newString += '<td>' + admittedMembers[j] + '</td>';
        newString += '<td>';
        newString += '<a href="javascript:;" onclick="removeGroupMember(' + j + ');">';
        newString += '<img src="./img/minus.png" alt="Remove from group"/>';
        newString += '</a></td>';
        newString += '</tr>';
      }

    tableBody.innerHTML = newString;							//  Replace whole table
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   A C C O U N T : T O G G L E S

//  Make the profile editing panel visible
function showActive()
  {
    if(DEBUG_VERBOSE)
      console.log('showActive()');

    var active = document.getElementById('active');
    var activeTab = document.getElementById('active_tab');
    var tabLink;
    if(active != null)
      active.removeAttribute('hidden');
    if(activeTab != null)
      {
        activeTab.className = 'active';
        tabLink = activeTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the profile editing panel
function hideActive()
  {
    if(DEBUG_VERBOSE)
      console.log('hideActive()');

    var active = document.getElementById('active');
    var activeTab = document.getElementById('active_tab');
    var tabLink;
    if(active != null)
      active.setAttribute('hidden', true);
    if(activeTab != null)
      {
        activeTab.removeAttribute('class');
        tabLink = activeTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideArchivedChat(); showActive(); };
      }
  }

//  Make the groups editing panel visible
function showArchivedChat()
  {
    if(DEBUG_VERBOSE)
      console.log('showArchivedChat()');

    var archived = document.getElementById('archived');
    var archivedTab = document.getElementById('archivedchat_tab');
    var tabLink;
    if(archived != null)
      archived.removeAttribute('hidden');
    if(archivedTab != null)
      {
        archivedTab.className = 'active';
        tabLink = archivedTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the groups editing panel
function hideArchivedChat()
  {
    if(DEBUG_VERBOSE)
      console.log('hideArchivedChat()');

    var archived = document.getElementById('archived');
    var archivedTab = document.getElementById('archivedchat_tab');
    var tabLink;
    if(archived != null)
      archived.setAttribute('hidden', true);
    if(archivedTab != null)
      {
        archivedTab.removeAttribute('class');
        tabLink = archivedTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideActive(); showArchivedChat(); };
      }
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   C H A T : A D D / R E M O V E   M E M B E R / G R O U P

//  Open the invitation modal
function popInvitePanel()
  {
    if(DEBUG_VERBOSE)
      console.log('popInvitePanel()');

    $('#invitechat-modal').modal('toggle');					//  Display modal
  }

//  Add a SINGLE MEMBER to the Chat Session invitiation list
function addChatInviteMember()
  {
    if(DEBUG_VERBOSE)
      console.log('addChatInviteMember()');

    var tableBody = document.getElementById('chatinvite_tbody');
    var usernameField = document.getElementById('invitechat_membersearch');
    var candidate = usernameField.value;						//  Not admitted until proven unique to list
    var admittedMembers = [];									//  List of all members in group so far
    var i;
    var newString;												//  Has to be added all at once

    if(candidate.length > 0)									//  Don't bother with an empty string
      {
        for(i = 0; i < tableBody.rows.length; i++)				//  Build list of admitted group members
          {
            if(admittedMembers.indexOf(tableBody.rows[i].cells[0].innerHTML) < 0)
              admittedMembers.push(tableBody.rows[i].cells[0].innerHTML);
          }

        if(admittedMembers.indexOf(candidate) < 0)
          {
            newString  = '<tr>';
            newString += '<td class="user-invitation">' + candidate + '</td>';
            newString += '<td>';
            newString += '<a href="javascript:;" onclick="removeChatInvite(' + (admittedMembers.length) + ');">';
            newString += '<img src="./img/minus.png" alt="Remove from invitation"/>';
            newString += '</a></td>';
            newString += '</tr>';

            tableBody.innerHTML += newString;
          }

        usernameField.value = '';								//  Blank the entry field
      }
  }

//  Add a GROUP to the Chat Session invitiation list
function addChatInviteGroup()
  {
    if(DEBUG_VERBOSE)
      console.log('addChatInviteGroup()');
  }

//  Remove a MEMBER OR GROUP from the Chat Session invitiation list
function removeChatInvite(i)
  {
    if(DEBUG_VERBOSE)
      console.log('removeChatInvite(' + i + ')');

    var tableBody = document.getElementById('chatinvite_tbody');
    var admittedMembers = [];									//  List of all members in group so far
    var j;
    var newString = '';											//  To replace whole <tbody>

    for(j = 0; j < tableBody.rows.length; j++)					//  Build list of admitted group members
      {
        if(admittedMembers.indexOf(tableBody.rows[j].cells[0].innerHTML) < 0)
          admittedMembers.push(tableBody.rows[j].cells[0].innerHTML);
      }

    admittedMembers.splice(i, 1);								//  Remove the i-th element

    for(j = 0; j < admittedMembers.length; j++)					//  Rebuild table from edited list
      {
        newString += '<tr>';
        newString += '<td>' + admittedMembers[j] + '</td>';
        newString += '<td>';
        newString += '<a href="javascript:;" onclick="removeChatInvite(' + j + ');">';
        newString += '<img src="./img/minus.png" alt="Remove from invitation"/>';
        newString += '</a></td>';
        newString += '</tr>';
      }

    tableBody.innerHTML = newString;							//  Replace whole table
  }

//  Open the invitation modal
function popExpelPanel()
  {
    if(DEBUG_VERBOSE)
      console.log('popExpelPanel()');

    $('#expelchat-modal').modal('toggle');					//  Display modal
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   P O S T : A D D / R E M O V E   M E M B E R / G R O U P

//  Add the member or group identified in the text field to the post access grantees' list
function addShare()
  {
    if(DEBUG_VERBOSE)
      console.log('addShare()');

    var tableBody = document.getElementById('newshare_members_tbody');
    var usernameField = document.getElementById('newshare_membersearch');
    var candidate = usernameField.value;						//  Not admitted until proven unique to list
    var admittedMembers = [];									//  List of all members in group so far
    var i;
    var newString;												//  Has to be added all at once

    if(candidate.length > 0)									//  Don't bother with an empty string
      {
        for(i = 0; i < tableBody.rows.length; i++)				//  Build list of admitted group members
          {
            if(admittedMembers.indexOf(tableBody.rows[i].cells[0].innerHTML) < 0)
              admittedMembers.push(tableBody.rows[i].cells[0].innerHTML);
          }

        if(admittedMembers.indexOf(candidate) < 0)
          {
            newString  = '<tr>';
            newString += '<td>' + candidate + '</td>';
            newString += '<td>';
            newString += '<a href="javascript:;" onclick="removeShareMember(' + (admittedMembers.length) + ');">';
            newString += '<img src="./img/minus.png" alt="Remove access"/>';
            newString += '</a></td>';
            newString += '</tr>';

            tableBody.innerHTML += newString;
          }

        usernameField.value = '';								//  Blank the entry field
      }
  }

function removeShareMember(i)
  {
    if(DEBUG_VERBOSE)
      console.log('removeShareMember(' + i + ')');

    var tableBody = document.getElementById('newshare_members_tbody');
    var admittedMembers = [];									//  List of all members in group so far
    var j;
    var newString = '';											//  To replace whole <tbody>

    for(j = 0; j < tableBody.rows.length; j++)					//  Build list of admitted group members
      {
        if(admittedMembers.indexOf(tableBody.rows[j].cells[0].innerHTML) < 0)
          admittedMembers.push(tableBody.rows[j].cells[0].innerHTML);
      }

    admittedMembers.splice(i, 1);								//  Remove the i-th element

    for(j = 0; j < admittedMembers.length; j++)					//  Rebuild table from edited list
      {
        newString += '<tr>';
        newString += '<td>' + admittedMembers[j] + '</td>';
        newString += '<td>';
        newString += '<a href="javascript:;" onclick="removeShareMember(' + j + ');">';
        newString += '<img src="./img/minus.png" alt="Remove access"/>';
        newString += '</a></td>';
        newString += '</tr>';
      }

    tableBody.innerHTML = newString;							//  Replace whole table
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   M A I L : T O G G L E S

//  Make the inbox visible
function showInbox()
  {
    if(DEBUG_VERBOSE)
      console.log('showInbox()');

    var inbox = document.getElementById('inbox');
    var inboxTab = document.getElementById('inbox_tab');
    var tabLink;
    if(inbox != null)
      inbox.removeAttribute('hidden');
    if(inboxTab != null)
      {
        inboxTab.className = 'active';
        tabLink = inboxTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the inbox
function hideInbox()
  {
    if(DEBUG_VERBOSE)
      console.log('hideInbox()');

    var inbox = document.getElementById('inbox');
    var inboxTab = document.getElementById('inbox_tab');
    var tabLink;
    if(inbox != null)
      inbox.setAttribute('hidden', true);
    if(inboxTab != null)
      {
        inboxTab.removeAttribute('class');
        tabLink = inboxTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideOutbox(); showInbox(); };
      }
  }

//  Make the outbox visible
function showOutbox()
  {
    if(DEBUG_VERBOSE)
      console.log('showOutbox()');

    var outbox = document.getElementById('outbox');
    var outboxTab = document.getElementById('outbox_tab');
    var tabLink;
    if(outbox != null)
      outbox.removeAttribute('hidden');
    if(outboxTab != null)
      {
        outboxTab.className = 'active';
        tabLink = outboxTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() {};
      }
  }

//  Hide the inbox
function hideOutbox()
  {
    if(DEBUG_VERBOSE)
      console.log('hideOutbox()');

    var outbox = document.getElementById('outbox');
    var outboxTab = document.getElementById('outbox_tab');
    var tabLink;
    if(outbox != null)
      outbox.setAttribute('hidden', true);
    if(outboxTab != null)
      {
        outboxTab.removeAttribute('class');
        tabLink = outboxTab.getElementsByTagName('a')[0];
        tabLink.onclick = function() { hideInbox(); showOutbox(); };
      }
  }

//////////////////////////////////////////////////////////////////////////////////////////
//   M A I L : D E L E T E

//  Fills modal window with appropriate values
function setupDeleteMail()
  {
    var i, j, ibox;
    var targetBox;
    var prefix;
    var markedForDeath = [];

    var delmailHeader = document.getElementById('delmail_header');
    var delmailLabel = document.getElementById('delmail_label');
																//  Determine which box will have messages deleted
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

    /******************************************************************************************
     What to do if markedForDeath.length == 0 ???
     ******************************************************************************************/

    if(markedForDeath.length > 1)
      {
        delmailHeader.innerHTML = 'Delete Selected Messages';
        if(ibox)
          delmailLabel.innerHTML = 'Are you sure you want to delete these ' + markedForDeath.length + ' messages from your inbox?';
        else
          delmailLabel.innerHTML = 'Are you sure you want to delete these ' + markedForDeath.length + ' messages from your outbox?';
      }
    else
      {
        delmailHeader.innerHTML = 'Delete Selected Message';
        if(ibox)
          delmailLabel.innerHTML = 'Are you sure you want to delete this message from your inbox?';
        else
          delmailLabel.innerHTML = 'Are you sure you want to delete this message from your outbox?';
      }
  }

//  Remove the values appropriate to one call so that they don't appear in subsequent calls
function clearDeleteMailWindow()
  {
    var delmailHeader = document.getElementById('delmail_header');
    var delmailLabel = document.getElementById('delmail_label');
    delmailHeader.innerHTML= '';
    delmailLabel.innerHTML = '';
  }
