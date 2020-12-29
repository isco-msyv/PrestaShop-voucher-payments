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

class RevolutetestvoucherConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart_id = Context::getContext()->cart->id;

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);
        $secure_key = $customer->secure_key;
        $currency_id = (int)Context::getContext()->currency->id;
        $orderTotal = (float)$cart->getOrderTotal();
        $voucherText = Tools::getValue('voucher_text');

        if (empty($voucherText)) {
            return $this->displayError('Invalid voucher');
        }

        $voucherCheck = $this->module->getVoucherByText($voucherText);

        if (empty($voucherCheck)) {
            return $this->displayError('Invalid voucher');
        }

        $voucherCheck = reset($voucherCheck);

        $voucher_is_full_price = (bool)$voucherCheck['voucher_is_full_price'];

        if ($voucher_is_full_price) {
            return $this->validateOrder($cart_id, $cart, $currency_id, $secure_key);
        }

        $voucherCurrency = (int)$voucherCheck['voucher_id_currency'];
        $voucherPrice = (float)$voucherCheck['voucher_price'];

        // if order has different currency convert the total with the voucher currency
        if ($voucherCurrency != $currency_id) {
            $voucherCurrencyObj = new Currency($voucherCurrency);
            $cartCurrencyObj = new Currency($currency_id);
            $orderTotal = Tools::convertPriceFull($orderTotal, $cartCurrencyObj, $voucherCurrencyObj);
        }

        if ($voucherPrice >= $orderTotal) {
            return $this->validateOrder($cart_id, $cart, $currency_id, $secure_key);
        }

        return $this->displayError('Voucher value is not enough for that order');
    }

    private function validateOrder($cart_id, $cart, $currency_id, $secure_key)
    {
        $payment_status = Configuration::get('PS_OS_PAYMENT'); // Default value for a payment that succeed.
        $message = null; // add a comment directly into the order so the merchant will see it in the BO.


        $module_name = $this->module->displayName;


        $this->module->validateOrder($cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key);

        /**
         * If the order has been validated we try to retrieve it
         */
        $order_id = Order::getOrderByCartId((int)$cart->id);

        if ($order_id) {
            /**
             * The order has been placed so we redirect the customer on the confirmation page.
             */
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key);
        } else {
            /*
             * An error occured and is shown on a new page.
             */
            $errorMsg = $this->module->l('An error occured. Please contact the merchant to have more informations');

            return $this->displayError($errorMsg);
        }
    }

    protected function displayError($message)
    {
        $this->context->smarty->assign('error', $message);
        if (version_compare(_PS_VERSION_, '1.7', '>')) {
            return $this->setTemplate('module:' . $this->module->name . '/views/templates/front/error1.7.tpl');
        }

        return $this->setTemplate('error.tpl');
    }
}
