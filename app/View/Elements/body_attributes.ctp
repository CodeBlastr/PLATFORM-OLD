class="<?php
$bootstrapConflicts = array('media');
if ( in_array($this->request->params['controller'], $bootstrapConflicts) ) {
	echo $this->request->params['controller'] . 'Controller';
} else {
	echo $this->request->params['controller'];
}
echo $this->Session->read('Auth.User') ? __(' authorized') : __(' restricted');
echo ' ';
echo $this->request->params['action'];
echo ' ';
echo __('userRole%s', $userRoleId); ?>" id="<?php
echo !empty($this->request->params['pass'][0]) ? strtolower($this->request->params['controller'].'_'.$this->request->params['action'].'_'.$this->request->params['pass'][0]) : strtolower($this->request->params['controller'].'_'.$this->request->params['action']);
?>" lang="<?php echo Configure::read('Config.language'); ?>"