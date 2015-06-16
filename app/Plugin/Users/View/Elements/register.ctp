<div id="userRegister" class="user form">
  <?php echo $this->Form->create(null, array('url' => array('plugin' => 'users', 'controller' => 'users', 'action' => 'register'), 'type' => 'file'));?>
  <?php echo $this->Form->input('Contact.id', array('type' => 'hidden')); ?>  
  <fieldset>
    <?php 
		if(defined('__APP_DEFAULT_USER_REGISTRATION_CONTACT_TYPE')) { 
			echo $this->Form->input('User.contact_type', array('type' => 'hidden', 'value' => __APP_DEFAULT_USER_REGISTRATION_CONTACT_TYPE));
		} else {
			echo $this->Form->input('User.contact_type', array('type' => 'hidden', 'value' => 'person'));
		}
		echo !empty($userRoleId) ? $this->Form->hidden('User.user_role_id', array('value' => $userRoleId)) : $this->Form->input('User.user_role_id');
		echo $this->Form->input('User.full_name', array('label' => 'Name', 'placeholder' => 'Name'));
		//echo $this->Form->input('User.email', array('value' => ''));
		//echo $this->Form->input('User.avatar', array('type' => 'file', 'label' => 'Avatar'));
		echo $this->Form->input('User.username', array('label' => 'Email', 'placeholder' => 'Email'));
		echo $this->Form->input('User.password', array('value' => '', 'placeholder' => 'Password'));
		//echo $this->Form->input('User.confirm_password', array('type' => 'password', 'value' => ''));
		if(isset($this->request->params['named']['referal_code']) && !empty($this->request->params['named']['referal_code'])) {
		 	echo $this->Form->input('User.referal_code', array('type' => 'hidden', 'value' => $this->request->params['named']['referal_code']));
		}
	?>
  <?php echo $this->Form->end('Submit');?> 
  </fieldset>  
</div>