CREATE SCHEMA IF NOT EXISTS `acdemo` DEFAULT CHARACTER SET utf8mb4 ;
GRANT Insert,Select,Delete,Update ON `acdemo`.* TO `acuser`@`%` IDENTIFIED by 'password123';
flush privileges;
