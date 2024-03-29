<?php
/**
 * @package Core
 * @subpackage model.enum
 */ 
interface StorageProfileProtocol extends BaseEnum
{
	const VIDIUN_DC = 0;
	const FTP = 1;
	const SCP = 2;
	const SFTP = 3;
	const S3 = 6;
	const LOCAL = 7;
}