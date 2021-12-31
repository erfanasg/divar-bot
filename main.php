<?php

include_once "Database.php";

$BOT_TOKEN = 'Token';
$db = new DB("localhost",  "main", 'password', "Agahi");
$db->Connect();

$button_set = array(
    1 => array(array("ثبت آگهی"), array("مشاهده آگهی های جاری")),
);

$replyMarkup = array(
 'keyboard' => $button_set[1],
 'one_time_keyboard' => false,
 'resize_keyboard' => true,
 'selective' => true
);

function SendMessageKey($chatId, $message, $r)
{
    $url = "https://api.telegram.org/bot" . $GLOBALS['BOT_TOKEN'] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message) . "&reply_markup=" . $r . "&parse_mode=markdown";
    file_get_contents($url);
}

function SendMessage($chat_id, $text)
{
    $api = $GLOBALS['BOT_TOKEN'];
	$getUrl = sprintf("https://api.telegram.org/bot%s/sendMessage?chat_id=%s&text=%s&parse_mode=markdown", $api, $chat_id, urlencode($text)) ;
    file_get_contents($getUrl);
}

function startsWith ($string, $startString) 
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}


$txt = array(
    "start" => "به ربات اصفهان آگهی خوش آمدید میتوانید کالای خود را آگهی کرده یا از میان آگهی های موجود کالای مورد نظر خود را خریداری کنید. \n برای استفاده از خدمات ربات ابتدا باید ثبت نام نمایید، پیام های بعدی شما را در این امر راهنمایی خواهند کرد.",
    "welcome" => "به ربات اصفهان آگهی خوش آمدید.\n برای ثبت آگهی خود در ربات از دکمه ی 'ثبت آگهی' و برای مشاهده ی همه ی آگهی های جاری در ربات از دکمه 'مشاهده آگهی های جاری' استفاده کنید.",
    "enter_phone" => "شماره ی همراه خود را وارد کنید:",
    "enter_firstname" => "نام خود را وارد کنید:",
    "enter_lastname" => "نام خانوادگی خود را وارد کنید:",
    "enter_ad_title" => "عنوان آگهی را وارد کنید: ",
    "enter_ad_text" => "متن آگهی خود را وارد کنید:",
    "name_length" => "نامی که وارد میکنید باید سه حرف یا بیشتر باشد! نام دیگری وارد کنید:",
    "phone_length" => "طول شماره وارد شده باید دقیقا 11 رقم باشد! شماره دیگری وارد کنید:",
    "sign_success" => "ثبت نام شما با موفقیت انجام شد.",
    "ad_success" => "آگهی شما در اصفهان آگهی ثبت شد.",
    "ad_title_length" => "طول عنوان حداقل باید سه باشد. دوباره وارد کنید:",
    "ad_text_length" => "طول آگهی شما حداقل باید ۳۰ کاراکتر باشد. دوباره وارد کنید:"
);

$replyMarkup['keyboard'] = $button_set[1];
$encodedMarkup = json_encode($replyMarkup, true);


$update = json_decode(file_get_contents('php://input'));
$type = $update->channel_post->chat->type;
$message = $update->message;
$chat_id = $update->message->chat->id;
$name = $update->message->chat->first_name;
$text = $message->text;
$type = $message->chat->type;

#[$name](tg://user?id=$chat_id)
#SendMessageKey($chat_id, "$chat_id", $encodedMarkup);

if(startsWith($text, '/start'))
{
    if($db->userExists($chat_id) == false)
    {
        $db->createUser($chat_id);
    }
    
    SendMessage($chat_id, $txt['start']);
    SendMessage($chat_id, $txt['enter_firstname']);
    exit();
}
else if($db->userExists($chat_id) == false)
{
    SendMessage($chat_id, "ربات را مجدد استارت کنید \n /start");
    exit();
}

$user_info = $db->getUserInfo($chat_id);

if(strlen($user_info['firstname']) < 3)
{
    if(strlen($text) < 3)
    {
        SendMessage($chat_id, $txt['name_length']);
        exit();
    }
    $db->SetFirstname($chat_id, $text);
    SendMessage($chat_id, $txt['enter_lastname']);
    exit();
}
else if(strlen($user_info['lastname']) < 3)
{
    if(strlen($text) < 3)
    {
        SendMessage($chat_id, $txt['name_length']);
        exit();
    }
    $db->SetLastname($chat_id, $text);
    SendMessage($chat_id, $txt['enter_phone']);
    exit();
}
else if(strlen($user_info['phone_number']) < 3)
{
    if(strlen($text) != 11)
    {
        SendMessage($chat_id, $txt['phone_length']);
        exit();
    }
    $db->SetPhone($chat_id, $text);
    SendMessage($chat_id, $txt['sign_success']);
    SendMessageKey($chat_id, $txt['welcome'], $encodedMarkup);
    exit();
}

$ad_status = $db->GetAd($chat_id);

if($ad_status == "title")
{
    if(strlen($text) < 3)
    {
        SendMessage($chat_id, $txt['ad_title_length']);
        exit();
    }
    $db->SetAd($chat_id, "text");
    $db->SendAdvertise($chat_id, $text);
    SendMessage($chat_id, $txt['enter_ad_text']);
    exit();
}
else if($ad_status == "text")
{
    if(strlen($text) < 30)
    {
        SendMessage($chat_id, $txt['ad_text_length']);
        exit();
    }
    $db->SetAd($chat_id, "none");
    $db->SetAdText($chat_id, $text);
    SendMessage($chat_id, $txt['ad_success']);
    exit();
}

if($text == 'ثبت آگهی')
{
    $db->SetAd($chat_id, "title");
    SendMessage($chat_id, $txt['enter_ad_title']);
}
else if($text == 'مشاهده آگهی های جاری')
{
    $ads = $db->getAdvertises();
    foreach($ads as $ad)
    {
        if(strlen($ad['text']) > 30)
        {
            $ad_title = $ad['title'];
            $res = "``` --------------------------------------------\n عنوان: $ad_title\nمتن آگهی: " . $ad['text'] . "```\n\n``` شماره تماس: " . $ad['phone_number'] . "```\n‌";
            SendMessageKey($chat_id, $res, $encodedMarkup);
        }
    }
}
else
{
    SendMessageKey($chat_id, "دستور مورد نظر وجود ندارد!", $encodedMarkup);
}

?>