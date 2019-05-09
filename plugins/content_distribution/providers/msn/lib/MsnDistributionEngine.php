<?php
/**
 * @package plugins.msnDistribution
 * @subpackage lib
 */
class MsnDistributionEngine extends DistributionEngine implements 
	IDistributionEngineUpdate,
	IDistributionEngineSubmit,
	IDistributionEngineCloseUpdate,
	IDistributionEngineCloseSubmit
{
	const TEMP_DIRECTORY = 'msn_distribution';
	const FEED_TEMPLATE = 'feed_template.xml';

	/* (non-PHPdoc)
	 * @see IDistributionEngineSubmit::submit()
	 */
	public function submit(VidiunDistributionSubmitJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunMsnDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunMsnDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunMsnDistributionJobProviderData))
			throw new Exception("Provider data must be of type VidiunMsnDistributionJobProviderData");
		
		$results = $this->handleSend($data, $data->distributionProfile, $data->providerData);
		$matches = null;
		if(preg_match('/<uuid[^>]*>([^<]+)<\/uuid>/', $results, $matches))
		{
			$data->remoteId = $matches[1];
		}
		else 
		{
			throw new Exception("No uuid returned from MSN");
		}
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseSubmit::closeSubmit()
	 */
	public function closeSubmit(VidiunDistributionSubmitJobData $data)
	{
		$errDescription = null;
		$publishState = $this->fetchStatus($data, $errDescription);
		VidiunLog::info("publishState [$publishState]");
		switch($publishState)
		{
			case 'Published':
				return true;
				
			case 'Pending':
				return false;
				
			case 'Error':
			case 'Update Error':
				
				if($errDescription)
					throw new Exception("MSN error: $errDescription");
					
				throw new Exception('Unknows MSN error');
				
			default:
				VidiunLog::err("Unknown publishState [$publishState]");
				return false;
		}
	}
	
	/* (non-PHPdoc)
	 * @see IDistributionEngineUpdate::update()
	 */
	public function update(VidiunDistributionUpdateJobData $data)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunMsnDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunMsnDistributionProfile");
	
		if(!$data->providerData || !($data->providerData instanceof VidiunMsnDistributionJobProviderData))
			throw new Exception("Provider data must be of type VidiunMsnDistributionJobProviderData");
		
		$results = $this->handleSend($data, $data->distributionProfile, $data->providerData);
		$matches = null;
		if(preg_match('/<uuid[^>]*>([^<]+)<\/uuid>/', $results, $matches))
		{
			$data->remoteId = $matches[1];
		}
		else 
		{
			throw new Exception("No uuid returned from MSN");
		}
		
		return false;
	}

	/* (non-PHPdoc)
	 * @see IDistributionEngineCloseUpdate::closeUpdate()
	 */
	public function closeUpdate(VidiunDistributionUpdateJobData $data)
	{
		$errDescription = null;
		$publishState = $this->fetchStatus($data, $errDescription);
		switch($publishState)
		{
			case 'Published':
				return true;
				
			case 'Pending':
				return false;
				
			case 'Error':
			case 'Update Error':
				if($errDescription)
					throw new Exception("MSN error: $errDescription");
					
				throw new Exception('Unknows MSN error');
				
			default:
				VidiunLog::err("Unknown publishState [$publishState]");
				return false;
		}
	}

	/**
	 * @param string $path
	 * @param VidiunDistributionJobData $data
	 * @param VidiunMsnDistributionProfile $distributionProfile
	 * @param VidiunMsnDistributionJobProviderData $providerData
	 * @throws Exception
	 */
	protected function handleSend(VidiunDistributionJobData $data, VidiunMsnDistributionProfile $distributionProfile, VidiunMsnDistributionJobProviderData $providerData)
	{
		$domain = $distributionProfile->domain;
		$username = $distributionProfile->username;
		$password = $distributionProfile->password;
		
		$url = "https://{$domain}/admin/services/storevideoandfiles.aspx";
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");

		$params = http_build_query(array('VideoXML' => $providerData->xml));
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		
		$results = curl_exec($ch);
		if(!$results)
		{
			$errNumber = curl_errno($ch);
			$errDescription = curl_error($ch);
			
			curl_close($ch);
		
			throw new Exception("Curl error [$errDescription] number [$errNumber]", $errNumber);
		}
		curl_close($ch);
		VidiunLog::log("MSN HTTP response:\n$results\n");
		$data->sentData = $providerData->xml;
		$data->results = $results;
		return $results;
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @param VidiunMsnDistributionProfile $distributionProfile
	 * @param VidiunMsnDistributionJobProviderData $providerData
	 */
	protected function handleUpdate(VidiunDistributionJobData $data, VidiunMsnDistributionProfile $distributionProfile, VidiunMsnDistributionJobProviderData $providerData)
	{
	}
	
	/**
	 * @param VidiunDistributionJobData $data
	 * @return string status
	 */
	protected function fetchStatus(VidiunDistributionJobData $data, &$errDescription)
	{
		if(!$data->distributionProfile || !($data->distributionProfile instanceof VidiunMsnDistributionProfile))
			throw new Exception("Distribution profile must be of type VidiunMsnDistributionProfile");
	
		$xml = $this->fetchXML($data, $data->distributionProfile);
	
		$liveSiteErrorNodes = $xml->documentElement->getElementsByTagName('liveSiteError');
		$errorNodes = $xml->documentElement->getElementsByTagName('error');
		if($liveSiteErrorNodes->length)
			$errDescription = $liveSiteErrorNodes->item(0)->textContent;
		elseif ($errorNodes->length)
			$errDescription = $errorNodes->item(0)->textContent;
		
		$publishStateAttr = $xml->documentElement->attributes->getNamedItem('publishState');
		if($publishStateAttr)
			return $publishStateAttr->value;
				
		return null;
	}

	/**
	 * @param VidiunDistributionJobData $data
	 * @throws Exception
	 * @return DOMDocument
	 */
	protected function fetchXML(VidiunDistributionJobData $data, VidiunMsnDistributionProfile $distributionProfile)
	{
		$domain = $distributionProfile->domain;
		$username = $distributionProfile->username;
		$password = $distributionProfile->password;
		
		$url = "https://{$domain}/admin/services/videobyuuid.aspx?uuid={$data->remoteId}";
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, false);
		
		$results = curl_exec($ch);
		if(!$results)
		{
			$errNumber = curl_errno($ch);
			$errDescription = curl_error($ch);
			
			curl_close($ch);
		
			throw new Exception("Curl error [$errDescription] number [$errNumber]", $errNumber);
		}
		curl_close($ch);

		VidiunLog::info("results [$results]");
		
		$xml = new DOMDocument();
		if($xml->loadXML($results))
			return $xml;
			
		return null;
	}
}