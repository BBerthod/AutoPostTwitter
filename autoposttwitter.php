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
$settings = array(
  'oauth_access_token' => "3437645865-wfxDPSR2tqSGMZP0qfSrV9usC5yfKFscGfhNhLH",
  'oauth_access_token_secret' => "i3uZ4SmmP5hcnE8wE465JocmrFRhLmt9inCNxwlCJkfiY",
  'consumer_key' => "FZM90efDxx3MZeOB5tJlwisvF",
  'consumer_secret' => "HFCdQFIjACVHpiNE4LiI9q1JeJv6JpkCloNDmI0pAc5S7Zk4PK"
  );

//On génere un id produit et on récupére le nom
function randomProductId(){
  $product_id = rand(543, 1055);
  $product = new Product($product_id);
  while (Product::getProductName($product_id) == null) {
   $product_id = rand(543, 1055);
   $product = new Product($product_id); 
 }
 return $product_id;
}

function testIfProductActive($product_id){
 $product = new Product($product_id);
 while($product->active == 0){
   $product_id = randomProductId();
   $product = new Product($product_id);
 }
 return $product_id;
}

function getProductInfo($product_id){
 $product = new Product($product_id);
 $name = Product::getProductName($product_id);
 $link = new Link();
 
 foreach ($product as $prod) {
  $product_link_rewrite = $prod['link_rewrite'];
  $product_id_product = $prod['id_product'];
}

$image = Image::getCover($product_id);
$imagePaths = $link->getImageLink($product->link_rewrite,  $image['id_image'], 'large_default'); 
$status = "" . $name . " http://vetementsportauto.fr//index.php?controller=product&id_product=".$product_id."";

return array($status, $imagePaths);
}

//On récupére un produit actif
$product_id = randomProductId();

$product_id = testIfProductActive($product_id);

//On génére le status twitter
// $status = getProductInfo($product_id);

list($status,$imagePaths) = getProductInfo($product_id);

//On tweet

require_once('tmhOAuth.php');

$str = substr($image, 23);

// url de l'image et nom de stockage temporaire
$tmp                 = 'tmp/'.uniqid().'.jpg';
// téléchargement et stockage de l'image
$imagePaths = substr_replace($imagePaths, 'http://', 0, 0).'/Array.jpg';

file_put_contents($tmp, file_get_contents($imagePaths));
// utilisation du "real path" de l'image
$image               = realpath($tmp);


$tmhOAuth = new tmhOAuth(array(
    'consumer_key'    => 'FZM90efDxx3MZeOB5tJlwisvF',
    'consumer_secret' => 'HFCdQFIjACVHpiNE4LiI9q1JeJv6JpkCloNDmI0pAc5S7Zk4PK',
    'user_token'      => '3437645865-wfxDPSR2tqSGMZP0qfSrV9usC5yfKFscGfhNhLH',
    'user_secret'     => 'i3uZ4SmmP5hcnE8wE465JocmrFRhLmt9inCNxwlCJkfiY'
));

$code = $tmhOAuth->request(
    'POST',
    $tmhOAuth->url('1.1/statuses/update_with_media'),
    array(
        'media[]'  => "@{$image}",
        'status'   => $status 
        // 'status'   => phpinfo(INFO_CONFIGURATION)
    ),
    true, // use auth
    true  // multipart
);


unlink($tmp);