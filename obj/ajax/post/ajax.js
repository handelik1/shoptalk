/*****************************************************************************************
     A J A X : P O S T
 All functions which update parts of the post/bulletin board system page without our ever
 having to leave the page. Depending on the values they return from the system, these
 functions manipulate the front-end display themselves.
*****************************************************************************************/

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
                    document.body.innerHTML = ret[1];		//  Rewrite page asynchronously
                    currentStation = SHOPTALK_POSTS;		//  Set section tracker
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
//   B U L L E T I N - B O A R D : functions


//////////////////////////////////////////////////////////////////////////////////////////
//   E R R O R - H A N D L I N G : Error handling functions for person-to-person messages
