CREATE TABLE IF NOT EXISTS `entry_vendor_task`
(
	`id` INTEGER  NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`queue_time` DATETIME DEFAULT NULL,
	`finish_time` DATETIME DEFAULT NULL,
	`partner_id` INTEGER  NOT NULL,
	`vendor_partner_id` INTEGER  NOT NULL,
	`entry_id` VARCHAR(31)  NOT NULL,
	`status` TINYINT  NOT NULL,
	`price` INTEGER  NOT NULL,
	`catalog_item_id` INTEGER  NOT NULL,
	`reach_profile_id` INTEGER  NOT NULL,
	`vuser_id` INTEGER  NOT NULL,
	`version` INTEGER,
	`context` VARCHAR(256),
	`custom_data` TEXT,
	PRIMARY KEY (`id`),
	KEY `partner_id_status_index`(`partner_id`, `status`),
	KEY `vendor_partner_id_status_index`(`vendor_partner_id`, `status`),
	KEY `partner_entry_index`(`partner_id`, `entry_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;