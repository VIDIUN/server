<?php
/**
 * @package Core
 * @subpackage model.enum
 */ 
interface PermissionName extends BaseEnum
{
	// special services
	
	const FEATURE_ANALYTICS_TAB = 'FEATURE_ANALYTICS_TAB';
	const FEATURE_ANALYTICS_API = 'FEATURE_ANALYTICS_API';
	const FEATURE_508_PLAYERS = 'FEATURE_508_PLAYERS';
	const FEATURE_LIVE_STREAM = 'FEATURE_LIVE_STREAM';
	const FEATURE_LIVE_STREAM_COPY_ENTITELMENTS = 'FEATURE_LIVE_STREAM_COPY_ENTITELMENTS';
	const FEATURE_VIDIUN_LIVE_STREAM = 'FEATURE_VIDIUN_LIVE_STREAM';
	const FEATURE_VIDIUN_LIVE_DISABLE_SMART_TRANSCODING = 'FEATURE_VIDIUN_LIVE_DISABLE_SMART_TRANSCODING';
	const FEATURE_VIDIUN_LIVE_STREAM_TRANSCODE = 'FEATURE_VIDIUN_LIVE_STREAM_TRANSCODE';
	const FEATURE_VIDIUN_LIVE_STREAM_HD_FLAVORS = 'FEATURE_VIDIUN_LIVE_STREAM_HD_FLAVORS';
	const FEATURE_VIDIUN_LIVE_STREAM_HD_PLUS_FLAVORS = 'FEATURE_VIDIUN_LIVE_STREAM_HD_PLUS_FLAVORS';
	const FEATURE_LIVE_STREAM_RECORD = 'FEATURE_LIVE_STREAM_RECORD';
	const FEATURE_LIVE_STREAM_VIDIUN_RECORDING = 'FEATURE_LIVE_STREAM_VIDIUN_RECORDING';
	const FEATURE_LIVE_CHANNEL = 'FEATURE_LIVE_CHANNEL';
	const FEATURE_VAST = 'FEATURE_VAST';
	const FEATURE_ADAP_TV = 'FEATURE_ADAP_TV';
	const FEATURE_TREMOR = 'FEATURE_TREMOR';
	const FEATURE_SILVERLIGHT = 'FEATURE_SILVERLIGHT';
	const FEATURE_PS2_PERMISSIONS_VALIDATION = 'FEATURE_PS2_PERMISSIONS_VALIDATION';
	const FEATURE_MULTI_FLAVOR_INGESTION = 'FEATURE_MULTI_FLAVOR_INGESTION';
	const FEATURE_ENTRY_REPLACEMENT = 'FEATURE_ENTRY_REPLACEMENT';
	const FEATURE_ENTRY_REPLACEMENT_APPROVAL = 'FEATURE_ENTRY_REPLACEMENT_APPROVAL';
	const FEATURE_MOBILE_FLAVORS = 'FEATURE_MOBILE_FLAVORS';
	const FEATURE_SUBTITLES  = 'FEATURE_SUBTITLES';
	const FEATURE_UGC_VCW = 'FEATURE_UGC_VCW';
	const FEATURE_UGC_DESKTOP_UPLOADER = 'FEATURE_UGC_DESKTOP_UPLOADER';
	const FEATURE_WHITE_LABEL_PLAYER = 'FEATURE_WHITE_LABEL_PLAYER';
	const FEATURE_EMAIL_INGEST = 'FEATURE_EMAIL_INGEST';
	const FEATURE_REMOTE_STORAGE = 'FEATURE_REMOTE_STORAGE';
	const FEATURE_REMOTE_STORAGE_INGEST = 'FEATURE_REMOTE_STORAGE_INGEST';
	const FEATURE_REMOTE_STORAGE_DELIVERY_PRIORITY = 'FEATURE_REMOTE_STORAGE_DELIVERY_PRIORITY';
	const FEATURE_REMOTE_STORAGE_RULE = 'FEATURE_REMOTE_STORAGE_RULE';
	const FEATURE_DISABLE_VMC_LIST_THUMBNAILS = 'FEATURE_DISABLE_VMC_LIST_THUMBNAILS';
	const FEATURE_DISABLE_VMC_DRILL_DOWN_THUMB_RESIZE = 'FEATURE_DISABLE_VMC_DRILL_DOWN_THUMB_RESIZE';
	const FEATURE_DISABLE_VMC_VDP_ALERTS = 'FEATURE_DISABLE_VMC_VDP_ALERTS';
	const FEATURE_VMC_DRILLDOWN_TAGS_COLUMN = 'FEATURE_VMC_DRILLDOWN_TAGS_COLUMN';
	const FEATURE_VMC_ENFORCE_HTTPS = 'FEATURE_VMC_ENFORCE_HTTPS';
	const FEATURE_HIDE_SENSITIVE_DATA_IN_RSS_FEED = 'FEATURE_HIDE_SENSITIVE_DATA_IN_RSS_FEED';
	const FEATURE_V1_FLAVORS = 'FEATURE_V1_FLAVORS';
	const FEATURE_V2_FLAVORS = 'FEATURE_V2_FLAVORS';
	const FEATURE_ENTITLEMENT = 'FEATURE_ENTITLEMENT';
	const FEATURE_ENTITLEMENT_USED = 'FEATURE_ENTITLEMENT_USED';
	const FEATURE_LIKE = 'FEATURE_LIKE';
	const FEATURE_END_USER_REPORTS = 'FEATURE_END_USER_REPORTS';
	const FEATURE_VAR_CONSOLE_LOGIN = 'FEATURE_VAR_CONSOLE_LOGIN';
	const FEATURE_EMBED_CODE_DEFAULT_PROTOCOL_HTTPS = 'FEATURE_EMBED_CODE_DEFAULT_PROTOCOL_HTTPS';
	const FEATURE_MIX_TAB = 'FEATURE_MIX_TAB';
	const FEATURE_PREVIEW_AND_EMBED_V2 = 'FEATURE_PREVIEW_AND_EMBED_V2';
	const FEATURE_HTML5_V2_PLAYER_PREVIEW = 'FEATURE_HTML5_V2_PLAYER_PREVIEW';
	const FEATURE_VMC_ALLOW_FRAME = 'FEATURE_VMC_ALLOW_FRAME';
	const FEATURE_ACCURATE_SERVE_CLIPPING = 'FEATURE_ACCURATE_SERVE_CLIPPING';
	const FEATURE_PLAY_SERVER = 'FEATURE_PLAY_SERVER';
	const FEATURE_DISABLE_RESET_PASSWORD_EMAIL = 'FEATURE_DISABLE_RESET_PASSWORD_EMAIL';
	const FEATURE_SERVER_NODE = 'FEATURE_SERVER_NODE';
	const FEATURE_CONTENT_ENCRYPTION = 'FEATURE_CONTENT_ENCRYPTION';
	const LECTURE_CAPTURE_USAGE = 'LECTURE_CAPTURE_USAGE';


