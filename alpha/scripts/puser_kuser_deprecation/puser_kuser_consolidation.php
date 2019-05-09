<?php

print("Usage for specific partner(1234): php puser_vuser_consolidation 1234\n");
print("Usage for all partners: php puser_vuser_consolidation\n");
print("In Order to run a real run just type realRun in the end\n");

require_once(dirname(__FILE__).'/../bootstrap.php');

/**
 * 
 * Holds all neccessary puser details
 * @author Roni
 *
 */
class puserDetails
{
	public function __construct($puserId = null, $partnerId = null)
	{
		$this->puserId = $puserId;
		$this->partnerId = $partnerId; 
	}
	
	/**
	 * 
	 * The puser Id 
	 * @var string
	 */
	public $puserId;
	
	/**
	 * 
	 * The puser partner
	 * @var int
	 */
	public $partnerId;
}

/**
 * 
 * Consolidates all the pusers in the system
 * Fixes the puser to vuser issues 
 * @author Roni
 *
 */
class puserVuserConsolidator
{
	/**
	 * 
	 * The partners for which to ignore during the consolidation
	 * @var unknown_type
	 */
	private $ignorePartners = array(0, 99, 100);
	
	/**
	 * 
	 * Says if this is a real run or a dry run
	 * @var bool
	 */	
	private $isDryRun = true;
	
	/**
	 * 
	 * The max log file size
	 * @var int
	 */
	const MAX_LOG_FILE_SIZE = 50000;
	
	/**
	 * 
	 * The partner id to consolidate users on. (if null we do on all)
	 * @var int
	 */
	public $partnerId = null; 
		
	/**
	 * 
	 * The log file name (for manual log rotation)
	 * @var string
	 */
	private $logFileName = "c:/opt/vidiun/app/scripts/puser_vuser_deprecation/deprecation.log"; 
	
	/**
	 * 
	 * The current log numbet
	 * @var int
	 */
	private $currentlogNumber = 0;
	
	/**
	 * 
	 * Limits the number of handled pusers
	 * @var unknown_type
	 */
	public $limit = 100;
	 
	/**
	 * 
	 * Count the number of handled pusers
	 * @var unknown_type
	 */
	private $numOfHandledPusers = 0;
	
	/**
	 * 
	 * Holds all handled pusers
	 * @var array
	 */
	private $handledPusers = array();
	
	/**
	 * 
	 * The created users (used for dry run)
	 * @var unknown_type
	 */
	private $createdVusers = array();
	
	/**
	 * 
	 * All the pusers in the system
	 * @var unknown_type
	 */
	private $pusers = array();

	/**
	 * 
	 * The last created at date for the last entry
	 * @var int
	 */
	private $lastEntryFile = 'puser_vuser_deprecation.last_entry';
	
	/**
	 * 
	 * The last id of the puser
	 * @var string
	 */
	private $lastPuserFile = 'puser_vuser_deprecation.last_puser';
	
	/**
	 * 
	 * The last id of the vuser
	 * @var string
	 */
	private $lastVuserFile = 'puser_vuser_deprecation.last_vuser';
		
	/**
	 * 
	 * Creates a new consolidator for the given partner Id
	 * @param int $partnerId
	 */
	public function __construct($partnerId = null)
	{
		$this->partnerId = $partnerId;
		if(file_exists("{$this->logFileName}.{$this->currentlogNumber}")) //Clean the log file
		{
			file_put_contents("{$this->logFileName}.{$this->currentlogNumber}", "");
		}
	}
	
	/**
	 * 
	 * Inits the class with the given args from the command line
	 * @param array $argv
	 */
	public function initArgs($args)
	{
		$partnerId = null;
		$numOfArgs = count($args);
		
		if(isset($args[1]) && is_numeric($args[1]))
		{
			$this->partnerId = $args[1];
		}
		
		//Gets the last parameter
		
		$lastArg = $args[$numOfArgs-1];
		$isDryRun = true;
		if($lastArg == 'realRun')
		{
			$isDryRun = false;
		}
	}
	
