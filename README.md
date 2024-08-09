Magento Demo

Project Title: Razoyo_CarProfile

Description:
We are consuming Razoyo cars API using Magento. We can fetch all the cars as a different car models and user can save/update car model in his "My Car Profile" by selecting the a model from the select dropdown in My Car page.

The saved car is displaying with details like make of car, model of car, MPG, seat opions along with price and image of the car in 2 column layout.

Getting Started:
Dependencies:
Software/Tools: Magento 2.x, Elasticsearch 7.17+, Composer 2.7.7, PHP 8.0+, Java (Open JDK) 17, Apache 2, MariaDB 10.x
Operating System : Windows, Linux.

Required PHP extensions:
ext-bcmath
ext-ctype
ext-curl
ext-dom
ext-gd
ext-hash
ext-iconv
ext-intl
ext-mbstring
ext-openssl
ext-pdo_mysql
ext-simplexml
ext-soap
ext-xsl
ext-zip
ext-sockets
ext-xml
ext-xmlreader
ext-xmlwriter
lib-libxml (DOMDocument)

Installation steps
- Download Magento and extract.
- Install Composer and install dependencies.
- Run Apache2, and MySQL.
- Create a Database for Magento in MySql.
- Run the installation script to install magento.

Executing program
After Installation run below commands:
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy -f
- php bin/magento cache:flush
- php bin/magento cache:clean

To change between different modes use below commands:
- php bin/magento deploy:mode:show (To check the current mode)
- php bin/magento deploy:mode:set developer
- php bin/magento deploy:mode:set production

Authors
- Aloha Technology

Version History:
1.0 - Initial Release