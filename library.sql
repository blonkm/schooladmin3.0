CREATE TABLE `libraryfiles` (`libid` int(11) unsigned NOT NULL AUTO_INCREMENT, `tid` int(11) UNSIGNED DEFAULT 1, `folder` VARCHAR(128), `filename` VARCHAR(128),`type` TEXT,`data` LONGBLOB, lastmodified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY `id` (`libid`), UNIQUE KEY `fileref` (`folder`,`filename`)) ENGINE=MyISAM;
CREATE TABLE `libraryaccess` (`libid` int(11) unsigned NOT NULL, `idtype` ENUM ('G','S'), `sgid` int(11) UNSIGNED, PRIMARY KEY `id` (`libid`,`idtype`,`sgid`)) ENGINE=MyISAM;
INSERT INTO tt_english (short,full) VALUES("Library","<img src='PNG/library.png' title='Library'>");
INSERT INTO tt_nederlands (short,full) VALUES("Library","<img src='PNG/library.png' title='Bibliotheek'>");
INSERT INTO tt_english (short,full) VALUES("Folder","Folder");
INSERT INTO tt_nederlands (short,full) VALUES("Folder","Map");
INSERT INTO tt_english (short,full) VALUES("Create_Folder","Create folder");
INSERT INTO tt_nederlands (short,full) VALUES("Create_Folder","Maak map");
INSERT INTO tt_english (short,full) VALUES("Lib_error_upload","File upload error");
INSERT INTO tt_nederlands (short,full) VALUES("Lib_error_upload","Fout tijdens upload bestand");
INSERT INTO tt_english (short,full) VALUES("Lib_error_oversize","File too large");
INSERT INTO tt_nederlands (short,full) VALUES("Lib_error_oversize","Bestand is te groot");
INSERT INTO tt_english (short,full) VALUES("Lib_error_incomplete","Upload incomplete");
INSERT INTO tt_nederlands (short,full) VALUES("Lib_error_incomplete","Bestand niet compleet");
INSERT INTO tt_english (short,full) VALUES("Lib_error_exists","File already exists by other owner");
INSERT INTO tt_nederlands (short,full) VALUES("Lib_error_exists","Bestand bestaat al met andere eigenaar");