<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rquotes
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

$css      = Uri::base() . 'modules/' . $module->module . '/assets/rquote.css';
$document = Factory::getDocument();
$document->addStyleSheet($css);

$quotemarks         = $params->get('quotemarks');
$showpicture        = $params->get('showpicture');
$cfg_which_database = $params->get('cfg_which_database');

if ($cfg_which_database)
{
	$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');
	DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
}
else
{
	DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', Uri::root());
}

if (!isset($rquote->person_picture))
{
	$rquote->person_picture = $rquote->picture;
}

if ($quotemarks == 0)
{
	echo '<strong>';
	echo '<p>';

	if ($showpicture)
	{
		// If ( sportsmanagementHelper::existPicture($rquote->person_picture) )
		// {

		echo '<img style="float: left;" src="' . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $rquote->person_picture . '" alt="' . $rquote->author . '" width="50" height="" />';

		// }
		// else
		// {
		//    echo '<img style="float: left;" src="'.$rquote->picture.'" alt="'.$rquote->author.'" width="50" height="" />';
		// }
	}

	echo $rquote->quote;
	echo '<div align="right">' . $rquote->author . '</div>';
	echo '</p>';
	echo '</strong>';
}

if ($quotemarks == 1)
{
	echo '<strong>';
	echo '<p>';

	if ($showpicture)
	{
		if (sportsmanagementHelper::existPicture($rquote->person_picture))
		{
			echo '<img style="float: left;" src="' . $rquote->person_picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
		else
		{
			echo '<img style="float: left;" src="' . $rquote->picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
	}

	$rquote->quote = strip_tags($rquote->quote, '<img><br><a>');
	echo '<div>' . ' " ' . $rquote->quote . ' "' . '</div>';
	echo '<div align="right">' . $rquote->author . '</div>';
	echo '</p>';
	echo '</strong>';
}

if ($quotemarks == 2)
{
	echo '<strong>';
	echo '<p>';

	if ($showpicture)
	{
		if (sportsmanagementHelper::existPicture($rquote->person_picture))
		{
			echo '<img style="float: left;" src="' . $rquote->person_picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
		else
		{
			echo '<img style="float: left;" src="' . $rquote->picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
	}

	$rquote->quote = strip_tags($rquote->quote, '<img><br><a>');
	echo '<div>' . '<img src="modules/' . $module->module . '/assets/images/quote1_25_start.png" width="15" height="15"> ' . $rquote->quote . ' <img src="modules/' . $module->module . '/assets/images/quote1_25_end.png" width="15" height="15">' . '</div>';
	echo '<div align="right">' . $rquote->author . '</div>';
	echo '</p>';
	echo '</strong>';
}

if ($quotemarks == 3)
{
	echo '<strong>';
	echo '<p>';

	if ($showpicture)
	{
		if (sportsmanagementHelper::existPicture($rquote->person_picture))
		{
			echo '<img style="float: left;" src="' . $rquote->person_picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
		else
		{
			echo '<img style="float: left;" src="' . $rquote->picture . '" alt="' . $rquote->author . '" width="50" height="" />';
		}
	}

	$rquote->quote = strip_tags($rquote->quote, '<img><br><a>');
	echo '<div class="mod_rquote_css"><p><span>' . $rquote->quote . '</span></p></div>';

	//	echo '<div align="right">'.$rquote->author;
	echo '<div class="mod_rquote_author">' . $rquote->author . '</div>';
	echo '</p>';
	echo '</strong>';
}

