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
class JFormRuleTime extends FormRule
{
    /**
     * The regular expression.
     *
     * @access protected
     * @var    string
     * @since  2.5
     */
    protected $regex = '^[0-9]{1,2}:[0-9]{1,2}$';
  
    public function test(SimpleXMLElement &$element, $value, $group = null, &$input = null, &$form = null)
    {
        if ($value == null or $value == '') {
            return true;
        }
        return parent::test($element, $value, $group, $input, $form);
    }
}
