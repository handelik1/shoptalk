<head>
	<title>HawkTalk</title>
	<link rel = "stylesheet" type= "text/css" href = "css/bootstrap.css">
	<link rel = "stylesheet" type= "text/css" href = "css/bootstrap-theme.css">
	<link rel = "stylesheet" type= "text/css" href = "css/custom.css">
  <meta id="meta" name="viewport" content="width=device-width; initial-scale=1.0" />
</head>

<body>

<!--Home Page Start-->
<div id = "homepage">
  <div class = "container-fluid">
    <div class="row">

      <div class="col-sm-2">
        <?php
          include("nav.html");
        ?>
      </div>

      <div class="col-sm-8">

        <div class = "container-fluid">
          <div class = "row">
            <div class = "col-sm-12">
              <div class = "chat-area">
                <p>Text will end up here</p>
              </div>
            </div>
          </div>
        </div>

        <div class = "container-fluid">
            <div class = "row">
              <form method = "post" action = "#">
                <div class = "col-sm-10">
                  <input class = "chat-text" type = "text" name = "message"/>
                </div>
                <div class = "col-sm-2">
                  <input class = "btn btn-primary" type = "button" name = "submit" value = "Go"/>
                </div>
              </form>
            </div>
        </div>

      </div>

      <div class="col-sm-2">

        <div class="sidebar-nav-right">
          <div class="navbar navbar-default" role="navigation">
            <div class="nav">
              <a class = "brand font-24 block brand-color">Tools</a>
              <ul class="nav navbar-nav center">
                <li><a href="#">Item 1</a></li>
                <li><a href="#">Item 2</a></li>
                <li><a href="#">Item 3</a></li>
                <li><a href="#">Item 4</a></li>
              </ul>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
<!--HomePage END-->


<!--Discussion Start-->
<?php
include("discussion.php")
?>
<!--Discussion End -->


</body>


<footer>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</footer>

<script>

function getDiscussion(){
  document.getElementById("homepage").style.display = 'none';
  document.getElementById("discussion-page").style.display = 'block';
}

function getHome(){
  document.getElementById("homepage").style.display = 'block';
  document.getElementById("discussion-page").style.display = 'none';
}

</script>
<script>
$('.dropdown-toggle').click(function() {
    var location = $(this).attr('href');
    window.location.href = location;
    return false;
});


</script>