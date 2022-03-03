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
	@build			2nd March, 2022
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		allowedimageformats.php
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
 * Allowedimageformats Form Field class for the Questionsanswers component
 */
class JFormFieldAllowedimageformats extends JFormFieldList
{
	/**
	 * The allowedimageformats field type.
	 *
	 * @var		string
	 */
	public $type = 'allowedimageformats';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		
		// check if helper class already is set
		if (!class_exists('QuestionsanswersHelper'))
		{
			// set the correct path focus
			$focus = JPATH_ADMINISTRATOR;
			// check if we are in the site area
			if (JFactory::getApplication()->isSite())
			{
				// set admin path
				$adminPath = $focus . '/components/com_questionsanswers/helpers/questionsanswers.php';
				// change the focus
				$focus = JPATH_ROOT;
			}
			// set path based on focus
			$path = $focus . '/components/com_questionsanswers/helpers/questionsanswers.php';
			// check if file exist, if not try admin again.
			if (file_exists($path))
			{
				// make sure to load the helper
				JLoader::register('QuestionsanswersHelper', $path);
			}
			// fallback option
			elseif (isset($adminPath) && file_exists($adminPath))
			{
				// make sure to load the helper
				JLoader::register('QuestionsanswersHelper', $adminPath);
			}
			else
			{
				// could not find this
				return false;
			}
		}
		// Start the options array
		$options = array();
		// Get the extensions list.
		$extensionList = QuestionsanswersHelper::getFileExtensions('image', true);
		if (QuestionsanswersHelper::checkArray($extensionList))
		{
			foreach($extensionList as $type => $extensions)
			{
				foreach($extensions as $extension)
				{
					$options[] = JHtml::_('select.option', $extension, $extension . ' [ ' . $type . ' ]');
				}
			}
		}
		return $options;
	}
}
