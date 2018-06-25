<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_getdivision.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextdfbkeyimport
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_( 'behavior.tooltip' );

?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<?php
echo '<br>'.JHtml::_('select.genericlist',
					$this->lists['divisions'],
					'divisionid',
					'class="inputbox" size="1" onchange=""',
					'value','text', $this->division);

?>
<input type="hidden" name="sent"			value="1" />
<input type="hidden" name="task"			value="" />
                			
</form>