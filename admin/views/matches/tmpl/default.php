<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matches
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$massadd=JFactory::getApplication()->input->getInt('massadd',0);
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

//echo 'selectlist <br><pre>'.print_r($this->selectlist,true).'</pre>';

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
?>

<div id="alt_decision_enter" style="display:<?php echo ($massadd == 0) ? 'none' : 'block'; ?>">
<?php 
echo $this->loadTemplate('massadd'); 
?>
</div>
<?php 
echo $this->loadTemplate('matches'); 
?>	
<?php 
if ( JComponentHelper::getParams($this->option)->get('show_edit_matches_matrix') )
{
echo $this->loadTemplate('matrix'); 
}
?>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>   
</div>
