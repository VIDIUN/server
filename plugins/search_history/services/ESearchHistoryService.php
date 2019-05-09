<?php
/**
 * @service searchHistory
 * @package plugins.searchHistory
 * @subpackage api.services
 */
class ESearchHistoryService extends VidiunBaseService 
{

    /**
     * @action list
     * @param VidiunESearchHistoryFilter|null $filter
     * @return VidiunESearchHistoryListResponse
     * @throws VidiunAPIException
     */
    public function listAction(VidiunESearchHistoryFilter $filter = null)
    {
        if (!$filter)
            $filter = new VidiunESearchHistoryFilter();

        try
        {
            $response = $filter->getListResponse();
        }
        catch (vESearchHistoryException $e)
        {
            $this->handleSearchHistoryException($e);
        }
        return $response;
    }

    /**
     * @action delete
     * @param string $searchTerm
     * @throws VidiunAPIException
     */
    public function deleteAction($searchTerm)
    {
        if (is_null($searchTerm) || $searchTerm == '')
        {
            throw new VidiunAPIException(VidiunESearchHistoryErrors::EMPTY_DELETE_SEARCH_TERM_NOT_ALLOWED);
        }

        try
        {
            $historyClient = new vESearchHistoryElasticClient();
            $historyClient->deleteSearchTermForUser($searchTerm);
        }
        catch (vESearchHistoryException $e)
        {
            $this->handleSearchHistoryException($e);
        }
    }

    private function handleSearchHistoryException($exception)
    {
        $code = $exception->getCode();
        $data = $exception->getData();
        switch ($code)
        {
            case vESearchHistoryException::INVALID_USER_ID:
                throw new VidiunAPIException(VidiunESearchHistoryErrors::INVALID_USER_ID);

            default:
                throw new VidiunAPIException(VidiunESearchHistoryErrors::INTERNAL_SERVERL_ERROR);
        }
    }

}
