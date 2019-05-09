<?php
/**
 * @package plugins.sphinxSearch
 * @subpackage model.filters
 */
class SphinxVuserCriteria extends SphinxCriteria
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getIndexObjectName() {
    	return "vuserIndex";
    }
    
	/* (non-PHPdoc)
	 * @see SphinxCriteria::applyFilterFields()
	 */
	protected function applyFilterFields(baseObjectFilter $filter)
	{		
		//Role ids and vuser permission names are indexed with the partner ID
		if ($filter->get('_eq_role_ids'))
		{
			$filter->set('_eq_role_ids', vuser::getIndexedFieldValue('vuserPeer::ROLE_IDS', $filter->get('_eq_role_ids'), vCurrentContext::getCurrentPartnerId()));
		}
		if ($filter->get('_in_role_ids'))
		{
			$filter->set('_eq_role_ids', vuser::getIndexedFieldValue('vuserPeer::ROLE_IDS', $filter->get('_eq_role_ids'), vCurrentContext::getCurrentPartnerId()));
		}
		if ($filter->get('_mlikeand_permission_names'))
		{
			$permissionNames = vuser::getIndexedFieldValue('vuserPeer::PERMISSION_NAMES', $filter->get('_mlikeand_permission_names'), vCurrentContext::getCurrentPartnerId());
			$permissionNames = implode(' ', explode(',', $permissionNames));
			$universalPermissionName = vuser::getIndexedFieldValue('vuserPeer::PERMISSION_NAMES', vuser::UNIVERSAL_PERMISSION, vCurrentContext::getCurrentPartnerId());
			$value = "($universalPermissionName | ($permissionNames))";
			$this->addMatch("@permission_names $value");
			$filter->unsetByName('_mlikeand_permission_names');
		}
		if ($filter->get('_mlikeor_permission_names'))
		{
			$filter->set('_mlikeor_permission_names', vuser::getIndexedFieldValue('vuserPeer::PERMISSION_NAMES', $filter->get('_mlikeor_permission_names').','.vuser::UNIVERSAL_PERMISSION, vCurrentContext::getCurrentPartnerId()));
		}
		
		if($filter->get('_likex_puser_id_or_screen_name'))
		{
			$freeTexts = $filter->get('_likex_puser_id_or_screen_name');
			VidiunLog::debug("Attach free text [$freeTexts]");
			
			$additionalConditions = array();
			$advancedSearch = $filter->getAdvancedSearch();
			if($advancedSearch)
			{
				$additionalConditions = $advancedSearch->getFreeTextConditions($filter->getPartnerSearchScope(), $freeTexts);
			}
			
			$this->addFreeTextToMatchClauseByMatchFields($freeTexts, vuserFilter::PUSER_ID_OR_SCREEN_NAME, $additionalConditions, true);
		}
		$filter->unsetByName('_likex_puser_id_or_screen_name');
		
		if($filter->get('_likex_first_name_or_last_name'))
		{
			$names = $filter->get('_likex_first_name_or_last_name');
			VidiunLog::debug("Attach free text [$names]");
			
			$this->addFreeTextToMatchClauseByMatchFields($names, vuserFilter::FIRST_NAME_OR_LAST_NAME, null, true);
		}
		$filter->unsetByName('_likex_first_name_or_last_name');
		
		return parent::applyFilterFields($filter);
	}
    
}