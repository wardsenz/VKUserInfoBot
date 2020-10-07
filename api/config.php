<?php

class Config {
    private $BotToken; // Telegram Bot Token
    private $VKToken; // VK Token

    public function getBotToken(){
        return $this->BotToken;
    }

    public function setBotToken($token){
        $this->BotToken = $token;
    }

    public function getVKToken(){
        return $this->VKToken;
    }

    public function setVKToken($token){
        $this->VKToken = $token;
    }
}

?>
