<?php

class vVsUrlTokenizer extends vUrlTokenizer
{
	/**
	 * @var bool
	 */
	protected $usePath;
	
	/**
	 * @var string
	 */
	protected $additionalUris;

	/**
	 * @return $usePath
	 */
	public function getUsePath() 
	{
		return $this->usePath;
	}
	
	/**
	 * @param bool $usePath
	 */
	public function setUsePath($usePath) 
	{
		$this->usePath = $usePath;
	}
	
	/**
	 * @return $additionalUris
	 */
	public function getAdditionalUris()
	{
		return $this->additionalUris;
	}

	/**
	 * @param string $additionalUris
	 */
	public function setAdditionalUris($additionalUris)
	{
		$this->additionalUris = $additionalUris;
	}

	/**
	 * @param string $url
	 * @param string $urlPrefix
	 * @return string
	 */
	public function tokenizeSingleUrl($url, $urlPrefix = null)
	{
		if (!$this->vsObject || !$this->vsObject->user)
		{
			require_once(__DIR__ . '/../../VExternalErrors.class.php');
			
			VExternalErrors::dieError(VExternalErrors::MISSING_PARAMETER, 'vs user');
		}
		
		$uriRestrict = explode(',', $url);		// cannot contain commas, since it's used as the privileges delimiter
		$uriRestrict = $uriRestrict[0] . '*';

		if ($this->additionalUris)
		{
			$uriRestrict .= '|' . $this->additionalUris;
		}
		
		$privileges = vSessionBase::PRIVILEGE_DISABLE_ENTITLEMENT_FOR_ENTRY . ':' . $this->entryId;
		$privileges .= ',' . vSessionBase::PRIVILEGE_VIEW . ':' . $this->entryId;
		$privileges .= ',' . vSessionBase::PRIVILEGE_URI_RESTRICTION . ':' . $uriRestrict;
		if ($this->limitIpAddress)
		{
			$privileges .= ',' . vSessionBase::PRIVILEGE_IP_RESTRICTION . ':' . infraRequestUtils::getRemoteAddress();
		}

		$vs = vSessionBase::generateVsV2(
			$this->key, 
			$this->vsObject->user, 
			vSessionBase::SESSION_TYPE_USER, 
			$this->partnerId, 
			$this->window, 
			$privileges, 
			null, 
			null);

		if ($this->usePath)
		{
			$insertPos = strpos($url, '/name/');
			if ($insertPos !== false)
			{
				return substr($url, 0, $insertPos) . '/vs/' . $vs . substr($url, $insertPos);
			}
		}
		return $url . '?vs=' . $vs;
	}
}
