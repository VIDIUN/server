<?php
/**
 * Class which parses the bulk upload CSV and creates the objects listed in it. 
 * This engine class parses CSVs which describe users.
 * 
 * @package plugins.bulkUploadCsv
 * @subpackage batch
 */
class BulkUploadUserEngineCsv extends BulkUploadEngineCsv
{
	const OBJECT_TYPE_TITLE = 'user';
	private $groupActionsList;

	public function __construct(VidiunBatchJob $job)
	{
		parent::__construct($job);
		$this->groupActionsList = array();
	}

	/**
     * (non-PHPdoc)
     * @see BulkUploadGeneralEngineCsv::createUploadResult()
     */
    protected function createUploadResult($values, $columns)
	{
		$bulkUploadResult = parent::createUploadResult($values, $columns);
		if (!$bulkUploadResult)
			return;

		$bulkUploadResult->bulkUploadResultObjectType = VidiunBulkUploadObjectType::USER;

		// trim the values
		array_walk($values, array('BulkUploadUserEngineCsv', 'trimArray'));

		// sets the result values
		$dateOfBirth = null;

		foreach($columns as $index => $column)
		{
			if(!is_numeric($index))
				continue;

			if ($column == 'dateOfBirth')
			{
			    $dateOfBirth = $values[$index];
			}

			if(iconv_strlen($values[$index], 'UTF-8'))
			{
				$bulkUploadResult->$column = $values[$index];
				VidiunLog::info("Set value $column [{$bulkUploadResult->$column}]");
			}
			else
			{
				VidiunLog::info("Value $column is empty");
			}
		}

		if(isset($columns['plugins']))
		{
			$bulkUploadPlugins = array();

			foreach($columns['plugins'] as $index => $column)
			{
				$bulkUploadPlugin = new VidiunBulkUploadPluginData();
				$bulkUploadPlugin->field = $column;
				$bulkUploadPlugin->value = iconv_strlen($values[$index], 'UTF-8') ? $values[$index] : null;
				$bulkUploadPlugins[] = $bulkUploadPlugin;

				VidiunLog::info("Set plugin value $column [{$bulkUploadPlugin->value}]");
			}

			$bulkUploadResult->pluginsData = $bulkUploadPlugins;
		}

		$bulkUploadResult->objectStatus = VidiunUserStatus::ACTIVE;
		$bulkUploadResult->status = VidiunBulkUploadResultStatus::IN_PROGRESS;

		if (!$bulkUploadResult->action)
		{
		    $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		}

		$bulkUploadResult = $this->validateBulkUploadResult($bulkUploadResult, $dateOfBirth);
		if($bulkUploadResult)
			$this->bulkUploadResults[] = $bulkUploadResult;
	}

	protected function validateBulkUploadResult (VidiunBulkUploadResult $bulkUploadResult, $dateOfBirth = null)
	{
	    /* @var $bulkUploadResult VidiunBulkUploadResultUser */
		if (!$bulkUploadResult->userId)
		{
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Mandatory Column [userId] missing from CSV.";
		}

		if ($dateOfBirth && !self::isFormatedDate($dateOfBirth, true))
		{
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Format of property dateOfBirth is incorrect [$dateOfBirth].";
		}

		if ($bulkUploadResult->gender && !self::isValidEnumValue("VidiunGender", $bulkUploadResult->gender))
		{
		    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Wrong value passed for property gender [$bulkUploadResult->gender]";
		}

	    if ($bulkUploadResult->action == VidiunBulkUploadAction::ADD_OR_UPDATE)
		{
		    VBatchBase::impersonate($this->currentPartnerId);;
		    try
		    {
		        $user = VBatchBase::$vClient->user->get($bulkUploadResult->userId);
    		    if ( $user )
    		    {
    		        $bulkUploadResult->action = VidiunBulkUploadAction::UPDATE;
    		    }
		    }
	        catch (Exception $e)
	        {
	            $bulkUploadResult->action = VidiunBulkUploadAction::ADD;
		    }
		    VBatchBase::unimpersonate();
		}


		if($this->maxRecords && $this->lineNumber > $this->maxRecords) // check max records
		{
			$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
			$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::APP;
			$bulkUploadResult->errorDescription = "Exeeded max records count per bulk";
		}

		if($bulkUploadResult->status == VidiunBulkUploadResultStatus::ERROR)
		{
			$this->addBulkUploadResult($bulkUploadResult);
			return null;
		}

		$bulkUploadResult->dateOfBirth = self::parseFormatedDate($bulkUploadResult->dateOfBirth, true);

		return $bulkUploadResult;
	}