	const DYNAMIC_FLAG_VMC_CHUNKED_CATEGORY_LOAD = 'DYNAMIC_FLAG_VMC_CHUNKED_CATEGORY_LOAD';
	const FEATURE_IGNORE_ENTRY_SEO_LINKS = 'FEATURE_IGNORE_ENTRY_SEO_LINKS';
	
	const FEATURE_DISABLE_CATEGORY_LIMIT = 'FEATURE_DISABLE_CATEGORY_LIMIT';
	const FEATURE_DISABLE_PARENT_ENTRY_SECURITY_INHERITANCE = 'FEATURE_DISABLE_PARENT_ENTRY_SECURITY_INHERITANCE';
	
	const FEATURE_DRAFT_ENTRY_CONV_PROF_SELECTION = 'FEATURE_DRAFT_ENTRY_CONV_PROF_SELECTION';
	const FEATURE_BLOCK_THUMBNAIL_CAPTURE = 'FEATURE_BLOCK_THUMBNAIL_CAPTURE';
	const FEATURE_BLOCK_FLVCLIPPER_ACTION = 'FEATURE_BLOCK_FLVCLIPPER_ACTION';
	
	const FEATURE_DISABLE_NEW_USER_EMAIL = 'FEATURE_DISABLE_NEW_USER_EMAIL';

