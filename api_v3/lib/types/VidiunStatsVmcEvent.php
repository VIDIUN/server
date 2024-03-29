<?php
/**
 * Will hold data from the Vidiun UI components to be passed on to the reports and analytics system
 * @package api
 * @subpackage objects
 */
class VidiunStatsVmcEvent extends VidiunObject 
{
/*
 * Bellow the definition of the event log line. The VidiunStatsEvent structure will strongly resemle this line but might differenciate slightly,
 * due to data that will come from other resources such as the suser's IP (coming from the HTTP header)
 * 
client version - will help interprete the line structure. different client versions might have slightly different data/data formats in the line
vmc_event_id - number is the row number in yuval's excel
datetime - same format as MySql's datetime - can change and should reflect the time zone
session id - can be some big random number or guid
partner id
entry id
unique viewer
widget id
ui_conf id
uid - the puser id as set by the partner
duration - milliseconds
user ip
process duration - in milliseconds
 */
	
	/**
	 * @var string
	 */
	public $clientVer;

	/**
	 * @var string
	 */
	public $vmcEventActionPath;
	
	
	/**
	 * @var VidiunStatsVmcEventType
	 */
	public $vmcEventType;
	
	/**
	 * the client's timestamp of this event
	 *  
	 * @var float
	 */
	public $eventTimestamp;

	/**
	 * a unique string generated by the client that will represent the client-side session: the primary component will pass it on to other components that sprout from it
	 * @var string
	 */
	public $sessionId;	
	
	/**
	 * @var int
	 */
	public $partnerId;
	
	/**
	 * @var string
	 */
	public $entryId;

	/**
	 * @var string
	 */
	public $widgetId;
	
	/**
	 * @var int
	 */
	public $uiconfId;	
	
	/**
	 * the partner's user id 
	 * @var string
	 */
	public $userId;

	/**
	 * will be retrieved from the request of the user 
	 * @var string
	 * @readonly
	 */
	public $userIp;	
		
}