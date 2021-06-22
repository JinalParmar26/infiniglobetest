# Follow below steps:

 - Clone repo : https://github.com/JinalParmar26/infiniglobetest.git
 - Run command composer install
 - Run command sudo chmod 777 -R statics
 - Change database configurations in config/Database.php
 - In database import the table structure from inifini_datas.sql file.
 - In index.php file change the path of zip file and then run the project
 - For the rest api there is screenshot in root directory(postmansetup.png) . Set url and parameters as shown in screenshot and run the API.
 - Unit Testing : change file path in unittest file and run command ./vendor/bin/phpunit tests