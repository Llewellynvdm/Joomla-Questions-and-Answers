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
	@subpackage		question_and_answer.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Questionsanswers Question_and_answer Admin Model
 */
class QuestionsanswersModelQuestion_and_answer extends AdminModel
{
	/**
	 * The tab layout fields array.
	 *
	 * @var      array
	 */
	protected $tabLayoutFields = array(
		'details' => array(
			'fullwidth' => array(
				'question',
				'main_image_uploader',
				'answer',
				'answer_documents_uploader'
			),
			'under' => array(
				'main_image',
				'answer_documents'
			),
			'rightside' => array(
				'catid'
			)
		)
	);

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_QUESTIONSANSWERS';

	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_questionsanswers.question_and_answer';

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'question_and_answer', $prefix = 'QuestionsanswersTable', $config = array())
	{
		// add table path for when model gets used from other component
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_questionsanswers/tables');
		// get instance of the table
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * get VDM internal session key
	 *
	 * @return  string  the session key
	 *
	 */
	public function getVDM()
	{
		if (!isset($this->vastDevMod))
		{
			$_id = 0; // new item probably (since it was not set in the getItem method)

			if (empty($_id))
			{
				$id = 0;
			}
			else
			{
				$id = $_id;
			}
			// set the id and view name to session
			if ($vdm = QuestionsanswersHelper::get('question_and_answer__'.$id))
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = QuestionsanswersHelper::randomkey(50);
				QuestionsanswersHelper::set($this->vastDevMod, 'question_and_answer__'.$id);
				QuestionsanswersHelper::set('question_and_answer__'.$id, $this->vastDevMod);
				// set a return value if found
				$jinput = JFactory::getApplication()->input;
				$return = $jinput->get('return', null, 'base64');
				QuestionsanswersHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && QuestionsanswersHelper::checkObject($item) && isset($item->guid)
					&& method_exists('QuestionsanswersHelper', 'validGUID')
					&& QuestionsanswersHelper::validGUID($item->guid))
				{
					QuestionsanswersHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}
		}
		return $this->vastDevMod;
	}

    
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			if (!empty($item->params) && !is_array($item->params))
			{
				// Convert the params field to an array.
				$registry = new Registry;
				$registry->loadString($item->params);
				$item->params = $registry->toArray();
			}

			if (!empty($item->metadata))
			{
				// Convert the metadata field to an array.
				$registry = new Registry;
				$registry->loadString($item->metadata);
				$item->metadata = $registry->toArray();
			}

			// Get the basic encryption.
			$basickey = QuestionsanswersHelper::getCryptKey('basic');
			// Get the encryption object.
			$basic = new FOFEncryptAes($basickey);

			if (!empty($item->answer_documents) && $basickey && !is_numeric($item->answer_documents) && $item->answer_documents === base64_encode(base64_decode($item->answer_documents, true)))
			{
				// basic decrypt data answer_documents.
				$item->answer_documents = rtrim($basic->decryptString($item->answer_documents), "\0");
			}

			if (!empty($item->main_image) && $basickey && !is_numeric($item->main_image) && $item->main_image === base64_encode(base64_decode($item->main_image, true)))
			{
				// basic decrypt data main_image.
				$item->main_image = rtrim($basic->decryptString($item->main_image), "\0");
			}


			if (empty($item->id))
			{
				$id = 0;
			}
			else
			{
				$id = $item->id;
			}
			// set the id and view name to session
			if ($vdm = QuestionsanswersHelper::get('question_and_answer__'.$id))
			{
				$this->vastDevMod = $vdm;
			}
			else
			{
				// set the vast development method key
				$this->vastDevMod = QuestionsanswersHelper::randomkey(50);
				QuestionsanswersHelper::set($this->vastDevMod, 'question_and_answer__'.$id);
				QuestionsanswersHelper::set('question_and_answer__'.$id, $this->vastDevMod);
				// set a return value if found
				$jinput = JFactory::getApplication()->input;
				$return = $jinput->get('return', null, 'base64');
				QuestionsanswersHelper::set($this->vastDevMod . '__return', $return);
				// set a GUID value if found
				if (isset($item) && QuestionsanswersHelper::checkObject($item) && isset($item->guid)
					&& method_exists('QuestionsanswersHelper', 'validGUID')
					&& QuestionsanswersHelper::validGUID($item->guid))
				{
					QuestionsanswersHelper::set($this->vastDevMod . '__guid', $item->guid);
				}
			}
			// build download links
			$item->links = array();	
			if (!empty($item->answer_documents) && QuestionsanswersHelper::checkJson($item->answer_documents) &&  !is_numeric($item->answer_documents))
			{
				$documents = json_decode($item->answer_documents);
				if (QuestionsanswersHelper::checkArray($documents))
				{
					// link options
					$linkoptions = QuestionsanswersHelper::getLinkOptions();
					// add session token
					$linkToken = '';
					if ($linkoptions['session'])
					{
						$linkToken = '&token=' . JSession::getFormToken();
					}
					// build links
					foreach($documents as $document)
					{
						if ($linkoptions['lock'] && $basickey)
						{
							// Get the encryption object.
							$localFile = QuestionsanswersHelper::base64_urlencode($basic->encryptString($document));
						}
						else
						{
							// can not get the encryption object so only base64 encode
							$localFile = QuestionsanswersHelper::base64_urlencode($document, true);
						}
						// load links
						$item->links[$document] = 'index.php?option=com_questionsanswers&task=download.document&file=' . $localFile . $linkToken;
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @param   array    $options   Optional array of options for the form creation.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true, $options = array('control' => 'jform'))
	{
		// set load data option
		$options['load_data'] = $loadData;
		// check if xpath was set in options
		$xpath = false;
		if (isset($options['xpath']))
		{
			$xpath = $options['xpath'];
			unset($options['xpath']);
		}
		// check if clear form was set in options
		$clear = false;
		if (isset($options['clear']))
		{
			$clear = $options['clear'];
			unset($options['clear']);
		}

		// Get the form.
		$form = $this->loadForm('com_questionsanswers.question_and_answer', 'question_and_answer', $options, $clear, $xpath);

		if (empty($form))
		{
			return false;
		}

		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0, 'INT');
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0, 'INT');
		}

		$user = JFactory::getUser();

		// Check for existing item.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.state', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.state', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		// If this is a new item insure the greated by is set.
		if (0 == $id)
		{
			// Set the created_by to this user
			$form->setValue('created_by', null, $user->id);
		}
		// Modify the form based on Edit Creaded By access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.created_by', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.created_by', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('created_by', 'readonly', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created_by', 'filter', 'unset');
		}
		// Modify the form based on Edit Creaded Date access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.created', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.created', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('created', 'disabled', 'true');
			// Disable fields while saving.
			$form->setFieldAttribute('created', 'filter', 'unset');
		}
		// Modify the form based on Edit Question access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.question', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.question', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('question', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('question', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('question'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('question', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('question', 'required', 'false');
			}
		}
		// Modify the form based on Edit Answer access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.answer', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.answer', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('answer', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('answer', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('answer'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('answer', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('answer', 'required', 'false');
			}
		}
		// Modify the form based on Edit Catid access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.catid', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.catid', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('catid', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('catid', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('catid'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('catid', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('catid', 'required', 'false');
			}
		}
		// Modify the form based on Edit Answer Documents access controls.
		if ($id != 0 && (!$user->authorise('question_and_answer.edit.answer_documents', 'com_questionsanswers.question_and_answer.' . (int) $id))
			|| ($id == 0 && !$user->authorise('question_and_answer.edit.answer_documents', 'com_questionsanswers')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('answer_documents', 'disabled', 'true');
			// Disable fields for display.
			$form->setFieldAttribute('answer_documents', 'readonly', 'true');
			// If there is no value continue.
			if (!$form->getValue('answer_documents'))
			{
				// Disable fields while saving.
				$form->setFieldAttribute('answer_documents', 'filter', 'unset');
				// Disable fields while saving.
				$form->setFieldAttribute('answer_documents', 'required', 'false');
			}
		}
		// Only load these values if no id is found
		if (0 == $id)
		{
			// Set redirected view name
			$redirectedView = $jinput->get('ref', null, 'STRING');
			// Set field name (or fall back to view name)
			$redirectedField = $jinput->get('field', $redirectedView, 'STRING');
			// Set redirected view id
			$redirectedId = $jinput->get('refid', 0, 'INT');
			// Set field id (or fall back to redirected view id)
			$redirectedValue = $jinput->get('field_id', $redirectedId, 'INT');
			if (0 != $redirectedValue && $redirectedField)
			{
				// Now set the local-redirected field default value
				$form->setValue($redirectedField, null, $redirectedValue);
			}
		}
		return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	script files
	 */
	public function getScript()
	{
		return 'media/com_questionsanswers/js/question_and_answer.js';
	}
    
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->published != -2)
			{
				return;
			}

			$user = JFactory::getUser();
			// The record has been set. Check the record permissions.
			return $user->authorise('question_and_answer.delete', 'com_questionsanswers.question_and_answer.' . (int) $record->id);
		}
		return false;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		$recordId = (!empty($record->id)) ? $record->id : 0;

		if ($recordId)
		{
			// The record has been set. Check the record permissions.
			$permission = $user->authorise('question_and_answer.edit.state', 'com_questionsanswers.question_and_answer.' . (int) $recordId);
			if (!$permission && !is_null($permission))
			{
				return false;
			}
		}
		// In the absense of better information, revert to the component permissions.
		return $user->authorise('question_and_answer.edit.state', 'com_questionsanswers');
	}
    
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	2.5
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		$user = JFactory::getUser();

		return $user->authorise('question_and_answer.edit', 'com_questionsanswers.question_and_answer.'. ((int) isset($data[$key]) ? $data[$key] : 0)) or $user->authorise('question_and_answer.edit',  'com_questionsanswers');
	}
    
	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		
		if (isset($table->name))
		{
			$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		}
		
		if (isset($table->alias) && empty($table->alias))
		{
			$table->generateAlias();
		}
		
		if (empty($table->id))
		{
			$table->created = $date->toSql();
			// set the user
			if ($table->created_by == 0 || empty($table->created_by))
			{
				$table->created_by = $user->id;
			}
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__questionsanswers_question_and_answer'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
		else
		{
			$table->modified = $date->toSql();
			$table->modified_by = $user->id;
		}
        
		if (!empty($table->id))
		{
			// Increment the items version number.
			$table->version++;
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_questionsanswers.edit.question_and_answer.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
			// run the perprocess of the data
			$this->preprocessData('com_questionsanswers.question_and_answer', $data);
		}

		return $data;
	}

	/**
	 * Method to get the unique fields of this table.
	 *
	 * @return  mixed  An array of field names, boolean false if none is set.
	 *
	 * @since   3.0
	 */
	protected function getUniqueFields()
	{
		return false;
	}
	
	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (!parent::delete($pks))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (!parent::publish($pks, $value))
		{
			return false;
		}
		
		return true;
        }
    
	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of item ids.
	 * @param   array  $contexts  An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize ids.
		$pks = array_unique($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		// Set some needed variables.
		$this->user			= JFactory::getUser();
		$this->table			= $this->getTable();
		$this->tableClassName		= get_class($this->table);
		$this->contentType		= new JUcmType;
		$this->type			= $this->contentType->getTypeByTable($this->tableClassName);
		$this->canDo			= QuestionsanswersHelper::getActions('question_and_answer');
		$this->batchSet			= true;

		if (!$this->canDo->get('core.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}
        
		if ($this->type == false)
		{
			$type = new JUcmType;
			$this->type = $type->getTypeByAlias($this->typeAlias);
		}

		$this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

		if (!empty($commands['move_copy']))
		{
			$cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c')
			{
				$result = $this->batchCopy($commands, $pks, $contexts);

				if (is_array($result))
				{
					foreach ($result as $old => $new)
					{
						$contexts[$new] = $contexts[$old];
					}
					$pks = array_values($result);
				}
				else
				{
					return false;
				}
			}
			elseif ($cmd == 'm' && !$this->batchMove($commands, $pks, $contexts))
			{
				return false;
			}

			$done = true;
		}

		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $values    The new values.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since 12.2
	 */
	protected function batchCopy($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user 		= JFactory::getUser();
			$this->table 		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= QuestionsanswersHelper::getActions('question_and_answer');
		}

		if (!$this->canDo->get('question_and_answer.create') && !$this->canDo->get('question_and_answer.batch'))
		{
			return false;
		}

		// get list of unique fields
		$uniqueFields = $this->getUniqueFields();
		// remove move_copy from array
		unset($values['move_copy']);

		// make sure published is set
		if (!isset($values['published']))
		{
			$values['published'] = 0;
		}
		elseif (isset($values['published']) && !$this->canDo->get('question_and_answer.edit.state'))
		{
				$values['published'] = 0;
		}

		if (isset($values['category']) && (int) $values['category'] > 0 && !static::checkCategoryId($values['category']))
		{
			return false;
		}
		elseif (isset($values['category']) && (int) $values['category'] > 0)
		{
			// move the category value to correct field name
			$values['catid'] = $values['category'];
			unset($values['category']);
		}
		elseif (isset($values['category']))
		{
			unset($values['category']);
		}

		$newIds = array();
		// Parent exists so let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// only allow copy if user may edit this item.
			if (!$this->user->authorise('question_and_answer.edit', $contexts[$pk]))
			{
				// Not fatal error
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
				continue;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// insert all set values
			if (QuestionsanswersHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					if (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}

			// update all unique fields
			if (QuestionsanswersHelper::checkArray($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$this->table->$uniqueField = $this->generateUnique($uniqueField,$this->table->$uniqueField);
				}
			}

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// TODO: Deal with ordering?
			// $this->table->ordering = 1;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch move items to a new category
	 *
	 * @param   integer  $value     The new category ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since 12.2
	 */
	protected function batchMove($values, $pks, $contexts)
	{
		if (empty($this->batchSet))
		{
			// Set some needed variables.
			$this->user		= JFactory::getUser();
			$this->table		= $this->getTable();
			$this->tableClassName	= get_class($this->table);
			$this->canDo		= QuestionsanswersHelper::getActions('question_and_answer');
		}

		if (!$this->canDo->get('question_and_answer.edit') && !$this->canDo->get('question_and_answer.batch'))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
			return false;
		}

		// make sure published only updates if user has the permission.
		if (isset($values['published']) && !$this->canDo->get('question_and_answer.edit.state'))
		{
			unset($values['published']);
		}
		// remove move_copy from array
		unset($values['move_copy']);

		if (isset($values['category']) && (int) $values['category'] > 0 && !static::checkCategoryId($values['category']))
		{
			return false;
		}
		elseif (isset($values['category']) && (int) $values['category'] > 0)
		{
			// move the category value to correct field name
			$values['catid'] = $values['category'];
			unset($values['category']);
		}
		elseif (isset($values['category']))
		{
			unset($values['category']);
		}


		// Parent exists so we proceed
		foreach ($pks as $pk)
		{
			if (!$this->user->authorise('question_and_answer.edit', $contexts[$pk]))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// insert all set values.
			if (QuestionsanswersHelper::checkArray($values))
			{
				foreach ($values as $key => $value)
				{
					// Do special action for access.
					if ('access' === $key && strlen($value) > 0)
					{
						$this->table->$key = $value;
					}
					elseif (strlen($value) > 0 && isset($this->table->$key))
					{
						$this->table->$key = $value;
					}
				}
			}


			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());

				return false;
			}

			if (!empty($this->type))
			{
				$this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
			}

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$input	= JFactory::getApplication()->input;
		$filter	= JFilterInput::getInstance();
        
		// set the metadata to the Item Data
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
            
			$metadata = new JRegistry;
			$metadata->loadArray($data['metadata']);
			$data['metadata'] = (string) $metadata;
		}

		// Get the basic encryption key.
		$basickey = QuestionsanswersHelper::getCryptKey('basic');
		// Get the encryption object
		$basic = new FOFEncryptAes($basickey);

		// Encrypt data answer_documents.
		if (isset($data['answer_documents']) && $basickey)
		{
			$data['answer_documents'] = $basic->encryptString($data['answer_documents']);
		}

		// Encrypt data main_image.
		if (isset($data['main_image']) && $basickey)
		{
			$data['main_image'] = $basic->encryptString($data['main_image']);
		}
        
		// Set the Params Items to data
		if (isset($data['params']) && is_array($data['params']))
		{
			$params = new JRegistry;
			$params->loadArray($data['params']);
			$data['params'] = (string) $params;
		}

		// Alter the unique field for save as copy
		if ($input->get('task') === 'save2copy')
		{
			// Automatic handling of other unique fields
			$uniqueFields = $this->getUniqueFields();
			if (QuestionsanswersHelper::checkArray($uniqueFields))
			{
				foreach ($uniqueFields as $uniqueField)
				{
					$data[$uniqueField] = $this->generateUnique($uniqueField,$data[$uniqueField]);
				}
			}
		}
		
		if (parent::save($data))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Method to generate a unique value.
	 *
	 * @param   string  $field name.
	 * @param   string  $value data.
	 *
	 * @return  string  New value.
	 *
	 * @since   3.0
	 */
	protected function generateUnique($field,$value)
	{

		// set field value unique
		$table = $this->getTable();

		while ($table->load(array($field => $value)))
		{
			$value = StringHelper::increment($value);
		}

		return $value;
	}
}
