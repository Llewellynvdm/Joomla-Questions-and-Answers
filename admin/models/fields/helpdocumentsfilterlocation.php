<?php
/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |
                                                        |_|
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		1.0.x
	@build			8th February, 2021
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		helpdocumentsfilterlocation.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Helpdocumentsfilterlocation Form Field class for the Questionsanswers component
 */
class JFormFieldHelpdocumentsfilterlocation extends JFormFieldList
{
	/**
	 * The helpdocumentsfilterlocation field type.
	 *
	 * @var		string
	 */
	public $type = 'helpdocumentsfilterlocation';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('location'));
		$query->from($db->quoteName('#__questionsanswers_help_document'));
		$query->order($db->quoteName('location') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$results = $db->loadColumn();
		$_filter = array();
		$_filter[] = JHtml::_('select.option', '', '- ' . JText::_('COM_QUESTIONSANSWERS_FILTER_SELECT_LOCATION') . ' -');

		if ($results)
		{
			// get help_documentsmodel
			$model = QuestionsanswersHelper::getModel('help_documents');
			$results = array_unique($results);
			foreach ($results as $location)
			{
				// Translate the location selection
				$text = $model->selectionTranslation($location,'location');
				// Now add the location and its text to the options array
				$_filter[] = JHtml::_('select.option', $location, JText::_($text));
			}
		}
		return $_filter;
	}
}
