<?php
$filepassword = 'PutHereAVeryStrongPassword'; //CHANGE THIS LINE
$installerVersion = '0.2'; // DO NOT CHANGE THIS LINE
if (($_SERVER['HTTPS'] != 'on') && (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')) {
    die('You have to visit the installer from HTTPS protocol');
}
if (isset($_POST['token']) && isset($_POST['filepassword']) && isset($_POST['dirto'])) {
    if ($_POST['filepassword'] != $filepassword) {
        die('Wrong password');
    }
    $token = $_POST['token'];

    $dirto = $_POST['dirto'];
    $installfiles = isset($_POST['filescheck']) ? 1 : 0;
    if ($installfiles) {
        if (isset($_POST['version'])) {
            $version = $_POST['version'];
        } else {
            $versions = json_decode(sendRequest('https://raw.silveros.it/silbot/releases/versions.json'), true);
            if (!isset($versions[$installerVersion])) {
                die('This installer is not supported anymore');
            } else {
                $version = $versions[$installerVersion][0];
            }
        }

        $dbact = isset($_POST['dbact']) ? 1 : 0;
        $ip = isset($_POST['ip']) ? addslashes($_POST['ip']) : '';
        $user = isset($_POST['user']) ? addslashes($_POST['user']) : '';
        $password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
        $db_name = isset($_POST['db_name']) ? addslashes($_POST['db_name']) : '';
        $universal = isset($_POST['universal']) ? addslashes($_POST['universal']) : '';
        $bot = isset($_POST['bot']) ? addslashes($_POST['bot']) : '';

        $downloadinfo = json_decode(sendRequest('https://raw.silveros.it/silbot/releases/' . $version . '/downloadinfo.json'), true);
        if (!isset($downloadinfo['dirs']) || !isset($downloadinfo['files'])) {
            die('Can\'t get version information');
        }
        $getMe = json_decode(sendRequest('https://api.telegram.org/bot' . $token . '/getMe'), true);
        if (!$getMe['ok']) {
            die('The token is incorrect');
        }
        if (is_dir($dirto)) {
            die('This folder already exists, delete it first');
        }
        if (!mkdir($dirto)) {
            die('I can\'t create this folder');
        }
        foreach ($downloadinfo['dirs'] as $dir) {
            mkdir($dirto . '/' . $dir);
        }
        foreach ($downloadinfo['files'] as $file) {
            if ($file == 'config.php') {
                $torep = [
                    '&dbact',
                    '&ip',
                    '&user',
                    '&password',
                    '&db_name',
                    '&universal',
                    '&bot',
                ];
                $rep = [
                    $dbact,
                    $ip,
                    $user,
                    $password,
                    $db_name,
                    $universal,
                    $bot,
                ];
                file_put_contents($dirto . '/config.php', str_replace($torep, $rep, sendRequest('https://raw.silveros.it/silbot/releases/1.4.1/' . $file)));
            } else {
                copy('https://raw.silveros.it/silbot/releases/'.$installerVersion.'/' . $file, $dirto . '/' . $file);
            }
        }
    }
    $ex = explode('/', $_SERVER['REQUEST_URI']);
    unset($ex[count($ex) - 1]);
    $rootdir = implode('/', $ex);
    $wburl = "https://" . $_SERVER['HTTP_HOST'] . $rootdir . '/' . $dirto . '/index.php';
    sendRequest('https://api.telegram.org/bot' . $token . '/setWebhook', ['url' => $wburl . '?token=' . $token, 'max_connections' => 100]);
    if ($dbact || !$installfiles) {
        sendRequest($wburl, ['install' => true]);
    }
    die('Silbot successfully installed');
} else {
    $versions = json_decode(sendRequest('https://raw.silveros.it/silbot/releases/versions.json'), true);
    $supported = $versions[$installerVersion];
    $option = '';
    foreach ($supported as $version) {
        $option .= '<option value="' . $version . '">' . $version . '</option>';
    }
    echo str_replace('&options', $option, sendRequest('http://raw.silveros.it/installer/html/releases/' . $installerVersion . '/installer.html'));
}


function sendRequest($url, $args = [], $response_type = false)
{
    $args = http_build_query($args);
    if (isset($args)) $url .= '?' . $args;
    $request = curl_init($url);
    curl_setopt_array($request, array(
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_USERAGENT => 'cURL request',
    ));
    curl_setopt($request, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $result = curl_exec($request);
    curl_close($request);
    return $result;
}
