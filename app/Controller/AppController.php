<?php
App::uses('Controller', 'Controller');
/**
 * App Wide Methods
 *
 * File is used for app wide convenience functions and logic and settings.
 * Methods in this file can be accessed from any other controller in the app.
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha� Project
 * @package       zuha
 * @subpackage    zuha.app
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
//Note : Enable CURL PHP in php.ini file to use Facebook.Connect component of facebook plugin: Faheem
class AppController extends Controller {

	public $uses = array();
	public $userId = '';
	public $acoPath = '';
	public $viewClass = 'Theme';
	public $theme = 'Default';
	public $userRoleId = 5;
	public $paginate = array();
	public $userRoles = array(
		'administrators',
		'guests'
		); 
	public $userRoleName = 'guests';
	public $params = array();
	public $templateId = '';
	public $pageTitleForLayout;
	public $helpers = array(
		'Session', 
		'Text', 
		'Form', 
		'Js', 
		'Time',
		'Html',
		'Utils.Tree',
		'Webpages.Webpage'
		);
	public $components = array(
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
					'fields' => array(
						'username' => array(
							'username', 
							'email'
							)
						)
					)
				),
			'authorize' => 'Controller',
			'loginAction' => array(
				'plugin' => 'users',
				'controller' => 'users',
				'action' => 'login'
				)
			),
		'Session', 
		'RequestHandler', 
		'Cookie',
		'FormSecurity'
		);

/**
 * Constructor method
 * 
 * @param
 * @param
 */
	public function __construct($request = null, $response = null) {
		
		parent::__construct($request, $response);
		$this->_getComponents();
		$this->_getHelpers();
		$this->_getUses();
		$this->pageTitleForLayout = Inflector::humanize(Inflector::underscore(' ' . $this->name . ' '));
	}

/**
 * Force password change method
 * 
 * @param void
 */
	protected function _forcePwdChange() {
		if ($this->Session->read('Auth.User.pwd_change')) {
			$goodUrls[] = '/users/users/edit/'.$this->Session->read('Auth.User.id').'/pw';
			$goodUrls[] = '/users/users/logout';
			if (in_array($this->request->here, $goodUrls) || $this->request->params['requested'] == 1) {
			} else {
				debug($this->request->here);
				debug($goodUrls);
				debug($this->request);
				break;
				$this->Session->setFlash('Please change your password.');
				$this->redirect(array('plugin' => 'users', 'controller' => 'users', 'action' => 'edit', $this->Session->read('Auth.User.id'), 'pw'));
			}
		}
	}


/**
 * Over ride a controllers default redirect action by adding a form field which specifies the redirect.
 */
	public function redirect($url, $status = null, $exit = true) {
		if (!empty($this->request->data['Success']['redirect']) && $status == 'success') {
			return parent::redirect($this->request->data['Success']['redirect'], $status, $exit);
		} elseif (!empty($this->request->data['Error']['redirect']) && $status == 'error') {
			return parent::redirect($this->request->data['Error']['redirect'], $status, $exit);
		} elseif (!empty($this->request->data['Override']['redirect'])) {
			return parent::redirect($this->request->data['Override']['redirect'], $status, $exit);
		} elseif (!empty($this->request->query['destination'])) {
			return parent::redirect($this->request->query['destination'], $status, $exit);
		} else {
			return parent::redirect($url, $status, $exit);
		}
	}

/**
 * Over write of core paginate method
 * to handle auto filtering.
 *
 * @param string
 * @param array
 * @param array
 */
	public function paginate($object = null, $scope = array(), $whitelist = array()) {
		$this->_handlePaginatorSorting();
		$this->_handlePaginatorFiltering($object);
		return parent::paginate($object, $scope, $whitelist);
	}


/**
 * Before Filter method
 * 
 * @todo Need to move the bottom three to the Theme class along with all of the theme / template stuff in this file.
 */
	public function beforeFilter() {
	    parent::beforeFilter();
		$this->_writeStats();
		$this->_configEditor();
		$this->_configAuth();
		$this->_forcePwdChange(); 
		$this->_rememberMe();
		$this->_userAttributes();
		$this->_checkGuestAccess();
		
		// move these and other request handlers in this file to Theme class
		$this->RequestHandler->ajaxLayout = 'default';
		$this->_siteTemplate(); // move this 
		$this->viewClass = $this->RequestHandler->ext == 'csv' ? 'Csv' : $this->viewClass;
		
		// order is important for these automatic view vars
		$this->set('page_title_for_layout', $this->_pageTitleForLayout()); 
		$this->set('title_for_layout', $this->_titleForLayout()); 
		$this->set('userRoleId', $this->userRoleId); // deprecated (use the one below) // 07/19/2013 RK
		$this->set('__userRoleId', $this->userRoleId);
	}
	
	
