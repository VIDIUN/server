<?php

class vObjectReadyForIndexInheritedTreeHandler implements vObjectReadyForIndexInheritedTreeEventConsumer
{
	
	/* (non-PHPdoc)
	 * @see vObjectReadyForIndexInheritedTreeEventConsumer::shouldConsumeReadyForIndexInheritedTreeEvent()
	 */
	public function shouldConsumeReadyForIndexInheritedTreeEvent(BaseObject $object)
	{
		if ($object instanceof category)
		{
			return true;
		}

		return false;
	}

	/* (non-PHPdoc)
	 * @see vObjectReadyForIndexInheritedTreeEventConsumer::objectReadyForIndexInheritedTreeEvent()
	 */
	public function objectReadyForIndexInheritedTreeEvent(BaseObject $object, $partnerCriteriaParams, BatchJob $raisedJob = null)
	{
		if ( $object instanceof category )
		{
			myPartnerUtils::reApplyPartnerFilters($partnerCriteriaParams);
			$object->addIndexCategoryInheritedTreeJob();
			$object->indexToSearchIndex();
		}

		return true;
	}

}