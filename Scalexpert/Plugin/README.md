# scalexpert-magento

Scalexpert plugins for Magento CE & EE 2.4.3, 2.4.4, 2.4.6

version: 1.2.0
 
## Installation & upgrade

- Remove app/code/Scalexpert/Plugin folder if already exists.
- Create a new app/code/Scalexpert/Plugin folder.
- Unzip module in your Magento 2 app/code/Scalexpert/Plugin folder.
- Open command line and change to Magento installation root directory.
- Enable module: php bin/magento module:enable --clear-static-content Scalexpert_Plugin
- Upgrade database: php bin/magento setup:upgrade
- Re-run compile command: php bin/magento setup:di:compile
- Update static files by: php bin/magento setup:static-content:deploy [locale]

In order to deactivate the module: php bin/magento module:disable --clear-static-content Scalexpert_Plugin
