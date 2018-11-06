<?php
require __DIR__ . '/vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
// set false for production
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "Gw1bZ6cwKxln54UvtUmN1ymqp6/sVk8aMLZPGBbIku3Veh13BmOrDw4x8FrGz6Qz9oHV8jd/mwZtsNpv2vY4pNU0HJyCxdO3HgVEYMMqWXaGH5goOcUM7jv2ldz7L61OsWu4vV/gZxI/qg+ZeUkwfgdB04t89/1O/w1cDnyilFU=";
$channel_secret = "e2ae44d00a1fb7bdd23b2d66f58a3530";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
 
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Hello Sang Pejuang !, Gan Batte !!!!";
});
 
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    // kode aplikasi nanti disini

$data = json_decode($body, true);
if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
        if ($event['type'] == 'message')
        {
            if($event['message']['type'] == 'text')
            {
                // send same message as reply to user
                // $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                // $result = $bot->replyText($replyToken, 'ini pesan balasan');
                // $textMessageBuilder = new TextMessageBuilder('ini pesan balasan');
                // $result = $bot->replyMessage($replyToken, $textMessageBuilder);

                // or we can use replyMessage() instead to send reply message
                // $textMessageBuilder1 = new TextMessageBuilder('ini adalah pesan balasan 1');
                // $textMessageBuilder2 = new TextMessageBuilder('ini adalah pesan balasan 2');
                // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);

                // try to rplay using sticker
                // $packageid = 1;
                // $stickerid = 13;
                // $stickerMessageBuilder = new StickerMessageBuilder($packageid, $stickerid);
                // $result = $bot->replyMessage($event['replyToken'], $stickerMessageBuilder);
                
                // try to reply multiple message (2 Message and 1 sticker)
                $textMessageBuilder1 = new TextMessageBuilder('ini adalah pesan balasan 1');
                $textMessageBuilder2 = new TextMessageBuilder('ini adalah pesan balasan 2');
                $packageid = 1;
                $stickerid = 2;
                $stickerMessageBuilder = new StickerMessageBuilder($packageid,$stickerid);
                $multiMessageBuilder = new MultiMessageBuilder();
                $multiMessageBuilder = add($textMessageBuilder1);
                $multiMessageBuilder = add($textMessageBuilder2);
                $multiMessageBuilder = add($stickerMessageBuilder);

                $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
                
                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                file_put_contents('php://stderr', $output);
            }
        }
    } 
}
 
});
$app->get('/pushmessage', function($req, $res) use ($bot)
{
    // send push message to user
    $userId = 'U0c39fbef2dfcab2b38de2e70586d805b';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);
   
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});
 
$app->run();