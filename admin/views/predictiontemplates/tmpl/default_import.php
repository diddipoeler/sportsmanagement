<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_import.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictiontemplates
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<script type="text/javascript">
<!--
window.addEvent('domready', function()
{
    $('templateid').addEvent('change', function()
    {
        if (this.value)
        {
            $('importform').submit();
        }
    });
});
//-->
</script>
<div id="masterimport">
    <form method="post" name="importform" id="importform">
        <p>
    <?php
    echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_INHERITS_SETTINGS',
        $this->predictiongame->name
    );
    ?>
        </p>
        <p>
    <?php
    echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_OVERRIDES_SETTINGS');
    ?>
        </p>
    <?php
    echo $this->lists['mastertemplates'];
    ?>
        <!--
        <input type='hidden' name='project_id'    value='<?php echo $this->projectws->id; ?>' />
        -->
        <input type='hidden' name='controller'    value='predictiontemplate' />
        <input type='hidden' name='task'         value='masterimport' />
    <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
