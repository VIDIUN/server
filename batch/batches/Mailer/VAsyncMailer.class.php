<?php
/**
 * Will import a single URL and store it in the file system.
 * The state machine of the job is as follows:
 * 	 	parse URL	(youTube is a special case) 
 * 		fetch heraders (to calculate the size of the file)
 * 		fetch file 
 * 		move the file to the archive
 * 		set the entry's new status and file details  (check if FLV) 
 *
 * @package Scheduler
 * @subpackage Mailer
 */
class VAsyncMailer extends VJobHandlerWorker
{
	const MAILER_DEFAULT_SENDER_EMAIL = 'notifications@vidiun.com';
	const MAILER_DEFAULT_SENDER_NAME = 'Vidiun Notification Service';
	const DEFAULT_LANGUAGE = 'en';
	
	protected $texts_array; // will hold the configuration of the ini file
	
	/**
	 * @var PHPMailer
	 */
	protected $mail;
	
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::MAIL;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $job;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::run()
	 */
	public function run($jobs = null)
	{
		if(VBatchBase::$taskConfig->isInitOnly())
			return $this->init();
		
		$jobs = VBatchBase::$vClient->batch->getExclusiveJobs( 
			$this->getExclusiveLockKey() , 
			VBatchBase::$taskConfig->maximumExecutionTime , 
			$this->getMaxJobsEachRun() , 
			$this->getFilter(),
			static::getType()
		);
			
		VidiunLog::info(count($jobs) . " mail jobs to perform");
								
		if(!count($jobs) > 0)
		{
			VidiunLog::info("Queue size: 0 sent to scheduler");
			$this->saveSchedulerQueue(self::getType(), 0);
			return;
		}
				
		$this->initConfig();
		VBatchBase::$vClient->startMultiRequest();
		foreach($jobs as $job)
			$this->send($job, $job->data);
		VBatchBase::$vClient->doMultiRequest();		
			
			
		VBatchBase::$vClient->startMultiRequest();
		foreach($jobs as $job)
		{
			VidiunLog::info("Free job[$job->id]");
			$this->onFree($job);
	 		VBatchBase::$vClient->batch->freeExclusiveJob($job->id, $this->getExclusiveLockKey(), static::getType());
		}
		$responses = VBatchBase::$vClient->doMultiRequest();
		$response = end($responses);
		
		VidiunLog::info("Queue size: $response->queueSize sent to scheduler");
		$this->saveSchedulerQueue(self::getType(), $response->queueSize);
	}
	
	/*
	 * Will take a single VidiunMailJob and send the mail using PHPMailer  
	 * 
	 * @param VidiunBatchJob $job
	 * @param VidiunMailJobData $data
	 */
	protected function send(VidiunBatchJob $job, VidiunMailJobData $data)
	{
		if (!isset($this->texts_array[$data->language]))
		{
			$this->initConfig($data->language);	
		}
		
		try
		{
			$separator = $data->separator;
 			$result = $this->sendEmail( 
 				$data->recipientEmail,
 				$data->recipientName,
 				$data->mailType,
 				explode ( $separator , $data->subjectParams ) ,
 				explode ( $separator , $data->bodyParams ),
 				$data->fromEmail ,
 				$data->fromName,
 				$data->language,
 				$data->isHtml);
			
	 		if ( $result )
	 		{
	 			$job->status = VidiunBatchJobStatus::FINISHED;
	 		}
	 		else
	 		{
	 			$job->status = VidiunBatchJobStatus::FAILED;
	 		}
	 			
			VidiunLog::info("job[$job->id] status: $job->status");
			$this->onUpdate($job);
			
			$updateJob = new VidiunBatchJob();
			$updateJob->status = $job->status;
	 		VBatchBase::$vClient->batch->updateExclusiveJob($job->id, $this->getExclusiveLockKey(), $updateJob);			
		}
		catch ( Exception $ex )
		{
			VidiunLog::crit( $ex );
		}
	}
	

