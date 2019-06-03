<?php
/**
 * @package plugins.systemPartner
 * @subpackage api.objects
 */
class VidiunSystemPartnerLimit extends VidiunObject
{
	/**
	 * @var VidiunSystemPartnerLimitType
	 */
	public $type;
	
	/**
	 * @var float
	 */
	public $max;
	
	/**
	 * @param VidiunSystemPartnerLimitType $type
	 * @param Partner $partner
	 * @return VidiunSystemPartnerLimit
	 */
	public static function fromPartner($type, Partner $partner)
	{
		$limit = new VidiunSystemPartnerLimit();
		$limit->type = $type;
		
		switch($type)
		{
			case VidiunSystemPartnerLimitType::ACCESS_CONTROLS:
				$limit->max = $partner->getAccessControls();
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_INPUTS:
				$limit->max = $partner->getMaxLiveStreamInputs();
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_OUTPUTS:
				$limit->max = $partner->getMaxLiveStreamOutputs();
				break;

			case VidiunSystemPartnerLimitType::USER_LOGIN_ATTEMPTS:
				$limit->max = $partner->getMaxLoginAttempts();
				break;
			
			case VidiunSystemPartnerLimitType::LIVE_RTC_STREAM_INPUTS:
				$limit->max = $partner->getMaxLiveRtcStreamInputs();
				break;
		}
		
		return $limit;
	} 

	public function validate()
	{
		switch($this->type)
		{
			case VidiunSystemPartnerLimitType::ACCESS_CONTROLS:
				$this->validatePropertyMinValue('max', 1, true);
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_INPUTS:
				$this->validatePropertyMinValue('max', 1, true);
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_OUTPUTS:
				$this->validatePropertyMinValue('max', 1, true);
				break;
				
			case VidiunSystemPartnerLimitType::USER_LOGIN_ATTEMPTS:
				$this->validatePropertyMinValue('max', 0, true);
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_RTC_STREAM_INPUTS:
				$this->validatePropertyMinValue('max', 0, true);
				break;
		}
	}
	
	/**
	 * @param Partner $partner
	 */
	public function apply(Partner $partner)
	{
		if($this->isNull('max'))
			$this->max = null;
			
		switch($this->type)
		{
			case VidiunSystemPartnerLimitType::ACCESS_CONTROLS:
				$partner->setAccessControls($this->max);
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_INPUTS:
				$partner->setMaxLiveStreamInputs($this->max);
				break;
				
			case VidiunSystemPartnerLimitType::LIVE_STREAM_OUTPUTS:
				$partner->setMaxLiveStreamOutputs($this->max);
				break;
				
			case VidiunSystemPartnerLimitType::USER_LOGIN_ATTEMPTS:
				$partner->setMaxLoginAttempts($this->max);
				break;
			
			case VidiunSystemPartnerLimitType::LIVE_RTC_STREAM_INPUTS:
				$partner->setMaxLiveRtcStreamInputs($this->max);
				break;
		}
	} 
}