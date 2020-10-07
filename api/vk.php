<?php

require_once "config.php";

$config = new Config();

$update = json_decode(file_get_contents("php://input"), TRUE);
$message = $update["message"]["text"];
$chatId = $update["message"]["chat"]["id"];
$messageId = $update["message"]["message_id"];
$reply = "&reply_to_message_id=".$messageId;
$userId = $update["message"]["from"]["id"];

// TODO sendMessage method instead of curl($api_url)
function curl($url) {
    $ch = curl_init();
    $opt = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
    curl_setopt_array($ch, $opt);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
$token = empty(getenv('vercel_tg')) ? $config->getBotToken() : getenv('vercel_tg');
$api_url = "https://api.telegram.org/bot{$token}";

if (strpos($message, "/start") === 0) {
    if ($userId == $chatId) {
        $start = "Привет! Я - бот для мгновенного получения информации о пользователе ВКонтакте по его ID или юзернейму.

Использовать: <code>/vk 1</code> или <code>durov</code>


Исходный код доступен здесь - https://github.com/wardsenz/VKUserInfoBot
Пулл-реквесты приветствуются!";
        curl($api_url."/sendmessage?chat_id=".$chatId."&parse_mode=html&text=".urlencode($start));
        exit;
    } else {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&text=Свяжитесь со мной в ЛС для получения помощи!");
        exit;
    }
}

if (strpos($message, "/vk") === 0) {
    $vk_array = explode(" ", $message);
    $vk_user = $vk_array[1];
    if (!isset($vk_user)) {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&text=Запрос пуст.");
        exit;
    }
    if ($vk_user == "0") {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&text=Укажите правильный ID / имя пользователя.");
        exit;
    }
    if (preg_match('/[А-Яа-яЁё]/u', $vk_user)) {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&text=Укажите правильный ID / имя пользователя.");
        exit;
    }
    if (preg_match('/[^a-zA-Z0-9_\d]/', $vk_user)) {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&text=Укажите правильный ID / имя пользователя.");
        exit;
    }
    $callToken = empty(getenv('vercel_vk')) ? $config->getVKToken() : getenv('vercel_vk');
    $call = "https://api.vk.com/method/users.get?user_ids={$vk_user}&fields=home_town,sex,relation,city,country,bdate,verified,status,online,last_seen,followers_count,site,domain,about,quotes,interests,personal,music,contacts,photo_max&access_token={$callToken}&v=5.122";
    $decoded = json_decode(curl($call), true);
    $response = $decoded["response"][0];

    // ERROR HANDLING
    if(isset($decoded["error"])) {
    $errcode = $decoded["error"]["error_code"];
    $errmsg = $decoded["error"]["error_msg"];

    if ($errcode === 113) {
        curl($api_url."/sendMessage?chat_id=".$chatId.$reply."&parse_mode=html&text=Не удалось найти пользователя с данным коротким именем или оно принадлежит сообществу.");
        exit;
    }
    $err_results = "Запрос не выполнен! Код ошибки: <code>{$errcode}</code>\nОтвет сервера: <code>{$errmsg}</code>";
    $send_results = urlencode($err_results);
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&parse_mode=html&text=".$send_results);
        exit;
    }

    $vkId = $response["id"];

    if ($response["deactivated"] == "deleted") {
        curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&parse_mode=html&text=".urlencode("Этот аккаунт удалён.\nID: <code>{$vkId}</code>"));
        exit;
    }

    $vkFname = $response["first_name"];

    $vkLname = $response["last_name"];

    $vkClosed = $response["is_closed"];

    $vkClosed = ($vkClosed === true) ? "Закрытая" : "Открытая";

    $vkPhotoMax = $response["photo_max"];

    $vkPhoto = urlencode($vkPhotoMax);

    $vkDomain = $response["domain"];

    $vkSex = getSex($response["sex"]);

    $vkCity = (empty($response["city"]["title"])) ? "Не указан или скрыт" : $response["city"]["title"];

    $vkHomeTown = (empty($response["home_town"])) ? "Не указан или скрыт" : $response["home_town"];

    $vkCountry = (empty($response["country"]["title"])) ? "Не указан или скрыт" : $response["country"]["title"];

    $vkBdate = (empty($response["bdate"])) ? "Не указан или скрыт" : $response["bdate"];

    $vkPhone = (empty($response["mobile_phone"])) ? "Не указан или скрыт" : $response["mobile_phone"];

    $vkPhoneHome = (empty($response["home_phone"])) ? "Не указан или скрыт" : $response["home_phone"];

    $vkSite = (empty($response["site"])) ? "Не указан" : $response["site"];

    $vkStatus = (empty($response["status"])) ? "Не указан" : $response["status"];

    $vkLastSeen = $response["last_seen"]["time"];

    $vkLastOnline = date('Y-m-d H:i:s', $vkLastSeen);

    $vkLastOnline = (preg_match("/1970-01-01/", $vkLastOnline) === 1) ? "Неизвестно" : $vkLastOnline;

    $vkVerified = ($response["verified"] === 1) ? "✅" : "❌";

    $vkFollowersCount = (empty($response["followers_count"])) ? "Нет данных" : $response["followers_count"];

    $vkRelation = getRelations($response["relation"]);

    $tg_response = json_decode(curl($api_url."/sendmessage?chat_id=".$chatId.$reply."&parse_mode=html&text=<i>Получение данных...</i>"), true);

    $parsedInfo = "
Имя: <b>{$vkFname}</b>
Фамилия: <b>{$vkLname}</b>
ID: <code>{$vkId}</code>
Пол: <b>{$vkSex}</b>
Короткое имя: <code>{$vkDomain}</code>
Страница: <b>{$vkClosed}</b>
Дата рождения: <b>{$vkBdate}</b>
Семейное положение: <b>{$vkRelation}</b>
Статус: <i>{$vkStatus}</i>
Последний онлайн: <code>{$vkLastOnline}</code>
Подтверждена: <b>{$vkVerified}</b>
Сайт: <code>{$vkSite}</code>
Телефон: <b>{$vkPhone}</b>
Телефон (дом.): <b>{$vkPhoneHome}</b>
Город: <b>{$vkCity}</b>
Родной город: <b>{$vkHomeTown}</b>
Страна: <b>{$vkCountry}</b>
Подписчики: {$vkFollowersCount} <a href=\"{$vkPhotoMax}\">&#8204;</a>";
    $results = urlencode($parsedInfo);
    $toEditId = $tg_response["result"]["message_id"];
    curl($api_url."/editmessagetext?chat_id=".$chatId."&parse_mode=html&message_id=".$toEditId."&text=".$results);
    }

    function getSex($sex){
        switch ($sex){
            case 1:
                return "Женский";
                break;
            case 2:
                return "Мужской";
                break;
            default:
                return "Неизвестно";
                break;
        }
        return $sex;
    }

    function getRelations($relation){
        switch ($relation){
            case 0:
                return "Не указано";
                break;
            case 1:
                return "Не женат / Не замужем";
                break;
            case 2:
                return "Есть друг/подруга";
                break;
            case 3:
                return "Помолвлен/помолвлена";
                break;
            case 4:
                return "Женат/замужем";
                break;
            case 5:
                return "Всё сложно";
                break;
            case 6:
                return "В активном поиске";
                break;
            case 7:
                return "Влюблён/влюблена";
                break;
            case 8:
                return "В гражданском браке";
                break;
            default:
                return "Неизвестно";
                break;
        }
}
?>
