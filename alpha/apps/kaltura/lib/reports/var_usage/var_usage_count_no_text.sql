SELECT COUNT(*)*(IF('{GROUP_COLUMN}' = 'date_id', DATEDIFF({TO_DATE_ID},{FROM_DATE_ID}), PERIOD_DIFF(FLOOR({TO_DATE_ID}/100),FLOOR({FROM_DATE_ID})/100)) + 1 ) count_all
FROM vidiundw.dwh_dim_partners 
WHERE
{OBJ_ID_CLAUSE}

