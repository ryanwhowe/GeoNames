CREATE TABLE `geoname` (
    `geonameid` int(11) NOT NULL,
    `name` varchar(200) DEFAULT NULL,
    `asciiname` varchar(200) DEFAULT NULL,
    `alternatenames` varchar(10000) DEFAULT NULL,
    `latitude` decimal(10,7) DEFAULT NULL,
    `longitude` decimal(10,7) DEFAULT NULL,
    `fclass` char(1) DEFAULT NULL,
    `fcode` varchar(10) DEFAULT NULL,
    `country` varchar(2) DEFAULT NULL,
    `cc2` varchar(60) DEFAULT NULL,
    `admin1` varchar(20) DEFAULT NULL,
    `admin2` varchar(80) DEFAULT NULL,
    `admin3` varchar(20) DEFAULT NULL,
    `admin4` varchar(20) DEFAULT NULL,
    `population` bigint(11) DEFAULT NULL,
    `elevation` int(11) DEFAULT NULL,
    `gtopo30` int(11) DEFAULT NULL,
    `timezone` varchar(40) DEFAULT NULL,
    `moddate` date DEFAULT NULL,
    PRIMARY KEY (`geonameid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `incremental_files` (
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `is_processed` SMALLINT DEFAULT 0 NOT NULL,
    `download_date` DATE NOT NULL,
    `processed_date` DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
