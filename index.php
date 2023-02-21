<?php 
require_once 'vendor/autoload.php';

use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;


//Database config
$GLOBALS["dbConfig"] = array(
    'host' => 'localhost',
    'user' => 'userName',
    'password' => '',
    'database' => 'dbName',
);

function getEventIfExist($config, $data){
    $db = new MysqliDb ($config['host'], $config['user'], $config['password'], $config['database']);
    $events = $db->rawQueryOne('SELECT * from events where `uid`=?', $data);
    return $events;
}

function setEvent($config, $data){
    $db = new MysqliDb ($config['host'], $config['user'], $config['password'], $config['database']);
    $id = $db->insert('events', $data);
    return $id;
}

function getAuthCookie($host){
    $urlAuth = "cp/processLogin";

    $headers = array(
        "Content-Type" => "application/x-www-form-urlencoded",
        "X-Requested-With" => "XMLHttpRequest"
    );

    // Blogabet.com auth credits
    $authData = array(
        "email" => "example@mail.com", // email example
        'password' => "12345", // password example
        'remember-me' => 1
    );

    $client = new Client([
        // Base URI is used with relative requests
        'base_uri' => $host,
        // You can set any number of default request options.
        'timeout'  => 2.0,
    ]);
    
    $response = $client->request('POST', $urlAuth, [
        'headers' => $headers,
        'form_params' => $authData
    ]);

    return $response->getHeaders()["Set-Cookie"][0];
}


function getHtmlFromURL($host, $cookie) {
    $parseUrl = "blog/dashboard";
    $headers = array(
        "Content-Type" => "application/x-www-form-urlencoded",
        "X-Requested-With" => "XMLHttpRequest",
        "Cookie" => $cookie
    );

    $client = new Client([
        'base_uri' => $host,
        //'timeout'  => 2.0,
    ]);

    $response = $client->request('GET', $parseUrl, [
        'headers' => $headers,
    ]);

    $body = $response->getBody();

    return $body;
}

function sendMessageToTelegram($message){

    
    $telegramToken = ""; // Telegram token
    $telegramChatId = 11111; // Telegram chatID

    $bot = new \TelegramBot\Api\BotApi($telegramToken);
    $bot->sendMessage($telegramChatId, $message, "markdown");
}

function getData($host, $source, $emoji) {
    $html = (string) getHtmlFromURL($host, getAuthCookie($host));
    $dom = HtmlDomParser::str_get_html($html);
    $elements = $dom->findMulti('#_blogPostsContent #blogPickList li.block.feed-pick');

    foreach ($elements as $element) {
        $uid = $element->getAttribute('data-time');
        $title = preg_replace('/\s+/',' ', $element->findOne('h3 a')->innerText());
        $description = preg_replace('/\s+/',' ', $element->findOne('.pick-line')->text());
        $type = preg_replace('/\s+/',' ', $element->findOne('.sport-line .text-muted')->text());
        //$date = $element->findOne('.title-age small.text-muted')->innerText();
        $sendDate = $element->findOne('.title-name .bet-age')->innerText();

        $live = "🕜 *Cобытие по линии*\n\n";
        
        if(str_contains($type, 'Livebet')){
            $live = "🔴 *Live событие*\n\n";
        }

        $message = "*$emoji Прогноз от $source*\n\n$live*📍 Событие: *$title\n*🎲 Прогноз:* $description\n*📊 Информация и дата события:* $type\n*📆 Дата прогноза:* $sendDate";
        

        $result = getEventIfExist($GLOBALS["dbConfig"], [$uid]);
        if(!isset($result)){
            setEvent($GLOBALS["dbConfig"], array('uid'=>$uid,'source' => $source, 'date' => $sendDate));
            sendMessageToTelegram($message);
        }
        
        sleep(6); // Костыльная задержка для того чтоб не было бана из-за большего кол-ва запросов
    }
}
//Примеры
getData("https://robsaomlb.blogabet.com/", "robsaoMLB", "⚾");
getData("https://abonamentpm.blogabet.com/", "RozpracujmyBukmacherów", "🎮🎾🏀");
getData("https://alexhunters.blogabet.com/", "AlexanHunters", "⚽🏀");
getData("https://reddog70.blogabet.com/", "Explore", "⚽");
getData("https://soccerpredictions10.blogabet.com/", "Dutch soccer tips", "⚽");
getData("https://soccerwagers.blogabet.com/", "Soccerwagers", "⚽");

echo "done";