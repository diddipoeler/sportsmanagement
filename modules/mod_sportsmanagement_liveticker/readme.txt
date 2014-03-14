Turtushout 
---------------------------------------------------------------------------
jquery based shoutbox for Joomla 1.5, feel free to modify any way you like if you like it )))
http://www.turtus.org.ua

Apr 12	0.12
----------------------------------------------------------------------
added:
1) logged user welcome message
2) guests messages mark

fixed:
sql insert bug, js fixes

Apr 01	0.11 version changes 
----------------------------------------------------------------------
added:
1) spambots protection, ip logging and antiflood (see advanced parameters in module properties, 
and dont forget to change secret salt)
2) additional display settings in module

and:
3) fixed warnings
4) turned on jQuery safemode

sql query to execute after upgrade:
ALTER TABLE `jos_turtushout` ADD `ip` VARCHAR( 255 ) NULL ;