# Kingboard
Copyright (C) 2010,2011 by Peter Petermann
All rights reserved.

## WARNING
this is a total prototype, undocumented, mostly born one night,
its not meant for anything productive, simply putting it up for 
educational purposes for a few friends

## LICENSE
assume a MIT Style license

## REQUIREMENTS
- PHP 5.2 (might run on earlier versions, untested)
- APACHE + mod_rewrite (might work on other servers with own rewrite rulesets, untested) (Mistral can be used instead, but thats highly experimental!)
- LINUX / MAC OS X (might run on windows, untested)
- MongoDB: 1.8.1 or higher
- King23:  current github clone
- Pheal: current github clone

## INSTALLATION
1. Clone it from github `git clone git://github.com/ppetermann/Kingboard.git`
2. Move into KingBoard `cd Kingboard`
3. Initiate Submodules `git submodule init`
4. Update Submodules `git submodule update`
5. Create folders cache and templates_c, make sure they are writeable by your webserver
6. Get the database dump from [github.com/beansman](https://github.com/beansman/CCP-Static-Datadump-to-MongoDB)
7. Extract the zip file and run mongorestore <ExtractPath>
8. Make your apache point to public/ path

## USAGE
run king23 Kingboard for a list of avaiable commands,
like adding api keys and running an killmail import


## PS
i hope i did not forget anything.
have fun, hope it helps
