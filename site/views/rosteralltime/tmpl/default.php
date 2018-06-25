<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rosteralltime
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);


//echo 'all time player<pre>'.print_r($this->rows,true).'</pre><br>';
//echo 'all time player config<pre>'.print_r($this->config,true).'</pre><br>';
//echo 'all time player playerposition<pre>'.print_r($this->playerposition,true).'</pre><br>';
//echo 'all time player positioneventtypes<pre>'.print_r($this->positioneventtypes,true).'</pre><br>';
?>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<?PHP
//echo $this->loadTemplate('players_new');
echo $this->loadTemplate('players');

	echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
	?>
</div>
