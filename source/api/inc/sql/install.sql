CREATE TABLE `tmsbot_record` (
  `rc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rc_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rc_type` varchar(10) NOT NULL,
  `rc_sender` varchar(30) NOT NULL,
  `rc_receiver` varchar(20) NOT NULL,
  `rc_request` text NOT NULL,
  `rc_response` text NOT NULL,
  PRIMARY KEY (`rc_id`),
  KEY `rc_sender` (`rc_sender`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tmsbot_qaa` (
  `qaa_id` int NOT NULL AUTO_INCREMENT, 
  `msgStr` varchar(255) DEFAULT NULL,
  `contentStr` text(65535) DEFAULT NULL,
  `deleted` int(11) DEFAULT NULL,
  `deleter` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`qaa_id`),
  INDEX `msgStr` (`msgStr`) 
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `tmsbot_weather_code` (
  `city_id` varchar(255) NOT NULL,
  `city_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;