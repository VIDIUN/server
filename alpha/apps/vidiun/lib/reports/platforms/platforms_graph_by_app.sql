SELECT
	DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1 date_id, # time shifted date
	device,
	IFNULL(SUM(count_plays),0) count_plays,
#	AVG(distinct_plays) distinct_plays, /* Because we don't know the real number, we use avarage instead*/
	IFNULL(SUM(sum_time_viewed),0) sum_time_viewed,
	IFNULL(SUM(sum_time_viewed)/SUM(count_plays),0) avg_time_viewed,
	IFNULL(SUM(count_loads),0) count_loads
FROM 
	dwh_hourly_events_context_app_devices ev, vidiundw.dwh_dim_os os, vidiundw.dwh_dim_applications ap
WHERE 
	ev.os_id = os.id
	AND ap.name = {APPLICATION_NAME}
	AND ap.partner_id = ev.partner_id
	AND ap.application_id = ev.application_id
	AND ev.partner_id =  {PARTNER_ID} # PARTNER_ID
	AND date_id BETWEEN IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID})  
    			AND     IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID})
			AND hour_id >= IF (date_id = IF({TIME_SHIFT}>0,(DATE({FROM_DATE_ID}) - INTERVAL 1 DAY)*1, {FROM_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 0)
			AND hour_id < IF (date_id = IF({TIME_SHIFT}<=0,(DATE({TO_DATE_ID}) + INTERVAL 1 DAY)*1, {TO_DATE_ID}), IF({TIME_SHIFT}>0, 24 - {TIME_SHIFT}, ABS({TIME_SHIFT})), 24)
	AND 
		( count_time_viewed > 0 OR
		  count_plays > 0 OR
		  count_loads > 0 OR 
		  sum_time_viewed > 0 )
GROUP BY DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1, device
ORDER BY DATE(DATE(date_id) + INTERVAL hour_id HOUR + INTERVAL {TIME_SHIFT} HOUR)*1, device
