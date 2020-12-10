ALTER TABLE Users
	DROP COLUMN first_name;
    ADD COLUMN (first_name varchar(60) default '', 
	last_name varchar(60) default '');
