# Magento 2 Pixelbin IO Module
Pixelbin.io is a Digital Asset Management Platform that empowers users to store, serve, and transform images and videos using AI-based solutions.

## 1. Documentation
- [Introduction guide](https://www.pixelbin.io/docs/introduction/)
- [User Guide](https://www.pixelbin.io/docs/getting-started/)

## 2. How to install Pixelbin IO Extension

### Install via composer
Run the following command in Magento 2 root folder:

```
composer require pixelbinio/pixelbin
composer require pixelbin/pixelbin
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```
### Install without composer
- Download the module from git
- Upload the module on in Magento root app/code directory
- Module is dependent on pixelbin php sdk as well so run below command as well to install it
  ```
  composer require pixelbin/pixelbin
  ```
  Install the module with below command
  ```
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy
    php bin/magento cache:flush
  ```
  
## Once module is install go to Store > Configuration > Pixelbin.IO > Pixelbin
Configure the module here like Enable/Disable, Cloud Name, Zone link etc

![configuration settings](https://i.imgur.com/xu6HSFB.png)


After that please configure the other parts of the module like default image, transformation, product gallery, lazylad and other.

Image sync with pixelbin
There are two ways to synchronize the media to pixelbin (Console command).
Through the console command bin/magento pixelbin:upload:all this is synchronized all the media to pixelbin except below folder
.thumbscatalog
.thumbswysiwyg
catalog/tmp/
catalog/product/cache/


All the sync status you can find it backend grid Pixelbin > Image Sync



Sync through the cron. In backend we have settings to sync via cron Store > Configuration > Pixelbin.IO > Pixelbin > Pixelbin manual sync
From here we can configure the module and enable manual sync. Once we enable it will collect all the images and add it into the queue and then via cron it will sync 500 images in every cron.



With this module we can directly import image/video from pixelbin to product image, product description, CMS block, CMS page