/**
 * Before Render method
 * 
 * sets a few variables needed for all views
 */
    public function beforeRender() {
		parent::beforeRender();
		$this->set('referer', $this->referer()); // used for back button links, could be useful for breadcrumbs possibly
		$this->set('_serialize', array_keys($this->viewVars));
        $this->set('_view', $this->view);
		// do a final permission check on the user field
		$modelName = Inflector::singularize($this->name);
		$this->set('_layout', $this->$modelName->theme); // set in the themeable behavior
		$this->Acl->check(array('permission' => true), $this->$modelName->permissionData);
	}
	

/**
 * User attributes method
 * 
 */
	protected function _userAttributes() {
        // order is important for these
		$this->userId = $this->Session->read('Auth.User.id');
		$this->userRoleId = $this->Session->read('Auth.User.user_role_id');
		/**
		 * @todo This is not working. Fix it.
		 * @since 7/24/2013
		 * @author Joel Byrnes <joel@buildrr.com>
		 */
//		$this->userRoleName = $this->Session->read('Auth.UserRole.name');
//		$this->userRoleName = !empty($this->userRoleName) ? $this->userRoleName : 'guests';
		$this->userRoleId = !empty($this->userRoleId) ? $this->userRoleId : (defined('__SYSTEM_GUESTS_USER_ROLE_ID') ?  __SYSTEM_GUESTS_USER_ROLE_ID : 5);
	}


/**
 * Remember Me Method
 * 
 * Logs you in if you have the rememberMe cookie on your client.
 */
	protected function _rememberMe() {
	   	$this->Cookie->httpOnly = true;
		
		if (!$this->Auth->loggedIn() && $this->Cookie->read('rememberMe')) {
	         $cookie = $this->Cookie->read('rememberMe');
	         $this->loadModel('Users.User'); // If the User model is not loaded already
	         $user = $this->User->find('first', array(
	         	'conditions' => array(
	            	'User.username' => $cookie['username'],
	                'User.password' => $cookie['password']
	              	)
	         	));
	        if ($user && !$this->Auth->login($user['User'])) {
	        	$this->redirect('/users/users/logout'); // destroy session & cookie
	    	}
		}
	}
	
/**
 * Write stats session variables
 */
	protected function _writeStats() {		
		$statsEntry = $this->Session->read('Stats.entry');
		if (empty($statsEntry)) {
			 $this->Session->write('Stats.entry', base64_encode(time()));  
		}		
		$referral = $this->Session->read('Stats.referer');
		if (empty($referral)) {
			$this->Session->write('Stats.referer', $_SERVER['HTTP_REFERER']);
		}
	}

/**
 * Configure Editor (CKE Editor)
 * 
 */
	protected function _configEditor() {
		if ($this->Session->read('Auth.User') && defined('SITE_DIR')) {
			$this->Session->write('KCFINDER.disabled', false);
			$this->Session->write('KCFINDER.uploadURL', '/theme/default/upload/' . $this->Session->read('Auth.User.id'));
			$this->Session->write('KCFINDER.uploadDir', '../../../../' . SITE_DIR . '/Locale/View/webroot/upload/' . $this->Session->read('Auth.User.id'));
		}
	}


/**
 * Handles when a page is being called after the context_sort element is used.
 * This element will call the same page it is sitting on, and add a get variable to the end
 * We want to take that get variable and redirect to the current page with those variables on the end.
 *
 * if the variable contextSorter is set we redirect to http://domain/current_url/contextSorterVar
 *
 * @return null
 */
	private function _handlePaginatorSorting() {
		#debug($this->request->url.$this->request->query['contextSorter']);
		if (!empty($this->request->query['contextSorter'])) {
			$this->redirect($this->request->query['contextSorter']);
		}
	}

/**
 * Handles auto filtering using named parameters on index pages.
 * Decides whether there are multiple filters or one.
 *
 * @param void
 * @return void
 */
 	private function _handlePaginatorFiltering($object = null) {
		if (empty($this->request->params['named']['filter'])) {
			$this->__handlePaginatorArchivable($object);
		}

		// filter by database field full value
		$filter = !empty($this->request->params['named']['filter']) ? $this->request->params['named']['filter'] : null;
		if (!empty($filter) && is_array($filter)) {
			// use an OR filter if we do multiple filters
			foreach ($filter as $name) {
				$this->__handlePaginatorFiltering(urldecode($name), $object);
			}
		} else if (!empty($filter)) {
			$this->__handlePaginatorFiltering(urldecode($filter), $object);
		}

		// filter by starting letter of database field
		$starter = !empty($this->request->params['named']['start']) ? $this->request->params['named']['start'] : null;
		if (!empty($starter) && is_array($starter)) {
			// use an OR filter if we do multiple filters
			foreach ($starter as $start) {
				$this->__handlePaginatorStarter(urldecode($start), $object);
			}
		} else if (!empty($starter)) {
			$this->__handlePaginatorStarter(urldecode($starter), $object);
		}

		// filter by any match of a string in a particular field
		$container = !empty($this->request->params['named']['contains']) ? $this->request->params['named']['contains'] : null;
		if (!empty($container) && is_array($container)) {
			// use an OR filter if we do multiple filters
			foreach ($container as $contain) {
				$this->__handlePaginatorContainer(urldecode($contain), $object);
			}
		} else if (!empty($container)) {
			$this->__handlePaginatorContainer(urldecode($container), $object);
		}

		// filter by range of a particular field
		$range = !empty($this->request->params['named']['range']) ? $this->request->params['named']['range'] : null;
		if (!empty($range) && is_array($range)) {
			// use an OR filter if we do multiple filters
			foreach ($range as $singleRange) {
				$this->__handlePaginatorRange(urldecode($singleRange), $object);
			}
		} else if (!empty($range)) {
			$this->__handlePaginatorRange(urldecode($range), $object);
		}
	}

