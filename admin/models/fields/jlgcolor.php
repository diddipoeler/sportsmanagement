<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlgcolor.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

/**
 * FormFieldJLGColor
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class JFormFieldJLGColor extends FormField
{
    /**
     * The form field type.
     *
     * @var   string
     * @since 11.3
     */
    protected $type = 'JLGColor';

    
    /**
     * FormFieldJLGColor::getInput()
     * 
     * @return
     */
    protected function getInput()
    {
        $app = Factory::getApplication();
        $document = Factory::getDocument();
        $document->addScript(Uri::base().'components/com_sportsmanagement/assets/js/301a.js');
        
        // Initialize some field attributes.
        $size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
        $classes = (string) $this->element['class'];
        $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        $class = $classes ? ' class="' . trim($classes) . '"' : '';
        
        $document->addScriptDeclaration(
            "
				window.addEvent('domready', function() {
					$$('.pickerbutton').addEvent('click', function(){
						var field = this.id.substr(12);
						showColorGrid3(field,'sample_'+field);
						return overlib(document.id('colorpicker301').innerHTML, 
						               CAPTION,'Farbe:', BELOW, RIGHT,FOLLOWMOUSE=false, 
						               CLOSEBTN=true, CLOSEBTNCOLORS=['white', 'white', 'white', 'white'], 
				                   STICKY=false);
					});
					document.id('sample_".$this->id."').style.backgroundColor = document.id('".$this->id."').value;
				});
		"
        );
        
        $html = array();        
        $html[] = '<input class="inputbox" type="text" id="sample_'.$this->id.'" size="1" value="">&nbsp;';        
        $html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
         . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . '/>';
        $html[] = '<input	type="button" id="buttonpicker'.$this->id.'" class="inputbox pickerbutton" value="..."/>';
        $html[] = '<div	id="colorpicker301" class="colorpicker301" style="position:absolute;top:0px;left:0px;z-index:1000;display:none;"></div>';

        return implode("\n", $html);
    }
}
