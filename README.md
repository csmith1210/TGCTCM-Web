# TGCTours Member Card Maker: Web App
### Project Description:
This is a web app version of the [TGCTours Card Maker](https://github.com/csmith1210/TGCT-Card-Maker) that was ported to PHP to run off any Apache or Nginx web server. This app will add users to the SQLite database as they load the script with their player ID and template choice. Then, a script is able to be called in a cron job to automatically update every user's image so that each week's stats are added to the image. Users now only have to link to their respective card images in their signatures and do not have to update it themselves.
### File Descriptions:
* **.htaccess**
  * Denies access to *.db files to outside connections and prevents browser caching of images using HTML ETags.
  * For Apache web servers. To convert to Nginx site.conf format please [go here](https://winginx.com/en/htaccess) and see [this page](https://stackoverflow.com/questions/24549377/how-to-configure-etag-on-nginx).
* **index.php**
  * This is the welcome page for the web app. It contains an HTML form to facilitate easy access to a player's member card and will replace the form with the user's image.
* **mkimg.php**
  * This script is the true port of the C# application to PHP since it contains the main functions of the web app. Scrapes TGCTours.com and saves a player's statistics to a template of their choosing.
* **update.php**
  * This script loops through every user in the SQLite database and updates their member card image. This is to be used in combination with cron (or a poor man's cron implementation) to update the images periodically without the need for user interaction.
* **rb.php**
  * This is the RedBeanPHP object relational mapping package used to facilitate database management and simulate C# DataTable objects.
* **red.db**
  * The SQLite database file. The file in this repository is empty.
### How to use the program:
1. Load the files onto an Apache or Nginx webserver, or access the web app [here](http://tgctcardmaker.rf.gd/).
   * If using your own webserver, ensure the .htaccess file or site.conf is configured properly. Also be sure to set a cron job to call update.php every 3 days or so (however often you want).
2. On the welcome page, enter the player's ID and template choice, then click the Get Image button.
3. Copy the outputted BBCode to your signature on forums, or use the image URL however you like.
4. The web app will automatically update your members card with each weeks stats as the TGCTours website is updated.
   * This will actually have a delay due to the cron job execution requirement.
### The Template:
![template](https://github.com/csmith1210/TGCT-Card-Maker/raw/master/TGCT%20Card%20Maker/Resources/template.png)
### Acknowledgements:
* [**Coming Sssoon Page by Creative Tim**](https://www.creative-tim.com/product/coming-sssoon-page): This project was used for the welcome page template. The HTML and CSS was heavily modified to suit the project's purposes. The project is licensed under the [MIT license](https://github.com/creativetimofficial/coming-sssoon-page/blob/master/LICENSE.md).
* [**Responsive Form Widget Template by W3layouts**](https://w3layouts.com/different-multiple-form-widget-flat-responsive-widget-template/): This project was used for its form layout (textbox and dropdown) for the welcome page template. The HTML and CSS was heavily modified to suit the projects purposes. The project is licensed under the [Creative Commons Attribution 3.0](http://creativecommons.org/licenses/by/3.0/).
* [**RedBeanPHP**](https://redbeanphp.com/index.php): This project was used to facilitate database management and simulate C# DataTable objects. The project is dual licensed under [New BSD and GPLv2](https://redbeanphp.com/index.php?p=/license).