	protected function sendEmail( $recipientemail, $recipientname, $type, $subjectParams, $bodyParams, $fromemail , $fromname, $language = 'en', $isHtml = false  )
	{
		$this->mail = new PHPMailer();
		$this->mail->CharSet = 'utf-8';
		$this->mail->Encoding = 'base64';
		$this->mail->IsHTML($isHtml);
		$this->mail->AddAddress($recipientemail);
			
		if ( $fromemail != null && $fromemail != '' ) 
		{
			// the sender is what was definied before the template mechanism
			$this->mail->Sender = self::MAILER_DEFAULT_SENDER_EMAIL;
			
			$this->mail->From = $fromemail ;
			$this->mail->FromName = ( $fromname ? $fromname : $fromemail ) ;
		}
		else
		{
			$this->mail->Sender = self::MAILER_DEFAULT_SENDER_EMAIL;
			
			$this->mail->From = self::MAILER_DEFAULT_SENDER_EMAIL ;
			$this->mail->FromName = self::MAILER_DEFAULT_SENDER_NAME ;
		}
			
		$this->mail->Subject = $this->getSubjectByType( $type, $language, $subjectParams  ) ;
		$this->mail->Body = $this->getBodyByType( $type, $language, $bodyParams, $recipientemail, $isHtml ) ;
			
//		$this->mail->setContentType( "text/plain; charset=\"utf-8\"" ) ; //; charset=utf-8" );
		// definition of the required parameters
		
//		$this->mail->prepare();

		// send the email
		$body = $this->mail->Body;
		if ( strlen ( $body ) > 1000 ) 
		{
			$body_to_log = "total length [" . strlen ( $body ) . "]:\n" . " body: " . substr($body , 0 , 1000 ) ;
		}
		else
		{
			$body_to_log  = " body: " . $body;
		}
		VidiunLog::info( 'sending email to: '. $recipientemail . " subject: " . $this->mail->Subject .  $body_to_log );
			
		try
		{
			return ( $this->mail->Send() ) ;
		} 
		catch ( Exception $e )
		{
			VidiunLog::err( $e );
			return false;
		}
	}
	
	
	protected function getSubjectByType( $type, $language, $subjectParamsArray  )
	{
		if ( $type > 0 )
		{
			$languageTexts = isset($this->texts_array[$language]) ? $this->texts_array[$language] : reset($this->texts_array);
			$defaultLanguageTexts = $this->texts_array[self::DEFAULT_LANGUAGE];
			$defaultSubject = isset ($defaultLanguageTexts['subjects'][$type]) ? $defaultLanguageTexts['subjects'][$type] : '';
			$subject = isset ($languageTexts['subjects'][$type]) ? $languageTexts['subjects'][$type] : $defaultSubject;
			$subject = vsprintf( $subject, $subjectParamsArray );
			//$this->mail->setSubject( $subject );
			return $subject;
		}
		else
		{
			// use template 
		}
	}

	protected function getBodyByType( $type, $language, $bodyParamsArray, $recipientemail, $isHtml = false  )
	{
		// if this does not need the common_header, under common_text should have $type_header =
		// same with footer
		$languageTexts = isset($this->texts_array[$language]) ? $this->texts_array[$language] : reset($this->texts_array);
		$defaultLanguageTexts = $this->texts_array[self::DEFAULT_LANGUAGE];
		$common_text_arr = $languageTexts['common_text'];
		$defaultCommonTexts = $defaultLanguageTexts['common_text'];
		$footer = ( isset($common_text_arr[$type . '_footer']) ) ? $common_text_arr[$type . '_footer'] : ($common_text_arr['footer'] ? $common_text_arr['footer'] : $defaultCommonTexts['footer']);
		$defaultBody = isset ($defaultLanguageTexts['bodies'][$type]) ? $defaultLanguageTexts['bodies'][$type] : '';
		$body = isset($languageTexts['bodies'][$type]) ? $languageTexts['bodies'][$type] : $defaultBody;
		
		// TODO - move to batch config
		$forumsLink = $this->getAdditionalParams('forumUrl');
		$unsubscribeLink = $this->getAdditionalParams('unsubscribeUrl') . self::createBlockEmailStr($recipientemail);
		
		$footer = vsprintf($footer, array($forumsLink, $unsubscribeLink) );

		$body .= "\n" . $footer;
		VidiunLog::debug("type [$type]");
		VidiunLog::debug("params [" . print_r($bodyParamsArray, true) . "]");
		VidiunLog::debug("body [$body]");
		VidiunLog::debug("footer [$footer]");
		$body = vsprintf( $body, $bodyParamsArray );
		if ($isHtml)
		{
			$body = str_replace( "<BR>", "<br />\n", $body );
			$body = '<p align="left" dir="ltr">'.$body.'</p>';
		}
		else
		{
			$body = str_replace( "<BR>", chr(13).chr(10), $body );
		}	
		$body = str_replace( "<EQ>", "=", $body );
		$body = str_replace( "<EM>", "!", $body ); // exclamation mark
		
		VidiunLog::debug("final body [$body]");
		return $body;
	}
		
	protected function initConfig ( $language = null)
	{
		$languages = array($language ? $language : self::DEFAULT_LANGUAGE );

		// now we read the ini files with the texts
		// NOTE: '=' signs CANNOT be used inside the ini files, instead use "<EQ>"
		$rootdir =  realpath(dirname(__FILE__).'');
			
		foreach ( $languages as $language)
		{
			if (!isset($this->texts_array[$language]))
			{
				$filename = $rootdir."/emails_".$language.".ini";
				VidiunLog::debug( 'ini filename = '.$filename );
				if ( ! file_exists ( $filename )) 
				{
					VidiunLog::crit( 'Fatal:::: Cannot find file: '.$filename );
					continue;
				}
				$ini_array = parse_ini_file( $filename, true );
				$this->texts_array[$language] = array( 'subjects' => $ini_array['subjects'],
				'bodies'=>$ini_array['bodies'] ,
				'common_text'=> $ini_array['common_text'] );
			}
		}		
	}
	
	
	// should be the same as on the server
	protected static $key = "myBlockedEmailUtils";
	const SEPARATOR = ";";
	const EXPIRY_INTERVAL = 2592000; // 30 days in seconds
	
	protected static function createBlockEmailStr ( $email )
	{
		return  $email . self::SEPARATOR . vString::expiryHash( $email , self::$key , self::EXPIRY_INTERVAL );
	}
}