    protected function addBulkUploadResult(VidiunBulkUploadResult $bulkUploadResult)
	{
		parent::addBulkUploadResult($bulkUploadResult);

	}
	/**
	 *
	 * Create the entries from the given bulk upload results
	 */
	protected function createObjects()
	{
		// start a multi request for add entries
		VBatchBase::$vClient->startMultiRequest();

		VidiunLog::info("job[{$this->job->id}] start creating users");
		$bulkUploadResultChunk = array(); // store the results of the created entries


		foreach($this->bulkUploadResults as $bulkUploadResult)
		{
			$this->addGroupUser($bulkUploadResult);

			/* @var $bulkUploadResult VidiunBulkUploadResultUser */
		    VidiunLog::info("Handling bulk upload result: [". $bulkUploadResult->userId ."]");
		    switch ($bulkUploadResult->action)
		    {
		        case VidiunBulkUploadAction::ADD:
    		        $user = $this->createUserFromResultAndJobData($bulkUploadResult);

        			$bulkUploadResultChunk[] = $bulkUploadResult;

        			VBatchBase::impersonate($this->currentPartnerId);;
        			VBatchBase::$vClient->user->add($user);
        			VBatchBase::unimpersonate();

		            break;

		        case VidiunBulkUploadAction::UPDATE:
		            $category = $this->createUserFromResultAndJobData($bulkUploadResult);

        			$bulkUploadResultChunk[] = $bulkUploadResult;

        			VBatchBase::impersonate($this->currentPartnerId);;
        			VBatchBase::$vClient->user->update($bulkUploadResult->userId, $category);
        			VBatchBase::unimpersonate();


		            break;

		        case VidiunBulkUploadAction::DELETE:
		            $bulkUploadResultChunk[] = $bulkUploadResult;

        			VBatchBase::impersonate($this->currentPartnerId);;
        			VBatchBase::$vClient->user->delete($bulkUploadResult->userId);
        			VBatchBase::unimpersonate();

		            break;

		        default:
		            $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
		            $bulkUploadResult->errorDescription = "Unknown action passed: [".$bulkUploadResult->action ."]";
		            break;
		    }

		    if(VBatchBase::$vClient->getMultiRequestQueueSize() >= $this->multiRequestSize)
			{
				// make all the media->add as the partner
				$requestResults = VBatchBase::$vClient->doMultiRequest();

				$this->multiUpdateResults($requestResults, $bulkUploadResultChunk);
				$this->checkAborted();
				VBatchBase::$vClient->startMultiRequest();
				$bulkUploadResultChunk = array();
			}
		}

		// make all the category actions as the partner
		$requestResults = VBatchBase::$vClient->doMultiRequest();

		if($requestResults && count($requestResults))
			$this->updateObjectsResults($requestResults, $bulkUploadResultChunk);


		$this->handleAllGroups();

		VidiunLog::info("job[{$this->job->id}] finish modifying users");
	}

	/**
	 * Function to create a new user from bulk upload result.
	 * @param VidiunBulkUploadResultUser $bulkUploadUserResult
	 */
	protected function createUserFromResultAndJobData (VidiunBulkUploadResultUser $bulkUploadUserResult)
	{
	    $user = new VidiunUser();
	    //Prepare object
	    if ($bulkUploadUserResult->userId)
	        $user->id = $bulkUploadUserResult->userId;

	    if ($bulkUploadUserResult->screenName)
	        $user->screenName = $bulkUploadUserResult->screenName;

	    if ($bulkUploadUserResult->tags)
	        $user->tags = $bulkUploadUserResult->tags;

	    if ($bulkUploadUserResult->firstName)
	        $user->firstName = $bulkUploadUserResult->firstName;

	    if ($bulkUploadUserResult->lastName)
	        $user->lastName = $bulkUploadUserResult->lastName;

	    if ($bulkUploadUserResult->email)
	        $user->email = $bulkUploadUserResult->email;

	    if ($bulkUploadUserResult->city)
	        $user->city = $bulkUploadUserResult->city;

	    if ($bulkUploadUserResult->country)
	        $user->country = $bulkUploadUserResult->country;

	    if ($bulkUploadUserResult->state)
	        $user->state = $bulkUploadUserResult->state;

	    if ($bulkUploadUserResult->zip)
	        $user->zip = $bulkUploadUserResult->zip;

	    if ($bulkUploadUserResult->gender)
	        $user->gender = $bulkUploadUserResult->gender;

	    if ($bulkUploadUserResult->dateOfBirth)
	        $user->dateOfBirth = $bulkUploadUserResult->dateOfBirth;

	    if ($bulkUploadUserResult->partnerData)
	        $user->partnerData = $bulkUploadUserResult->partnerData;

	    return $user;
	}

	/**
	 *
	 * Gets the columns for V1 csv file
	 */
	protected function getColumns()
	{
		return array(
		    "action",
		    "userId",
		    "screenName",
		    "firstName",
		    "lastName",
		    "email",
		    "tags",
		    "gender",
		    "zip",
		    "country",
		    "state",
			"city",
		    "dateOfBirth",
			"partnerData",
			"group",
			"userRole"
		);
	}


