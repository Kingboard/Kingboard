# Kingboard
Copyright 2010 - 2012, Peter Petermann, the Kingboard team and EVSCO.
All rights reserved.

## WARNING
this is a work in progress, it lacks a lot of documentation and is currently
not meant for productive use, consider it extremly unstable, and probably
compatibility breaking

## LICENSE
see LICENSE.md File

## REQUIREMENTS
- PHP 5.3
- APACHE + mod_rewrite (might work on other servers with own rewrite rulesets, untested) (Mistral can be used instead, but thats highly experimental!)
- LINUX / MAC OS X (might run on windows, untested)
- MongoDB: 1.8.1 or higher
- King23: current github clone
- Pheal: current github clone

## INSTALLATION
1. Clone it from github `git clone git://github.com/ppetermann/Kingboard.git`
2. Move into KingBoard `cd Kingboard`
3. Initiate Submodules `git submodule init`
4. Update Submodules `git submodule update`
5. Create folders cache and templates_c, make sure they are writable  by your webserver
6. Get the database dump from [github.com/beansman](https://github.com/beansman/CCP-Static-Datadump-to-MongoDB)
7. Extract the zip file and run mongorestore <ExtractPath>
8. Make your apache point to public/ path
9. (optional, recommended) run king23 KingboardMaintenance:setup_indexes

## USAGE
basically you should have a running killboard site with no content yet,
at the moment content is only added through tasks,
checkout:
king23 Kingboard
king23 KingboardMaintenance
king23 KingboardCron
for information on available tasks

## Links
- [Kingboard](https://github.com/ppetermann/Kingboard)
- [Kingboard Developer Wiki](https://github.com/ppetermann/Kingboard/wiki)
- [King23](http://king23.net)
- [Pheal](https://github.com/ppetermann/pheal)
- [EVSCO](http://evsco.net)