/**
 * Checks to see if we're paginating a sub model one level deep.
 *
 * If we are then we get the class and return the model schema, if not return false.
 * @param string
 * @return mixed
 */
	private function _getPaginatorVars($object, $field) {
		$ModelName = $this->modelClass;
		if (@$this->$object->name) {
			$Object = $this->$object;
		} else if (@$this->$ModelName->$object->name) {
			$Object = $this->$ModelName->$object;
		} else if (@$this->$ModelName->name) {
			$Object = $this->$ModelName;
		}

		if (@$Object->name) {
			$options['alias'] = !empty($object) ? $object : $ModelName;
			$options['schema'] = $this->__paginatorSchema($Object->schema(), $field);
			$options['fieldName'] = $this->__paginatorFieldName($field, $options['schema']);
			$options['fieldValue'] = substr($field, strpos($field, ':') + 1); // returns 'incart' from 'status:incart'
			$options['fieldValue'] = $options['fieldValue'] == 'null' ? null : $options['fieldValue'];  // handle null as a value
			return $options;
			
		} else {
			return null;
		}
	}

/**
 * Removed archived records from paginated lists by default.
 *
 * @param void
 * @return void
 */
	private function __handlePaginatorArchivable($object) {
		$options = $this->_getPaginatorVars($object, 'is_archived');
		if (!empty($options['schema']['is_archived'])) {
			$this->redirect(Router::reverse($this->request->params + array('filter' => 'archived:0', 'url' => null)));
		}
	}

/**
 * The actual handling of filtering for paginated pages by full field value
 * Adds additional conditions to the paginate variable (one at time)
 *
 * @param mixed
 * @return void
 */
	private function __handlePaginatorFiltering($field, $object) {
		$options = $this->_getPaginatorVars($object, $field);
		if (!empty($options['fieldName'])) {
			if ($options['schema'][$options['fieldName']]['type'] == 'datetime' || $options['schema'][$options['fieldName']]['type'] == 'date') {
				$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName'].' >'] = $options['fieldValue'];
			} else {
				$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName']][] = $options['fieldValue'];
			}
			$this->pageTitleForLayout = __(' %s ', $options['fieldValue']) . $this->pageTitleForLayout;
		} else {
			// no matching field don't filter anything
			if (Configure::read('debug') > 0) {
				$this->Session->setFlash(__('Invalid field filter attempted on ' . $options['alias']));
			}
		}
	}
	
/**
 * Paginator Schema
 * 
 * Update the schema with the meta fields (refer to metable behavior)
 * 
 * @param object $schema
 * @param string $field
 */
	protected function __paginatorSchema($schema, $field) {
		$field = $this->__paginatorFieldName($field);
		if (strpos($field, '!') === 0) {
			$schema[$field] = array(
				'type' => 'string',
				'null' => true,
				'default' => null,
				'length' => 255,
				'collate' => 'utf8_general_ci',
				'charset' => 'utf8'
			);
		}
		return $schema;
	}

/**
 * The actual handling of filtering for paginated pages
 * Adds additional conditions to the paginate variable (one at time)
 *
 * @param mixed
 * @return void
 */
	private function __handlePaginatorStarter($startField, $object) {
		$options = $this->_getPaginatorVars($object, $startField);

		if (!empty($options['fieldName'])) {
			$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName'].' LIKE'] = $options['fieldValue'] . '%';
			$this->pageTitleForLayout = __(' %s ', $options['fieldValue']) . $this->pageTitleForLayout;
		} else {
			// no matching field don't filter anything
			if (Configure::read('debug') > 0) {
				$this->Session->setFlash(__('Invalid starter filter attempted.'));
			}
		}
	}

/**
 * The actual handling of filtering for paginated pages
 * Adds additional conditions to the paginate variable (one at time)
 *
 * @param mixed
 * @return void
 */
	private function __handlePaginatorContainer($containField, $object) {
		$options = $this->_getPaginatorVars($object, $containField);
		if (!empty($options['fieldName'])) {
			$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName'].' LIKE'] = '%' . $options['fieldValue'] . '%';
			$this->pageTitleForLayout = __(' %s ', $options['fieldValue']) . $this->pageTitleForLayout;
		} else {
			// no matching field don't filter anything
			if (Configure::read('debug') > 0) {
				$this->Session->setFlash(__('Invalid container filter attempted.'));
			}
		}
	}
	
	
	/**
	 * 
	 * @param type $rangeField
	 * @param type $object
	 */
	private function __handlePaginatorRange($rangeField, $object) {
		$options = $this->_getPaginatorVars($object, $rangeField);
		$range = explode(';', $options['fieldValue']);
		if (!empty($options['fieldName'])) {
			$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName'].' >='] = $range[0];
			if(!empty($range[1])) {
				$this->paginate['conditions'][$options['alias'].'.'.$options['fieldName'].' <='] = $range[1];
			}
//			debug($this->paginate['conditions']);
			$this->pageTitleForLayout = __(' %s ', $options['fieldValue']) . $this->pageTitleForLayout;
		} else {
			// no matching field don't filter anything
			if (Configure::read('debug') > 0) {
				$this->Session->setFlash(__('Invalid range filter attempted.'));
			}
		}
	}


