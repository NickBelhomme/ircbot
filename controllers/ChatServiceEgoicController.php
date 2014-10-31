<?php
require_once CONTROLLERS.'/ChatServiceController.php';

class ChatServiceEgoicController extends ChatServiceController
{
    public function execute($data)
    {
        $nickName = $this->getNickName($data);

        if (strpos(strtolower($data), ':what is oop') !== false) {
           $this->_chatService->message('OOP stands for Object Oriented Programming', $nickName);
        }
        else if (strpos(strtolower($data), 'domineren') !== false) {
           $this->_chatService->message('I want to dominate too!! World Domination RULES!', $nickName);
        }
    }
}