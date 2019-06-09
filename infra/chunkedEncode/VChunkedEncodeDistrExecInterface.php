<?php
 
/*****************************
 * Includes & Globals
 */
//ini_set("memory_limit","512M");

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/****************************
	 * VChunkedEncodeDistrExecInterface
	 */
	interface VChunkedEncodeDistrExecInterface
	{
		/* ---------------------------
		 *
		 */
		 public function AddJob($job);
		 
		/* ---------------------------
		 *
		 */
		 public function SaveJob($job);
		 
		/* ---------------------------
		 *
		 */
		 public function FetchJob($keyIdx);
		 
		/* ---------------------------
		 *
		 */
		public function DeleteJob($keyIdx);
		
		/* ---------------------------
		 * GetActiveSessions
		 *	
		 */
		public function GetActiveSessions();
	}
	/*****************************
	 * End of VChunkedEncodeDistrExecInterface
	 *****************************/

	/****************************
	 * VChunkedEncodeDistrSchedInterface
	 */
	interface  VChunkedEncodeDistrSchedInterface extends VChunkedEncodeDistrExecInterface
	{
		/* ---------------------------
		 *
		 */
		public function FetchNextJob();

		/* ---------------------------
		 *
		 */
		public function RefreshJobs($maxSlots, &$jobs);

		/* ---------------------------
		 *
		 */
		public function ExecuteJob($job);
	}
	/*****************************
	 * End of VChunkedEncodeDistrSchedInterface
	 *****************************/
