ers
===

EJC Registration System
-----------------------

The EJC Registration System has three main tasks:
1. Give jugglers the possibility to buy tickets for the European Juggling Convention.
2. Give the organisation team onsite the possibility to easily check what the participants have booked.
3. Create needed statistics after the EJC

Installation instructions
-------------------------

1. get a copy of the project:

$ git clone https://github.com/inbaz/ers

2. create a VirtualHost running PHP (We tested on PHP 5.5, maybe 5.4 is working, 5.3 doesn't)

3. create a mysql database and user

mysql> CREATE DATABASE ers CHARACTER SET utf8 COLLATE utf8_bin;
mysql> GRANT ALL PRIVILEGES ON ers.* TO 'ers'@'localhost' IDENTIFIED BY 'secureerspass';
mysql> exit;

4. load database scheme

mysql < install/ers.sql

5. load database example data

mysql < install/ers-insterts.sql

Administration information
--------------------------
