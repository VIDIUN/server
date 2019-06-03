function VidiunAccessControlOrderBy()
{
}
VidiunAccessControlOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunAccessControlOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunAudioCodec()
{
}
VidiunAudioCodec.prototype.NONE = "";
VidiunAudioCodec.prototype.MP3 = "mp3";
VidiunAudioCodec.prototype.AAC = "aac";

function VidiunBaseEntryOrderBy()
{
}
VidiunBaseEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunBaseEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunBaseEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunBaseEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunBaseEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunBaseEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunBaseEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunBaseEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunBaseJobOrderBy()
{
}
VidiunBaseJobOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunBaseJobOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunBaseJobOrderBy.prototype.EXECUTION_ATTEMPTS_ASC = "+executionAttempts";
VidiunBaseJobOrderBy.prototype.EXECUTION_ATTEMPTS_DESC = "-executionAttempts";

function VidiunBaseSyndicationFeedOrderBy()
{
}
VidiunBaseSyndicationFeedOrderBy.prototype.PLAYLIST_ID_ASC = "+playlistId";
VidiunBaseSyndicationFeedOrderBy.prototype.PLAYLIST_ID_DESC = "-playlistId";
VidiunBaseSyndicationFeedOrderBy.prototype.NAME_ASC = "+name";
VidiunBaseSyndicationFeedOrderBy.prototype.NAME_DESC = "-name";
VidiunBaseSyndicationFeedOrderBy.prototype.TYPE_ASC = "+type";
VidiunBaseSyndicationFeedOrderBy.prototype.TYPE_DESC = "-type";
VidiunBaseSyndicationFeedOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunBaseSyndicationFeedOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunBatchJobErrorTypes()
{
}
VidiunBatchJobErrorTypes.prototype.APP = 0;
VidiunBatchJobErrorTypes.prototype.RUNTIME = 1;
VidiunBatchJobErrorTypes.prototype.HTTP = 2;
VidiunBatchJobErrorTypes.prototype.CURL = 3;

function VidiunBatchJobOrderBy()
{
}
VidiunBatchJobOrderBy.prototype.STATUS_ASC = "+status";
VidiunBatchJobOrderBy.prototype.STATUS_DESC = "-status";
VidiunBatchJobOrderBy.prototype.QUEUE_TIME_ASC = "+queueTime";
VidiunBatchJobOrderBy.prototype.QUEUE_TIME_DESC = "-queueTime";
VidiunBatchJobOrderBy.prototype.FINISH_TIME_ASC = "+finishTime";
VidiunBatchJobOrderBy.prototype.FINISH_TIME_DESC = "-finishTime";
VidiunBatchJobOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunBatchJobOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunBatchJobOrderBy.prototype.EXECUTION_ATTEMPTS_ASC = "+executionAttempts";
VidiunBatchJobOrderBy.prototype.EXECUTION_ATTEMPTS_DESC = "-executionAttempts";

function VidiunBatchJobStatus()
{
}
VidiunBatchJobStatus.prototype.PENDING = 0;
VidiunBatchJobStatus.prototype.QUEUED = 1;
VidiunBatchJobStatus.prototype.PROCESSING = 2;
VidiunBatchJobStatus.prototype.PROCESSED = 3;
VidiunBatchJobStatus.prototype.MOVEFILE = 4;
VidiunBatchJobStatus.prototype.FINISHED = 5;
VidiunBatchJobStatus.prototype.FAILED = 6;
VidiunBatchJobStatus.prototype.ABORTED = 7;
VidiunBatchJobStatus.prototype.ALMOST_DONE = 8;
VidiunBatchJobStatus.prototype.RETRY = 9;
VidiunBatchJobStatus.prototype.FATAL = 10;

function VidiunBatchJobType()
{
}
VidiunBatchJobType.prototype.CONVERT = 0;
VidiunBatchJobType.prototype.IMPORT = 1;
VidiunBatchJobType.prototype.DELETE = 2;
VidiunBatchJobType.prototype.FLATTEN = 3;
VidiunBatchJobType.prototype.BULKUPLOAD = 4;
VidiunBatchJobType.prototype.DVDCREATOR = 5;
VidiunBatchJobType.prototype.DOWNLOAD = 6;
VidiunBatchJobType.prototype.OOCONVERT = 7;
VidiunBatchJobType.prototype.CONVERT_PROFILE = 10;
VidiunBatchJobType.prototype.POSTCONVERT = 11;
VidiunBatchJobType.prototype.PULL = 12;
VidiunBatchJobType.prototype.REMOTE_CONVERT = 13;
VidiunBatchJobType.prototype.EXTRACT_MEDIA = 14;
VidiunBatchJobType.prototype.MAIL = 15;
VidiunBatchJobType.prototype.NOTIFICATION = 16;
VidiunBatchJobType.prototype.CLEANUP = 17;
VidiunBatchJobType.prototype.SCHEDULER_HELPER = 18;
VidiunBatchJobType.prototype.BULKDOWNLOAD = 19;
VidiunBatchJobType.prototype.PROJECT = 1000;

function VidiunBulkUploadCsvVersion()
{
}
VidiunBulkUploadCsvVersion.prototype.V1 = "1";
VidiunBulkUploadCsvVersion.prototype.V2 = "2";

function VidiunCategoryOrderBy()
{
}
VidiunCategoryOrderBy.prototype.DEPTH_ASC = "+depth";
VidiunCategoryOrderBy.prototype.DEPTH_DESC = "-depth";
VidiunCategoryOrderBy.prototype.FULL_NAME_ASC = "+fullName";
VidiunCategoryOrderBy.prototype.FULL_NAME_DESC = "-fullName";
VidiunCategoryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunCategoryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunCommercialUseType()
{
}
VidiunCommercialUseType.prototype.COMMERCIAL_USE = "commercial_use";
VidiunCommercialUseType.prototype.NON_COMMERCIAL_USE = "non-commercial_use";

function VidiunContainerFormat()
{
}
VidiunContainerFormat.prototype.FLV = "flv";
VidiunContainerFormat.prototype.MP4 = "mp4";
VidiunContainerFormat.prototype.AVI = "avi";
VidiunContainerFormat.prototype.MOV = "mov";
VidiunContainerFormat.prototype._3GP = "3gp";

function VidiunConversionProfileOrderBy()
{
}
VidiunConversionProfileOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunConversionProfileOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunCountryRestrictionType()
{
}
VidiunCountryRestrictionType.prototype.RESTRICT_COUNTRY_LIST = 0;
VidiunCountryRestrictionType.prototype.ALLOW_COUNTRY_LIST = 1;

function VidiunDataEntryOrderBy()
{
}
VidiunDataEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunDataEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunDataEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunDataEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunDataEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunDataEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunDataEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunDataEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunDirectoryRestrictionType()
{
}
VidiunDirectoryRestrictionType.prototype.DONT_DISPLAY = 0;
VidiunDirectoryRestrictionType.prototype.DISPLAY_WITH_LINK = 1;

function VidiunDocumentEntryOrderBy()
{
}
VidiunDocumentEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunDocumentEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunDocumentEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunDocumentEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunDocumentEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunDocumentEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunDocumentEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunDocumentEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunDocumentType()
{
}
VidiunDocumentType.prototype.DOCUMENT = 11;
VidiunDocumentType.prototype.SWF = 12;

function VidiunDurationType()
{
}
VidiunDurationType.prototype.NOT_AVAILABLE = "notavailable";
VidiunDurationType.prototype.SHORT = "short";
VidiunDurationType.prototype.MEDIUM = "medium";
VidiunDurationType.prototype.LONG = "long";

function VidiunEditorType()
{
}
VidiunEditorType.prototype.SIMPLE = 1;
VidiunEditorType.prototype.ADVANCED = 2;

function VidiunEntryModerationStatus()
{
}
VidiunEntryModerationStatus.prototype.PENDING_MODERATION = 1;
VidiunEntryModerationStatus.prototype.APPROVED = 2;
VidiunEntryModerationStatus.prototype.REJECTED = 3;
VidiunEntryModerationStatus.prototype.FLAGGED_FOR_REVIEW = 5;
VidiunEntryModerationStatus.prototype.AUTO_APPROVED = 6;

function VidiunEntryStatus()
{
}
VidiunEntryStatus.prototype.ERROR_IMPORTING = -2;
VidiunEntryStatus.prototype.ERROR_CONVERTING = -1;
VidiunEntryStatus.prototype.IMPORT = 0;
VidiunEntryStatus.prototype.PRECONVERT = 1;
VidiunEntryStatus.prototype.READY = 2;
VidiunEntryStatus.prototype.DELETED = 3;
VidiunEntryStatus.prototype.PENDING = 4;
VidiunEntryStatus.prototype.MODERATE = 5;
VidiunEntryStatus.prototype.BLOCKED = 6;

function VidiunEntryType()
{
}
VidiunEntryType.prototype.AUTOMATIC = -1;
VidiunEntryType.prototype.MEDIA_CLIP = 1;
VidiunEntryType.prototype.MIX = 2;
VidiunEntryType.prototype.PLAYLIST = 5;
VidiunEntryType.prototype.DATA = 6;
VidiunEntryType.prototype.DOCUMENT = 10;

function VidiunFlavorAssetStatus()
{
}
VidiunFlavorAssetStatus.prototype.ERROR = -1;
VidiunFlavorAssetStatus.prototype.QUEUED = 0;
VidiunFlavorAssetStatus.prototype.CONVERTING = 1;
VidiunFlavorAssetStatus.prototype.READY = 2;
VidiunFlavorAssetStatus.prototype.DELETED = 3;
VidiunFlavorAssetStatus.prototype.NOT_APPLICABLE = 4;

function VidiunFlavorParamsOrderBy()
{
}

function VidiunFlavorParamsOutputOrderBy()
{
}

function VidiunGender()
{
}
VidiunGender.prototype.UNKNOWN = 0;
VidiunGender.prototype.MALE = 1;
VidiunGender.prototype.FEMALE = 2;

function VidiunGoogleSyndicationFeedAdultValues()
{
}
VidiunGoogleSyndicationFeedAdultValues.prototype.YES = "Yes";
VidiunGoogleSyndicationFeedAdultValues.prototype.NO = "No";

function VidiunGoogleVideoSyndicationFeedOrderBy()
{
}
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.PLAYLIST_ID_ASC = "+playlistId";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.PLAYLIST_ID_DESC = "-playlistId";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.NAME_ASC = "+name";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.NAME_DESC = "-name";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.TYPE_ASC = "+type";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.TYPE_DESC = "-type";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunGoogleVideoSyndicationFeedOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunITunesSyndicationFeedAdultValues()
{
}
VidiunITunesSyndicationFeedAdultValues.prototype.YES = "yes";
VidiunITunesSyndicationFeedAdultValues.prototype.NO = "no";
VidiunITunesSyndicationFeedAdultValues.prototype.CLEAN = "clean";

function VidiunITunesSyndicationFeedCategories()
{
}
VidiunITunesSyndicationFeedCategories.prototype.ARTS = "Arts";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_DESIGN = "Arts/Design";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_FASHION_BEAUTY = "Arts/Fashion &amp; Beauty";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_FOOD = "Arts/Food";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_LITERATURE = "Arts/Literature";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_PERFORMING_ARTS = "Arts/Performing Arts";
VidiunITunesSyndicationFeedCategories.prototype.ARTS_VISUAL_ARTS = "Arts/Visual Arts";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS = "Business";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS_BUSINESS_NEWS = "Business/Business News";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS_CAREERS = "Business/Careers";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS_INVESTING = "Business/Investing";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS_MANAGEMENT_MARKETING = "Business/Management &amp; Marketing";
VidiunITunesSyndicationFeedCategories.prototype.BUSINESS_SHOPPING = "Business/Shopping";
VidiunITunesSyndicationFeedCategories.prototype.COMEDY = "Comedy";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION = "Education";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION_TECHNOLOGY = "Education/Education Technology";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION_HIGHER_EDUCATION = "Education/Higher Education";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION_K_12 = "Education/K-12";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION_LANGUAGE_COURSES = "Education/Language Courses";
VidiunITunesSyndicationFeedCategories.prototype.EDUCATION_TRAINING = "Education/Training";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES = "Games &amp; Hobbies";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES_AUTOMOTIVE = "Games &amp; Hobbies/Automotive";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES_AVIATION = "Games &amp; Hobbies/Aviation";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES_HOBBIES = "Games &amp; Hobbies/Hobbies";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES_OTHER_GAMES = "Games &amp; Hobbies/Other Games";
VidiunITunesSyndicationFeedCategories.prototype.GAMES_HOBBIES_VIDEO_GAMES = "Games &amp; Hobbies/Video Games";
VidiunITunesSyndicationFeedCategories.prototype.GOVERNMENT_ORGANIZATIONS = "Government &amp; Organizations";
VidiunITunesSyndicationFeedCategories.prototype.GOVERNMENT_ORGANIZATIONS_LOCAL = "Government &amp; Organizations/Local";
VidiunITunesSyndicationFeedCategories.prototype.GOVERNMENT_ORGANIZATIONS_NATIONAL = "Government &amp; Organizations/National";
VidiunITunesSyndicationFeedCategories.prototype.GOVERNMENT_ORGANIZATIONS_NON_PROFIT = "Government &amp; Organizations/Non-Profit";
VidiunITunesSyndicationFeedCategories.prototype.GOVERNMENT_ORGANIZATIONS_REGIONAL = "Government &amp; Organizations/Regional";
VidiunITunesSyndicationFeedCategories.prototype.HEALTH = "Health";
VidiunITunesSyndicationFeedCategories.prototype.HEALTH_ALTERNATIVE_HEALTH = "Health/Alternative Health";
VidiunITunesSyndicationFeedCategories.prototype.HEALTH_FITNESS_NUTRITION = "Health/Fitness &amp; Nutrition";
VidiunITunesSyndicationFeedCategories.prototype.HEALTH_SELF_HELP = "Health/Self-Help";
VidiunITunesSyndicationFeedCategories.prototype.HEALTH_SEXUALITY = "Health/Sexuality";
VidiunITunesSyndicationFeedCategories.prototype.KIDS_FAMILY = "Kids &amp; Family";
VidiunITunesSyndicationFeedCategories.prototype.MUSIC = "Music";
VidiunITunesSyndicationFeedCategories.prototype.NEWS_POLITICS = "News &amp; Politics";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY = "Religion &amp; Spirituality";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_BUDDHISM = "Religion &amp; Spirituality/Buddhism";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_CHRISTIANITY = "Religion &amp; Spirituality/Christianity";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_HINDUISM = "Religion &amp; Spirituality/Hinduism";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_ISLAM = "Religion &amp; Spirituality/Islam";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_JUDAISM = "Religion &amp; Spirituality/Judaism";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_OTHER = "Religion &amp; Spirituality/Other";
VidiunITunesSyndicationFeedCategories.prototype.RELIGION_SPIRITUALITY_SPIRITUALITY = "Religion &amp; Spirituality/Spirituality";
VidiunITunesSyndicationFeedCategories.prototype.SCIENCE_MEDICINE = "Science &amp; Medicine";
VidiunITunesSyndicationFeedCategories.prototype.SCIENCE_MEDICINE_MEDICINE = "Science &amp; Medicine/Medicine";
VidiunITunesSyndicationFeedCategories.prototype.SCIENCE_MEDICINE_NATURAL_SCIENCES = "Science &amp; Medicine/Natural Sciences";
VidiunITunesSyndicationFeedCategories.prototype.SCIENCE_MEDICINE_SOCIAL_SCIENCES = "Science &amp; Medicine/Social Sciences";
VidiunITunesSyndicationFeedCategories.prototype.SOCIETY_CULTURE = "Society &amp; Culture";
VidiunITunesSyndicationFeedCategories.prototype.SOCIETY_CULTURE_HISTORY = "Society &amp; Culture/History";
VidiunITunesSyndicationFeedCategories.prototype.SOCIETY_CULTURE_PERSONAL_JOURNALS = "Society &amp; Culture/Personal Journals";
VidiunITunesSyndicationFeedCategories.prototype.SOCIETY_CULTURE_PHILOSOPHY = "Society &amp; Culture/Philosophy";
VidiunITunesSyndicationFeedCategories.prototype.SOCIETY_CULTURE_PLACES_TRAVEL = "Society &amp; Culture/Places &amp; Travel";
VidiunITunesSyndicationFeedCategories.prototype.SPORTS_RECREATION = "Sports &amp; Recreation";
VidiunITunesSyndicationFeedCategories.prototype.SPORTS_RECREATION_AMATEUR = "Sports &amp; Recreation/Amateur";
VidiunITunesSyndicationFeedCategories.prototype.SPORTS_RECREATION_COLLEGE_HIGH_SCHOOL = "Sports &amp; Recreation/College &amp; High School";
VidiunITunesSyndicationFeedCategories.prototype.SPORTS_RECREATION_OUTDOOR = "Sports &amp; Recreation/Outdoor";
VidiunITunesSyndicationFeedCategories.prototype.SPORTS_RECREATION_PROFESSIONAL = "Sports &amp; Recreation/Professional";
VidiunITunesSyndicationFeedCategories.prototype.TECHNOLOGY = "Technology";
VidiunITunesSyndicationFeedCategories.prototype.TECHNOLOGY_GADGETS = "Technology/Gadgets";
VidiunITunesSyndicationFeedCategories.prototype.TECHNOLOGY_TECH_NEWS = "Technology/Tech News";
VidiunITunesSyndicationFeedCategories.prototype.TECHNOLOGY_PODCASTING = "Technology/Podcasting";
VidiunITunesSyndicationFeedCategories.prototype.TECHNOLOGY_SOFTWARE_HOW_TO = "Technology/Software How-To";
VidiunITunesSyndicationFeedCategories.prototype.TV_FILM = "TV &amp; Film";

function VidiunITunesSyndicationFeedOrderBy()
{
}
VidiunITunesSyndicationFeedOrderBy.prototype.PLAYLIST_ID_ASC = "+playlistId";
VidiunITunesSyndicationFeedOrderBy.prototype.PLAYLIST_ID_DESC = "-playlistId";
VidiunITunesSyndicationFeedOrderBy.prototype.NAME_ASC = "+name";
VidiunITunesSyndicationFeedOrderBy.prototype.NAME_DESC = "-name";
VidiunITunesSyndicationFeedOrderBy.prototype.TYPE_ASC = "+type";
VidiunITunesSyndicationFeedOrderBy.prototype.TYPE_DESC = "-type";
VidiunITunesSyndicationFeedOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunITunesSyndicationFeedOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunLicenseType()
{
}
VidiunLicenseType.prototype.UNKNOWN = -1;
VidiunLicenseType.prototype.NONE = 0;
VidiunLicenseType.prototype.COPYRIGHTED = 1;
VidiunLicenseType.prototype.PUBLIC_DOMAIN = 2;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION = 3;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION_SHARE_ALIKE = 4;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION_NO_DERIVATIVES = 5;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL = 6;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_SHARE_ALIKE = 7;
VidiunLicenseType.prototype.CREATIVECOMMONS_ATTRIBUTION_NON_COMMERCIAL_NO_DERIVATIVES = 8;
VidiunLicenseType.prototype.GFDL = 9;
VidiunLicenseType.prototype.GPL = 10;
VidiunLicenseType.prototype.AFFERO_GPL = 11;
VidiunLicenseType.prototype.LGPL = 12;
VidiunLicenseType.prototype.BSD = 13;
VidiunLicenseType.prototype.APACHE = 14;
VidiunLicenseType.prototype.MOZILLA = 15;

function VidiunMailJobOrderBy()
{
}
VidiunMailJobOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunMailJobOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunMailJobOrderBy.prototype.EXECUTION_ATTEMPTS_ASC = "+executionAttempts";
VidiunMailJobOrderBy.prototype.EXECUTION_ATTEMPTS_DESC = "-executionAttempts";

function VidiunMailJobStatus()
{
}
VidiunMailJobStatus.prototype.PENDING = 1;
VidiunMailJobStatus.prototype.SENT = 2;
VidiunMailJobStatus.prototype.ERROR = 3;
VidiunMailJobStatus.prototype.QUEUED = 4;