/**
 * Get the field name with if its close
 *
 * @param string	A string which is close to a db field name.
 * @return string
 */
	private function __paginatorFieldName($string, $modelFields = array()) {
		$fieldName = Inflector::underscore(substr($string, 0, strpos($string, ':'))); // standardizes various name versions

		if (!empty($modelFields[$fieldName])) {
			// match exact field name (no change necessary)
			return $fieldName;
		} else if (!empty($modelFields[$fieldName.'_id'])) {
			// match something_id naming convention
			return $fieldName.'_id';
		} else if (!empty($modelFields['is_'.$fieldName])) {
			// match is_something naming convention
			return 'is_'.$fieldName;
		} else if (strpos($fieldName, '!') === 0) {
			// match meta fields
			return $fieldName;
		} else {
			return null;
		}
	}


/**
 * Convenience delete method
 * The goal is to make less code necessary in individual controllers
 * and have more reusable code.
 *
 * @param string 	model name
 * @param int 		$id
 * @param array 	use $options['redirect'] to override default redirect (referer
 * @return string
 * @todo get rid of this, make the controllers handle it themselves
 */
	public function __delete($model = null, $id = null, $options = null) {
		// set default class & message for setFlash
		$class = 'flash_bad';
		$msg   = 'Invalid Id';

		// check id is valid
		if(!empty($id)) {
			// get the Item
			$item = $this->$model->read(null,$id);

			// check Item is valid
			if(!empty($item)) {
				// try deleting the item
				if($this->$model->delete($id)) {
					$class = 'flash_good';
					$msg   = 'Successfully deleted';
				} else {
					$msg = 'There was a problem deleting your Item, please try again';
				}
			} else {
				$msg = 'Id not found';
			}
		}

		// output JSON on AJAX request
		if($this->RequestHandler->isAjax()) {
			$this->autoRender = $this->layout = false;
			echo json_encode(array('success' => ($class=='flash_bad') ? FALSE : TRUE,'msg'=>"<p id='flashMessage' class='{$class}'>{$msg}</p>"));
			exit;
		}

		// set flash message & redirect
		$this->Session->setFlash(__($msg, true));
		if (!empty($options['redirect'])) {
			$this->redirect($options['redirect']);
		} else {
			$this->redirect(Controller::$this->referer());
		}
	}


/**
 * Used to show admin layout for admin pages & userRole views if they exist
 */
	public function _siteTemplate() {
		if (
				!$this->request->ext == 'csv'
				&& !$this->request->is('ajax')
				&& !empty($this->request->params['prefix'])
				&& $this->request->params['prefix'] == 'admin'
				&& strpos($this->request->params['action'], 'admin_') === 0
		) {
			if ( $this->request->params['prefix'] == CakeSession::read('Auth.User.view_prefix') ) {
				// this if checks to see if the user role has a specific view file
				$this->request->params['action'] = str_replace('admin_', '', $this->request->params['action']);
				unset($this->request->params['prefix']);
				$this->request->url = str_replace('admin/', '', $this->request->url);
				$this->request->here = str_replace('/admin', '', $this->request->here);
				$Dispatcher = new Dispatcher();
				$Dispatcher->dispatch($this->request, new CakeResponse(array('charset' => Configure::read('App.encoding'))));
				break;
			} else {
				$this->Session->setFlash(__('Section access restricted.'));
				$this->redirect($this->referer());
			}
		} else if ( !empty($this->request->params['admin']) && $this->request->params['admin'] == 1 ) {
			$this->request->params['action'] = str_replace('admin_', '', $this->request->params['action']);
			foreach ( App::path('views') as $path ) {
				$paths[] = !empty($this->request->params['plugin']) ? str_replace(DS . 'View', DS . 'Plugin' . DS . ucfirst($this->request->params['plugin']) . DS . 'View', $path) : $path;
			} // end App::path loop
			foreach ( $paths as $path ) {
				if ( file_exists($path . CakeSession::read('Auth.User.view_prefix') . DS . $this->viewPath . DS . $this->request->params['action'] . '.ctp') ) {
					$this->viewPath = CakeSession::read('Auth.User.view_prefix') . DS . ucfirst($this->request->params['controller']);
				} // end view prefix loop
			} // end paths loop
			$this->layout = 'default';
		} else if ( empty($this->request->params['requested']) && !$this->request->is('ajax') && !$this->request->ext == 'csv' ) {
			// this else if makes so that extensions still get parsed
			$this->_getTemplate();
		}
	}


