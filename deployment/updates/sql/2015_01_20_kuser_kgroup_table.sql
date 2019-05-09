CREATE TABLE `vuser_vgroup`
(
	`id` BIGINT  NOT NULL AUTO_INCREMENT,
	`vuser_id` INTEGER  NOT NULL,
	`puser_id` VARCHAR(100) NOT NULL,
	`vgroup_id` INTEGER  NOT NULL,
	`pgroup_id` VARCHAR(100) NOT NULL,
	`status` TINYINT  NOT NULL,
	`partner_id` INTEGER  NOT NULL,
	`created_at` DATETIME,
	`updated_at` DATETIME,
	`custom_data` TEXT,
	PRIMARY KEY (`id`),
	KEY `partner_vuser_index`(`vuser_id`, `status`),
	KEY `partner_vgroup_index`(`vgroup_id`, `status`),
	KEY `partner_index`(`partner_id`, `status`),
	CONSTRAINT `vuser_vgroup_FK_1`
	FOREIGN KEY (`vgroup_id`)
	REFERENCES `vuser` (`id`),
	CONSTRAINT `vuser_vgroup_FK_2`
	FOREIGN KEY (`vuser_id`)
	REFERENCES `vuser` (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;