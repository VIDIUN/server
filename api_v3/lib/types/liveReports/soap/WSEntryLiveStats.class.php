<?php


class WSEntryLiveStats extends WSLiveStats
{				
	function getVidiunObject() {
		return new VidiunEntryLiveStats();
	}
	
	/**
	 * @var string
	 **/
	public $entryId;
	
	/**
	 * @var long
	 */
	public $peakAudience;

	/**
	 * @var long
	 */
	public $peakDvrAudience;
}


