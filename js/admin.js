$ = jQuery.noConflict();
jQuery(document).ready(function($) {
	$(document).on('click', '#update_meta_btn',function(e) {
		e.preventDefault();
		var data = {
			action: 'get_all_products',
		};
		$.post(woo_products_importer.ajax_url, data, function(response) {
			var products = jQuery.parseJSON(response);
			
			update_products(products);
		});
	});

	var update_product = function(product) {
		var data = {
			action: 'update_product',
			id: product.id
		};
		$.post(woo_products_importer.ajax_url, data, function (response) {
			var p = jQuery.parseJSON(response);
			if (p.title !== undefined) {
				var status_text = 'fail';
				var status_color = 'Red';
				if (p.status) {
					status_text = 'success';
					status_color = 'Green';
				}
				var el = $('<div class=".woo_products_importer__results-item"><span style="color: ' + status_color + '">[' + status_text + ']</span> ' + p.title + '</div>');
				$('.woo_products_importer__results').prepend(el);
				// setTimeout(function() {
				// 	el.remove();
				// }, 2000);
			}
			return p;
		});
	};

	var update_products = async function (products) {
		for (i = 0; i < products.length; i++) {
			await sleep(1000);
			update_product(products[i]);
		}
	};

	var sleep = function(ms) {
		return new Promise(resolve => setTimeout(resolve, ms));
	};
});