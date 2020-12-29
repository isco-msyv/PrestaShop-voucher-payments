<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Revolutetestvoucher extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'revolutetestvoucher';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Ismayil Musayev';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Revolute test vouchers');
        $this->description = $this->l('The module allows the customer to pay with predefined vouchers');


        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('REVOLUTETESTVOUCHER_LABEL', 'Voucher payment');

        include(dirname(__FILE__) . '/sql/install.php');

        $this->createDefaultVouchers();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentOptions');
    }

    public function createDefaultVouchers()
    {
        $def_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $defaultVouchers = array(
            array(
                'voucher_string' => 'bd28808c-380a-4274-bde1-b1ce31258ea1',
                'voucher_price' => 5,
                'voucher_id_currency' => $def_currency,
                'voucher_is_full_price' => 0,
            ), array(
                'voucher_string' => 'db15f80e-2f65-4794-aeb8-7a03d225eb53',
                'voucher_price' => 10,
                'voucher_id_currency' => $def_currency,
                'voucher_is_full_price' => 0,
            ), array(
                'voucher_string' => '8c086291-f90c-4208-892a-c77f9d9446ea',
                'voucher_price' => 50,
                'voucher_id_currency' => $def_currency,
                'voucher_is_full_price' => 0,
            ), array(
                'voucher_string' => '23ca0e77-49d5-47fb-ae67-d1809f995198',
                'voucher_price' => 0,
                'voucher_id_currency' => $def_currency,
                'voucher_is_full_price' => 1,
            ),
        );

        $this->saveVouchers($defaultVouchers);
    }

    public function uninstall()
    {
        Configuration::deleteByName('REVOLUTETESTVOUCHER_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSettings')) == true) {
            $this->postProcess();
        }

        if (((bool)Tools::isSubmit('submitInsertVoucher')) == true) {
            $this->saveInsertVoucher();
        }

        $vouchers = $this->getVouchers();
        foreach ($vouchers as &$voucher) {
            $currency = new Currency($voucher['voucher_id_currency']);
            $voucher['voucher_id_currency'] = $currency->iso_code;
            if ($voucher['voucher_is_full_price']) {
                $voucher['voucher_is_full_price'] = "YES";
            } else {
                $voucher['voucher_is_full_price'] = "NO";
            }
        }
        $this->context->smarty->assign('vouchers', $vouchers);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $this->renderSettingsForm() . $output . $this->renderVoucherInsertForm();
    }

    public function saveVouchers($vouchers)
    {

        foreach ($vouchers as $voucher) {
            $this->saveVoucher($voucher);
        }
    }

    public function saveVoucher($voucher)
    {
        if (empty($voucher['voucher_string'])) {
            return;
        }

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'revolutetestvoucher` (`voucher_string`, `voucher_id_currency`, `voucher_price`, `voucher_is_full_price`) 
                VALUES ("' . pSQL($voucher['voucher_string']) . '","' . (int)$voucher['voucher_id_currency'] . '","' . (float)$voucher['voucher_price'] . '","' . (int)$voucher['voucher_is_full_price'] . '")';

        Db::getInstance()->execute($sql);
    }

    public function getVouchers()
    {

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'revolutetestvoucher`';
        return Db::getInstance()->executeS($sql);
    }

    public function getVoucherByText($voucher_string)
    {

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'revolutetestvoucher` WHERE voucher_string like "' . pSQL($voucher_string) . '"';
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderSettingsForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getSettingsConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getSettingsConfigForm()));
    }

    protected function renderVoucherInsertForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitInsertVoucher';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getVoucherInsertConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getVoucherInsertConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getSettingsConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Text for Payment method'),
                        'name' => 'REVOLUTETESTVOUCHER_LABEL',
                        'required' => true,
                        'label' => $this->l('Text'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getVoucherInsertConfigForm()
    {
        $currencies = Currency::getCurrencies();
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Create Voucher'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Voucher text code'),
                        'name' => 'VOUCHER_TEXT',
                        'label' => $this->l('Voucher Text'),
                        'required' => true,
                    ), array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Text for Payment method'),
                        'name' => 'VOUCHER_VALUE',
                        'label' => $this->l('Vocuher Value'),
                        'required' => true,
                    ), array(
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->l('VOUCHER CURRENCY'),
                        'name' => 'VOUCHER_CURRENCY',
                        'required' => true,
                        'label' => $this->l('Vocuher Currency'),
                        'options' => array(
                            'query' => $currencies,
                            'id' => 'id_currency',
                            'name' => 'name'
                        ),
                    ), array(
                        'type' => 'switch',
                        'label' => $this->l('Is Voucher for full price'),
                        'name' => 'VOUCHER_FULL_PRICE',
                        'required' => true,
                        'is_bool' => true,
                        'desc' => $this->l('If enabled then voucher will be valid for any amount of orders'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getVoucherInsertConfigFormValues()
    {
        return array(
            'VOUCHER_TEXT' => $this->generatePassword(),
            'VOUCHER_VALUE' => 0.00,
            'VOUCHER_CURRENCY' => 0,
            'VOUCHER_FULL_PRICE' => false,
        );
    }

    public function generatePassword($length = 30)
    {

        $chars = '234-567-89b-cdf-hk--mnp-rst-vzB-CDF-HJ-KL-M-NPR-S-T-V-Z';
        $shuffled = str_shuffle($chars);
        $result = mb_substr($shuffled, 0, $length);

        return $result;
    }

    protected function getSettingsConfigFormValues()
    {
        return array(
            'REVOLUTETESTVOUCHER_LABEL' => Configuration::get('REVOLUTETESTVOUCHER_LABEL')
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getVoucherInsertConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    protected function saveInsertVoucher()
    {
        $voucher = array();
        $voucher['voucher_string'] = Tools::getValue('VOUCHER_TEXT');
        $voucher['voucher_price'] = Tools::getValue('VOUCHER_VALUE');
        $voucher['voucher_id_currency'] = Tools::getValue('VOUCHER_CURRENCY');
        $voucher['voucher_is_full_price'] = Tools::getValue('VOUCHER_FULL_PRICE');

        $this->saveVoucher($voucher);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        //no need for now
//        if (Tools::getValue('module_name') == $this->name) {
//            $this->context->controller->addJS($this->_path . 'views/js/back.js');
//            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
//        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        //no need for now
//        $this->context->controller->addJS($this->_path . '/views/js/front.js');
//        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        $this->smarty->assign('paymentText', Configuration::get('REVOLUTETESTVOUCHER_LABEL'));
        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return; //1.7 version has general view for order return
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $paymentForm = $this->fetch('module:'.$this->name.'/views/templates/hook/payment_form1.7.tpl');
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($this->l(Configuration::get('REVOLUTETESTVOUCHER_LABEL')))
            ->setForm($paymentForm)
            ->setLogo(_MODULE_DIR_.$this->name.'/logo.png')
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true));

        return array($option);
    }
}
