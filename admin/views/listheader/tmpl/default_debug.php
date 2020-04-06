<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_debug.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage listheader
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

<div id='editcell'>
<?PHP

if(version_compare(JVERSION, '3.0.0', 'ge')) {
    // Define slides options
        $slidesOptions = array(
            "active" => "slide2_id" // It is the ID of the active tab.
        );  
    echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id1', $slidesOptions);
    echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id1', Text::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info');

    echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id2', $slidesOptions);
    $array_schluessel = array_keys(sportsmanagementHelper::$_success_text);


    for($a=0; $a < sizeof($array_schluessel); $a++ )
    {
        echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id2', Text::_($array_schluessel[$a]), 'debug_info_text');
        foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
        {
            ?>
            <fieldset>
            <legend><?php echo Text::_($row->methode); ?></legend>
    <table class='adminlist' width="100%"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
    </fieldset>
    <?php
        }
        echo HTMLHelper::_('bootstrap.endSlide');        
    }
    echo HTMLHelper::_('bootstrap.endAccordion');

    echo HTMLHelper::_('bootstrap.endSlide');
    echo HTMLHelper::_('bootstrap.endAccordion');
}
else
{ 
    $options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 1,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
    );  
    echo HTMLHelper::_('sliders.start', 'debug_info', $options); 
    echo HTMLHelper::_('sliders.panel', Text::_('COM_SPORTSMANAGEMENT_DEBUG_INFO'), 'debug_info');
    $array_schluessel = array_keys(sportsmanagementHelper::$_success_text);

    echo HTMLHelper::_('sliders.start', 'debug_info_text', $options);
    for($a=0; $a < sizeof($array_schluessel); $a++ )
    {
        echo HTMLHelper::_('sliders.panel', Text::_($array_schluessel[$a]), 'debug_info_text');
        foreach (sportsmanagementHelper::$_success_text[$array_schluessel[$a] ] as $row)
        {
            ?>
               <fieldset>
            <legend><?php echo Text::_($row->methode); ?></legend>
        <table class='adminlist' width="100%"><tr><td><?php echo $row->line; ?></td><td><?php echo $row->text; ?></td></tr></table>
       </fieldset>
        <?php
        }        
    }
    echo HTMLHelper::_('sliders.end');
    echo HTMLHelper::_('sliders.end');
}




?>
</div>
