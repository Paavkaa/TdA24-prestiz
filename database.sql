CREATE TABLE db.users
(
    uuid           UUID        NOT NULL PRIMARY KEY,
    title_before   VARCHAR(50),
    first_name     VARCHAR(50) NOT NULL,
    middle_name    VARCHAR(50),
    last_name      VARCHAR(50) NOT NULL,
    title_after    VARCHAR(50),
    picture_url    VARCHAR(255),
    location       VARCHAR(100),
    claim          TEXT,
    bio            TEXT,
    price_per_hour INT
);

CREATE TABLE db.tags
(
    uuid UUID        NOT NULL PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE db.users_tags
(
    user_uuid UUID NOT NULL,
    tag_uuid  UUID NOT NULL,
    PRIMARY KEY (user_uuid, tag_uuid),
    FOREIGN KEY (user_uuid) REFERENCES db.users (uuid),
    FOREIGN KEY (tag_uuid) REFERENCES db.tags (uuid)
);


CREATE TABLE db.telephone_numbers
(
    uuid      UUID        NOT NULL PRIMARY KEY,
    user_uuid UUID        NOT NULL,
    number    VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_uuid) REFERENCES db.users (uuid)
);

CREATE TABLE db.email_addresses
(
    uuid      UUID        NOT NULL PRIMARY KEY,
    user_uuid UUID        NOT NULL,
    email     VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_uuid) REFERENCES db.users (uuid)
);