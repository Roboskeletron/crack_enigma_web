drop database if exists crack_enigma;
create database crack_enigma;
use crack_enigma;

CREATE TABLE crack_enigma.users (
		name varchar(80) NOT NULL,
		email varchar(80) NOT NULL,
		password varchar(100) NOT NULL,
		CONSTRAINT NewTable_pk PRIMARY KEY (email),
		CONSTRAINT NewTable_un UNIQUE KEY (name)
	)
	ENGINE = InnoDB;

CREATE TABLE crack_enigma.cyphertexts (
		id BIGINT UNSIGNED auto_increment NOT NULL,
		name varchar(80) NOT NULL,
		author varchar(80) NOT NULL,
		`text` TEXT NOT NULL,
		encrypted TEXT NOT NULL,
		code json NOT NULL,
		`total attempts` INT UNSIGNED DEFAULT 0 NOT NULL,
		`successful attempts` INT UNSIGNED DEFAULT 0 NOT NULL,
		CONSTRAINT cyphertexts_pk PRIMARY KEY (id),
		CONSTRAINT cyphertexts_un UNIQUE KEY (name,author))
	ENGINE = InnoDB;
	
ALTER TABLE crack_enigma.cyphertexts ADD CONSTRAINT cyphertexts_FK FOREIGN KEY (author) REFERENCES crack_enigma.users(name) ON DELETE CASCADE ON UPDATE CASCADE;
