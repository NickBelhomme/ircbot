<?php
require_once CONTROLLERS.'/ChatServiceController.php';

class ChatServiceTinyUrlController extends ChatServiceController
{
    public function execute($data)
    {
        if ($this->getChannelName() !== false)
        {
            if($this->isPrivateMessage($data)) {
                $nickName = $this->getNickName($data);
            } else {
                $nickName = $this->getChannelName();
            }

            $urls = $this->extractUrls($data);
            if ($urls != false)
            {
                foreach($urls as $url)
                {
                    if (!empty($url[0]) && 'http' !== $url[0]) {
                        $tinyUrl = $this->getTinyUrl($url[0]);
                        $message = $url[0] .' => '.$tinyUrl;
                        $this->_chatService->message($message, $nickName);
                        $this->logDataAndResponse($data, $message, $nickName);
                    }
                }
            }
        }
    }

    protected function extractUrls($data)
    {
        $pattern = '@\b(https?|ftp|file)://[-a-z0-9+&#/%?=~_|!:,.;]*[-a-z0-9+&#/%=~_|]@i';
        preg_match_all($pattern, $data, $matches);
        if (count($matches) > 0) {
            return $matches;
        }
        return false;
    }

    protected function getTinyUrl($url)
    {
    	$converturl = "http://tinyurl.com/create.php";						// www.tinyurl.com processing page
    	$searchstart = "<blockquote><b>http://tinyurl.com";					// look for this string in the page code
    	$searchend = "</b>";												// this is the end of the tinyurl string
    	$resultoffset = 15;													// position after the $searchstart location that tinyurl starts
    	$readpage = file_get_contents($converturl."?url=".$url)."<br>";	    // load resultpage from tinyurl.com into $readpage
    	$start = strpos($readpage, $searchstart) + $resultoffset;			// find start position of tinyurl string
    	$finish = strpos($readpage, "</b>", $start)-$start;					// find end position of tinyurl string
    	return substr($readpage, $start, $finish);
    }
}