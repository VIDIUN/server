<?php

class UserBulkUploadCsv 
{
	const JOB_STATUS_CODE_OK = 0;
	const JOB_STATUS_CODE_WARNING = 1;
	const JOB_STATUS_CODE_ERROR = 2;
	
	public $bulkError;
	public $monitorDescription;
	
	function getBulkMonitorResult($client, $config) 
	{
			$monitorResult = new VidiunMonitorResult();
			$apiCall = null;

			try
			{
				$apiCall = 'session.start';
				$start = microtime(true);
				$vs = $client->session->start($config['monitor-partner']['secret'], 'monitor-user', VidiunSessionType::USER, $config['monitor-partner']['id']);
				$client->setVs($vs);
					
				$userId_1 = uniqid('monitor-user1');
				$userId_2 = uniqid('monitor-user2');
				// create add users csv
				$csvPath = tempnam(sys_get_temp_dir(), 'csv');
				$csvData = array(
					array(
						"*action" => VidiunBulkUploadAction::ADD,
						"userId" => $userId_1,
						"screenName" => "monitor-user1",
						"firstName" => "monitor",
						"lastName" => "user1",
						"email" => "monitor@user1.com",
						"tags" => "monitor,user1",
					),
					array(
						"*action" => VidiunBulkUploadAction::ADD,
						"userId" => $userId_2,
						"screenName" => "monitor-user2",
						"firstName" => "monitor",
						"lastName" => "user2",
						"email" => "monitor@user2.com",
						"tags" => "monitor,user2",
					),
				);


				$f = fopen($csvPath, 'w');
				fputcsv($f, array_keys(reset($csvData)));
				foreach ($csvData as $csvLine)
					fputcsv($f, $csvLine);
				fclose($f);
				
				$this->bulkError = null;
				$this->monitorDescription = '';
				$bulkStatus;
				$apiCall = 'user.addFromBulkUpload';
				$bulkUpload = $client->user->addFromBulkUpload($csvPath);
				/* @var $bulkUpload VidiunBulkUpload */

				$bulkUploadPlugin = VidiunBulkUploadClientPlugin::get($client);
				$bulkStatus = self::getBulkJobStatus("Add", $bulkUpload, $bulkUploadPlugin);
				
				if ($this->bulkError) {
					$bulkStatus = self::JOB_STATUS_CODE_ERROR;
					
					$error = new VidiunMonitorError();
					$error->description = "Add: " . $this->bulkError;
					$error->level = VidiunMonitorError::ERR;
				
					$monitorResult->errors[] = $error;
					$this->monitorDescription = $error->description;
				}
				//update user
				else 
				{
					// create update users csv
					$csvPath = tempnam(sys_get_temp_dir(), 'csv');
					$csvData = array(
						array(
							"*action" => VidiunBulkUploadAction::UPDATE,
							"userId" => $userId_1,
							"screenName" => "monitor-user1-update",
						),
						array(
							"*action" => VidiunBulkUploadAction::UPDATE,
							"userId" => $userId_2,
							"screenName" => "monitor-user2-update",
						),
					);
					$f = fopen($csvPath, 'w');
					fputcsv($f, array_keys(reset($csvData)));
					foreach ($csvData as $csvLine)
						fputcsv($f, $csvLine);
					fclose($f);
					
					$this->bulkError = null;
					$apiCall = 'user.addFromBulkUpload';
					$bulkUpload = $client->user->addFromBulkUpload($csvPath);
					/* @var $bulkUpload VidiunBulkUpload */

					$bulkUploadPlugin = VidiunBulkUploadClientPlugin::get($client);
					$bulkStatus = self::getBulkJobStatus("Update", $bulkUpload, $bulkUploadPlugin);
					
					if ($this->bulkError) {
						$bulkStatus = self::JOB_STATUS_CODE_ERROR;
						
						$error = new VidiunMonitorError();
						$error->description = 'Update: ' . $this->bulkError;
						$error->level = VidiunMonitorError::ERR;
					
						$monitorResult->errors[] = $error;
						$this->monitorDescription .= $error->description;
					}
					else {
						// create delete users csv
						$csvPath = tempnam(sys_get_temp_dir(), 'csv');
						$csvData = array(
							array(
								"*action" => VidiunBulkUploadAction::DELETE,
								"userId" => $userId_1,
							),
							array(
								"*action" => VidiunBulkUploadAction::DELETE,
								"userId" => $userId_2,
							),
						);

						$f = fopen($csvPath, 'w');
						fputcsv($f, array_keys(reset($csvData)));
						foreach ($csvData as $csvLine)
							fputcsv($f, $csvLine);
						fclose($f);
						
						$this->bulkError = null;
						$apiCall = 'user.addFromBulkUpload';
						$bulkUpload = $client->user->addFromBulkUpload($csvPath);
						/* @var $bulkUpload VidiunBulkUpload */


						$bulkUploadPlugin = VidiunBulkUploadClientPlugin::get($client);
						$bulkStatus = self::getBulkJobStatus("Delete", $bulkUpload, $bulkUploadPlugin);
						
						if ($this->bulkError) {
							$bulkStatus = self::JOB_STATUS_CODE_ERROR;
							
							$error = new VidiunMonitorError();
							$error->description = 'Delete: ' . $this->bulkError;
							$error->level = VidiunMonitorError::ERR;
						
							$monitorResult->errors[] = $error;
							$this->monitorDescription .= $error->description;
						}
					}
				}

				$monitorResult->executionTime = microtime(true) - $start;
				$monitorResult->value = $bulkStatus;
				$monitorResult->description = $this->monitorDescription;
			}	
			catch(VidiunException $e)
			{
				$end = microtime(true);
				$monitorResult->executionTime = $end - $start;
				
				$error = new VidiunMonitorError();
				$error->code = $e->getCode();
				$error->description = $e->getMessage();
				$error->level = VidiunMonitorError::ERR;
				
				$monitorResult->errors[] = $error;
				$monitorResult->description = "Exception: " . get_class($e) . ", API: $apiCall, Code: " . $e->getCode() . ", Message: " . $e->getMessage();
			}
			catch(VidiunClientException $ce)
			{
				$end = microtime(true);
				$monitorResult->executionTime = $end - $start;
				
				$error = new VidiunMonitorError();
				$error->code = $ce->getCode();
				$error->description = $ce->getMessage();
				$error->level = VidiunMonitorError::CRIT;
				
				$monitorResult->errors[] = $error;
				$monitorResult->description = "Exception: " . get_class($ce) . ", API: $apiCall, Code: " . $ce->getCode() . ", Message: " . $ce->getMessage();
			}
			
			return $monitorResult;

	}
	
