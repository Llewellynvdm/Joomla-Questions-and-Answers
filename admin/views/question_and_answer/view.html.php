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

	@version		1.1.x
	@build			27th May, 2022
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		view.html.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Question_and_answer Html View class
 */
class QuestionsanswersViewQuestion_and_answer extends HtmlView
{
	/**
	 * display method of View
	 * @return void
	 */
	public function display($tpl = null)
	{
		// set params
		$this->params = JComponentHelper::getParams('com_questionsanswers');
		// Assign the variables
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');
		$this->state = $this->get('State');
		// get action permissions
		$this->canDo = QuestionsanswersHelper::getActions('question_and_answer', $this->item);
		// get input
		$jinput = JFactory::getApplication()->input;
		$this->ref = $jinput->get('ref', 0, 'word');
		$this->refid = $jinput->get('refid', 0, 'int');
		$return = $jinput->get('return', null, 'base64');
		// set the referral string
		$this->referral = '';
		if ($this->refid && $this->ref)
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string)$this->ref . '&refid=' . (int)$this->refid;
		}
		elseif($this->ref)
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string)$this->ref;
		}
		// check return value
		if (!is_null($return))
		{
			// add the return value
			$this->referral .= '&return=' . (string)$return;
		}

		// Set the toolbar
		$this->addToolBar();
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}


	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_NEW' : 'COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (QuestionsanswersHelper::checkString($this->referral))
		{
			if ($this->canDo->get('question_and_answer.create') && $isNew)
			{
				// We can create the record.
				JToolBarHelper::save('question_and_answer.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('question_and_answer.edit'))
			{
				// We can save the record.
				JToolBarHelper::save('question_and_answer.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				JToolBarHelper::cancel('question_and_answer.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				JToolBarHelper::cancel('question_and_answer.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('question_and_answer.create'))
				{
					JToolBarHelper::apply('question_and_answer.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('question_and_answer.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::custom('question_and_answer.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				JToolBarHelper::cancel('question_and_answer.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('question_and_answer.edit'))
				{
					// We can save the new record
					JToolBarHelper::apply('question_and_answer.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('question_and_answer.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('question_and_answer.create'))
					{
						JToolBarHelper::custom('question_and_answer.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('question_and_answer.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('question_and_answer.edit') && $canVersion)
				{
					JToolbarHelper::versions('com_questionsanswers.question_and_answer', $this->item->id);
				}
				if ($this->canDo->get('question_and_answer.create'))
				{
					JToolBarHelper::custom('question_and_answer.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				JToolBarHelper::cancel('question_and_answer.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		JToolbarHelper::divider();
		// set help url for this view if found
		$this->help_url = QuestionsanswersHelper::getHelpUrl('question_and_answer');
		if (QuestionsanswersHelper::checkString($this->help_url))
		{
			JToolbarHelper::help('COM_QUESTIONSANSWERS_HELP_MANAGER', false, $this->help_url);
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if(strlen($var) > 30)
		{
    		// use the helper htmlEscape method instead and shorten the string
			return QuestionsanswersHelper::htmlEscape($var, $this->_charset, true, 30);
		}
		// use the helper htmlEscape method instead.
		return QuestionsanswersHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = ($this->item->id < 1);
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_($isNew ? 'COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_NEW' : 'COM_QUESTIONSANSWERS_QUESTION_AND_ANSWER_EDIT'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_questionsanswers/assets/css/question_and_answer.css", (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// Add Ajax Token
		$this->document->addScriptDeclaration("var token = '".JSession::getFormToken()."';");
		$this->document->addScript(JURI::root() . $this->script, (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		$this->document->addScript(JURI::root() . "administrator/components/com_questionsanswers/views/question_and_answer/submitbutton.js", (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript'); 
		// add var key
		$this->document->addScriptDeclaration("var vastDevMod = '".$this->get('VDM')."';"); 
		// load the links on the page
		$this->document->addScriptDeclaration("var documentsLinks = " . json_encode($this->item->links) . ";");

		// add the style sheets
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/uikit.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/accordion.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/tooltip.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/notify.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/form-file.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/progress.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/placeholder.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addStyleSheet( JURI::root(true) .'/media/com_questionsanswers/uikit-v2//css/components/upload.gradient.min.css' , (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// add JavaScripts
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/uikit.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/accordion.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/tooltip.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/lightbox.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/notify.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript( JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/upload.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		JText::script('view not acceptable. Error');
	}
}
