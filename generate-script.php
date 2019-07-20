<?php

require_once("db-conf.php");

function field_value($get, $fallback = null) {

    if (isset($_GET[$get]))
        return htmlentities($_GET[$get]);
    else
        return $fallback;

}

$page = "generator";
if (isset($_POST['done']) || isset($_GET['script'])) {
    
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

} elseif (isset($_GET['save_config'])) {

    if (!isset($_POST['config'])) {

        header("LOCATION: ?error=no_config");
        die();

    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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
    <link rel="stylesheet" href="generate-additional.css">

    <title>Generate a Raspberry Pi Onboarding Script</title>
</head>

<body>

    <div class="container">
        <?php if ($page == "generator") { ?>
        <div class="jumbotron raspi-jumbo">

            <h1><i class="fab fa-raspberry-pi"></i> Generate a Raspberry Pi Onboarding Script</h1>

            <div class="row">

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

                        <button type="button" id="show-reporting-script" class="list-group-item list-group-item-action">
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

                <div class="col-md-8">
                    <form name="generate-script" method="post" action="">

                        <div class="raspi-fields">

                            <div class="raspi-field" id="empty">

                                <h5 class="text-muted"><i class="fas fa-arrow-left"></i> Use the sidebar on the left to
                                    add configuration options</h5>

                            </div>

                            <div class="raspi-field" id="hostname">

                                <input type="hidden" name="use-hostname" value="false">

                                <div class="form-group row">
                                    <label for="rpi-hostname" class="col-sm-2 col-form-label">Hostname:</label>

                                    <div class="col-sm-10">

                                        <input type="text" name="rpi-hostname" class="form-control" id="rpi-hostname"
                                            placeholder="ex. bens-pi">

                                    </div>
                                </div>

                            </div>


                            <div class="raspi-field" id="password">

                                <input type="hidden" name="use-password" value="false">

                                <div class="form-group row">
                                    <label for="passwordInput" class="col-sm-2 col-form-label">Password:</label>

                                    <div class="col-sm-10">

                                        <input type="text" name="rpi-password" class="form-control" id="passwordInput">

                                    </div>
                                </div>
                                <p class="small">This will change the password for the default user, <code>pi</code>.
                                </p>

                            </div>

                            <div class="raspi-field" id="reporting-script">

                                <input type="hidden" name="use-reporting-script" value="false">

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
                                            value="<?php echo field_value("url", "https://bpmct.net/projects/raspi/ping"); ?>">

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="rpi-reporting-group" class="col-sm-5 col-form-label">Group/Cluster
                                        Name:</label>

                                    <div class="col-sm-7">

                                        <input type="text" name="rpi-reporting-group" class="form-control"
                                            id="rpi-reporting-group" placeholder="ex. science-class"
                                            value="<?php echo field_value("group"); ?>">

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="rpi-reporting-freq" class="col-sm-5 col-form-label">Ping
                                        Frequency:</label>

                                    <div class="col-sm-7">

                                        <select name="rpi-reporting-freq" id="raspi-vnc-freq" class="form-control">

                                            <option value="startup">Just on startup</option>
                                            <option value="1min">Every minute</option>
                                            <option value="5min">Every 5 minutes</option>
                                            <option value="30min" selected>Every 30 minutes</option>
                                            <option value="1hr">Every hour</option>
                                            <option value="6hr">Every 6 hours</option>
                                            <option value="daily">Daily</option>

                                        </select>

                                    </div>
                                </div>

                            </div>

                            <div class="raspi-field" id="vnc">

                                <input type="hidden" name="use-vnc" value="false">

                                <div class="form-group row">
                                    <label for="rpi-vnc" class="col-sm-5 col-form-label">Enable VNC:</label>

                                    <div class="col-sm-7">

                                        <select name="raspi-vnc" id="raspi-vnc" class="form-control">

                                            <option value="yes" selected>Yes</option>
                                            <option value="no">No</option>

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

                                        <select name="raspi-ssh" id="raspi-ssh" class="form-control">

                                            <option value="yes" selected>Yes</option>
                                            <option value="no">No</option>

                                        </select>

                                    </div>
                                </div>

                            </div>



                        </div>

                        <div class="raspi-form-buttons">

                            <button type="submit" name="done" value="get-url" class="btn btn-lg btn-primary" disabled><i
                                    class="fa fa-share-square" ></i> Share Config URL</button>
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

            <?php } ?>

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

            foreach ($_GET as $key => $value) {

                echo "show_field(\"" . $key . "\");\n";

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
            alert("Copied! Now paste it into your terminal (CTRL+ALT+T)");
        
        
        });

    </script>


</body>

</html>