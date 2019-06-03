<?php
/**
 * @package Scheduler
 * @subpackage RecalculateCache
 */
class VRecalculateResponseProfileCacheEngine extends VRecalculateCacheEngine
{
	const RESPONSE_PROFILE_CACHE_ALREADY_RECALCULATED = 'RESPONSE_PROFILE_CACHE_ALREADY_RECALCULATED';
	const RESPONSE_PROFILE_CACHE_RECALCULATE_RESTARTED = 'RESPONSE_PROFILE_CACHE_RECALCULATE_RESTARTED';
	
	protected $maxCacheObjectsPerRequest = 10;
	
	public function __construct()
	{
		if(VBatchBase::$taskConfig->params->maxCacheObjectsPerRequest)
			$this->maxCacheObjectsPerRequest = intval(VBatchBase::$taskConfig->params->maxCacheObjectsPerRequest);
	}
	
	/* (non-PHPdoc)
	 * @see VRecalculateCacheEngine::recalculate()
	 */
	public function recalculate(VidiunRecalculateCacheJobData $data)
	{
		return $this->doRecalculate($data);
	}
	
	public function doRecalculate(VidiunRecalculateResponseProfileCacheJobData $data)
	{
		$job = VJobHandlerWorker::getCurrentJob();
		VBatchBase::impersonate($job->partnerId);
		$partner = VBatchBase::$vClient->partner->get($job->partnerId);
		VBatchBase::unimpersonate();
		
		$role = reset($data->userRoles);
		/* @var $role VidiunIntegerValue */
		$privileges = array(
			'setrole:' . $role->value,
			'disableentitlement',
		);
		$privileges = implode(',', $privileges);
		
		$client = new VidiunClient(VBatchBase::$vClientConfig);
		$vs = $client->generateSession($partner->adminSecret, 'batchUser', $data->vsType, $job->partnerId, 86400, $privileges);
		$client->setVs($vs);
		
		$options = new VidiunResponseProfileCacheRecalculateOptions();
		$options->limit = $this->maxCacheObjectsPerRequest;
		$options->cachedObjectType = $data->cachedObjectType;
		$options->objectId = $data->objectId;
		$options->startObjectKey = $data->startObjectKey;
		$options->endObjectKey = $data->endObjectKey;
		$options->jobCreatedAt = $job->createdAt;
		$options->isFirstLoop = true;
		
		$recalculated = 0;
		try 
		{
			do
			{
				$results = $client->responseProfile->recalculate($options);
				$recalculated += $results->recalculated;
				$options->startObjectKey = $results->lastObjectKey;
				$options->isFirstLoop = false;
			} while($results->lastObjectKey);
		}
		catch(VidiunException $e)
		{
			if($e->getCode() != self::RESPONSE_PROFILE_CACHE_ALREADY_RECALCULATED && $e->getCode() != self::RESPONSE_PROFILE_CACHE_RECALCULATE_RESTARTED)
				throw $e;
			
			VidiunLog::err($e);
		}
		
		return $recalculated;
	}
}
