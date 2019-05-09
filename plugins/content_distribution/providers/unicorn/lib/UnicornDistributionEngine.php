<?php
/**
 * @package plugins.unicornDistribution
 * @subpackage lib
 */
class UnicornDistributionEngine extends DistributionEngine implements IDistributionEngineUpdate, IDistributionEngineSubmit, IDistributionEngineDelete, IDistributionEngineCloseSubmit, IDistributionEngineCloseUpdate, IDistributionEngineCloseDelete
{
	const FAR_FUTURE = 933120000; // 60s * 60m * 24h * 30d * 12m * 30y
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUnicornDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUnicornDistributionProfile");
		
		if(!$data->providerData || !($data->providerData instanceof VidiunUnicornDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUnicornDistributionJobProviderData");
		
		$this->handleSubmit($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUnicornDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUnicornDistributionProfile");
		
		if(!$data->providerData || !($data->providerData instanceof VidiunUnicornDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUnicornDistributionJobProviderData");
		
		return $this->handleSubmit($data, $data->distributionProfile, $data->providerData);
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineDelete::delete()
	 */
	public function delete(VidiunDistributionDeleteJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunUnicornDistributionProfile))
			VidiunLog::err("Distribution profile must be of type VidiunUnicornDistributionProfile");
		
		if(!$data->providerData || !($data->providerData instanceof VidiunUnicornDistributionJobProviderData))
			VidiunLog::err("Provider data must be of type VidiunUnicornDistributionJobProviderData");
		
		$this->handleDelete($data, $data->distributionProfile, $data->providerData);
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		// will be closed by the callback notification
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		// will be closed by the callback notification
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseDelete::closeDelete()
	 */
	public function closeDelete(VidiunDistributionDeleteJobData $data)
	{
		// will be closed by the callback notification
		return false;
	}
	
	protected function getNotificationUrl(VidiunUnicornDistributionJobProviderData $providerData)
	{
		$job = VJobHandlerWorker::getCurrentJob();
		$serviceUrl = trim($providerData->notificationBaseUrl, '/');
		return "$serviceUrl/api_v3/index.php/service/unicornDistribution_unicorn/action/notify/partnerId/{$job->partnerId}/id/{$job->id}";
	}
	
	/**
	 * @param int $partnerId
	 * @param string $entryId
	 * @param string $assetIds comma seperated
	 * @return array<VidiunCaptionAsset>
	 */
	protected function getCaptionAssets($partnerId, $entryId, $assetIds)
	{
		VBatchBase::impersonate($partnerId);
		$filter = new VidiunCaptionAssetFilter();
		$filter->entryIdEqual = $entryId;
		$filter->idIn = $assetIds;
		
		$captionPlugin = VidiunCaptionClientPlugin::get(VBatchBase::$vClient);
		$assetsList = $captionPlugin->captionAsset->listAction($filter);
		VBatchBase::unimpersonate();
		
		return $assetsList->objects;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUnicornDistributionProfile $distributionProfile
	 * @param VidiunUnicornDistributionJobProviderData $providerData
	 * @return string
	 */
	protected function buildXml(VidiunDistributionJobData $data, VidiunUnicornDistributionProfile $distributionProfile, VidiunUnicornDistributionJobProviderData $providerData)
	{
		$entryDistribution = $data->entryDistribution;
		/* @var $entryDistribution VidiunEntryDistribution */
		
		$flavorAssetIds = explode(',', $entryDistribution->flavorAssetIds);
		$flavorAssetId = reset($flavorAssetIds);
		$downloadURL = $this->getAssetUrl($flavorAssetId);
		
		$xml = new SimpleXMLElement('<APIIngestRequest/>');
		$xml->addChild('UserName', $distributionProfile->username);
		$xml->addChild('Password', $distributionProfile->password);
		$xml->addChild('DomainName', $distributionProfile->domainName);
		
		$avItemXml = $xml->addChild('AVItem');
		$avItemXml->addChild('CatalogGUID', $providerData->catalogGuid);
		$avItemXml->addChild('ForeignKey', $entryDistribution->entryId);
		$avItemXml->addChild('IngestItemType', 'Video');
		
		$ingestInfoXml = $avItemXml->addChild('IngestInfo');
		$ingestInfoXml->addChild('DownloadURL', $downloadURL);
		
		$avItemXml->addChild('Title', $providerData->title);
		
		if($entryDistribution->assetIds)
		{
			$captionsXml = $avItemXml->addChild('Captions');
			
			$captions = $this->getCaptionAssets($entryDistribution->partnerId, $entryDistribution->entryId, $entryDistribution->assetIds);
			foreach($captions as $caption)
			{
				/* @var $caption VidiunCaptionAsset */
				$captionXml = $captionsXml->addChild('Caption');
				$captionXml->addChild('ForeignKey', $caption->id);
				
				$ingestInfoXml = $captionXml->addChild('IngestInfo');
				$ingestInfoXml->addChild('DownloadURL', $this->getAssetUrl($caption->id));
				
				$captionXml->addChild('Language', $caption->languageCode);
			}
		}
		
		$publicationRulesXml = $avItemXml->addChild('PublicationRules');
		$publicationRuleXml = $publicationRulesXml->addChild('PublicationRule');
		
		$format = 'Y-m-d\TH:i:s\Z'; // e.g. 2007-03-01T13:00:00Z
		$publicationRuleXml->addChild('ChannelGUID', $distributionProfile->channelGuid);
		$publicationRuleXml->addChild('StartDate', date($format, $data->entryDistribution->sunrise));
		
		if($data instanceof VidiunDistributionDeleteJobData)
		{
			$publicationRuleXml->addChild('EndDate', date($format, time()));
		}
		elseif($data->entryDistribution->sunset)
		{
			$publicationRuleXml->addChild('EndDate', date($format, $data->entryDistribution->sunset));
		}
		else
		{
			$publicationRuleXml->addChild('EndDate', date($format, time() + self::FAR_FUTURE));
		}
		
		$xml->addChild('NotificationURL', $this->getNotificationUrl($providerData));
		$xml->addChild('NotificationRequestMethod', 'GET');
		
		return $xml->asXML();
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUnicornDistributionProfile $distributionProfile
	 * @param VidiunUnicornDistributionJobProviderData $providerData
	 */
	protected function handleSubmit(VidiunDistributionJobData $data, VidiunUnicornDistributionProfile $distributionProfile, VidiunUnicornDistributionJobProviderData $providerData)
	{
		$xml = $this->buildXml($data, $distributionProfile, $providerData);
		$data->sentData = $xml;
		$remoteId = $this->send($distributionProfile, $xml);
		if($remoteId)
		{
			VidiunLog::info("Remote ID [$remoteId]");
			$data->remoteId = $remoteId;
		}
		
		return !$providerData->mediaChanged;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUnicornDistributionProfile $distributionProfile
	 * @param VidiunUnicornDistributionJobProviderData $providerData
	 */
	protected function handleDelete(VidiunDistributionJobData $data, VidiunUnicornDistributionProfile $distributionProfile, VidiunUnicornDistributionJobProviderData $providerData)
	{
		$xml = $this->buildXml($data, $distributionProfile, $providerData);
		$data->sentData = $xml;
		$this->send($distributionProfile, $xml);
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunUnicornDistributionProfile $distributionProfile
	 * @param VidiunUnicornDistributionJobProviderData $providerData
	 */
	protected function send(VidiunUnicornDistributionProfile $distributionProfile, $xml)
	{
		$ch = curl_init($distributionProfile->apiHostUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml'));
		$response = curl_exec($ch);
		
		
		if(!$response)
		{
			$curlError = curl_error($ch);
			$curlErrorNumber = curl_errno($ch);
			curl_close($ch);
			throw new VidiunDispatcherException("HTTP request failed: $curlError", $curlErrorNumber);
		}
		curl_close($ch);
		VidiunLog::info("Response [$response]");
	
		$matches = null;
		if(preg_match_all('/HTTP\/?[\d.]{0,3} ([\d]{3}) ([^\n\r]+)/', $response, $matches))
		{
			foreach($matches[0] as $index => $match)
			{
				$code = intval($matches[1][$index]);
				$message = $matches[2][$index];
			
				if($code == VCurlHeaderResponse::HTTP_STATUS_CONTINUE)
				{
					continue;
				}
				
				if($code != VCurlHeaderResponse::HTTP_STATUS_OK)
				{
					throw new Exception("HTTP response code [$code] error: $message", $code);
				}
				
				if(preg_match('/^MediaItemGuid: (.+)$/', $message, $matches))
				{
					return $matches[1];
				}
				
				return null;
			}
		}

		throw new VidiunDistributionException("Unexpected HTTP response");
	}
}