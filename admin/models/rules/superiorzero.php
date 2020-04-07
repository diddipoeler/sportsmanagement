<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       superiorzero.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rules
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormRule;

/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleSuperiorzero extends FormRule
{
	/**
	 * The regular expression.
	 *
	 * @access protected
	 * @var    string
	 * @since  2.5
	 */
	protected $regex = '^[1-9][0-9]*$';
}
