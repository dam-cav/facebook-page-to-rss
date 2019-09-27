# FacebookPage2Rss

Get posts of facebook's pages as rss feed directly from html code without using facebook API.

## Scraped classes

| Class | Content |
| ----- | ------- |
| _1xnd | Entire list of posts       |
|  _4-u2 _4-u8 | Single post, used to know when a new post is found |
| l_c3pyo2v0u | Heading with page logo, name, time |
| _5pcq | Contains href attribute with post link |
| timestampContent | Time text |
|_5pbx userContent _3576 | Post text |
|_5pbx userContent _3ds9 _3576 | Text-only post |
|_52c6 | Contains href attribute with post embedded page preview |
|mbs _6m6 _2cnj _5s6c | Title of embedded page |
|_6m7 _3bt9 | Text of embedded page |
|scaledImageFitWidth/Height img | Post main image |

## How to use this repository for private purposes

You need a host that supports PHP 7 and a mySQL database with InnoDB engine.
I specifically chose these minimum requirements because there are a lot of sites that offer these services for free.

I agree that mySQL is a bit overkill for this sort of thing but using something like mongoDB would require adding extra libraries with Composer, which many free sites don't support.


Steps to take to start the system yourself:

- Setup your mySQL database and fill the tables with the `CREATE TABLE` commands you can find in the *db/rss.sql* file.
- Run the insert command from the same file's comment for each of your followed pages.
Make sure you replace `PAGEID` with the id of the page you want, you can get it from the URL of the page itself, for example the id of the `https://www.facebook.com/GitHub/`page is `GitHub`.
- Edit the *source/rss_db.php* file on lines 5,6,7,8 entering your database data.
- Upload the contents of the *source* folder to your hosting site.
  (Make sure no one has access to *rss_db.php*! Configure the server correctly! In the case of Apache, you need to modify the ".htaccess" file)
  From now on when you call `YOURSITEDOMAIN/generate.php` the post with older update will be updated.
  At `YOURSITEDOMAIN/rss.php?rsschannel=PAGEID` there will be specific page feeds.
- Now you need to setup a cron-job that calls `YOURSITEDOMAIN/generate.php` with the update frequency you prefer, choose this time considering the number of pages you follow.
  If the site you use does not support cron-jobs, you can use [cron-job.org](https://cron-job.org/).

## RSS Client

RSS is a standard so you can use whatever client you prefer, however I have only tested with [Feeder](https://f-droid.org/en/packages/com.nononsenseapps.feeder/) on Android and with [Thunderbird](https://www.thunderbird.net/) on PC and I actively use only the first one.

## Possible Problems / Things you might not like

- For each *generate.php* executed, the list of posts in the database for the page is completely emptied and regenerated.
  The RSS clients I have tested do not show notifications again for posts already seen because they use the [guid](https://www.w3schools.com/xml/rss_tag_guid.asp) of the rss to uniquely identify news already read.

  The *guid* I used is always the url of the post or the url included in its preview (or the md5 hash of the description as a way to work even in case problems), if for some reason this vary in the time, you may receive multiple notifications for the same post.

- If there are more images in the post, only one is taken and used as a preview.
  This is a wanted behavior, if you don't like this, this is not the solution for you.
- The description provided by the feeds is only a preview and does not always report the entire post.
  It provides just the information you need to understand the topic and decide whether to open the URL associated with the feed.
- I have tested this only with pages that interest me so I can't guarantee the operation with all the pages on facebook, feel free to create your fork to adapt the software to your needs.
