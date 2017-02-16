<head>
    <title>ShopTalk</title>
    <link rel = "stylesheet" type= "text/css" href = "css/bootstrap.css">
    <link rel = "stylesheet" type= "text/css" href = "css/bootstrap-theme.css">
    <link rel = "stylesheet" type= "text/css" href = "css/custom.css">
  <meta id="meta" name="viewport" content="width=device-width; initial-scale=1.0" />
</head>


<div id= "discussion-page">
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
              <div class = "container-fluid">
                <div class = "row">

                  <div class = "col-sm-10">
                    <h1 class = "text-center discussion-header">Discussion Board</h1>
                  </div>

                  <div class = "col-sm-2">
                    <button type = "button" class = "new-discussion-button font-26" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">+</button>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

          <div class = "row ">
            <div class = "col-sm-12">
              <div class = "container-fluid">
                <div class="collapse" id="collapseExample">
                  <div class="card card-block">
                    <div class = "disc-panel">
                      <form action="#" method="post">
                        <h2 class = "new-disc-header font-24">New Discussion</h2>
                          <hr>
                        <h2 class = "discussion-title">Discussion Title</h2>
                        <input class="disc-title-text" name="discussion-title" placeholder="Your title goes here." type="text"><br><br>
                        <textarea class="disc-text" name="discussion-text" placeholder="Your text goes here." rows="10"></textarea>
                     <button type="button" class="btn-default disc-button">Create</button>
                    </form>
                    </div>
                  </div>
                </div>
              </div>
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
