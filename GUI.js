/*****************************************************************************************
     G U I:
 This is the script for various interface initializations and updates.
*****************************************************************************************/

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
    switch(m)
      {
        case '': 												//  Smartphone view
                 var elem = document.getElementById('nav_home_li');
                 elem.setAttribute('hidden', true);				//  Hide the left-hand nav homepage item
                 break;
      }
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
  }
