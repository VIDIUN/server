<?php
/**
 * @package Core
 * @subpackage model.interfaces
 */ 
interface IOwnable extends IBaseObject
{
	/**
	 * @return string
	 */
	public function getPuserId();
	
	/**
	 * @return int
	 */
	public function getVuserId();

	/**
	 * @return boolean
	 */
	public function isEntitledVuserEdit( $vuserId );
	
	/**
	 * @return boolean
	 */
	public function isOwnerActionsAllowed( $vuserId );
}