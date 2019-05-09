<?php
/**
 * @package Scheduler
 * @subpackage Cleanup
 */

/**
 * Will run periodically and cleanup directories from old files that have a specific pattern (older than x days)
 *
 * @package Scheduler
 * @subpackage Cleanup
 */
class VAsyncDirectoryCleanup extends VPeriodicWorker
{
	/* (non-PHPdoc)
	 * @see VBatchBase::getType()
	 */
	public static function getType()
	{
		return VidiunBatchJobType::CLEANUP;
	}

	/* (non-PHPdoc)
	 * @see VBatchBase::run()
	*/
	public function run($jobs = null)
	{
		$path = $this->getAdditionalParams("path");
		$pattern = $this->getAdditionalParams("pattern");
		$simulateOnly = $this->getAdditionalParams("simulateOnly");
		$minutesOld = $this->getAdditionalParams("minutesOld");
		$searchPath = $path . $pattern;
		VidiunLog::info("Searching [$searchPath]");

		if($this->getAdditionalParams("usePHP"))
		{
			$this->deleteFilesPHP($searchPath, $minutesOld, $simulateOnly);
		}
		else
		{
			$this->deleteFilesLinux($searchPath, $minutesOld, $simulateOnly);
		}
	}

	// XXX - If this function forces deletion of files in the given directory. If it is used with params
	// given from the user - Please add input validation.
	protected function deleteFilesLinux($searchPath, $minutesOld, $simulateOnly)
	{
		$command = "find $searchPath -mmin +$minutesOld -exec rm -rf {} \;";
		VidiunLog::info("Executing command: $command");

		$returnedValue = null;
		passthru($command, $returnedValue);
		VidiunLog::info("Returned value [$returnedValue]");
	}

	protected function deleteFilesPHP($searchPath, $minutesOld, $simulateOnly)
	{
		$secondsOld = $minutesOld * 60;

		$files = glob ( $searchPath);
		VidiunLog::info("Found [" . count ( $files ) . "] to scan");

		$now = time();
		VidiunLog::info("Deleting files that are " . $secondsOld ." seconds old (modified before " . date('c', $now - $secondsOld) . ")");
		$deletedCount = 0;
		foreach ( $files as $file )
		{
			$filemtime = filemtime($file);
			if ($filemtime > $now - $secondsOld)
				continue;

			if ( $simulateOnly )
			{
				VidiunLog::info( "Simulating: Deleting file [$file], it's last modification time was " . date('c', $filemtime));
				continue;
			}

			VidiunLog::info("Deleting file [$file], it's last modification time was " . date('c', $filemtime));
			$res = @unlink ( $file );
			if ( ! $res ){
				VidiunLog::err("Error: problem while deleting [$file]");
				continue;
			}
			$deletedCount++;
		}
	}
}
