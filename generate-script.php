<?php

require_once("db-conf.php");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$page = "generator";
if ((isset($_POST['done']) && $_POST['done'] == "get-script") || isset($_GET['script'])) {

    $page = "script";

    if (isset($_GET['script'])) {

        $find_script = $db->prepare("SELECT * FROM scripts WHERE slug=?");
        $find_script->bindValue(1, $_GET['script'], PDO::PARAM_STR);
        $find_script->execute();

        $script = $find_script->fetchAll(PDO::FETCH_ASSOC);

        if (count($script) != 1) {

            header("LOCATION: ?error=no_script");
            die();

        }

        $config = json_decode($script[0]['config'], true);

    } else {

        $config = $_POST;

    }

} elseif (isset($_POST['shorten-url']) && $_POST['shorten-url'] == "true") {
    
  $params = urldecode($_POST['params']);

  $slug = generateRandomString(6);


  try {

      $insert_config = $db->prepare("INSERT INTO urls(slug,config,extras) VALUES(:field1,:field2,:field3)");
      $insert_config->execute(array(':field1' => $slug, ':field2' => $params, ':field3' => ""));

  } catch(PDOException $ex) {

      die($ex->getMessage());
  
  }
  
  $affected_rows = $insert_config->rowCount();

   header("LOCATION: c/$slug&s=t");

   die("");
    
} elseif (isset($_POST['done']) && $_POST['done'] == "get-url") {

    $page = "share-url";
    
} elseif(isset($_POST['generate-url'])) {

    $url = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $first = true;

    foreach($_POST as $item => $value) {

        if ($item != "generate-url") {

            if ($first) {

                $url .= "?$item=$value";

                $first = false;

            } else {

                $url .= "&$item=$value";

            }

        }


    }

    $page = "here-url";

} elseif (isset($_GET['save_config'])) {

    if (!isset($_POST['config'])) {

        header("LOCATION: ?error=no_config");
        die();

    }

    $slug = generateRandomString();

    try {

        $insert_config = $db->prepare("INSERT INTO scripts(slug,config,extras) VALUES(:field1,:field2,:field3)");
        $insert_config->execute(array(':field1' => $slug, ':field2' => $_POST['config'], ':field3' => ""));

    } catch(PDOException $ex) {

        die($ex->getMessage());
    
    }

    
    $affected_rows = $insert_config->rowCount();

    header("LOCATION: ?script=$slug");

}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/journal/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-ciphE0NCAlD2/N6NUApXAN2dAs/vcSAOTzyE202jJx3oS8n4tAQezRgnlHqcJ59C" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Additional CSS -->
    <?php if (isset($_GET['slug'])) { ?>
    <link rel="stylesheet" href="../css/generate-additional.css">
    <?php } else { ?>
    <link rel="stylesheet" href="css/generate-additional.css">
    <?php } ?>


    <title>Generate a Raspberry Pi Onboarding Script</title>
</head>

