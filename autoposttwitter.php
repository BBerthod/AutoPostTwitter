<?php
if (!defined('_PS_VERSION_'))
  exit;

class AutoPostTwitter extends Module
{
  public function __construct()
  {
    $this->name = 'autoposttwitter';
    $this->tab = 'front_office_features';
    $this->version = '1.0';
    $this->author = 'Billy Berthod';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6.1.2');
    $this->dependencies = array('blockcart');

    parent::__construct();

    $this->displayName = $this->l('autoposttwitter');
    $this->description = $this->l('autoposttwitter');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('MYMODULE_NAME'))      
      $this->warning = $this->l('No name provided');

    if (!Configuration::get('MYMODULE_TWITTERAPIKEY'))      
      $this->warning = $this->l('No name provided');
  }

  public function getContent()
  {
    $output = null;

    if (Tools::isSubmit('submit'.$this->name))
    {
      $my_module_name = strval(Tools::getValue('MYMODULE_NAME'));
      $my_module_twitterapikey = strval(Tools::getValue('MYMODULE_TWITTERAPIKEY'));
      if (!$my_module_name  || empty($my_module_name) || !Validate::isGenericName($my_module_name))
        $output .= $this->displayError( $this->l('Invalid Configuration value') );
      elseif (!$my_module_twitterapikey  || empty($my_module_twitterapikey) || !Validate::isGenericName($my_module_twitterapikey))
        $output .= $this->displayError( $this->l('Invalid Configuration value') );
      else
      {
        Configuration::updateValue('MYMODULE_NAME', $my_module_name);
        Configuration::updateValue('MYMODULE_TWITTERAPIKEY', $my_module_twitterapikey);
        $output .= $this->displayConfirmation($this->l('Settings updated'));
      }
    }
    return $output.$this->displayForm();
  }

  public function displayForm()
  {
    // Get default Language
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
    $fields_form[0]['form'] = array(
      'legend' => array(
        'title' => $this->l('Settings'),
        ),
      'input' => array(
        array(
          'type' => 'text',
          'label' => $this->l('Configuration value'),
          'name' => 'MYMODULE_NAME',
          'size' => 20,
          'required' => true
          ),
        array(
          'type' => 'text',
          'label' => $this->l('TWITTERAPIKEY value'),
          'name' => 'MYMODULE_TWITTERAPIKEY',
          'size' => 150,
          'required' => true
          )
        ),
      'submit' => array(
        'title' => $this->l('Save'),
        'class' => 'button'
        )
      );



    $helper = new HelperForm();

    // Module, t    oken and currentIndex
    $helper->module = $this;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
    $helper->default_form_language = $default_lang;
    $helper->allow_employee_form_lang = $default_lang;

    // Title and toolbar
    $helper->title = $this->displayName;
    $helper->show_toolbar = true;        // false -> remove toolbar
    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
    $helper->submit_action = 'submit'.$this->name;
    $helper->toolbar_btn = array(
      'save' =>
      array(
        'desc' => $this->l('Save'),
        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
        '&token='.Tools::getAdminTokenLite('AdminModules'),
        ),
      'back' => array(
        'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
        'desc' => $this->l('Back to list')
        )
      );

    // Load current value
    $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
    $helper->fields_value['MYMODULE_TWITTERAPIKEY'] = Configuration::get('MYMODULE_TWITTERAPIKEY');

    return $helper->generateForm($fields_form);
  }

}


require_once('TwitterAPIExchange.php');
/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$settings = array(
    'oauth_access_token' => "2696463349-meob2jcqrtsoBgWRnGTfaqNs4dzGj4Ri3D6pw2v",
    'oauth_access_token_secret' => "bY31sBZHkbsUnEDbmkFcnggVGjGsFPl9Su6CeN7oGu6ht",
    'consumer_key' => "DwaIrBNnUBOvOepa4i27gM7iQ",
    'consumer_secret' => "uUwEnur8B0KMk3llUhKSXdBdKcLWzOxnGpUUJn7mJiGPjjgFfd"
);
$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
$requestMethod = "GET";
if (isset($_GET['user']))  {$user = $_GET['user'];}  else {$user  = "iagdotme";}
if (isset($_GET['count'])) {$count = $_GET['count'];} else {$count = 20;}
$getfield = "?screen_name=$user&count=$count";
$twitter = new TwitterAPIExchange($settings);
$string = json_decode($twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);
if($string["errors"][0]["message"] != "") {echo "<h3>Sorry, there was a problem.</h3><p>Twitter returned the following error message:</p><p><em>".$string[errors][0]["message"]."</em></p>";exit();}
foreach($string as $items)
    {
        echo "Time and Date of Tweet: ".$items['created_at']."<br />";
        echo "Tweet: ". $items['text']."<br />";
        echo "Tweeted by: ". $items['user']['name']."<br />";
        echo "Screen name: ". $items['user']['screen_name']."<br />";
        echo "Followers: ". $items['user']['followers_count']."<br />";
        echo "Friends: ". $items['user']['friends_count']."<br />";
        echo "Listed: ". $items['user']['listed_count']."<br /><hr />";
    }
?>