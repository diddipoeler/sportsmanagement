<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextindividualsportes
 * @file       default_generate.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * No, they do not have a position for the season because each matchday the team captain decide the position of the players (at the match beginning). 
 * Day1 a player may be at A position (A, B, C and D are home positions and W X Y and Z are away positions) and Day 2 B or C or D ...
 
  When we are in the screen to enter the individual match is it possible to :
- automaticly generate all individual match for the game ? Exemple for Espoirs :
  A or C against W (depending if the team is composed of 2 or 3 players)
B or C against X (depending if the team is composed of 2 or 3 players)
Double against Double
A against X or Y (depending if the team is composed of 2 or 3 players)
B against W or Y (depending if the team is composed of 2 or 3 players)
 
For Classement par equipes : 
C against Y
B against X
A against Y or Z (depending if the team is composed of 3 or 4 players)
C or D against W (depending if the team is composed of 3 or 4 players)
A against X
B against W
C against X or Z (depending if the team is composed of 3 or 4 players)
B or D against Y (depending if the team is composed of 3 or 4 players)
A against W
Double against Double

 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;


echo 'homeplayers<pre>'.print_r($this_homeplayers,true).'</pre>';

echo 'awayplayers<pre>'.print_r($this_awayplayers,true).'</pre>';

?>
