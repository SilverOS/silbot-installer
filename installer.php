<?php
if (($_SERVER['HTTPS'] != 'on') && (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')) {
    die($_SERVER['HTTP_X_FORWARDED_PROTO']);
}
if (isset($_POST['token']) && isset($_POST['dirto'])) {
    $token = $_POST['token'];
    $dirto = $_POST['dirto'];
    
    $dbact = isset($_POST['dbact']) ? 1 : 0;
    $ip = isset($_POST['ip']) ? addslashes($_POST['ip']) : '';
    $user = isset($_POST['user']) ? addslashes($_POST['user']) : '';
    $password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
    $db_name = isset($_POST['db_name']) ? addslashes($_POST['db_name']) : '';
    $universal = isset($_POST['universal']) ? addslashes($_POST['universal']) : '';
    $bot = isset($_POST['bot']) ? addslashes($_POST['bot']) : '';

    $downloadinfo = json_decode(sendRequest('https://raw.silveros.it/silbot/releases/1.4.1/downloadinfo.json'),true);
    if (!isset($downloadinfo['dirs']) || !isset($downloadinfo['files'])) {
        die('Can\'t get version information');
    }
    $getMe = json_decode(sendRequest('https://api.telegram.org/bot' . $token . '/getMe'),true);
    if (!$getMe['ok']) {
        die('The token is incorrect');
    }
    $ex = explode('/',$_SERVER['REQUEST_URI']);
    unset($ex[count($ex)-1]);
    $rootdir = implode('/',$ex);
    $wburl = "https://" . $_SERVER['HTTP_HOST'] . $rootdir . '/' . $dirto . '/index.php';
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
            file_put_contents($dirto . '/config.php',str_replace($torep,$rep,sendRequest('https://raw.silveros.it/silbot/releases/1.4.1/' . $file)));
        } else {
            copy('https://raw.silveros.it/silbot/releases/1.4.1/' . $file, $dirto . '/' . $file);
        }
    }
    sendRequest('https://api.telegram.org/bot' . $token . '/setWebhook',['url' => $wburl . '?token=' . $token, 'max_connections' => 100]);
    if ($dbact) {
        sendRequest($wburl,['install' => true]);
    }
    die('Silbot successfully installed');
} else {
    echo sendRequest('http://raw.silveros.it/installer/html/releases/0.1/installer.html');
}


function sendRequest($url, $args = [], $response_type = false)
{
    $args = http_build_query($args);
    if(isset($args)) $url .= '?' . $args;
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
