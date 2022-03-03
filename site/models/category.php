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
	@subpackage		category.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Questionsanswers Model for Category
 */
class QuestionsanswersModelCategory extends JModelList
{
	/**
	 * Model user data.
	 *
	 * @var        strings
	 */
	protected $user;
	protected $userId;
	protected $guest;
	protected $groups;
	protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user = JFactory::getUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->initSet = true; 
		// Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__questionsanswers_question_and_answer as a
		$query->select($db->quoteName(
			array('a.id','a.catid'),
			array('id','catid')));
		$query->from($db->quoteName('#__questionsanswers_question_and_answer', 'a'));

		// Filtering.

		$catid = $this->input->get('catid', null);
		$this->childrenButtonIDs = 'root';
		if ($catid > 0)
		{
			if ($children = QuestionsanswersHelper::getVars('categories', $catid, 'parent_id', 'id', 'IN', ''))
			{
				// load the fist found children for buttons
				$this->childrenButtonIDs = $children;
				// set to bucket
				$childrenBucket = array($children);
				while ($children = QuestionsanswersHelper::getVars('categories', $children, 'parent_id', 'id', 'IN', ''))
				{
					$childrenBucket[] = $children;
				}
				$getAllCats = QuestionsanswersHelper::mergeArrays($childrenBucket);
				// load main category
				$getAllCats[] = $catid;
				// load children category if set
				$query->where('a.catid IN (' . implode(',',$getAllCats) . ')');
			}
			else
			{
				unset($this->childrenButtonIDs);
				// load main category if set
				$query->where('a.catid = ' . (int) $catid);
			}
		}
		// Get where a.published is 1
		$query->where('a.published = 1');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$user = JFactory::getUser();
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_questionsanswers', true);

		// Insure all item fields are adapted where needed.
		if (QuestionsanswersHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
			}
		}

		// return items
		return $items;
	}

	/**
	 * Get the uikit needed components
	 *
	 * @return mixed  An array of objects on success.
	 *
	 */
	public function getUikitComp()
	{
		if (isset($this->uikitComp) && QuestionsanswersHelper::checkArray($this->uikitComp))
		{
			return $this->uikitComp;
		}
		return false;
	}

	public function getButtons()
	{
		// add back button
		$this->addBackButton = true;
		// load the children buttons
		if (isset($this->childrenButtonIDs) && QuestionsanswersHelper::checkArray($this->childrenButtonIDs))
		{
			return QuestionsanswersHelper::getNames($this->childrenButtonIDs, 'catid');
		}
		// load the top parent buttons
		elseif (isset($this->childrenButtonIDs) && QuestionsanswersHelper::checkString($this->childrenButtonIDs) && $this->childrenButtonIDs === 'root' && $parents = $this->getParentButtonIDs())
		{
			// don't add back button
			$this->addBackButton = false;
			return QuestionsanswersHelper::getNames($parents, 'catid');
		}
		return false;
	}

	public function getBackButton()
	{
		// set back button
		return $this->addBackButton;
	}


	protected function getParentButtonIDs()
	{
		// get the db object
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from($db->quoteName('#__categories', 'a'));
		$query->where('a.extension = ' . $db->quote('com_questionsanswers.questions_and_answers'));
		$query->where('a.published = 1');
		$query->where('a.parent_id = 1');
		// set query
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadColumn();
		}
		return false;
	}
}