function VidiunMailType()
{
}
VidiunMailType.prototype.MAIL_TYPE_VIDIUN_NEWSLETTER = 10;
VidiunMailType.prototype.MAIL_TYPE_ADDED_TO_FAVORITES = 11;
VidiunMailType.prototype.MAIL_TYPE_ADDED_TO_CLIP_FAVORITES = 12;
VidiunMailType.prototype.MAIL_TYPE_NEW_COMMENT_IN_PROFILE = 13;
VidiunMailType.prototype.MAIL_TYPE_CLIP_ADDED_YOUR_VIDIUN = 20;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_ADDED = 21;
VidiunMailType.prototype.MAIL_TYPE_ROUGHCUT_CREATED = 22;
VidiunMailType.prototype.MAIL_TYPE_ADDED_VIDIUN_TO_YOUR_FAVORITES = 23;
VidiunMailType.prototype.MAIL_TYPE_NEW_COMMENT_IN_VIDIUN = 24;
VidiunMailType.prototype.MAIL_TYPE_CLIP_ADDED = 30;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_CREATED = 31;
VidiunMailType.prototype.MAIL_TYPE_ADDED_VIDIUN_TO_HIS_FAVORITES = 32;
VidiunMailType.prototype.MAIL_TYPE_NEW_COMMENT_IN_VIDIUN_YOU_CONTRIBUTED = 33;
VidiunMailType.prototype.MAIL_TYPE_CLIP_CONTRIBUTED = 40;
VidiunMailType.prototype.MAIL_TYPE_ROUGHCUT_CREATED_SUBSCRIBED = 41;
VidiunMailType.prototype.MAIL_TYPE_ADDED_VIDIUN_TO_HIS_FAVORITES_SUBSCRIBED = 42;
VidiunMailType.prototype.MAIL_TYPE_NEW_COMMENT_IN_VIDIUN_YOU_SUBSCRIBED = 43;
VidiunMailType.prototype.MAIL_TYPE_REGISTER_CONFIRM = 50;
VidiunMailType.prototype.MAIL_TYPE_PASSWORD_RESET = 51;
VidiunMailType.prototype.MAIL_TYPE_LOGIN_MAIL_RESET = 52;
VidiunMailType.prototype.MAIL_TYPE_REGISTER_CONFIRM_VIDEO_SERVICE = 54;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_READY = 60;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_IS_READY = 62;
VidiunMailType.prototype.MAIL_TYPE_BULK_DOWNLOAD_READY = 63;
VidiunMailType.prototype.MAIL_TYPE_NOTIFY_ERR = 70;
VidiunMailType.prototype.MAIL_TYPE_ACCOUNT_UPGRADE_CONFIRM = 80;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_SERVICE_NOTICE = 81;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_SERVICE_NOTICE_LIMIT_REACHED = 82;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_SERVICE_NOTICE_ACCOUNT_LOCKED = 83;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_SERVICE_NOTICE_ACCOUNT_DELETED = 84;
VidiunMailType.prototype.MAIL_TYPE_VIDEO_SERVICE_NOTICE_UPGRADE_OFFER = 85;
VidiunMailType.prototype.MAIL_TYPE_ACCOUNT_REACTIVE_CONFIRM = 86;
VidiunMailType.prototype.MAIL_TYPE_SYSTEM_USER_RESET_PASSWORD = 110;
VidiunMailType.prototype.MAIL_TYPE_SYSTEM_USER_RESET_PASSWORD_SUCCESS = 111;

function VidiunPlayableEntryOrderBy()
{
}
VidiunPlayableEntryOrderBy.prototype.PLAYS_ASC = "+plays";
VidiunPlayableEntryOrderBy.prototype.PLAYS_DESC = "-plays";
VidiunPlayableEntryOrderBy.prototype.VIEWS_ASC = "+views";
VidiunPlayableEntryOrderBy.prototype.VIEWS_DESC = "-views";
VidiunPlayableEntryOrderBy.prototype.DURATION_ASC = "+duration";
VidiunPlayableEntryOrderBy.prototype.DURATION_DESC = "-duration";
VidiunPlayableEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunPlayableEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunPlayableEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunPlayableEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunPlayableEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunPlayableEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunPlayableEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunPlayableEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunMediaEntryOrderBy()
{
}
VidiunMediaEntryOrderBy.prototype.MEDIA_TYPE_ASC = "+mediaType";
VidiunMediaEntryOrderBy.prototype.MEDIA_TYPE_DESC = "-mediaType";
VidiunMediaEntryOrderBy.prototype.PLAYS_ASC = "+plays";
VidiunMediaEntryOrderBy.prototype.PLAYS_DESC = "-plays";
VidiunMediaEntryOrderBy.prototype.VIEWS_ASC = "+views";
VidiunMediaEntryOrderBy.prototype.VIEWS_DESC = "-views";
VidiunMediaEntryOrderBy.prototype.DURATION_ASC = "+duration";
VidiunMediaEntryOrderBy.prototype.DURATION_DESC = "-duration";
VidiunMediaEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunMediaEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunMediaEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunMediaEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunMediaEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunMediaEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunMediaEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunMediaEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunMediaType()
{
}
VidiunMediaType.prototype.VIDEO = 1;
VidiunMediaType.prototype.IMAGE = 2;
VidiunMediaType.prototype.AUDIO = 5;

function VidiunMixEntryOrderBy()
{
}
VidiunMixEntryOrderBy.prototype.PLAYS_ASC = "+plays";
VidiunMixEntryOrderBy.prototype.PLAYS_DESC = "-plays";
VidiunMixEntryOrderBy.prototype.VIEWS_ASC = "+views";
VidiunMixEntryOrderBy.prototype.VIEWS_DESC = "-views";
VidiunMixEntryOrderBy.prototype.DURATION_ASC = "+duration";
VidiunMixEntryOrderBy.prototype.DURATION_DESC = "-duration";
VidiunMixEntryOrderBy.prototype.NAME_ASC = "+name";
VidiunMixEntryOrderBy.prototype.NAME_DESC = "-name";
VidiunMixEntryOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunMixEntryOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunMixEntryOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunMixEntryOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunMixEntryOrderBy.prototype.RANK_ASC = "+rank";
VidiunMixEntryOrderBy.prototype.RANK_DESC = "-rank";

function VidiunModerationFlagStatus()
{
}
VidiunModerationFlagStatus.prototype.PENDING = 1;
VidiunModerationFlagStatus.prototype.MODERATED = 2;

function VidiunModerationFlagType()
{
}
VidiunModerationFlagType.prototype.SEXUAL_CONTENT = 1;
VidiunModerationFlagType.prototype.VIOLENT_REPULSIVE = 2;
VidiunModerationFlagType.prototype.HARMFUL_DANGEROUS = 3;
VidiunModerationFlagType.prototype.SPAM_COMMERCIALS = 4;

function VidiunModerationObjectType()
{
}
VidiunModerationObjectType.prototype.ENTRY = 2;
VidiunModerationObjectType.prototype.USER = 3;

function VidiunNotificationObjectType()
{
}
VidiunNotificationObjectType.prototype.ENTRY = 1;
VidiunNotificationObjectType.prototype.VSHOW = 2;
VidiunNotificationObjectType.prototype.USER = 3;
VidiunNotificationObjectType.prototype.BATCH_JOB = 4;

function VidiunNotificationOrderBy()
{
}
VidiunNotificationOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunNotificationOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunNotificationOrderBy.prototype.EXECUTION_ATTEMPTS_ASC = "+executionAttempts";
VidiunNotificationOrderBy.prototype.EXECUTION_ATTEMPTS_DESC = "-executionAttempts";

function VidiunNotificationStatus()
{
}
VidiunNotificationStatus.prototype.PENDING = 1;
VidiunNotificationStatus.prototype.SENT = 2;
VidiunNotificationStatus.prototype.ERROR = 3;
VidiunNotificationStatus.prototype.SHOULD_RESEND = 4;
VidiunNotificationStatus.prototype.ERROR_RESENDING = 5;
VidiunNotificationStatus.prototype.SENT_SYNCH = 6;
VidiunNotificationStatus.prototype.QUEUED = 7;

function VidiunNotificationType()
{
}
VidiunNotificationType.prototype.ENTRY_ADD = 1;
VidiunNotificationType.prototype.ENTR_UPDATE_PERMISSIONS = 2;
VidiunNotificationType.prototype.ENTRY_DELETE = 3;
VidiunNotificationType.prototype.ENTRY_BLOCK = 4;
VidiunNotificationType.prototype.ENTRY_UPDATE = 5;
VidiunNotificationType.prototype.ENTRY_UPDATE_THUMBNAIL = 6;
VidiunNotificationType.prototype.ENTRY_UPDATE_MODERATION = 7;
VidiunNotificationType.prototype.USER_ADD = 21;
VidiunNotificationType.prototype.USER_BANNED = 26;

function VidiunNullableBoolean()
{
}
VidiunNullableBoolean.prototype.NULL_VALUE = -1;
VidiunNullableBoolean.prototype.FALSE_VALUE = 0;
VidiunNullableBoolean.prototype.TRUE_VALUE = 1;

function VidiunPartnerOrderBy()
{
}
VidiunPartnerOrderBy.prototype.ID_ASC = "+id";
VidiunPartnerOrderBy.prototype.ID_DESC = "-id";
VidiunPartnerOrderBy.prototype.NAME_ASC = "+name";
VidiunPartnerOrderBy.prototype.NAME_DESC = "-name";
VidiunPartnerOrderBy.prototype.WEBSITE_ASC = "+website";
VidiunPartnerOrderBy.prototype.WEBSITE_DESC = "-website";
VidiunPartnerOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunPartnerOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunPartnerOrderBy.prototype.ADMIN_NAME_ASC = "+adminName";
VidiunPartnerOrderBy.prototype.ADMIN_NAME_DESC = "-adminName";
VidiunPartnerOrderBy.prototype.ADMIN_EMAIL_ASC = "+adminEmail";
VidiunPartnerOrderBy.prototype.ADMIN_EMAIL_DESC = "-adminEmail";
VidiunPartnerOrderBy.prototype.STATUS_ASC = "+status";
VidiunPartnerOrderBy.prototype.STATUS_DESC = "-status";

function VidiunPartnerType()
{
}
VidiunPartnerType.prototype.VMC = 1;
VidiunPartnerType.prototype.WIKI = 100;
VidiunPartnerType.prototype.WORDPRESS = 101;
VidiunPartnerType.prototype.DRUPAL = 102;
VidiunPartnerType.prototype.DEKIWIKI = 103;
VidiunPartnerType.prototype.MOODLE = 104;
VidiunPartnerType.prototype.COMMUNITY_EDITION = 105;
VidiunPartnerType.prototype.JOOMLA = 106;

function VidiunPlaylistOrderBy()
{
}
VidiunPlaylistOrderBy.prototype.NAME_ASC = "+name";
VidiunPlaylistOrderBy.prototype.NAME_DESC = "-name";
VidiunPlaylistOrderBy.prototype.MODERATION_COUNT_ASC = "+moderationCount";
VidiunPlaylistOrderBy.prototype.MODERATION_COUNT_DESC = "-moderationCount";
VidiunPlaylistOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunPlaylistOrderBy.prototype.CREATED_AT_DESC = "-createdAt";
VidiunPlaylistOrderBy.prototype.RANK_ASC = "+rank";
VidiunPlaylistOrderBy.prototype.RANK_DESC = "-rank";

function VidiunPlaylistType()
{
}
VidiunPlaylistType.prototype.DYNAMIC = 10;
VidiunPlaylistType.prototype.STATIC_LIST = 3;
VidiunPlaylistType.prototype.EXTERNAL = 101;

function VidiunReportType()
{
}
VidiunReportType.prototype.TOP_CONTENT = 1;
VidiunReportType.prototype.CONTENT_DROPOFF = 2;
VidiunReportType.prototype.CONTENT_INTERACTIONS = 3;
VidiunReportType.prototype.MAP_OVERLAY = 4;
VidiunReportType.prototype.TOP_CONTRIBUTORS = 5;
VidiunReportType.prototype.TOP_SYNDICATION = 6;
VidiunReportType.prototype.CONTENT_CONTRIBUTIONS = 7;
VidiunReportType.prototype.WIDGETS_STATS = 8;

function VidiunSearchProviderType()
{
}
VidiunSearchProviderType.prototype.FLICKR = 3;
VidiunSearchProviderType.prototype.YOUTUBE = 4;
VidiunSearchProviderType.prototype.MYSPACE = 7;
VidiunSearchProviderType.prototype.PHOTOBUCKET = 8;
VidiunSearchProviderType.prototype.JAMENDO = 9;
VidiunSearchProviderType.prototype.CCMIXTER = 10;
VidiunSearchProviderType.prototype.NYPL = 11;
VidiunSearchProviderType.prototype.CURRENT = 12;
VidiunSearchProviderType.prototype.MEDIA_COMMONS = 13;
VidiunSearchProviderType.prototype.VIDIUN = 20;
VidiunSearchProviderType.prototype.VIDIUN_USER_CLIPS = 21;
VidiunSearchProviderType.prototype.ARCHIVE_ORG = 22;
VidiunSearchProviderType.prototype.VIDIUN_PARTNER = 23;
VidiunSearchProviderType.prototype.METACAFE = 24;
VidiunSearchProviderType.prototype.SEARCH_PROXY = 28;

function VidiunSessionType()
{
}
VidiunSessionType.prototype.USER = 0;
VidiunSessionType.prototype.ADMIN = 2;

function VidiunSiteRestrictionType()
{
}
VidiunSiteRestrictionType.prototype.RESTRICT_SITE_LIST = 0;
VidiunSiteRestrictionType.prototype.ALLOW_SITE_LIST = 1;

function VidiunSourceType()
{
}
VidiunSourceType.prototype.FILE = 1;
VidiunSourceType.prototype.WEBCAM = 2;
VidiunSourceType.prototype.URL = 5;
VidiunSourceType.prototype.SEARCH_PROVIDER = 6;

function VidiunStatsEventType()
{
}
VidiunStatsEventType.prototype.WIDGET_LOADED = 1;
VidiunStatsEventType.prototype.MEDIA_LOADED = 2;
VidiunStatsEventType.prototype.PLAY = 3;
VidiunStatsEventType.prototype.PLAY_REACHED_25 = 4;
VidiunStatsEventType.prototype.PLAY_REACHED_50 = 5;
VidiunStatsEventType.prototype.PLAY_REACHED_75 = 6;
VidiunStatsEventType.prototype.PLAY_REACHED_100 = 7;
VidiunStatsEventType.prototype.OPEN_EDIT = 8;
VidiunStatsEventType.prototype.OPEN_VIRAL = 9;
VidiunStatsEventType.prototype.OPEN_DOWNLOAD = 10;
VidiunStatsEventType.prototype.OPEN_REPORT = 11;
VidiunStatsEventType.prototype.BUFFER_START = 12;
VidiunStatsEventType.prototype.BUFFER_END = 13;
VidiunStatsEventType.prototype.OPEN_FULL_SCREEN = 14;
VidiunStatsEventType.prototype.CLOSE_FULL_SCREEN = 15;
VidiunStatsEventType.prototype.REPLAY = 16;
VidiunStatsEventType.prototype.SEEK = 17;
VidiunStatsEventType.prototype.OPEN_UPLOAD = 18;
VidiunStatsEventType.prototype.SAVE_PUBLISH = 19;
VidiunStatsEventType.prototype.CLOSE_EDITOR = 20;
VidiunStatsEventType.prototype.PRE_BUMPER_PLAYED = 21;
VidiunStatsEventType.prototype.POST_BUMPER_PLAYED = 22;
VidiunStatsEventType.prototype.BUMPER_CLICKED = 23;
VidiunStatsEventType.prototype.FUTURE_USE_1 = 24;
VidiunStatsEventType.prototype.FUTURE_USE_2 = 25;
VidiunStatsEventType.prototype.FUTURE_USE_3 = 26;

function VidiunStatsVmcEventType()
{
}
VidiunStatsVmcEventType.prototype.CONTENT_PAGE_VIEW = 1001;
VidiunStatsVmcEventType.prototype.CONTENT_ADD_PLAYLIST = 1010;
VidiunStatsVmcEventType.prototype.CONTENT_EDIT_PLAYLIST = 1011;
VidiunStatsVmcEventType.prototype.CONTENT_DELETE_PLAYLIST = 1012;
VidiunStatsVmcEventType.prototype.CONTENT_DELETE_ITEM = 1058;
VidiunStatsVmcEventType.prototype.CONTENT_EDIT_ENTRY = 1013;
VidiunStatsVmcEventType.prototype.CONTENT_CHANGE_THUMBNAIL = 1014;
VidiunStatsVmcEventType.prototype.CONTENT_ADD_TAGS = 1015;
VidiunStatsVmcEventType.prototype.CONTENT_REMOVE_TAGS = 1016;
VidiunStatsVmcEventType.prototype.CONTENT_ADD_ADMIN_TAGS = 1017;
VidiunStatsVmcEventType.prototype.CONTENT_REMOVE_ADMIN_TAGS = 1018;
VidiunStatsVmcEventType.prototype.CONTENT_DOWNLOAD = 1019;
VidiunStatsVmcEventType.prototype.CONTENT_APPROVE_MODERATION = 1020;
VidiunStatsVmcEventType.prototype.CONTENT_REJECT_MODERATION = 1021;
VidiunStatsVmcEventType.prototype.CONTENT_BULK_UPLOAD = 1022;
VidiunStatsVmcEventType.prototype.CONTENT_ADMIN_VCW_UPLOAD = 1023;
VidiunStatsVmcEventType.prototype.CONTENT_CONTENT_GO_TO_PAGE = 1057;
VidiunStatsVmcEventType.prototype.ACCOUNT_CHANGE_PARTNER_INFO = 1030;
VidiunStatsVmcEventType.prototype.ACCOUNT_CHANGE_LOGIN_INFO = 1031;
VidiunStatsVmcEventType.prototype.ACCOUNT_CONTACT_US_USAGE = 1032;
VidiunStatsVmcEventType.prototype.ACCOUNT_UPDATE_SERVER_SETTINGS = 1033;
VidiunStatsVmcEventType.prototype.ACCOUNT_ACCOUNT_OVERVIEW = 1034;
VidiunStatsVmcEventType.prototype.ACCOUNT_ACCESS_CONTROL = 1035;
VidiunStatsVmcEventType.prototype.ACCOUNT_TRANSCODING_SETTINGS = 1036;
VidiunStatsVmcEventType.prototype.ACCOUNT_ACCOUNT_UPGRADE = 1037;
VidiunStatsVmcEventType.prototype.ACCOUNT_SAVE_SERVER_SETTINGS = 1038;
VidiunStatsVmcEventType.prototype.ACCOUNT_ACCESS_CONTROL_DELETE = 1039;
VidiunStatsVmcEventType.prototype.ACCOUNT_SAVE_TRANSCODING_SETTINGS = 1040;
VidiunStatsVmcEventType.prototype.LOGIN = 1041;
VidiunStatsVmcEventType.prototype.DASHBOARD_IMPORT_CONTENT = 1042;
VidiunStatsVmcEventType.prototype.DASHBOARD_UPDATE_CONTENT = 1043;
VidiunStatsVmcEventType.prototype.DASHBOARD_ACCOUNT_CONTACT_US = 1044;
VidiunStatsVmcEventType.prototype.DASHBOARD_VIEW_REPORTS = 1045;
VidiunStatsVmcEventType.prototype.DASHBOARD_EMBED_PLAYER = 1046;
VidiunStatsVmcEventType.prototype.DASHBOARD_EMBED_PLAYLIST = 1047;
VidiunStatsVmcEventType.prototype.DASHBOARD_CUSTOMIZE_PLAYERS = 1048;
VidiunStatsVmcEventType.prototype.APP_STUDIO_NEW_PLAYER_SINGLE_VIDEO = 1050;
VidiunStatsVmcEventType.prototype.APP_STUDIO_NEW_PLAYER_PLAYLIST = 1051;
VidiunStatsVmcEventType.prototype.APP_STUDIO_NEW_PLAYER_MULTI_TAB_PLAYLIST = 1052;
VidiunStatsVmcEventType.prototype.APP_STUDIO_EDIT_PLAYER_SINGLE_VIDEO = 1053;
VidiunStatsVmcEventType.prototype.APP_STUDIO_EDIT_PLAYER_PLAYLIST = 1054;
VidiunStatsVmcEventType.prototype.APP_STUDIO_EDIT_PLAYER_MULTI_TAB_PLAYLIST = 1055;
VidiunStatsVmcEventType.prototype.APP_STUDIO_DUPLICATE_PLAYER = 1056;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_BANDWIDTH_USAGE_TAB = 1070;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_REPORTS_TAB = 1071;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_USERS_AND_COMMUNITY_REPORTS_TAB = 1072;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_TOP_CONTRIBUTORS = 1073;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_MAP_OVERLAYS = 1074;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_TOP_SYNDICATIONS = 1075;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_TOP_CONTENT = 1076;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_DROPOFF = 1077;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_INTERACTIONS = 1078;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_CONTRIBUTIONS = 1079;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_VIDEO_DRILL_DOWN = 1080;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_DRILL_DOWN_INTERACTION = 1081;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_CONTENT_CONTRIBUTIONS_DRILLDOWN = 1082;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_VIDEO_DRILL_DOWN_DROPOFF = 1083;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_MAP_OVERLAYS_DRILLDOWN = 1084;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_TOP_SYNDICATIONS_DRILL_DOWN = 1085;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_BANDWIDTH_USAGE_VIEW_MONTHLY = 1086;
VidiunStatsVmcEventType.prototype.REPORTS_AND_ANALYTICS_BANDWIDTH_USAGE_VIEW_YEARLY = 1087;

