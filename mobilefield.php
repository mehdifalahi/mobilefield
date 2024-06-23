<?php 


use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Customer\Exception\CustomerException;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Search\Filters\CustomerFilters;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

if (!defined('_PS_VERSION_'))
	exit;



class mobilefield extends Module
{


    public function __construct()
    {
        $this->name = 'mobilefield';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->module_key = '';

        $this->author = 'Mehdi Falahi';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Mobile Field';
        $this->description = '';
		$this->module_dir = '../../../../modules/'.$this->name.'/';
		$this->id_lang = (int)Context::getContext()->language->id;
	}
	
	
		
	public function install()
	{
		return parent::install() &&
			$this->registerHook('actionCustomerGridDefinitionModifier') &&
			$this->registerHook('actionCustomerGridQueryBuilderModifier') &&
			$this->registerHook('actionCustomerFormBuilderModifier') &&
			$this->registerHook('actionAfterCreateCustomerFormHandler') &&
			$this->registerHook('actionAfterUpdateCustomerFormHandler') &&
			$this->registerHook('additionalCustomerFormFields') &&
			$this->registerHook('actionObjectCustomerUpdateAfter') &&
			$this->registerHook('actionObjectCustomerAddAfter') &&
			$this->registerHook('header') &&
			$this->installTables()
		;
	}
	

    private function installTables()
    {
        $sql = '			
			CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . 'mobilefield` (
			  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
			  `mobile` varchar(100) NOT NULL,
			  `country` varchar(100) NOT NULL,
			  `code` varchar(100) NOT NULL,			  
			  PRIMARY KEY (`customer_id`),
			  KEY `mobile` (`mobile`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;			
        ';

        return Db::getInstance()->execute($sql);
    }		




    public function hookActionCustomerFormBuilderModifier(array $params)
    {

        $formBuilder = $params['form_builder'];
        $formBuilder->add('mobile', TextType::class, [
            'label' => $this->l('Mobile'),
            'required' => false,
        ]);	
        $formBuilder->add('mobile_code', TextType::class, [
            'label' => $this->l('Mobile Code'),
            'required' => false,
        ]);	
        $formBuilder->add('mobile_country', TextType::class, [
            'label' => $this->l('Mobile Country'),
            'required' => false,
        ]);	

        $customerId = $params['id'];
		
		$row = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'mobilefield`
			WHERE `customer_id` = '.(int)$customerId);			
		
        $params['data']['mobile'] = $row['mobile'];
        $params['data']['mobile_code'] = $row['code'];
        $params['data']['mobile_country'] = $row['country'];
        $formBuilder->setData($params['data']);
    }




	public function hookActionAfterUpdateCustomerFormHandler(array $params)
	{
		$this->updateCustomerDate($params);
	}


	public function hookActionAfterCreateCustomerFormHandler(array $params)
	{
		$this->updateCustomerDate($params);
	}


	private function updateCustomerDate(array $params)
	{
		$customerId = $params['id'];
		$customerFormData = $params['form_data'];
		$mobile 		= $customerFormData['mobile'];
		$code 	= $customerFormData['mobile_code'];
		$country = $customerFormData['mobile_country'];
		
		Db::getInstance()->Execute("DELETE FROM `" . pSQL(_DB_PREFIX_) . "mobilefield` WHERE customer_id = " . (int)$customerId);
		Db::getInstance()->Execute("INSERT INTO `" . pSQL(_DB_PREFIX_) . "mobilefield` (customer_id, mobile, country, code) VALUES (".(int)$customerId.", '$mobile', '$country', '$code')");
	}


    public function hookactionObjectCustomerUpdateAfter($params)
    {
        $customerId = (int)$params['object']->id;
        $mobile = Tools::getValue('phoneNumber');
        $country = Tools::getValue('defaultCountry');
        $code = Tools::getValue('carrierCode');

		Db::getInstance()->Execute("DELETE FROM `" . pSQL(_DB_PREFIX_) . "mobilefield` WHERE customer_id = " . (int)$customerId);
		Db::getInstance()->Execute("INSERT INTO `" . pSQL(_DB_PREFIX_) . "mobilefield` (customer_id, mobile, country, code) VALUES (".(int)$customerId.", '$mobile', '$country', '$code')");
    }



    public function hookactionObjectCustomerAddAfter($params)
    {
        $customerId = (int)$params['object']->id;
        $mobile = Tools::getValue('phoneNumber');
        $country = Tools::getValue('defaultCountry');
        $code = Tools::getValue('carrierCode');

		Db::getInstance()->Execute("DELETE FROM `" . pSQL(_DB_PREFIX_) . "mobilefield` WHERE customer_id = " . (int)$customerId);
		Db::getInstance()->Execute("INSERT INTO `" . pSQL(_DB_PREFIX_) . "mobilefield` (customer_id, mobile, country, code) VALUES (".(int)$customerId.", '$mobile', '$country', '$code')");
    }


    public function hookAdditionalCustomerFormFields($params)
    {
        $customerId = Context::getContext()->customer->id;
		$row = Db::getInstance()->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'mobilefield`
			WHERE `customer_id` = '.(int)$customerId);			
		$mobile = $row['mobile'] ? $row['country'].'_'.$row['mobile']:'';
        $extra_fields = array();
        $extra_fields['mobile'] = (new FormField)
            ->setName('mobile')
            ->setType('text')
            ->setValue($mobile)
            ->setLabel($this->l('Mobile'));

        return $extra_fields;
    }
	
	
    public function hookHeader()
    {
		$php_self = $this->context->controller->php_self;
		if($php_self == 'identity' || $php_self == 'authentication'){
			//$this->context->controller->addJS($this->_path . 'js/jquery-2.2.4.min.js');
			$this->context->controller->addJS($this->_path . 'js/bootstrap.bundle.min.js');
			$this->context->controller->addJS($this->_path . 'js/intlInputPhone.js');
			$this->context->controller->addJS($this->_path . 'js/mobilefield.js');
			$this->context->controller->addCSS($this->_path . 'css/intlInputPhone.css');
		}
	}	
	
}	