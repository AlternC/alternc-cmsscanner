
CREATE TABLE IF NOT EXISTS `cmsscanner` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cms` varchar(128) NOT NULL,
  `version` varchar(128) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `sdate` datetime NOT NULL DEFAULT current_timestamp(),
  `vhosts` text NOT NULL DEFAULT '',
  `uid` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`folder`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='List of php software found in the server';

 CREATE TABLE `cmsscanner_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdate` datetime NOT NULL DEFAULT current_timestamp(),
  `cms` varchar(128) NOT NULL,
  `version` varchar(128) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `vhosts` TEXT NOT NULL default '',
  `uid` int(10) unsigned NOT NULL,
  `action` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `oldversion` varchar(255) NOT NULL,
  `oldvhosts` TEXT NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `sdate` (`sdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO variable SET name='cmsscanner_cron', value=3, comment='shall we update the list of hosted software on the server automatically (0=no, 1=daily, 2=weekly, 3=monthly)';
INSERT IGNORE INTO variable SET name='cmsscanner_checkerapi', value='https://cmschecker.octopuce.fr/', comment='If not null, use this API url (should end by /) to check software version and SHA256 sums.';