function VidiunSyndicationFeedStatus()
{
}
VidiunSyndicationFeedStatus.prototype.DELETED = -1;
VidiunSyndicationFeedStatus.prototype.ACTIVE = 1;

function VidiunSyndicationFeedType()
{
}
VidiunSyndicationFeedType.prototype.GOOGLE_VIDEO = 1;
VidiunSyndicationFeedType.prototype.YAHOO = 2;
VidiunSyndicationFeedType.prototype.ITUNES = 3;
VidiunSyndicationFeedType.prototype.TUBE_MOGUL = 4;

function VidiunSystemUserOrderBy()
{
}
VidiunSystemUserOrderBy.prototype.ID_ASC = "+id";
VidiunSystemUserOrderBy.prototype.ID_DESC = "-id";
VidiunSystemUserOrderBy.prototype.STATUS_ASC = "+status";
VidiunSystemUserOrderBy.prototype.STATUS_DESC = "-status";

function VidiunSystemUserStatus()
{
}
VidiunSystemUserStatus.prototype.BLOCKED = 0;
VidiunSystemUserStatus.prototype.ACTIVE = 1;

function VidiunTubeMogulSyndicationFeedCategories()
{
}
VidiunTubeMogulSyndicationFeedCategories.prototype.ARTS_AND_ANIMATION = "Arts &amp; Animation";
VidiunTubeMogulSyndicationFeedCategories.prototype.COMEDY = "Comedy";
VidiunTubeMogulSyndicationFeedCategories.prototype.ENTERTAINMENT = "Entertainment";
VidiunTubeMogulSyndicationFeedCategories.prototype.MUSIC = "Music";
VidiunTubeMogulSyndicationFeedCategories.prototype.NEWS_AND_BLOGS = "News &amp; Blogs";
VidiunTubeMogulSyndicationFeedCategories.prototype.SCIENCE_AND_TECHNOLOGY = "Science &amp; Technology";
VidiunTubeMogulSyndicationFeedCategories.prototype.SPORTS = "Sports";
VidiunTubeMogulSyndicationFeedCategories.prototype.TRAVEL_AND_PLACES = "Travel &amp; Places";
VidiunTubeMogulSyndicationFeedCategories.prototype.VIDEO_GAMES = "Video Games";
VidiunTubeMogulSyndicationFeedCategories.prototype.ANIMALS_AND_PETS = "Animals &amp; Pets";
VidiunTubeMogulSyndicationFeedCategories.prototype.AUTOS = "Autos";
VidiunTubeMogulSyndicationFeedCategories.prototype.VLOGS_PEOPLE = "Vlogs &amp; People";
VidiunTubeMogulSyndicationFeedCategories.prototype.HOW_TO_INSTRUCTIONAL_DIY = "How To/Instructional/DIY";
VidiunTubeMogulSyndicationFeedCategories.prototype.COMMERCIALS_PROMOTIONAL = "Commercials/Promotional";
VidiunTubeMogulSyndicationFeedCategories.prototype.FAMILY_AND_KIDS = "Family &amp; Kids";

function VidiunTubeMogulSyndicationFeedOrderBy()
{
}
VidiunTubeMogulSyndicationFeedOrderBy.prototype.PLAYLIST_ID_ASC = "+playlistId";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.PLAYLIST_ID_DESC = "-playlistId";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.NAME_ASC = "+name";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.NAME_DESC = "-name";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.TYPE_ASC = "+type";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.TYPE_DESC = "-type";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunTubeMogulSyndicationFeedOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunUiConfCreationMode()
{
}
VidiunUiConfCreationMode.prototype.WIZARD = 2;
VidiunUiConfCreationMode.prototype.ADVANCED = 3;

function VidiunUiConfObjType()
{
}
VidiunUiConfObjType.prototype.PLAYER = 1;
VidiunUiConfObjType.prototype.CONTRIBUTION_WIZARD = 2;
VidiunUiConfObjType.prototype.SIMPLE_EDITOR = 3;
VidiunUiConfObjType.prototype.ADVANCED_EDITOR = 4;
VidiunUiConfObjType.prototype.PLAYLIST = 5;
VidiunUiConfObjType.prototype.APP_STUDIO = 6;

function VidiunUiConfOrderBy()
{
}
VidiunUiConfOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunUiConfOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunUploadErrorCode()
{
}
VidiunUploadErrorCode.prototype.NO_ERROR = 0;
VidiunUploadErrorCode.prototype.GENERAL_ERROR = 1;
VidiunUploadErrorCode.prototype.PARTIAL_UPLOAD = 2;

function VidiunUserOrderBy()
{
}
VidiunUserOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunUserOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunUserStatus()
{
}
VidiunUserStatus.prototype.BLOCKED = 0;
VidiunUserStatus.prototype.ACTIVE = 1;
VidiunUserStatus.prototype.DELETED = 2;

function VidiunVideoCodec()
{
}
VidiunVideoCodec.prototype.NONE = "";
VidiunVideoCodec.prototype.VP6 = "vp6";
VidiunVideoCodec.prototype.H263 = "h263";
VidiunVideoCodec.prototype.H264 = "h264";
VidiunVideoCodec.prototype.FLV = "flv";

function VidiunWidgetOrderBy()
{
}
VidiunWidgetOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunWidgetOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunWidgetSecurityType()
{
}
VidiunWidgetSecurityType.prototype.NONE = 1;
VidiunWidgetSecurityType.prototype.TIMEHASH = 2;

function VidiunYahooSyndicationFeedAdultValues()
{
}
VidiunYahooSyndicationFeedAdultValues.prototype.ADULT = "adult";
VidiunYahooSyndicationFeedAdultValues.prototype.NON_ADULT = "nonadult";

function VidiunYahooSyndicationFeedCategories()
{
}
VidiunYahooSyndicationFeedCategories.prototype.ACTION = "Action";
VidiunYahooSyndicationFeedCategories.prototype.ART_AND_ANIMATION = "Art &amp; Animation";
VidiunYahooSyndicationFeedCategories.prototype.ENTERTAINMENT_AND_TV = "Entertainment &amp; TV";
VidiunYahooSyndicationFeedCategories.prototype.FOOD = "Food";
VidiunYahooSyndicationFeedCategories.prototype.GAMES = "Games";
VidiunYahooSyndicationFeedCategories.prototype.HOW_TO = "How-To";
VidiunYahooSyndicationFeedCategories.prototype.MUSIC = "Music";
VidiunYahooSyndicationFeedCategories.prototype.PEOPLE_AND_VLOGS = "People &amp; Vlogs";
VidiunYahooSyndicationFeedCategories.prototype.SCIENCE_AND_ENVIRONMENT = "Science &amp; Environment";
VidiunYahooSyndicationFeedCategories.prototype.TRANSPORTATION = "Transportation";
VidiunYahooSyndicationFeedCategories.prototype.ANIMALS = "Animals";
VidiunYahooSyndicationFeedCategories.prototype.COMMERCIALS = "Commercials";
VidiunYahooSyndicationFeedCategories.prototype.FAMILY = "Family";
VidiunYahooSyndicationFeedCategories.prototype.FUNNY_VIDEOS = "Funny Videos";
VidiunYahooSyndicationFeedCategories.prototype.HEALTH_AND_BEAUTY = "Health &amp; Beauty";
VidiunYahooSyndicationFeedCategories.prototype.MOVIES_AND_SHORTS = "Movies &amp; Shorts";
VidiunYahooSyndicationFeedCategories.prototype.NEWS_AND_POLITICS = "News &amp; Politics";
VidiunYahooSyndicationFeedCategories.prototype.PRODUCTS_AND_TECH = "Products &amp; Tech.";
VidiunYahooSyndicationFeedCategories.prototype.SPORTS = "Sports";
VidiunYahooSyndicationFeedCategories.prototype.TRAVEL = "Travel";

function VidiunYahooSyndicationFeedOrderBy()
{
}
VidiunYahooSyndicationFeedOrderBy.prototype.PLAYLIST_ID_ASC = "+playlistId";
VidiunYahooSyndicationFeedOrderBy.prototype.PLAYLIST_ID_DESC = "-playlistId";
VidiunYahooSyndicationFeedOrderBy.prototype.NAME_ASC = "+name";
VidiunYahooSyndicationFeedOrderBy.prototype.NAME_DESC = "-name";
VidiunYahooSyndicationFeedOrderBy.prototype.TYPE_ASC = "+type";
VidiunYahooSyndicationFeedOrderBy.prototype.TYPE_DESC = "-type";
VidiunYahooSyndicationFeedOrderBy.prototype.CREATED_AT_ASC = "+createdAt";
VidiunYahooSyndicationFeedOrderBy.prototype.CREATED_AT_DESC = "-createdAt";

function VidiunAccessControl()
{
}
VidiunAccessControl.prototype = new VidiunObjectBase();
/**
 * The id of the Access Control Profile
	 * 
 *
 * @var int
 * @readonly
 */
VidiunAccessControl.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunAccessControl.prototype.partnerId = null;

/**
 * The name of the Access Control Profile
	 * 
 *
 * @var string
 */
VidiunAccessControl.prototype.name = null;

/**
 * The description of the Access Control Profile
	 * 
 *
 * @var string
 */
VidiunAccessControl.prototype.description = null;

/**
 * Creation date as Unix timestamp (In seconds) 
	 * 
 *
 * @var int
 * @readonly
 */
VidiunAccessControl.prototype.createdAt = null;

/**
 * True if this Conversion Profile is the default
	 * 
 *
 * @var VidiunNullableBoolean
 */
VidiunAccessControl.prototype.isDefault = null;

/**
 * Array of Access Control Restrictions
	 * 
 *
 * @var VidiunRestrictionArray
 */
VidiunAccessControl.prototype.restrictions = null;


function VidiunFilter()
{
}
VidiunFilter.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunFilter.prototype.orderBy = null;


function VidiunAccessControlFilter()
{
}
VidiunAccessControlFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var int
 */
VidiunAccessControlFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunAccessControlFilter.prototype.idIn = null;

/**
 * 
 *
 * @var int
 */
VidiunAccessControlFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunAccessControlFilter.prototype.createdAtLessThanOrEqual = null;


function VidiunAccessControlListResponse()
{
}
VidiunAccessControlListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunAccessControlArray
 * @readonly
 */
VidiunAccessControlListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunAccessControlListResponse.prototype.totalCount = null;


function VidiunAdminUser()
{
}
VidiunAdminUser.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunAdminUser.prototype.password = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunAdminUser.prototype.email = null;

/**
 * 
 *
 * @var string
 */
VidiunAdminUser.prototype.screenName = null;


function VidiunBaseEntry()
{
}
VidiunBaseEntry.prototype = new VidiunObjectBase();
/**
 * Auto generated 10 characters alphanumeric string
	 * 
 *
 * @var string
 * @readonly
 */
VidiunBaseEntry.prototype.id = null;

/**
 * Entry name (Min 1 chars)
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.name = null;

/**
 * Entry description
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.description = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.partnerId = null;

/**
 * The ID of the user who is the owner of this entry 
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.userId = null;

/**
 * Entry tags
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.tags = null;

/**
 * Entry admin tags can be updated only by administrators
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.adminTags = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.categories = null;

/**
 * 
 *
 * @var VidiunEntryStatus
 * @readonly
 */
VidiunBaseEntry.prototype.status = null;

/**
 * Entry moderation status
	 * 
 *
 * @var VidiunEntryModerationStatus
 * @readonly
 */
VidiunBaseEntry.prototype.moderationStatus = null;

/**
 * Number of moderation requests waiting for this entry
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.moderationCount = null;

/**
 * The type of the entry, this is auto filled by the derived entry object
	 * 
 *
 * @var VidiunEntryType
 * @readonly
 */
VidiunBaseEntry.prototype.type = null;

/**
 * Entry creation date as Unix timestamp (In seconds)
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.createdAt = null;

/**
 * Calculated rank
	 * 
 *
 * @var float
 * @readonly
 */
VidiunBaseEntry.prototype.rank = null;

/**
 * The total (sum) of all votes
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.totalRank = null;

/**
 * Number of votes
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.votes = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntry.prototype.groupId = null;

/**
 * Can be used to store various partner related data as a string 
	 * 
 *
 * @var string
 */
VidiunBaseEntry.prototype.partnerData = null;

/**
 * Download URL for the entry
	 * 
 *
 * @var string
 * @readonly
 */
VidiunBaseEntry.prototype.downloadUrl = null;

/**
 * Indexed search text for full text search
 *
 * @var string
 * @readonly
 */
VidiunBaseEntry.prototype.searchText = null;

/**
 * License type used for this entry
	 * 
 *
 * @var VidiunLicenseType
 */
VidiunBaseEntry.prototype.licenseType = null;

/**
 * Version of the entry data
 *
 * @var int
 * @readonly
 */
VidiunBaseEntry.prototype.version = null;

/**
 * Thumbnail URL
	 * 
 *
 * @var string
 * @readonly
 */
VidiunBaseEntry.prototype.thumbnailUrl = null;

/**
 * The Access Control ID assigned to this entry (null when not set, send -1 to remove)  
	 * 
 *
 * @var int
 */
VidiunBaseEntry.prototype.accessControlId = null;

/**
 * Entry scheduling start date (null when not set, send -1 to remove)
	 * 
 *
 * @var int
 */
VidiunBaseEntry.prototype.startDate = null;

/**
 * Entry scheduling end date (null when not set, send -1 to remove)
	 * 
 *
 * @var int
 */
VidiunBaseEntry.prototype.endDate = null;


function VidiunBaseEntryFilter()
{
}
VidiunBaseEntryFilter.prototype = new VidiunFilter();
/**
 * This filter should be in use for retrieving only a specific entry (identified by its entryId).
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.idEqual = null;

/**
 * This filter should be in use for retrieving few specific entries (string should include comma separated list of entryId strings).
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.idIn = null;

/**
 * This filter should be in use for retrieving specific entries while applying an SQL 'LIKE' pattern matching on entry names. It should include only one pattern for matching entry names against.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.nameLike = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on entry names. It could include few (comma separated) patterns for matching entry names against, while applying an OR logic to retrieve entries that match at least one input pattern.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.nameMultiLikeOr = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on entry names. It could include few (comma separated) patterns for matching entry names against, while applying an AND logic to retrieve entries that match all input patterns.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.nameMultiLikeAnd = null;

/**
 * This filter should be in use for retrieving entries with a specific name.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.nameEqual = null;

/**
 * This filter should be in use for retrieving only entries which were uploaded by/assigned to users of a specific Vidiun Partner (identified by Partner ID).
	 * @var int
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.partnerIdEqual = null;

/**
 * This filter should be in use for retrieving only entries within Vidiun network which were uploaded by/assigned to users of few Vidiun Partners  (string should include comma separated list of PartnerIDs)
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.partnerIdIn = null;

/**
 * This filter parameter should be in use for retrieving only entries, uploaded by/assigned to a specific user (identified by user Id).
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.userIdEqual = null;

/**
 * This filter should be in use for retrieving specific entries while applying an SQL 'LIKE' pattern matching on entry tags. It should include only one pattern for matching entry tags against.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsLike = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on tags.  It could include few (comma separated) patterns for matching entry tags against, while applying an OR logic to retrieve entries that match at least one input pattern.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsMultiLikeOr = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on tags.  It could include few (comma separated) patterns for matching entry tags against, while applying an AND logic to retrieve entries that match all input patterns.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsMultiLikeAnd = null;

/**
 * This filter should be in use for retrieving specific entries while applying an SQL 'LIKE' pattern matching on entry tags, set by an ADMIN user. It should include only one pattern for matching entry tags against.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.adminTagsLike = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on tags, set by an ADMIN user.  It could include few (comma separated) patterns for matching entry tags against, while applying an OR logic to retrieve entries that match at least one input pattern.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.adminTagsMultiLikeOr = null;

/**
 * This filter should be in use for retrieving specific entries, while applying an SQL 'LIKE' pattern matching on tags, set by an ADMIN user.  It could include few (comma separated) patterns for matching entry tags against, while applying an AND logic to retrieve entries that match all input patterns.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.adminTagsMultiLikeAnd = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.categoriesMatchAnd = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.categoriesMatchOr = null;

/**
 * This filter should be in use for retrieving only entries, at a specific {@link ?object=VidiunEntryStatus VidiunEntryStatus}.
	 * @var VidiunEntryStatus
 *
 * @var VidiunEntryStatus
 */
VidiunBaseEntryFilter.prototype.statusEqual = null;

/**
 * This filter should be in use for retrieving only entries, not at a specific {@link ?object=VidiunEntryStatus VidiunEntryStatus}.
	 * @var VidiunEntryStatus
 *
 * @var VidiunEntryStatus
 */
VidiunBaseEntryFilter.prototype.statusNotEqual = null;

/**
 * This filter should be in use for retrieving only entries, at few specific {@link ?object=VidiunEntryStatus VidiunEntryStatus} (comma separated).
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.statusIn = null;

/**
 * This filter should be in use for retrieving only entries, not at few specific {@link ?object=VidiunEntryStatus VidiunEntryStatus} (comma separated).
	 * @var VidiunEntryStatus
 *
 * @var VidiunEntryStatus
 */
VidiunBaseEntryFilter.prototype.statusNotIn = null;

/**
 * 
 *
 * @var VidiunEntryModerationStatus
 */
VidiunBaseEntryFilter.prototype.moderationStatusEqual = null;

/**
 * 
 *
 * @var VidiunEntryModerationStatus
 */
VidiunBaseEntryFilter.prototype.moderationStatusNotEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.moderationStatusIn = null;

/**
 * 
 *
 * @var VidiunEntryModerationStatus
 */
VidiunBaseEntryFilter.prototype.moderationStatusNotIn = null;

/**
 * 
 *
 * @var VidiunEntryType
 */
VidiunBaseEntryFilter.prototype.typeEqual = null;

/**
 * This filter should be in use for retrieving entries of few {@link ?object=VidiunEntryType VidiunEntryType} (string should include a comma separated list of {@link ?object=VidiunEntryType VidiunEntryType} enumerated parameters).
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.typeIn = null;

/**
 * This filter parameter should be in use for retrieving only entries which were created at Vidiun system after a specific time/date (standard timestamp format).
	 * @var int
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * This filter parameter should be in use for retrieving only entries which were created at Vidiun system before a specific time/date (standard timestamp format).
	 * @var int
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.createdAtLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.groupIdEqual = null;

/**
 * This filter should be in use for retrieving specific entries while search match the input string within all of the following metadata attributes: name, description, tags, adminTags.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.searchTextMatchAnd = null;

/**
 * This filter should be in use for retrieving specific entries while search match the input string within at least one of the following metadata attributes: name, description, tags, adminTags.
	 * @var string
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.searchTextMatchOr = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.accessControlIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.accessControlIdIn = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.startDateGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.startDateLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.startDateGreaterThanOrEqualOrNull = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.startDateLessThanOrEqualOrNull = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.endDateGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.endDateLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.endDateGreaterThanOrEqualOrNull = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseEntryFilter.prototype.endDateLessThanOrEqualOrNull = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsNameMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsAdminTagsMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsAdminTagsNameMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsNameMultiLikeAnd = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsAdminTagsMultiLikeAnd = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseEntryFilter.prototype.tagsAdminTagsNameMultiLikeAnd = null;


function VidiunBaseEntryListResponse()
{
}
VidiunBaseEntryListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunBaseEntryArray
 * @readonly
 */
VidiunBaseEntryListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseEntryListResponse.prototype.totalCount = null;


function VidiunBaseJob()
{
}
VidiunBaseJob.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.partnerId = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.createdAt = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.updatedAt = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.processorExpiration = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseJob.prototype.executionAttempts = null;


function VidiunBaseJobFilter()
{
}
VidiunBaseJobFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var int
 */
VidiunBaseJobFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseJobFilter.prototype.idGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseJobFilter.prototype.partnerIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseJobFilter.prototype.partnerIdIn = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseJobFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseJobFilter.prototype.createdAtLessThanOrEqual = null;


function VidiunBaseRestriction()
{
}
VidiunBaseRestriction.prototype = new VidiunObjectBase();

function VidiunBaseSyndicationFeed()
{
}
VidiunBaseSyndicationFeed.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.id = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.feedUrl = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.partnerId = null;

/**
 * link a playlist that will set what content the feed will include
	 * if empty, all content will be included in feed
	 * 
 *
 * @var string
 */
VidiunBaseSyndicationFeed.prototype.playlistId = null;

/**
 * feed name
	 * 
 *
 * @var string
 */
VidiunBaseSyndicationFeed.prototype.name = null;

/**
 * feed status
	 * 
 *
 * @var VidiunSyndicationFeedStatus
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.status = null;

/**
 * feed type
	 * 
 *
 * @var VidiunSyndicationFeedType
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.type = null;

/**
 * Base URL for each video, on the partners site
	 * This is required by all syndication types.
 *
 * @var string
 */
VidiunBaseSyndicationFeed.prototype.landingPage = null;

/**
 * Creation date as Unix timestamp (In seconds)
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseSyndicationFeed.prototype.createdAt = null;

/**
 * allow_embed tells google OR yahoo weather to allow embedding the video on google OR yahoo video results
	 * or just to provide a link to the landing page.
	 * it is applied on the video-player_loc property in the XML (google)
	 * and addes media-player tag (yahoo)
 *
 * @var bool
 */
VidiunBaseSyndicationFeed.prototype.allowEmbed = null;

/**
 * Select a uiconf ID as player skin to include in the vwidget url
 *
 * @var int
 */
VidiunBaseSyndicationFeed.prototype.playerUiconfId = null;

/**
 * 
 *
 * @var int
 */
VidiunBaseSyndicationFeed.prototype.flavorParamId = null;

/**
 * 
 *
 * @var bool
 */
VidiunBaseSyndicationFeed.prototype.transcodeExistingContent = null;

/**
 * 
 *
 * @var bool
 */
VidiunBaseSyndicationFeed.prototype.addToDefaultConversionProfile = null;

/**
 * 
 *
 * @var string
 */
VidiunBaseSyndicationFeed.prototype.categories = null;


function VidiunBaseSyndicationFeedFilter()
{
}
VidiunBaseSyndicationFeedFilter.prototype = new VidiunFilter();

function VidiunBaseSyndicationFeedListResponse()
{
}
VidiunBaseSyndicationFeedListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunBaseSyndicationFeedArray
 * @readonly
 */
VidiunBaseSyndicationFeedListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBaseSyndicationFeedListResponse.prototype.totalCount = null;


function VidiunBatchJob()
{
}
VidiunBatchJob.prototype = new VidiunBaseJob();
/**
 * 
 *
 * @var string
 */
VidiunBatchJob.prototype.entryId = null;

/**
 * 
 *
 * @var VidiunBatchJobType
 * @readonly
 */
VidiunBatchJob.prototype.jobType = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.jobSubType = null;

/**
 * 
 *
 * @var VidiunJobData
 */
VidiunBatchJob.prototype.data = null;

/**
 * 
 *
 * @var VidiunBatchJobStatus
 */
VidiunBatchJob.prototype.status = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.abort = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.checkAgainTimeout = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.progress = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJob.prototype.message = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJob.prototype.description = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.updatesCount = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.priority = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.workGroupId = null;

/**
 * The id of the bulk upload job that initiated this job
 *
 * @var int
 */
VidiunBatchJob.prototype.bulkJobId = null;

/**
 * When one job creates another - the parent should set this parentJobId to be its own id.
 *
 * @var int
 */
VidiunBatchJob.prototype.parentJobId = null;

/**
 * The id of the root parent job
 *
 * @var int
 */
VidiunBatchJob.prototype.rootJobId = null;

/**
 * The time that the job was pulled from the queue
 *
 * @var int
 */
VidiunBatchJob.prototype.queueTime = null;

/**
 * The time that the job was finished or closed as failed
 *
 * @var int
 */
VidiunBatchJob.prototype.finishTime = null;

/**
 * 
 *
 * @var VidiunBatchJobErrorTypes
 */
VidiunBatchJob.prototype.errType = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.errNumber = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJob.prototype.fileSize = null;

/**
 * 
 *
 * @var bool
 */
VidiunBatchJob.prototype.lastWorkerRemote = null;


function VidiunBatchJobFilter()
{
}
VidiunBatchJobFilter.prototype = new VidiunBaseJobFilter();
/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.entryIdEqual = null;

/**
 * 
 *
 * @var VidiunBatchJobType
 */
VidiunBatchJobFilter.prototype.jobTypeEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.jobTypeIn = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.jobSubTypeEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.jobSubTypeIn = null;

/**
 * 
 *
 * @var VidiunBatchJobStatus
 */
VidiunBatchJobFilter.prototype.statusEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.statusIn = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.workGroupIdIn = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.queueTimeGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.queueTimeLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.finishTimeGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.finishTimeLessThanOrEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunBatchJobFilter.prototype.errTypeIn = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.fileSizeLessThan = null;

/**
 * 
 *
 * @var int
 */
VidiunBatchJobFilter.prototype.fileSizeGreaterThan = null;


function VidiunBatchJobListResponse()
{
}
VidiunBatchJobListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunBatchJobArray
 * @readonly
 */
VidiunBatchJobListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBatchJobListResponse.prototype.totalCount = null;


function VidiunBatchJobResponse()
{
}
VidiunBatchJobResponse.prototype = new VidiunObjectBase();
/**
 * The main batch job
	 * 
 *
 * @var VidiunBatchJob
 */
VidiunBatchJobResponse.prototype.batchJob = null;

/**
 * All batch jobs that reference the main job as root
	 * 
 *
 * @var VidiunBatchJobArray
 */
VidiunBatchJobResponse.prototype.childBatchJobs = null;


function VidiunJobData()
{
}
VidiunJobData.prototype = new VidiunObjectBase();

function VidiunBulkDownloadJobData()
{
}
VidiunBulkDownloadJobData.prototype = new VidiunJobData();
/**
 * Comma separated list of entry ids
	 * 
 *
 * @var string
 */
VidiunBulkDownloadJobData.prototype.entryIds = null;

/**
 * Flavor params id to use for conversion
	 * 
 *
 * @var int
 */
VidiunBulkDownloadJobData.prototype.flavorParamsId = null;

/**
 * The id of the requesting user
	 * 
 *
 * @var string
 */
VidiunBulkDownloadJobData.prototype.puserId = null;


function VidiunBulkUpload()
{
}
VidiunBulkUpload.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 */
VidiunBulkUpload.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUpload.prototype.uploadedBy = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUpload.prototype.uploadedOn = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUpload.prototype.numOfEntries = null;

/**
 * 
 *
 * @var VidiunBatchJobStatus
 */
VidiunBulkUpload.prototype.status = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUpload.prototype.logFileUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUpload.prototype.csvFileUrl = null;

/**
 * 
 *
 * @var VidiunBulkUploadResultArray
 */
VidiunBulkUpload.prototype.results = null;


function VidiunBulkUploadJobData()
{
}
VidiunBulkUploadJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var int
 */
VidiunBulkUploadJobData.prototype.userId = null;

/**
 * The screen name of the user
	 * 
 *
 * @var string
 */
VidiunBulkUploadJobData.prototype.uploadedBy = null;

/**
 * Selected profile id for all bulk entries
	 * 
 *
 * @var int
 */
VidiunBulkUploadJobData.prototype.conversionProfileId = null;

/**
 * Created by the API
	 * 
 *
 * @var string
 */
VidiunBulkUploadJobData.prototype.csvFilePath = null;

/**
 * Created by the API
	 * 
 *
 * @var string
 */
VidiunBulkUploadJobData.prototype.resultsFileLocalPath = null;

/**
 * Created by the API
	 * 
 *
 * @var string
 */
VidiunBulkUploadJobData.prototype.resultsFileUrl = null;

/**
 * Number of created entries
	 * 
 *
 * @var int
 */
VidiunBulkUploadJobData.prototype.numOfEntries = null;

/**
 * The version of the csv file
	 * 
 *
 * @var VidiunBulkUploadCsvVersion
 */
VidiunBulkUploadJobData.prototype.csvVersion = null;


function VidiunBulkUploadListResponse()
{
}
VidiunBulkUploadListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunBulkUploads
 * @readonly
 */
VidiunBulkUploadListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunBulkUploadListResponse.prototype.totalCount = null;


function VidiunBulkUploadResult()
{
}
VidiunBulkUploadResult.prototype = new VidiunObjectBase();
/**
 * The id of the result
	 * 
 *
 * @var int
 * @readonly
 */
VidiunBulkUploadResult.prototype.id = null;

/**
 * The id of the parent job
	 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.bulkUploadJobId = null;

/**
 * The index of the line in the CSV
	 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.lineIndex = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.entryId = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.entryStatus = null;

/**
 * The data as recieved in the csv
	 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.rowData = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.title = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.description = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.tags = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.url = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.contentType = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.conversionProfileId = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.accessControlProfileId = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.category = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.scheduleStartDate = null;

/**
 * 
 *
 * @var int
 */
VidiunBulkUploadResult.prototype.scheduleEndDate = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.thumbnailUrl = null;

/**
 * 
 *
 * @var bool
 */
VidiunBulkUploadResult.prototype.thumbnailSaved = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.partnerData = null;

/**
 * 
 *
 * @var string
 */
VidiunBulkUploadResult.prototype.errorDescription = null;


function VidiunCEError()
{
}
VidiunCEError.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunCEError.prototype.id = null;

/**
 * 
 *
 * @var int
 */
VidiunCEError.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.browser = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.serverIp = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.serverOs = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.phpVersion = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.ceAdminEmail = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.type = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.description = null;

/**
 * 
 *
 * @var string
 */
VidiunCEError.prototype.data = null;


function VidiunCategory()
{
}
VidiunCategory.prototype = new VidiunObjectBase();
/**
 * The id of the Category
	 * 
 *
 * @var int
 * @readonly
 */
VidiunCategory.prototype.id = null;

/**
 * 
 *
 * @var int
 */
VidiunCategory.prototype.parentId = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunCategory.prototype.depth = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunCategory.prototype.partnerId = null;

/**
 * The name of the Category. 
	 * The following characters are not allowed: '<', '>', ','
	 * 
 *
 * @var string
 */
VidiunCategory.prototype.name = null;

/**
 * The full name of the Category
	 * 
 *
 * @var string
 * @readonly
 */
VidiunCategory.prototype.fullName = null;

/**
 * Number of entries in this Category (including child categories)
	 * 
 *
 * @var int
 * @readonly
 */
VidiunCategory.prototype.entriesCount = null;

/**
 * Creation date as Unix timestamp (In seconds)
	 * 
 *
 * @var int
 * @readonly
 */
VidiunCategory.prototype.createdAt = null;


function VidiunCategoryFilter()
{
}
VidiunCategoryFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var int
 */
VidiunCategoryFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunCategoryFilter.prototype.idIn = null;

/**
 * 
 *
 * @var int
 */
VidiunCategoryFilter.prototype.parentIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunCategoryFilter.prototype.parentIdIn = null;

/**
 * 
 *
 * @var int
 */
VidiunCategoryFilter.prototype.depthEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunCategoryFilter.prototype.fullNameEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunCategoryFilter.prototype.fullNameStartsWith = null;


function VidiunCategoryListResponse()
{
}
VidiunCategoryListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunCategoryArray
 * @readonly
 */
VidiunCategoryListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunCategoryListResponse.prototype.totalCount = null;


function VidiunClientNotification()
{
}
VidiunClientNotification.prototype = new VidiunObjectBase();
/**
 * The URL where the notification should be sent to 
 *
 * @var string
 */
VidiunClientNotification.prototype.url = null;

/**
 * The serialized notification data to send
 *
 * @var string
 */
VidiunClientNotification.prototype.data = null;


function VidiunConvartableJobData()
{
}
VidiunConvartableJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunConvartableJobData.prototype.srcFileSyncLocalPath = null;

/**
 * 
 *
 * @var string
 */
VidiunConvartableJobData.prototype.srcFileSyncRemoteUrl = null;

/**
 * 
 *
 * @var int
 */
VidiunConvartableJobData.prototype.flavorParamsOutputId = null;

/**
 * 
 *
 * @var VidiunFlavorParamsOutput
 */
VidiunConvartableJobData.prototype.flavorParamsOutput = null;

/**
 * 
 *
 * @var int
 */
VidiunConvartableJobData.prototype.mediaInfoId = null;


function VidiunConversionProfile()
{
}
VidiunConversionProfile.prototype = new VidiunObjectBase();
/**
 * The id of the Conversion Profile
	 * 
 *
 * @var int
 * @readonly
 */
VidiunConversionProfile.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunConversionProfile.prototype.partnerId = null;

/**
 * The name of the Conversion Profile
	 * 
 *
 * @var string
 */
VidiunConversionProfile.prototype.name = null;

/**
 * The description of the Conversion Profile
	 * 
 *
 * @var string
 */
VidiunConversionProfile.prototype.description = null;

/**
 * Creation date as Unix timestamp (In seconds) 
	 * 
 *
 * @var int
 * @readonly
 */
VidiunConversionProfile.prototype.createdAt = null;

/**
 * List of included flavor ids (comma separated)
	 * 
 *
 * @var string
 */
VidiunConversionProfile.prototype.flavorParamsIds = null;

/**
 * True if this Conversion Profile is the default
	 * 
 *
 * @var VidiunNullableBoolean
 */
VidiunConversionProfile.prototype.isDefault = null;

/**
 * Cropping dimensions
	 * 
 *
 * @var VidiunCropDimensions
 */
VidiunConversionProfile.prototype.cropDimensions = null;

/**
 * Clipping start position (in miliseconds)
	 * 
 *
 * @var int
 */
VidiunConversionProfile.prototype.clipStart = null;

/**
 * Clipping duration (in miliseconds)
	 * 
 *
 * @var int
 */
VidiunConversionProfile.prototype.clipDuration = null;


function VidiunConversionProfileFilter()
{
}
VidiunConversionProfileFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var int
 */
VidiunConversionProfileFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunConversionProfileFilter.prototype.idIn = null;


function VidiunConversionProfileListResponse()
{
}
VidiunConversionProfileListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunConversionProfileArray
 * @readonly
 */
VidiunConversionProfileListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunConversionProfileListResponse.prototype.totalCount = null;


function VidiunConvertJobData()
{
}
VidiunConvertJobData.prototype = new VidiunConvartableJobData();
/**
 * 
 *
 * @var string
 */
VidiunConvertJobData.prototype.destFileSyncLocalPath = null;

/**
 * 
 *
 * @var string
 */
VidiunConvertJobData.prototype.destFileSyncRemoteUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunConvertJobData.prototype.logFileSyncLocalPath = null;

/**
 * 
 *
 * @var string
 */
VidiunConvertJobData.prototype.flavorAssetId = null;

/**
 * 
 *
 * @var string
 */
VidiunConvertJobData.prototype.remoteMediaId = null;


function VidiunConvertProfileJobData()
{
}
VidiunConvertProfileJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunConvertProfileJobData.prototype.inputFileSyncLocalPath = null;

/**
 * The height of last created thumbnail, will be used to comapare if this thumbnail is the best we can have
	 * 
 *
 * @var int
 */
VidiunConvertProfileJobData.prototype.thumbHeight = null;

/**
 * The bit rate of last created thumbnail, will be used to comapare if this thumbnail is the best we can have
	 * 
 *
 * @var int
 */
VidiunConvertProfileJobData.prototype.thumbBitrate = null;


function VidiunCountryRestriction()
{
}
VidiunCountryRestriction.prototype = new VidiunBaseRestriction();
/**
 * Country restriction type (Allow or deny)
	 * 
 *
 * @var VidiunCountryRestrictionType
 */
VidiunCountryRestriction.prototype.countryRestrictionType = null;

/**
 * Comma separated list of country codes to allow to deny 
	 * 
 *
 * @var string
 */
VidiunCountryRestriction.prototype.countryList = null;


function VidiunCropDimensions()
{
}
VidiunCropDimensions.prototype = new VidiunObjectBase();
/**
 * Crop left point
	 * 
 *
 * @var int
 */
VidiunCropDimensions.prototype.left = null;

/**
 * Crop top point
	 * 
 *
 * @var int
 */
VidiunCropDimensions.prototype.top = null;

/**
 * Crop width
	 * 
 *
 * @var int
 */
VidiunCropDimensions.prototype.width = null;

/**
 * Crop height
	 * 
 *
 * @var int
 */
VidiunCropDimensions.prototype.height = null;


function VidiunDataEntry()
{
}
VidiunDataEntry.prototype = new VidiunBaseEntry();
/**
 * The data of the entry
 *
 * @var string
 */
VidiunDataEntry.prototype.dataContent = null;


function VidiunDataEntryFilter()
{
}
VidiunDataEntryFilter.prototype = new VidiunBaseEntryFilter();

function VidiunDataListResponse()
{
}
VidiunDataListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunDataEntryArray
 * @readonly
 */
VidiunDataListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunDataListResponse.prototype.totalCount = null;


function VidiunDirectoryRestriction()
{
}
VidiunDirectoryRestriction.prototype = new VidiunBaseRestriction();
/**
 * Vidiun directory restriction type
	 * 
 *
 * @var VidiunDirectoryRestrictionType
 */
VidiunDirectoryRestriction.prototype.directoryRestrictionType = null;


function VidiunDocumentEntry()
{
}
VidiunDocumentEntry.prototype = new VidiunBaseEntry();
/**
 * The type of the document
 *
 * @var VidiunDocumentType
 * @insertonly
 */
VidiunDocumentEntry.prototype.documentType = null;


function VidiunDocumentEntryFilter()
{
}
VidiunDocumentEntryFilter.prototype = new VidiunBaseEntryFilter();
/**
 * 
 *
 * @var VidiunDocumentType
 */
VidiunDocumentEntryFilter.prototype.documentTypeEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunDocumentEntryFilter.prototype.documentTypeIn = null;


function VidiunEntryExtraDataParams()
{
}
VidiunEntryExtraDataParams.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunEntryExtraDataParams.prototype.referrer = null;


function VidiunEntryExtraDataResult()
{
}
VidiunEntryExtraDataResult.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var bool
 */
VidiunEntryExtraDataResult.prototype.isSiteRestricted = null;

/**
 * 
 *
 * @var bool
 */
VidiunEntryExtraDataResult.prototype.isCountryRestricted = null;

/**
 * 
 *
 * @var bool
 */
VidiunEntryExtraDataResult.prototype.isSessionRestricted = null;

/**
 * 
 *
 * @var int
 */
VidiunEntryExtraDataResult.prototype.previewLength = null;

/**
 * 
 *
 * @var bool
 */
VidiunEntryExtraDataResult.prototype.isScheduledNow = null;

/**
 * 
 *
 * @var bool
 */
VidiunEntryExtraDataResult.prototype.isAdmin = null;


function VidiunExtractMediaJobData()
{
}
VidiunExtractMediaJobData.prototype = new VidiunConvartableJobData();
/**
 * 
 *
 * @var string
 */
VidiunExtractMediaJobData.prototype.flavorAssetId = null;


function VidiunFilterPager()
{
}
VidiunFilterPager.prototype = new VidiunObjectBase();
/**
 * The number of objects to retrieve. (Default is 30, maximum page size is 500).
	 * 
 *
 * @var int
 */
VidiunFilterPager.prototype.pageSize = null;

/**
 * The page number for which {pageSize} of objects should be retrieved (Default is 1).
	 * 
 *
 * @var int
 */
VidiunFilterPager.prototype.pageIndex = null;


function VidiunFlattenJobData()
{
}
VidiunFlattenJobData.prototype = new VidiunJobData();

function VidiunFlavorAsset()
{
}
VidiunFlavorAsset.prototype = new VidiunObjectBase();
/**
 * The ID of the Flavor Asset
	 * 
 *
 * @var string
 * @readonly
 */
VidiunFlavorAsset.prototype.id = null;

/**
 * The entry ID of the Flavor Asset
	 * 
 *
 * @var string
 * @readonly
 */
VidiunFlavorAsset.prototype.entryId = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunFlavorAsset.prototype.partnerId = null;

/**
 * The status of the Flavor Asset
	 * 
 *
 * @var VidiunFlavorAssetStatus
 * @readonly
 */
