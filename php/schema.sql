create table geoname
(
    geonameid      int            not null primary key,
    name           varchar(200)   null,
    asciiname      varchar(200)   null,
    alternatenames varchar(10000) null,
    latitude       decimal(10, 7) null,
    longitude      decimal(10, 7) null,
    fclass         char           null,
    fcode          varchar(10)    null,
    country        varchar(2)     null,
    cc2            varchar(60)    null,
    admin1         varchar(20)    null,
    admin2         varchar(80)    null,
    admin3         varchar(20)    null,
    admin4         varchar(20)    null,
    population     bigint(11)     null,
    elevation      int            null,
    gtopo30        int            null,
    timezone       varchar(40)    null,
    moddate        date           null
)
    charset = utf8mb4;




create table incremental_files
(
    id             int auto_increment primary key,
    filename       varchar(255)       not null,
    is_processed   smallint default 0 not null,
    download_date  date               not null,
    processed_date date               null,
    constraint incremental_files_filename_uindex
        unique (filename)
)
    charset = utf8mb4;


