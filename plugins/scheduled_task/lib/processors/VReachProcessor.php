<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.processors
 */
class VReachProcessor extends VGenericProcessor
{
	/**
	 * @param VidiunScheduledTaskProfile $profile
	 */
	public function processProfile(VidiunScheduledTaskProfile $profile)
	{
		// To make sure that we run this task once a day.
		if ( self::wasHandledToday($profile) )
		{
			VidiunLog::info("Reach Scheduled Task Profile [$profile->id] was already handled today. No need to handle again");
			return;
		}

		$this->taskRunner->impersonate($profile->partnerId);
		try
		{
			$maxTotalCountAllowed = $this->preProcess($profile);
			$objectsData = $this->handleProcess($profile, $maxTotalCountAllowed);
			$this->postProcess($profile, $objectsData);

		} catch (Exception $ex)
		{
			$this->taskRunner->unimpersonate();
			throw $ex;
		}
		$this->taskRunner->unimpersonate();
	}

	/**
	 * @param VidiunScheduledTaskProfile $profile
	 * @param $object
	 * @param $errorObjectsIds
	 * @param $objectsData
	 * @return array
	 */
	protected function handleObject(VidiunScheduledTaskProfile $profile, $object, $errorObjectsIds, $objectsData)
	{
		list($error, $tasksCompleted) = $this->processObject($profile, $object);
		if ($error)
			$errorObjectsIds[] = $object->id;
		else if ($object instanceof VidiunEntryVendorTask && $object->status == EntryVendorTaskStatus::PENDING_MODERATION)
				$objectsData[] = $object;

		return array($error, $objectsData, $tasksCompleted);
	}

	protected function postProcess($profile, $objectsData)
	{
		if ((self::getReachProfileTaskType($profile) == ObjectTaskType::MAIL_NOTIFICATION) && count($objectsData))
		{
			$client = $this->taskRunner->getClient();
			VReachMailNotificationEngine::sendMailNotification($profile->objectTasks[0], $objectsData, $profile->id, $profile->partnerId, $client);
		}
	}

	protected static function wasHandledToday(VidiunScheduledTaskProfile  $profile) {
		return (intval(time() / 86400) == (intval($profile->lastExecutionStartedAt / 86400)));
	}

	protected static function getReachProfileTaskType(VidiunScheduledTaskProfile $profile)
	{
		return $profile->objectTasks[0]->type;
	}

}