<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       associationslist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * FormFieldAssociationsList
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldAssociationsList extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'AssociationsList';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$app      = Factory::getApplication();
		$option   = Factory::getApplication()->input->getCmd('option');
        $view   = Factory::getApplication()->input->getCmd('view');
        $db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$selected = 0;
        $country = '';
		$options   = array();
		$vartable  = (string) $this->element['targettable'];
		$select_id = Factory::getApplication()->input->getVar('id');
        $post = Factory::getApplication()->input->post->getArray();
        
        //Factory::getApplication()->enqueueMessage('<pre>'.print_r($view,true)      .'</pre>', 'error');
        //Factory::getApplication()->enqueueMessage('<pre>'.print_r($post,true)      .'</pre>', 'error');

		if (is_array($select_id))
		{
			$select_id = $select_id[0];
		}

switch ($view)
{
    case 'leagues':
    $country = $post['filter']['search_nation'];
    if ( $country )
    {
        $query->clear();
    $query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country = '" . $country . "'");
			$query->where('t.parent_id = 0');
			$query->order('t.name');
	    try{
			$db->setQuery($query);
			$options = $db->loadObjectList();    
         }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
    }
    
    break;
	case 'projects':
    $country = $post['filter']['search_nation'];
    if ( $country )
    {
        $query->clear();
    $query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country = '" . $country . "'");
			//$query->where('t.parent_id = 0');
			$query->order('t.name');
	    try{
			$db->setQuery($query);
			$options = $db->loadObjectList();    
         }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
    }
    
    break;
    default:
    if ($select_id)
		{
			
            $query->clear();
			$query->select('country');
			$query->from('#__sportsmanagement_' . $vartable . ' AS t');
			$query->where('t.id = ' . $select_id);
	    try{
			$db->setQuery($query);
			$country = $db->loadResult();
 }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
		
			$query->clear();

			$query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country = '" . $country . "'");
			$query->where('t.parent_id = 0');
			$query->order('t.name');
	    try{
			$db->setQuery($query);
			$sections = $db->loadObjectList();
 }
		catch (Exception $e)
		{
	$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
   $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');

		}
		
			$categoryparent = empty($sections) ? 0 : $sections[0]->value;

			$list = $this->JJ_categoryArray(0, $country);

			$preoptions = array();
			$name       = 'parent_id';

			foreach ($list as $item)
			{
				if (!$preoptions && !$selected && ($sections || !$item->section))
				{
					$selected = $item->id;
				}

				$options [] = HTMLHelper::_('select.option', $item->id, $item->treename, 'value', 'text', !$sections && $item->section);
			}
		}
    break;
    
}
		

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


	/**
	 * JFormFieldAssociationsList::JJ_categoryArray()
	 * 
	 * @param integer $admin
	 * @param string $country
	 * @return
	 */
	function JJ_categoryArray($admin = 0, $country = '')
	{
		$db = sportsmanagementHelper::getDBConnection();
        $query     = $db->getQuery(true);
        $query->clear();
        
        $query->select('*');
        $query->from('#__sportsmanagement_associations');
        $query->where('country LIKE ' . $db->Quote('' . $country . ''));
        $query->order('ordering, name');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		/** Establish the hierarchy of the menu */
		$children = array();

		/** First pass - collect children */
		foreach ($items as $v)
		{
			$pt   = $v->parent_id;
			$list = isset($children[$pt]) ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		/** Second pass - get an indent list of the items */
		$array = $this->fbTreeRecurse(0, '', array(), $children, 10, 0, 1);

		return $array;
	}

	/**
	 * FormFieldAssociationsList::fbTreeRecurse()
	 *
	 * @param   mixed    $id
	 * @param   mixed    $indent
	 * @param   mixed    $list
	 * @param   mixed    $children
	 * @param   integer  $maxlevel
	 * @param   integer  $level
	 * @param   integer  $type
	 *
	 * @return
	 */
	function fbTreeRecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{

		if (isset($children[$id]) && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ($type)
				{
					$pre    = '&nbsp;';
					$spacer = '...';
				}
				else
				{
					$pre    = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $this->sm_htmlspecialchars($v->name);
				}
				else
				{
					$txt = $pre . $this->sm_htmlspecialchars($v->name);
				}

				$pt                  = $v->parent_id;
				$list[$id]           = $v;
				$list[$id]->treename = $indent . $txt;
				$list[$id]->children = !empty($children[$id]) ? count($children[$id]) : 0;
				$list[$id]->section  = ($v->parent_id == 0);

				$list = $this->fbTreeRecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}

		return $list;
	}

	/**
	 * FormFieldAssociationsList::sm_htmlspecialchars()
	 *
	 * @param   mixed   $string
	 * @param   mixed   $quote_style
	 * @param   string  $charset
	 *
	 * @return
	 */
	function sm_htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'UTF-8')
	{
		return htmlspecialchars($string, $quote_style, $charset);
	}

}
