<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_debug.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>

<div id='editcell'>
<?PHP

if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	// Define slides options
		$slidesOptions = array(
			"active" => "slide2_id" // It is the ID of the active tab.
		);
	echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);
	echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info');

	foreach (sportsmanagementHelper::$_success_text as $key => $value)
	{
			?>
			   <fieldset>
			<legend><?php echo Text::_($key); ?></legend>
			<table class='adminlist'><tr><td><?php echo TVarDumper::dump($value, 10, true);?></td></tr></table>
			   </fieldset>
			<?php
	}

	echo HTMLHelper::_('bootstrap.endSlide');
	echo HTMLHelper::_('bootstrap.endAccordion');
}
else
{
?>


<div class="panel-group" id="accordion">
<?PHP
$array_schluessel = array_keys(sportsmanagementHelper::$_success_text);

for ($a = 0; $a < sizeof($array_schluessel); $a++)
	{
?>  
<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#accordion" href="#<?php echo Text::_($array_schluessel[$a]); ?>"><?php echo Text::_($array_schluessel[$a]); ?></a>
</h4>
</div>
<?PHP
foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
		{
?>
<div id="<?php echo Text::_($array_schluessel[$a]); ?>" class="panel-collapse collapse">
<div class="panel-body">
<table class="adminlist"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
</div>
</div>

<?php
}
?>
</div>

<?PHP
}
?>
</div>


<!--
<div class="panel-group" id="accordion">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">1. What is HTML?</a>
				</h4>
			</div>
			<div id="collapseOne" class="panel-collapse collapse in">
				<div class="panel-body">
					<p>HTML stands for HyperText Markup Language. HTML is the main markup language for describing the structure of Web pages. <a href="http://www.tutorialrepublic.com/html-tutorial/" target="_blank">Learn more.</a></p>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">2. What is Bootstrap?</a>
				</h4>
			</div>
			<div id="collapseTwo" class="panel-collapse collapse">
				<div class="panel-body">
					<p>Bootstrap is a powerful front-end framework for faster and easier web development. It is a collection of CSS and HTML conventions. <a href="http://www.tutorialrepublic.com/twitter-bootstrap-tutorial/" target="_blank">Learn more.</a></p>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">3. What is CSS?</a>
				</h4>
			</div>
			<div id="collapseThree" class="panel-collapse collapse">
				<div class="panel-body">
					<p>CSS stands for Cascading Style Sheet. CSS allows you to specify various style properties for a given HTML element such as colors, backgrounds, fonts etc. <a href="http://www.tutorialrepublic.com/css-tutorial/" target="_blank">Learn more.</a></p>
				</div>
			</div>
		</div>
	</div>
-->
<?PHP
}




?>
</div>
