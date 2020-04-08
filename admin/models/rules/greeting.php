<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rules
 * @file       superiorzero.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormRule;

/**
 * Form Rule class for the Joomla Framework.
 */
class JFormRuleGreeting extends FormRule
{
	/**
	 * The regular expression.
	 *
	 * @access protected
	 * @var    string
	 * @since  1.6
	 */
	protected $regex = '^[^0-9]+$';
}
