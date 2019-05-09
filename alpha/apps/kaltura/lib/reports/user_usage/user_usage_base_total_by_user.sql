SELECT
	IFNULL(SUM(total_storage_kb/1024),0) total_storage_mb,
	IFNULL(SUM(total_entries),0) total_entries,
	IFNULL(SUM(total_msecs),0) total_msecs
FROM
	vidiundw.dwh_hourly_user_usage u JOIN (SELECT vuser_id, MAX(date_id) date_id FROM vidiundw.dwh_hourly_user_usage WHERE partner_id = {PARTNER_ID} and date_id < {FROM_DATE_ID} GROUP BY vuser_id) total
	ON u.vuser_id = total.vuser_id AND u.date_id = total.date_id   										
	WHERE {OBJ_ID_CLAUSE}
UNION SELECT
	0 total_storage_mb,
	0 total_entries,
	0 total_msecs

 	






