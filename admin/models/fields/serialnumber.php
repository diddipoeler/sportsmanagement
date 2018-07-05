
<?php


// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldserialnumber extends JFormField {
		
	public $type = 'serialnumber';

	/**
	 * Method to get the field options.
	 */
	protected function getLabel() {
		
		$html = '';
		
		
		
		return $html;
	}

	/**
	 * Method to get the field input markup.
	 */
	protected function getInput() {
		
		$title = trim($this->element['title']);
		$image_src = $this->element['imagesrc'];
		$text = trim($this->element['text']);
		$link = $this->element['link'];
		
		$titleintext = false;
		if ($this->element['titleintext']) {
			$titleintext = ($this->element['titleintext'] === 'true');
		}
		
		$html = '';
		
		

		return $html;
	}

}
?>