	const FEATURE_DYNAMIC_SEGMENT_DURATION = 'FEATURE_DYNAMIC_SEGMENT_DURATION';
	const FEATURE_FILE_TYPE_RESTRICTION_PERMISSION = 'FEATURE_FILE_TYPE_RESTRICTION_PERMISSION';
	const FEATURE_MEDIA_REPURPOSING_PERMISSION = 'FEATURE_MEDIA_REPURPOSING_PERMISSION';
	const FEATURE_V3_STUDIO_PERMISSION = 'FEATURE_V3_STUDIO_PERMISSION';
	const REACH_PLUGIN_PERMISSION = 'REACH_PLUGIN_PERMISSION';
	const REACH_VENDOR_PARTNER_PERMISSION = "REACH_VENDOR_PARTNER_PERMISSION";
	const FEATURE_ALLOW_VAST_CUE_POINT_NO_URL = 'FEATURE_ALLOW_VAST_CUE_POINT_NO_URL';

    // base system permissions
	
	const USER_SESSION_PERMISSION = 'BASE_USER_SESSION_PERMISSION';
	const ALWAYS_ALLOWED_ACTIONS = 'ALWAYS_ALLOWED_ACTIONS';
	const ALWAYS_ALLOWED_FROM_INTERNAL_IP_ACTIONS = 'ALWAYS_ALLOWED_FROM_INTERNAL_IP_ACTIONS';
	
	const SYSTEM_FILESYNC = 'SYSTEM_FILESYNC';
	const SYSTEM_INTERNAL = 'SYSTEM_INTERNAL';
	const VMC_ACCESS = 'VMC_ACCESS';
	const VMC_READ_ONLY = 'VMC_READ_ONLY';
	
	// system admin and admin console permissions
	
	const SYSTEM_ADMIN_BASE = 'SYSTEM_ADMIN_BASE';
	
	const SYSTEM_ADMIN_PUBLISHER_BASE = 'SYSTEM_ADMIN_PUBLISHER_BASE';
	const SYSTEM_ADMIN_PUBLISHER_VMC_ACCESS = 'SYSTEM_ADMIN_PUBLISHER_VMC_ACCESS';
	const SYSTEM_ADMIN_PUBLISHER_CONFIG = 'SYSTEM_ADMIN_PUBLISHER_CONFIG';
	const SYSTEM_ADMIN_PUBLISHER_BLOCK = 'SYSTEM_ADMIN_PUBLISHER_BLOCK';
	const SYSTEM_ADMIN_PUBLISHER_REMOVE = 'SYSTEM_ADMIN_PUBLISHER_REMOVE';
	const SYSTEM_ADMIN_PUBLISHER_ADD = 'SYSTEM_ADMIN_PUBLISHER_ADD';
	const SYSTEM_ADMIN_PUBLISHER_USAGE = 'SYSTEM_ADMIN_PUBLISHER_USAGE';
	const SYSTEM_ADMIN_PUBLISHER_LIST_COMMERCIAL = 'SYSTEM_ADMIN_PUBLISHER_LIST_COMMERCIAL';
	
	const SYSTEM_ADMIN_USER_MANAGE = 'SYSTEM_ADMIN_USER_MANAGE';
	
	const SYSTEM_ADMIN_SYSTEM_MONITOR = 'SYSTEM_ADMIN_SYSTEM_MONITOR';
	
	const SYSTEM_ADMIN_DEVELOPERS_TAB = 'SYSTEM_ADMIN_DEVELOPERS_TAB';
	
	const SYSTEM_ADMIN_BATCH_CONTROL = 'SYSTEM_ADMIN_BATCH_CONTROL';
	const SYSTEM_ADMIN_BATCH_CONTROL_INPROGRESS = 'SYSTEM_ADMIN_BATCH_CONTROL_INPROGRESS';
	const SYSTEM_ADMIN_BATCH_CONTROL_FAILED = 'SYSTEM_ADMIN_BATCH_CONTROL_FAILED';
	const SYSTEM_ADMIN_BATCH_CONTROL_SETUP = 'SYSTEM_ADMIN_BATCH_CONTROL_SETUP';
	
