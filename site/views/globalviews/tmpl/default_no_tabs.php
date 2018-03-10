<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_no_tabs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */ 
defined('_JEXEC') or die('Restricted access');
?>
<div class="row-fluid" id="no_tabs">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<?php
$view = JFactory::getApplication()->input->getCmd('view');

foreach ($this->output as $key => $templ) {
    switch ($view) {
        case 'player':
            $template = $templ['template'];
            $text = $templ['text'];
            break;
        default:
            $template = $templ;
            $text = $key;
            break;
    }

    echo $this->loadTemplate($template);
}
?>
    </div> 
</div>