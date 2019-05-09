<?php
/**
 * @package plugins.contentDistribution
 * @subpackage api.objects
 */
class VidiunGenericDistributionJobProviderData extends VidiunDistributionJobProviderData
{
	private static $actionAttributes = array(
		VidiunDistributionAction::SUBMIT => 'submitAction',
		VidiunDistributionAction::UPDATE => 'updateAction',
		VidiunDistributionAction::DELETE => 'deleteAction',
		VidiunDistributionAction::FETCH_REPORT => 'fetchReportAction',
	);
	
	/**
	 * @var string
	 */
	public $xml;
	
	/**
	 * @var string
	 */
	public $resultParseData;
	
	/**
	 * @var VidiunGenericDistributionProviderParser
	 */
	public $resultParserType;
	
	public function __construct(VidiunDistributionJobData $distributionJobData = null)
	{
		if(!$distributionJobData)
			return;
			
		$action = VidiunDistributionAction::SUBMIT;
		if($distributionJobData instanceof VidiunDistributionDeleteJobData)
			$action = VidiunDistributionAction::DELETE;
		if($distributionJobData instanceof VidiunDistributionUpdateJobData)
			$action = VidiunDistributionAction::UPDATE;
		if($distributionJobData instanceof VidiunDistributionFetchReportJobData)
			$action = VidiunDistributionAction::FETCH_REPORT;
			
		if(!($distributionJobData->distributionProfile instanceof VidiunGenericDistributionProfile))
		{
			VidiunLog::err("Distribution profile is not generic");
			return;
		}
		
		$this->loadProperties($distributionJobData, $distributionJobData->distributionProfile, $action);
	}
	
	public function loadProperties(VidiunDistributionJobData $distributionJobData, VidiunGenericDistributionProfile $distributionProfile, $action)
	{
		$actionName = self::$actionAttributes[$action];
		
		$genericProviderAction = GenericDistributionProviderActionPeer::retrieveByProviderAndAction($distributionProfile->genericProviderId, $action);
		if(!$genericProviderAction)
		{
			VidiunLog::err("Generic provider [{$distributionProfile->genericProviderId}] action [$actionName] not found");
			return;
		}
		
		if(!$distributionJobData->entryDistribution)
		{
			VidiunLog::err("Entry Distribution object not provided");
			return;
		}
		
		if(!$distributionProfile->$actionName->protocol)
			$distributionProfile->$actionName->protocol = $genericProviderAction->getProtocol();
		if(!$distributionProfile->$actionName->serverUrl)
			$distributionProfile->$actionName->serverUrl = $genericProviderAction->getServerAddress();
		if(!$distributionProfile->$actionName->serverPath)
			$distributionProfile->$actionName->serverPath = $genericProviderAction->getRemotePath();
		if(!$distributionProfile->$actionName->username)
			$distributionProfile->$actionName->username = $genericProviderAction->getRemoteUsername();
		if(!$distributionProfile->$actionName->password)
			$distributionProfile->$actionName->password = $genericProviderAction->getRemotePassword();
		if(!$distributionProfile->$actionName->ftpPassiveMode)
			$distributionProfile->$actionName->ftpPassiveMode = $genericProviderAction->getFtpPassiveMode();
		if(!$distributionProfile->$actionName->httpFieldName)
			$distributionProfile->$actionName->httpFieldName = $genericProviderAction->getHttpFieldName();
		if(!$distributionProfile->$actionName->httpFileName)
			$distributionProfile->$actionName->httpFileName = $genericProviderAction->getHttpFileName();
	
		$entry = entryPeer::retrieveByPKNoFilter($distributionJobData->entryDistribution->entryId);
		if(!$entry)
		{
			VidiunLog::err("Entry [" . $distributionJobData->entryDistribution->entryId . "] not found");
			return;
		}
			
		$mrss = vMrssManager::getEntryMrss($entry);
		if(!$mrss)
		{
			VidiunLog::err("MRSS not returned for entry [" . $entry->getId() . "]");
			return;
		}
			
		$xml = new VDOMDocument();
		if(!$xml->loadXML($mrss))
		{
			VidiunLog::err("MRSS not is not valid XML:\n$mrss\n");
			return;
		}
		
		$key = $genericProviderAction->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_MRSS_TRANSFORMER);
		if(vFileSyncUtils::fileSync_exists($key))
		{
			$xslPath = vFileSyncUtils::getLocalFilePathForKey($key);
			if($xslPath)
			{
				$xsl = new VDOMDocument();
				$xsl->load($xslPath);
			
				// set variables in the xsl
				$varNodes = $xsl->getElementsByTagName('variable');
				foreach($varNodes as $varNode)
				{
					$nameAttr = $varNode->attributes->getNamedItem('name');
					if(!$nameAttr)
						continue;
						
					$name = $nameAttr->value;
					if($name && $distributionJobData->$name)
					{
						$varNode->textContent = $distributionJobData->$name;
						$varNode->appendChild($xsl->createTextNode($distributionJobData->$name));
					}
				}
				
				$proc = new XSLTProcessor;
				$proc->registerPHPFunctions(vXml::getXslEnabledPhpFunctions());
				$proc->importStyleSheet($xsl);
				
				$xml = $proc->transformToDoc($xml);
				if(!$xml)
				{
					VidiunLog::err("Transform returned false");
					return;
				}
			}
		}
	
		$key = $genericProviderAction->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_MRSS_VALIDATOR);
		
		list ($fileSync , $local) = vFileSyncUtils::getReadyFileSyncForKey( $key , true , false  );
		if($fileSync)
		{
			/* @var $fileSync FileSync */
			$xsdPath = $fileSync->getFullPath();
			if($xsdPath && !$xml->schemaValidate($xsdPath, $fileSync->getEncryptionKey(), $fileSync->getIv()))
			{
				VidiunLog::err("Inavlid XML:\n" . $xml->saveXML());
				VidiunLog::err("Schema [$xsdPath]:\n" . $fileSync->decrypt());	
				return;
			}
		}
		
		$this->xml = $xml->saveXML();
		
		$key = $genericProviderAction->getSyncKey(GenericDistributionProviderAction::FILE_SYNC_DISTRIBUTION_PROVIDER_ACTION_RESULTS_TRANSFORMER);
		if(vFileSyncUtils::fileSync_exists($key))
			$this->resultParseData = vFileSyncUtils::file_get_contents($key, true, false);
			
		$this->resultParserType = $genericProviderAction->getResultsParser();
	}
		
	private static $map_between_objects = array
	(
		"xml" ,
		"resultParseData" ,
		"resultParserType" ,
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
