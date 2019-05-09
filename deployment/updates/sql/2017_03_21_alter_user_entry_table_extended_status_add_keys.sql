ALTER TABLE user_entry 
ADD extended_status INT AFTER type,
ADD KEY `vuser_id_updated_at` (`vuser_id`,`updated_at`),
ADD	KEY `vuser_id_extended_status_updated_at` (`vuser_id`, `extended_status`, `updated_at`);