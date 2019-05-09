ALTER TABLE user_entry 
ADD privacy_context VARCHAR(255) AFTER extended_status,
DROP KEY `entry_id`,
DROP KEY `vuser_id_updated_at`,
DROP KEY `vuser_id_extended_status_updated_at`,
ADD KEY (`entry_id`, `vuser_id`, `privacy_context`),
ADD KEY `vuser_id_updated_at` (`vuser_id`,`updated_at`),
ADD KEY `vuser_id_extended_status_updated_at` (`vuser_id`, `extended_status`, `updated_at`, `privacy_context`);
