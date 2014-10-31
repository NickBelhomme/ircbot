<?php
require_once CONTROLLERS.'/ChatServiceController.php';

class ChatServiceWhoIsController extends ChatServiceController
{
    protected $whois = array();

    public function __construct($chatService) {
        parent::__construct($chatService);
        $this->whois['nick'] = 'Nick is een jongeman van 29 uit Aarschot';
    }

    public function execute($data)
    {
        $nickName = $this->getNickName($data);

        if (preg_match('/:\!whois ([a-z0-9-_.]+)/', strtolower($data), $matches)) {
           if(array_key_exists($matches[1], $this->whois)) {
               $this->_chatService->message($this->whois[$matches[1]], $nickName);
           }
        } else if(preg_match('/:\!register ([a-z0-9-_.]+) ([a-z0-9-_ .]+)/', strtolower($data), $matches)) {
           if(!array_key_exists($matches[1], $this->whois)) {
               $this->_chatService->message('thx for registering '.$matches[1], $nickName);
           } else {
               $this->_chatService->message('thx for updating '.$matches[1], $nickName);
           }
           $this->whois[$matches[1]] = $matches[2];
        }
    }
}