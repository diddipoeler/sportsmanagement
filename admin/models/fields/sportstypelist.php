<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       sportstypelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
FormHelper::loadFieldClass('list');



if (!class_exists('sportsmanagementHelper'))
{
    // Add the classes for handling
    $classpath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_sportsmanagement' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
}

/**
 * FormFieldsportstypelist
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldsportstypelist extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'sportstypelist';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     *
     * @since 11.1
     */
    protected function getInput()
    {
        // Initialize variables.
      $script = array();
        $options = array();
        $lang    = Factory::getLanguage();
        $db      = sportsmanagementHelper::getDBConnection(false, false);
        $query   = $db->getQuery(true);

        $query->select('id AS value, name AS text, icon as itempicture');
        $query->from('#__sportsmanagement_sports_type');
        $query->order('name');
        $db->setQuery($query);
        $options = $db->loadObjectList();
/**
        $extension = "COM_SPORTSMANAGEMENT";
        $source    = JPATH_ADMINISTRATOR . '/components/' . $extension;
        $lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
        || $lang->load($extension, $source, null, false, false)
        || $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
        || $lang->load($extension, $source, $lang->getDefault(), false, false);
*/

      $script[] = 'var '.$this->fieldname.' = new Array;';

        foreach ($options as $row)
        {
            $row->text = Text::_($row->text);
        }
         foreach ($options as $key => $value)
			{
				if (!$value->itempicture)
				{
					$value->itempicture = sportsmanagementHelper::getDefaultPlaceholder("ph_logo_small");
				}

				$script[] = $this->fieldname.'[' . ($key) . ']=\'' . $value->itempicture . "';";
			}
           Factory::getDocument()->addScriptDeclaration(implode("\n", $script));   



      Factory::getDocument()->addStyleDeclaration(
            '
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
        );

// String $opt - second parameter of formbehavior2::select2
        // for details http://ivaynberg.github.io/select2/
        $opt = ' allowClear: true,
   width: "100%",
   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = '.$this->fieldname.'[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },

   escapeMarkup: function(m) { return m; }
';

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

$append = 'onchange="this.form.submit();"';
        $html = array();

if (version_compare( substr(JVERSION, 0, 3), '5.0', 'ge'))
{
$html[] = HTMLHelper::_('formbehavior.chosen', '.'.$this->fieldname, $opt);
}
else
{
$html[] = HTMLHelper::_('formbehavior2.select2', '.'.$this->fieldname, $opt);
}

    $html[] = HTMLHelper::_(
                'select.genericlist', $options, $this->name,
                'style="width:225px;" class="'.$this->fieldname.'  " size="1"' . $append, 'value', 'text', $this->value
            );



    return implode("\n", $html);






        //return $options;
    }
}