	const SYSTEM_ADMIN_STORAGE = 'SYSTEM_ADMIN_STORAGE';
	const SYSTEM_ADMIN_VIRUS_SCAN = 'SYSTEM_ADMIN_VIRUS_SCAN';
	const SYSTEM_ADMIN_EMAIL_INGESTION = 'SYSTEM_ADMIN_EMAIL_INGESTION';
	const SYSTEM_ADMIN_CONTENT_DISTRIBUTION_BASE = 'SYSTEM_ADMIN_CONTENT_DISTRIBUTION_BASE';
	const SYSTEM_ADMIN_CONTENT_DISTRIBUTION_MODIFY = 'SYSTEM_ADMIN_CONTENT_DISTRIBUTION_MODIFY';
	const SYSTEM_ADMIN_PERMISSIONS_MANAGE = 'SYSTEM_ADMIN_PERMISSIONS_MANAGE';
	const SYSTEM_ADMIN_ENTRY_INVESTIGATION = 'SYSTEM_ADMIN_ENTRY_INVESTIGATION';

	const SYSTEM_ADMIN_CATALOG_ITEM_BASE = "reach.SYSTEM_ADMIN_CATALOG_ITEM_BASE";
	const SYSTEM_ADMIN_CATALOG_ITEM_MODIFY = "reach.SYSTEM_ADMIN_CATALOG_ITEM_MODIFY";
	
	// batch related permissions
	
	const BATCH_BASE = 'BATCH_BASE';
		
	// user actions permissions
	
	const CONTENT_INGEST_UPLOAD = 'CONTENT_INGEST_UPLOAD';
	const CONTENT_INGEST_BULK_UPLOAD = 'CONTENT_INGEST_BULK_UPLOAD';
	const CONTENT_INGEST_FEED = 'CONTENT_INGEST_FEED';
	
	const CONTENT_MANAGE_DISTRIBUTION_BASE = 'CONTENT_MANAGE_DISTRIBUTION_BASE';
	const CONTENT_MANAGE_DISTRIBUTION_WHERE = 'CONTENT_MANAGE_DISTRIBUTION_WHERE';
	const CONTENT_MANAGE_DISTRIBUTION_SEND = 'CONTENT_MANAGE_DISTRIBUTION_SEND';
	const CONTENT_MANAGE_DISTRIBUTION_REMOVE = 'CONTENT_MANAGE_DISTRIBUTION_REMOVE';
	const CONTENT_MANAGE_DISTRIBUTION_PROFILE_MODIFY = 'CONTENT_MANAGE_DISTRIBUTION_PROFILE_MODIFY';
	
	const CONTENT_MANAGE_VIRUS_SCAN = 'CONTENT_MANAGE_VIRUS_SCAN';
	const CONTENT_MANAGE_MIX = 'CONTENT_MANAGE_MIX';
	const CONTENT_MANAGE_BASE = 'CONTENT_MANAGE_BASE';
	const CONTENT_MANAGE_METADATA = 'CONTENT_MANAGE_METADATA';
	const CONTENT_MANAGE_ASSIGN_CATEGORIES = 'CONTENT_MANAGE_ASSIGN_CATEGORIES';
	const CONTENT_MANAGE_THUMBNAIL = 'CONTENT_MANAGE_THUMBNAIL';
	const CONTENT_MANAGE_SCHEDULE = 'CONTENT_MANAGE_SCHEDULE';
	const CONTENT_MANAGE_ACCESS_CONTROL = 'CONTENT_MANAGE_ACCESS_CONTROL';
	const CONTENT_MANAGE_CUSTOM_DATA = 'CONTENT_MANAGE_CUSTOM_DATA';
	const CONTENT_MANAGE_DELETE = 'CONTENT_MANAGE_DELETE';
	const CONTENT_MANAGE_RECONVERT = 'CONTENT_MANAGE_RECONVERT';
	const CONTENT_MANAGE_EDIT_CATEGORIES = 'CONTENT_MANAGE_EDIT_CATEGORIES';
	const CONTENT_MANAGE_ANNOTATION = 'CONTENT_MANAGE_ANNOTATION';
	const CONTENT_MANAGE_SHARE = 'CONTENT_MANAGE_SHARE';
	const CONTENT_MANAGE_DOWNLOAD = 'CONTENT_MANAGE_DOWNLOAD';
	const CONTENT_MANAGE_EXPORT = 'CONTENT_MANAGE_EXPORT';
	