/**
 * Used to find the template and makes a call to parse all page views.  Sets the defaultTemplate variable for the layout.
 * This function parses the settings for templates, in order to decide which template to use, based on url, and user role.
 *
 * @todo 		Move this to the webpage model and optimize it.. looks a bit overcomplicated
 */
	public function _getTemplate() {
		if (defined('__APP_TEMPLATES')) {
			$templates = templateSettings();
			// check urls first so that we don't accidentally use a default template before a template that was set for this url.
			if (!empty($templates)) {
				foreach ($templates as $key => $template) {
					if (!empty($template['urls'])) {
						// this over rides isDefault, so if its truly a default template, don't set urls
						$this->templateId = $this->_urlTemplate($template);
						// get rid of template values so we don't have to check them twice
						unset($templates[$key]);
					}
					if (!empty($this->templateId)) {
						$templated['Webpage']['id'] = $template['templateId']; // used in javascript.ctp and css.ctp elements
						// as soon as we have the first template that matches, end this loop
						break;
					}
				} // end loop
			}
			
			if (!empty($templates) && empty($this->templateId)) {
				foreach ($templates as $key => $template) {
					if (!empty($template['isDefault'])) {
						$this->templateId = $template['templateName'];
						$this->templateId = !empty($template['userRoles']) ? $this->_userTemplate($template) : $this->templateId;
					}
					if (!empty($this->templateId)) {
						$templated['Webpage']['id'] = $template['templateId']; // used in javascript.ctp and css.ctp elements
						// as soon as we have the first template that matches, end this loop
						break;
					}
				} // end loop
			}
		}
		// getting rid of the template in the navbar (no one uses it) 7/22/2013 RK
		// this is because the Webpage model is not loaded for the install site page, and 'all' so that we can pass all templates to the navbar
		// $templated = $this->request->controller == 'install' && $this->request->action == 'site' ? null : $this->Webpage->find('all', array('conditions' => array('Webpage.type' => 'template'), 'order' => array('FIND_IN_SET(`Webpage`.`id`, \''.$this->templateId.'\')' => 'DESC')));
		$templateFile = ROOT.DS.SITE_DIR.DS.'Locale'.DS.'View'.DS.'Layouts'.DS.$this->templateId;
		$templated['Webpage']['content'] = file_get_contents($templateFile);
		// $templated = $this->request->controller == 'install' && $this->request->action == 'site' ? null : $this->Webpage->find('first', array('conditions' => array('Webpage.id' => $this->templateId), 'callbacks' => false));
        // $this->set('templates', Set::combine($templated, '{n}.Webpage.id', '{n}.Webpage.name')); // for the admin navbar
        // $templated = !empty($this->templateId) ? Set::extract('/Webpage[id=' . $this->templateId . ']', $templated) : null; // getting it back to 'first' type results
        // $templated = !empty($templated[0]) ? $templated[0] : null; // getting it back to 'first' type results
        $this->Webpage->parseIncludedPages($templated, null, null, $this->userRoleId, $this->request);
        $this->set('defaultTemplate', $templated);
		if (!empty($this->templateId)) {
            $this->set('templateId', $this->templateId); // for the admin navbar
			$this->layout = 'custom';
		}
	}


/**
 * Checks if the template selected is available to the current users role
 * 
 * @param array $data
 * @return null
 */
	private function _userTemplate($data) {
		// check if the url being requested matches any template settings for user roles
		// set a new template id if the session is over writing it
		$currentUserRole = $this->Session->read('viewingRole') ? $this->Session->read('viewingRole') : $this->userRoleId;

		if (!empty($data['userRoles'])) {
			foreach ($data['userRoles'] as $userRole) {
				if ($userRole == $currentUserRole) {
					$templateId = $data['templateName'];
				}
			} // end userRole loop
		} elseif (!empty($data['templateName'])) {
			$templateId = $data['templateName'];
		}

		if (!empty($templateId)) {
			return $templateId;
		} else {
			return null;
		}
	}

/**
 * check if the selected template is available to the current url
 *
 * @param {array}		Individual template data arrays from the settings.ini (or defaults.ini) file.
 */
	private function _urlTemplate($data) {
		// check if the url being requested matches any template settings for specific urls
		if (!empty($data['urls'])) {
			$i=0;
			foreach ($data['urls'] as $url) {
				$urlString = str_replace('/', '\/', trim($url));
				$urlRegEx = '/'.str_replace('*', '(.*)', $urlString).'/';
				$urlRegEx = strpos($urlRegEx, '\/') === 1 ? '/'.substr($urlRegEx, 3) : $urlRegEx;
				$url = $this->request->action == 'index' ? $this->request->plugin . '/' . $this->request->controller . '/' . $this->request->action . '/' : $this->request->url . '/';
				$urlCompare = strpos($url, '/') === 0 ? substr($url, 1) : $url;
				if (preg_match($urlRegEx, $urlCompare)) {
					$templateId = !empty($data['userRoles']) ? $this->_userTemplate($data) : $data['templateName'];
				}
			$i++;
			}
		}
		if (!empty($templateId)) {
			return $templateId;
		} else {
			return null;
		}
	}
    
