{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="row">
	<div class="col-xs-12 col-md-12">
		<p class="payment_module" id="revolutetestvoucher_payment_button">
			<a id="voucherPayButton" href="{$link->getModuleLink('revolutetestvoucher', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}">
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{$paymentText|escape:'htmlall':'UTF-8'}" width="32" height="32" />
				{$paymentText|escape:'htmlall':'UTF-8'}
			</a>
		</p>
	</div>
	<div class="col-md-4" id="voucherTextForm" style="display: none">
		<form action="{$link->getModuleLink('revolutetestvoucher', 'confirmation', array(), true)|escape:'htmlall':'UTF-8'}">
			<label for="voucher_text">{l s='Insert Voucher text:' mod='revolutetestvoucher'}</label>
			<input class="form-control" type="text" id="voucher_text" name="voucher_text">
			<br>
			<input type="submit" class="btn btn-primary" value="Submit">
		</form>
	</div>
</div>

<script>
	$(document).ready(function () {
		$('#voucherPayButton').on('click', function (e) {
			e.preventDefault();
			$('#voucherTextForm').fadeIn();
		})
	});
</script>