VidiunFlavorAsset.prototype.status = null;

/**
 * The Flavor Params used to create this Flavor Asset
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.flavorParamsId = null;

/**
 * The version of the Flavor Asset
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.version = null;

/**
 * The width of the Flavor Asset 
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.width = null;

/**
 * The height of the Flavor Asset
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.height = null;

/**
 * The overall bitrate (in KBits) of the Flavor Asset 
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.bitrate = null;

/**
 * The frame rate (in FPS) of the Flavor Asset
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.frameRate = null;

/**
 * The size (in KBytes) of the Flavor Asset
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorAsset.prototype.size = null;

/**
 * True if this Flavor Asset is the original source
	 * 
 *
 * @var bool
 */
VidiunFlavorAsset.prototype.isOriginal = null;

/**
 * Tags used to identify the Flavor Asset in various scenarios
	 * 
 *
 * @var string
 */
VidiunFlavorAsset.prototype.tags = null;

/**
 * True if this Flavor Asset is playable in VDP
	 * 
 *
 * @var bool
 */
VidiunFlavorAsset.prototype.isWeb = null;

/**
 * The file extension
	 * 
 *
 * @var string
 */
VidiunFlavorAsset.prototype.fileExt = null;

/**
 * The container format
	 * 
 *
 * @var string
 */
VidiunFlavorAsset.prototype.containerFormat = null;

/**
 * The video codec
	 * 
 *
 * @var string
 */
VidiunFlavorAsset.prototype.videoCodecId = null;


function VidiunFlavorAssetWithParams()
{
}
VidiunFlavorAssetWithParams.prototype = new VidiunObjectBase();
/**
 * The Flavor Asset (Can be null when there are params without asset)
	 * 
 *
 * @var VidiunFlavorAsset
 */
VidiunFlavorAssetWithParams.prototype.flavorAsset = null;

/**
 * The Flavor Params
	 * 
 *
 * @var VidiunFlavorParams
 */
VidiunFlavorAssetWithParams.prototype.flavorParams = null;

/**
 * The entry id
	 * 
 *
 * @var string
 */
VidiunFlavorAssetWithParams.prototype.entryId = null;


function VidiunFlavorParams()
{
}
VidiunFlavorParams.prototype = new VidiunObjectBase();
/**
 * The id of the Flavor Params
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorParams.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorParams.prototype.partnerId = null;

/**
 * The name of the Flavor Params
	 * 
 *
 * @var string
 */
VidiunFlavorParams.prototype.name = null;

/**
 * The description of the Flavor Params
	 * 
 *
 * @var string
 */
VidiunFlavorParams.prototype.description = null;

/**
 * Creation date as Unix timestamp (In seconds)
	 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorParams.prototype.createdAt = null;

/**
 * True if those Flavor Params are part of system defaults
	 * 
 *
 * @var VidiunNullableBoolean
 * @readonly
 */
VidiunFlavorParams.prototype.isSystemDefault = null;

/**
 * The Flavor Params tags are used to identify the flavor for different usage (e.g. web, hd, mobile)
	 * 
 *
 * @var string
 */
VidiunFlavorParams.prototype.tags = null;

/**
 * The container format of the Flavor Params
	 * 
 *
 * @var VidiunContainerFormat
 */
VidiunFlavorParams.prototype.format = null;

/**
 * The video codec of the Flavor Params
	 * 
 *
 * @var VidiunVideoCodec
 */
VidiunFlavorParams.prototype.videoCodec = null;

/**
 * The video bitrate (in KBits) of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.videoBitrate = null;

/**
 * The audio codec of the Flavor Params
	 * 
 *
 * @var VidiunAudioCodec
 */
VidiunFlavorParams.prototype.audioCodec = null;

/**
 * The audio bitrate (in KBits) of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.audioBitrate = null;

/**
 * The number of audio channels for "downmixing"
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.audioChannels = null;

/**
 * The audio sample rate of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.audioSampleRate = null;

/**
 * The desired width of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.width = null;

/**
 * The desired height of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.height = null;

/**
 * The frame rate of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.frameRate = null;

/**
 * The gop size of the Flavor Params
	 * 
 *
 * @var int
 */
VidiunFlavorParams.prototype.gopSize = null;

/**
 * The list of conversion engines (comma separated)
	 * 
 *
 * @var string
 */
VidiunFlavorParams.prototype.conversionEngines = null;

/**
 * The list of conversion engines extra params (separated with "|")
	 * 
 *
 * @var string
 */
VidiunFlavorParams.prototype.conversionEnginesExtraParams = null;

/**
 * 
 *
 * @var bool
 */
VidiunFlavorParams.prototype.twoPass = null;


function VidiunFlavorParamsFilter()
{
}
VidiunFlavorParamsFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var VidiunNullableBoolean
 */
VidiunFlavorParamsFilter.prototype.isSystemDefaultEqual = null;


function VidiunFlavorParamsListResponse()
{
}
VidiunFlavorParamsListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunFlavorParamsArray
 * @readonly
 */
VidiunFlavorParamsListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunFlavorParamsListResponse.prototype.totalCount = null;


function VidiunFlavorParamsOutput()
{
}
VidiunFlavorParamsOutput.prototype = new VidiunFlavorParams();
/**
 * 
 *
 * @var int
 */
VidiunFlavorParamsOutput.prototype.flavorParamsId = null;

/**
 * 
 *
 * @var string
 */
VidiunFlavorParamsOutput.prototype.commandLinesStr = null;


function VidiunFlavorParamsOutputFilter()
{
}
VidiunFlavorParamsOutputFilter.prototype = new VidiunFlavorParamsFilter();

function VidiunGoogleVideoSyndicationFeed()
{
}
VidiunGoogleVideoSyndicationFeed.prototype = new VidiunBaseSyndicationFeed();
/**
 * 
 *
 * @var VidiunGoogleSyndicationFeedAdultValues
 */
VidiunGoogleVideoSyndicationFeed.prototype.adultContent = null;


function VidiunGoogleVideoSyndicationFeedFilter()
{
}
VidiunGoogleVideoSyndicationFeedFilter.prototype = new VidiunBaseSyndicationFeedFilter();

function VidiunITunesSyndicationFeed()
{
}
VidiunITunesSyndicationFeed.prototype = new VidiunBaseSyndicationFeed();
/**
 * feed description
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.feedDescription = null;

/**
 * feed language
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.language = null;

/**
 * feed landing page (i.e publisher website)
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.feedLandingPage = null;

/**
 * author/publisher name
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.ownerName = null;

/**
 * publisher email
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.ownerEmail = null;

/**
 * podcast thumbnail
	 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.feedImageUrl = null;

/**
 * 
 *
 * @var VidiunITunesSyndicationFeedCategories
 * @readonly
 */
VidiunITunesSyndicationFeed.prototype.category = null;

/**
 * 
 *
 * @var VidiunITunesSyndicationFeedAdultValues
 */
VidiunITunesSyndicationFeed.prototype.adultContent = null;

/**
 * 
 *
 * @var string
 */
VidiunITunesSyndicationFeed.prototype.feedAuthor = null;


function VidiunITunesSyndicationFeedFilter()
{
}
VidiunITunesSyndicationFeedFilter.prototype = new VidiunBaseSyndicationFeedFilter();

function VidiunImportJobData()
{
}
VidiunImportJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunImportJobData.prototype.srcFileUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunImportJobData.prototype.destFileLocalPath = null;

/**
 * 
 *
 * @var string
 */
VidiunImportJobData.prototype.flavorAssetId = null;


function VidiunMailJob()
{
}
VidiunMailJob.prototype = new VidiunBaseJob();
/**
 * 
 *
 * @var VidiunMailType
 */
VidiunMailJob.prototype.mailType = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJob.prototype.mailPriority = null;

/**
 * 
 *
 * @var VidiunMailJobStatus
 */
VidiunMailJob.prototype.status = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.recipientName = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.recipientEmail = null;

/**
 * vuserId  
 *
 * @var int
 */
VidiunMailJob.prototype.recipientId = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.fromName = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.fromEmail = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.bodyParams = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.subjectParams = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJob.prototype.templatePath = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJob.prototype.culture = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJob.prototype.campaignId = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJob.prototype.minSendDate = null;


function VidiunMailJobData()
{
}
VidiunMailJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var VidiunMailType
 */
VidiunMailJobData.prototype.mailType = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJobData.prototype.mailPriority = null;

/**
 * 
 *
 * @var VidiunMailJobStatus
 */
VidiunMailJobData.prototype.status = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.recipientName = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.recipientEmail = null;

/**
 * vuserId  
 *
 * @var int
 */
VidiunMailJobData.prototype.recipientId = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.fromName = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.fromEmail = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.bodyParams = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.subjectParams = null;

/**
 * 
 *
 * @var string
 */
VidiunMailJobData.prototype.templatePath = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJobData.prototype.culture = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJobData.prototype.campaignId = null;

/**
 * 
 *
 * @var int
 */
VidiunMailJobData.prototype.minSendDate = null;

/**
 * 
 *
 * @var bool
 */
VidiunMailJobData.prototype.isHtml = null;


function VidiunMailJobFilter()
{
}
VidiunMailJobFilter.prototype = new VidiunBaseJobFilter();

function VidiunPlayableEntry()
{
}
VidiunPlayableEntry.prototype = new VidiunBaseEntry();
/**
 * Number of plays
	 * 
 *
 * @var int
 * @readonly
 */
VidiunPlayableEntry.prototype.plays = null;

/**
 * Number of views
	 * 
 *
 * @var int
 * @readonly
 */
VidiunPlayableEntry.prototype.views = null;

/**
 * The width in pixels
	 * 
 *
 * @var int
 * @readonly
 */
VidiunPlayableEntry.prototype.width = null;

/**
 * The height in pixels
	 * 
 *
 * @var int
 * @readonly
 */
VidiunPlayableEntry.prototype.height = null;

/**
 * The duration in seconds
	 * 
 *
 * @var int
 * @readonly
 */
VidiunPlayableEntry.prototype.duration = null;

/**
 * The duration type (short for 0-4 mins, medium for 4-20 mins, long for 20+ mins)
	 * 
 *
 * @var VidiunDurationType
 * @readonly
 */
VidiunPlayableEntry.prototype.durationType = null;


function VidiunMediaEntry()
{
}
VidiunMediaEntry.prototype = new VidiunPlayableEntry();
/**
 * The media type of the entry
	 * 
 *
 * @var VidiunMediaType
 * @insertonly
 */
VidiunMediaEntry.prototype.mediaType = null;

/**
 * Override the default conversion quality  
	 * 
 *
 * @var string
 * @insertonly
 */
VidiunMediaEntry.prototype.conversionQuality = null;

/**
 * The source type of the entry 
 *
 * @var VidiunSourceType
 * @readonly
 */
VidiunMediaEntry.prototype.sourceType = null;

/**
 * The search provider type used to import this entry
 *
 * @var VidiunSearchProviderType
 * @readonly
 */
VidiunMediaEntry.prototype.searchProviderType = null;

/**
 * The ID of the media in the importing site
 *
 * @var string
 * @readonly
 */
VidiunMediaEntry.prototype.searchProviderId = null;

/**
 * The user name used for credits
 *
 * @var string
 */
VidiunMediaEntry.prototype.creditUserName = null;

/**
 * The URL for credits
 *
 * @var string
 */
VidiunMediaEntry.prototype.creditUrl = null;

/**
 * The media date extracted from EXIF data (For images) as Unix timestamp (In seconds)
 *
 * @var int
 * @readonly
 */
VidiunMediaEntry.prototype.mediaDate = null;

/**
 * The URL used for playback. This is not the download URL.
 *
 * @var string
 * @readonly
 */
VidiunMediaEntry.prototype.dataUrl = null;

/**
 * Comma separated flavor params ids that exists for this media entry
	 * 
 *
 * @var string
 * @readonly
 */
VidiunMediaEntry.prototype.flavorParamsIds = null;


function VidiunPlayableEntryFilter()
{
}
VidiunPlayableEntryFilter.prototype = new VidiunBaseEntryFilter();
/**
 * 
 *
 * @var int
 */
VidiunPlayableEntryFilter.prototype.durationLessThan = null;

/**
 * 
 *
 * @var int
 */
VidiunPlayableEntryFilter.prototype.durationGreaterThan = null;

/**
 * 
 *
 * @var int
 */
VidiunPlayableEntryFilter.prototype.durationLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunPlayableEntryFilter.prototype.durationGreaterThanOrEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunPlayableEntryFilter.prototype.durationTypeMatchOr = null;


function VidiunMediaEntryFilter()
{
}
VidiunMediaEntryFilter.prototype = new VidiunPlayableEntryFilter();
/**
 * 
 *
 * @var VidiunMediaType
 */
VidiunMediaEntryFilter.prototype.mediaTypeEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunMediaEntryFilter.prototype.mediaTypeIn = null;

/**
 * 
 *
 * @var int
 */
VidiunMediaEntryFilter.prototype.mediaDateGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunMediaEntryFilter.prototype.mediaDateLessThanOrEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunMediaEntryFilter.prototype.flavorParamsIdsMatchOr = null;

/**
 * 
 *
 * @var string
 */
VidiunMediaEntryFilter.prototype.flavorParamsIdsMatchAnd = null;


function VidiunMediaEntryFilterForPlaylist()
{
}
VidiunMediaEntryFilterForPlaylist.prototype = new VidiunMediaEntryFilter();
/**
 * 
 *
 * @var int
 */
VidiunMediaEntryFilterForPlaylist.prototype.limit = null;


function VidiunMediaListResponse()
{
}
VidiunMediaListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunMediaEntryArray
 * @readonly
 */
VidiunMediaListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunMediaListResponse.prototype.totalCount = null;


function VidiunMixEntry()
{
}
VidiunMixEntry.prototype = new VidiunPlayableEntry();
/**
 * Indicates whether the user has submited a real thumbnail to the mix (Not the one that was generated automaticaly)
	 * 
 *
 * @var bool
 * @readonly
 */
VidiunMixEntry.prototype.hasRealThumbnail = null;

/**
 * The editor type used to edit the metadata
	 * 
 *
 * @var VidiunEditorType
 */
VidiunMixEntry.prototype.editorType = null;

/**
 * The xml data of the mix
 *
 * @var string
 */
VidiunMixEntry.prototype.dataContent = null;


function VidiunMixEntryFilter()
{
}
VidiunMixEntryFilter.prototype = new VidiunPlayableEntryFilter();

function VidiunMixListResponse()
{
}
VidiunMixListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunMixEntryArray
 * @readonly
 */
VidiunMixListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunMixListResponse.prototype.totalCount = null;


function VidiunModerationFlag()
{
}
VidiunModerationFlag.prototype = new VidiunObjectBase();
/**
 * Moderation flag id
 *
 * @var int
 * @readonly
 */
VidiunModerationFlag.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunModerationFlag.prototype.partnerId = null;

/**
 * The user id that added the moderation flag
 *
 * @var string
 * @readonly
 */
VidiunModerationFlag.prototype.userId = null;

/**
 * The type of the moderation flag (entry or user)
 *
 * @var VidiunModerationObjectType
 * @readonly
 */
VidiunModerationFlag.prototype.moderationObjectType = null;

/**
 * If moderation flag is set for entry, this is the flagged entry id
 *
 * @var string
 */
VidiunModerationFlag.prototype.flaggedEntryId = null;

/**
 * If moderation flag is set for user, this is the flagged user id
 *
 * @var string
 */
VidiunModerationFlag.prototype.flaggedUserId = null;

/**
 * The moderation flag status
 *
 * @var VidiunModerationFlagStatus
 * @readonly
 */
VidiunModerationFlag.prototype.status = null;

/**
 * The comment that was added to the flag
 *
 * @var string
 */
VidiunModerationFlag.prototype.comments = null;

/**
 * 
 *
 * @var VidiunModerationFlagType
 */
VidiunModerationFlag.prototype.flagType = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunModerationFlag.prototype.createdAt = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunModerationFlag.prototype.updatedAt = null;


function VidiunModerationFlagListResponse()
{
}
VidiunModerationFlagListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunModerationFlagArray
 * @readonly
 */
VidiunModerationFlagListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunModerationFlagListResponse.prototype.totalCount = null;


function VidiunNotification()
{
}
VidiunNotification.prototype = new VidiunBaseJob();
/**
 * 
 *
 * @var string
 */
VidiunNotification.prototype.puserId = null;

/**
 * 
 *
 * @var VidiunNotificationType
 */
VidiunNotification.prototype.type = null;

/**
 * 
 *
 * @var string
 */
VidiunNotification.prototype.objectId = null;

/**
 * 
 *
 * @var VidiunNotificationStatus
 */
VidiunNotification.prototype.status = null;

/**
 * 
 *
 * @var string
 */
VidiunNotification.prototype.notificationData = null;

/**
 * 
 *
 * @var int
 */
VidiunNotification.prototype.numberOfAttempts = null;

/**
 * 
 *
 * @var string
 */
VidiunNotification.prototype.notificationResult = null;

/**
 * 
 *
 * @var VidiunNotificationObjectType
 */
VidiunNotification.prototype.objType = null;

function VidiunNotificationJobData()
{
}
VidiunNotificationJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunNotificationJobData.prototype.userId = null;

/**
 * 
 *
 * @var VidiunNotificationType
 */
VidiunNotificationJobData.prototype.type = null;

/**
 * 
 *
 * @var string
 */
VidiunNotificationJobData.prototype.typeAsString = null;

/**
 * 
 *
 * @var string
 */
VidiunNotificationJobData.prototype.objectId = null;

/**
 * 
 *
 * @var VidiunNotificationStatus
 */
VidiunNotificationJobData.prototype.status = null;

/**
 * 
 *
 * @var string
 */
VidiunNotificationJobData.prototype.data = null;

/**
 * 
 *
 * @var int
 */
VidiunNotificationJobData.prototype.numberOfAttempts = null;

/**
 * 
 *
 * @var string
 */
VidiunNotificationJobData.prototype.notificationResult = null;

/**
 * 
 *
 * @var VidiunNotificationObjectType
 */
VidiunNotificationJobData.prototype.objType = null;


function VidiunPartner()
{
}
VidiunPartner.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPartner.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.name = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.website = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.notificationUrl = null;

/**
 * 
 *
 * @var int
 */
VidiunPartner.prototype.appearInSearch = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunPartner.prototype.createdAt = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.adminName = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.adminEmail = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.description = null;

/**
 * 
 *
 * @var VidiunCommercialUseType
 */
VidiunPartner.prototype.commercialUse = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.landingPage = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.userLandingPage = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.contentCategories = null;

/**
 * 
 *
 * @var VidiunPartnerType
 */
VidiunPartner.prototype.type = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.phone = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.describeYourself = null;

/**
 * 
 *
 * @var bool
 */
VidiunPartner.prototype.adultContent = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.defConversionProfileType = null;

/**
 * 
 *
 * @var int
 */
VidiunPartner.prototype.notify = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPartner.prototype.status = null;

/**
 * 
 *
 * @var int
 */
VidiunPartner.prototype.allowQuickEdit = null;

/**
 * 
 *
 * @var int
 */
VidiunPartner.prototype.mergeEntryLists = null;

/**
 * 
 *
 * @var string
 */
VidiunPartner.prototype.notificationsConfig = null;

/**
 * 
 *
 * @var int
 */
VidiunPartner.prototype.maxUploadSize = null;

/**
 * readonly
 *
 * @var int
 */
VidiunPartner.prototype.partnerPackage = null;

/**
 * readonly
 *
 * @var string
 */
VidiunPartner.prototype.secret = null;

/**
 * readonly
 *
 * @var string
 */
VidiunPartner.prototype.adminSecret = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunPartner.prototype.cmsPassword = null;

/**
 * readonly
 *
 * @var int
 */
VidiunPartner.prototype.allowMultiNotification = null;


function VidiunPartnerFilter()
{
}
VidiunPartnerFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var string
 */
VidiunPartnerFilter.prototype.nameLike = null;

/**
 * 
 *
 * @var string
 */
VidiunPartnerFilter.prototype.nameMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunPartnerFilter.prototype.nameMultiLikeAnd = null;

/**
 * 
 *
 * @var string
 */
VidiunPartnerFilter.prototype.nameEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunPartnerFilter.prototype.statusEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunPartnerFilter.prototype.statusIn = null;


function VidiunPartnerUsage()
{
}
VidiunPartnerUsage.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var float
 * @readonly
 */
VidiunPartnerUsage.prototype.hostingGB = null;

