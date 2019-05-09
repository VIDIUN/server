<?php
/**
 * @package plugins.pushNotification
* @subpackage admin
*/
class Form_PushNotificationTemplateConfiguration extends Form_EventNotificationTemplateConfiguration
{
	public function populateFromObject($object, $add_underscore = true)
	{
		if(!($object instanceof Vidiun_Client_PushNotification_Type_PushNotificationTemplate))
			return;
		
		if($object->queueNameParameters && count($object->queueNameParameters))
		{
			$queueNameParameters = array();
			foreach($object->queueNameParameters as $index => $parameter)
				$queueNameParameters[] = $this->getParameterDescription($parameter);
		
			$queueNameParametersList = new Infra_Form_HtmlList('queueNameParameters', array(
					'legend'		=> 'queue Name Parameters',
					'list'			=> $queueNameParameters,
			));
			$this->addElements(array($queueNameParametersList));
		}

		if($object->queueKeyParameters && count($object->queueKeyParameters))
		{
			$queueKeyParameters = array();
			foreach($object->queueKeyParameters as $index => $parameter)
				$queueKeyParameters[] = $this->getParameterDescription($parameter);
		
			$queueKeyParametersList = new Infra_Form_HtmlList('queueKeyParameters', array(
					'legend'		=> 'queue Key Parameters',
					'list'			=> $queueKeyParameters,
			));
			$this->addElements(array($queueKeyParametersList));
		}
		
		parent::populateFromObject($object, $add_underscore);
	}
	
    protected function addTypeElements(Vidiun_Client_EventNotification_Type_EventNotificationTemplate $eventNotificationTemplate)
    {
        $element = new Infra_Form_Html('http_title', array(
            'content' => '<b>Notification Handler Service  Details</b>',
        ));
        $this->addElements(array($element));
        
        $this->addElement('select', 'api_object_type', array(
            'label'			=> 'Object Type (VidiunObject):',
 			'default'       => $eventNotificationTemplate->apiObjectType,
            'filters'		=> array('StringTrim'),
            'required'		=> true,            
            'multiOptions' 	=> array(
                'VidiunBaseEntry' => 'Base Entry',
                'VidiunDataEntry' => 'Data Entry',
                'VidiunDocumentEntry' => 'Document Entry',
                'VidiunMediaEntry' => 'Media Entry',
                'VidiunExternalMediaEntry' => 'External Media Entry',
                'VidiunLiveStreamEntry' => 'Live Stream Entry',
                'VidiunPlaylist' => 'Playlist',
                'VidiunCategory' => 'Category',
                'VidiunUser' => 'User',
                'VidiunCuePoint' => 'CuePoint',
                'VidiunAdCuePoint' => 'Ad Cue-Point',
                'VidiunAnnotation' => 'Annotation',
                'VidiunCodeCuePoint' => 'Code Cue-Point',
				'VidiunThumbCuePoint' => 'Thumb Cue-Point',
                'VidiunDistributionProfile' => 'Distribution Profile',
                'VidiunEntryDistribution' => 'Entry Distribution',
                'VidiunMetadata' => 'Metadata',
                'VidiunAsset' => 'Asset',
                'VidiunFlavorAsset' => 'Flavor Asset',
                'VidiunThumbAsset' => 'Thumbnail Asset',
                'VidiunAccessControlProfile' => 'Access Control',
                'VidiunBatchJob' => 'BatchJob',
                'VidiunBulkUploadResultEntry' => 'Bulk-Upload Entry Result',
                'VidiunBulkUploadResultCategory' => 'Bulk-Upload Category Result',
                'VidiunBulkUploadResultUser' => 'Bulk-Upload User Result',
                'VidiunBulkUploadResultCategoryUser' => 'Bulk-Upload Category - User Result',
                'VidiunCategoryUser' => 'Category - User',
                'VidiunConversionProfile' => 'Conversion Profile',
                'VidiunFlavorParams' => 'Flavor Params',
                'VidiunConversionProfileAssetParams' => 'Asset Params - Conversion Profile',
                'VidiunFlavorParamsOutput' => 'Flavor Params Output',
                'VidiunGenericsynDicationFeed' => 'Genericsyn Dication Feed',
                'VidiunPartner' => 'Partner',
                'VidiunPermission' => 'Permission',
                'VidiunPermissionItem' => 'Permission Item',
                'VidiunScheduler' => 'Scheduler',
                'VidiunSchedulerConfig' => 'Scheduler Config',
                'VidiunSchedulerStatus' => 'Scheduler Status',
                'VidiunSchedulerWorker' => 'Scheduler Worker',
                'VidiunStorageProfile' => 'Storage Profile',
                'VidiunThumbParams' => 'Thumbnail Params',
                'VidiunThumbParamsOutput' => 'Thumbnail Params Output',
                'VidiunUploadToken' => 'Upload Token',
                'VidiunUserLoginData' => 'User Login Data',
                'VidiunUserRole' => 'User Role',
                'VidiunWidget' => 'Widget',
                'VidiunCategoryEntry' => 'Category - Entry',
                'VidiunLiveStreamScheduleEvent' => 'Schedule Live-Stream Event',
                'VidiunRecordScheduleEvent' => 'Schedule Recorded Event',
                'VidiunLocationScheduleResource' => 'Schedule Location Resource',
                'VidiunLiveEntryScheduleResource' => 'Schedule Live-Entry Resource',
                'VidiunCameraScheduleResource' => 'Schedule Camera Resource',
                'VidiunScheduleEventResource' => 'Schedule Event-Resource',
                'VidiunClippingTaskEntryServerNode' => 'Clipping Task Entry-Server-Node',
            ),
        ));
    
        $this->addElement('select', 'object_format', array(
            'label'			=> 'Format:',
            'filters'		=> array('StringTrim'),
            'required'		=> true,
            'multiOptions' 	=> array(
                Vidiun_Client_Enum_ResponseType::RESPONSE_TYPE_JSON => 'JSON',
                Vidiun_Client_Enum_ResponseType::RESPONSE_TYPE_XML => 'XML',
                Vidiun_Client_Enum_ResponseType::RESPONSE_TYPE_PHP => 'PHP',
            ),
        ));

        $responseProfile = new Vidiun_Form_Element_ObjectSelect('response_profile_id', array(
        	'label' => 'Response Profile:',
        	'nameAttribute' => 'name',
        	'service' => 'responseProfile',
        	'pageSize' => 500,
        	'impersonate' => $eventNotificationTemplate->partnerId,
			'addNull' => true,
        ));
        $this->addElements(array($responseProfile));
    }    
}