	/**
	 * 
	 * Gets all the vusers from the puser_ vuser table by the given puser id and partner id
	 * @param string $puserId
	 * @param int $partnerId
	 */
	private function getVusersFromPuserVuserTable($puserId, $partnerId)
	{
		PuserVuserPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(PuserVuserPeer::PUSER_ID, $puserId, Criteria::EQUAL);
		$c->add(PuserVuserPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
		$c->add(PuserVuserPeer::PARTNER_ID, $this->ignorePartners, Criteria::NOT_IN);
		PuserVuserPeer::setUseCriteriaFilter(false);
		$puserVusers = PuserVuserPeer::doSelect($c);
		PuserVuserPeer::setUseCriteriaFilter(true);
		
		$vusers = array();
		
		foreach($puserVusers as $puserVuser) 
		{
			// now we check that the puser_vuser table doesn't reference to a different puser in the vuser table
			$vuser = vuserPeer::retrieveByPK($puserVuser->getVuserId());
			
			if(!is_null($vuser))
			{
				$vuserId = $vuser->getId();
				$vuserTablePuserId = $vuser->getPuserId();
				if(is_null($vuserTablePuserId))
				{
					$vuser->setPuserId($puserId);
										
					$this->printToLog("puserId [$puserId] in the Vuser table is null for vuser [$vuserId]- Perfect Match just set the vuser puser id");
					if(!$this->isDryRun)
					{
						$vuser->save();
					} 
				}
				
				if($vuserTablePuserId == $puserId && $vuser->getPartnerId() == $partnerId) // if this is the same partner and user
				{
					$vusers[] = $vuser; //add to the valid vusers
					$this->printToLog("Vuser [$vuserId] was added to the users found in the puser vuser table");
				}
				else // the puser on the vsuer are different from the vuser in the puser table
				{
					$vuserTablePuserId = $vuser->getPuserId();
					$puserTableVuserId = $puserVuser->getVuserId();
					
					$this->printToLog("We have a different vusers and pusers (Cross reference!!!)");
					$this->printToLog("partnerId [$partnerId], table puser_vuser: given puserId[$puserId] -> vuserId[$puserTableVuserId ], table vuser puserId [$vuserTablePuserId]");
				}
			}
			else //No such vuser
			{
				$vuserId = $puserVuser->getVuserId(); // the vuser id on the puser table
				$this->printToLog("Puser [$puserId], has vuser [$vuserId] and it can't be found on VUSER table");
			}
		}
		 
		return $vusers;
	}
	
	/**
	 * 
	 * Gets all the vusers from the vuser table
	 * @param string $puserId
	 * @param int $partnerId
	 */
	private function getVusersFromVuserTable($puserId, $partnerId)
	{
		vuserPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(vuserPeer::PUSER_ID, $puserId, Criteria::EQUAL);
		$c->add(vuserPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
		$c->add(vuserPeer::PARTNER_ID, $this->ignorePartners, Criteria::NOT_IN);
		vuserPeer::setUseCriteriaFilter(false);
		$vusers =  vuserPeer::doSelect($c);
		vuserPeer::setUseCriteriaFilter(true);
		return $vusers;
	}

	/**
	 * 
	 * Gets all entries for the given vuser
	 * @param int $vuserId - the vuser id
	 */
	private function getEntriesByVuser($vuserId)
	{
		entryPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(entryPeer::VUSER_ID, $vuserId, Criteria::EQUAL);
		$c->addAnd(entryPeer::PUSER_ID, null, Criteria::NOT_EQUAL);
		$c->addAnd(entryPeer::PUSER_ID, "", Criteria::NOT_EQUAL);
		
//		$c->setLimit($limit);
		entryPeer::setUseCriteriaFilter(false);
		$entries = entryPeer::doSelect($c);
		entryPeer::setUseCriteriaFilter(true);
		
		return $entries;
	}

	/**
	 * 
	 * Consolidates a given array of vusers to the first member in the vusers array
	 * @param array<vuser> $vusers
	 */
	private function consolidateVusers(array $vusers)
	{
		//Set the new vuser to be the first in teh array 
		$newVuser = $vusers[0];
		$newVuserId = $newVuser->getId();
		
		foreach ($vusers as $vuser)
		{
			$vuserId = $vuser->getId();
			$puserId = $vuser->getPuserId();
			$partnerId = $vuser->getPartnerId();
			
			if($vuserId == $newVuserId)
				continue;
			
			$entriesForVuser = $this->getEntriesByVuser($vuserId);
			foreach ($entriesForVuser as $entry)
			{
				$entryId = $entry->getId();
				$entryPuserId = $entry->getPuserId();
				$entryPartnerId = $entry->getPartnerId();
				if( $puserId == $entryPuserId && $partnerId == $entryPartnerId ) //if partner and puser are the same (as they should)
				{
					$entry->setVuserId($newVuserId);
					$this->printToLog("Changed EntryId [$entryId] from Vuser [$vuserId] to new Vsuer [$newVuserId for puser [$puserId], partner [$partnerId]\n");
					if(!$this->isDryRun)
					{
						$entry->save();
					}
				}
				else
				{
					$this->printToLog("EntryId [$entryId], entryPuser [$entryPuserId], entryPartner [$entryPartnerId] NOT CHANGED ".
					 				  "from Vuser [$vuserId] to new Vsuer [$newVuserId for puser [$puserId], partner [$partnerId]\n");
				}
			}
		}
	}

	/**
	 * 
	 * Gets or creates a vuser for the given puser id and partner id
	 * @param string $puserId
	 * @param int $partnerId
	 */
	private function getOrCreateVuser($puserId, $partnerId)
	{
//	if vuser table contains the puser only once =>
//		vuser = from vuser table
//	if vuser table contains the puser more than once =>
//		vuser = first vuser from table

//function getvuser(puser) Main algorithm:
//	if vuser table contains the puser =>
//		vuser = first from vuser table
//	else if vuserPuser contains the puser => 
//		vuser = from vuserPuser
//		if vuser table does not contain vuser
//			add vuser to table
//		else (Vuser contains)
//			if this is the same puser on the vuser table or null
//				Update the vuser table with the current puser id
//			else
//				//Print major conflict
//	else
//		create new vuser (+ optionally fix vuserPuser)

		//$this->printToLog("Getting or creating vuser for puser [$puserId], partner [$partnerId]");
		$vuser = null;
		
		$vusers = $this->getVusersFromVuserTable($puserId, $partnerId);
		//$this->printParam("vusers from vuser are: ", count($vusers));
		
		if($vusers && count($vusers) > 0)
		{
			//$this->printToLog("Vuser was found in the vuser table (maybe update the puser_vuser table)");
			
			$vuser = $vusers;
			if(is_array($vusers) && count($vusers) != 1)
			{
				$vuser = $vusers[0]; // gets the first vuser (if there are many)
				if(count($vusers) > 1) //if there are more then 1 or less then 1 Vuser we need to consolidate  / Create the vuser them all
				{
					$this->printToLog(count($vusers) . " were found in VUSER table for puser [$puserId], partner [$partnerId], needs to consolidate them all");
					$this->consolidateVusers($vusers);
				}
			}
		}
		else //Search the puser in puser_vuser table
		{
			//$this->printToLog(count($vusers) . " were found in VUSER table for puser [$puserId], partner [$partnerId], Searching in Puser Table");

			$vusers = $this->getVusersFromPuserVuserTable($puserId, $partnerId);
					
			$vuser = $vusers;
			if(is_array($vusers) && count($vusers) > 0)
			{
				$this->printToLog(count($vusers) . " were found in PUSER table for puser [$puserId], partner [$partnerId], needs to consolidate them all");
				$vuser = $vusers[0]; // gets the first vuser (if there are many)
			}
			else
			{
				if(count($vusers) == 0)
				{
					//$this->printToLog(count($vusers) . " vusers were found in PUSER table for puser [$puserId], partner [$partnerId]");
					//No vusers were found
				}
			}
		}
		
		if(is_null($vuser ) || (is_array($vuser) && count($vuser) == 0))
		{
			$this->printToLog("Vuser was not found!!! Creating new vuser for puser [$puserId], partner [$partnerId]");
			
			//no vuser found so we create one
			$this->createVuser($puserId, $partnerId);
		}
		
		return $vuser;
	}

	/**
	 * 
	 * Creates a new vuser and insert it into the vuser table
	 * @param string $puserId
	 * @param int $partnerId
	 */
	private function createVuser($puserId, $partnerId)
	{
		$vuser = new vuser();
		$vuser->partnerId = $partnerId;
		$vuser->puserId = $puserId;
		if($this->isDryRun)
		{
			$this->createdVusers["{$puserId}_{$partnerId}"] = $vuser;
		}
		else
		{
			$rowsAffected = $vuser->save();

			if($rowsAffected != 1)
			{
				$this->printToLog("Error in save: rows affected [$rowsAffected]");
			}
		}
		
	}
	
	/**
	 * 
	 * Prints a given meesage and param
	 * @param string $message
	 * @param unknown_type $param
	 */
	private function printParam($message, $param)
	{
		$this->printToLog($message . print_r($param, true));
	}
	
	/**
	 * 
	 * Gets all the pusers from the entry table
	 * @param int $lastEntryDate - the last entry date
	 * @param int $limit - the limit for the query
	 */
	private function getAllPusersInEntry($lastEntryDate, $limit)
	{
		$pusers = array();
		entryPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(entryPeer::CREATED_AT, $lastEntryDate, Criteria::GREATER_THAN);
		$c->addAnd(entryPeer::PUSER_ID, null, Criteria::NOT_EQUAL);
		$c->addAnd(entryPeer::PUSER_ID, "", Criteria::NOT_EQUAL);
		
		if($this->partnerId)
		{
			$c->addAnd(entryPeer::PARTNER_ID, $this->partnerId, Criteria::EQUAL);
		}
		
		$c->addAnd(entryPeer::PARTNER_ID, $this->ignorePartners, Criteria::NOT_IN);
		
		$c->addAscendingOrderByColumn(entryPeer::CREATED_AT);
		$c->setLimit($limit);
		entryPeer::setUseCriteriaFilter(false);
		$entries = entryPeer::doSelect($c);
		entryPeer::setUseCriteriaFilter(true);
		
		foreach ($entries as $entry)
		{
	//		$this->printToLog("Found entry with puser [{$entry->getPuserId()}], partner [{$entry->getPartnerId()}]");
			$pusers[] = new puserDetails($entry->getPuserId(), $entry->getPartnerId());
			
			file_put_contents($this->lastEntryFile, $entry->getCreatedAt());
		}
		
		return $pusers;
	}
		
	/**
	 * 
	 * Gets all the pusers from the puser table
	 * @param int $lastPuserId - the last puser id 
	 * @param int $limit - the limit for the query
	 */
	private function getAllPusersInPuser($lastPuserId, $limit)
	{
		$pusers = array();
		PuserVuserPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(PuserVuserPeer::ID, $lastPuserId, Criteria::GREATER_THAN);// if case we have several entries in the same date (and we stop in the middle)
		$c->addAnd(PuserVuserPeer::PUSER_ID, null, Criteria::NOT_EQUAL);
		$c->addAnd(PuserVuserPeer::PUSER_ID, "", Criteria::NOT_EQUAL);
		
		if($this->partnerId)
		{
			$c->addAnd(PuserVuserPeer::PARTNER_ID, $this->partnerId, Criteria::EQUAL);
		}
		
		$c->addAnd(PuserVuserPeer::PARTNER_ID, $this->ignorePartners, Criteria::NOT_IN);
		
		$c->addAscendingOrderByColumn(PuserVuserPeer::ID);
		$c->setLimit($limit);
		PuserVuserPeer::setUseCriteriaFilter(false);
		$pusers1 = PuserVuserPeer::doSelect($c);
		PuserVuserPeer::setUseCriteriaFilter(true);
				
		foreach ($pusers1 as $puser)
		{
		//	$this->printToLog("Found puser with id [{$puser->getId()}], partner [{$puser->getPartnerId()}]");
			$pusers[] = new puserDetails($puser->getPuserId(), $puser->getPartnerId());
			
			file_put_contents($this->lastPuserFile, $puser->getId());
		}
		
		return $pusers;
	}

	/**
	 * 
	 * Gets all the pusers from the vuser table
	 * @param int $lastVuserId - the last puser id 
	 * @param int $limit - the limit for the query
	 */
	private function getAllPusersInVuser($lastVuserId, $limit)
	{
		$pusers = array();
		vuserPeer::clearInstancePool();
		$c = new Criteria();
		$c->add(vuserPeer::ID, $lastVuserId, Criteria::GREATER_THAN);// if case we have several entries in the same date (and we stop in the middle)
		$c->addAnd(vuserPeer::ID, null, Criteria::NOT_EQUAL);
		$c->addAnd(vuserPeer::ID, "", Criteria::NOT_EQUAL);
		
		if($this->partnerId)
		{
			$c->addAnd(vuserPeer::PARTNER_ID, $this->partnerId, Criteria::EQUAL);
		}
		$c->addAnd(vuserPeer::PARTNER_ID, $this->ignorePartners, Criteria::NOT_IN);
		
		$c->addAscendingOrderByColumn(vuserPeer::ID);
		$c->setLimit($limit);
		vuserPeer::setUseCriteriaFilter(false);
		$vusers = vuserPeer::doSelect($c);
		vuserPeer::setUseCriteriaFilter(true);
				
		foreach ($vusers as $vuser)
		{
	//		$this->printToLog("Found puser with id [{$vuser->getPuserId()}], partner [{$vuser->getPartnerId()}] on Vuser [{$vuser->getId()}]");
			$pusers[] = new puserDetails($vuser->getPuserId(), $vuser->getPartnerId());
			
			file_put_contents($this->lastVuserFile, $vuser->getId());
		}
		
		return $pusers;
	}
	
	/**
	 * 
	 * Returns a list of all available pusers in the system by the given limit
	 * @param int $limit - the max number of entries / vusers / pusers to process
	 * @return puserDetails
	 */
	private function getAllPusersInTheSystem($limit)
	{
		$lastEntryDate = $this->getLastDate($this->lastEntryFile);
		$lastVuserId = $this->getLastId($this->lastVuserFile);
		$lastPuserId = $this->getLastId($this->lastPuserFile);
				
		$entryPusers = $this->getAllPusersInEntry($lastEntryDate, $limit);
		$puserPusers = $this->getAllPusersInPuser($lastPuserId, $limit);
		$vuserPusers = $this->getAllPusersInVuser($lastVuserId, $this->limit);
	
		$this->pusers = array_merge($this->pusers, $entryPusers);
		$this->pusers = array_merge($this->pusers, $puserPusers);
		$this->pusers = array_merge($this->pusers, $vuserPusers);

		return $this->pusers;
	}
	
	/**
	 * 
	 * Gets the last id of the given file (for entry, vuser, puser_vuser)
	 * @param string $file - the file path
	 */
	private function getLastId($file)
	{
		$lastId = 0;
		if(file_exists($file)) 
		{
			$lastId = file_get_contents($file);
			//$this->printToLog('file [$file] already exists with value - '.$lastId);
		}
		
		if(!$lastId)
			$lastId = 0;
		
		return $lastId;
	}
	
	/**
	 * 
	 * Gets the last date int the given file (for entry, vuser, puser_vuser)
	 * @param string $file - the file path
	 */
	private function getLastDate($file)
	{
		$lastDate = 0;
		if(file_exists($file)) 
		{
			$lastDate = file_get_contents($file);
			//$this->printToLog("file [$file] already exists with value - ".$lastDate);
		}
		
		if(!$lastDate)
			$lastDate = 0;
		
		return $lastDate;
	}

	/**
	 * 
	 * Consolidates all the pusers in the system (so each can have one vuser)
	 */
	public function consolidate()
	{
		$this->printToLog("Starting consolidation");
		
		$isMoreUsers = true;
		
		while($isMoreUsers)
		{
			$this->pusers = array();
			
			$pusers = $this->getAllPusersInTheSystem($this->limit);
						
			foreach($pusers as $puser)
			{
				$puserId = $puser->puserId;
				$partnerId = $puser->partnerId;
				
				if(in_array("{$puserId}_{$partnerId}", $this->handledPusers)) //if puser was handled we skip him
				{
					$vusers = $this->getVusersFromVuserTable($puserId, $partnerId);
					if(!is_null($vusers))
					{
						//$this->printToLog("Vuser is!!! : " . print_r($vuser, true));
						if(isset($this->createdVusers["{$puserId}_{$partnerId}"]))
						{
							//$this->printToLog("Puser [{$puserId}], partner [{$partnerId}] was handled VuserId [{$vuser->getId()}]");
						}
						elseif(is_array($vusers) && count($vusers) > 0)
						{
							$vuser = $vusers[0]; 
							//$this->printToLog("Puser [{$puserId}], partner [{$partnerId}] was handled VuserId [{$vuser->getId()}]");
						}
						else if(is_array($vusers) && count($vusers) == 0)
						{
							$this->printToLog("Puser [{$puserId}], partner [{$partnerId}] has no Vuser");
						}
					}
					else
					{
						$this->printToLog("Puser [{$puserId}], partner [{$partnerId}] was handled but Vuser is null");
					}
					
					continue;
				}
				
				$this->numOfHandledPusers++;
				$this->handledPusers["{$puserId}_{$partnerId}"] = "{$puserId}_{$partnerId}"; 
				
				
				$vuser = $this->getOrCreateVuser($puserId, $partnerId);
				
				if(is_null($vuser))
				{
					$this->printToLog("Vuser is null!!! for puser [{$puserId}], partner [{$partnerId}]");
					die(); // kill the script
				}
				
				//TODO: save the added puser / vuser 
				//file_put_contents($lastUserFile, $lastUser);
			}
			
			//$this->printToLog("Handled: ". count($pusers). " Pusers");
			if(count($pusers) == 0) // no more users
			{
				$isMoreUsers = false;
			}
		}
		
		$this->printToLog("Consolidation handled: {$this->numOfHandledPusers} pusers");
		return;
		
//Open issues:

//distribution: ask T
//	should use vuser_id or entry_id=>puser_id

//DWH:
//make sure that the updated at changes 
		
	}

	/**
	 * 
	 * Prints a message to the log. (rotate the log if it is too big)
	 * @param string $message
	 */
	private function printToLog($message)
	{
//		print("In print To Log \n");
		VidiunLog::debug($message);
//		$dirname = dirname(__FILE__);
//		$logFilePath = "{$this->logFileName}.{$this->currentlogNumber}";
//		print("Log file size: " . filesize($logFilePath) . "\n");
//		if(filesize($logFilePath) > puserVuserConsolidator::MAX_LOG_FILE_SIZE)
//		{
//			$this->rotateLog();
//		}
	}

	/**
	 * 
	 * Rotates the log into the new file 
	 */
	private function rotateLog()
	{
		$this->currentlogNumber++;
		
		try // we don't want to fail when logger is not configured right
		{
			$dirname = dirname(__FILE__);
			$logFilePath = "{$this->logFileName}.{$this->currentlogNumber}";
			$config = new Zend_Config_Ini("$dirname/logger.ini");
			$config->writers->stream->stream = $logFilePath;
		}
		catch(Zend_Config_Exception $ex)
		{
			$config = null;
		}
		
		VidiunLog::initLog($config);
	}
}

$puserVuserConsolidator = new puserVuserConsolidator();
$puserVuserConsolidator->initArgs($argv);
$puserVuserConsolidator->consolidate();