CREATE TABLE IF NOT EXISTS `parameters` (
  `id` VARCHAR(255) NOT NULL,
  `content` TEXT DEFAULT NULL,
  `is_serialized` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

ALTER TABLE `parameter`
  ADD PRIMARY KEY (`id`);
