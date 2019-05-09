<?php
/**
 * @package Scheduler
 * @subpackage ReportExport
 */
class ReportExportFactory
{
	public static function getEngine($reportItem, $outputPath)
	{
		switch ($reportItem->action)
		{
			case VidiunReportExportItemType::TABLE:
				return new vReportExportTableEngine($reportItem, $outputPath);
			case VidiunReportExportItemType::GRAPH:
				return new vReportExportGraphEngine($reportItem, $outputPath);
			case VidiunReportExportItemType::TOTAL:
				return new vReportExportTotalEngine($reportItem, $outputPath);
			default:
				return null;
		}
	}

}
