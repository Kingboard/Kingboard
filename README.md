# Kingboard
Copyright 2010 - 2013, Peter Petermann, the Kingboard team and EVSCO.
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
### Assumptions
A few assumptions are made before you start:
1. you are on linux, and you have commandline access.
2. you know how to handle yourself on linux
3. the prequesites (see README) are installed.
4. you have a vhost setup for the kingboard installation - this wont work in a subdirectory.

### Quick Install
1. run `php /path/to/composer.phar create-project kingboard/kingboard path/name` to install kingboard with its dependencies
2. extract vendor/kingboard/ccpdump/KingBoard.zip to a temporary directory and run mongorestore <ExtractPath>
3. Create folders cache/api and cache/templates_c below your kingboard path, make sure they are writable by your webserver
4. Make your webservers docroot point to public/ path
5. Setup rewrite (for apache the .htaccess should do that, for lighttpd add the rule: url.rewrite-if-not-file = (".*\?(.*)$" => "/index.php?$1", "" => "/index.php")
6. run vendor/bin/king23 KingboardMaintenance:setup_indexes
7. copy conf/config.php-dist to conf/config.php and edit.

you should now be able to call your kingboard.

## Links
- [Kingboard Github](https://github.com/Kingboard/Kingboard)
- [Kingboard Website](https://kingboard.3rdpartyeve.net)
- [King23](http://king23.net)
- [Pheal](https://github.com/ppetermann/pheal)
- [EVSCO](http://evsco.net)