/**
 * Loads components dynamically using both system wide, and per controller loading abilities.
 *
 * You can create a comma separated (no spaces) list if you only need a system wide component.  If you would like to specify components on a per controller basis, then you use ControllerName[] = Plugin.Component. (ie. Projects[] = Ratings.Ratings).  If you want both per controller, and system wide, then use the key components[] = Plugin.Component for each system wide component to load.  Note: You cannot have a comma separated list, and the named list at the same time.
 */
	private function _getComponents() {
		if (!empty($_SERVER['REQUEST_URI']) && basename($_SERVER['REQUEST_URI']) != 'site') {
			$this->components[] = 'Acl';
		}

		if (defined('__APP_LOAD_APP_COMPONENTS')) {
			$settings = __APP_LOAD_APP_COMPONENTS;
			if ($components = @unserialize($settings)) {
				foreach ($components as $key => $value) {
					if ($key == 'components') {
						foreach ($value as $val) {
							$this->components[] = $val;
						}
					} else if ($key == $this->name) {
						if (is_array($value)) {
							foreach ($value as $val) {
								$this->components[] = $val;
							}
						} else {
							$this->components[] = $value;
						}
					}
				}
			} else {
				$this->components = array_merge($this->components, explode(',', $settings));
			}
		}
		

		// not really loving it but it has to be here because it is in the construct and for logins to work
		if (in_array('Facebook', CakePlugin::loaded())) {
			$this->components['Facebook.Connect'] = array('plugin' => 'Users', 'model' => 'User'); // correct way !!?
		}
	}



/**
 * Loads helpers dynamically system wide, and per controller loading abilities.
 */
	private function _getHelpers() {
		if(defined('__APP_LOAD_APP_HELPERS')) {
			$settings = __APP_LOAD_APP_HELPERS;
			if ($helpers = @unserialize($settings)) {
				foreach ($helpers as $key => $value) {
					if ($key == 'helpers') {
						foreach ($value as $val) {
							$this->helpers[] = $val;
						}
					} else if ($key == $this->name) {
						if (is_array($value)) {
							foreach ($value as $val) {
								$this->helpers[] = $val;
							}
						} else {
							$this->helpers[] = $value;
						}
					}
				}
			} else {
				$this->helpers = array_merge($this->helpers, explode(',', $settings));
			}
		}

		// not really loving these helpers here
		
		// this one has to be here because it is in the construct and for logins to work
		if (in_array('Facebook', CakePlugin::loaded())) {
			$this->helpers[] = 'Facebook.Facebook';
		}
		// please leave a comment about why this would have to be here
		if (in_array('Media', CakePlugin::loaded())) {
			$this->helpers[] = 'Media.Media';
		}
	}


/**
 * Loads uses dynamically system wide
 */
	private function _getUses() {
		if (!empty($this->request)) { // this is so that it doesn't load during console activities
			if (is_array($this->uses)) {
				$this->uses = array_merge($this->uses, array('Webpages.Webpage'));
			} else {
				// there is only one (non-array) in $this->uses
				$this->uses = array($this->uses, 'Webpages.Webpage');
			}
		}
		

		// not really loving it but it has to be here because it is in the construct and for logins to work
		if (in_array('Facebook', CakePlugin::loaded())) {
			$this->uses = ( is_array($this->uses) ) ? array_merge($this->uses, array('Facebook.Facebook')) : array($this->uses, 'Facebook.Facebook');
		}
	}


/**
 * Site status
 *
 * @todo	Deprecated, remove references and delete this function OR Upgrade to a new versioning system.
 */
	private function _siteStatus() {
		if ($this->userRoleId == 1) {
			$fileSettings = new File(CONFIGS.'settings.ini');
			$fileDefaults = new File(CONFIGS.'defaults.ini');
			// the settings file doesn't exist sometimes, and thats fine
			if ($settings = $fileSettings->read()) {
				App::uses('File', 'Utility');

				$defaults = $fileDefaults->read();

				if ($settings != $defaults) {
				 	$this->set('dbSyncError', '<div class="siteUpgradeNeeded">Site settings are out of date.  Please <a href="/admin">upgrade database</a>. <br> If you think the defaults.ini file is out of date <a href="/admin/settings/update_defaults/">update defaults</a>. <br> If you think the settings.ini file is out of date <a href="/admin/settings/update_settings/">update settings</a></div>');
				 }
			 }
		 }
	 }



