<?php

/**
 * TODO - think of how's best to work with these classes - $attach_policy and stuff !
 * 
 * @package Core
 * @subpackage model
 */
abstract class vshowCustomData extends myBaseObject
{
	//const VSHOW_CUSTOM_DATA_FIELD = "custom_data";

	protected $m_vshow = NULL;

	// when this ctor is called - if the vshow is not NULL, initialize from it
	public function __construct( vshow $vshow = NULL , $attach_policy = NULL )
	{
		if ( $vshow != NULL )
		{
			$this->m_vshow = $vshow;
			$this->deserializeFromString( $this->getCustomData());
		}

	}

	protected function attachToVshow ( vshow $vshow , $attach_policy )
	{
		$this->m_vshow = $vshow;
		$this->deserializeFromString( $this->getCustomData());
		
	}


	protected function updateVshow ()
	{
		$this->setCustomeData ( $this->serializeToString() );
	}

	private  function getCustomData ()
	{
		return $this->m_vshow->getCustomData();
	}

	private function setCustomData ( $value )
	{
		return $this->m_vshow->setCustomData( $value );
	}

}
?>