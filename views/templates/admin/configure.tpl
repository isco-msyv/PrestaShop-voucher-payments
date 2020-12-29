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

<!-- Nav tabs -->
<div class="panel" style="list-style: none">
	<div class="panel-heading">
		<i class="icon-cogs"></i> {l s='Voucher List' mod='revolutetestvoucher'}
	</div>

	<table class="table">
		<thead>
		<tr>
			<th><span class="title_box ">{l s='Voucher code' mod='revolutetestvoucher'}</span></th>
			<th><span class="title_box ">{l s='Voucher Value' mod='revolutetestvoucher'}</span></th>
			<th><span class="title_box ">{l s='Currency' mod='revolutetestvoucher'}</span></th>
			<th><span class="title_box ">{l s='Is full price' mod='revolutetestvoucher'}</span></th>
		</tr>
		</thead>
		<tbody>

		{foreach from=$vouchers item=voucher}
		<tr>
			<td><a class="vocuhers_list" style="cursor: pointer">{$voucher['voucher_string']}</a></td>
			<td>{$voucher['voucher_price']|escape:'htmlall':'UTF-8'}</td>
			<td>{$voucher['voucher_id_currency']|escape:'htmlall':'UTF-8'}</td>
			<td>{$voucher['voucher_is_full_price']|escape:'htmlall':'UTF-8'}</td>
		</tr>
		{/foreach}
		</tbody>
	</table>
</div>

<script>
	vocuhersList = document.querySelectorAll(".vocuhers_list");
	for (const voucher of vocuhersList) {
		voucher.onclick = function() {
			document.execCommand("copy");
		};

		voucher.addEventListener("copy", function(event) {
			event.preventDefault();
			if (event.clipboardData) {
				event.clipboardData.setData("text/plain", voucher.textContent);
				alert("Voucher text copied!")
			}
		});
	}
</script>
