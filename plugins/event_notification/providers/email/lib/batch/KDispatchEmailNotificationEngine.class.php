<?php
/**
 * @package plugins.emailNotification
 * @subpackage Scheduler
 */
class VDispatchEmailNotificationEngine extends VDispatchEventNotificationEngine
{
	const TO_RECIPIENT_TYPE = 'Address';
	
	const CC_RECIPIENT_TYPE = 'CC';
	
	const BCC_RECIPIENT_TYPE = 'BCC';
	
	const REPLYTO_RECIPIENT_TYPE = 'ReplyTo';
	
	/**
	 * Old vidiun default
	 * @var strung
	 */
	protected $defaultFromMail = 'notifications@vidiun.com';
	 
	/**
	 * Old vidiun default
	 * @var strung
	 */
	protected $defaultFromName = 'Vidiun Notification Service';
	
	/**
	 * @var PHPMailer
	 */
	static protected $mailer = null;
	
	static protected $emailFooterTemplate = null;
	
	/* (non-PHPdoc)
	 * @see VDispatchEventNotificationEngine::__construct()
	 */
	public function __construct()
	{
		if(isset(VBatchBase::$taskConfig->params->defaultFromMail) && VBatchBase::$taskConfig->params->defaultFromMail)
			$this->defaultFromMail = VBatchBase::$taskConfig->params->defaultFromMail;
			
		if(isset(VBatchBase::$taskConfig->params->defaultFromName) && VBatchBase::$taskConfig->params->defaultFromName)
			$this->defaultFromName = VBatchBase::$taskConfig->params->defaultFromName;

		if($this::$mailer)
		{
			$this::$mailer->ClearAllRecipients();
			$this::$mailer->ClearCustomHeaders();
			$this::$mailer->ClearReplyTos();
			$this::$mailer->ClearAttachments();
		}
		else
		{
			$this::$mailer = new PHPMailer();
			$this::$mailer->Mailer = 'smtp';
			$this::$mailer->CharSet = 'utf-8';
			$this::$mailer->SMTPKeepAlive = true;
		
			if(isset(VBatchBase::$taskConfig->params->mailPriority) && VBatchBase::$taskConfig->params->mailPriority)
				$this::$mailer->Priority = 	VBatchBase::$taskConfig->params->mailPriority;
				
			if(isset(VBatchBase::$taskConfig->params->mailCharSet) && VBatchBase::$taskConfig->params->mailCharSet)
				$this::$mailer->CharSet = VBatchBase::$taskConfig->params->mailCharSet;
				
			if(isset(VBatchBase::$taskConfig->params->mailContentType) && VBatchBase::$taskConfig->params->mailContentType)
				$this::$mailer->ContentType = VBatchBase::$taskConfig->params->mailContentType;
				
			if(isset(VBatchBase::$taskConfig->params->mailEncoding) && VBatchBase::$taskConfig->params->mailEncoding)
				$this::$mailer->Encoding = 	VBatchBase::$taskConfig->params->mailEncoding;
				
			if(isset(VBatchBase::$taskConfig->params->mailWordWrap) && VBatchBase::$taskConfig->params->mailWordWrap)
				$this::$mailer->WordWrap = 	VBatchBase::$taskConfig->params->mailWordWrap;
				
			if(isset(VBatchBase::$taskConfig->params->mailMailer) && VBatchBase::$taskConfig->params->mailMailer)
				$this::$mailer->Mailer = 	VBatchBase::$taskConfig->params->mailMailer;
				
			if(isset(VBatchBase::$taskConfig->params->mailSendmail) && VBatchBase::$taskConfig->params->mailSendmail)
				$this::$mailer->Sendmail = 	VBatchBase::$taskConfig->params->mailSendmail;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpHost) && VBatchBase::$taskConfig->params->mailSmtpHost)
				$this::$mailer->Host = 	VBatchBase::$taskConfig->params->mailSmtpHost;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpPort) && VBatchBase::$taskConfig->params->mailSmtpPort)
				$this::$mailer->Port = 	VBatchBase::$taskConfig->params->mailSmtpPort;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpHeloMessage) && VBatchBase::$taskConfig->params->mailSmtpHeloMessage)
				$this::$mailer->Helo = 	VBatchBase::$taskConfig->params->mailSmtpHeloMessage;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpSecure) && VBatchBase::$taskConfig->params->mailSmtpSecure)
				$this::$mailer->SMTPSecure = 	VBatchBase::$taskConfig->params->mailSmtpSecure;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpAuth) && VBatchBase::$taskConfig->params->mailSmtpAuth)
				$this::$mailer->SMTPAuth = 	VBatchBase::$taskConfig->params->mailSmtpAuth;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpUsername) && VBatchBase::$taskConfig->params->mailSmtpUsername)
				$this::$mailer->Username = 	VBatchBase::$taskConfig->params->mailSmtpUsername;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpPassword) && VBatchBase::$taskConfig->params->mailSmtpPassword)
				$this::$mailer->Password = 	VBatchBase::$taskConfig->params->mailSmtpPassword;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpTimeout) && VBatchBase::$taskConfig->params->mailSmtpTimeout)
				$this::$mailer->Timeout = 	VBatchBase::$taskConfig->params->mailSmtpTimeout;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpTimeout) && VBatchBase::$taskConfig->params->mailSmtpTimeout)
				$this::$mailer->Timeout = 	VBatchBase::$taskConfig->params->mailSmtpTimeout;
				
			if(isset(VBatchBase::$taskConfig->params->mailSmtpKeepAlive) && VBatchBase::$taskConfig->params->mailSmtpKeepAlive)
				$this::$mailer->SMTPKeepAlive = 	VBatchBase::$taskConfig->params->mailSmtpKeepAlive;
				
			if(isset(VBatchBase::$taskConfig->params->mailXMailerHeader) && VBatchBase::$taskConfig->params->mailXMailerHeader)
				$this::$mailer->XMailer = 	VBatchBase::$taskConfig->params->mailXMailerHeader;
				
			if(isset(VBatchBase::$taskConfig->params->mailErrorMessageLanguage) && VBatchBase::$taskConfig->params->mailErrorMessageLanguage)
				$this::$mailer->SetLanguage(VBatchBase::$taskConfig->params->mailErrorMessageLanguage);
		}
	}
	
	/* (non-PHPdoc)
	 * @see VDispatchEventNotificationEngine::dispatch()
	 */
	public function dispatch(VidiunEventNotificationTemplate $eventNotificationTemplate, VidiunEventNotificationDispatchJobData &$data)
	{
		$this->sendEmail($eventNotificationTemplate, $data);
	}

	/**
	 * @param VidiunEmailNotificationTemplate $emailNotificationTemplate
	 * @param VidiunEmailNotificationDispatchJobData $data
	 * @return boolean
	 */
	protected function sendEmail(VidiunEmailNotificationTemplate $emailNotificationTemplate, VidiunEmailNotificationDispatchJobData &$data)
	{
		if(!$data->to && !$data->cc && !$data->bcc)
			throw new Exception("Recipient e-mail address cannot be null");
			
		$this::$mailer->IsHTML($emailNotificationTemplate->format == VidiunEmailNotificationFormat::HTML);
		
		if($data->priority)
			$this::$mailer->Priority = 	$data->priority;
		if($data->confirmReadingTo)
			$this::$mailer->ConfirmReadingTo = $data->confirmReadingTo;
		if($data->hostname)
			$this::$mailer->Hostname = $data->hostname;
		if($data->messageID)
			$this::$mailer->MessageID = $data->messageID;

		$contentParameters = array();
		if(is_array($data->contentParameters) && count($data->contentParameters))
		{
			foreach($data->contentParameters as $contentParameter)
			{
				/* @var $contentParameter VidiunKeyValue */
				$contentParameters['{' .$contentParameter->key. '}'] = strip_tags($contentParameter->value);
			}		
		}
			
		if($data->to)
		{
			$recipients = $this->getRecipientArray($data->to, $contentParameters);
			foreach ($recipients as $email=>$name)
			{
				if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					continue;
				}
				VidiunLog::info("Adding recipient to TO recipients $name<$email>");
				self::$mailer->AddAddress($email, $name);
			}
		}
		
		if($data->cc)
		{
			$recipients = $this->getRecipientArray($data->cc, $contentParameters);
			foreach ($recipients as $email=>$name)
			{
				if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					continue;
				}
				VidiunLog::info("Adding recipient to CC recipients $name<$email>");
				self::$mailer->AddCC($email, $name);
			}
		}
		
		if($data->bcc)
		{
			$recipients = $this->getRecipientArray($data->bcc, $contentParameters);
			foreach ($recipients as $email=>$name)
			{
				if (!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					continue;
				}
				VidiunLog::info("Adding recipient to BCC recipients $name<$email>");
				self::$mailer->AddBCC($email, $name);
			}
		}
		
		if($data->replyTo)
		{
			$recipients = $this->getRecipientArray($data->replyTo, $contentParameters);
			foreach ($recipients as $email=>$name)
			{
				VidiunLog::info("Adding recipient to ReplyTo recipients $name<$email>");
				self::$mailer->AddReplyTo($email, $name);
			}
		}
			
		if(!is_null($data->fromEmail)) 
		{
			$email = $data->fromEmail;
			$name = $data->fromName;
			if(is_array($contentParameters) && count($contentParameters))
			{
					$email = str_replace(array_keys($contentParameters), $contentParameters, $email);
					$name = str_replace(array_keys($contentParameters), $contentParameters, $name);
			}
			
			$this::$mailer->Sender = $email;
			$this::$mailer->From = $email;
			$this::$mailer->FromName = $name;
		}
		else
		{
			$this::$mailer->Sender = $this->defaultFromMail;
			$this::$mailer->From = $this->defaultFromMail;
			$this::$mailer->FromName = $this->defaultFromName;
		}
		VidiunLog::info("Sender [{$this::$mailer->FromName}<{$this::$mailer->From}>]");
		
		$subject = $emailNotificationTemplate->subject;
		$body = $emailNotificationTemplate->body;

		$footer = $this->getEmailFooter();
		if(!is_null($footer))
		{
			$body .= "\n" . $footer;
		}
		
		if(is_array($contentParameters) && count($contentParameters))
		{		
			$subject = str_replace(array_keys($contentParameters), $contentParameters, $subject);
			$body = str_replace(array_keys($contentParameters), $contentParameters, $body);
		}
				
		VidiunLog::info("Subject [$subject]");
		VidiunLog::info("Body [$body]");
		
		$this::$mailer->Subject = $subject;
		$this::$mailer->Body = $body;
	
		if(is_array($data->customHeaders) && count($data->customHeaders))
		{
			foreach($data->customHeaders as $customHeader)
			{
				/* @var $customHeader VidiunKeyValue */
				$key = $customHeader->key;
				$value = $customHeader->value;
				/* @var $customHeader VidiunKeyValue */
				if(is_array($contentParameters) && count($contentParameters))
				{
					$key = str_replace(array_keys($contentParameters), $contentParameters, $key);
					$value = str_replace(array_keys($contentParameters), $contentParameters, $value);
				}
				$this::$mailer->AddCustomHeader("$key: $value");
			}
		}
		
		try
		{
			$success = $this::$mailer->Send();
			if(!$success)
				throw new vTemporaryException("Sending mail failed: " . $this::$mailer->ErrorInfo);
		}
		catch(Exception $e)
		{
			throw new vTemporaryException("Sending mail failed with exception: " . $e->getMessage(), $e->getCode());	
		}
			
		return true;
	}
	
	private function getEmailFooter()
	{
		if(is_null(self::$emailFooterTemplate))
		{
			$file_path = dirname(__FILE__)."/emailFooter.html";
			if(file_exists($file_path))
			{
				$file_content = file_get_contents($file_path);
				self::$emailFooterTemplate = $file_content;
			}
		}
		
		$footer = vsprintf(self::$emailFooterTemplate, array(VBatchBase::$taskConfig->params->forumUrl));	
		return $footer;
	} 
	
	/**
	 * Function to retrieve array of recipients for the email notifiation based on the data.
	 * @param VidiunEmailNotificationRecipientJobData $recipientJobData
	 * @param array $contentParameters
	 * @return array;
	 */
	protected function getRecipientArray (VidiunEmailNotificationRecipientJobData $recipientJobData, array $contentParameters)
	{
		$recipientEngine = VEmailNotificationRecipientEngine::getEmailNotificationRecipientEngine($recipientJobData);
		$recipients = $recipientEngine->getRecipients($contentParameters);
		
		return $recipients;
	}
}
