<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_extrafields.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_EXTRA_FIELDS');
echo $this->loadTemplate('jsm_notes');

$this->tips = array();

switch (Factory::getApplication()->input->getVar('view'))
{
case 'clubinfo':
$this->extrafields = sportsmanagementHelper::getUserExtraFields($this->club->id, 'frontend', sportsmanagementModelClubInfo::$cfg_which_database,Factory::getApplication()->input->get('view'));
break;
}


//echo '<pre>'.print_r($this->extrafields,true).'</pre>';

if (isset($this->extrafields))
{
    $ausgabe = '<table class="table">';
	foreach ($this->extrafields as $field)
	{
		$value      = $field->fvalue;
		$field_type = $field->field_type;


		if (!empty($value)) // && !$field->backendonly)
		{
          switch (Factory::getApplication()->input->getVar('view'))
					{
						case 'clubinfo':
							$title = $this->club->name;
							break;
					}
          
$ausgabe .= '<tr>';          
$ausgabe .= '<td>'.Text::_($field->name).'</td>';
switch ($field_type)
					{
						case 'link':
							$ausgabe .= '<td>'. HTMLHelper::_('link', $field->fvalue, $title, array("target" => "_blank")).'</td>';
							break;
						default:
							$ausgabe .= '<td>'. Text::_($field->fvalue).'</td>';
							break;
					}          
        
$ausgabe .= '</tr>';          
          switch ( strtolower(Text::_($field->name)) )
    {
      case 'wikipedia':
       
       
        $ausgabe .= '<tr><td>';   
         ?>
           <div class="row">

   <div class="col-lg-12 col-md-12 col-sm-12">
          
  <iframe class="col-lg-12 col-md-12 col-sm-12" style="height: 400px;" src="<?php echo $field->fvalue; ?>" ></iframe>

            </div>

</div>
      <?php  
        $ausgabe .= '</td></tr>'; 
        break;
    }    
			
		}
	}
$ausgabe .= '</table>';
$this->tips[] = $ausgabe;    
}


echo $this->loadTemplate('jsm_tips');
