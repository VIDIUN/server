<?php
/**
 * @package plugins.sphinxSearch
 * @subpackage model.filters
 */
class SphinxCategoryVuserCriteria extends SphinxCriteria
{
	public function getIndexObjectName() {
		return "categoryVuserIndex";
	}
	
	/* (non-PHPdoc)
	 * @see SphinxCriteria::getFieldPrefix()
	 */
	public function getFieldPrefix ($fieldName)
	{
		switch ($fieldName)
		{
			case 'permission_names':
				return categoryVuser::PERMISSION_NAME_FIELD_INDEX_PREFIX. vCurrentContext::getCurrentPartnerId();
			case 'category_vuser_status':
				return categoryVuser::STATUS_FIELD_PREFIX . vCurrentContext::getCurrentPartnerId();
		}

		return parent::getFieldPrefix($fieldName);
	}

	/* (non-PHPdoc)
	 * @see SphinxCriteria::applyFilterFields()
	 */
	protected function applyFilterFields(baseObjectFilter $filter)
	{
		if ($filter->get('_in_status'))
		{
			$statusList = explode(',', $filter->get('_in_status'));
			$statusList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::STATUS, $statusList);
			$filter->set('_in_status', implode(',', $statusList));
		}
		
		if ($filter->get('_eq_status'))
		{
			$filter->set('_eq_status', categoryVuser::getSearchIndexFieldValue(categoryVuserPeer::STATUS, $filter->get('_eq_status'), vCurrentContext::getCurrentPartnerId()));
		}
		
		if ($filter->is_set('_in_update_method') && $filter->get('_in_update_method') != "")
		{
			$updateMethodList = explode(',', $filter->get('_in_update_method'));
			$updateMethodList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::UPDATE_METHOD, $updateMethodList);
			$filter->set('_in_update_method', implode(',', $updateMethodList));
		}
		
		if ($filter->get('_eq_update_method'))
		{
			$filter->set('_eq_update_method', categoryVuser::getSearchIndexFieldValue(categoryVuserPeer::UPDATE_METHOD, $filter->get('_eq_update_method'), vCurrentContext::getCurrentPartnerId()));
		}
		
		if (!is_null($filter->get('_eq_permission_level')))
		{
			$permissionLevel = $filter->get('_eq_permission_level');
			$permissionNamesList = categoryVuser::getPermissionNamesByPermissionLevel($permissionLevel);
			$negativePermissionNamesList = $this->fixPermissionNamesListForSphinx($permissionLevel);
			
			if($negativePermissionNamesList)
				$filter->set('_notcontains_permission_names', implode(',', $negativePermissionNamesList));
			
			if($filter->get('_matchand_permission_names'))
			{
				$permissionNamesList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList);				
				$criterion = $this->getNewCriterion(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList, baseObjectFilter::MATCH_AND);
				$this->addAnd($criterion);
			}
			else 
				$filter->set('_matchand_permission_names', $permissionNamesList);
				
			$filter->unsetByName('_eq_permission_level');
		}
		
		if ($filter->get('_in_permission_level'))
		{
			$permissionLevels = $filter->get('_in_permission_level');
			$permissionLevels = explode(',', $permissionLevels);
			foreach ($permissionLevels as $permissionLevel)
			{
				$permissionNamesList = categoryVuser::getPermissionNamesByPermissionLevel($permissionLevel);
				$permissionNamesList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList);
				$criterion = $this->getNewCriterion(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList, baseObjectFilter::MATCH_AND);
				$this->addOr($criterion);
			}
			
			$filter->unsetByName('_in_permission_level');
		}
		
		if ($filter->get('_matchor_permission_names'))
		{
			$permissionNamesList = explode(',', $filter->get('_matchor_permission_names'));
			$permissionNamesList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList);
			$filter->set('_matchor_permission_names', implode(',', $permissionNamesList));
		}

		if ($filter->get('_matchand_permission_names'))
		{
			$permissionNamesList = explode(',', $filter->get('_matchand_permission_names'));
			$permissionNamesList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList);
			$filter->set('_matchand_permission_names', implode(',', $permissionNamesList));
		}
		
		if ($filter->get('_notcontains_permission_names'))
		{
			$permissionNamesList = explode(',', $filter->get('_notcontains_permission_names'));
			$permissionNamesList = $this->translateToSearchIndexFieldValue(categoryVuserPeer::PERMISSION_NAMES, $permissionNamesList);
			$filter->set('_notcontains_permission_names', $permissionNamesList);
		}
		
		if ($filter->get('_eq_category_full_ids'))
		{
			$filter->set('_eq_category_full_ids', $filter->get('_eq_category_full_ids').category::FULL_IDS_EQUAL_MATCH_STRING);
		}
		
		return parent::applyFilterFields($filter);
	}
	
	public function fixPermissionNamesListForSphinx($permissionLevel)
	{
		switch ($permissionLevel)
	    {
	      case CategoryVuserPermissionLevel::MODERATOR:
	        $negativePermissionNamesArr[] = PermissionName::CATEGORY_CONTRIBUTE;
	        break;
	      case CategoryVuserPermissionLevel::CONTRIBUTOR:
	        $negativePermissionNamesArr[] = PermissionName::CATEGORY_MODERATE;
	        break;
	      case CategoryVuserPermissionLevel::MEMBER:
	        $negativePermissionNamesArr[] = PermissionName::CATEGORY_EDIT;
	        $negativePermissionNamesArr[] = PermissionName::CATEGORY_MODERATE;
	        $negativePermissionNamesArr[] = PermissionName::CATEGORY_CONTRIBUTE;
	        break;
	    }
	    
	    return $negativePermissionNamesArr;    
	}
	
	public function translateToSearchIndexFieldValue($fieldName, $toTranslate)
	{
		foreach ($toTranslate as &$translate)
		{
			$translate = categoryVuser::getSearchIndexFieldValue($fieldName, $translate, vCurrentContext::getCurrentPartnerId());
		}
		
		return $toTranslate;
	}
	
	public function translateSphinxCriterion (SphinxCriterion $crit)
	{
		$field = $crit->getTable() . '.' . $crit->getColumn();
		$value = $crit->getValue();
		
		$fieldName = null;
		if ($field == categoryVuserPeer::STATUS)
			$fieldName = categoryVuserPeer::STATUS;

		if ($fieldName)
		{
			$partnerIdCrit = $this->getCriterion(categoryVuserPeer::PARTNER_ID);
			if ($partnerIdCrit && $partnerIdCrit->getComparison() == Criteria::EQUAL)
				$partnerId = $partnerIdCrit->getValue();
			else
				$partnerId = vCurrentContext::getCurrentPartnerId();
			
			if (is_array($value))
			{
				foreach ($value as &$singleValue)
				{
					$singleValue = 	categoryVuser::getSearchIndexFieldValue($fieldName, $singleValue, $partnerId);
				}
			}
			else 
			{
				$value = categoryVuser::getSearchIndexFieldValue($fieldName, $value, $partnerId);
			}
		}

		return array($field, $crit->getComparison(), $value);
	}
	
}