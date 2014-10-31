<?php
abstract class ChatServiceController
{
    protected $_chatService;

    public function __construct(ChatService $chatService)
    {
        $this->_chatService = $chatService;
    }

    protected function getNickName($data)
    {
        return $this->_chatService->getNickName($data);
    }

    protected function isPrivateMessage($data) {
        //:BelhommeN!n=id943033@unimatrix.skynet.be PRIVMSG ProteusIV :http://www.google.be
        if(strpos($data, 'PRIVMSG '.$this->getChannelName()) !== false) {
            return false;
        }
        return true;
    }

    protected function getChannelName()
    {
        return $this->_chatService->getChannelName();
    }

    protected function logDataAndResponse($data, $response, $nick='') {
        $fh = fopen(dirname(__FILE__).DIRECTORY_SEPARATOR.'dataAndResponse.log', 'a+');
        fwrite($fh, date('Ymd H:i:s')."\t".str_replace(dirname(__FILE__),'',__FILE__)."\t".$data."\t".$response."\t".$nick.PHP_EOL);
        fclose($fh);
    }

    public abstract function execute($data);
}