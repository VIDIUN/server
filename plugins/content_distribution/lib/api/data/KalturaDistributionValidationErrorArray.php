<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunDistributionValidationErrorArray extends VidiunTypedArray
{
	public static function fromDbArray(array $arr, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new VidiunDistributionValidationErrorArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
			$nObj = null;
			switch($obj->getErrorType())
			{
				case DistributionErrorType::MISSING_FLAVOR:
    				$nObj = new VidiunDistributionValidationErrorMissingFlavor();
    				break;
    			
				case DistributionErrorType::MISSING_THUMBNAIL:
    				$nObj = new VidiunDistributionValidationErrorMissingThumbnail();
    				break;
    			
				case DistributionErrorType::MISSING_METADATA:
    				$nObj = new VidiunDistributionValidationErrorMissingMetadata();
    				break;

				case DistributionErrorType::MISSING_ASSET:
					$nObj = new VidiunDistributionValidationErrorMissingAsset();
					break;
    			
				case DistributionErrorType::INVALID_DATA:
					if($obj->getMetadataProfileId())
    					$nObj = new VidiunDistributionValidationErrorInvalidMetadata();
    				else
    					$nObj = new VidiunDistributionValidationErrorInvalidData();
    				break;

    				case DistributionErrorType::CONDITION_NOT_MET:
    					$nObj = new VidiunDistributionValidationErrorConditionNotMet();
    					break;

				default:
					break;
			}
			
			if(!$nObj)
				continue;
				
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("VidiunDistributionValidationError");	
	}
}