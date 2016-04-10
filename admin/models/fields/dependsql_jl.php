<?php 

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

JHtml::_( 'behavior.framework' );


class JFormFieldDependSQL extends JFormFieldList
{
	/**
	 * field name
	 *
	 * @access protected
	 * @var string
	 */
	protected $type = 'dependsql';

	protected function getInput()
	{
		// elements
		//$required   = $this->element['required'] ? ' required aria-required="true"' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$key 		= ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val 		= ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		$task 		= $this->element['task'];
		$depends 	= $this->element['depends'];
		$ctrl 		= $this->name;
		
		// Attribs
		$attribs	 = ' task="'.$task.'"';
		$attribs	.= $required;
		if ($v = $this->element['size'])
		{
			$attribs .= ' size="'.$v.'"';
		}
		if ($depends)
		{
			$attribs	.= ' depends="'.$depends.'"';
		}
		$attribs	.= ' class="inputbox';
		// Optionally add "depend" to the class attribute
		if ($depends)
		{
			$attribs	.= ' depend"';
		}
		else
		{
			$attribs	.= '"';
		}
		$attribs	.= ' current="'.$this->value.'"';
		
	//	// language
//		$lang = JFactory::getLanguage();
//		$lang->load("com_joomleague", JPATH_ADMINISTRATOR);
		
		
		if ($required=='true') {
			$options = array();
		}
		else {
			// $options = array(JHtml::_('select.option', '', JText::_('Loading..'), $key, JText::_($val)));
			$options = array();
		}

		$query = $this->element['query'];
		if ($query!='')
		{
			$db = JFactory::getDbo();
			$db->setQuery($query);
			$options = array_merge($options, $db->loadObjectList());
		}
		
		if ($depends)
		{
			$doc = JFactory::getDocument();
			$doc->addScript(JUri::base() . 'components/com_sportsmanagement/assets/js/depend.js' );
		}

		return JHtml::_('select.genericlist',  $options, $this->name, trim($attribs), $key, $val, $this->value, $this->id);
		
	}
}
