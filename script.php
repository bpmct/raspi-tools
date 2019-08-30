<?php

if (!isset($config)) {

    if(!isset($_GET['slug']) || !$_GET['slug']) {

        header("LOCATION: generate-script");
        die();

    }
    
    if (!isset($db))
        require_once("db-conf.php");


    $slug = str_replace("/", "", $_GET['slug']);
    $slug = str_replace(".sh", "", $slug);

    $find_script = $db->prepare("SELECT * FROM scripts WHERE slug=?");
    $find_script->bindValue(1, $slug, PDO::PARAM_STR);
    $find_script->execute();

    $script = $find_script->fetchAll(PDO::FETCH_ASSOC);

    if (count($script) != 1) {

        header("LOCATION: ../generate-script?error=no_script");
        die();

    }

    $config = json_decode($script[0]['config'], true);


}

//Let's start making the script
$script = "";

function script_append($newline) {

    global $script;

    $script .= $newline . "\n";

}

function script_br() {

    global $script;
    $script .= "\n";

}

script_append("#!/bin/bash");

script_br();

script_append("# ---- CHECKS IF SCRIPT IS RUN AS ROOT ----");
script_append("if [[ \$EUID -ne 0 ]]; then");
script_append("  echo \"This script must be run as root\" && sleep 3"); 
script_append("  exit 1");
script_append("fi");

script_br();

if ($config['use-reporting']) {

    script_append("# ---- GENERATE REPORTING SCRIPT CRON JOB ----");

    script_br();
    
    script_append("# Let's make the file(s)");
    script_append("cd ~");
    script_append("mkdir -p .raspi-report");
    script_append("cd .raspi-report");
    script_append("echo '#!/bin/bash' > startup.sh");
    script_append("echo \"GROUP='" . $config['rpi-reporting-group'] ."'\" >> startup.sh");
    script_append("echo \"URL='" . $config['rpi-reporting-url'] ."'\" >> startup.sh");

    script_append('echo -e "function check_online {\n  ping -q -w 1 -c 1 `ip r | grep default | cut -d \' \' -f 3` > /dev/null && echo 1 || echo 0\n}\nIS_ONLINE=\$(check_online)\nCHECKS=0\nwhile [ \$IS_ONLINE -eq 0 ]; do\n  sleep 10;\nIS_ONLINE=\$(check_online)\nCHECKS=\$[ \$CHECKS + 1 ]\nif [ \$CHECKS -gt 5 ]; then\n  break\nfi\ndone\nif [ \$IS_ONLINE -eq 0 ]; then\n  exit 1\nfi" >> startup.sh');

    script_append("echo \"IPADDRESS=\\$(/sbin/ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1')\" >> startup.sh");
    script_append("echo \"HOSTNAME=\\$(hostname)\" >> startup.sh");
    script_append('echo \'CURL_URL="$URL?hostname=$HOSTNAME&ip=$IPADDRESS&group=$GROUP"\' >> startup.sh');

    script_append('chmod +x startup.sh');
    script_append('cp startup.sh update.sh');
    
    script_append("echo 'curl -s \"\$CURL_URL&reason=startup\" > /dev/null' >> startup.sh");

    if ($config['rpi-reporting-freq'] != "startup") { 

        script_append("echo 'curl -s \"\$CURL_URL&reason=update\" > /dev/null' >> update.sh");

        if($config["rpi-reporting-freq"] == "1min")
            $crontab_freq = "* * * * *";
        elseif($config["rpi-reporting-freq"] == "5min")
            $crontab_freq = "*/5 * * * *";
        elseif($config["rpi-reporting-freq"] == "30min")
            $crontab_freq = "*/30 * * * *";
        elseif($config["rpi-reporting-freq"] == "1hr")
            $crontab_freq = "0 * * * *";
        elseif($config["rpi-reporting-freq"] == "6hr")
            $crontab_freq = "0 */6 * * *";
        else
            $crontab_freq = "0 0 * * *";

    
    }
    
    script_br();
    script_append("# Let's add the script(s) to crontab");

    script_append("croncmd1=\"/bin/bash /root/.raspi-report/startup.sh > /dev/null 2>&1\"");
    script_append('cronjob1="@reboot $croncmd1"');
    script_append('( crontab -l | grep -v -F "$croncmd1" ; echo "$cronjob1" ) | crontab -');

    if ($config['rpi-reporting-freq'] != "startup") {

        script_br();
        script_append("croncmd2=\"/bin/bash /root/.raspi-report/update.sh > /dev/null 2>&1\"");
        script_append('cronjob2="'. $crontab_freq .' $croncmd2"');
        script_append('( crontab -l | grep -v -F "$croncmd2" ; echo "$cronjob2" ) | crontab -');

    }

} if ($config['use-hostname']) {

    script_br();

    script_append("#---- SET HOSTNAME ----");

    if($config['rpi-hostname'])
        script_append("raspi-config nonint do_hostname ". $config['rpi-hostname']);

} if ($config['use-password']) {

    script_br();

    script_append("#---- SET PASSWORD ----");

    if($config['rpi-password'])
        script_append("echo 'pi:".  $config['rpi-password'] ."' | chpasswd");

} if ($config['use-vnc']) {

    script_br();

    script_append("#---- VNC Server ----");


    if ($config['rpi-vnc'] == "yes")
        script_append("raspi-config nonint do_vnc 0");
    elseif ($config['rpi-vnc'] == "no")
        script_append("raspi-config nonint do_vnc 1");


} if ($config['use-ssh']) {

    script_br();

    script_append("#---- SSH Server ----");


    if ($config['rpi-ssh'] == "yes")
        script_append("raspi-config nonint do_ssh 0");
    elseif ($config['rpi-ssh'] == "no")
        script_append("raspi-config nonint do_ssh 1");


}

script_br();

script_append("#---- Let's restart the Raspberry Pi now");

script_append("echo \"Success! Rebooting now...\" && sleep 3");

script_append("reboot");

if(isset($_GET['slug'])) {

    header("Content-Type: text/html");
    echo $script;

}
