DROP TABLE IF EXISTS love_album;
CREATE TABLE love_album (
  id int(11) NOT NULL AUTO_INCREMENT,
  album_name varchar(100) NOT NULL,
  album_cover varchar(500) DEFAULT NULL,
  album_desc varchar(200) DEFAULT NULL,
  sort_order int(11) DEFAULT '0',
  create_time datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO love_album (album_name, album_desc, sort_order, create_time) VALUES
('Default Album', 'All uncategorized photos', 0, NOW());