	const LIVE_STREAM_ADD = 'LIVE_STREAM_ADD';
	const LIVE_STREAM_UPDATE = 'LIVE_STREAM_UPDATE';
		
	const CONTENT_MODERATE_BASE = 'CONTENT_MODERATE_BASE';
	const CONTENT_MODERATE_METADATA = 'CONTENT_MODERATE_METADATA';
	const CONTENT_MODERATE_CUSTOM_DATA = 'CONTENT_MODERATE_CUSTOM_DATA';
	const CONTENT_MODERATE_APPROVE_REJECT = 'CONTENT_MODERATE_APPROVE_REJECT';
	
	const PLAYLIST_BASE = 'PLAYLIST_BASE';
	const PLAYLIST_ADD = 'PLAYLIST_ADD';
	const PLAYLIST_UPDATE = 'PLAYLIST_UPDATE';
	const PLAYLIST_DELETE = 'PLAYLIST_DELETE';
	
	const SYNDICATION_BASE = 'SYNDICATION_BASE';
	const SYNDICATION_ADD = 'SYNDICATION_ADD';
	const SYNDICATION_UPDATE = 'SYNDICATION_UPDATE';
	const SYNDICATION_DELETE = 'SYNDICATION_DELETE';
	
	const STUDIO_BASE = 'STUDIO_BASE';
	const STUDIO_ADD_UICONF = 'STUDIO_ADD_UICONF';
	const STUDIO_UPDATE_UICONF = 'STUDIO_UPDATE_UICONF';
	const STUDIO_DELETE_UICONF = 'STUDIO_DELETE_UICONF';

	const ACCOUNT_BASE = 'ACCOUNT_BASE';
	const ACCOUNT_UPDATE_SETTINGS = 'ACCOUNT_UPDATE_SETTINGS';
	
	const INTEGRATION_BASE = 'INTEGRATION_BASE';
	const INTEGRATION_UPDATE_SETTINGS = 'INTEGRATION_UPDATE_SETTINGS';
	
	const TRANSCODING_BASE = 'TRANSCODING_BASE';
	const TRANSCODING_ADD = 'TRANSCODING_ADD';
	const TRANSCODING_UPDATE = 'TRANSCODING_UPDATE';
	const TRANSCODING_DELETE = 'TRANSCODING_DELETE';
	
	const CUSTOM_DATA_PROFILE_BASE = 'CUSTOM_DATA_PROFILE_BASE';
	const CUSTOM_DATA_PROFILE_ADD = 'CUSTOM_DATA_PROFILE_ADD';
	const CUSTOM_DATA_PROFILE_UPDATE = 'CUSTOM_DATA_PROFILE_UPDATE';
	const CUSTOM_DATA_PROFILE_DELETE = 'CUSTOM_DATA_PROFILE_DELETE';
	const CUSTOM_DATA_FIELD_ADD = 'CUSTOM_DATA_FIELD_ADD';
	const CUSTOM_DATA_FIELD_UPDATE = 'CUSTOM_DATA_FIELD_UPDATE';
	const CUSTOM_DATA_FIELD_DELETE = 'CUSTOM_DATA_FIELD_DELETE';
	
	const ADMIN_BASE = 'ADMIN_BASE';
	
	const ADMIN_USER_ADD = 'ADMIN_USER_ADD';
	const ADMIN_USER_UPDATE = 'ADMIN_USER_UPDATE';
	const ADMIN_USER_DELETE = 'ADMIN_USER_DELETE';
	
