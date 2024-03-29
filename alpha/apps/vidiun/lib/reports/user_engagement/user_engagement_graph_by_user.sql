SELECT
	DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1 date_id, # time shifted date
	IFNULL(SUM(count_plays),0) count_plays,
	IFNULL(SUM(sum_time_viewed),0) sum_time_viewed,
	IFNULL(SUM(sum_time_viewed)/SUM(count_plays),0) avg_time_viewed,
	IFNULL(SUM(count_loads),0) count_loads
FROM 
	dwh_hourly_events_context_entry_user_app ev, vidiundw.dwh_dim_pusers us
WHERE 	
	{OBJ_ID_CLAUSE} # ev.entry_id in 
	AND {CAT_ID_CLAUSE}
	AND ev.partner_id = {PARTNER_ID}
	AND us.puser_id = ev.user_id
	AND name IN {PUSER_ID}  
	AND ev.partner_id =  us.partner_id
	AND date_id BETWEEN IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID})  
    			AND     IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID})
			AND hour_id >= IF (date_id = IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 0)
			AND hour_id < IF (date_id = IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 24)
	AND 
		( count_time_viewed > 0 OR
		  count_plays > 0 OR
		  count_loads > 0 OR 
		  sum_time_viewed > 0 )
GROUP BY DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1
ORDER BY DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1
