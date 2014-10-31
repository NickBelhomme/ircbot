<?php
error_reporting(E_ALL|E_STRICT);

date_default_timezone_set('Europe/Brussels');
define('SCRIPT_ROOT', dirname(__FILE__));
define('CONTROLLERS', SCRIPT_ROOT.'/controllers');
require_once SCRIPT_ROOT.'/ChatService.php';

try {
    $chatService = new ChatService();
    $chatService->loadConfigFile(SCRIPT_ROOT.'/config.ini');
    $chatService->connect();
    $chatService->registerBot();
    $chatService->loadControllers();
    $chatService->listen();
}
catch(ChatServiceException $e)
{
    echo $e->getMessage().PHP_EOL.socket_strerror(socket_last_error($chatService->_socket));
    $chatService = null;
}
?>