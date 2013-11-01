<?php
App::uses('Sanitize', 'Utility');
/**
 * Settings Model
 *
 * This database table contains all of the settings for the site, but its important to note that it is never called on by the application.  Instead every time you create or edit a setting, it updates a static file in the config folder called settings.ini.  This is done to make performance fast, even with hundreds of settings.
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
 * @subpackage    zuha.app.models
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 * @todo      Make it so that we list all of the settings available, and only if they have a value do we write it to the ini file.
 */
class Setting extends AppModel {

	public $name = 'Setting';

	/**
	 * instead of storing available settings in a database we store all of the available settings here
	 */
	public $settings = array();

/**
 * 
 * @param type $id
 * @param type $table
 * @param type $ds
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['displayName'] = sprintf('CONCAT(%s.type, " : ", %s.name)', $this->alias, $this->alias);
		$this->displayField = 'displayName';
		$settings = array(
			'System' => array(
				array(
					'name' => 'GUESTS_USER_ROLE_ID',
					'description' => 'Defines the user role the system should use for guest access. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '5',
				),
				array(
					'name' => 'SMTP',
					'description' => 'Defines email configuration settings so that sending email is possible. Please note that these values will be encrypted during entry, and cannot be retrieved.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'smtpUsername = xyz@example.com' . PHP_EOL . 'smtpPassword = "XXXXXXX"' . PHP_EOL . 'smtpHost = mail.example.com' . PHP_EOL . 'smtpPort = 465' . PHP_EOL . 'from = myemail@example.com' . PHP_EOL . 'fromName = "My Name"',
				),
				array(
					'name' => 'ZUHA_DB_VERSION ',
					'description' => 'Defines the current version of the database.  Used to determine if an upgrade is needed. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '0.0123',
				),
				array(
					'name' => 'LOAD_PLUGINS',
					'description' => 'Defines the plugins that should be loaded. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'plugins[] = Webpages' . PHP_EOL . 'plugins[] = Contacts' . PHP_EOL . 'plugins[] = Search',
				),
			),
			'Transactions' => array(
				array(
					'name' => 'DEFAULT_PAYMENT',
					'description' => 'Defines default payment option for the site. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'AUTHORIZE',
				),
				array(
					'name' => 'ENABLE_PAYMENT_OPTIONS',
					'description' => 'Defines the options, in order, which will be shown in the dropdown of payment options for the app. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'AUTHORIZE = Authorize' . PHP_EOL . 'AUTHORIZEONLY = "Authorize Only"' . PHP_EOL . 'PAYPAL.ACCOUNT = Paypal' . PHP_EOL . 'CREDIT = Credit' . PHP_EOL . 'BLUEPAY.CC = CREDIT' . PHP_EOL . 'BLUEPAY.ACH = CHECK'
				),
				array(
					'name' => 'AUTHORIZENET_LOGIN_ID',
					'description' => 'Defines the login to access payment api of Authorize.net. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '463h3f98f4u89',
				),
				array(
					'name' => 'AUTHORIZENET_TRANSACTION_KEY',
					'description' => 'Defines the transaction key to access payment api of Authorize.net. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '48fj0j2389ur02983ur',
				),
				array(
					'name' => 'AUTHORIZENET_MODE',
					'description' => 'Defines whether authorize.net is in test mode.  Any value at all, means its in test mode, otherwise it is live. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '1',
				),
				array(
					'name' => 'SAGEPAY_VENDOR',
					'description' => 'The Vendor Name that you registered at SagePay.  Default is razorit which is a simulation account.' . 'Default value : razorit',
				),
				array(
					'name' => 'SAGEPAY_CURRENCY',
					'description' => 'The currency to use for SagePay. gbp/usd' . PHP_EOL . PHP_EOL . 'Default value : usd',
				),
				array(
					'name' => 'SAGEPAY_MODE',
					'description' => 'The mode to run SagePay in. SIMULATOR/DEVELOPMENT/LIVE' . PHP_EOL . PHP_EOL . 'Default value : SIMULATOR',
				),
				array(
					'name' => 'SAGEPAYMENTS_MERCHANT_ID',
					'description' => 'The Merchant ID for SagePayments' . PHP_EOL . PHP_EOL . 'Default value : empty',
				),
				array(
					'name' => 'SAGEPAYMENTS_MERCHANT_KEY',
					'description' => 'The Merchant Key for SagePayments' . PHP_EOL . PHP_EOL . 'Default value : empty',
				),
				array(
					'name' => 'PAYSIMPLE',
					'description' => 'environment = sandbox' . PHP_EOL . 'apiUsername = APIUserXXXXX' . PHP_EOL . 'sharedSecret = FdNcOBCgngMkvJ...'
				),
				array(
					'name' => 'BLUEPAY',
					'description' => 'mode = TEST OR LIVE' . PHP_EOL . 'accountId = 99999999999' . PHP_EOL . 'secretKey = FdNcOBCgngMkvJ...'
				),
				array(
					'name' => 'PAYPAL',
					'description' => 'Defines the credentials to Access Paypal Payment PRO : https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-api-add-direct-access.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'API_USERNAME = webpro_126328478_biz_api1.example.com' . PHP_EOL . 'API_PASSWORD = 9294399233' . PHP_EOL . 'API_SIGNATURE = ApJtg.JrUW0YLN.tPmmGiu-exM.va778w7f873mX29QghYJnTf' . PHP_EOL . 'API_ENDPOINT = https://api-3t.sandbox.paypal.com/nvp' . PHP_EOL . 'PROXY_HOST = 127.0.0.1' . PHP_EOL . 'PROXY_PORT = 808' . PHP_EOL . 'PAYPAL_URL = "https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token="' . PHP_EOL . 'VERSION  = 51.0' . PHP_EOL . 'USE_PROXY = "FALSE"',
				),
				array(
					'name' => 'PAYPAL_ADAPTIVE',
					'description' => 'Defines the credentials to Access payment api of Paypal for Adaptive payment methods.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'API_USERNAME = pro2_1306331130_biz_api1.enbake.com' . PHP_EOL . 'API_PASSWORD = 1306331152' . PHP_EOL . 'API_SIGNATURE = A8p31ikyPTksXuHA3gAY-vp4j5.uAaEj4E89F8jscaqMIfjpaXVNe4cJ' . PHP_EOL . 'API_ENDPOINT = https://svcs.sandbox.paypal.com/AdaptivePayments' . PHP_EOL . 'PROXY_HOST = 127.0.0.1' . PHP_EOL . 'PROXY_PORT = 808' . PHP_EOL . 'PAYPAL_URL = "https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token="' . PHP_EOL . 'VERSION  = 51.0' . PHP_EOL . 'USE_PROXY = "FALSE"',
				),
				array(
					'name' => 'CHAINED_PAYMENT',
					'description' => 'Defines the values to Access chained payment of Paypal.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'returnUrl = "http://xyz.zuha.com"' . PHP_EOL . 'cancelUrl = "http://xyz.zuha.com"' . PHP_EOL . 'receiverPrimaryArray[] = ""' . PHP_EOL . 'receiverInvoiceIdArray[] = ""' . PHP_EOL . 'feesPayer = ""' . PHP_EOL . 'ipnNotificationUrl = ""' . PHP_EOL . 'memo = ""' . PHP_EOL . 'pin = ""' . PHP_EOL . 'preapprovalKey = ""' . PHP_EOL . 'reverseAllParallelPaymentsOnError = ""' . PHP_EOL . 'senderEmail = "pro2_1306331130_biz@example.com"',
				),
				array(
					'name' => 'CHECKOUT_REDIRECT',
					'description' => 'Defines where to redirect to after a successful checkout.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'url = "/thank-you/" ' . PHP_EOL . PHP_EOL . ' OR ' . PHP_EOL . PHP_EOL . 'model = Member' . PHP_EOL . 'action = set_paid_user_role' . PHP_EOL . 'pass[] = catalog_item_id',
				),
				array(
					'name' => 'LOCATIONS',
					'description' => 'Defines the users to whom the payment should divided using chained payment.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'syracuse[] =  40,syracuse@example.com' . PHP_EOL . 'syracuse[] = 40,adagency@example.com',
				),
				array(
					'name' => 'ENABLE_SHIPPING',
					'description' => 'Defines the shipping option Enable/Disable for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'false',
				),
				array(
					'name' => 'SHIPPING_FEDEX_USER_CREDENTIAL',
					'description' => 'Defines the shipping fedex user credentials for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'UserCredential["Key"] = "BWw8o4cRu1z7NZZU"' . PHP_EOL . 'UserCredential["Password"] = "CjV3icwSEDDpgFiTFweIkaEAc"',
				),
				array(
					'name' => 'SHIPPING_FEDEX_CLIENT_DETAIL',
					'description' => 'Defines the shipping fedex client credentials for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'AccountNumber = "510087585"' . PHP_EOL . 'MeterNumber = "100061554"',
				),
				array(
					'name' => 'SHIPPING_FEDEX_VERSION',
					'description' => 'Defines the shipping fedex version.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'ServiceId = "crs"' . PHP_EOL . 'Major = 9' . PHP_EOL . 'Intermediate = 0' . PHP_EOL . 'Minor = 0',
				),
				array(
					'name' => 'SHIPPING_FEDEX_REQUESTED_SHIPMENT_SHIPPER',
					'description' => 'Defines the shipping fedex default ship from address for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'Address["City"] = ""' . PHP_EOL . 'Address["StateOrProvinceCode"] = "CA"' . PHP_EOL . 'Address["PostalCode"] = "95451"' . PHP_EOL . 'Address["CountryCode"] = "US"',
				),
				array(
					'name' => 'SHIPPING_FEDEX_REQUESTED_SHIPMENT',
					'description' => 'Defines the shipping fedex settings for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'DropoffType = REGULAR_PICKUP' . PHP_EOL . 'ServiceType = FEDEX_GROUND' . PHP_EOL . 'PackagingType = YOUR_PACKAGING' . PHP_EOL . 'RateRequestTypes = ACCOUNT' . PHP_EOL . 'RateRequestTypes = LIST' . PHP_EOL . 'PackageDetail = INDIVIDUAL_PACKAGES',
				),
				array(
					'name' => 'SHIPPING_FEDEX_WEIGHT_UNIT',
					'description' => 'Defines the shipping fedex weight unit for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'LB',
				),
				array(
					'name' => 'SHIPPING_FEDEX_DIMENSIONS_UNIT',
					'description' => 'Defines the shipping fedex dimension unit for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'IN',
				),
				array(
					'name' => 'SHIPPING_FEDEX_DEFAULT_WEIGHT',
					'description' => 'Defines the shipping fedex default weight if the weight is not givven for item for the site.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '2.0',
				),
				array(
					'name' => 'FEDEX',
					'description' => 'Define Enabled Shipping Service options the following variable defines the options which should be display  in the dropdown of shipping type for the app.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'GROUNDHOMEDELIVERY = STANDARD' . PHP_EOL . 'INTERNATIONALFIRST = "INTERNATIONAL FLAT FEE"' . PHP_EOL . 'FEDEX1DAYFREIGHT = "NEXT DAY"',
				),
				array(
					'name' => 'FLAT_SHIPPING_RATE',
					'description' => 'Define Flat Shipping Rate for the complete order.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '99',
				),
				array(
					'name' => 'SSL',
					'description' => 'Defines whether to use ssl during the checkout, and you can place some html to show trust logos.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'ssl = 1' . PHP_EOL . 'trustLogos = "enter some html to use in the trust logos area"',
				),
				array(
					'name' => 'ENABLE_SINGLE_PAYMENT_TYPE',
					'description' => 'Defines whether to use single payment method for payment.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'true',
				),
				array(
					'name' => 'ENABLE_GUEST_CHECKOUT',
					'description' => 'Defines whether or not guest checkout is enabled.' . PHP_EOL . PHP_EOL . 'Default Value : ' . PHP_EOL . 'false',
				),
				array(
					'name' => 'RECEIPT_EMAIL', 
					'description' => 'Sets the email content for a transaction receipt.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'subject = "Some Subject"' . PHP_EOL . 'body = "Some body of the email"',
				)
			),
			'App' => array(
				array(
					'name' => 'LOGIN_ACTION',
					'description' => 'Defines where users will be redirected to if they reach a page they do not have access to.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '/some-page',
				),
				array(
					'name' => 'DEFAULT_USER_REGISTRATION_ROLE_ID',
					'description' => 'Defines the role users will be assigned by default when they register as a new users at yourdomain.com/users/users/register.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '3',
				),
				array(
					'name' => 'DEFAULT_LOGIN_ERROR_MESSAGE',
					'description' => 'Defines the message visitors see if they are not logged in and reach a restricted page. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '"Please become a registered user to access that feature."',
				),
				array(
					'name' => 'TEMPLATES',
					'description' => 'Defines which user roles and urls templates will be used at. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'template[] = A Serialized Array : You must use the template edit pages to set',
				),
				array(
					'name' => 'LOAD_APP_HELPERS',
					'description' => 'Defines which helpers should be loaded and when they should be loaded. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'Menu',
				),
				array(
					'name' => 'LOGIN_REDIRECT_URL',
					'description' => 'Defines the url users go to after logging in. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '/tickets/tickets/add/' . PHP_EOL . PHP_EOL . 'or' . PHP_EOL . PHP_EOL . '1 = /projects/' . PHP_EOL . '3 = /tickets/' . PHP_EOL . PHP_EOL . 'The numbers in the second example are user role id\'s. Used if you want different user roles redirected to different places after login.',
				),
				array(
					'name' => 'LOGOUT_REDIRECT_URL',
					'description' => 'Defines the url users go to after logging out. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '/goodbye/',
				),
				array(
					'name' => 'REGISTRATION_EMAIL_VERIFICATION',
					'description' => 'Defines whether registration requires email verification before the account is approved. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'anything (If this setting exists at all, then verification is required.)',
				),
				array(
					'name' => 'MEMBERSHIP_CATALOG_ITEM_REDIRECT',
					'description' => 'Defines the url for new regiter members to choose a membership plan. ' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '/catalogs/catalog_items/view/48',
				),
			),
			'Reports' => array(
				array(
					'name' => 'ANALYTICS',
					'description' => 'Defines the Google Analytics information for tracking traffic and displaying reports.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'setAccount = UA-999999-9' . PHP_EOL . 'setDomainName = .domain.com' . PHP_EOL . 'userName = google@account-login.com' . PHP_EOL . 'password = mySecurePassword',
				),
			),
			'Invoices' => array(
				array(
					'name' => 'DEFAULT_INTRODUCTION',
					'description' => 'Defines the default notes to clients when creating an invoice. Can be easily over written during invoice creation.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '"Thank you for your business."',
				),
				array(
					'name' => 'DEFAULT_CONCLUSION',
					'description' => 'Defines the default conclusion, or invoice terms to clients when creating an invoice. Can be easily over written during invoice creation.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '"Due in Net 30. Mail your payment here, etc..."',
				),
				array(
					'name' => 'DEFAULT_RATE',
					'description' => 'Defines the default hourly rate for invoices.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '18.50',
				),
				array(
					'name' => 'EMAIL_TEMPLATES',
					'description' => 'Defines the email message templates for use when sending emails about invoices to invoice recipients.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . PHP_EOL . 'template[] = "<p>Hello, </p><p>Thank you for your business. Please view your invoice by visiting this link : </p>',
				),
			),
			'Galleries' => array(
				array(
					'name' => 'SETTINGS',
					'description' => 'Defines the settings for all galleries that will appear on the site. The entire __GALLERY tree is deprecated and will be removed in the future, please use this.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'galleryType = "zoomable"' . PHP_EOL . 'smallImageWidth = 50' . PHP_EOL . 'smallImageHeight = 120' . PHP_EOL . 'mediumImageWidth = 200' . PHP_EOL . 'mediumImageHeight = 250' . PHP_EOL . 'largeImageWidth = 600' . PHP_EOL . 'largeImageHeight = 450' . PHP_EOL . 'conversionType = "resizeCrop"' . PHP_EOL . 'indexImageWidth = 100' . PHP_EOL . 'indexImageHeight = 100',
				),
			),
			'Element' => array(
				array(
					'name' => 'PROJECTS_MOST_WATCHED',
					'description' => 'Defines setting variables for the most watched module.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'moduleTitle = "My Custom Title"' . PHP_EOL . 'numberOfProjects = 5',
				),
				array(
					'name' => 'BLOGS_LATEST',
					'description' => 'Defines setting variables for the latest blog posts module.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'moduleTitle = "My Custom Title"' . PHP_EOL . 'numberOfPosts = 5' . PHP_EOL . 'blogID = 1',
				),
				array(
					'name' => 'USERS_LOGIN',
					'description' => 'Customize the User Login elements.'
					. PHP_EOL . PHP_EOL
					. 'Default Values : ' . PHP_EOL
					. 'divId = "loginElement"' . PHP_EOL
					. 'divClass = "loginElement"' . PHP_EOL
					. 'textWelcome = "Welcome : "' . PHP_EOL
					. 'textRegister = "Register"' . PHP_EOL
					. 'linkRegisterUrl = "/usrs/users/register"' . PHP_EOL
					. 'textLogIn = "Login"' . PHP_EOL
					. 'linkLoginUrl = "/users/users/login"' . PHP_EOL
					. 'textLogOut = "Logout"' . PHP_EOL
					. 'textSeparator = "-"' . PHP_EOL
					. 'linkClass = "loginLink"' . PHP_EOL
					. 'linkIdUser = "useridLink"' . PHP_EOL
					. 'linkIdLogin = "useridLink"' . PHP_EOL
					. 'linkIdLogout = "logoutLink"' . PHP_EOL
					. 'linkIdSignUp = "signupLink"' . PHP_EOL
					. 'linkIdSignIn = "signinLink"' . PHP_EOL
				),
			),
    		'Forms' => array(
				array(
					'name' => 'KEYS',
					'description' => 'Defines keys to bypass form security.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'key[] = "gf2398f989a8j9823987923"' . PHP_EOL . 'key[] = "jhaksf283787aj9j298aj9f82j"',
				),
			),
			'Media' => array(
				array(
					'name' => 'MEDIA_SORTED',
					'description' => 'Defines settings for the Media Sorted module.' . PHP_EOL . 'The Media Sorted module can return a UL of any number of results of Media of either Type, sorted ASC or DESC, based on any field in the Media table.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . 'mediaType = "video"' . PHP_EOL . 'field = "rating"' . PHP_EOL . 'sort = "DESC"' . PHP_EOL . 'numberOfResults = 5',
				),
			),
			'Users' => array(
				array(
					'name' => 'PAID_EXPIRED_ROLE_ID',
					'description' => 'Defines setting variables for the expired user role id.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '3',
				),
				array(
					'name' => 'PAID_ROLE_ID',
					'description' => 'Defines setting variables for the paid user role id.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '1',
				),
				array(
					'name' => 'PAID_ROLE_REDIRECT',
					'description' => 'Defines setting variables for the paid user role redirect.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '/users/users/my',
				),
				array(
					'name' => 'NEW_REGISTRATION_CREDITS',
					'description' => 'Defines setting variables for credits given to referal user on new user registration.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '5',
				),
				array(
					'name' => 'CREDITS_PER_PRICE_UNIT',
					'description' => 'Defines setting variables for credits given to user on purchase of credits.' . PHP_EOL . PHP_EOL . 'Example value : ' . PHP_EOL . '5',
				),
			),
			'Connections' => array(
				array(
					'name' => 'PAYPAL',
					'description' => 'Defines testing environment. sandbox OR live' . PHP_EOL
					. PHP_EOL . 'environment = sandbox'
					. PHP_EOL . 'apiUsername = asdg33qb5haeh'
					. PHP_EOL . 'apiPassword = 12345'
					. PHP_EOL . 'apiSignature = 32jdnrjfmasdfk33qb5haeh'
					. PHP_EOL . 'apiAppId = 42x'
					. PHP_EOL . 'apiPermissionScope[] = ACCOUNT_BALANCE'
					. PHP_EOL . 'chainedPrimaryEmail = payMe1st@example.com'
					. PHP_EOL . 'chainedPrimaryPercentage = 2'
				),
				array(
					'name' => 'PAYPAL_API_USERNAME',
					'description' => 'The API username to be used to connect to the PayPal API.'
				),
				array(
					'name' => 'PAYPAL_API_PASSWORD',
					'description' => 'The password for the PayPal API uesrname.'
				),
				array(
					'name' => 'PAYPAL_API_SIGNATURE',
					'description' => 'The API Signature to connect to the PayPal API.'
				),
				array(
					'name' => 'PAYPAL_APP_ID',
					'description' => 'Your PayPal App ID.'
				),
				array(
					'name' => 'PAYPAL_PERMISSION_SCOPE',
					'description' => 'A string or array of permssions to request from the PayPal user.'
				)
			),
			'Favorites' => array(
				array(
					'name' => 'FAVORITES_SETTINGS',
					'description' => 'Favorites Plugin Settings' . PHP_EOL
					. PHP_EOL . 'types[favorite] = "Post"'
					. PHP_EOL . 'types[watch] = "Post"'
					. PHP_EOL . 'defaultTexts[favorite] = "Favorite it"'
					. PHP_EOL . 'defaultTexts[watch] = "Watch it"'
					. PHP_EOL . 'modelCategories[] = "Post"'
					,
				),
			),
			'Ratings' => array(
				array(
					'name' => 'RATINGS_SETTINGS',
					'description' => 'Ratings Plugin Settings, used to set the values to rate be' . PHP_EOL
					. PHP_EOL . '0 = "Hate"'
					. PHP_EOL . '1 = "OK"'
					. PHP_EOL . '2 = "Like"'
					,
				),
			),
		);
		ksort($settings);
		$this->settings = $settings;
	}

/**
 * 
 * @param type $results
 * @param type $primary
 * @return type
 */
	public function afterFind($results, $primary = false) {
		$i = 0;
		if (!empty($results[$i]['Setting'])) {
			foreach ($results as $result) {
				$results[$i]['Setting']['displayName'] = Inflector::humanize(strtolower($result['Setting']['displayName']));
				$i++;
			}
		}
		return $results;
	}

/**
 * Handles the saving of settings data to the settings.ini file
 *
 * necessary $data value examples
 * $data['Setting']['type'] = Plugin
 * $data['Setting']['name'] = RATINGS_SETTINGS
 * $data['Setting']['value'] = some string
 * 
 * @param {data}    An array contain the setting data
 * @param {bool}    If set to true, it will add to the value instead of replace.
 * @return {bool}    True if the settings were saved and the file was created.
 */
	public function add($data, $append = false) {
		$data = $this->_cleanSettingData($data, $append);
		if ($this->saveAll($data)) {
			// call all settings and write the ini file
			if ($this->writeSettingsIniData()) {
				return true;
			} else {
				// roll back
				$this->delete($this->id);
				throw new Exception('Config directory and/or defaults.ini directory is not writeable');
			}
		}
	}

/**
 * This function sets up the data from the settings table so that it will write a whole new file each time a setting is saved.
 *
 * @return {string}    A string of data used to write to the settings.ini file.
 */
	public function prepareSettingsIniData() {
		$settings = $this->find('all');
		$writeData = '; Do not edit this file, instead go to /admin/settings and edit or add settings' . PHP_EOL . PHP_EOL;
		foreach ($settings as $setting) {
			if (strpos($setting['Setting']['value'], '\=') || !strpos($setting['Setting']['value'], '=')) {
				$setting['Setting']['value'] = str_replace('\=', '=', $setting['Setting']['value']);
				$writeData .= '__';
				$writeData .= strtoupper($setting['Setting']['type']);
				$writeData .= '_';
				$writeData .= strtoupper($setting['Setting']['name']);
				$writeData .= ' = ';
				$writeData .= $setting['Setting']['value'];
				$writeData .= PHP_EOL;
			} else {
				$holdSettings[] = $setting;
			}
		}
		$writeData .=!empty($holdSettings) ? $this->finishIniData($holdSettings) : '';
		return $writeData;
	}

/**
 * We need to make sure that ini sections appear after all straight values in the ini file
 * 
 * @param array $settings
 * @return string
 */
	public function finishIniData($settings) {
		$writeData = '';
		foreach ($settings as $setting) {
			$writeData .= PHP_EOL . '[__';
			$writeData .= strtoupper($setting['Setting']['type']);
			$writeData .= '_';
			$writeData .= strtoupper($setting['Setting']['name']) . ']' . PHP_EOL;
			$writeData .= $setting['Setting']['value'];
			$writeData .= PHP_EOL . PHP_EOL;
		}
		return $writeData;
	}

/**
 * This function sets up the data from the settings table so that it will write a whole new file each time a setting is saved.
 *
 * @return {string}    A string of data used to write to the settings.ini file.
 */
	public function writeSettingsIniData($siteDir = null) {
		$directory = !empty($siteDir) ? ROOT . DS . $siteDir . DS . 'Config' . DS : CONFIGS;
		App::uses('File', 'Utility');
		$file = new File($directory . 'settings.ini');
		$writeData = $this->prepareSettingsIniData();

		if ($file->write($file->prepare($writeData))) {
			if ($this->writeDefaultsIniData()) {
				return true;
			} else {
				throw new Exception(__('Defaults.ini write to %s failed', $directory));
			}
		} else {
			throw new Exception(__('Settings.ini write to %s failed', $directory));
		}
	}

/**
 * This function writes the defaults.ini file, assuming that it is because the settings.ini has been fully upgraded to the latest version.
 *
 * @return {string}    A string of data used to write to the settings.ini file.
 */
	public function writeDefaultsIniData($siteDir = null) {
		$directory = !empty($siteDir) ? ROOT . DS . $siteDir . DS . 'Config' . DS : CONFIGS;
		App::uses('File', 'Utility');
		$file = new File($directory . 'defaults.ini');

		$writeData = $this->prepareSettingsIniData();
		if ($file->write($file->prepare($writeData))) {
			return true;
		} else {
			throw new Exception(__('Write defaults.ini to %s failed', $directory));
		}
	}

/**
 * Checks whether the setting already exists and cleans the data array if it does.
 * This is used mainly by outside of the model functions which don't know if the Setting exists or not.
 *
 * @param {array}    An array of Setting data
 */
	private function _cleanSettingData($data, $append = false) {
		
		if (is_array($data['Setting']['value'])) {
			$settingValue = '';
			foreach ($data['Setting']['value'] as $key => $value) {
				if (is_array($value)) {
					// Form->input('Setting.value.variable.key')
					// turns into variable[key] = value
					foreach ($value as $index => $val) {
						$settingValue .= __('%s[%s] = "%s"%s', $key, $index, Sanitize::escape($val), PHP_EOL);
					}
				} else {
					// Form->input('Setting.value.variable)
					// turns into variable = value
					$settingValue .= __('%s = "%s"%s', $key, Sanitize::escape($value), PHP_EOL);
				}
			}
			$data['Setting']['value'] = $settingValue;
		}
		if (!empty($data['Setting'][0])) {
			$i = 0;
			foreach ($data['Setting'] as $setting) {
				if (is_array($setting['value'])) {
					$newValue = null;
					foreach ($setting['value'] as $key => $value) {
						$newValue .= is_numeric($value) ? $key . ' = ' . $value . '' . PHP_EOL : $key . ' = "' . $value . '"' . PHP_EOL;
					} // end value loop
				} else {
					$newValue = $setting['value'];
				}
				$data['Setting'][$i]['value'] = $newValue;
				$i++;
			} // end setting loop
			$data = $data['Setting']; // because we are using saveAll
		}

		// @todo break these out into individual setting function in a foreach loop that will 
		// handle many and single records to save
		if (!empty($data['Setting']['name']) && !empty($data['Setting']['type'])) {
			// see if the setting already exists
			$setting = $this->find('first', array(
				'conditions' => array(
					'Setting.name' => $data['Setting']['name'],
					'Setting.type' => $data['Setting']['type'],
					),
				));
			if (!empty($setting)) {
				// if it does, then set the id, so that we over write instead of creating a new setting
				$data['Setting']['id'] = $setting['Setting']['id'];
			}

			if (!empty($append) && !empty($setting)) {
				$data['Setting']['value'] = $setting['Setting']['value'] . PHP_EOL . $data['Setting']['value'];
			}
		}

		// some values need to be encrypted.  We do that here (@todo put this in its own two 
		// functions.  One for "encode" function, and one for which settings should be encoded, 
		// so that we can specify all settings which need encryption, and reuse this instead 
		// of the if (xxxx setting) thing.  And make the corresponding decode() function somehwere as well.
		if (!empty($data['Setting']['name']) && $data['Setting']['name'] == 'SMTP' && !parse_ini_string($data['Setting']['name'])) {
			$data['Setting']['value'] = 'smtp = "' . base64_encode(Security::cipher($data['Setting']['value'], Configure::read('Security.iniSalt'))) . '"';
		}

		if (!empty($data['Query']) && $data['Setting']['name'] == 'ZUHA_DB_VERSION') {
			$data['Setting']['value'] = $data['Setting']['value'] + 0.0001;
		}


		return $data;
	}

/**
 * All of the system settings possible belong here.
 *
 * @param {string}
 * @return {array}
 */
	public function getNames($typeName = null) {
		if (!empty($typeName)) {
			//$preFix = Zuha::enum($typeName);
			return $this->settings[$typeName];
		}

		/* This is a really helpful piece of code, but I don't know where to put it for reuse
		 * because the $this part doesn't work in global.php.  I mean I know we could pass the
		 * model over to it and import, I just didn't have time today.
		 * It prints out the last query run.
		  $dbo = $this->getDatasource();
		  $logs = $dbo->_queriesLog;
		  debug(end($logs)); */
	}

/**
 * Return the description for a particular setting
 *
 * @param {string}    A string of the setting type
 * @param {string}    A string containing the setting name
 * @return {string}    Text description
 */
	public function getDescription($typeName, $name) {
		foreach ($this->settings[$typeName] as $setting) {
			if ($setting['name'] == $name) {
				$description = $setting['description'];
			}
		}
		return $description;
	}

/**
 * An options array suitable for select form input
 *
 * @return {array}
 */
	public function types() {
		foreach ($this->settings as $key => $value) :
			$types[$key] = $key;
		endforeach;

		return $types;
	}

/**
 * returns settings in a way that can be parsed into an editable form
 *
 * @param {string}   same as find()
 * @options {array}  same as find()
 * @return {array}
 */
	public function getFormSettings($type = 'all', $params = array()) {
		$settings = $this->find($type, $params);

		$i = 0;
		foreach ($settings as $key => $setting) {
			$settings['Setting'][$i] = $setting['Setting'];
			$settings['Setting'][$i]['value'] = $this->_settingFormInputs($setting['Setting']['type'], $setting['Setting']['name'], $setting['Setting']['value']);
			unset($settings[$i]);
			$i++;
		}
		return $settings;
	}

/**
 * @todo  Ha, convoluted enough?  This like all the settings need to be available if and only if the plugin is loaded, 
 * and then they should get the available properties using a standardized callback to the individual plugin.   
 * Maybe something like, Galleries.Config.settings... 
 * Configure::write('SETTINGS', array('galleryType' =>  array('description' => 'xyz', 'formInput' => array('type' => 'select', etc.)));
 * @todo ^^^^^^^^^^^ DO THIS BEFORE YOU PUT TOO MANY SETTINGS HERE ^^^^^^^^^^^^^
 */
	private function _settingFormInputs($type, $name, $value) {
		if (strpos($value, '=')) {
			$value = parse_ini_string($value);
			foreach ($value as $key => $val) {
				switch ($type) {
					case 'Galleries' :
						switch ($name) {
							case 'SETTINGS' :
								switch ($key) {
									case 'galleryType' :
										App::uses('Gallery', 'Galleries.Model');
										$Gallery = new Gallery;
										$value[$key] = array(
											'value' => $val,
											'type' => 'select',
											'options' => $Gallery->types()
										);
										break;
									case 'conversionType' :
										App::uses('Gallery', 'Galleries.Model');
										$Gallery = new Gallery;
										$value[$key] = array(
											'value' => $val,
											'type' => 'select',
											'options' => $Gallery->conversionTypes());
										break;
									default :
										// @todo I don't want this to be in every default level case
										// if it doesn't have to be
										$value[$key] = array('value' => $val);
								}
								break;
							default :
								$value[$key] = array('value' => $val);
						}
						break;
					default :
						$value[$key] = array('value' => $val);
				}
			}
		}
		return $value;
	}

}