/**
 * 
 *
 * @var float
 * @readonly
 */
VidiunPartnerUsage.prototype.Percent = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPartnerUsage.prototype.packageBW = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPartnerUsage.prototype.usageGB = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPartnerUsage.prototype.reachedLimitDate = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunPartnerUsage.prototype.usageGraph = null;


function VidiunPlaylist()
{
}
VidiunPlaylist.prototype = new VidiunBaseEntry();
/**
 * Content of the playlist - 
	 * XML if the playlistType is dynamic 
	 * text if the playlistType is static 
	 * url if the playlistType is mRss 
 *
 * @var string
 */
VidiunPlaylist.prototype.playlistContent = null;

/**
 * 
 *
 * @var VidiunMediaEntryFilterForPlaylistArray
 */
VidiunPlaylist.prototype.filters = null;

/**
 * 
 *
 * @var int
 */
VidiunPlaylist.prototype.totalResults = null;

/**
 * Type of playlist  
 *
 * @var VidiunPlaylistType
 */
VidiunPlaylist.prototype.playlistType = null;

/**
 * Number of plays
 *
 * @var int
 * @readonly
 */
VidiunPlaylist.prototype.plays = null;

/**
 * Number of views
 *
 * @var int
 * @readonly
 */
VidiunPlaylist.prototype.views = null;

/**
 * The duration in seconds
 *
 * @var int
 * @readonly
 */
VidiunPlaylist.prototype.duration = null;


function VidiunPlaylistFilter()
{
}
VidiunPlaylistFilter.prototype = new VidiunBaseEntryFilter();

function VidiunPlaylistListResponse()
{
}
VidiunPlaylistListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunPlaylistArray
 * @readonly
 */
VidiunPlaylistListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunPlaylistListResponse.prototype.totalCount = null;


function VidiunPostConvertJobData()
{
}
VidiunPostConvertJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunPostConvertJobData.prototype.srcFileSyncLocalPath = null;

/**
 * 
 *
 * @var string
 */
VidiunPostConvertJobData.prototype.flavorAssetId = null;

/**
 * Indicates if a thumbnail should be created
	 * 
 *
 * @var bool
 */
VidiunPostConvertJobData.prototype.createThumb = null;

/**
 * The path of the created thumbnail
	 * 
 *
 * @var string
 */
VidiunPostConvertJobData.prototype.thumbPath = null;

/**
 * The position of the thumbnail in the media file
	 * 
 *
 * @var int
 */
VidiunPostConvertJobData.prototype.thumbOffset = null;

/**
 * The height of the movie, will be used to comapare if this thumbnail is the best we can have
	 * 
 *
 * @var int
 */
VidiunPostConvertJobData.prototype.thumbHeight = null;

/**
 * The bit rate of the movie, will be used to comapare if this thumbnail is the best we can have
	 * 
 *
 * @var int
 */
VidiunPostConvertJobData.prototype.thumbBitrate = null;

/**
 * 
 *
 * @var int
 */
VidiunPostConvertJobData.prototype.flavorParamsOutputId = null;


function VidiunSessionRestriction()
{
}
VidiunSessionRestriction.prototype = new VidiunBaseRestriction();

function VidiunPreviewRestriction()
{
}
VidiunPreviewRestriction.prototype = new VidiunSessionRestriction();
/**
 * The preview restriction length 
	 * 
 *
 * @var int
 */
VidiunPreviewRestriction.prototype.previewLength = null;


function VidiunPullJobData()
{
}
VidiunPullJobData.prototype = new VidiunJobData();
/**
 * 
 *
 * @var string
 */
VidiunPullJobData.prototype.srcFileUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunPullJobData.prototype.destFileLocalPath = null;


function VidiunRemoteConvertJobData()
{
}
VidiunRemoteConvertJobData.prototype = new VidiunConvartableJobData();
/**
 * 
 *
 * @var string
 */
VidiunRemoteConvertJobData.prototype.srcFileUrl = null;

/**
 * Should be set by the API
	 * 
 *
 * @var string
 */
VidiunRemoteConvertJobData.prototype.destFileUrl = null;


function VidiunReportGraph()
{
}
VidiunReportGraph.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunReportGraph.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunReportGraph.prototype.data = null;


function VidiunReportInputFilter()
{
}
VidiunReportInputFilter.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 */
VidiunReportInputFilter.prototype.fromDate = null;

/**
 * 
 *
 * @var int
 */
VidiunReportInputFilter.prototype.toDate = null;

/**
 * 
 *
 * @var string
 */
VidiunReportInputFilter.prototype.keywords = null;

/**
 * 
 *
 * @var bool
 */
VidiunReportInputFilter.prototype.searchInTags = null;

/**
 * 
 *
 * @var bool
 */
VidiunReportInputFilter.prototype.searchInAdminTags = null;

/**
 * 
 *
 * @var string
 */
VidiunReportInputFilter.prototype.categories = null;


function VidiunReportTable()
{
}
VidiunReportTable.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunReportTable.prototype.header = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunReportTable.prototype.data = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunReportTable.prototype.totalCount = null;


function VidiunReportTotal()
{
}
VidiunReportTotal.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunReportTotal.prototype.header = null;

/**
 * 
 *
 * @var string
 */
VidiunReportTotal.prototype.data = null;


function VidiunSearch()
{
}
VidiunSearch.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunSearch.prototype.keyWords = null;

/**
 * 
 *
 * @var VidiunSearchProviderType
 */
VidiunSearch.prototype.searchSource = null;

/**
 * 
 *
 * @var VidiunMediaType
 */
VidiunSearch.prototype.mediaType = null;

/**
 * Use this field to pass dynamic data for searching
	 * For example - if you set this field to "mymovies_$partner_id"
	 * The $partner_id will be automatically replcaed with your real partner Id
	 * 
 *
 * @var string
 */
VidiunSearch.prototype.extraData = null;

/**
 * 
 *
 * @var string
 */
VidiunSearch.prototype.authData = null;


function VidiunSearchAuthData()
{
}
VidiunSearchAuthData.prototype = new VidiunObjectBase();
/**
 * The authentication data that further should be used for search
	 * 
 *
 * @var string
 */
VidiunSearchAuthData.prototype.authData = null;

/**
 * Login URL when user need to sign-in and authorize the search
 *
 * @var string
 */
VidiunSearchAuthData.prototype.loginUrl = null;

/**
 * Information when there was an error
 *
 * @var string
 */
VidiunSearchAuthData.prototype.message = null;


function VidiunSearchResult()
{
}
VidiunSearchResult.prototype = new VidiunSearch();
/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.title = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.thumbUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.description = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.tags = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.url = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.sourceLink = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.credit = null;

/**
 * 
 *
 * @var VidiunLicenseType
 */
VidiunSearchResult.prototype.licenseType = null;

/**
 * 
 *
 * @var string
 */
VidiunSearchResult.prototype.flashPlaybackType = null;


function VidiunSearchResultResponse()
{
}
VidiunSearchResultResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunSearchResultArray
 * @readonly
 */
VidiunSearchResultResponse.prototype.objects = null;

/**
 * 
 *
 * @var bool
 * @readonly
 */
VidiunSearchResultResponse.prototype.needMediaInfo = null;


function VidiunSiteRestriction()
{
}
VidiunSiteRestriction.prototype = new VidiunBaseRestriction();
/**
 * The site restriction type (allow or deny)
	 * 
 *
 * @var VidiunSiteRestrictionType
 */
VidiunSiteRestriction.prototype.siteRestrictionType = null;

/**
 * Comma separated list of sites (domains) to allow or deny
	 * 
 *
 * @var string
 */
VidiunSiteRestriction.prototype.siteList = null;


function VidiunStartWidgetSessionResponse()
{
}
VidiunStartWidgetSessionResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunStartWidgetSessionResponse.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunStartWidgetSessionResponse.prototype.vs = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunStartWidgetSessionResponse.prototype.userId = null;


function VidiunStatsEvent()
{
}
VidiunStatsEvent.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunStatsEvent.prototype.clientVer = null;

/**
 * 
 *
 * @var VidiunStatsEventType
 */
VidiunStatsEvent.prototype.eventType = null;

/**
 * the client's timestamp of this event
	 * 
 *
 * @var float
 */
VidiunStatsEvent.prototype.eventTimestamp = null;

/**
 * a unique string generated by the client that will represent the client-side session: the primary component will pass it on to other components that sprout from it
 *
 * @var string
 */
VidiunStatsEvent.prototype.sessionId = null;

/**
 * 
 *
 * @var int
 */
VidiunStatsEvent.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunStatsEvent.prototype.entryId = null;

/**
 * the UV cookie - creates in the operational system and should be passed on ofr every event 
 *
 * @var string
 */
VidiunStatsEvent.prototype.uniqueViewer = null;

/**
 * 
 *
 * @var string
 */
VidiunStatsEvent.prototype.widgetId = null;

/**
 * 
 *
 * @var int
 */
VidiunStatsEvent.prototype.uiconfId = null;

/**
 * the partner's user id 
 *
 * @var string
 */
VidiunStatsEvent.prototype.userId = null;

/**
 * the timestamp along the video when the event happend 
 *
 * @var int
 */
VidiunStatsEvent.prototype.currentPoint = null;

/**
 * the duration of the video in milliseconds - will make it much faster than quering the db for each entry 
 *
 * @var int
 */
VidiunStatsEvent.prototype.duration = null;

/**
 * will be retrieved from the request of the user 
 *
 * @var string
 * @readonly
 */
VidiunStatsEvent.prototype.userIp = null;

/**
 * the time in milliseconds the event took
 *
 * @var int
 */
VidiunStatsEvent.prototype.processDuration = null;

/**
 * the id of the GUI control - will be used in the future to better understand what the user clicked
 *
 * @var string
 */
VidiunStatsEvent.prototype.controlId = null;

/**
 * true if the user ever used seek in this session 
 *
 * @var bool
 */
VidiunStatsEvent.prototype.seek = null;

/**
 * timestamp of the new point on the timeline of the video after the user seeks 
 *
 * @var int
 */
VidiunStatsEvent.prototype.newPoint = null;

/**
 * the referrer of the client
 *
 * @var string
 */
VidiunStatsEvent.prototype.referrer = null;

/**
 * will indicate if the event is thrown for the first video in the session
 *
 * @var bool
 */
VidiunStatsEvent.prototype.isFirstInSession = null;


function VidiunStatsVmcEvent()
{
}
VidiunStatsVmcEvent.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.clientVer = null;

/**
 * 
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.vmcEventActionPath = null;

/**
 * 
 *
 * @var VidiunStatsVmcEventType
 */
VidiunStatsVmcEvent.prototype.vmcEventType = null;

/**
 * the client's timestamp of this event
	 * 
 *
 * @var float
 */
VidiunStatsVmcEvent.prototype.eventTimestamp = null;

/**
 * a unique string generated by the client that will represent the client-side session: the primary component will pass it on to other components that sprout from it
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.sessionId = null;

/**
 * 
 *
 * @var int
 */
VidiunStatsVmcEvent.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.entryId = null;

/**
 * 
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.widgetId = null;

/**
 * 
 *
 * @var int
 */
VidiunStatsVmcEvent.prototype.uiconfId = null;

/**
 * the partner's user id 
 *
 * @var string
 */
VidiunStatsVmcEvent.prototype.userId = null;

/**
 * will be retrieved from the request of the user 
 *
 * @var string
 * @readonly
 */
VidiunStatsVmcEvent.prototype.userIp = null;


function VidiunSyndicationFeedEntryCount()
{
}
VidiunSyndicationFeedEntryCount.prototype = new VidiunObjectBase();
/**
 * the total count of entries that should appear in the feed without flavor filtering
 *
 * @var int
 */
VidiunSyndicationFeedEntryCount.prototype.totalEntryCount = null;

/**
 * count of entries that will appear in the feed (including all relevant filters)
 *
 * @var int
 */
VidiunSyndicationFeedEntryCount.prototype.actualEntryCount = null;

/**
 * count of entries that requires transcoding in order to be included in feed
 *
 * @var int
 */
VidiunSyndicationFeedEntryCount.prototype.requireTranscodingCount = null;


function VidiunSystemUser()
{
}
VidiunSystemUser.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunSystemUser.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunSystemUser.prototype.email = null;

/**
 * 
 *
 * @var string
 */
VidiunSystemUser.prototype.firstName = null;

/**
 * 
 *
 * @var string
 */
VidiunSystemUser.prototype.lastName = null;

/**
 * 
 *
 * @var string
 */
VidiunSystemUser.prototype.password = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunSystemUser.prototype.createdBy = null;

/**
 * 
 *
 * @var VidiunSystemUserStatus
 */
VidiunSystemUser.prototype.status = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunSystemUser.prototype.statusUpdatedAt = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunSystemUser.prototype.createdAt = null;


function VidiunSystemUserFilter()
{
}
VidiunSystemUserFilter.prototype = new VidiunFilter();

function VidiunSystemUserListResponse()
{
}
VidiunSystemUserListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunSystemUserArray
 * @readonly
 */
VidiunSystemUserListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunSystemUserListResponse.prototype.totalCount = null;


function VidiunTubeMogulSyndicationFeed()
{
}
VidiunTubeMogulSyndicationFeed.prototype = new VidiunBaseSyndicationFeed();
/**
 * 
 *
 * @var VidiunTubeMogulSyndicationFeedCategories
 * @readonly
 */
VidiunTubeMogulSyndicationFeed.prototype.category = null;


function VidiunTubeMogulSyndicationFeedFilter()
{
}
VidiunTubeMogulSyndicationFeedFilter.prototype = new VidiunBaseSyndicationFeedFilter();

function VidiunUiConf()
{
}
VidiunUiConf.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUiConf.prototype.id = null;

/**
 * Name of the uiConf, this is not a primary key
 *
 * @var string
 */
VidiunUiConf.prototype.name = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.description = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUiConf.prototype.partnerId = null;

/**
 * 
 *
 * @var VidiunUiConfObjType
 */
VidiunUiConf.prototype.objType = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunUiConf.prototype.objTypeAsString = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConf.prototype.width = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConf.prototype.height = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.htmlParams = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.swfUrl = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunUiConf.prototype.confFilePath = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.confFile = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.confFileFeatures = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.confVars = null;

/**
 * 
 *
 * @var bool
 */
VidiunUiConf.prototype.useCdn = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.tags = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConf.prototype.swfUrlVersion = null;

/**
 * Entry creation date as Unix timestamp (In seconds)
 *
 * @var int
 * @readonly
 */
VidiunUiConf.prototype.createdAt = null;

/**
 * Entry creation date as Unix timestamp (In seconds)
 *
 * @var int
 * @readonly
 */
VidiunUiConf.prototype.updatedAt = null;

/**
 * 
 *
 * @var VidiunUiConfCreationMode
 */
VidiunUiConf.prototype.creationMode = null;


function VidiunUiConfFilter()
{
}
VidiunUiConfFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var int
 */
VidiunUiConfFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConfFilter.prototype.idIn = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConfFilter.prototype.nameLike = null;

/**
 * 
 *
 * @var VidiunUiConfObjType
 */
VidiunUiConfFilter.prototype.objTypeEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConfFilter.prototype.tagsMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunUiConfFilter.prototype.tagsMultiLikeAnd = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConfFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConfFilter.prototype.createdAtLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConfFilter.prototype.updatedAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunUiConfFilter.prototype.updatedAtLessThanOrEqual = null;


function VidiunUiConfListResponse()
{
}
VidiunUiConfListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunUiConfArray
 * @readonly
 */
VidiunUiConfListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUiConfListResponse.prototype.totalCount = null;


function VidiunUploadResponse()
{
}
VidiunUploadResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunUploadResponse.prototype.uploadTokenId = null;

/**
 * 
 *
 * @var int
 */
VidiunUploadResponse.prototype.fileSize = null;

/**
 * 
 *
 * @var VidiunUploadErrorCode
 */
VidiunUploadResponse.prototype.errorCode = null;

/**
 * 
 *
 * @var string
 */
VidiunUploadResponse.prototype.errorDescription = null;


function VidiunUser()
{
}
VidiunUser.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.id = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUser.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.screenName = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.fullName = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.email = null;

/**
 * 
 *
 * @var int
 */
VidiunUser.prototype.dateOfBirth = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.country = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.state = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.city = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.zip = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.thumbnailUrl = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.description = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.tags = null;

/**
 * Admin tags can be updated only by using an admin session
 *
 * @var string
 */
VidiunUser.prototype.adminTags = null;

/**
 * 
 *
 * @var VidiunGender
 */
VidiunUser.prototype.gender = null;

/**
 * 
 *
 * @var VidiunUserStatus
 */
VidiunUser.prototype.status = null;

/**
 * Creation date as Unix timestamp (In seconds)
 *
 * @var int
 * @readonly
 */
VidiunUser.prototype.createdAt = null;

/**
 * Last update date as Unix timestamp (In seconds)
 *
 * @var int
 * @readonly
 */
VidiunUser.prototype.updatedAt = null;

/**
 * Can be used to store various partner related data as a string 
 *
 * @var string
 */
VidiunUser.prototype.partnerData = null;

/**
 * 
 *
 * @var int
 */
VidiunUser.prototype.indexedPartnerDataInt = null;

/**
 * 
 *
 * @var string
 */
VidiunUser.prototype.indexedPartnerDataString = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUser.prototype.storageSize = null;


function VidiunUserFilter()
{
}
VidiunUserFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.idIn = null;

/**
 * 
 *
 * @var int
 */
VidiunUserFilter.prototype.partnerIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.screenNameLike = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.screenNameStartsWith = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.emailLike = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.emailStartsWith = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.tagsMultiLikeOr = null;

/**
 * 
 *
 * @var string
 */
VidiunUserFilter.prototype.tagsMultiLikeAnd = null;

/**
 * 
 *
 * @var int
 */
VidiunUserFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunUserFilter.prototype.createdAtLessThanOrEqual = null;


function VidiunUserListResponse()
{
}
VidiunUserListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunUserArray
 * @readonly
 */
VidiunUserListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunUserListResponse.prototype.totalCount = null;


function VidiunWidget()
{
}
VidiunWidget.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunWidget.prototype.id = null;

/**
 * 
 *
 * @var string
 */
VidiunWidget.prototype.sourceWidgetId = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunWidget.prototype.rootWidgetId = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunWidget.prototype.partnerId = null;

/**
 * 
 *
 * @var string
 */
VidiunWidget.prototype.entryId = null;

/**
 * 
 *
 * @var int
 */
VidiunWidget.prototype.uiConfId = null;

/**
 * 
 *
 * @var VidiunWidgetSecurityType
 */
VidiunWidget.prototype.securityType = null;

/**
 * 
 *
 * @var int
 */
VidiunWidget.prototype.securityPolicy = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunWidget.prototype.createdAt = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunWidget.prototype.updatedAt = null;

/**
 * Can be used to store various partner related data as a string 
 *
 * @var string
 */
VidiunWidget.prototype.partnerData = null;

/**
 * 
 *
 * @var string
 * @readonly
 */
VidiunWidget.prototype.widgetHTML = null;


function VidiunWidgetFilter()
{
}
VidiunWidgetFilter.prototype = new VidiunFilter();
/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.idEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.idIn = null;

/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.sourceWidgetIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.rootWidgetIdEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.partnerIdEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.entryIdEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.uiConfIdEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.createdAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.createdAtLessThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.updatedAtGreaterThanOrEqual = null;

/**
 * 
 *
 * @var int
 */
VidiunWidgetFilter.prototype.updatedAtLessThanOrEqual = null;

/**
 * 
 *
 * @var string
 */
VidiunWidgetFilter.prototype.partnerDataLike = null;


function VidiunWidgetListResponse()
{
}
VidiunWidgetListResponse.prototype = new VidiunObjectBase();
/**
 * 
 *
 * @var VidiunWidgetArray
 * @readonly
 */
VidiunWidgetListResponse.prototype.objects = null;

/**
 * 
 *
 * @var int
 * @readonly
 */
VidiunWidgetListResponse.prototype.totalCount = null;


function VidiunYahooSyndicationFeed()
{
}
VidiunYahooSyndicationFeed.prototype = new VidiunBaseSyndicationFeed();
/**
 * 
 *
 * @var VidiunYahooSyndicationFeedCategories
 * @readonly
 */
VidiunYahooSyndicationFeed.prototype.category = null;

/**
 * 
 *
 * @var VidiunYahooSyndicationFeedAdultValues
 */
VidiunYahooSyndicationFeed.prototype.adultContent = null;

/**
 * feed description
	 * 
 *
 * @var string
 */
