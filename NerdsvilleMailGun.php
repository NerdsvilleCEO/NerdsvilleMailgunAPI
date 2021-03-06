<?php

class NerdsvilleMailGun{
    function __construct($from, $apiVersion, $domain, $secret, $trackClicks, $trackOpens, $fileNames=null, $filetypes=null, $attachments=null, $isImage=false){
       $this->from = $from;
       $this->apiVersion = $apiVersion;
       $this->domain = $domain;
       $this->secret = $secret;
       $this->trackClicks = $trackClicks;
       $this->trackOpens = $trackOpens;
       $this->attachments = $attachments;
       $this->fileNames = $fileNames;
       $this->filetypes = $filetypes;
       $this->isImage = $isImage;
    }

    function sendPlainText($to, $subject, $message){
       $postfields = $this->setPostFields($to, $subject, $message);
       $this->initializeCurl($postfields);  
    }

    function sendHTMLMessage($to, $subject, $htmlMessage){
       $postfields = $this->setPostFields($to, $subject, "", $htmlMessage);
       $this->initializeCurl($postfields);
    }

    function sendPlainTextOrHTML($to, $subject, $message, $htmlMessage){
       $postfields = $this->setPostFields($to, $subject, $message, $htmlMessage);
       $this->initializeCurl($postfields);
    }

    function setPostFields($to, $subject, $message="", $htmlMessage=""){
        $postfields = array(
		    "to"=>$to,
		    "from"=>$from,
		    "subject"=>$subject,
		    "text"=>$message,
		    "html"=>$htmlMessage=="" ? $message : $htmlMessage,
		    "o:tracking-clicks"=>$this->trackClicks ? "yes" : "no",
		    "o:tracking-opens"=>$this->trackOpens ? "yes" : "no");
        if($this->isImage){
            foreach($this->attachments as $key=>$attachment){ 
                $postfields["attachment[".$key."]"] = "@".$attachment.
                                           ';filename=' . $this->fileNames[$key].
                                           ';type='. $this->filetypes[$key];
            }
        }
        return $postfields;
   }
	   
   function initializeCurl($postfields){
	$curlURL = "https://api.mailgun.net/v" + $this->apiVersion+"/" + $this->domain;
        $curlURL .= "/messages"; //Using messages API endpoint for POST
	$headers = $isImage ? array("Content-Type:multipart/form-data") : array();
        $ch = curl_init();

	/*CURL OPTIONS*/
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, 'api:' + $this->secret);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_URL, $curlURL);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
	curl_exec($ch);

	$info = curl_getinfo($ch);
	if(!curl_errno($ch) || $info['http_code'] != 200) {
	    print 'SUCCESS!';
	} else {
	    print 'CAN HAZ ERROR?!?!?' . curl_error($ch);
	}
	curl_close($ch);
    }
}
