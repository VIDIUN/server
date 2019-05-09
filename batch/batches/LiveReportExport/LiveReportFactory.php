<?php

class LiveReportFactory {
	
	public function getExporter($type, VidiunLiveReportExportJobData $jobData) {
		
		$exporter = null;
		switch ($type) {
			case VidiunLiveReportExportType::PARTNER_TOTAL_ALL :
				$exporter = new PartnerTotalAllExporter($jobData);
				break;
			case VidiunLiveReportExportType::PARTNER_TOTAL_LIVE :
				$exporter = new PartnerTotalLiveExporter($jobData);
				break;
			case VidiunLiveReportExportType::ENTRY_TIME_LINE_ALL :
				$exporter = new EntryTimeLineAllExporter($jobData);
				break;
			case VidiunLiveReportExportType::ENTRY_TIME_LINE_LIVE :
				$exporter = new EntryTimeLineLiveExporter ($jobData);
				break;
			case VidiunLiveReportExportType::LOCATION_ALL :
				$exporter = new LocationAllExporter($jobData);
				break;
			case VidiunLiveReportExportType::LOCATION_LIVE :
				$exporter = new LocationLiveExporter($jobData);
				break;
			case VidiunLiveReportExportType::SYNDICATION_ALL :
				$exporter = new SyndicationAllExporter($jobData);
				break;
			case VidiunLiveReportExportType::SYNDICATION_LIVE :
				$exporter = new SyndicationLiveExporter($jobData);
				break;
			default:
				throw new VOperationEngineException("Unknown Exporter type : " . $type);
		}
		
		$exporter->init($jobData);
		
		return $exporter;
	}
}