/**
 * Configure AuthComponent
 */
   private function _configAuth() {
		$authError = defined('__APP_DEFAULT_LOGIN_ERROR_MESSAGE') ?	array('message'=> __APP_DEFAULT_LOGIN_ERROR_MESSAGE) : array('message'=> 'Please register or login to access that feature.');
		$this->Auth->authError = $authError['message'];
        $this->Auth->loginAction = defined('__APP_LOGIN_ACTION') ? __APP_LOGIN_ACTION : array('plugin' => 'users', 'controller' => 'users', 'action' => 'login');
		$this->Auth->authorize = array('Controller');
		$this->Auth->authenticate = array(
            'Form' => array(
                'userModel' => 'Users.User',
                'fields' => array('username' => array('username', 'email'), 'password' => 'password'),
            	)
        	);
		$this->Auth->actionPath = 'controllers/';
		$this->Auth->allowedActions = array('display', 'itemize');

		if (!empty($this->allowedActions)) {
			$allowedActions = array_merge($this->Auth->allowedActions, $this->allowedActions);
			$this->Auth->allowedActions = $allowedActions;
		}
   }

/**
 * Handle various auto page title variables.
 * Easily over ridden by individual controllers.
 */
	private function _pageTitleForLayout() {
		return $this->pageTitleForLayout = Inflector::humanize(Inflector::underscore(strtolower($this->pageTitleForLayout)));
	}

	private function _titleForLayout() {
		return $this->titleForLayout = Inflector::humanize(Inflector::underscore($this->titleForLayout));
	}


/**
 * Make sending email available to all controllers (AppModel calls to this function)
 *
 * @param string		String - address to send email to
 * @param sring			$subject: subject of email.
 * @param string		$message['html'] in the layout will be replaced with this text.
 * @param string		$template to be picked from folder for email. By default, if $mail is given in any template.
 * @param array			address/name pairs (e.g.: array(example@address.com => name, ...)
 * @param UNKNOWN		Have not used it don't know what it does or if it works.
 * @return bool
 */
	public function __sendMail($toEmail = null, $subject = null, $message = null, $template = 'default', $from = array(), $attachment = array()) {
		$this->SwiftMailer = $this->Components->load('SwiftMailer');
		if (defined('__SYSTEM_SMTP')) {
			extract(unserialize(__SYSTEM_SMTP));
			$smtp = base64_decode($smtp);
			$smtp = Security::cipher($smtp, Configure::read('Security.iniSalt'));
			
			if(parse_ini_string($smtp)) {
				
				if(isset($toEmail['to']) && is_array($toEmail)) $this->SwiftMailer->to = $toEmail['to'];
				else $this->SwiftMailer->to = $toEmail;
				if(isset($toEmail['cc']) && is_array($toEmail)) $this->SwiftMailer->cc = $toEmail['cc'];
				if(isset($toEmail['bcc']) && is_array($toEmail)) $this->SwiftMailer->bcc = $toEmail['bcc'];
				if(isset($toEmail['replyTo']) && is_array($toEmail)) $this->SwiftMailer->replyTo = $toEmail['replyTo'];

				$this->SwiftMailer->template = $template;
				$this->SwiftMailer->attachments = $attachment;
				$this->SwiftMailer->layout = 'email';
				$this->SwiftMailer->sendAs = 'html';
				
				if ($message) {
              		$this->SwiftMailer->content = $message;
					if(is_array($message) && isset($message['html'])) {
						$this->SwiftMailer->content = $message['html'];
					} else {
						$message = array('html' => $message);
					}
					$this->set('message', $message);
				}

				if (!$subject) {
					$subject = 'No Subject';
				}
				//Set view variables as normal
				return $this->SwiftMailer->send($template, $subject);
			} else {
				throw new Exception(__('SMTP Ini parsing failed.'));
			}
		} else {
			throw new Exception(__('SMTP Settings not defined.'));
		}
   }


/**###########################################################
##############################################################
#################  HERE DOWN IS PERMISSIONS ##################
##############################################################
##############################################################
##############################################################
##############################################################
##############################################################*/


	
/**
 * Check Guest Access method
 * 
 * Where the rubber meets the road in ACL
 * 
 * @todo Auth->action() didn't work, but we can get the actual action mapping at some point
 */
	protected function _checkGuestAccess() {
		$allowed = array_search($this->request->params['action'], $this->Auth->allowedActions);
		
		if ($allowed === 0 || $allowed > 0 ) {
			$this->Auth->allow('*');	
		} else if (empty($this->userId) && empty($allowed)) {
			$aro = $this->_guestsAro(); // guests group aro model and foreign_key
			$this->acoPath = $this->_getAcoPath(); // get controller and action
			// this first one checks record level if record level exists
			// which it can exist and guests could still have access
			// @todo Auth->action() didn't work, but we can get the actual action mapping at some point
			$action = $this->request->action == 'view' ? 'read' : 'update';
			if ($this->Acl->check($aro, $this->acoPath, $action)) {
				$this->Auth->allow();
			}
		}
		// we get here and do nothing if you are logged in
	}
	