    protected function updateObjectsResults(array $requestResults, array $bulkUploadResults)
	{
		VidiunLog::info("Updating " . count($requestResults) . " results");
		$dummy=array();
		// checking the created entries
		foreach($requestResults as $index => $requestResult)
		{
			$bulkUploadResult = $bulkUploadResults[$index];
			$this->handleMultiRequest($dummy);
			if(is_array($requestResult) && isset($requestResult['code']))
			{
			    $bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
				$bulkUploadResult->objectStatus = $requestResult['code'];
				$bulkUploadResult->errorDescription = $requestResult['message'];
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}

			if($requestResult instanceof Exception)
			{
				$bulkUploadResult->status = VidiunBulkUploadResultStatus::ERROR;
				$bulkUploadResult->errorType = VidiunBatchJobErrorTypes::VIDIUN_API;
				$bulkUploadResult->errorDescription = $requestResult->getMessage();
				$this->addBulkUploadResult($bulkUploadResult);
				continue;
			}

			$this->addBulkUploadResult($bulkUploadResult);
		}
		$this->handleMultiRequest($dummy,true);
	}


	private function addGroupUser(VidiunBulkUploadResultUser $userResult)
	{
		if($userResult->group)
			$this->groupActionsList[]=$userResult;
	}

	private function getGroupActionList(&$usersToAddList,&$userGroupToDeleteMap)
	{
		foreach ($this->groupActionsList as $group)
		{
			if (strpos($group->group, "-") !== 0)
				$usersToAddList[]=$group;
			else
				$userGroupToDeleteMap[$group->userId] = substr($group->group, 1);
		}
	}

	private function getUsers($usersList)
	{
		$ret = array();
		foreach ($usersList as $group)
		{
			$this->handleMultiRequest($ret);
			VBatchBase::$vClient->user->get($group->group);
		}
		$this->handleMultiRequest($ret,true);
		return $ret;
	}

	private function deleteUsers($usersMap)
	{
		$ret = array();
		foreach ($usersMap as $userId=>$group)
		{
			$this->handleMultiRequest($ret);
			VBatchBase::$vClient->groupUser->delete($userId, $group);
		}
		$this->handleMultiRequest($ret,true);
		return $ret;
	}

	private function multiUpdateResults($results , $bulkUploadRequest)
	{
		VBatchBase::unimpersonate();
		$this->updateObjectsResults($results,$bulkUploadRequest);
		VBatchBase::impersonate($this->currentPartnerId);
	}

	private function addUserOfTypeGroup($actualGroupUsersList , $expectedGroupUsersList)
	{
		$ret=array();
		foreach ($actualGroupUsersList as $index => $user)
		{
			//check if value does not exist
			if( !($user instanceof VidiunUser)  ||  ($user->type != VidiunUserType::GROUP))
			{
				$this->handleMultiRequest($ret);
				VidiunLog::debug("Adding User of type group" . $expectedGroupUsersList[$index]->group );
				$groupUser = new VidiunUser();
				$groupUser->id = $expectedGroupUsersList[$index]->group;
				$groupUser->type = VidiunUserType::GROUP;
				VBatchBase::$vClient->user->add($groupUser);
			}
		}
		$this->handleMultiRequest($ret,true);
		return $ret;
	}

	private function handleMultiRequest(&$ret,$finish=false)
	{
		$count = VBatchBase::$vClient->getMultiRequestQueueSize();
		//Start of new multi request session
		if($count)
		{
			if (($count % $this->multiRequestSize) == 0 || $finish)
			{
				$result = VBatchBase::$vClient->doMultiRequest();
				if (count($result))
					$ret = array_merge($ret, $result);
				if (!$finish)
					VBatchBase::$vClient->startMultiRequest();
			}
		}
		elseif (!$finish)
		{
			VBatchBase::$vClient->startMultiRequest();
		}
	}

	private function addGroupUsers($groupUsersList)
	{
		$ret = array();
		foreach ($groupUsersList as $groupUserParams)
		{
			$this->handleMultiRequest($ret);
			$groupUser = new VidiunGroupUser();
			$groupUser->userId = $groupUserParams->userId;
			$groupUser->groupId = $groupUserParams->group;
			$groupUser->creationMode = VidiunGroupUserCreationMode::AUTOMATIC;
			$groupUser->userRole = $groupUserParams->userRole;
			VBatchBase::$vClient->groupUser->add($groupUser);
		}
		$this->handleMultiRequest($ret,true);
		return $ret;
	}

	private function handleAllGroups()
	{
		VidiunLog::info("Handling user/group association");
		VBatchBase::impersonate($this->currentPartnerId);
		$userGroupToDeleteMap = array();
		$groupUsersToAddList= array();
		$this->multiRequestSize = 100;
		$this->getGroupActionList($groupUsersToAddList,$userGroupToDeleteMap);
		$this->deleteUsers($userGroupToDeleteMap);
		if(count($groupUsersToAddList))
		{
			$requestResults = $this->getUsers($groupUsersToAddList);
			$this->addUserOfTypeGroup($requestResults, $groupUsersToAddList);

			$ret = $this->addGroupUsers($groupUsersToAddList);
			$this->multiUpdateResults($ret, $groupUsersToAddList);
		}
		VBatchBase::unimpersonate();
	}

	protected function getUploadResultInstance ()
	{
	    return new VidiunBulkUploadResultUser();
	}
	
	public function getObjectTypeTitle()
	{
		return self::OBJECT_TYPE_TITLE;
	}
}
