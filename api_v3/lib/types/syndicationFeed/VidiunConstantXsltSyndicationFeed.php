<?php
/**
 * @abstract
 * @package api
 * @subpackage objects
 */
abstract class VidiunConstantXsltSyndicationFeed extends VidiunGenericXsltSyndicationFeed
{
	protected $xsltPath;

	public function doFromObject($source_object, VidiunDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);

		if($this->shouldGet('xslt', $responseProfile))
		{
			$real_path = realpath( $this->xsltPath );
			if ( file_exists ( $real_path ) )
			{
				$startTime = microtime(true);
				$contents = file_get_contents( $real_path);
				VidiunLog::info("Roku xslt file was found [$real_path] fgc took [".(microtime(true) - $startTime)."]");
				$this->xslt = $contents;
			}
			else
			{
				VidiunLog::info("Roku xslt file was not found [$this->xsltPath]");
				throw new VidiunAPIException(VidiunErrors::FILE_NOT_FOUND);
			}
		}
	}
	
	/**
	 * @param SyndicationDistributionProfile $object_to_fill
	 * @param array $props_to_skip
	 * @return genericSyndicationFeed
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$this->xslt = null;
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/**
	 * @param SyndicationDistributionProfile $object_to_fill
	 * @param array $props_to_skip
	 * @return genericSyndicationFeed
	 */
	public function toUpdatableObject ( $object_to_fill , $props_to_skip = array() )
	{
		$this->xslt = null;
		return parent::toUpdatableObject($object_to_fill, $props_to_skip );
	}

	public function getPropertiesToValidate()
	{
		$propsToValidate = parent::getPropertiesToValidate();
		unset($propsToValidate['xslt']);
		return $propsToValidate;
	}


}