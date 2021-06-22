# Follow below steps:

 - Clone repo : https://github.com/JinalParmar26/infiniglobetest.git
 - Run command composer install
 - Run command sudo chmod 777 -R statics
 - Change database configurations in config/Database.php
 - In database create table as follow:
	`CREATE TABLE `inifini_datas` (
	  `data_id` int(11) NOT NULL,
	  `data_file_name` varchar(255) NOT NULL,
	  `data_contents` json NOT NULL,
	  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;`
 - In index.php file change the path of zip file and then run the project
 - For the rest api there is screenshot in root directory(postmansetup.png) . Set url and parameters as shown in screenshot and run the API.
 - Unit Testing : change file path in unittest file and run command ./vendor/bin/phpunit tests