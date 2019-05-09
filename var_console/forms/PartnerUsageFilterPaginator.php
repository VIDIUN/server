<?php
/**
 * @package Var
 * @subpackage Partners
 */
class Form_PartnerUsageFilterPaginator extends Infra_FilterPaginator
{
     /**
      * "Total" report result
      * @var Vidiun_Client_VarConsole_Type_VarPartnerUsageItem
      */
     protected $total;   
     
    /**
	 * 
	 * @param int $offset
	 * @param int $itemCountPerPage
	 */
	protected function callService($offset, $itemCountPerPage)
	{
		$client = Infra_ClientHelper::getClient();
		if ($this->impersonatedPartnerId) {
			Infra_ClientHelper::impersonate($this->impersonatedPartnerId);
		}
		$pager = new Vidiun_Client_Type_FilterPager();
		$pager->pageIndex = (int)($offset / $itemCountPerPage) + 1;
		$pager->pageSize = $itemCountPerPage;
		$action = $this->action;
		$params = $this->args;
		$params[] = $pager;
		try{
			$response = call_user_func_array(array($this->service, $action), $params);
		}
		catch(Vidiun_Client_Exception $e){
			VidiunLog::err($e->getMessage());
			return array();
		}
		$this->totalCount = $response->totalCount;
		
		$this->total = $response->total;
		
		if(!$response->objects)
			return array();
			
		return $response->objects;
	}
	
	/**
     * @return Vidiun_Client_VarConsole_Type_VarPartnerUsageItem
     */
    public function getTotal ()
    {
        return $this->total;
    }

	

}