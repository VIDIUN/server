<?php
/**
 * @package api
 * @subpackage objects
 */
class VidiunMixEntry extends VidiunPlayableEntry
{
	/**
	 * Indicates whether the user has submited a real thumbnail to the mix (Not the one that was generated automaticaly)
	 * 
	 * @var bool
	 * @readonly
	 */
	public $hasRealThumbnail;
	
	/**
	 * The editor type used to edit the metadata
	 * 
	 * @var VidiunEditorType
	 */
	public $editorType;

	/**
	 * The xml data of the mix
	 *
	 * @var string
	 */
	public $dataContent;
	
	public function __construct()
	{
		$this->type = VidiunEntryType::MIX;
	}
	
	private static $map_between_objects = array
	(
		"hasRealThumbnail" => "hasRealThumb",
		"editorType",
		"dataContent"
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
    public function doFromObject($entry, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($entry, $responseProfile);

		if($this->shouldGet('editorType', $responseProfile))
		{
			if ($entry->getEditorType() == "vidiunAdvancedEditor" || $entry->getEditorType() == "Veditor")
			    $this->editorType = VidiunEditorType::ADVANCED;
			else
			    $this->editorType = VidiunEditorType::SIMPLE;
		}
	}
	
	public function toObject($entry = null, $skip = array())
	{
		$entry = parent::toObject($entry, $skip);
		
		if ($this->editorType === VidiunEditorType::ADVANCED)
			$entry->setEditorType("vidiunAdvancedEditor");
		else
			$entry->setEditorType("vidiunSimpleEditor");
			
		return $entry;
	}
}
?>