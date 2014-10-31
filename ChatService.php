<?php
require_once SCRIPT_ROOT.'/ChatServiceException.php';
Class ChatService
{
    protected $_controllers;
    protected $_configParams;
    protected $_channel;
    public $_socket;

    public function __construct()
    {
        $this->_controllers = array();
        $this->_configParams = null;
        $this->_socket = null;
        $this->_channel = false;
    }

    public function __destruct()
    {
        if($this->_socket !=  false)
        {
            $messageQuit = 'Shutting Down, Going to kick some ass irl!';
            if($this->_channel !== false)
            {
                $this->message($messageQuit,$this->_channel);
            }
            socket_close($this->_socket);
            $this->_socket = false;
            sleep(1);
            $this->messageConsole('Shutting down '.$this->_configParams['CHATBOT']['nickname']);
            exit;
        }
    }

    public function loadConfigFile($configFile)
    {
        if (!file_exists($configFile)) {
            throw new ChatServiceException('Config file could not be loaded.');
        }
        $this->_configParams = parse_ini_file($configFile, 'CHATBOT');
    }

    public function connect()
    {
        $this->checkSetup();

        if (!$this->_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) {
            throw new ChatServiceException('Fatal Error: Socket could not get created.');
        }
        $this->messageConsole('Socket created');

        if (!socket_bind($this->_socket,$this->_configParams['CHATBOT']['hostname'])) {
             throw new ChatServiceException('Fatal Error: Connection could not be initialized for host '.$this->_configParams['CHATBOT']['hostname'].'.');
        }
        $this->messageConsole('Connection initialized for host');

        if (!socket_connect($this->_socket,$this->_configParams['CHATBOT']['server'],$this->_configParams['CHATBOT']['port'])) {
            throw new ChatServiceException('Fatal Error: Could not connect to server');
        }
        $this->messageConsole('Connecting...');
    }

    public function registerBot()
    {
        $this->checkSetup();

        $this->messageSocket('USER '.$this->_configParams['CHATBOT']['ident'].' '.$this->_configParams['CHATBOT']['hostname'].' '.$this->_configParams['CHATBOT']['server'].' :'.$this->_configParams['CHATBOT']['realname']);
        $this->messageSocket('NICK '.$this->_configParams['CHATBOT']['nickname']);
        $this->messageConsole($this->_configParams['CHATBOT']['nickname'].' is alive');
    }

    public function loadControllers()
    {
        $this->checkSetup();
        $controllers = explode(':', $this->_configParams['CHATBOT']['controllers']);

        foreach($controllers as $controller)
        {
            if(!file_exists(CONTROLLERS.'/'.$controller.'.php'))
            {
                throw new ChatServiceException('Fatal Error: a Controller was set but could not get loaded.');
            }
            require_once(CONTROLLERS.'/'.$controller.'.php');
            $this->_controllers[] = new $controller($this);
            $this->messageConsole($this->_configParams['CHATBOT']['nickname'].' is learning '.$controller);
        }
    }

    public function describe($message)
    {
        $this->checkSetup();
        $this->messageSocket('me '.$message);
    }

    public function listen()
    {
        $this->messageConsole($this->_configParams['CHATBOT']['nickname'].' is listening...');
        while(TRUE) {
            if (!$data = socket_read($this->_socket,65000,PHP_NORMAL_READ)) {
               throw new ChatServiceException('Fatal Error: Could not communicate (READ) with socket');
            }

            if ($data == "\n") {
                continue;
            }

            // Before we join we must fist listen if we have received the MOTD
            if ($this->_channel == false && strstr($data,'MOTD')) {
                $this->joinChannel();
            }

            // The IRC-Server will send PING requests and we need to answer them to keep alive.
            $this->pingPong($data);

            // Let the Robot ShutDown.
            $this->shutDown($data);

            // Now we listen and forward the data to the controller
            foreach($this->_controllers as $controller)
            {
                $controller->execute($data);
            }
        }
    }

    public function shutDown($data)
    {
        if(strpos(strtolower($data), ':shutdownrobot') !== false) {
           $this->__destruct();
        }
    }


    public function getNickName($str)
    {
        $nickname = explode('!',$str);
        $nickname = $nickname[0];
        return substr($nickname,1);
    }

    public function getChannelName()
    {
        return $this->_channel;
    }


    protected function pingPong($data)
    {
        $eData = explode(' ',$data);
        if ($eData[0] == 'PING') {
            $this->messageSocket('PONG '.$eData[1]);
        }
    }


    protected function joinChannel()
    {
        $this->_channel = $this->_configParams['CHATBOT']['channel'];
        $this->_password = $this->_configParams['CHATBOT']['password'];
        $this->messageSocket(
            sprintf(
                'JOIN %s %s',
                $this->_channel,
                $this->_password
            )
        );
        $this->message('Ik ben een superbot!',$this->_channel);
    }


    protected function checkSetup()
    {
        if (is_null($this->_configParams))
        {
            throw new ChatServiceException('ChatBot is not setup correctly');
        }
    }

    public function message($message, $receiver)
    {
        $this->checkSetup();
        $this->messageSocket('PRIVMSG '.$receiver.' :'.$message);
    }

    protected function messageSocket($data)
    {
        socket_write($this->_socket,$data."\r\n");
    }

    protected function messageConsole($message)
    {
        echo $message.PHP_EOL;
    }
}
?>