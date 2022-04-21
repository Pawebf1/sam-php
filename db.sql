CREATE DATABASE sam_php;
USE sam_php;

DROP TABLE IF EXISTS ladunek;
DROP TABLE IF EXISTS transport;
CREATE TABLE transport (
                           id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                           transport_z          VARCHAR(30)  NOT NULL,
                           transport_do      VARCHAR(30)  NOT NULL,
                           typ_samolotu		  VARCHAR(30)  NOT NULL,
                           data_transportu   DATE  NOT NULL,
                           dokumenty         VARCHAR(100),
                           PRIMARY KEY(id)
);

CREATE TABLE ladunek (
                         id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                         transport_id INTEGER UNSIGNED NOT NULL,
                         nazwa         VARCHAR(30)  NOT NULL,
                         ciezar_ladunku      INTEGER  NOT NULL,
                         typ_ladunku      VARCHAR(30)  NOT NULL,
                         PRIMARY KEY(id),
                         FOREIGN KEY (transport_id) REFERENCES transport(id)
)

