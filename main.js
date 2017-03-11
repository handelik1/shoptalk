/*****************************************************************************************
     M A I N:
 This is the script for system variables and main loops. This is the engine that keeps
 front-end processes moving.
*****************************************************************************************/

const DEBUG_VERBOSE = true;									//  Whether to display debugging notes

const SHOPTALK_NOTHING = 0;
const SHOPTALK_MAILBOX = 1;									//  ShopTalk has "stations."
const SHOPTALK_CHAT = 2;									//  Track which one is in use so we
const SHOPTALK_POSTS = 3;									//  can refresh to it correctly
const SHOPTALK_ACCOUNT = 4;
const SHOPTALK_IN_SESSION = 5;

const REFRESH_INTERVAL = 1000;								//  Milliseconds to refresh

var uname = '';
var pword = '';
var currentStation = SHOPTALK_NOTHING;

function loop()
  {
    switch(currentStation)
      {
        case SHOPTALK_MAILBOX:     if(callComplete_refreshMailbox)
                                     checkMail();
                                   break;
        case SHOPTALK_CHAT:        if(callComplete_refreshChatDirectory)
                                     refreshChatDirectories();
                                   break;
        case SHOPTALK_IN_SESSION:  if(callComplete_refreshChatSession)
                                     heartbeat();
                                   break;
      }
    var t = setTimeout(loop, 500);
  }