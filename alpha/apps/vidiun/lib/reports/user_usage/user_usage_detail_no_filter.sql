SELECT
	raw_data.vuser_id,
	users.puser_id name,
	raw_data.added_entries added_entries,
	raw_data.deleted_entries deleted_entries,
	total.total_entries total_entries,
	raw_data.added_storage_mb added_storage_mb,
	raw_data.deleted_storage_mb deleted_storage_mb,
	total.total_storage_mb total_storage_mb,
	raw_data.added_msecs added_msecs,
	raw_data.deleted_msecs deleted_msecs,
	total.total_msecs total_msecs
FROM	
	(SELECT
		vuser_id, date_id,
		IFNULL(SUM(added_storage_kb),0)/1024 added_storage_mb,
		IFNULL(SUM(deleted_storage_kb),0)/1024 deleted_storage_mb,
		IFNULL(SUM(added_entries),0) added_entries,
		IFNULL(SUM(deleted_entries),0) deleted_entries,
		IFNULL(SUM(added_msecs),0) added_msecs,
		IFNULL(SUM(deleted_msecs),0) deleted_msecs
	FROM
		vidiundw.dwh_hourly_user_usage
        WHERE
		partner_id = {PARTNER_ID}
		AND
		date_id BETWEEN {FROM_DATE_ID} AND {TO_DATE_ID}
	GROUP BY vuser_id	
    ) raw_data,
	(SELECT
		u.vuser_id,
		total_storage_kb/1024 total_storage_mb,
		total_entries,
		total_msecs
	FROM
		vidiundw.dwh_hourly_user_usage u JOIN (SELECT vuser_id, MAX(date_id) date_id FROM vidiundw.dwh_hourly_user_usage WHERE partner_id = {PARTNER_ID} AND date_id <= {TO_DATE_ID} GROUP BY vuser_id) MAX
	    ON u.vuser_id = max.vuser_id AND u.date_id = max.date_id) total,
	dwh_dim_vusers users
WHERE raw_data.vuser_id = total.vuser_id
AND raw_data.vuser_id = users.vuser_id
ORDER BY {SORT_FIELD}
LIMIT {PAGINATION_FIRST},{PAGINATION_SIZE}		

