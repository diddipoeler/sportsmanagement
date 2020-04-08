<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_show_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>
<div class="<?php echo $this->divclassrow; ?>" id="show_tabs">
<?php

switch ($this->view)
{
	case 'player':
		foreach ($this->output as $key => $templ)
		{
			$this->outputnew[$templ['text']] = $templ['template'];
		}

		$this->output = $this->outputnew;
	break;
}


if (version_compare(JSM_JVERSION, '4', 'eq'))
{
	$idxTab = 0;
	echo HTMLHelper::_('bootstrap.startTabSet', 'myTab4', array('active' => 'name'));

	foreach ($this->output as $key => $templ)
	{
		$template = $templ;
		$text = $key;
		$active = ($idxTab == 0) ? HTMLHelper::_('bootstrap.startTabSet', 'myTab4', array('active' => $text)) : '';

		echo HTMLHelper::_('bootstrap.addTab', 'myTab4', $text, Text::_($text));
		?>
		<div class="<?php echo $this->divclasscontainer;?>">
		<div class="<?php echo $this->divclassrow; ?>">
		<?PHP
		echo $this->loadTemplate($template);
		?>
		</div>
		</div>
		<?PHP
		echo HTMLHelper::_('bootstrap.endTab');
		$idxTab++;
	}

	echo HTMLHelper::_('bootstrap.endTabSet');
}
elseif (version_compare(JSM_JVERSION, '3', 'eq'))
{
	// Joomla! 3.0 code here
	$idxTab = 0;
	echo HTMLHelper::_('bootstrap.startTabSet', 'myTab4', array('active' => 'name'));

	foreach ($this->output as $key => $templ)
	{
		$template = $templ;
		$text = $key;
		$active = ($idxTab == 0) ? HTMLHelper::_('bootstrap.startTabSet', 'myTab4', array('active' => $text)) : '';

		echo HTMLHelper::_('bootstrap.addTab', 'myTab4', $text, Text::_($text));
		?>
		<div class="<?php echo $this->divclasscontainer;?>">
		<div class="<?php echo $this->divclassrow; ?>">
		<?PHP
		if ($this->params->get('show_allranking', 0) && $this->view == 'resultsranking' && $template == 'ranking')
		{
			/**
 * sollen überhaupt reiter angezeigt werden ?
 */
			if ($this->config['show_table_1']
				|| $this->config['show_table_2']
				|| $this->config['show_table_3']
				|| $this->config['show_table_4']
				|| $this->config['show_table_5']
			)
			{
				echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsranking', array('active' => 'show_table_1' )); // Start tab set

				if ($this->config['show_table_1'])
				{
					echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_1', Text::_($this->config['table_text_1']));
					echo $this->loadTemplate('ranking');
					echo HTMLHelper::_('bootstrap.endTab');
				}


				if ($this->config['show_table_2'])
				{
							echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_2', Text::_($this->config['table_text_2']));
							echo $this->loadTemplate('ranking_home');
							echo HTMLHelper::_('bootstrap.endTab');
				}


				if ($this->config['show_table_3'])
				{
							echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_3', Text::_($this->config['table_text_3']));
							echo $this->loadTemplate('ranking_away');
							echo HTMLHelper::_('bootstrap.endTab');
				}


				if ($this->config['show_table_4'])
				{
							echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_4', Text::_($this->config['table_text_4']));
							echo $this->loadTemplate('ranking_first');
							echo HTMLHelper::_('bootstrap.endTab');
				}


				if ($this->config['show_table_5'])
				{
							echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', 'show_table_5', Text::_($this->config['table_text_5']));
							echo $this->loadTemplate('ranking_second');
							echo HTMLHelper::_('bootstrap.endTab');
				}

				echo HTMLHelper::_('bootstrap.endTabSet'); // End tab set
			}
		}
		else
		{
			echo $this->loadTemplate($template);
		}
		?>
		</div>
		</div>
		<?PHP
		echo HTMLHelper::_('bootstrap.endTab');
		$idxTab++;
	}

	echo HTMLHelper::_('bootstrap.endTabSet');
}
elseif (version_compare(JSM_JVERSION, '2', 'eq'))
{
	// Joomla! 2.5 code here
	$view = Factory::getApplication()->input->getCmd('view');
?>

<div class="panel with-nav-tabs panel-default">
<div class="panel-heading">

<!-- Tabs-Navs -->
<ul class="nav nav-tabs" >
<?PHP
$count = 0;

foreach ($this->output as $key => $templ)
	{
	$active = ($count == 0) ? 'active' : '';

	switch ($view)
		{
		case 'player':
			$template = $templ['template'];
			$text = $templ['text'];
		break;
		default:
			$template = $templ;
			$text = $key;
		break;
	}
?>
<li class="<?PHP echo $active; ?>"><a href="#<?PHP echo $template; ?>" data-toggle="tab"><?PHP echo Text::_($text); ?></a></li>
<?PHP
$count++;
}
?>
</ul>
</div>
<!-- Tab-Inhalte -->
<div class="panel-body">
<div class="tab-content">
<?PHP
$count = 0;

foreach ($this->output as $key => $templ)
	{
	$active = ($count == 0) ? 'in active' : '';

	switch ($view)
		{
		case 'player':
			$template = $templ['template'];
			$text = $templ['text'];
			break;
		default:
			$template = $templ;
			$text = $key;
			break;
	}
?>
<div class="tab-pane fade <?PHP echo $active; ?>" id="<?PHP echo $template; ?>">
<?PHP
switch ($template)
		{
	case 'previousx':
		$this->currentteam = $this->match->projectteam1_id;
		echo $this->loadTemplate($template);
		$this->currentteam = $this->match->projectteam2_id;
		echo $this->loadTemplate($template);
	break;
	default:
		echo $this->loadTemplate($template);
	break;
}
?>
</div>
<?PHP
$count++;
}
?>
</div>
</div>
</div>
<?PHP
}
elseif (version_compare(JVERSION, '1.7.0', 'ge'))
{
	// Joomla! 1.7 code here
}
elseif (version_compare(JVERSION, '1.6.0', 'ge'))
{
	// Joomla! 1.6 code here
}
else
{
	// Joomla! 1.5 code here
}
?>
</div>
