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
	@build			5th May, 2018
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		questions_and_answers.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	Questions &amp; Answers 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * Questions_and_answers Controller
 */
class QuestionsanswersControllerQuestions_and_answers extends JControllerAdmin
{
	protected $text_prefix = 'COM_QUESTIONSANSWERS_QUESTIONS_AND_ANSWERS';
	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Question_and_answer', $prefix = 'QuestionsanswersModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		
		return $model;
	}

	public function exportData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('question_and_answer.export', 'com_questionsanswers') && $user->authorise('core.export', 'com_questionsanswers'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			JArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Questions_and_answers');
			// get the data to export
			$data = $model->getExportData($pks);
			if (QuestionsanswersHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				QuestionsanswersHelper::xls($data,'Questions_and_answers_'.$date->format('jS_F_Y'),'Questions and answers exported ('.$date->format('jS F, Y').')','questions and answers');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_QUESTIONSANSWERS_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_questionsanswers&view=questions_and_answers', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('question_and_answer.import', 'com_questionsanswers') && $user->authorise('core.import', 'com_questionsanswers'))
		{
			// Get the import model
			$model = $this->getModel('Questions_and_answers');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (QuestionsanswersHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('question_and_answer_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'questions_and_answers');
				$session->set('dataType_VDM_IMPORTINTO', 'question_and_answer');
				// Redirect to import view.
				$message = JText::_('COM_QUESTIONSANSWERS_IMPORT_SELECT_FILE_FOR_QUESTIONS_AND_ANSWERS');
				$this->setRedirect(JRoute::_('index.php?option=com_questionsanswers&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_QUESTIONSANSWERS_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_questionsanswers&view=questions_and_answers', false), $message, 'error');
		return;
	}  
}