	function getBulkJobStatus($action, $bulkUpload, $bulkUploadPlugin)
	{
		$bulkStatus = null;
		while($bulkUpload)
		{
			if($bulkUpload->status == VidiunBatchJobStatus::FINISHED)
			{
				$bulkStatus = self::JOB_STATUS_CODE_OK;
				$this->monitorDescription .= "Users Bulk Upload $action Job was finished successfully\n";
				break;
			}
			if($bulkUpload->status == VidiunBatchJobStatus::FINISHED_PARTIALLY)
			{
				$bulkStatus = self::JOB_STATUS_CODE_WARNING;
				$this->monitorDescription .= "Users Bulk Upload $action Job Finished, but with some errors\n";
				break;
			}
			if($bulkUpload->status == VidiunBatchJobStatus::FAILED)
			{
				$this->bulkError =  "Bulk upload [$bulkUpload->id] failed";
				break;
			}
			if($bulkUpload->status == VidiunBatchJobStatus::ABORTED)
			{
				$this->bulkError = "Bulk upload [$bulkUpload->id] aborted";
				break;
			}
			if($bulkUpload->status == VidiunBatchJobStatus::FATAL)
			{
				$this->bulkError = "Bulk upload [$bulkUpload->id] failed fataly";
				break;
			}
			
			sleep(15);
			$bulkUpload = $bulkUploadPlugin->bulk->get($bulkUpload->id);
		}
		if(!$bulkUpload)
		{
				 $this->bulkError = "$action Bulk upload not found";
		}
		return $bulkStatus;
	}

}

$config = array();
$client = null;
/* @var $client VidiunClient */
require_once __DIR__  . '/common.php';

$options = getopt('', array(
	'service-url:',
	'debug',
));


$userBulkUploadCsv = new UserBulkUploadCsv();
echo $userBulkUploadCsv->getBulkMonitorResult($client, $config);
exit(0);



