<?php

 session_start();													#  Create file to hold session identifier
 $current_time = time();											#  Save current time
 srand($current_time);												#  Seed randomizer

 function get_session_serial()										#  Generate a session uuid
  {
    $uuid32     = md5(uniqid(rand(), true));
    $uuid       = substr($uuid32, 0, 8)
                . substr($uuid32, 8, 4)
                . substr($uuid32, 12, 4)
                . substr($uuid32, 16, 4)
                . substr($uuid32, 20, 12);
    return $uuid;
  }

 if(isset($_SESSION['logged_in']))									#  Are you already logged in?
   {
     $docstr  = '<html>';
     																#  Head
     $docstr .=   '<head>';
     $docstr .=     '<title>ShopTalk</title>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/bootstrap.css"/>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.css"/>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/custom.css"/>';
     $docstr .=     '<meta charset="UTF-8"/>';
     $docstr .=     '<meta name="keywords" content="interactive,tutor,student,technical,illustration,chat,forum"/>';
     $docstr .=     '<meta id="meta" name="viewport" content="width=device-width; initial-scale=1.0" />';
     ################################################################  Include scripts for all our AJAX calls:
    																#  Ajax functions for account log in/out,
    																#  creation and modification
     $docstr .=     '<script type="text/javascript" src="obj/ajax/account/ajax.js"></script>';
    																#  Ajax functions for chat
     $docstr .=     '<script type="text/javascript" src="obj/ajax/chat/ajax.js"></script>';
    																#  Ajax functions for messaging system
     $docstr .=     '<script type="text/javascript" src="obj/ajax/mail/ajax.js"></script>';
    																#  Ajax functions for bulletin board
     $docstr .=     '<script type="text/javascript" src="obj/ajax/post/ajax.js"></script>';

     $docstr .=     '<script type="text/javascript" src="GUI.js"></script>';
     $docstr .=     '<script type="text/javascript" src="main.js"></script>';
     $docstr .=   '</head>';
     																#  Body
     $docstr .=   '<body onload="refresh()">';
     $docstr .=   '</body>';
    																#  Include other libraries and utilities
     $docstr .=   '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>';
     $docstr .=   '<script src="js/jquery.mobile.custom.js"></script>';
     $docstr .=   '<script src="js/bootstrap.min.js"></script>';
     $docstr .= '</html>';
   }
 else																#  Otherwise, build the login page
   {
     $_SESSION['uuid'] = get_session_serial();						#  Create session identifier
     $_SESSION['created'] = $current_time;							#  Set session creation time
     $_SESSION['last_access'] = $current_time;						#  Set session last access time

     $docstr  = '<html>';
     																#  Head
     $docstr .=   '<head>';
     $docstr .=     '<title>ShopTalk</title>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/bootstrap.css"/>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/bootstrap-theme.css"/>';
     $docstr .=     '<link rel="stylesheet" type="text/css" href="css/custom.css"/>';
     $docstr .=     '<meta charset="UTF-8"/>';
     $docstr .=     '<meta name="keywords" content="interactive,tutor,student,technical,illustration,chat,forum"/>';
     $docstr .=     '<meta id="meta" name="viewport" content="width=device-width; initial-scale=1.0" />';
     ################################################################  Include scripts for all our AJAX calls:
    																#  Ajax functions for account log in/out,
    																#  creation and modification
     $docstr .=     '<script type="text/javascript" src="obj/ajax/account/ajax.js"></script>';
    																#  Ajax functions for chat
     $docstr .=     '<script type="text/javascript" src="obj/ajax/chat/ajax.js"></script>';
    																#  Ajax functions for messaging system
     $docstr .=     '<script type="text/javascript" src="obj/ajax/mail/ajax.js"></script>';
    																#  Ajax functions for bulletin board
     $docstr .=     '<script type="text/javascript" src="obj/ajax/post/ajax.js"></script>';
     $docstr .=   '</head>';
     																#  Body
     $docstr .=   '<body style="background-color: #000000" onload="begin()">';
     																#  Nav
     $docstr .=     '<nav class="navbar navbar-default main-nav" role="navigation">';
     $docstr .=       '<div class="container">';
     $docstr .=         '<div class="navbar-header navbar-header-text">';
     $docstr .=           '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">';
     $docstr .=             '<span class="sr-only">Toggle navigation</span>';
     $docstr .=             '<span class="icon-bar"></span>';
     $docstr .=             '<span class="icon-bar"></span>';
     $docstr .=             '<span class="icon-bar"></span>';
     $docstr .=           '</button>';
     $docstr .=           '<a onclick="reload()" class="navbar-brand font-24 shoptalk-brand">SynergyGirl</a>';
     $docstr .=         '</div>';
     $docstr .=         '<div class="collapse navbar-collapse" id="navbar-collapse-1" >';
     $docstr .=           '<ul class="nav navbar-nav pull-right drop-nav">';
     $docstr .=             '<li>';
     $docstr .=               '<a class="shoptalk-brand font-16" data-toggle="modal" data-target="#reg-modal">Register</a>';
     $docstr .=             '</li>';
     $docstr .=             '<li>';
     $docstr .=               '<a class="shoptalk-brand font-16" data-toggle="modal" data-target="#signin-modal">Sign in</a>';
     $docstr .=             '</li>';
     $docstr .=           '</ul>';
     $docstr .=         '</div>';
     $docstr .=       '</div>';
     $docstr .=     '</nav>';
     																#  Registration modal
     $docstr .=     '<div class="modal fade" id="reg-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
     $docstr .=       '<div class="credential-panel" id="reg-panel">';
     $docstr .=         '<div class="credential-form" id="reg-form">';
     $docstr .=           '<h2 class="sign-in font-24">Registration</h2>';
     $docstr .=           '<hr style="margin-top: 0px"></hr>';
     $docstr .=           '<label class="credential-label" style="margin-top: 0px">First Name</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_firstname" type="text">';
     $docstr .=           '<label class="credential-label">Last Name</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_lastname" type="text">';
     $docstr .=           '<label class="credential-label">Email Address</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_email" type="text">';
     $docstr .=           '<label class="credential-label">Institution</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_institution" type="text">';
     $docstr .=           '<label class="credential-label">User Name</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_uname" type="text">';
     $docstr .=           '<label class="credential-label">Password</label>';
     $docstr .=           '<input class="reg-credential passpop" id="reqacc_pword" name="password" type="password" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="Password must be at least 8 characters long, contain at least one letter and at least 1 number.">';
     $docstr .=           '<label class="credential-label">Confirm Password</label>';
     $docstr .=           '<input class="reg-credential" id="reqacc_confirmpword" type="password">';
     $docstr .=           '<label class="reg-credential" style="color: #FF0000; max-width: 65%;" id="reqacc_notice"></label>';
     $docstr .=           '<input type="submit" class="btn-default register-button" id="reqacc_submit" value="Register" onclick="reqacc();">';
     $docstr .=         '</div>';
     $docstr .=       '</div>';
     $docstr .=     '</div>';
     																#  Sign-in modal
     $docstr .=     '<div class="modal fade" id="signin-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
     $docstr .=       '<div class="modal-dialog" role="document">';
     $docstr .=         '<div class="card card-block">';
     $docstr .=           '<div class="credential-panel signin-panel">';
     $docstr .=             '<div class="credential-form">';
     $docstr .=               '<h2 class="sign-in font-24">Sign in</h2>';
     $docstr .=               '<hr style="margin-top: 0px"></hr>';
     $docstr .=               '<label class="credential-label">User Name</label>';
     $docstr .=               '<input class="signin-credential" id="login_uname" type="text" required><br>';
     $docstr .=               '<label class="credential-label">Password</label>';
     $docstr .=               '<input class="signin-credential" id="login_pword" type="password" required>';
     $docstr .=               '<label class="reg-credential" style="color: #FF0000; max-width: 65%;" id="login_notice"></label>';
     $docstr .=               '<input type="submit" id="login_submit" class="btn-default signin-button" value="Submit" onclick="login();">';
     $docstr .=             '</div>';
     $docstr .=           '</div>';
     $docstr .=         '</div>';
     $docstr .=       '</div>';
     $docstr .=     '</div>';
     																#  Center-stage carousel
     $docstr .=     '<div class="jumbotron main-jumbo main-jumbo-shrink">';
     																#  data-interval="false"
     																#  prevents auto-transition
     $docstr .=       '<div id="myCarousel" class="carousel slide" data-interval="false" data-ride="carousel">';
     																#  Indicators
     $docstr .=         '<ol class="carousel-indicators">';
     $docstr .=           '<li data-target="#myCarousel" data-slide-to="0" class="active"></li>';
     $docstr .=           '<li data-target="#myCarousel" data-slide-to="1"></li>';
     $docstr .=           '<li data-target="#myCarousel" data-slide-to="2"></li>';
     $docstr .=         '</ol>';
     																#  Wrappers for slides
     $docstr .=         '<div class="carousel-inner" role="listbox">';
     $docstr .=           '<div class="item active">';
     $docstr .=             '<h1 class="text-center main-header">ShopTalk</h1>';
     $docstr .=             '<h2 class="text-center main-header main-header-subtext">Interactive Tutoring System</h2>';
     $docstr .=           '</div>';
     $docstr .=           '<div class="item">';
     $docstr .=             '<h1 class="text-center main-header">Private and Group Chat</h1>';
     $docstr .=             '<h2 class="text-center main-header main-header-subtext">Learn new skills from your peers all in real-time</h2>';
     $docstr .=           '</div>';
     $docstr .=           '<div class="item">';
     $docstr .=             '<h1 class="text-center main-header">Discussion Forum</h1>';
     $docstr .=             '<h2 class="text-center main-header main-header-subtext">Share your thoughts and hear from others too</h2>';
     $docstr .=           '</div>';
     $docstr .=         '</div>';
     																#  Left and right controls
     $docstr .=         '<a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">';
     $docstr .=           '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>';
     $docstr .=           '<span class="sr-only">Previous</span>';
     $docstr .=         '</a>';
     $docstr .=         '<a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">';
     $docstr .=           '<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
     $docstr .=           '<span class="sr-only">Next</span>';
     $docstr .=         '</a>';
     $docstr .=       '</div>';
     $docstr .=     '</div>';
     $docstr .=   '</body>';

     $docstr .=   '<footer>';
     $docstr .=     '<div class="container-fluid">';
     $docstr .=       '<p class="text-muted">Copyright 2017</p>';
     $docstr .=     '</div>';
     $docstr .=   '</footer>';
    																#  Include other libraries and utilities
     $docstr .=   '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>';
     $docstr .=   '<script src="js/jquery.mobile.custom.js"></script>';
     $docstr .=   '<script src="js/bootstrap.min.js"></script>';
    																#  Include our scripts
     $docstr .=   '<script type="text/javascript" src="GUI.js"></script>';
     $docstr .=   '<script type="text/javascript" src="main.js"></script>';

     $docstr .= '</html>';
   }

 echo $docstr;
 ?>