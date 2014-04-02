CREATE TABLE `tmsbot_qaa` (
  `qaa_id` int NOT NULL AUTO_INCREMENT, 
  `msgStr` varchar(255) DEFAULT NULL,
  `contentStr` text(65535) DEFAULT NULL,
  `deleted` int(11) DEFAULT NULL,
  `deleter` varchar(255) DEFAULT NULL,
  `qaa_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`qaa_id`),
  INDEX `msgStr` (`msgStr`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tmsbot_weather_code` (
  `city_id` varchar(255) NOT NULL,
  `city_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;