<body>

    <div class="container">
        <?php if ($page == "generator") { 
        
        if (isset($_GET['slug'])) {

            $slug = str_replace("/", "", $_GET['slug']);
        
            $find_script = $db->prepare("SELECT * FROM urls WHERE slug=?");
            $find_script->bindValue(1, $slug, PDO::PARAM_STR);
            $find_script->execute();
        
            $script = $find_script->fetchAll(PDO::FETCH_ASSOC);
        
            if (count($script) == 1) {
        
                $params = json_decode($script[0]['config'], true);
        
            }
        
        } else {

            $params = $_GET;

        }

        function field_value($params, $get, $fallback = null) {

            if (isset($params[$get]))
                return htmlentities($params[$get]);
            else
                return $fallback;
        
        }
        
        function field_disabled($params, $field) {
        
            if (isset($params["readonly-$field"]) && $params["readonly-$field"] == "true")
                return "readonly";
        
        }
        
        function field_option($params, $name, $value, $selected = false) {
        
            echo "value=\"$value\"";
        
            if (isset($params[$name]) && $params[$name] == $value) {
        
                echo " selected";
        
            } elseif (!isset($params[$name]) && $selected) {
        
                echo " selected";
        
            } elseif (isset($params["readonly-$name"]) && $params["readonly-$name"] == "true") {
        
                echo " disabled";
        
            }
        
        
        }
            
        ?>
        <div class="jumbotron raspi-jumbo">

            <h1><i class="fab fa-raspberry-pi"></i> Generate a Raspberry Pi Onboarding Script</h1>

            <div class="row">

            <?php 
            $bigColSize = "col-md-8";
            if (! (isset($params['hide-sidebar']) && $params['hide-sidebar'] == "true")) { ?>

                <div class="col-md-4">

                    <div class="list-group list-group-flush" id="commands">

                        <a class="list-group-item flex-column align-items-start list-group-item-dark">
                            <h4><i class="fa fa-plus-circle"></i> Add commands:</h4>

                        </a>

                        <button type="button" id="show-hostname" class="list-group-item list-group-item-action">
                            <span class="badge badge-pill badge-info badge-dark"><i class="fa fa-user"></i></span>
                            Change Hostname
                        </button>

                        <button type="button" id="show-password" class="list-group-item list-group-item-action">
                            <span class="badge badge-pill badge-info badge-dark"><i class="fa fa-key"></i></span>
                            Set Password
                        </button>

                        <button type="button" id="show-reporting" class="list-group-item list-group-item-action">
                            <span class="badge badge-pill badge-info badge-dark"><i class="fa fa-server"></i></span>
                            Reporting Script
                        </button>

                        <button type="button" id="show-vnc" class="list-group-item list-group-item-action">
                            <span class="badge badge-pill badge-info badge-dark">VNC</span>
                            Remote Desktop
                        </button>

                        <button type="button" id="show-ssh" class="list-group-item list-group-item-action">
                            <span class="badge badge-pill badge-info badge-dark"><i class="fa fa-terminal"></i></span>
                            SSH Access
                        </button>


                    </div>

                </div>

            <?php } else {
                
                $bigColSize = "col-md-8 offset-md-2";

            } ?>

                <div class="<?php echo $bigColSize; ?>">
                    <form name="generate-script" method="post" action="">

                        <div class="raspi-fields">

                            <?php if (isset($_GET['s']) && $_GET['s'] == "t") { 
                                
                            $url = str_replace("&s=t", "", "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
                                
                            ?>
                                <div class="alert alert-success" role="alert">
                                <button type="button" class="close" onclick="window.location.href = '<?php echo $url; ?>';" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                    <h4 class="alert-heading">Awesome!</h4>
                                    <hr />
                                    <p>You've created a custom configuration page. Here's your short URL to share:</p>
                                    <div class="input-group">
                                    
                                        <input type="text" id="scriptOneLine" class="form-control" placeholder="URL is in here :)" 
                                            value="<?php echo $url; ?>"
                                            aria-label="Install bash script" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button id="copy" class="btn btn-info" type="button"><i class="fa fa-copy"></i></button>
                                        </div>
                                    </div>

                                </div>

                            <?php } else if (count($params) > 1) {

                            ?>

                            <div class="alert alert-info fade show raspi-config-alert" role="alert">
                                <span class="raspi-alert-sm">Note: This is a custom configuration page, some fields may be disabled or auto-filled.</span>
                            </div>

                            <?php } ?>

                            <div class="raspi-field" id="empty">

                                <h5 class="text-muted"><i class="fas fa-arrow-left"></i> Use the sidebar on the left to
                                    add configuration options</h5>

                            </div>

                            <div class="raspi-field" id="hostname">

                                <input type="hidden" name="use-hostname" value="false">

                                <div class="form-group row">
                                    <label for="rpi-hostname" class="col-sm-5 col-form-label">Hostname:</label>

                                    <div class="col-sm-7">

                                        <input type="text" name="rpi-hostname" class="form-control" id="rpi-hostname"
                                            placeholder="ex. bens-pi" value="<?php echo field_value($params, "hostname"); ?>" <?php echo field_disabled($params, "hostname"); ?>>

                                    </div>
                                </div>

                            </div>


                            <div class="raspi-field" id="password">

                                <input type="hidden" name="use-password" value="false">

                                <div class="form-group row">
                                    <label for="passwordInput" class="col-sm-5 col-form-label">Password:</label>

                                    <div class="col-sm-7">

                                        <input type="text" name="rpi-password" class="form-control" id="passwordInput" value="<?php echo field_value($params, "password"); ?>" <?php echo field_disabled($params, "hostname"); ?>>

                                    </div>
                                </div>
                                <p class="small">This will change the password for the default user, <code>pi</code>.
                                </p>

                            </div>

                            <div class="raspi-field" id="reporting">

                                <input type="hidden" name="use-reporting" value="false">

                                <h3><span class="badge badge-pill badge-info badge-dark"><i
                                            class="fa fa-server"></i></span> Reporting Script</h3>
                                <p class="small">Have the Raspberry Pi ping a server with its IP and hostname when it
                                    powers on. Learn
                                    more <a href="monitor">here</a>.</p>

                                <div class="form-group row">
                                    <label for="rpi-reporting-url" class="col-sm-5 col-form-label">URL to Ping:</label>

                                    <div class="col-sm-7">

                                        <input type="text" name="rpi-reporting-url" class="form-control"
                                            id="rpi-reporting-url"
                                            value="<?php echo field_value($params, "reporting-url", "https://raspi.tools/ping"); ?>" <?php echo field_disabled($params, "reporting-url"); ?>>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="rpi-reporting-group" class="col-sm-5 col-form-label">Group/Cluster
                                        Name:</label>

                                    <div class="col-sm-7">

                                        <input type="text" name="rpi-reporting-group" class="form-control"
                                            id="rpi-reporting-group" placeholder="ex. science-class"
                                            value="<?php echo field_value($params, "reporting-group"); ?>" <?php echo field_disabled($params, "reporting-group"); ?>>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="rpi-reporting-freq" class="col-sm-5 col-form-label">Ping
                                        Frequency:</label>

                                    <div class="col-sm-7">

                                        <select name="rpi-reporting-freq" id="rpi-vnc-freq" class="form-control" <?php echo field_disabled($params, "reporting-freq"); ?>>

                                            <option <?php echo field_option($params, "reporting-freq", "startup"); ?>>Just on startup</option>
                                            <option <?php echo field_option($params, "reporting-freq", "1min"); ?>>Every minute</option>
                                            <option <?php echo field_option($params, "reporting-freq", "5min"); ?>>Every 5 minutes</option>
                                            <option <?php echo field_option($params, "reporting-freq", "30min", true); ?>>Every 30 minutes</option>
                                            <option <?php echo field_option($params, "reporting-freq", "1hr"); ?>>Every hour</option>
                                            <option <?php echo field_option($params, "reporting-freq", "6hr"); ?>>Every 6 hours</option>
                                            <option <?php echo field_option($params, "reporting-freq", "daily"); ?>>Daily</option>

                                        </select>

                                    </div>
                                </div>

                            </div>

                            <div class="raspi-field" id="vnc">

                                <input type="hidden" name="use-vnc" value="false">

                                <div class="form-group row">
                                    <label for="rpi-vnc" class="col-sm-5 col-form-label">Enable VNC:</label>

                                    <div class="col-sm-7">

                                        <select name="rpi-vnc" id="rpi-vnc" class="form-control" <?php echo field_disabled($params, "vnc"); ?>>

                                            <option <?php echo field_option($params, "vnc", "yes", true); ?>>Yes</option>
                                            <option <?php echo field_option($params, "vnc", "no"); ?>>No</option>

                                        </select>

                                    </div>
                                </div>

                            </div>

                            <div class="raspi-field" id="ssh">

                                <input type="hidden" name="use-ssh" value="false">

                                <div class="form-group row">
                                    <label for="rpi-ssh" class="col-sm-5 col-form-label"><span
                                            class="badge badge-pill badge-info badge-dark"><i
                                                class="fa fa-terminal"></i></span> Enable SSH:</label>

                                    <div class="col-sm-7">

                                        <select name="rpi-ssh" id="rpi-ssh" class="form-control" <?php echo field_disabled($params, "ssh"); ?>>

                                            <option <?php echo field_option($params, "ssh", "yes", true); ?>>Yes</option>
                                            <option <?php echo field_option($params, "vnc", "no"); ?>>No</option>

                                        </select>

                                    </div>
                                </div>

                            </div>



                        </div>

                        <div class="raspi-form-buttons">

                        <?php if (!(isset($params['hide-gen-url']) && $params['hide-gen-url'] == "true")) { ?>

                            <button type="submit" name="done" value="get-url" class="btn btn-lg btn-primary"><i
                                    class="fa fa-share-square" ></i> Share Config URL</button>

                        <?php } ?>

                            <button type="submit" name="done" value="get-script" class="btn btn-lg btn-success"><i
                                    class="fa fa-terminal"></i> Generate Script</button>

                        </div>

                    </form>


                </div>

            </div>

        </div>
        <?php } elseif ($page == "script") { 

            //This creates $script
            require_once("script.php");
        
        ?>

        <div class="jumbotron raspi-jumbo">

            <h1><span class="badge badge-pill badge-info badge-dark"><i class="fa fa-terminal"></i></span> Here's Your
                Script:</h1>

            <?php echo "<textarea class=\"form-control raspi-script\">". $script ."</textarea>"; ?>

            <h2 class="or"><?php if (isset($_GET['script'])) { echo "Or use your install command:"; } else { echo "Or save these settings:"; } ?></h2>
            
            <p class="or2"><?php if (isset($_GET['script'])) { echo "Copy and paste this into your terminal..."; } else { echo "Save this configuration to generate a one-line install command..."; } ?></p>

            <?php if (isset($_GET['script'])) { ?>

            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="input-group">
                        <input type="text" id="scriptOneLine" class="form-control" placeholder="Install bash script" 
                            value="sudo curl -s https://bpmct.net/projects/raspi/script/<?php echo $_GET['script']; ?>.sh | sudo bash -s"
                            aria-label="Install bash script" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button id="copy" class="btn btn-info" type="button"><i class="fa fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <?php } else { ?>

            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="input-group">
                        <input id="raspi-url" type="text" class="form-control unselectable" placeholder="Install bash script" 
                            value="sudo curl -s https://bpmct.net/projects/raspi/scripts/gv2943oG0R.sh | sudo bash -s"
                            aria-label="Install bash script" aria-describedby="basic-addon2" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-info unselectable" type="button" disabled><i class="fa fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <form action="?save_config" method="post">

            <input type="hidden" name="config" value="<?php echo htmlentities(json_encode($_POST)); ?>">

            <p class="raspi-save text-muted">
                <small><i class="fa fa-arrow-up"></i> Example command, it will not work. Saving is free and doesn't require an account.</p>

            </p>

            <p class="raspi-save">
                <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save this configuration</button>
            </p>

            <?php }?>

            </form>

        </div>

        <?php } elseif ($page == "share-url") { ?>

            <div class="jumbotron raspi-jumbo">

                <h1><span class="badge badge-pill badge-info badge-dark"><i class="fa fa-link"></i></span> Create a Custom URL:</h1>

                <p class="text-center">Create a custom URL with your configuration autofilled. Good for a lot of devices or sharing with a class :)</p>

                <form name="create-url" method="post" action="">

                <div class="card raspi-url-config-card">
                        
                        <div class="card-body">

                            <h3><i class="fa fa-cogs"></i> Page settings</h3>
                            
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="hide-gen-url" name="hide-gen-url" value="true">
                                <label class="form-check-label" for="hide-gen-url">Hide "Share Config URL"</label>
                            </div>


                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="hide-sidebar" name="hide-sidebar" value="true">
                                <label class="form-check-label" for="hide-sidebar">Hide the sidebar</label>
                            </div>

                        </div>
                
                </div>

                <?php

                $sections = array();

                foreach($_POST as $input => $value) {

                    if (strpos($input, 'use-') === false && $input != "done") {

                        $input = str_replace("rpi-", "", $input);

                        $sections_string = json_encode($sections);

                        $input_category = explode("-", $input)[0];

                        if (strpos($sections_string, $input_category) !== false) {
                        
                ?>
                <div class="card raspi-url-config-card">
                        
                        <div class="card-body">

                            <h3><?php echo $input; ?></h3>

                            <p><small>Current value is <?php if ($value) { echo "<code>$value</code>"; } else { echo "blank"; } ?></small></p>

                            <input type="hidden" name="<?php echo $input; ?>" value="<?php echo $value; ?>" />

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="<?php echo $input; ?>" name="readonly-<?php echo $input; ?>" value="true">
                                <label class="form-check-label" for="<?php echo $input; ?>">Make field uneditable</label>
                            </div>


                        </div>
                
                </div>
                <?php
                        }
                    } elseif (strpos($input, 'use-') !== false) { 
                
                    $input = str_replace("use-", "", $input);

                    if ($value == "true") {
                        array_push($sections, $input);
                
                ?>

                        <input type="hidden" name="show-<?php echo $input; ?>" value="true">

                <?php 
                   
                    }

                    }
                }
                ?>

            <div class="text-right">

            <button type="submit" name="generate-url" value="true" class="btn btn-lg btn-success"><i
                class="fa fa-link" ></i> Get Config URL</button>
            
            </div>

                </form>

            </div>

        <?php } elseif ($page == "here-url") { ?>

            <div class="jumbotron raspi-jumbo">

            <h1><span class="badge badge-pill badge-success"><i class="fa fa-link"></i></span> Here's your URL:</h1>

            <input type="text" class="form-control" value="<?php echo $url; ?>" readonly>
            <p class="text-center"><small><a href="<?php echo $url; ?>" target="_blank">Visit the page</a></small></p>

            <h2 class="or">Or shorten this link:</h2>
            
            <p class="or2">You don't need an account or anything, but it will save the config to our databases.</p>
            
            <form name="shorten-link" method="post" action="">

                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="input-group">
                            <input id="raspi-url" type="text" class="form-control unselectable" placeholder="Install bash script" 
                                value="ex. https://raspi.tools/c/lR2A2w4"
                                aria-label="Shortened link" aria-describedby="basic-addon2" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-info unselectable" type="button" disabled><i class="fa fa-copy"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="params" value="<?php echo urlencode(json_encode($_POST)); ?>" />

                <br />

                <p class="raspi-save">
                    <button type="submit" name="shorten-url" value="true" class="btn btn-success btn-lg"><i class="fa fa-link"></i> Generate short link</button>
                </p>

            </form>

            </div>

        <?php } ?>
        <p class="text-muted raspi-credits small">Made with <i class="fa fa-heart"></i> by <a class="text-muted" href="https://ben.services" target="_blank" title="Ben Potter" alt="Ben Potter">Ben</a>.</p>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>

    <script type="text/javascript">
        var visible_fields = [];

        function check_empty() {

            if (!Array.isArray(visible_fields) || !visible_fields.length) {

                $("div#empty").show();
                $("div.raspi-form-buttons").hide();

            } else {

                $("div#empty").hide();
                $("div.raspi-form-buttons").fadeIn(300);

            }

        }

        function show_field(field) {

            visible_fields.push(field);

            check_empty();

            $("button#show-" + field).addClass("active");

            $("input[name='use-" + field + "']").val("true");

            $("div#" + field).fadeIn(300);


        }

        function hide_field(field) {

            for (var i = 0; i < visible_fields.length; i++) {
                if (visible_fields[i] === field) {
                    visible_fields.splice(i, 1);
                }
            }

            $("input[name='use-" + field + "']").val("false");

            $("button#show-" + field).removeClass("active");

            $("div#" + field).fadeOut("fast", function () {
                check_empty();
            });

        }

        $(":button").click(function (event) {

            var button_id = event.target.id;

            var element_to_show = button_id.replace("show-", "");

            if (visible_fields.includes(element_to_show)) {

                hide_field(element_to_show);


            } else {

                show_field(element_to_show);

            }

        });

        $(document).ready(function () {
            <?php

            foreach ($params as $key => $value) {

                if (strpos($key, 'show-') !== false) {

                    $key = str_replace("show-", "", $key);
                 
                    echo "show_field(\"" . $key . "\");\n";

                }
            }

            ?>
            check_empty();
        });

        $( "#raspi-url" ).mousedown(function(event) {
            event.preventDefault();
        });

        $( "#copy" ).click(function() {
            
            var copyText = document.getElementById("scriptOneLine");
            copyText.select();
            document.execCommand("copy");
            alert("Copied to your clipboard!");
        
        
        });

    </script>


</body>

</html>