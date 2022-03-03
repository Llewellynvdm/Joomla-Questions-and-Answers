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
	@subpackage		view.html.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;

/**
 * Questionsanswers View class for the Category
 */
class QuestionsanswersViewCategory extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null)
	{		
		// get combined params of both component and menu
		$this->app = JFactory::getApplication();
		$this->params = $this->app->getParams();
		$this->menu = $this->app->getMenu()->getActive();
		// get the user object
		$this->user = JFactory::getUser();
		// Initialise variables.
		$this->items = $this->get('Items');
		if (isset($this->items) && QuestionsanswersHelper::checkArray($this->items))
		{
			// set the items to Global Arrays and Other
			$this->setGlobals($this->items);
			// set the category buttons
			$this->buttons = $this->get('Buttons');
			$this->backButton =  $this->get('BackButton');
		}

		// Set the toolbar
		$this->addToolBar();

		// set the document
		$this->_prepareDocument();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode(PHP_EOL, $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	* Checker
	* @var         string
	*/
	public $qnaBundlesKey;


	/**
	* The tag Array
	* @var         array
	*/
	public $tagArray = array();

	/**
	* The category Array
	* @var         array
	*/
	public $categoryArray = array('non set');

	/**
	* set Global Arrays and other
	*
	* @params       object    $items The expert Values
	*
	* @return         void
	*
	*/
	protected function setGlobals(&$items)
	{
		// set buckets
		$bundels = array();
		$categoryBundels = array();
		$tagArray = array();
		foreach ($items as $nr => &$item)
		{
			// build the item bundels
			$bundels[] = $item->id;
			// build the category bundels
			if (!empty($item->catid))
			{
				$categoryBundels[$item->catid] = $item->catid;
			}
		}
		if (QuestionsanswersHelper::checkArray($categoryBundels))
		{
			// set the filter values
			$this->categoryArray = QuestionsanswersHelper::getNames($categoryBundels, 'catid');
		}
		// json encode
		$qnaBundles = json_encode($bundels);
		// set a global key
		$this->qnaBundlesKey = md5($qnaBundles);
		// set the sessions
		QuestionsanswersHelper::set($this->qnaBundlesKey,$qnaBundles);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_SITE.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new questionsanswersHeaderCheck;

		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		// Set script size.
		$size = $this->params->get('uikit_min');

		// Load uikit version.
		$this->uikitVersion = $this->params->get('uikit_version', 2);

		// Use Uikit Version 2
		if (2 == $this->uikitVersion)
		{
			// Set css style.
			$style = $this->params->get('uikit_style');

			// The uikit css.
			if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addStyleSheet(JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/uikit'.$style.$size.'.css', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// The uikit js.
			if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addScript(JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/uikit'.$size.'.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			}

			// Load the script to find all uikit components needed.
			if ($uikit != 2)
			{
				// Set the default uikit components in this view.
				$uikitComp = array();
				$uikitComp[] = 'data-uk-grid';
			}

			// Load the needed uikit components in this view.
			if ($uikit != 2 && isset($uikitComp) && QuestionsanswersHelper::checkArray($uikitComp))
			{
				// load just in case.
				jimport('joomla.filesystem.file');
				// loading...
				foreach ($uikitComp as $class)
				{
					foreach (QuestionsanswersHelper::$uk_components[$class] as $name)
					{
						// check if the CSS file exists.
						if (File::exists(JPATH_ROOT.'/media/com_questionsanswers/uikit-v2/css/components/'.$name.$style.$size.'.css'))
						{
							// load the css.
							$this->document->addStyleSheet(JURI::root(true) .'/media/com_questionsanswers/uikit-v2/css/components/'.$name.$style.$size.'.css', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
						}
						// check if the JavaScript file exists.
						if (File::exists(JPATH_ROOT.'/media/com_questionsanswers/uikit-v2/js/components/'.$name.$size.'.js'))
						{
							// load the js.
							$this->document->addScript(JURI::root(true) .'/media/com_questionsanswers/uikit-v2/js/components/'.$name.$size.'.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('type' => 'text/javascript', 'async' => 'async') : true);
						}
					}
				}
			}
		}
		// Use Uikit Version 3
		elseif (3 == $this->uikitVersion)
		{
			// The uikit css.
			if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addStyleSheet(JURI::root(true) .'/media/com_questionsanswers/uikit-v3/css/uikit'.$size.'.css', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
			}
			// The uikit js.
			if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
			{
				$this->document->addScript(JURI::root(true) .'/media/com_questionsanswers/uikit-v3/js/uikit'.$size.'.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
				$this->document->addScript(JURI::root(true) .'/media/com_questionsanswers/uikit-v3/js/uikit-icons'.$size.'.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
			}
		}

		// Add the CSS for Footable
		$this->document->addStyleSheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
		$this->document->addStyleSheet(JURI::root() .'media/com_questionsanswers/footable-v3/css/footable.standalone.min.css', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		// Add the JavaScript for Footable (adding all funtions)
		$this->document->addScript(JURI::root() .'media/com_questionsanswers/footable-v3/js/footable.min.js', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		// load the meta description
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		// load the key words if set
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		// check the robot params
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		// check if main category name can be found
		if ($catid = $this->app->input->get('catid', null))
		{
			$title = QuestionsanswersHelper::getVar(null, $catid, 'id', 'title', '=', 'categories');
		}
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = JText::_('COM_QUESTIONSANSWERS_QUESTIONS_ANSWERS');
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
		}
		
		if (empty($title))
		{
			$title = $this->app->get('sitename'); 
		}
		$this->Title = $title;
		$this->document->setTitle($title);
		
		// set the id and view name to session
		if ($vdm = QuestionsanswersHelper::get('events__0'))
		{
			$vastDevMod = $vdm;
		}
		else
		{
			$vastDevMod = QuestionsanswersHelper::randomkey(50);
			QuestionsanswersHelper::set($vastDevMod, 'events__0');
			QuestionsanswersHelper::set('events__0', $vastDevMod);
		}
		// add var key
		$this->document->addScriptDeclaration("var vastDevMod = '".$vastDevMod."';");
		// add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/components/com_questionsanswers/assets/css/category.css', (QuestionsanswersHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		
		// set help url for this view if found
		$this->help_url = QuestionsanswersHelper::getHelpUrl('category');
		if (QuestionsanswersHelper::checkString($this->help_url))
		{
			JToolbarHelper::help('COM_QUESTIONSANSWERS_HELP_MANAGER', false, $this->help_url);
		}
		// now initiate the toolbar
		$this->toolbar = JToolbar::getInstance();
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var, $sorten = false, $length = 40)
	{
		// use the helper htmlEscape method instead.
		return QuestionsanswersHelper::htmlEscape($var, $this->_charset, $sorten, $length);
	}
}
