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

$css      = Uri::base() . 'modules/mod_sportsmanagement_rquotes/assets/rquote.css';
$document = Factory::getDocument();
$document->addStyleSheet($css);

echo '<span class="mod_rquote_quote_text_file">' . $rows[$num] . '</span>';


// Echo( $rows[$num]);

