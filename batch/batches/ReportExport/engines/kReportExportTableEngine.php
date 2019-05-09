<?php
/**
 * @package Scheduler
 * @subpackage ReportExport
 */
class vReportExportTableEngine extends vReportExportEngine
{
	const MAX_CSV_RESULT_SIZE = 60000;
	
	public function createReport()
	{
		$pager = new VidiunFilterPager();
		$pager->pageIndex = 1;
		$pager->pageSize = self::MAX_CSV_RESULT_SIZE;

		$result =  VBatchBase::$vClient->report->getTable($this->reportItem->reportType, $this->reportItem->filter,
			$pager, $this->reportItem->order, $this->reportItem->objectIds, $this->reportItem->responseOptions);
		return $this->buildCsv($result);
	}

	protected function buildCsv($result)
	{
		$this->writeReportTitle();
		$this->writeDelimitedRow($result->header);

		$rows = explode(';', $result->data);
		foreach ($rows as $row)
		{
			$this->writeDelimitedRow($row);
		}
		fclose($this->fp);
		return $this->filename;
	}

}
