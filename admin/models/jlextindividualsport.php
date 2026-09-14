<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator individual-sport form model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportModel;

if (!class_exists('sportsmanagementModeljlextindividualsport', false)) {
    class_alias(JlextindividualsportModel::class, 'sportsmanagementModeljlextindividualsport');
}
