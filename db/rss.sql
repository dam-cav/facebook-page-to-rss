CREATE TABLE channel(
    title VARCHAR(60) PRIMARY KEY,
    link VARCHAR(150),
    description VARCHAR(400),
    lastupdate datetime NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE item(
    title VARCHAR(120) PRIMARY KEY,
    link VARCHAR(150),
    description VARCHAR(800),
    imgurl VARCHAR(350),
    ptime DATETIME,
    chtitle VARCHAR(60),
    FOREIGN KEY (chtitle) REFERENCES channel(title)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

#Example: change PAGEID with the id of the pages that interest you and run the query for each one of them:
#INSERT INTO `channel` (`title`, `link`, `description`, `lastupdate`) VALUES ('PAGEID', 'https://www.facebook.com/pg/PAGEID/posts/', NULL, '2000-01-01 00:00:01');