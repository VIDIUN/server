<?php
/**
 * Aspera service
 *
 * @service aspera
 * @package plugins.aspera
 * @subpackage api.services
 */
class AsperaService extends VidiunBaseService
{

	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		$this->applyPartnerFilterForClass('asset');
		if(!AsperaPlugin::isAllowedPartner($this->getPartnerId()))
			throw new VidiunAPIException(VidiunErrors::FEATURE_FORBIDDEN, AsperaPlugin::PLUGIN_NAME);
	}

	/**
	 *
	 * @action getFaspUrl
	 * @param string $flavorAssetId
	 * @throws VidiunAPIException
	 * @return string
	 */
	function getFaspUrlAction($flavorAssetId)
	{
		VidiunResponseCacher::disableCache();
		
		$assetDb = assetPeer::retrieveById($flavorAssetId);
		if (!$assetDb || !($assetDb instanceof flavorAsset))
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_ID_NOT_FOUND, $flavorAssetId);

		if (!$assetDb->isLocalReadyStatus())
			throw new VidiunAPIException(VidiunErrors::FLAVOR_ASSET_IS_NOT_READY);

		$syncKey = $assetDb->getSyncKey(flavorAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		/* @var $fileSync FileSync */
		list($fileSync, $isFileSyncLocal) = vFileSyncUtils::getReadyFileSyncForKey($syncKey);
		$filePath = $fileSync->getFilePath();

		$transferUser = $this->getFromAsperaConfig('transfer_user');
		$transferHost = $this->getFromAsperaConfig('transfer_host');
		$asperaNodeApi = new AsperaNodeApi(
			$this->getFromAsperaConfig('node_api_user'),
			$this->getFromAsperaConfig('node_api_password'),
			$this->getFromAsperaConfig('node_api_host'),
			$this->getFromAsperaConfig('node_api_port')
		);

		$options = array(
			'transfer_requests' => array(
				'transfer_request' => array(
					'remote_host' => $transferHost
				)
			)
		);
		$tokenResponse = $asperaNodeApi->getToken($filePath, $options);
		$token = $tokenResponse->transfer_spec->token;

		$urlParams = array(
			'auth' => 'no',
			'token' => $token
		);

		return 'fasp://'.$transferUser.'@'.$transferHost.$filePath.'?'.http_build_query($urlParams, '', '&');
	}

	protected function getFromAsperaConfig($key)
	{
		$asperaConfig = vConf::get('aspera');
		if (!is_array($asperaConfig))
			throw new vCoreException('Aspera config section is not an array');

		if (!isset($asperaConfig[$key]))
			throw new vCoreException('The key '.$key.' was not found in the aspera config section');

		return $asperaConfig[$key];
	}
}
