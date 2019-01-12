<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlxmlimport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.modal');

$model = $this->getModel('jlxmlimport');
echo $model->getXml;
?>