/**
 * This function is called by $this->Auth->authorize('controller') and only fires when the user is logged in.
 *
 * @todo		Move this to the permissions app_controller or somewhere over there.
 * @todo		Optimize this somehow, someway.
 */
	public function isAuthorized($user) {
		// this allows all users in the administrators group access to everything
		if (!empty($user['view_prefix']) && ($user['view_prefix'] == 'admin' || $user['user_role_id'] == 1)) { return true; }
		
	/**
	 * We don't need to check guest access because this function is only called if we're logged in. 
	 * Also there is already a check in another function that fires before this to check guest access
	 * 
	 * 
		// check guest access
		$aro = $this->_guestsAro(); // guest aro model and foreign_key
		$aco = $this->_getAcoPath(); // get aco
		debug($aro);
		if ($this->Acl->check($aro, $aco)) {
			//echo 'guest access passed';
			//return array('passed' => 1, 'message' => 'guest access passed');
			return true;
		} else {
			// the stuff after this coment block was in this else previously
		} 
	 */
			
		// get paths
		$aro = $this->_userAro($user['id']); // user aro model and foreign_key
		$aco = $this->_getAcoPath(); // get aco

		/**
		 * The 3rd parameter for Acl->check() defaults to '*'.
		 * When '*', it checks ALL 4 types: _create, _read, _update, _delete
		 * In our use case, webpages/webpages/view/X, we only want to assign _read
		 * To check for that, we must specify 'read' when doing Acl->check().
		 * There is kinda supposed to be internal mapping of 'view' => 'read' in Auth,
		 * but it's not happening here.
		 *
		 * Here is a quick and dirty fix:
		 */
		$aclCheckAction = ($this->request->action == 'view') ? 'read' : '*';
		
		if ($this->Acl->check($aro, $aco, $aclCheckAction)) {
			return true;
		} else {
			// debug($this->Acl->Aco->node($this->_getAcoPath()));
			// debug($this->Acl->Aro->node($this->_userAro($user['id'])));
			// debug($this->Acl->check($aro, $aco));
			// debug($user);
			// debug($this->Session->read());
			// debug($aro);
			// debug($aco);
			// break;
			
			//Sets the response status code to 401 needed for ajax calls
			//Otherwise it will return 200 calling any success callback
			$this->response->statusCode(401);
			
			$requestor = $aro['model'] . ' ' . $aro['foreign_key'];
			$requested = is_array($aco) ? $aco['model'] . ' ' . $aco['foreign_key'] : str_replace('/', ' ', $aco);
			$message = defined('__APP_DEFAULT_LOGIN_ERROR_MESSAGE') ? __APP_DEFAULT_LOGIN_ERROR_MESSAGE : 'does not have access to';
			$this->Session->setFlash(__('%s %s %s.', $requestor, $message, $requested));
			$this->redirect(array('plugin' => 'users', 'controller' => 'users', 'action' => 'restricted'));
		}
	}

/**
 * Gets the variables used to lookup the aco id for the action type of lookup
 * VERY IMPORTANT : If the aco is a record level type of aco (ie. model and foreign_key lookup) that means that all groups and users who have access rights must be defined.  You cannot have negative values for access permissions, and thats okay, because we deny everything by default.
 *
 * return {array || string}		The path to the aco to look up.
 */
	private function _getAcoPath() {
		if (!empty($this->request->params['pass'][0])) {
			// check if the record level aco exists first
			$aco = $this->Acl->Aco->find('count', array(
				'conditions' => array(
					'model' => $this->modelClass,
					'foreign_key' => $this->request->params['pass'][0]
					)
				));
		}
		if(!empty($aco)) {
			return array('model' => $this->modelClass, 'foreign_key' => $this->request->params['pass'][0]);
		} else {
			$controller = Inflector::camelize($this->request->params['controller']);
			$action = $this->request->params['action'];
			// $aco = 'controllers/Webpages/Webpages/view'; // you could do the full path, but the shorter path is slightly faster. But it does not allow name collisions. (the full path would allow name collisions, and be slightly slower).
			return $controller.'/'.$action;
		}
	}

/**
 * Gets the variables used for the lookup of the guest aro id
 */
	private function _guestsAro() {
		$guestsAro = array('model' => 'UserRole', 'foreign_key' => 5);
		if (defined('__SYSTEM_GUESTS_USER_ROLE_ID')) {
			$guestsAro = array('model' => 'UserRole', 'foreign_key' => __SYSTEM_GUESTS_USER_ROLE_ID);
		}
		return $guestsAro;
	}

/**
 * Gets the variables used for the lookup of the aro id
 */
	private function _userAro($userId) {
		$guestsAro = array('model' => 'User', 'foreign_key' => $userId);
		return $guestsAro;
	}

/**
 * Authentication method
 * 
 * @todo needs comments about why this function is here. Especially because it's not called in this controller.
 * @todo otherwise deprecate, remove references and delete this function.
 */
	public function authentication(){
		$this->layout = false;
		$this->autoRender = false;

		$data = ($this->request->data);
		$data['requireAuth'] = 0;
		$allowed = array_search($this->request->data['action'], $this->Auth->allowedActions);

		if ($allowed === 0 || $allowed > 0 ) {
			$this->Auth->allow('*');
			$data['requireAuth'] = 1;
		}
		echo json_encode($data);
	}


/**
 * Run cron method
 * 
 * Supposedly makes it so that any plugin can tie into the cron job that is run, but haven't tested 1/11/2012 RK
 * @todo remove references and delete this function 7/21/2013
 */
	public function runcron()	{
		$this->render(false);
	}
}