<?php
/**
 * Subclass for representing a row from the 'vvote' table.
 *
 *
 *
 * @package Core
 * @subpackage model
 */
class vvote extends Basevvote implements IBaseObject
{
	private $statistics_results = null;
	
	public function save(PropelPDO $con = null)
	{
		if ( $this->isNew() )
		{
			$this->statistics_results = myStatisticsMgr::addVvote($this);
		}
		else if (in_array(vvotePeer::STATUS, $this->modifiedColumns))
		{
		   $this->statistics_results = myStatisticsMgr::modifyEntryVotesByvVote($this); 
		}
		
		return parent::save( $con );
	}
	
	public function getFormattedCreatedAt( $format = dateUtils::VIDIUN_FORMAT )
	{
		return dateUtils::formatVidiunDate( $this , 'getCreatedAt' , $format );
	}

	public function getFormattedUpdatedAt( $format = dateUtils::VIDIUN_FORMAT )
	{
		return dateUtils::formatVidiunDate( $this , 'getUpdatedAt' , $format );
	}
	
	
	public function getStatisticsResults ()
	{
		return $this->statistics_results;
	}
	
}
