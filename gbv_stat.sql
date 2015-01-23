CREATE TABLE `gbv_stat` (
 `identnum` bigint(20) unsigned NOT NULL,
 `identrep` enum('REPO1','REPO2','REPO3') collate ascii_bin NOT NULL,
 `date` date NOT NULL,
 `counter` int(11) unsigned NOT NULL,
 `counter_abstract` int(11) unsigned NOT NULL,
 `robots` int(11) unsigned NOT NULL,
 `robots_abstract` int(11) unsigned NOT NULL,
 `country` char(2) collate ascii_bin default NULL,
 UNIQUE KEY `identnum` (`identnum`,`identrep`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=ascii COLLATE=ascii_bin