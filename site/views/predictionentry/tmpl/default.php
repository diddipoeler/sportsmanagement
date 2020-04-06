<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionentry
 */

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="row-fluid">
<?php

    echo $this->loadTemplate('predictionheading');
    echo $this->loadTemplate('sectionheader');

if (( !isset($this->actJoomlaUser) ) || ( $this->actJoomlaUser->id == 0 ) ) {
    echo $this->loadTemplate('view_deny');
}
else
{
    if (( !$this->isPredictionMember ) && ( !$this->allowedAdmin ) ) {
        echo $this->loadTemplate('view_not_member');
    }
    else
    {
        if ($this->isNewMember) {
                echo $this->loadTemplate('view_welcome');
        }

        if (!$this->tippEntryDone) {
            if (($this->config['show_help']==0)||($this->config['show_help']==2)) {
                    echo $this->model->createHelptText($predictionProject->mode);
            }
                echo $this->loadTemplate('view_tippentry_do');
            if (($this->config['show_help']==1)||($this->config['show_help']==2)) {
                echo $this->model->createHelptText($predictionProject->mode);
            }
          
        }
        else
        {
            echo $this->loadTemplate('view_tippentry_done');
        }
    }
}
    echo $this->loadTemplate('jsminfo');
?>
</div>
