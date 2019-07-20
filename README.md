# raspi-tools
A collection of tools &amp; tutorials for getting started with the Raspberry Pi :)

Written (very quickly) with vanilla PHP & MySQL because it was what I knew best and I wanted to make it quickly.

Code isn't amazing but it does the job. I will do my best to keep it backwards compatible as I (slowly) refactor.

Latest (master) build is running on https://bpmct.net/projects/raspi

## Run this on your server:

A simple LAMP stack is required. Create a database and copy `db-conf.example.php` to `db-conf.php`. Then change database details. Import the tables structure by using the `tables.sql` file in MySQL Cli or PHPMyAdmin. 

## Contributing

If you made changes to the DB structure, be sure to include the modified version of `tables.sql`. You can generate this by using PHPMyAdmin Export (structure only) or by using this command:

`mysqldump  -d -u mysql_user -p database_name > tables.sql`

## Updating

To update to the latest version, simply run a `git pull` or replace your files. Import the latest `tables.sql` if you're OK with erasing your tables.

**Note:** If you don't want to lose your database info, open `tables.sql` and compare the table structures in the file with your table structures. Modify as necessary. Don't import the file before taking a look as it could your existing tables and re-create them. Will be improved later
