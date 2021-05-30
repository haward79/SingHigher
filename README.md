
# What's this
This is a web based online KTV system.


# Requirements
You need a server with dependencies to host this web application.
This web is written in html, css, JavaScript, Vue.js, php, MySQL, so you have to setup all dependencies on your server to install.

Following is the softwares and their versions on my server.
- OS : Ubuntu Server 20.04.2 LTS (Focal Fossa)
- Apache : 2.4.41
- Html : 5
- Css : 3
- JavaScript : ES 6
- Vue : 3.0.11
- php : 7.4.3
- MySQL : 8.0.25

You can use other softwares for replacement, but some code modification may be required.


# Installation
1. Clone or download this project from github.
2. Move the project to the path where your websites is placed.
3. Setup your web server for this project. Take apache for example, add a virtualhost for this project is a good idea. SingHigher directory inside the root of this project should be the root of website.
4. Import SingHigher.sql in the root of project to your mysql database.
5. Change the database authentication information in the file, php/database.php on line 5.

        /*  php/database.php on line 5.
         *
         *  Please fill out your hostname, username, password for your mysql configuration according.
         *
         */

        mysqli_connect('hostname', 'username', 'password', 'SingHigher');

6. Get a Youtube data API key from [YouTube Developer](https://developers.google.com/youtube/v3/) . (It's free with some limitation.)
7. Change youtube api key(s) in the file php/youtube.php on line 3.

        /*  php/youtube.php on line 3.
         *
         *  Please fill out your api key(s) in the array.
         *  One key as a string element in the array.
         *
         *  To make the system work, at least one key is required.
         *  More than one keys is recommended to prevent out of quota.
         *
         */

        define('kYoutubeKey', ['api_key1', 'api_key2']);

8. Open web browser and check whether the website is working correctly.


# Change Log
- 2021-05-31 (Version 1.0.0)
    1. Project pulled to github.


# Copyright
This project is written by haward79.
All rights reserved by haward79.

For other users, you are free to copy and spread contents in this project.
However, modification is NOT allowed.

For more details or project modification request, please contact haward79 and wait for good news.
You can find haward79's e-mail from my [personal website](https://www.haward79.tw/).