VidiunYahooSyndicationFeed.prototype.feedDescription = null;

/**
 * feed landing page (i.e publisher website)
	 * 
 *
 * @var string
 */
VidiunYahooSyndicationFeed.prototype.feedLandingPage = null;


function VidiunYahooSyndicationFeedFilter()
{
}
VidiunYahooSyndicationFeedFilter.prototype = new VidiunBaseSyndicationFeedFilter();


function VidiunAccessControlService(client)
{
	this.init(client);
}

VidiunAccessControlService.prototype = new VidiunServiceBase();

VidiunAccessControlService.prototype.add = function(callback, accessControl)
{

	vparams = new Object();
	this.client.addParam(vparams, "accessControl", accessControl.toParams());
	this.client.queueServiceActionCall("accesscontrol", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAccessControlService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("accesscontrol", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAccessControlService.prototype.update = function(callback, id, accessControl)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "accessControl", accessControl.toParams());
	this.client.queueServiceActionCall("accesscontrol", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAccessControlService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("accesscontrol", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAccessControlService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("accesscontrol", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunAdminconsoleService(client)
{
	this.init(client);
}

VidiunAdminconsoleService.prototype = new VidiunServiceBase();

VidiunAdminconsoleService.prototype.listBatchJobs = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", toParams(filter));
	if (pager != null)
		this.client.addParam(vparams, "pager", toParams(pager));
	this.client.queueServiceActionCall("adminconsole", "listBatchJobs", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunAdminUserService(client)
{
	this.init(client);
}

VidiunAdminUserService.prototype = new VidiunServiceBase();

VidiunAdminUserService.prototype.updatePassword = function(callback, email, password, newEmail, newPassword)
{
	if(!newEmail)
		newEmail = "";
	if(!newPassword)
		newPassword = "";

	vparams = new Object();
	this.client.addParam(vparams, "email", email);
	this.client.addParam(vparams, "password", password);
	this.client.addParam(vparams, "newEmail", newEmail);
	this.client.addParam(vparams, "newPassword", newPassword);
	this.client.queueServiceActionCall("adminuser", "updatePassword", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAdminUserService.prototype.resetPassword = function(callback, email)
{

	vparams = new Object();
	this.client.addParam(vparams, "email", email);
	this.client.queueServiceActionCall("adminuser", "resetPassword", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunAdminUserService.prototype.login = function(callback, email, password)
{

	vparams = new Object();
	this.client.addParam(vparams, "email", email);
	this.client.addParam(vparams, "password", password);
	this.client.queueServiceActionCall("adminuser", "login", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunBaseEntryService(client)
{
	this.init(client);
}

VidiunBaseEntryService.prototype = new VidiunServiceBase();

VidiunBaseEntryService.prototype.addFromUploadedFile = function(callback, entry, uploadTokenId, type)
{
	if(!type)
		type = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entry", entry.toParams());
	this.client.addParam(vparams, "uploadTokenId", uploadTokenId);
	this.client.addParam(vparams, "type", type);
	this.client.queueServiceActionCall("baseentry", "addFromUploadedFile", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.get = function(callback, entryId, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("baseentry", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.update = function(callback, entryId, baseEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "baseEntry", baseEntry.toParams());
	this.client.queueServiceActionCall("baseentry", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.getByIds = function(callback, entryIds)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryIds", entryIds);
	this.client.queueServiceActionCall("baseentry", "getByIds", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.delete = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("baseentry", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("baseentry", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.count = function(callback, filter)
{
	if(!filter)
		filter = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	this.client.queueServiceActionCall("baseentry", "count", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.upload = function(callback, fileData)
{

	vparams = new Object();
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.queueServiceActionCall("baseentry", "upload", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.updateThumbnailJpeg = function(callback, entryId, fileData)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.queueServiceActionCall("baseentry", "updateThumbnailJpeg", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.updateThumbnailFromUrl = function(callback, entryId, url)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "url", url);
	this.client.queueServiceActionCall("baseentry", "updateThumbnailFromUrl", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.updateThumbnailFromSourceEntry = function(callback, entryId, sourceEntryId, timeOffset)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "sourceEntryId", sourceEntryId);
	this.client.addParam(vparams, "timeOffset", timeOffset);
	this.client.queueServiceActionCall("baseentry", "updateThumbnailFromSourceEntry", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.flag = function(callback, moderationFlag)
{

	vparams = new Object();
	this.client.addParam(vparams, "moderationFlag", moderationFlag.toParams());
	this.client.queueServiceActionCall("baseentry", "flag", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.reject = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("baseentry", "reject", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.approve = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("baseentry", "approve", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.listFlags = function(callback, entryId, pager)
{
	if(!pager)
		pager = null;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("baseentry", "listFlags", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.anonymousRank = function(callback, entryId, rank)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "rank", rank);
	this.client.queueServiceActionCall("baseentry", "anonymousRank", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBaseEntryService.prototype.getExtraData = function(callback, entryId, extraDataParams)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "extraDataParams", extraDataParams.toParams());
	this.client.queueServiceActionCall("baseentry", "getExtraData", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunBulkUploadService(client)
{
	this.init(client);
}

VidiunBulkUploadService.prototype = new VidiunServiceBase();

VidiunBulkUploadService.prototype.add = function(callback, conversionProfileId, csvFileData)
{

	vparams = new Object();
	this.client.addParam(vparams, "conversionProfileId", conversionProfileId);
	vfiles = new Object();
	this.client.addParam(vfiles, "csvFileData", csvFileData);
	this.client.queueServiceActionCall("bulkupload", "add", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBulkUploadService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("bulkupload", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunBulkUploadService.prototype.listAction = function(callback, pager)
{
	if(!pager)
		pager = null;

	vparams = new Object();
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("bulkupload", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunCategoryService(client)
{
	this.init(client);
}

VidiunCategoryService.prototype = new VidiunServiceBase();

VidiunCategoryService.prototype.add = function(callback, category)
{

	vparams = new Object();
	this.client.addParam(vparams, "category", category.toParams());
	this.client.queueServiceActionCall("category", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunCategoryService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("category", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunCategoryService.prototype.update = function(callback, id, category)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "category", category.toParams());
	this.client.queueServiceActionCall("category", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunCategoryService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("category", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunCategoryService.prototype.listAction = function(callback, filter)
{
	if(!filter)
		filter = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	this.client.queueServiceActionCall("category", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunConversionProfileService(client)
{
	this.init(client);
}

VidiunConversionProfileService.prototype = new VidiunServiceBase();

VidiunConversionProfileService.prototype.add = function(callback, conversionProfile)
{

	vparams = new Object();
	this.client.addParam(vparams, "conversionProfile", conversionProfile.toParams());
	this.client.queueServiceActionCall("conversionprofile", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunConversionProfileService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("conversionprofile", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunConversionProfileService.prototype.update = function(callback, id, conversionProfile)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "conversionProfile", conversionProfile.toParams());
	this.client.queueServiceActionCall("conversionprofile", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunConversionProfileService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("conversionprofile", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunConversionProfileService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("conversionprofile", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunDataService(client)
{
	this.init(client);
}

VidiunDataService.prototype = new VidiunServiceBase();

VidiunDataService.prototype.add = function(callback, dataEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "dataEntry", dataEntry.toParams());
	this.client.queueServiceActionCall("data", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunDataService.prototype.get = function(callback, entryId, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("data", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunDataService.prototype.update = function(callback, entryId, documentEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "documentEntry", documentEntry.toParams());
	this.client.queueServiceActionCall("data", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunDataService.prototype.delete = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("data", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunDataService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("data", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunFlavorAssetService(client)
{
	this.init(client);
}

VidiunFlavorAssetService.prototype = new VidiunServiceBase();

VidiunFlavorAssetService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorasset", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.getByEntryId = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("flavorasset", "getByEntryId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.getWebPlayableByEntryId = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("flavorasset", "getWebPlayableByEntryId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.convert = function(callback, entryId, flavorParamsId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "flavorParamsId", flavorParamsId);
	this.client.queueServiceActionCall("flavorasset", "convert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.reconvert = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorasset", "reconvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorasset", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.getDownloadUrl = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorasset", "getDownloadUrl", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorAssetService.prototype.getFlavorAssetsWithParams = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("flavorasset", "getFlavorAssetsWithParams", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunFlavorParamsService(client)
{
	this.init(client);
}

VidiunFlavorParamsService.prototype = new VidiunServiceBase();

VidiunFlavorParamsService.prototype.add = function(callback, flavorParams)
{

	vparams = new Object();
	this.client.addParam(vparams, "flavorParams", flavorParams.toParams());
	this.client.queueServiceActionCall("flavorparams", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorParamsService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorparams", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorParamsService.prototype.update = function(callback, id, flavorParams)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "flavorParams", flavorParams.toParams());
	this.client.queueServiceActionCall("flavorparams", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorParamsService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("flavorparams", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorParamsService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("flavorparams", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunFlavorParamsService.prototype.getByConversionProfileId = function(callback, conversionProfileId)
{

	vparams = new Object();
	this.client.addParam(vparams, "conversionProfileId", conversionProfileId);
	this.client.queueServiceActionCall("flavorparams", "getByConversionProfileId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunJobsService(client)
{
	this.init(client);
}

VidiunJobsService.prototype = new VidiunServiceBase();

VidiunJobsService.prototype.getImportStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getImportStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteImport = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteImport", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortImport = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortImport", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryImport = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryImport", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getBulkUploadStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getBulkUploadStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteBulkUpload = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteBulkUpload", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortBulkUpload = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortBulkUpload", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryBulkUpload = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryBulkUpload", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getConvertStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getConvertStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getConvertProfileStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getConvertProfileStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getRemoteConvertStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getRemoteConvertStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteRemoteConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteRemoteConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortRemoteConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortRemoteConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryRemoteConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryRemoteConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteConvertProfile = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteConvertProfile", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortConvertProfile = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortConvertProfile", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryConvertProfile = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryConvertProfile", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getPostConvertStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getPostConvertStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deletePostConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deletePostConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortPostConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortPostConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryPostConvert = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryPostConvert", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getPullStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getPullStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deletePull = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deletePull", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortPull = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortPull", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryPull = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryPull", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getExtractMediaStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getExtractMediaStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteExtractMedia = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteExtractMedia", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortExtractMedia = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortExtractMedia", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryExtractMedia = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryExtractMedia", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getNotificationStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getNotificationStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteNotification = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteNotification", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortNotification = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortNotification", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryNotification = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryNotification", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getMailStatus = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "getMailStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteMail = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "deleteMail", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortMail = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "abortMail", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryMail = function(callback, jobId)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.queueServiceActionCall("jobs", "retryMail", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.addMailJob = function(callback, mailJobData)
{

	vparams = new Object();
	this.client.addParam(vparams, "mailJobData", mailJobData.toParams());
	this.client.queueServiceActionCall("jobs", "addMailJob", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.addBatchJob = function(callback, batchJob)
{

	vparams = new Object();
	this.client.addParam(vparams, "batchJob", batchJob.toParams());
	this.client.queueServiceActionCall("jobs", "addBatchJob", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.getStatus = function(callback, jobId, jobType)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.addParam(vparams, "jobType", jobType);
	this.client.queueServiceActionCall("jobs", "getStatus", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.deleteJob = function(callback, jobId, jobType)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.addParam(vparams, "jobType", jobType);
	this.client.queueServiceActionCall("jobs", "deleteJob", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.abortJob = function(callback, jobId, jobType)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.addParam(vparams, "jobType", jobType);
	this.client.queueServiceActionCall("jobs", "abortJob", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.retryJob = function(callback, jobId, jobType)
{

	vparams = new Object();
	this.client.addParam(vparams, "jobId", jobId);
	this.client.addParam(vparams, "jobType", jobType);
	this.client.queueServiceActionCall("jobs", "retryJob", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunJobsService.prototype.listBatchJobs = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", toParams(filter));
	if (pager != null)
		this.client.addParam(vparams, "pager", toParams(pager));
	this.client.queueServiceActionCall("jobs", "listBatchJobs", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunMediaService(client)
{
	this.init(client);
}

VidiunMediaService.prototype = new VidiunServiceBase();

VidiunMediaService.prototype.addFromBulk = function(callback, mediaEntry, url, bulkUploadId)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	this.client.addParam(vparams, "url", url);
	this.client.addParam(vparams, "bulkUploadId", bulkUploadId);
	this.client.queueServiceActionCall("media", "addFromBulk", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.addFromUrl = function(callback, mediaEntry, url)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	this.client.addParam(vparams, "url", url);
	this.client.queueServiceActionCall("media", "addFromUrl", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.addFromSearchResult = function(callback, mediaEntry, searchResult)
{
	if(!mediaEntry)
		mediaEntry = null;
	if(!searchResult)
		searchResult = null;

	vparams = new Object();
	if (mediaEntry != null)
		this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	if (searchResult != null)
		this.client.addParam(vparams, "searchResult", searchResult.toParams());
	this.client.queueServiceActionCall("media", "addFromSearchResult", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.addFromUploadedFile = function(callback, mediaEntry, uploadTokenId)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	this.client.addParam(vparams, "uploadTokenId", uploadTokenId);
	this.client.queueServiceActionCall("media", "addFromUploadedFile", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.addFromRecordedWebcam = function(callback, mediaEntry, webcamTokenId)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	this.client.addParam(vparams, "webcamTokenId", webcamTokenId);
	this.client.queueServiceActionCall("media", "addFromRecordedWebcam", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.get = function(callback, entryId, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("media", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.update = function(callback, entryId, mediaEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "mediaEntry", mediaEntry.toParams());
	this.client.queueServiceActionCall("media", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.delete = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("media", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("media", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.count = function(callback, filter)
{
	if(!filter)
		filter = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	this.client.queueServiceActionCall("media", "count", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.upload = function(callback, fileData)
{

	vparams = new Object();
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.queueServiceActionCall("media", "upload", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.updateThumbnail = function(callback, entryId, timeOffset)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "timeOffset", timeOffset);
	this.client.queueServiceActionCall("media", "updateThumbnail", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.updateThumbnailFromSourceEntry = function(callback, entryId, sourceEntryId, timeOffset)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "sourceEntryId", sourceEntryId);
	this.client.addParam(vparams, "timeOffset", timeOffset);
	this.client.queueServiceActionCall("media", "updateThumbnailFromSourceEntry", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.updateThumbnailJpeg = function(callback, entryId, fileData)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.queueServiceActionCall("media", "updateThumbnailJpeg", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.updateThumbnailFromUrl = function(callback, entryId, url)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "url", url);
	this.client.queueServiceActionCall("media", "updateThumbnailFromUrl", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.requestConversion = function(callback, entryId, fileFormat)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "fileFormat", fileFormat);
	this.client.queueServiceActionCall("media", "requestConversion", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.flag = function(callback, moderationFlag)
{

	vparams = new Object();
	this.client.addParam(vparams, "moderationFlag", moderationFlag.toParams());
	this.client.queueServiceActionCall("media", "flag", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.reject = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("media", "reject", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.approve = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("media", "approve", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.listFlags = function(callback, entryId, pager)
{
	if(!pager)
		pager = null;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("media", "listFlags", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMediaService.prototype.anonymousRank = function(callback, entryId, rank)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "rank", rank);
	this.client.queueServiceActionCall("media", "anonymousRank", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunMixingService(client)
{
	this.init(client);
}

VidiunMixingService.prototype = new VidiunServiceBase();

VidiunMixingService.prototype.add = function(callback, mixEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "mixEntry", mixEntry.toParams());
	this.client.queueServiceActionCall("mixing", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.get = function(callback, entryId, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("mixing", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.update = function(callback, entryId, mixEntry)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "mixEntry", mixEntry.toParams());
	this.client.queueServiceActionCall("mixing", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.delete = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("mixing", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("mixing", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.count = function(callback, filter)
{
	if(!filter)
		filter = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	this.client.queueServiceActionCall("mixing", "count", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.cloneAction = function(callback, entryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.queueServiceActionCall("mixing", "clone", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.appendMediaEntry = function(callback, mixEntryId, mediaEntryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "mixEntryId", mixEntryId);
	this.client.addParam(vparams, "mediaEntryId", mediaEntryId);
	this.client.queueServiceActionCall("mixing", "appendMediaEntry", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.requestFlattening = function(callback, entryId, fileFormat, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "fileFormat", fileFormat);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("mixing", "requestFlattening", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.getMixesByMediaId = function(callback, mediaEntryId)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaEntryId", mediaEntryId);
	this.client.queueServiceActionCall("mixing", "getMixesByMediaId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.getReadyMediaEntries = function(callback, mixId, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "mixId", mixId);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("mixing", "getReadyMediaEntries", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunMixingService.prototype.anonymousRank = function(callback, entryId, rank)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "rank", rank);
	this.client.queueServiceActionCall("mixing", "anonymousRank", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunNotificationService(client)
{
	this.init(client);
}

VidiunNotificationService.prototype = new VidiunServiceBase();

VidiunNotificationService.prototype.getClientNotification = function(callback, entryId, type)
{

	vparams = new Object();
	this.client.addParam(vparams, "entryId", entryId);
	this.client.addParam(vparams, "type", type);
	this.client.queueServiceActionCall("notification", "getClientNotification", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunPartnerService(client)
{
	this.init(client);
}

VidiunPartnerService.prototype = new VidiunServiceBase();

VidiunPartnerService.prototype.register = function(callback, partner, cmsPassword)
{
	if(!cmsPassword)
		cmsPassword = "";

	vparams = new Object();
	this.client.addParam(vparams, "partner", partner.toParams());
	this.client.addParam(vparams, "cmsPassword", cmsPassword);
	this.client.queueServiceActionCall("partner", "register", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPartnerService.prototype.update = function(callback, partner, allowEmpty)
{
	if(!allowEmpty)
		allowEmpty = false;

	vparams = new Object();
	this.client.addParam(vparams, "partner", partner.toParams());
	this.client.addParam(vparams, "allowEmpty", allowEmpty);
	this.client.queueServiceActionCall("partner", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPartnerService.prototype.getSecrets = function(callback, partnerId, adminEmail, cmsPassword)
{

	vparams = new Object();
	this.client.addParam(vparams, "partnerId", partnerId);
	this.client.addParam(vparams, "adminEmail", adminEmail);
	this.client.addParam(vparams, "cmsPassword", cmsPassword);
	this.client.queueServiceActionCall("partner", "getSecrets", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPartnerService.prototype.getInfo = function(callback)
{

	vparams = new Object();
	this.client.queueServiceActionCall("partner", "getInfo", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPartnerService.prototype.get = function(callback, partnerId)
{

	vparams = new Object();
	this.client.addParam(vparams, "partnerId", partnerId);
	this.client.queueServiceActionCall("partner", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPartnerService.prototype.getUsage = function(callback, year, month, resolution)
{
	if(!year)
		year = "";
	if(!month)
		month = 1;
	if(!resolution)
		resolution = "days";

	vparams = new Object();
	this.client.addParam(vparams, "year", year);
	this.client.addParam(vparams, "month", month);
	this.client.addParam(vparams, "resolution", resolution);
	this.client.queueServiceActionCall("partner", "getUsage", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunPlaylistService(client)
{
	this.init(client);
}

VidiunPlaylistService.prototype = new VidiunServiceBase();

VidiunPlaylistService.prototype.add = function(callback, playlist, updateStats)
{
	if(!updateStats)
		updateStats = false;

	vparams = new Object();
	this.client.addParam(vparams, "playlist", playlist.toParams());
	this.client.addParam(vparams, "updateStats", updateStats);
	this.client.queueServiceActionCall("playlist", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.get = function(callback, id, version)
{
	if(!version)
		version = -1;

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "version", version);
	this.client.queueServiceActionCall("playlist", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.update = function(callback, id, playlist, updateStats)
{
	if(!updateStats)
		updateStats = false;

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "playlist", playlist.toParams());
	this.client.addParam(vparams, "updateStats", updateStats);
	this.client.queueServiceActionCall("playlist", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("playlist", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("playlist", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.execute = function(callback, id, detailed)
{
	if(!detailed)
		detailed = false;

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "detailed", detailed);
	this.client.queueServiceActionCall("playlist", "execute", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.executeFromContent = function(callback, playlistType, playlistContent, detailed)
{
	if(!detailed)
		detailed = false;

	vparams = new Object();
	this.client.addParam(vparams, "playlistType", playlistType);
	this.client.addParam(vparams, "playlistContent", playlistContent);
	this.client.addParam(vparams, "detailed", detailed);
	this.client.queueServiceActionCall("playlist", "executeFromContent", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.executeFromFilters = function(callback, filters, totalResults, detailed)
{
	if(!detailed)
		detailed = false;

	vparams = new Object();
	for(var index in filters)
	{
		var obj = filters[index];
		this.client.addParam(vparams, "filters:" + index, obj.toParams());
	}
	this.client.addParam(vparams, "totalResults", totalResults);
	this.client.addParam(vparams, "detailed", detailed);
	this.client.queueServiceActionCall("playlist", "executeFromFilters", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunPlaylistService.prototype.getStatsFromContent = function(callback, playlistType, playlistContent)
{

	vparams = new Object();
	this.client.addParam(vparams, "playlistType", playlistType);
	this.client.addParam(vparams, "playlistContent", playlistContent);
	this.client.queueServiceActionCall("playlist", "getStatsFromContent", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunReportService(client)
{
	this.init(client);
}

VidiunReportService.prototype = new VidiunServiceBase();

VidiunReportService.prototype.getGraphs = function(callback, reportType, reportInputFilter, dimension, objectIds)
{
	if(!dimension)
		dimension = null;
	if(!objectIds)
		objectIds = null;

	vparams = new Object();
	this.client.addParam(vparams, "reportType", reportType);
	this.client.addParam(vparams, "reportInputFilter", reportInputFilter.toParams());
	this.client.addParam(vparams, "dimension", dimension);
	this.client.addParam(vparams, "objectIds", objectIds);
	this.client.queueServiceActionCall("report", "getGraphs", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunReportService.prototype.getTotal = function(callback, reportType, reportInputFilter, objectIds)
{
	if(!objectIds)
		objectIds = null;

	vparams = new Object();
	this.client.addParam(vparams, "reportType", reportType);
	this.client.addParam(vparams, "reportInputFilter", reportInputFilter.toParams());
	this.client.addParam(vparams, "objectIds", objectIds);
	this.client.queueServiceActionCall("report", "getTotal", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunReportService.prototype.getTable = function(callback, reportType, reportInputFilter, pager, order, objectIds)
{
	if(!order)
		order = null;
	if(!objectIds)
		objectIds = null;

	vparams = new Object();
	this.client.addParam(vparams, "reportType", reportType);
	this.client.addParam(vparams, "reportInputFilter", reportInputFilter.toParams());
	this.client.addParam(vparams, "pager", pager.toParams());
	this.client.addParam(vparams, "order", order);
	this.client.addParam(vparams, "objectIds", objectIds);
	this.client.queueServiceActionCall("report", "getTable", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunReportService.prototype.getUrlForReportAsCsv = function(callback, reportTitle, reportText, headers, reportType, reportInputFilter, dimension, pager, order, objectIds)
{
	if(!dimension)
		dimension = null;
	if(!pager)
		pager = null;
	if(!order)
		order = null;
	if(!objectIds)
		objectIds = null;

	vparams = new Object();
	this.client.addParam(vparams, "reportTitle", reportTitle);
	this.client.addParam(vparams, "reportText", reportText);
	this.client.addParam(vparams, "headers", headers);
	this.client.addParam(vparams, "reportType", reportType);
	this.client.addParam(vparams, "reportInputFilter", reportInputFilter.toParams());
	this.client.addParam(vparams, "dimension", dimension);
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.addParam(vparams, "order", order);
	this.client.addParam(vparams, "objectIds", objectIds);
	this.client.queueServiceActionCall("report", "getUrlForReportAsCsv", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunSearchService(client)
{
	this.init(client);
}

VidiunSearchService.prototype = new VidiunServiceBase();

VidiunSearchService.prototype.search = function(callback, search, pager)
{
	if(!pager)
		pager = null;

	vparams = new Object();
	this.client.addParam(vparams, "search", search.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("search", "search", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSearchService.prototype.getMediaInfo = function(callback, searchResult)
{

	vparams = new Object();
	this.client.addParam(vparams, "searchResult", searchResult.toParams());
	this.client.queueServiceActionCall("search", "getMediaInfo", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSearchService.prototype.searchUrl = function(callback, mediaType, url)
{

	vparams = new Object();
	this.client.addParam(vparams, "mediaType", mediaType);
	this.client.addParam(vparams, "url", url);
	this.client.queueServiceActionCall("search", "searchUrl", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSearchService.prototype.externalLogin = function(callback, searchSource, userName, password)
{

	vparams = new Object();
	this.client.addParam(vparams, "searchSource", searchSource);
	this.client.addParam(vparams, "userName", userName);
	this.client.addParam(vparams, "password", password);
	this.client.queueServiceActionCall("search", "externalLogin", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunSessionService(client)
{
	this.init(client);
}

VidiunSessionService.prototype = new VidiunServiceBase();

VidiunSessionService.prototype.start = function(callback, secret, userId, type, partnerId, expiry, privileges)
{
	if(!userId)
		userId = "";
	if(!type)
		type = 0;
	if(!partnerId)
		partnerId = -1;
	if(!expiry)
		expiry = 86400;
	if(!privileges)
		privileges = null;

	vparams = new Object();
	this.client.addParam(vparams, "secret", secret);
	this.client.addParam(vparams, "userId", userId);
	this.client.addParam(vparams, "type", type);
	this.client.addParam(vparams, "partnerId", partnerId);
	this.client.addParam(vparams, "expiry", expiry);
	this.client.addParam(vparams, "privileges", privileges);
	this.client.queueServiceActionCall("session", "start", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSessionService.prototype.startWidgetSession = function(callback, widgetId, expiry)
{
	if(!expiry)
		expiry = 86400;

	vparams = new Object();
	this.client.addParam(vparams, "widgetId", widgetId);
	this.client.addParam(vparams, "expiry", expiry);
	this.client.queueServiceActionCall("session", "startWidgetSession", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunStatsService(client)
{
	this.init(client);
}

VidiunStatsService.prototype = new VidiunServiceBase();

VidiunStatsService.prototype.collect = function(callback, event)
{

	vparams = new Object();
	this.client.addParam(vparams, "event", event.toParams());
	this.client.queueServiceActionCall("stats", "collect", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunStatsService.prototype.vmcCollect = function(callback, vmcEvent)
{

	vparams = new Object();
	this.client.addParam(vparams, "vmcEvent", vmcEvent.toParams());
	this.client.queueServiceActionCall("stats", "vmcCollect", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunStatsService.prototype.reportVceError = function(callback, vidiunCEError)
{

	vparams = new Object();
	this.client.addParam(vparams, "vidiunCEError", vidiunCEError.toParams());
	this.client.queueServiceActionCall("stats", "reportVceError", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunSyndicationFeedService(client)
{
	this.init(client);
}

VidiunSyndicationFeedService.prototype = new VidiunServiceBase();

VidiunSyndicationFeedService.prototype.add = function(callback, syndicationFeed)
{

	vparams = new Object();
	this.client.addParam(vparams, "syndicationFeed", syndicationFeed.toParams());
	this.client.queueServiceActionCall("syndicationfeed", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("syndicationfeed", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.update = function(callback, id, syndicationFeed)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "syndicationFeed", syndicationFeed.toParams());
	this.client.queueServiceActionCall("syndicationfeed", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("syndicationfeed", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("syndicationfeed", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.getEntryCount = function(callback, feedId)
{

	vparams = new Object();
	this.client.addParam(vparams, "feedId", feedId);
	this.client.queueServiceActionCall("syndicationfeed", "getEntryCount", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSyndicationFeedService.prototype.requestConversion = function(callback, feedId)
{

	vparams = new Object();
	this.client.addParam(vparams, "feedId", feedId);
	this.client.queueServiceActionCall("syndicationfeed", "requestConversion", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunSystemService(client)
{
	this.init(client);
}

VidiunSystemService.prototype = new VidiunServiceBase();

VidiunSystemService.prototype.ping = function(callback)
{

	vparams = new Object();
	this.client.queueServiceActionCall("system", "ping", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunUiConfService(client)
{
	this.init(client);
}

VidiunUiConfService.prototype = new VidiunServiceBase();

VidiunUiConfService.prototype.add = function(callback, uiConf)
{

	vparams = new Object();
	this.client.addParam(vparams, "uiConf", uiConf.toParams());
	this.client.queueServiceActionCall("uiconf", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.update = function(callback, id, uiConf)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "uiConf", uiConf.toParams());
	this.client.queueServiceActionCall("uiconf", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("uiconf", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.delete = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("uiconf", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.cloneAction = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("uiconf", "clone", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.listTemplates = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("uiconf", "listTemplates", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUiConfService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("uiconf", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunUploadService(client)
{
	this.init(client);
}

VidiunUploadService.prototype = new VidiunServiceBase();

VidiunUploadService.prototype.getUploadTokenId = function(callback)
{

	vparams = new Object();
	this.client.queueServiceActionCall("upload", "getUploadTokenId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUploadService.prototype.uploadByTokenId = function(callback, fileData, uploadTokenId)
{

	vparams = new Object();
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.addParam(vparams, "uploadTokenId", uploadTokenId);
	this.client.queueServiceActionCall("upload", "uploadByTokenId", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUploadService.prototype.getUploadedFileStatusByTokenId = function(callback, uploadTokenId)
{

	vparams = new Object();
	this.client.addParam(vparams, "uploadTokenId", uploadTokenId);
	this.client.queueServiceActionCall("upload", "getUploadedFileStatusByTokenId", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUploadService.prototype.upload = function(callback, fileData)
{

	vparams = new Object();
	vfiles = new Object();
	this.client.addParam(vfiles, "fileData", fileData);
	this.client.queueServiceActionCall("upload", "upload", vparams, vfiles);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunUserService(client)
{
	this.init(client);
}

VidiunUserService.prototype = new VidiunServiceBase();

VidiunUserService.prototype.add = function(callback, user)
{

	vparams = new Object();
	this.client.addParam(vparams, "user", user.toParams());
	this.client.queueServiceActionCall("user", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUserService.prototype.update = function(callback, userId, user)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.addParam(vparams, "user", user.toParams());
	this.client.queueServiceActionCall("user", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUserService.prototype.get = function(callback, userId)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.queueServiceActionCall("user", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUserService.prototype.delete = function(callback, userId)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.queueServiceActionCall("user", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUserService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("user", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunUserService.prototype.notifyBan = function(callback, userId)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.queueServiceActionCall("user", "notifyBan", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunWidgetService(client)
{
	this.init(client);
}

VidiunWidgetService.prototype = new VidiunServiceBase();

VidiunWidgetService.prototype.add = function(callback, widget)
{

	vparams = new Object();
	this.client.addParam(vparams, "widget", widget.toParams());
	this.client.queueServiceActionCall("widget", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunWidgetService.prototype.update = function(callback, id, widget)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.addParam(vparams, "widget", widget.toParams());
	this.client.queueServiceActionCall("widget", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunWidgetService.prototype.get = function(callback, id)
{

	vparams = new Object();
	this.client.addParam(vparams, "id", id);
	this.client.queueServiceActionCall("widget", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunWidgetService.prototype.cloneAction = function(callback, widget)
{

	vparams = new Object();
	this.client.addParam(vparams, "widget", widget.toParams());
	this.client.queueServiceActionCall("widget", "clone", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunWidgetService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("widget", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunXInternalService(client)
{
	this.init(client);
}

VidiunXInternalService.prototype = new VidiunServiceBase();

VidiunXInternalService.prototype.xAddBulkDownload = function(callback, entryIds, flavorParamsId)
{
	if(!flavorParamsId)
		flavorParamsId = "";

	vparams = new Object();
	this.client.addParam(vparams, "entryIds", entryIds);
	this.client.addParam(vparams, "flavorParamsId", flavorParamsId);
	this.client.queueServiceActionCall("xinternal", "xAddBulkDownload", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunSystemUserService(client)
{
	this.init(client);
}

VidiunSystemUserService.prototype = new VidiunServiceBase();

VidiunSystemUserService.prototype.verifyPassword = function(callback, email, password)
{

	vparams = new Object();
	this.client.addParam(vparams, "email", email);
	this.client.addParam(vparams, "password", password);
	this.client.queueServiceActionCall("systemuser", "verifyPassword", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.generateNewPassword = function(callback)
{

	vparams = new Object();
	this.client.queueServiceActionCall("systemuser", "generateNewPassword", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.setNewPassword = function(callback, userId, password)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.addParam(vparams, "password", password);
	this.client.queueServiceActionCall("systemuser", "setNewPassword", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.add = function(callback, systemUser)
{

	vparams = new Object();
	this.client.addParam(vparams, "systemUser", systemUser.toParams());
	this.client.queueServiceActionCall("systemuser", "add", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.get = function(callback, userId)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.queueServiceActionCall("systemuser", "get", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.getByEmail = function(callback, email)
{

	vparams = new Object();
	this.client.addParam(vparams, "email", email);
	this.client.queueServiceActionCall("systemuser", "getByEmail", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.update = function(callback, userId, systemUser)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.addParam(vparams, "systemUser", systemUser.toParams());
	this.client.queueServiceActionCall("systemuser", "update", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.delete = function(callback, userId)
{

	vparams = new Object();
	this.client.addParam(vparams, "userId", userId);
	this.client.queueServiceActionCall("systemuser", "delete", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

VidiunSystemUserService.prototype.listAction = function(callback, filter, pager)
{
	if(!filter)
		filter = null;
	if(!pager)
		pager = null;

	vparams = new Object();
	if (filter != null)
		this.client.addParam(vparams, "filter", filter.toParams());
	if (pager != null)
		this.client.addParam(vparams, "pager", pager.toParams());
	this.client.queueServiceActionCall("systemuser", "list", vparams);
	if (!this.client.isMultiRequest())
		this.client.doQueue(callback);
};

function VidiunClient(config)
{
	this.init(config);
}

VidiunClient.prototype = new VidiunClientBase()
/**
 * Add & Manage Access Controls
 *
 * @var VidiunAccessControlService
 */
VidiunClient.prototype.accessControl = null;

/**
 * admin console service lets you manage cross partner reports, activity, status and config. 
	 * 
 *
 * @var VidiunAdminconsoleService
 */
VidiunClient.prototype.adminconsole = null;

/**
 * Manage details for the administrative user
 *
 * @var VidiunAdminUserService
 */
VidiunClient.prototype.adminUser = null;

/**
 * Base Entry Service
 *
 * @var VidiunBaseEntryService
 */
VidiunClient.prototype.baseEntry = null;

/**
 * Bulk upload service is used to upload & manage bulk uploads using CSV files
 *
 * @var VidiunBulkUploadService
 */
VidiunClient.prototype.bulkUpload = null;

/**
 * Add & Manage Categories
 *
 * @var VidiunCategoryService
 */
VidiunClient.prototype.category = null;

/**
 * Add & Manage Conversion Profiles
 *
 * @var VidiunConversionProfileService
 */
VidiunClient.prototype.conversionProfile = null;

/**
 * Data service lets you manage data content (textual content)
 *
 * @var VidiunDataService
 */
VidiunClient.prototype.data = null;

/**
 * Retrieve information and invoke actions on Flavor Asset
 *
 * @var VidiunFlavorAssetService
 */
VidiunClient.prototype.flavorAsset = null;

/**
 * Add & Manage Flavor Params
 *
 * @var VidiunFlavorParamsService
 */
VidiunClient.prototype.flavorParams = null;

/**
 * batch service lets you handle different batch process from remote machines.
	 * As oppesed to other ojects in the system, locking mechanism is critical in this case.
	 * For this reason the GetExclusiveXX, UpdateExclusiveXX and FreeExclusiveXX actions are important for the system's intergity.
	 * In general - updating batch object should be done only using the UpdateExclusiveXX which in turn can be called only after 
	 * acuiring a batch objet properly (using  GetExclusiveXX).
	 * If an object was aquired and should be returned to the pool in it's initial state - use the FreeExclusiveXX action 
	 * 
 *
 * @var VidiunJobsService
 */
VidiunClient.prototype.jobs = null;

/**
 * Media service lets you upload and manage media files (images / videos & audio)
 *
 * @var VidiunMediaService
 */
VidiunClient.prototype.media = null;

/**
 * A Mix is an XML unique format invented by Vidiun, it allows the user to create a mix of videos and images, in and out points, transitions, text overlays, soundtrack, effects and much more...
	 * Mixing service lets you create a new mix, manage its metadata and make basic manipulations.   
 *
 * @var VidiunMixingService
 */
VidiunClient.prototype.mixing = null;

/**
 * Notification Service
 *
 * @var VidiunNotificationService
 */
VidiunClient.prototype.notification = null;

/**
 * partner service allows you to change/manage your partner personal details and settings as well
 *
 * @var VidiunPartnerService
 */
VidiunClient.prototype.partner = null;

/**
 * Playlist service lets you create,manage and play your playlists
	 * Playlists could be static (containing a fixed list of entries) or dynamic (baseed on a filter)
 *
 * @var VidiunPlaylistService
 */
VidiunClient.prototype.playlist = null;

/**
 * api for getting reports data by the report type and some inputFilter
 *
 * @var VidiunReportService
 */
VidiunClient.prototype.report = null;

/**
 * Search service allows you to search for media in various media providers
	 * This service is being used mostly by the CW component
 *
 * @var VidiunSearchService
 */
VidiunClient.prototype.search = null;

/**
 * Session service
 *
 * @var VidiunSessionService
 */
VidiunClient.prototype.session = null;

/**
 * Stats Service
 *
 * @var VidiunStatsService
 */
VidiunClient.prototype.stats = null;

/**
 * Add & Manage Syndication Feeds
 *
 * @var VidiunSyndicationFeedService
 */
VidiunClient.prototype.syndicationFeed = null;

/**
 * System service is used for internal system helpers & to retrieve system level information
 *
 * @var VidiunSystemService
 */
VidiunClient.prototype.system = null;

/**
 * UiConf service lets you create and manage your UIConfs for the various flash components
	 * This service is used by the VMC-ApplicationStudio
 *
 * @var VidiunUiConfService
 */
VidiunClient.prototype.uiConf = null;

/**
 * Upload service is used to upload files and get the token that can be later used as a reference to the uploaded file
	 * 
 *
 * @var VidiunUploadService
 */
VidiunClient.prototype.upload = null;

/**
 * Manage partner users on Vidiun's side
	 * The userId in vidiun is the unique Id in the partner's system, and the [partnerId,Id] couple are unique key in vidiun's DB
 *
 * @var VidiunUserService
 */
VidiunClient.prototype.user = null;

/**
 * widget service for full widget management
 *
 * @var VidiunWidgetService
 */
VidiunClient.prototype.widget = null;

/**
 * Internal Service is used for actions that are used internally in Vidiun applications and might be changed in the future without any notice.
 *
 * @var VidiunXInternalService
 */
VidiunClient.prototype.xInternal = null;

/**
 * System user service
 *
 * @var VidiunSystemUserService
 */
VidiunClient.prototype.systemUser = null;


VidiunClient.prototype.init = function(config)
{
	VidiunClientBase.prototype.init.apply(this, arguments);
	this.accessControl = new VidiunAccessControlService(this);
	this.adminconsole = new VidiunAdminconsoleService(this);
	this.adminUser = new VidiunAdminUserService(this);
	this.baseEntry = new VidiunBaseEntryService(this);
	this.bulkUpload = new VidiunBulkUploadService(this);
	this.category = new VidiunCategoryService(this);
	this.conversionProfile = new VidiunConversionProfileService(this);
	this.data = new VidiunDataService(this);
	this.flavorAsset = new VidiunFlavorAssetService(this);
	this.flavorParams = new VidiunFlavorParamsService(this);
	this.jobs = new VidiunJobsService(this);
	this.media = new VidiunMediaService(this);
	this.mixing = new VidiunMixingService(this);
	this.notification = new VidiunNotificationService(this);
	this.partner = new VidiunPartnerService(this);
	this.playlist = new VidiunPlaylistService(this);
	this.report = new VidiunReportService(this);
	this.search = new VidiunSearchService(this);
	this.session = new VidiunSessionService(this);
	this.stats = new VidiunStatsService(this);
	this.syndicationFeed = new VidiunSyndicationFeedService(this);
	this.system = new VidiunSystemService(this);
	this.uiConf = new VidiunUiConfService(this);
	this.upload = new VidiunUploadService(this);
	this.user = new VidiunUserService(this);
	this.widget = new VidiunWidgetService(this);
	this.xInternal = new VidiunXInternalService(this);
	this.systemUser = new VidiunSystemUserService(this);
}
