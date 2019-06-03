
var vObjects = {
	coreObjectType: {
		label: 			'Event',
		subSelections:	{
			baseEntry:						{label: 'Base Entry', coreType: 'entry', apiType: 'VidiunBaseEntry'},
			dataEntry:						{label: 'Data Entry', coreType: 'entry', apiType: 'VidiunDataEntry'},
			documentEntry:					{label: 'Document Entry', coreType: 'entry', apiType: 'VidiunDocumentEntry'},
			mediaEntry:						{label: 'Media Entry', coreType: 'entry', apiType: 'VidiunMediaEntry'},
			externalMediaEntry:				{label: 'External Media Entry', coreType: 'entry', apiType: 'VidiunExternalMediaEntry'},
			liveStreamEntry:				{label: 'Live Stream Entry', coreType: 'entry', apiType: 'VidiunLiveStreamEntry'},
			playlist:						{label: 'Playlist', coreType: 'entry', apiType: 'VidiunPlaylist'},
			category:						{label:	'Category', apiType: 'VidiunCategory'},
			vuser:							{label:	'User', apiType: 'VidiunUser'},
	        CuePoint:						{label:	'CuePoint', apiType: 'VidiunCuePoint'},
	        AdCuePoint:						{label:	'Ad Cue-Point', apiType: 'VidiunAdCuePoint'},
	        Annotation:						{label:	'Annotation', apiType: 'VidiunAnnotation'},
	        CodeCuePoint:					{label:	'Code Cue-Point', apiType: 'VidiunCodeCuePoint'},
	        DistributionProfile:			{label:	'Distribution Profile', apiType: 'VidiunDistributionProfile'},
	        EntryDistribution:				{label:	'Entry Distribution', apiType: 'VidiunEntryDistribution'},
	        Metadata:						{label:	'Metadata', apiType: 'VidiunMetadata'},
	        asset:							{label:	'Asset', apiType: 'VidiunAsset'},
	        attachmentAsset:				{label: 'AttachmentAsset', apiType: 'VidiunAttachmentAsset'},
	        flavorAsset:					{label:	'Flavor Asset', apiType: 'VidiunFlavorAsset'},
	        thumbAsset:						{label:	'Thumbnail Asset', apiType: 'VidiunThumbAsset'},
	        accessControl:					{label:	'Access Control', apiType: 'VidiunAccessControlProfile'},
	        BatchJob:						{label:	'BatchJob', apiType: 'VidiunBatchJob'},
	        BulkUploadResultEntry:			{label:	'Bulk-Upload Entry Result', apiType: 'VidiunBulkUploadResultEntry'},
	        BulkUploadResultCategory:		{label:	'Bulk-Upload Category Result', apiType: 'VidiunBulkUploadResultCategory'},
	        BulkUploadResultVuser:			{label:	'Bulk-Upload User Result', apiType: 'VidiunBulkUploadResultUser'},
	        BulkUploadResultCategoryVuser:	{label:	'Bulk-Upload Category - User Result', apiType: 'VidiunBulkUploadResultCategoryUser'},
	        categoryVuser:					{label:	'Category - User', apiType: 'VidiunCategoryUser'},
	        conversionProfile2:				{label:	'Conversion Profile', apiType: 'VidiunConversionProfile'},
	        flavorParams:					{label:	'Flavor Params', apiType: 'VidiunFlavorParams'},
	        flavorParamsConversionProfile:	{label:	'Asset Params - Conversion Profile', apiType: 'VidiunConversionProfileAssetParams'},
	        flavorParamsOutput:				{label:	'Flavor Params Output', apiType: 'VidiunFlavorParamsOutput'},
	        genericsynDicationFeed:			{label:	'Genericsyn Dication Feed', apiType: 'VidiunGenericsynDicationFeed'},
	        Partner:						{label:	'Partner', apiType: 'VidiunPartner'},
	        Permission:						{label:	'Permission', apiType: 'VidiunPermission'},
	        PermissionItem:					{label:	'Permission Item', apiType: 'VidiunPermissionItem'},
	        Scheduler:						{label:	'Scheduler', apiType: 'VidiunScheduler'},
	        SchedulerConfig:				{label:	'Scheduler Config', apiType: 'VidiunSchedulerConfig'},
	        SchedulerStatus:				{label:	'Scheduler Status', apiType: 'VidiunSchedulerStatus'},
	        SchedulerWorker:				{label:	'Scheduler Worker', apiType: 'VidiunSchedulerWorker'},
	        StorageProfile:					{label:	'Storage Profile', apiType: 'VidiunStorageProfile'},
	        thumbParams:					{label:	'Thumbnail Params', apiType: 'VidiunThumbParams'},
	        thumbParamsOutput:				{label:	'Thumbnail Params Output', apiType: 'VidiunThumbParamsOutput'},
	        UploadToken:					{label:	'Upload Token', apiType: 'VidiunUploadToken'},
	        UserLoginData:					{label:	'User Login Data', apiType: 'VidiunUserLoginData'},
	        UserRole:						{label:	'User Role', apiType: 'VidiunUserRole'},
	        widget:							{label:	'Widget', apiType: 'VidiunWidget'},
	        categoryEntry:					{label:	'Category - Entry', apiType: 'VidiunCategoryEntry'}
		},
		subLabel:		'Select Object Type',
		getData:		function(subCode, variables){
							var coreType = variables.value;
							if(variables.coreType != null)
								coreType = variables.coreType;
								
							var ret = {
								code: '(($scope->getEvent()->getObject() instanceof ' + coreType + ') ? $scope->getEvent()->getObject() : null)',
								coreType: coreType
							};
							
							if(variables.apiType != null)
								ret.apiName = variables.apiType;
								
							return ret;
		}
	}
};
