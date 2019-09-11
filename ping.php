<?php
//This is the sript the API will ping.


if (!isset($_GET['hostname']) || !isset($_GET['ip'])) {

    die("no 'hostname' and/or 'ip' defined.");
    exit();

}

if (!isset($_GET['group'])) {

    die("no 'group' defined.");
    exit();

}

require_once("db-conf.php");

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//Delete records older than 60 mins
$delete_old = $db->exec('DELETE FROM records WHERE time < (NOW() - INTERVAL 60 MINUTE)');

//Delete records with same ip and group
$delete_existing = $db->prepare("DELETE FROM records WHERE groupkey=:groupname and ip=:ip");
$delete_existing->bindValue(':groupname', $_GET['group'], PDO::PARAM_STR);
$delete_existing->bindValue(':ip', $_GET['ip'], PDO::PARAM_STR);
$delete_existing->execute();

//Delete records with same hostname and group
$delete_existing = $db->prepare("DELETE FROM records WHERE groupkey=:groupname and hostname=:hostname");
$delete_existing->bindValue(':groupname', $_GET['group'], PDO::PARAM_STR);
$delete_existing->bindValue(':hostname', $_GET['hostname'], PDO::PARAM_STR);
$delete_existing->execute();

try {

$add_row = $db->prepare("INSERT INTO records(groupkey,hostname,ip,extras) VALUES(:field1,:field2,:field3,:field4)");
$add_row->execute(array(':field1' => $_GET['group'], ':field2' => $_GET['hostname'], ':field3' => $_GET['ip'], ':field4' => ""));


} catch (PDOException $e)
{
    die ($e);
}

if ($add_row->rowCount() == 1) {

    die("success");

} else {

    die("failure");

}

exit();