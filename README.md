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
- MongoDB
- King23
- Pheal

## INSTALLATION
1. clone it from github git://github.com/ppetermann/Kingboard.git
2. symlink lib/King23 to a King23 installation (see King23s readme for more information), also ensure you have the king23 script in path
3. symlink lib/Pheal to a Pheal installation (see Pheal docs for that)
4. Alternative to 2&3, clone them their with the respective names
5. create folders cache and templates_c, make sure they are writeable by your webserver
6. get a sqlite eve database dump
7. run king23 EveImport:items path/to/sqlitefile
8. run king23 EveImport:solarsystems path/to/sqlitefile
9. make your apache point to public/ path

## USAGE
run king23 Kingboard for a list of avaiable commands,
like adding api keys and running an killmail import


## PS
i hope i did not forget anything.
have fun, hope it helps
