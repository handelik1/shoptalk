/*****************************************************************************************
     M A I N:
 This is the script for system variables and main loops. This is the engine that keeps
 front-end processes moving.
*****************************************************************************************/

const DEBUG_VERBOSE = true;									//  Whether to display debugging notes

const SHOPTALK_MAILBOX = 0;									//  ShopTalk has three "stations."
const SHOPTALK_CHAT = 1;									//  Track which one is in use so we
const SHOPTALK_POSTS = 2;									//  can refresh to it correctly

var uname = '';
var pword = '';
var currentStation = SHOPTALK_MAILBOX;						//  Default is mailbox/welcome

