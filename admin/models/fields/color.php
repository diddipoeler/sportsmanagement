<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       color.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

/**
 * FormFieldColor
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldColor extends \JFormFieldText
{
    protected $type = 'Color';

    /**
     * FormFieldColor::getInput()
     *
     * @return
     */
    public function getInput()
    {
        $document = Factory::getDocument();
        $document->addScript(Uri::base(). 'components/com_gcalendar/libraries/jscolor/jscolor.js');
        return parent::getInput();
    }

    /**
     * FormFieldColor::setup()
     *
     * @param  mixed $element
     * @param  mixed $value
     * @param  mixed $group
     * @return
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return= parent::setup($element, $value, $group);
        $this->element['class'] = $this->element['class'].' color';
        return $return;
    }
}
?>
