<?php
	const LIMIT = 500;
	const INITIAL_CREATED_AT_VALUE = '2000-01-01 00:00:00';

	require_once(__dir__ . "/../../../alpha/scripts/bootstrap.php");
	$c = new Criteria();
	$c->addAscendingOrderByColumn(vvotePeer::CREATED_AT);
	$c->setLimit(LIMIT);
	
	$createdAtValue = INITIAL_CREATED_AT_VALUE;
	$vVotes = array(1);
	while(!empty($vVotes))
	{
		$c->add(vvotePeer::CREATED_AT, $createdAtValue, Criteria::GREATER_THAN);
		vvotePeer::setUseCriteriaFilter(false);
		$vVotes = vvotePeer::doSelect($c);
		vvotePeer::setUseCriteriaFilter(true);

		foreach($vVotes as $vVote)
		{
			$vuserId = $vVote->getVuserId();
			vuserPeer::setUseCriteriaFilter(false);
			$vuser = vuserPeer::retrieveByPKNoFilter($vuserId);
			vuserPeer::setUseCriteriaFilter(true);

			if(!$vuser)
			{
				VidiunLog::err("no user found with id $vuserId");
				continue;
			}
			$puserId = $vuser->getPuserId();
			$vVote->setPuserId($puserId);
			$vVote->save();
		}
		VidiunLog::debug("created is - " . $vVote->getCreatedAt());
		$createdAtValue = $vVote->getCreatedAt();
	}
