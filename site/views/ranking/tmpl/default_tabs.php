<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_tabs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
  
echo JHtml::_('bootstrap.startTabSet', 'defaulttabs', array('active' => 'start')); //start tab set
echo JHtml::_('bootstrap.addTab', 'defaulttabs', 'start', JText::_('COM_EXAMPLE_NAME'));
echo $this->loadTemplate('ranking');
echo JHtml::_('bootstrap.endTab');


echo JHtml::_('bootstrap.endTabSet'); //end tab set  
?>
