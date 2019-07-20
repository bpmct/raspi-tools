<?php

if (!isset($_GET['file']))
    $file = "";
else
    $file = str_replace("/", "", $_GET['file']);

if ($file == null) {
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/journal/bootstrap.min.css">

  <!-- Additional CSS -->
  <link rel="stylesheet" href="../css/landing-additional.css">

  <title>raspi.tools</title>

</head>

<body>

  <div class="container h-100">
    <div class="row h-100">
      <div class="col-sm-12 my-auto portal">
        <h1>raspi.tools Tutorials</h1>
        <sub>A collection of tutorials for getting started with the Raspberry Pi 😊</sub>
        <ul>
            <?php
            $files = scandir('.');

            $count = 0;

            foreach($files as $file) {
                if (strpos($file, '.md') !== false) {

                    if ($count % 2 == 0)
                        $class = "primary";
                    else
                        $class = "info";

                    $no_extension = str_replace(".md", "", $file);
                    $pretty_name = str_replace("-", " ", $no_extension);
                    $pretty_name = strtoupper($pretty_name);

                    echo "<a class=\"btn btn-md btn-$class btn-tutorial\" href=\"../tutorial/$no_extension\">$pretty_name</a><br />";

                    $count++;

                }
            }
            ?>
        </ul>
      </div>
    </div>
  </div>

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

<?php } else {

$file = str_replace("/", "", $_GET['file']);

$file_location = "../tutorials/" . $file . ".md";

$no_extension = str_replace(".md", "", $file);
$pretty_name = str_replace("-", " ", $no_extension);
$pretty_name = strtoupper($pretty_name);


if (file_exists($file_location)) {
    
    $file_contents = file_get_contents($file_location);
    
    include("parsedown.php");
    
    $Parsedown = new Parsedown();

    $body = $Parsedown->text($file_contents);

    preg_match_all('#<h1>(.*?)</h1>#', $body, $matches);

    $title = implode ( ' - ', array_slice($matches[0], 0, 2));

    if (!empty($title))
        $title = strip_tags($title);
    else
        $title = $pretty_name;


?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/journal/bootstrap.min.css">

  <!-- Additional CSS -->
  <link rel="stylesheet" href="../css/landing-additional.css">

  <title><?php echo $title; ?></title>

</head>

<body>

  <div class="container container-md">
    <div class="jumbotron">
        <?php echo $body; ?>
    </div>
  </div>

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

<?php } else {

    http_response_code(404);

    die("<title>Error 404</title><strong>Error 404:</strong> Page not found.");

}

} ?>