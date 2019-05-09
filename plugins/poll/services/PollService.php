<?php

/**
 * Poll service
 *
 * The poll service works against the cache entirely no DB instance should be used here
 *
 * @service poll
 * @package plugins.poll
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 */
class PollService extends VidiunBaseService
{

	/**
	 * Add Action
	 * @action add
	 * @param string $pollType
	 * @return string
	 * @throws VidiunAPIException
	 */
	public function addAction($pollType = 'SINGLE_ANONYMOUS')
	{
		VidiunResponseCacher::disableCache();
		try
		{
			$pollActions = new PollActions();
			return $pollActions->generatePollId($pollType);
		}
		catch (Exception $e)
		{
			throw new VidiunAPIException($e->getMessage());
		}
	}

	/**
	 * Get Votes Action
	 * @action getVotes
	 * @param string $pollId
	 * @param string $answerIds
	 * @return string
	 * @throws VidiunAPIException
	 */
	public function getVotesAction($pollId, $answerIds)
	{
		$otherDcVotesKey='otherDCVotes';
		VidiunResponseCacher::disableCache();
		try
		{
			$pollActions = new PollActions();
			$localDcVotes = $pollActions->getVotes($pollId, $answerIds);
		}
		catch (Exception $e)
		{
			throw new VidiunAPIException($e->getMessage());
		}

		if(!vFileUtils::isAlreadyInDumpApi())
		{
			$remoteDCIds = vDataCenterMgr::getAllDcs();
			if($remoteDCIds && count($remoteDCIds) > 0)
			{
				$remoteDCHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId(1 - vDataCenterMgr::getCurrentDcId());
				if ($remoteDCHost)
				{
					$_POST[$otherDcVotesKey] = json_encode($localDcVotes);
					return vFileUtils::dumpApiRequest($remoteDCHost, true);
				}
			}
		}
		else
		{
			if(isset($_POST[$otherDcVotesKey]))
			{
				$prevData = json_decode($_POST[$otherDcVotesKey]);
				try
				{
					$localDcVotes->merge($prevData);
				} catch (Exception $e)
				{
					throw new VidiunAPIException($e->getMessage());
				}
			}
		}
		return json_encode($localDcVotes);
	}

	/**
	 * Get resetVotes Action
	 * @action resetVotes
	 * @param string $pollId
	 * @throws VidiunAPIException
	 */
	public function resetVotesAction($pollId)
	{

		VidiunResponseCacher::disableCache();
		try
		{
			$pollActions = new PollActions();
			$newVersion = $pollActions->resetVotes($pollId);
			VidiunLog::debug("New cache version - {$newVersion} to PollId - {$pollId}");
		}
		catch (Exception $e)
		{
			throw new VidiunAPIException($e->getMessage());
		}

		if(!vFileUtils::isAlreadyInDumpApi())
		{
			$remoteDCIds = vDataCenterMgr::getAllDcs();
			if ($remoteDCIds && count($remoteDCIds) > 0)
			{
				$remoteDCHost = vDataCenterMgr::getRemoteDcExternalUrlByDcId(1 - vDataCenterMgr::getCurrentDcId());
				if ($remoteDCHost)
					return vFileUtils::dumpApiRequest($remoteDCHost, true);
			}
		}
	}


	/**
	 * Vote Action
	 * @action vote
	 * @param string $pollId
	 * @param string $userId
	 * @param string $answerIds
	 * @return string
	 * @throws VidiunAPIException
	 */
	public function voteAction($pollId, $userId, $answerIds)
	{
		VidiunResponseCacher::disableCache();
		try
		{
			$pollActions = new PollActions();
			$vsUserId = vCurrentContext::$uid;
			$pollActions->setVote($pollId, $userId,$vsUserId ,$answerIds);
		}
		catch (Exception $e)
		{
			throw new VidiunAPIException($e->getMessage());
		}
	}

	/**
	 * Vote Action
	 * @action getVote
	 * @param string $pollId
	 * @param string $userId
	 * @return string
	 */
	public function getVoteAction($pollId, $userId)
	{
		VidiunResponseCacher::disableCache();
		$vsUserId = vCurrentContext::$uid;
		$pollActions = new PollActions();
		return $pollActions->doGetVote($pollId, $userId, $vsUserId);
	}

	/**
	 * Should return true or false for allowing/disallowing vidiun network filter for the given action.
	 * Can be extended to partner specific checks etc...
	 * @return true if "vidiun network" is enabled for the given action or false otherwise
	 * @param string $actionName action name
	 */
	protected function vidiunNetworkAllowed($actionName)
	{
		return false;
	}

}
