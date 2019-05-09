SET GLOBAL sql_mode = '';
DELIMITER $$

/* Procedure structure for procedure `update_entries` */
DROP PROCEDURE IF EXISTS `update_entries`$$

CREATE PROCEDURE `update_entries`()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE entry_id CHAR(50);
    DECLARE new_views, new_plays INT;
    DECLARE updated_entries CURSOR FOR SELECT id, plays, views FROM temp_entry_update;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN updated_entries;
    
    SET SESSION sql_log_bin = 1;
    REPEAT
    FETCH updated_entries INTO entry_id, new_plays, new_views;
    UPDATE entry SET entry.plays = new_plays, entry.views = new_views WHERE entry.id = entry_id;
    UNTIL done END REPEAT;
    SET SESSION sql_log_bin = 0;
    CLOSE updated_entries;
    END$$


/* Procedure structure for procedure `update_vusers` */
DROP PROCEDURE IF EXISTS `update_vusers`$$

CREATE PROCEDURE `update_vusers`()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE new_vuser_id CHAR(50);
    DECLARE new_storage_size INT;
    DECLARE updated_vusers CURSOR FOR SELECT vuser_id, storage_kb FROM vidiun.temp_updated_vusers_storage_usage;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    OPEN updated_vusers;
    
    SET SESSION sql_log_bin = 1;
    REPEAT
    FETCH updated_vusers INTO new_vuser_id, new_storage_size;
    UPDATE vuser SET vuser.storage_size = new_storage_size WHERE vuser.id = new_vuser_id;
    UNTIL done END REPEAT;
    SET SESSION sql_log_bin = 0;
    CLOSE updated_vusers;
    END$$

DELIMITER ;
