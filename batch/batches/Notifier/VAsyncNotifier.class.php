<?php
/**
 * @package Scheduler
 * @subpackage Notifier
 */

/**
 * Will run periodically and cleanup directories from old files that have a specific pattern (older than x days) 
 * 
 * @uses batch->addMailJob
 * @uses multiRequest
 * 
 * @package Scheduler
 * @subpackage Notifier
 */
class VAsyncNotifier extends VJobHandlerWorker
{
	private $partnerMap = null;
	
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::NOTIFICATION;
	}
	
	/* (non-PHPdoc)
	 * @see VJobHandlerWorker::exec()
	 */
	protected function exec(VidiunBatchJob $job)
	{
		return $job;
	}
	
	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		if(VBatchBase::$taskConfig->isInitOnly())
			return $this->init();
		
		// of type VidiunBatchGetExclusiveNotificationJobsResponse
		$notificationResponse = VBatchBase::$vClient->batch->getExclusiveNotificationJobs($this->getExclusiveLockKey(), VBatchBase::$taskConfig->maximumExecutionTime, VBatchBase::$taskConfig->maxJobsEachRun, $this->getFilter());
		
		$jobs = $notificationResponse->notifications;
		$partners = $notificationResponse->partners;
		
		VidiunLog::info(count($jobs) . " notification jobs to perform");
		
		if(! count($jobs))
		{
			VidiunLog::info("Queue size: 0 sent to scheduler");
			$this->saveSchedulerQueue(self::getType(), 0);
			return;
		}
		
		$this->setPartnerMap($partners);
		$this->sendNotifications($jobs);
	}
	
	/**
	 * @param array $notificationJobs
	 */
	private function sendNotifications(array $notificationJobs)
	{
		try
		{
			// eventually all partners will support multiNotifications 
			// when so - can remove some of the code
			// see  which notifications should go to multiNotification and which should stay in single notificaiton
			list($single_notifications, $multi_notifications) = $this->splitToMulti($notificationJobs);
			
			foreach($multi_notifications as $partner_id => $multi_notifications_per_partner)
			{
				$partner = $this->getPartner($partner_id);
				if(! $partner)
					continue;
				
				VidiunLog::info("Sending multi-notifications to partner [$partner_id]");
				
				// we assume that the partner wants notificatins or else it would have not appeared in the list	
				list($params_sent, $res, $http_code) = $this->sendMultiNotifications($partner->notificationUrl, $partner->adminSecret, $multi_notifications_per_partner);
				$this->updateMultiNotificationStatus($multi_notifications_per_partner, $http_code, $res);
			}
			
			// see if can reduce number of notifications
			// if an object was deleted - all previous notifications are not relevant
			foreach($single_notifications as $not)
			{
				$partner = $this->getPartner($not->partnerId);
				if(! $partner)
					continue;
				
				VidiunLog::info("Sending single-notifications to partner [{$partner->id}]");
				// we assume that the partner wants notificatins or else it would have not appeared in the DB			
				list($params_sent, $res, $http_code) = $this->sendSingleNotification($partner->notificationUrl, $partner->adminSecret, $not);
				$this->updateNotificationStatus($not, $http_code, $res);
			}
		}
		catch(Exception $ex)
		{
		
		}
		
		VBatchBase::$vClient->startMultiRequest();
		foreach($notificationJobs as $job)
		{
			VidiunLog::info("Free job[$job->id]");
			$this->freeExclusiveJob($job);
			$this->onFree($job);
		}

		$freeExclusiveResults = VBatchBase::$vClient->doMultiRequest();
		$freeExclusiveResults = array_pop($freeExclusiveResults);
		VidiunLog::info("Queue size: {$freeExclusiveResults->queueSize} sent to scheduler");
		$this->saveSchedulerQueue(static::getType(), $freeExclusiveResults->queueSize);
	}
	
	/**
	 * @param string $url
	 * @param string $signature_key
	 * @param VidiunBatchJob $not
	 * @param string $prefix
	 * @return array 
	 */
	private function sendSingleNotification($url, $signature_key, VidiunBatchJob $not, $prefix = null)
	{
		$start_time = microtime(true);
		
		list($params, $raw_siganture) = VAsyncNotifierParamsUtils::prepareNotificationData($url, $signature_key, $not, $not->data, $prefix);
		
		try
		{
			list($params, $result, $http_code) = VAsyncNotifierSender::send($url, $params);
		}
		catch(Exception $ex)
		{
			// try a second time - the connection will probably be closed
			try
			{
				list($params, $result, $http_code) = VAsyncNotifierSender::send($url, $params);
			}
			catch(Exception $ex)
			{
				VidiunLog::err('sendSingleNotification failed second try with message: '.$ex->getMessage());
			}
		}
		
		$end_time = microtime(true);
		VidiunLog::info("partner [{$not->partnerId}] notification [{$not->id}] of type [{$not->jobSubType}] to [{$url}]\nhttp result code [{$http_code}]\n" . print_r($params, true) . "\nresult [{$result}]\nraw_signature [$raw_siganture]\ntook [" . ($end_time - $start_time) . "]");
		
		// see if the hit worked properly
		// the hit should return a specific string to indicate a success 
		return array($params, $result, $http_code);
	}
	
	/**
	 * @param string $url
	 * @param string $signature_key
	 * @param array $not_list
	 * @return array 
	 */
	private function sendMultiNotifications($url, $signature_key, array $not_list)
	{
		$start_time = microtime(true);
		
		$params = array();
		$index = 1;
		$not_id_str = "";
		foreach($not_list as $not)
		{
			$prefix = "not{$index}_";
			list($notification_params, $raw_siganture) = VAsyncNotifierParamsUtils::prepareNotificationData($url, $signature_key, $not, $not->data, $prefix);
			$index ++;
			$params = array_merge($params, $notification_params);
			$not_id_str .= $not->id . ", ";
		}
		
		$params["multi_notification"] = "true";
		$params["number_of_notifications"] = count($not_list);
		
		//the "sig" parameter will be overidden - so eventually only the last will remain
		list($params, $raw_siganture) = VAsyncNotifierParamsUtils::signParams($signature_key, $params);
		try
		{
			list($params, $result, $http_code) = VAsyncNotifierSender::send($url, $params);
		}
		catch(Exception $ex)
		{
			// try a second time - the connection will probably be closed
			try
			{
				list($params, $result, $http_code) = VAsyncNotifierSender::send($url, $params);
			}
			catch(Exception $ex)
			{
				VidiunLog::err('sendMultiNotifications failed second try with message: '.$ex->getMessage());
			}
		}
		
		$end_time = microtime(true);
		VidiunLog::info("partner [{$not->partnerId}] notification [$not_id_str] to [{$url}]\nhttp result code [{$http_code}]\n" . print_r($params, true) . "\nresult [{$result}]\nraw_signature [$raw_siganture]\ntook [" . ($end_time - $start_time) . "]");
		
		// see if the hit worked properly
		// the hit should return a specific string to indicate a success 
		return array($params, $result, $http_code);
	}
	
	/**
	 * @param array $not_list
	 * @param int $http_code
	 * @param string $res
	 */
	private function updateMultiNotificationStatus(array $not_list, $http_code, $res)
	{
		VBatchBase::$vClient->startMultiRequest();
		foreach($not_list as $not)
			$this->updateNotificationStatus($not, $http_code, $res);
		VBatchBase::$vClient->doMultiRequest();
	}
	
	/**
	 * update the $not->status and $not->numberOfAttempts
	 * 
	 * @param VidiunBatchJob $not
	 * @param unknown_type $http_code
	 * @param unknown_type $res
	 */
	private function updateNotificationStatus(VidiunBatchJob $not, $http_code, $res)
	{
		$not->data->notificationResult = $res;

		if(!VCurlHeaderResponse::isError($http_code) && $res !== false)
		{
			// final state - update on server
			$not->status = VidiunBatchJobStatus::FINISHED;
		}
		else //if ( $res == VidiunNotificationResult::ERROR_RETRY  )
		{
			$not->status = VidiunBatchJobStatus::RETRY;
		}

		$updateData = new VidiunNotificationJobData();
		//Instead of writing the notification result to th DB, write it to the log only
		VidiunLog::info("Notification result: [" . $not->data->notificationResult ."]");
		//$updateData->notificationResult = $not->data->notificationResult;
		
		$updateNot = new VidiunBatchJob();
		$updateNot->status = $not->status;
		$updateNot->data = $updateData;
		
		$this->onUpdate($not);
		$this->updateExclusiveJob($not->id, $updateNot);
	}
	
	/**
	 * will split the notifications into 2 lists -
	 * multi_notifications_per_partner - an associative array where the key is the partner_id and value is an array of notifications for that partner
	 * single_notifications - an array of notifications that should be sent one by one
	 * 
	 * @param array $notifications
	 * @return multitype:multitype: Ambigous <multitype:, unknown> 
	 */
	private function splitToMulti(array $notifications)
	{
		$single_notifications = array();
		$multi_notifications = array();
		foreach($notifications as $not)
		{
			$partner = $this->getPartner($not->partnerId);
			if($partner->allowMultiNotification)
			{
				if(! isset($multi_notifications[$not->partnerId]))
					$multi_notifications[$not->partnerId] = array();
				
				$multi_notifications[$not->partnerId][] = $not;
			}
			else
			{
				$single_notifications[] = $not;
			}
		}
		return array($single_notifications, $multi_notifications);
	}
	
	/**
	 * the partners list should hold the partner for each of the notifications
	 * 
	 * @param array $partners
	 */
	private function setPartnerMap($partners)
	{
		if($this->partnerMap === null)
		{
			// instead of iterating the list every time - build a map for the first time where the partnerId is the key
			// build the partnerMap for the first time
			foreach($partners as $partner)
			{
				$this->partnerMap[$partner->id] = $partner;
			}
		}
	}
	
	private function getPartner($partnerId)
	{
		if(isset($this->partnerMap[$partnerId]))
		{
			return $this->partnerMap[$partnerId];
		}
		VidiunLog::err("Cannot find partner for partnerId [$partnerId]");
		return false;
	}
}

?>