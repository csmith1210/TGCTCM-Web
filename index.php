<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="apple-touch-icon" sizes="76x76" href="images/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="images/favicon.png">
    
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    
    <title>TGCTours Card Maker</title>
    
    <link href="css/w3_style.css" rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(function() {
            //----- OPEN
            $('[data-popup-open]').on('click', function(e)  {
                var targeted_popup_class = jQuery(this).attr('data-popup-open');
                $('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);

                e.preventDefault();
            });

            //----- CLOSE
            $('[data-popup-close]').on('click', function(e)  {
                var targeted_popup_class = jQuery(this).attr('data-popup-close');
                $('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);

                e.preventDefault();
            });
        })
    </script>
    <?php
    $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
    ?>

  
</head>

<body>
    <div class="main" style="background-image: url('images/default.jpg')">
        <div class="cover"></div>
        <div class="container wrapper">
            <h1 class="logo">
                TGCTours Member Card Maker
            </h1>
            <div class="content">
                <h4 class="motto">Statistical image creator for forum signatures (or to brag).</h4>
                <div class="subscribe">
                    <h5 class="info-text">
                        Enter your TGCTours player ID, choose your template version, hit the button. 
                    </h5>
                    <div id="form" class="signin-form task-grid">
                        <form action="" method="post" class="w3_form_post">
                                <input type="text" name="id" placeholder="Enter your player ID... (e.g. 33673)" required="">
                                <select id="category" name="template" required="">
                                        <option value="" disabled selected style="display: none;">Template Selection</option>
                                        <option value="1">EmeraldPi</option>
                                        <option value="2">Pablo</option>
                                </select>
                                <input type="submit" value="Get Image">
                        </form>
                    </div>
                    <div id="hiddenDiv" class="signin-form" style="display:none; font-family:'Roboto', sans-serif;">
                        <h6>
                            <?php
                            if(isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['template']) && !empty($_POST['template'])) {
                                ?>
                                <script type="text/javascript">$('#form').hide(); $('#hiddenDiv').show();</script>
                                <div class="loader"><img src="images/loading.gif" /></div>
                                <?php
                                $id = $_POST['id'];
                                $template = $_POST['template'];
                                $url = "mkimg.php?id=$id&template=$template"; ?>
                                <img onload="$('.loader').hide();" src="<?php echo $url; ?>" alt="image" /><br />
                                <p style="font-size:0.75em;">* Image may appear slightly blurry on this page *</p><br />
                                <?php
                                echo "Copy and paste the following BBCode into your forum signature:";
                                ?>
                                <pre><br /><code>[IMG]<?php echo $root.'cards/'.$id; ?>.png[/IMG]</code></pre>
                                <?php
                            }
                            ?>
                        </h6>
                        <br />
                        <button id="return">RETURN HOME</button>
                        <script type="text/javascript">
                            $(function() {
                                $("#return").click( function()
                                    {
                                        var url = window.location.origin?window.location.origin+'/':window.location.protocol+'/'+window.location.host+'/';
                                        window.location.href = url;
                                    }
                                );
                            });
                        </script>
                    </div>
                    <h5 class="info-text" id="dbCount">
                        Currently <?php require 'rb.php'; R::setup('sqlite:red.db'); $users = R::findAll('user'); echo count($users); ?> players in the database.
                    </h5>
                </div>
            </div>
        </div>
        <ul class="footer">
          <li class="tags">
              <a class="btn" data-popup-open="popup-1" href="#">
                  <i class="fa fa-info-circle"></i>
                  Help
              </a>
          </li>
          <li class="tags">&nbsp;&bull;&nbsp;</li>
          <li class="tags">
              <a href="https://github.com/csmith1210/TGCTCM-Web">
                  <i class="fa fa-github"></i>
                  GitHub
              </a>
          </li>
        </ul>
    </div>
    <div class="popup" data-popup="popup-1" id="popcon">
        <div class="popup-inner">
            <h1>TGCTours Member Card Maker Help</h1>
            <br />
            <p>Your TGCTours player ID can be found in your profile page URL at TGCTours.com &gt; General Info &gt; Tour Info &gt; Your Tour Profile. The player ID is the number at the end of the URL.</p>
            <br />
            <p>The template choices are the different designs as created by users on the TGCTours Forums. The choices are as follows:</p>
            <br />
            <ul>
                <li>EmeraldPi: One template for every tour with the player's tour logo overlaid on the template
                    <ul>
                        <li><img src="resources/template.png" alt="EmeraldPi Template"/></li>
                    </ul>
                </li>
                <li>Pablo: One template per tour (background color is representative of the tour)
                    <ul>
                        <li>
                            <img src="resources/pablo-templates/world.png" alt="Pablo World Tour Template"/>
                            <img src="resources/pablo-templates/pga.png" alt="Pablo PGA Tour Template"/>
                            <img src="resources/pablo-templates/euro.png" alt="Pablo European Tour Template"/>
                            <img src="resources/pablo-templates/web.png" alt="Pablo Web.com Tour Template"/>
                            <img src="resources/pablo-templates/challenge.png" alt="Pablo Challenge Tour Template"/>
                        </li>
                    </ul>
                </li>
            </ul>
            <a class="popup-close" data-popup-close="popup-1" href="#">x</a>
        </div>
    </div>
<script>
var modal = document.getElementById('popcon');
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>
</body>
</html>