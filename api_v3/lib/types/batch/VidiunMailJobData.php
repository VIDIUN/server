<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunMailJobData extends VidiunJobData
{
	/**
	 * @var VidiunMailType
	 */
	public $mailType;

	/**
	 * @var int
	 */
    public $mailPriority;

    /**
	 * @var VidiunMailJobStatus
	 */
    public $status ;
    
	/**
	 * @var string
	 */
	public $recipientName;  

	/**
	 * @var string
	 */	
   	public $recipientEmail;
   	
	/**
	 * vuserId  
	 * @var int
	 */   	
    public $recipientId;
    
	/**
	 * @var string
	 */    
    public $fromName;
    
	/**
	 * @var string
	 */    
    public $fromEmail;
  
	/**
	 * @var string
	 */    
    public $bodyParams;

	/**
	 * @var string
	 */    
    public $subjectParams;  

	/**
 	* @var string
 	*/
    public $templatePath;

	/**
 	* @var VidiunLanguageCode
 	*/
    public $language;

	/**
 	* @var int
 	*/
    public $campaignId;

	/**
 	* @var int
 	*/
    public $minSendDate;
    
    /**
     * @var bool
     */
    public $isHtml=true;
	
	/**
     * @var string
     */
    public $separator = '|';
    
	private static $map_between_objects = array
	(
		"mailType" ,
	    "mailPriority" ,
	    "status " ,
		"recipientName" ,  
	   	"recipientEmail" ,
	    "recipientId" ,
	    "fromName" ,
	    "fromEmail" ,
	    "bodyParams" ,
	    "subjectParams" ,  
	    "templatePath" ,
	    "language" ,
	    "campaignId" ,
	    "minSendDate" ,
		"isHtml" ,
		"separator",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new vMailJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}

?>