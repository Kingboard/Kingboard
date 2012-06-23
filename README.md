# Kingboard
Copyright 2010 - 2012, Peter Petermann, the Kingboard team and EVSCO.
All rights reserved.

## WARNING
this is a work in progress, it lacks documentation and is currently
not meant for productive use, consider it extremly unstable, and probably
compatibility breaking

## LICENSE
see LICENSE.md File

## REQUIREMENTS
- PHP 5.3+
- APACHE + mod_rewrite (might work on other servers with own rewrite rulesets, untested) (Mistral can be used instead, but thats highly experimental!) OR lighttpd + rewrite rule OR with PHP 5.4+ the buildin webserver
- LINUX / MAC OS X (might run on windows, untested)
- MongoDB: 1.8.1 or higher, 2.0+ recommended
- King23: current github clone
- Pheal: current github clone
- Composer: http://getcomposer.org/

## INSTALLATION
1. Clone it from github `git clone git://github.com/ppetermann/Kingboard.git`
2. Move into KingBoard `cd Kingboard`
3. run `php composer.phar install` to install dependencies
5. Create folders cache/api cache/templates_c, make sure they are writable by your webserver
6. Get the database dump from [github.com/beansman](https://github.com/beansman/CCP-Static-Datadump-to-MongoDB)
7. Extract the zip file and run mongorestore <ExtractPath>
8. Make your webservers docroot point to public/ path
9. (optional) if using lighthttpd make a rewrite rule: url.rewrite-if-not-file = (".*\?(.*)$" => "/index.php?$1", "" => "/index.php")
10. (optional, recommended) run vendor/bin/king23 KingboardMaintenance:setup_indexes
11. copy conf/config.php-dist to conf/config.php and edit.

## USAGE
basically you should have a running killboard site with no content yet,
at the moment content is only added through tasks,
checkout:
vendor/bin/king23 Kingboard
vendor/bin/king23 KingboardMaintenance
vendor/bin/king23 KingboardCron
for information on available tasks

## Links
- [Kingboard Github](https://github.com/ppetermann/Kingboard)
- [Kingboard Website](https://kingboard.3rdpartyeve.net)
- [King23](http://king23.net)
- [Pheal](https://github.com/ppetermann/pheal)
- [EVSCO](http://evsco.net)