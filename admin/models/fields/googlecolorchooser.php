<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      googlecolorchooser.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;

class JFormFieldGoogleColorChooser extends \FormFieldText
{
	protected $type = 'GoogleColorChooser';

	private $googleColors = array(
	'A32929'
	,'B1365F'
	,'7A367A'
	,'5229A3'
	,'29527A'
	,'2952A3'
	,'1B887A'
	,'28754E'
	,'0D7813'
	,'528800'
	,'88880E'
	,'AB8B00'
	,'BE6D00'
	,'B1440E'
	,'865A5A'
	,'705770'
	,'4E5D6C'
	,'5A6986'
	,'4A716C'
	,'6E6E41'
	,'8D6F47');

	public function getInput()
	{
		$document = Factory::getDocument();
		$document->addScript(Uri::base(). 'components/com_sportsmanagement/libraries/jscolor/jscolor.js' );

		$buffer = "<input type=\"text\" name=\"".$this->name."\" id=\"".$this->id."\" readonly=\"readonly\" class=\"inputbox\" \n";
		$buffer .= "size=\"100%\" value=\"".$this->value."\" style=\"background-color: ".jsmGCalendarUtil::getFadedColor($this->value)."\" />\n";
		$buffer .= "<br CLEAR=\"both\"/><label id=\"jform_colors-lbl\" title=\"\" for=\"jform_color\"></label><table><tbody>\n";
		for ($i = 0; $i < count($this->googleColors); $i++) {
			if($i % 7 == 0)
			$buffer .= "<tr>\n";
			$c = $this->googleColors[$i];
			$cFaded = jsmGCalendarUtil::getFadedColor($c);
			$buffer .= "<td onmouseover=\"this.style.cursor='pointer'\" onclick=\"document.getElementById('".$this->id."').style.backgroundColor = '".$cFaded."';document.getElementById('".$this->id."').value = '".$c."';\" style=\"background-color: ".$cFaded.";width: 20px;\"></td><td>".$c."</td>\n";
			if($i % 7 == 6)
			$buffer .= "</tr>\n";
		}
		$buffer .="</tbody></table>\n";

		return $buffer;
	}
}
?>
