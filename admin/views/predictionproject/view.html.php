<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionproject
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewpredictionproject
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewpredictionproject extends sportsmanagementView
{
    /**
     * sportsmanagementViewpredictionproject::init()
     *
     * @return
     */
    public function init()
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors));

            return false;
        }

        $this->item->name = '';
        $this->app->setUserState("$this->option.pid", $this->item->project_id);

        switch ($this->getLayout()) {
            case 'edit';
            case 'edit_3';
            case 'edit_4';
                $this->setLayout('edit_3');
                break;
        }
    }
}
