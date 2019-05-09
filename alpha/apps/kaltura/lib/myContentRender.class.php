<?php
/**
 * required for VERY old widgets (wiki extension)
 *
 */
class myContentRender
{
	/**
	 * This function returns the html representation of an entry.
	 * the entity id and it's random obfuscator (which is used also for versioning)
	 * if the random obfuscator is lower then the MIN_OBFUSCATOR_VALUE the file name
	 * refers to a static template which resides in the templates directory
	 * @param int $mediaType = the media type (video, image, text, etc...)
	 * @param string $data = path to data stored on vidiun servers
	 * @param int $width = width of html object
	 * @param int $height = height of html object
	 * @return string the html reprenstation of the given data
	 */
	static public function createPlayerMedia($entry)
	{
		$status = $entry->getStatus();
		$mediaType = $entry->getMediaType();
		
		$vmediaType = entry::ENTRY_MEDIA_TYPE_TEXT;
		
		if ($status == entryStatus::IMPORT)
		{
			$vmediaData = 'The clip is currently being imported. This may take a couple of minutes. You can continue browsing this Vidiun';
		}
		else if ($status == entryStatus::PRECONVERT)
		{
			$vmediaData = 'Clip is being converted. This might take a couple of minutes. You can continue browsing the Vidiun.' ;// 'Entry is being converted';
		}
		else if ($status == entryStatus::ERROR_CONVERTING)
		{
			$vmediaData = 'Error converting entry';
		}
		else if ($mediaType == entry::ENTRY_MEDIA_TYPE_SHOW)
		{
			$vmediaType = $mediaType;
			$vmediaData = $entry->getId();
		}
		else if ($mediaType == entry::ENTRY_MEDIA_TYPE_IMAGE ||
			$mediaType == entry::ENTRY_MEDIA_TYPE_AUDIO ||
			$mediaType == entry::ENTRY_MEDIA_TYPE_VIDEO)
		{
			$vmediaType = $mediaType;
			$vmediaData = "http://".$_SERVER['SERVER_NAME'].$entry->getDataPath();
		}
		else
		{
			$vmediaData = 'Error: Cannot Show Object';
		}
		
		return array($status, $vmediaType, $vmediaData);
	}

	/**
	 * This function returns the html representation of an entry that can be embedded into other sites.
	 * It is very similar to the function above, except omits the surrounding div tags.
	 *
	 * the entity id and it's random obfuscator (which is used also for versioning)
	 * if the random obfuscator is lower then the MIN_OBFUSCATOR_VALUE the file name
	 * refers to a static template which resides in the templates directory
	 * @param int $mediaType = NOT USED!!! the media type (video, image, text, etc...)
	 * @param string $data = path to data stored on vidiun servers
	 * @param int $width = width of html object
	 * @param int $height = height of html object
	 * @return string the html reprenstation of the given data
	 */
	static public function createShareHTML($mediaType, $data, $width, $height)
	{
		$imagesExtArray = array('bmp', 'png', 'jpg', 'gif');

		$shareHTML = 'Cant Show Object';

		$ext = strtolower(pathinfo($data, PATHINFO_EXTENSION));
		if (in_array($ext, $imagesExtArray))
		{
			$shareHTML = '<img style="width:_vid_width_px;height:_vid_height_px" src="'.$data.'">';
		}

		$shareHTML = str_ireplace("_vid_width_", $width, $shareHTML);
		$shareHTML = str_ireplace("_vid_height_", $height, $shareHTML);

		return $shareHTML;
	}
}

?>