	const ADMIN_ROLE_ADD = 'ADMIN_ROLE_ADD';
	const ADMIN_ROLE_UPDATE = 'ADMIN_ROLE_UPDATE';
	const ADMIN_ROLE_DELETE = 'ADMIN_ROLE_DELETE';
	
	const ADMIN_PERMISSION_ADD = 'ADMIN_PERMISSION_ADD';
	const ADMIN_PERMISSION_UPDATE = 'ADMIN_PERMISSION_UPDATE';
	const ADMIN_PERMISSION_DELETE = 'ADMIN_PERMISSION_DELETE';
	
	const ADMIN_PUBLISHER_MANAGE = 'ADMIN_PUBLISHER_MANAGE';
	const ANALYTICS_BASE = 'ANALYTICS_BASE';
	
	const WIDGET_ADMIN = 'WIDGET_ADMIN';
	const SEARCH_SERVICE = 'SEARCH_SERVICE';
	const ANALYTICS_SEND_DATA = 'ANALYTICS_SEND_DATA';
	
	const AUDIT_TRAIL_BASE = 'AUDIT_TRAIL_BASE';
	const AUDIT_TRAIL_ADD = 'AUDIT_TRAIL_ADD';

	const MANAGE_ADMIN_USERS = 'MANAGE_ADMIN_USERS';

	const FEATURE_SELF_SERVE = 'FEATURE_SELF_SERVE';

	// VMC only permissions
	
	const ADVERTISING_BASE = 'ADVERTISING_BASE';
	const ADVERTISING_UPDATE_SETTINGS = 'ADVERTISING_UPDATE_SETTINGS';
	const PLAYLIST_EMBED_CODE = 'PLAYLIST_EMBED_CODE';
	const STUDIO_BRAND_UICONF = 'STUDIO_BRAND_UICONF';
	const STUDIO_SELECT_CONTENT = 'STUDIO_SELECT_CONTENT';
	const CONTENT_MANAGE_EMBED_CODE = 'CONTENT_MANAGE_EMBED_CODE';

	
	// not valid yet
	
	const ADMIN_WHITE_BRANDING = 'ADMIN_WHITE_BRANDING';	

	// new category permissions for the categoryUser
	const CATEGORY_SUBSCRIBE = 'CATEGORY_SUBSCRIBE';
	const CATEGORY_CONTRIBUTE = 'CATEGORY_CONTRIBUTE';
	const CATEGORY_MODERATE = 'CATEGORY_MODERATE';
	const CATEGORY_EDIT = 'CATEGORY_EDIT'; 
	const CATEGORY_VIEW = 'CATEGORY_VIEW';
	
	const FEATURE_SHOW_FLASH_STUDIO = 'FEATURE_SHOW_FLASH_STUDIO';
	const FEATURE_SHOW_HTML_STUDIO = 'FEATURE_SHOW_HTML_STUDIO';
	const FEATURE_HIDE_TEMPLATE_PARTNER_UICONFS = 'FEATURE_HIDE_TEMPLATE_PARTNER_UICONFS';
	
	const FEATURE_RECALCULATE_RESPONSE_PROFILE_CACHE = 'FEATURE_RECALCULATE_RESPONSE_PROFILE_CACHE';
	const FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE = 'FEATURE_ENABLE_RESPONSE_PROFILE_USER_CACHE';
	
	const FEATURE_BLOCK_CATEGORY_MODERATION_SELF_APPROVE = 'FEATURE_BLOCK_CATEGORY_MODERATION_SELF_APPROVE';
	const FEATURE_UNLIMITED_RECORDING_DURATION = 'FEATURE_UNLIMITED_RECORDING_DURATION';
	
	const CONF_MAPS_VIEW_PLUGIN_ADMIN = 'CONF_MAPS_VIEW_PLUGIN_ADMIN';
	const CONF_MAPS_EDIT_PLUGIN_ADMIN = 'CONF_MAPS_EDIT_PLUGIN_ADMIN';
	

	const FEATURE_HTTP_NOTIFICATIONS_DEFAULT_PROTOCOL_HTTPS = 'FEATURE_HTTP_NOTIFICATIONS_DEFAULT_PROTOCOL_HTTPS';
}
