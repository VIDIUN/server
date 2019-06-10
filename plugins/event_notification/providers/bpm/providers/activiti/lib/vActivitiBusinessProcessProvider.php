<?php 
/**
 * @package plugins.activitiBusinessProcessNotification
 */
class vActivitiBusinessProcessProvider extends vBusinessProcessProvider
{
	/**
	 * @var VidiunActivitiBusinessProcessServer
	 */
	protected $server;
	
	/**
	 * @var ActivitiClient
	 */
	protected $client;

	public function __construct($server)
	{
		$this->server = $server;
		
		$this->client = new ActivitiClient();
		$this->client->setUrl($this->server->host, $this->server->port, $this->server->protocol);
		$this->client->setCredentials($this->server->username, $this->server->password);
	}
	
	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::enableDebug()
	 */
	public function enableDebug($enable)
	{
		$this->client->setDebug($enable);
	}
	
	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::listBusinessProcesses()
	 */
	public function listBusinessProcesses()
	{
		$size = 100;
		$start = 0;
		$processes = $this->client->processDefinitions->listOfProcessDefinitions($size);
		
		$ret = array();
		while($processes)
		{
			foreach($processes->getData() as $process)
			{
				/* @var $process ActivitiListOfProcessDefinitionsResponseData */
				$ret[$process->getKey()] = $process->getName();
			}
			
			if(($processes->getStart() + $processes->getSize()) < $processes->getTotal())
			{
				$start += $size;
				$processes = $this->client->processDefinitions->listOfProcessDefinitions($size, $start);
			}
			else 
			{
				$processes = false;
			}
		}
		return $ret;
	}

	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::startBusinessProcess()
	 */
	public function startBusinessProcess($processId, array $variables)
	{
		$startVariables = array();
		foreach($variables as $name => $value)
		{
			$variable = new ActivitiStartProcessInstanceRequestVariable();
			$variable->setName($name);
			$variable->setValue($value);
			$startVariables[] = $variable;
		}
		
		$response = $this->client->processInstances->startProcessInstance(null, null, $startVariables, $processId);
		if($response)
			return $response->getId();
			
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::getCase()
	 */
	public function getCase($caseId)
	{
		$processInstance = $this->client->processInstances->getProcessInstance($caseId);
		
		$case = new vBusinessProcessCase();
		$case->setId($processInstance->getId());
		$case->setBusinessProcessId($processInstance->getBusinesskey());
		$case->setActivityId($processInstance->getActivityid());
		$case->setSuspended($processInstance->getSuspended());
		
		return $case;
	}
	
	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::abortCase()
	 */
	public function abortCase($caseId)
	{
		$processInstances = $this->client->executions->queryExecutions($caseId, null, null);
		
		$action = 'messageEventReceived';
		$processDeleted = false;
		
		foreach($processInstances->getData() as $processInstance)
		{
			/* @var $processInstance ActivitiQueryExecutionsResponseData */
			try 
			{
				$this->client->processInstances->deleteProcessInstance($processInstance->getId());
				$processDeleted = true;
			}
			catch (Exception $e)
			{
				VidiunLog::err($e);
			}
		}
		
		if(!$processDeleted)
		{
			throw new Exception("Process case [$caseId] not found");
		}
	}

	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::signalCase()
	 */
	public function signalCase($caseId, $eventId, $message, array $variables = array())
	{
		$messageVariables = array();
		foreach($variables as $name => $value)
		{
			$variable = new ActivitiStartProcessInstanceRequestVariable();
			$variable->setName($name);
			$variable->setValue($value);
			$messageVariables[] = $variable;
		}
		
		$processInstances = $this->client->executions->queryExecutions($caseId, null, null);
		
		$action = 'messageEventReceived';
		
		foreach($processInstances->getData() as $processInstance)
		{
			/* @var $processInstance ActivitiQueryExecutionsResponseData */
			if($processInstance->getActivityid() === $eventId)
			{
				$this->client->executions->executeAnActionOnAnExecution($processInstance->getId(), $action, null, $messageVariables, $message);
			}
		}
	}

	/* (non-PHPdoc)
	 * @see vBusinessProcessProvider::getCaseDiagramUrl()
	 */
	public function getCaseDiagram($caseId, $filename)
	{
		vFileUtils::fullMkdir($filename);
		vFile::setFileContent($filename, $this->client->processInstances->getDiagramForProcessInstance($caseId));
	}
}
