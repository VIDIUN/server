<?php
/**
 * Annotation service - Video Annotation
 *
 * @service annotation
 * @package plugins.annotation
 * @subpackage api.services
 * @throws VidiunErrors::SERVICE_FORBIDDEN
 * @deprecated use cuePoint service instead
 */
class AnnotationService extends CuePointService
{
	/**
	 * @return CuePointType or null to limit the service type
	 */
	protected function getCuePointType()
	{
		return AnnotationPlugin::getCuePointTypeCoreValue(AnnotationCuePointType::ANNOTATION);
	}

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);

		if(!AnnotationPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, AnnotationPlugin::PLUGIN_NAME);
	}

	/**
	 * Allows you to add an annotation object associated with an entry
	 *
	 * @action add
	 * @param VidiunAnnotation $annotation
	 * @return VidiunAnnotation
	 */
	function addAction(VidiunCuePoint $annotation)
	{
		return parent::addAction($annotation);
	}

	/**
	 * Clone cuePoint with id to given entry
	 *
	 * @action clone
	 * @param string $id
	 * @param string $entryId
	 * @param string $parentId
	 * @return VidiunAnnotation
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 * @throws VidiunErrors::ENTRY_ID_NOT_FOUND
	 */
	function cloneAction($id, $entryId, $parentId = null)
	{
		$dbAnnotation = parent::doClone($id, $entryId);
		if ( !$dbAnnotation instanceof annotation)
		{
			throw new VidiunAPIException(VidiunErrors::INVALID_OBJECT_TYPE, get_class($dbAnnotation));
		}
		if ($parentId)
		{
			$dbAnnotation->setParentId($parentId);
		}
		$dbAnnotation->save();
		return VidiunAnnotation::getInstance($dbAnnotation, $this->getResponseProfile());
	}

	/**
	 * Update annotation by id
	 *
	 * @action update
	 * @param string $id
	 * @param VidiunAnnotation $annotation
	 * @return VidiunAnnotation
	 * @throws VidiunCuePointErrors::INVALID_CUE_POINT_ID
	 */
	function updateAction($id, VidiunCuePoint $annotation)
	{
		return parent::updateAction($id, $annotation);
	}
	
	/**
	* List annotation objects by filter and pager
	*
	* @action list
	* @param VidiunAnnotationFilter $filter
	* @param VidiunFilterPager $pager
	* @return VidiunAnnotationListResponse
	*/
	function listAction(VidiunCuePointFilter $filter = null, VidiunFilterPager $pager = null)
	{
		if(!$filter)
			$filter = new VidiunAnnotationFilter();
		
		$filter->cuePointTypeEqual = AnnotationPlugin::getApiValue(AnnotationCuePointType::ANNOTATION);
		
		$list = parent::listAction($filter, $pager);
		$ret = new VidiunAnnotationListResponse();
		$ret->objects = $list->objects;
		$ret->totalCount = $list->totalCount;
		
		return $ret;
	}
}
