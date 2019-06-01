<?php
/**
 * @package api
 * @subpackage enum
 */
class VidiunAmazonS3StorageProfileFilesPermissionLevel extends VidiunStringEnum
{
	// ACL flags
	const ACL_PRIVATE = "private";
	const ACL_PUBLIC_READ = "public-read";
	const ACL_PUBLIC_READ_WRITE = "public-read-write";
	const ACL_AUTHENTICATED_READ = "authenticated-read";
}
