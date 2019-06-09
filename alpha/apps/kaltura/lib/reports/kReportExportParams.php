<?php

class vReportExportParams
{
	/**
	 * @var string
	 */
	protected $recipientEmail;

	/**
	 * @var array
	 */
	protected $reportItems;

	/**
	 * @return string
	 */
	public function getRecipientEmail()
	{
		return $this->recipientEmail;
	}

	/**
	 * @param string $recipientEmail
	 */
	public function setRecipientEmail($recipientEmail)
	{
		$this->recipientEmail = $recipientEmail;
	}

	/**
	 * @return array
	 */
	public function getReportItems()
	{
		return $this->reportItems;
	}

	/**
	 * @param array $reportItems
	 */
	public function setReportItems($reportItems)
	{
		$this->reportItems = $reportItems;
	}

}
