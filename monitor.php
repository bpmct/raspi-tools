<?php

require_once("db-conf.php");

//Let's delete old records
$delete_old = $db->exec('DELETE FROM records WHERE time < (NOW() - INTERVAL 60 MINUTE)');

function timeAgo($timestamp){
    $datetime1=new DateTime("now");
    $datetime2=date_create($timestamp);
    $diff=date_diff($datetime1, $datetime2);
    $timemsg='';
    if($diff->y > 0){
        $timemsg = $diff->y .' year'. ($diff->y > 1?"s":'');

    }
    else if($diff->m > 0){
     $timemsg = $diff->m . ' month'. ($diff->m > 1?"s":'');
    }
    else if($diff->d > 0){
     $timemsg = $diff->d .' day'. ($diff->d > 1?"s":'');
    }
    else if($diff->h > 0){
     $timemsg = $diff->h .' hour'.($diff->h > 1 ? "s":'');
    }
    else if($diff->i > 0){
     $timemsg = $diff->i .' minute'. ($diff->i > 1?"s":'');
    }
    else if($diff->s > 0){
     $timemsg = $diff->s .' second'. ($diff->s > 1?"s":'');
    }

$timemsg = $timemsg.' ago';
    return $timemsg;
}

$view = "small";
if (isset($_GET['view']) && $_GET['view'] == "large") {

    $view = "large";

}

$auto_check = "off";
if (isset($_GET['auto-check']) && $_GET['auto-check'] == "on") {

    $auto_check = "on";

}

?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"
        integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <!-- Additional CSS -->
    <link rel="stylesheet" href="monitor-additional.css">

    <title>Raspberry Pis</title>
</head>

<body>

    <?php if (!isset($_GET['group'])) { ?>

    <div class="container d-flex h-100">
        <div class="row align-self-center w-100">
            <div class="col-6 mx-auto">
                <div class="jumbotron">
                    <h1>Enter your group name:</h1>

                    <form action="" method="get">

                        <div class="form-group">

                            <input type="text" pattern="[a-zA-Z0-9-]+" class="form-control form-control-lg" name="group"
                                placeholder="" required />

                        </div>

                        <div class="form-group">

                            <button type="submit" class="btn btn-xl btn-success">Let's go!</button>

                        </div>

                    </form>
                </div>
                <p class="credit text-muted">Made with <i class="fa fa-heart"></i> by <a
                        href="https://bpmct.net/?ref=raspi" target="_BLANK">Ben</a>.</p>
            </div>
        </div>
    </div>

    <?php } else { 
        
    $group =  preg_replace('/[^\w]/', '', $_GET['group']);
        
    ?>

    <div class="container d-flex h-100">
        <div class="row align-self-center w-100">
            <div class="mx-auto">
                <div class="jumbotron raspi-list">
                    <a class="text-muted raspi-cogs-button" data-toggle="modal" href="#" data-target="#raspi-cogs"><i
                            class="fa fa-cog"></i></a>
                    <h1 class="list">Raspberry Pis in <code><?php echo $group; ?></code>:</h1>
                    <p class="bottom-notice text-muted">Rows are removed after 60 minutes.</p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Hostname</th>
                                <th scope="col">IP Address</th>
                                <th scope="col">Last update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $get_pis = $db->prepare("SELECT * FROM records WHERE groupkey=? ORDER BY id DESC");
                            $get_pis->execute(array($group));
                            $get_pis = $get_pis->fetchAll(PDO::FETCH_ASSOC);
                            foreach($get_pis as $pi) { ?>
                            <tr>
                                <tD><?php echo $pi['hostname']; ?></tD>
                                <td><?php echo $pi['ip']; ?></td>
                                <td><?php echo timeago($pi['time']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <p class="credit text-muted"><a href="?">Back home</a></p>

                <!--- Cogs Modal -->
                <div class="modal fade" id="raspi-cogs" tabindex="-1" role="dialog" aria-labelledby="raspi-cogs-label"
                    aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="raspi-cogs-label"><i
                                        class="fa fa-truck fa-flip-horizontal"></i> Additional Options:</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <form name="raspi-cogs-form" action="" method="get">
                                <input type="hidden" name="group" value="<?php echo $group; ?>" />
                                <div class="modal-body">

                                    <div class="form-group row">
                                        <label for="displayMode" class="col-sm-4 col-form-label">Display mode</label>
                                        <div class="col-sm-8">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label
                                                    class="btn btn-primary<?php if ($view == "small") echo " active"; ?>">
                                                    <input type="radio" name="view" value="small" autocomplete="off"
                                                        <?php if ($view == "small") echo " checked"; ?>>
                                                    Small box
                                                </label>
                                                <label
                                                    class="btn btn-primary<?php if ($view == "large") echo " active"; ?>">
                                                    <input type="radio" name="view" value="large" autocomplete="off"
                                                        <?php if ($view == "large") echo " checked"; ?>>
                                                    Large display
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="displayMode" class="col-sm-4 col-form-label">Auto-check</label>
                                        <div class="col-sm-8">
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label
                                                    class="btn btn-info<?php if ($auto_check == "on") echo " active"; ?>">
                                                    <input type="radio" name="auto-check" value="on" autocomplete="off"
                                                        <?php if ($auto_check == "on") echo " checked"; ?>>
                                                    <i class="fa fa-check"></i> On
                                                </label>
                                                <label
                                                    class="btn btn-info<?php if ($auto_check == "off") echo " active"; ?>">
                                                    <input type="radio" name="auto-check" value="off" autocomplete="off"
                                                        <?php if ($auto_check == "off") echo " checked"; ?>>
                                                    <i class="fa fa-times"></i> Off
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="cogs-spacer"></div>

                                    <p class="add-link small"><a href="generate-script?reporting-script&group=<?php echo $group; ?>"><strong>Add a device:</strong> Generate an install link</a></p>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <?php } ?>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>