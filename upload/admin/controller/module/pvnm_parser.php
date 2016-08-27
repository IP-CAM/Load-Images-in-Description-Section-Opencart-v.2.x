<?php
class ControllerModulePvnmParser extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('module/pvnm_parser');

		$title = strip_tags($this->language->get('heading_title'));		

		$this->document->setTitle($title);

		$this->load->model('setting/setting');
		$this->load->model('catalog/category');
		$this->load->model('catalog/attribute_group');
		$this->load->model('module/pvnm_parser');

		if ($this->validate()) {
			$this->truncate();
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pvnm_parser', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('module/pvnm_parser', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_parse'] = $this->language->get('button_parse');
		$data['tab_settings'] = $this->language->get('tab_settings');
		$data['tab_help'] = $this->language->get('tab_help');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_documentation'] = $this->language->get('text_documentation');
		$data['text_developer'] = $this->language->get('text_developer');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_product_limit'] = $this->language->get('entry_product_limit');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_attribute_group'] = $this->language->get('entry_attribute_group');
		$data['entry_donor'] = $this->language->get('entry_donor');
		$data['entry_limit'] = $this->language->get('entry_limit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $title,
			'href' => $this->url->link('module/pvnm_parser', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('module/pvnm_parser', 'token=' . $this->session->data['token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['pvnm_parser_status'])) {
			$data['pvnm_parser_status'] = $this->request->post['pvnm_parser_status'];
		} else {
			$data['pvnm_parser_status'] = $this->config->get('pvnm_parser_status');
		}

		if (isset($this->request->post['pvnm_parser_product_limit'])) {
			$data['pvnm_parser_product_limit'] = $this->request->post['pvnm_parser_product_limit'];
		} else { 
			$data['pvnm_parser_product_limit'] = $this->config->get('pvnm_parser_product_limit');
		}

		if (isset($this->request->post['pvnm_parser_category'])) {
			$data['pvnm_parser_category'] = $this->request->post['pvnm_parser_category'];
		} elseif ($this->config->get('pvnm_parser_category')) {
			$data['pvnm_parser_category'] = $this->config->get('pvnm_parser_category');
		} else {
			$data['pvnm_parser_category'] = array();
		}

		$filter_data = array(
			'sort'  => 'sort_order',
			'order' => 'ASC'
		);

		$data['token'] = $this->session->data['token'];
		$data['categories'] = $this->model_catalog_category->getCategories($filter_data);
		$data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/pvnm_parser.tpl', $data));
	}

	public function searchProducts() {
		require_once(DIR_SYSTEM . 'library/nokogiri.php');

		$this->load->language('module/pvnm_parser');

		$this->load->model('module/pvnm_parser');

		$json = array();

		if ($this->validate()) {
			$categories = $this->config->get('pvnm_parser_category');

			$next = $this->request->post['next'];
			$page = $this->request->post['page'];

			$category_id = $categories[$next]['category_id'];

			$limit = $categories[$next]['limit'];

			if ($page == 1) {
				$url = $categories[$next]['url'];
			} else {
				$url = $categories[$next]['url'] . '&page=' . $page ;
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$html = curl_exec($ch);
			curl_close($ch);

			$saw = new nokogiri($html);

			if ($limit >= $page) {
				// There are products in the category
				if ($saw->get('.js-product-image')) {
					foreach ($saw->get('.js-product-image') as $link){
						$this->model_module_pvnm_parser->foundProduct('https://www.walmart.com' . $link['href'], $category_id);
					}

					if ($limit == $page) {
						if (isset($categories[$next + 1]['category_id'])) {
							$next++;

							$json['next'] = $next;
							$json['page'] = 1;
						} else {
							unset($next);
						}
					} else {
						$json['next'] = $next;
						$json['page'] = $page + 1;
					}
				} elseif (!$saw->get('.js-product-image')) {
					if (isset($categories[$next + 1]['category_id'])) {
						$next++;

						$json['next'] = $next;
						$json['page'] = 1;
					} else {
						unset($next);
					}
				}
			} elseif (isset($categories[$next + 1]['category_id'])) {
				$next++;

				$json['next'] = $next;
				$json['page'] = 1;
			} else {
				unset($next);
			}

			$found_products = $this->model_module_pvnm_parser->getTotalProduct();

			$json['success'] = sprintf($this->language->get('text_success_found'), $found_products);
		} else {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function parseProducts() {
		require_once(DIR_SYSTEM . 'library/nokogiri.php');

		$this->load->language('module/pvnm_parser');

		$this->load->model('module/pvnm_parser');
		$this->load->model('catalog/attribute');
		$this->load->model('catalog/manufacturer');

		$json = array();

		if ($this->validate()) {
			$limit = $this->config->get('pvnm_parser_product_limit');
			$categories = $this->config->get('pvnm_parser_category');

			$next = $this->request->post['next'];

			$category_id = $categories[$next]['category_id'];

			$attribute_group_id = $categories[$next]['attribute_group_id'];

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_status'      => 1
			);

			$category_parse_products = $this->model_module_pvnm_parser->getTotalProduct($filter_data);

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_status'      => 0
			);

			$category_found_products = $this->model_module_pvnm_parser->getTotalProduct($filter_data);

			if ($limit > $category_parse_products && $category_found_products > 0) {
				$filter_data = array(
					'filter_category_id' => $category_id,
					'filter_status'      => 0,
					'limit'              => 1
				);

				$found_products = $this->model_module_pvnm_parser->getFoundProduct($filter_data);

				foreach ($found_products as $product) {
					$model = '';
					$manufacturer_id = 0;
					$price = 0;
					$image = '';
					$description = '';
					$product_image = array();
					$product_attribute = array();

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $product['url']);
					curl_setopt($ch, CURLOPT_POST, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$html = curl_exec($ch);
					curl_close($ch);

					$saw = new nokogiri($html);

					$product_name = $saw->get('.js-product-heading span')->toArray();

					if (isset($product_name[0]['#text'][0])) {
						$name = trim(str_replace('"', '&quot;', $product_name[0]['#text'][0]));
					}

					$product_description = $saw->get('.product-about .js-ellipsis')->toXml();

					preg_match_all('#<root><div class="js-ellipsis module" data-max-height="350"> <p class="product-description-disclaimer"> <b>Important Made in USA Origin Disclaimer:</b> For certain items sold by Walmart on Walmart.com, the displayed country of origin
information may not be accurate or consistent with manufacturer information. For updated, accurate country of origin data, it is
recommended that you rely on product packaging or manufacturer information. </p>(.+?)</div></root>#is', $product_description, $product_description);

					if (isset($product_description[1][0])) {
						$description .= preg_replace('/<a(.*)>|<\/a>/iU', '', $product_description[1][0]);
					}

					//if (isset($product_description_extended[1][0])) {
					//	$description .= preg_replace('/<a(.*)>|<\/a>/iU', '', $product_description_extended[1][0]);
					//}

					$product_price = $saw->get('.js-price-display')->toArray();

					if (isset($product_price[0]['#text'][1])) {
						$price = $product_price[0]['#text'][1];
					}

					$product_manufacturer = $saw->get('#WMItemBrandLnk span')->toArray();

					// Add manufacturer to temporary product
					if (isset($product_manufacturer[0]['#text'][0])) {
						$product_manufacturer = trim($product_manufacturer[0]['#text'][0]);

						$filter_data = array(
							'filter_name' => $product_manufacturer,
							'start'       => 0,
							'limit'       => 1
						);

						// Serching manufacturer in database
						$manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_data);

						if (!empty($manufacturers)) {
							$manufacturer_id = $manufacturers[0]['manufacturer_id'];
						} else {
							$manufacturer_data = array(
								'name'               => $product_manufacturer,
								'sort_order'         => '',
								'manufacturer_store' => array(0),
								'keyword'            => $this->translit(mb_strtolower($product_manufacturer))
							);

							// Add manufacturer to opencart
							$manufacturer_id = $this->model_catalog_manufacturer->addManufacturer($manufacturer_data);
						}
					}

					$product_image = $saw->get('.js-product-primary-image')->toArray();

					if (isset($product_image[0]['data-zoom-image']) && !empty($product_image[0]['data-zoom-image'])) {
						$image = $product_image[0]['data-zoom-image'];
					} elseif (isset($product_image[0]['src']) && !empty($product_image[0]['src'])) {
						$image = mb_substr($product_image[0]['src'], 0, strpos($product_image[0]['src'], '?odnHeight'));
					}

					//foreach ($saw->get('.js-product-thumb') as $key => $link) {
					//	$product_image[] = 'https://www.walmart.com' . $link['href'];
					//}

					foreach ($saw->get('.js-product-specs-row td:first-child') as $key => $link) {
						if ($link['#text'][0] == 'Manufacturer Part Number:') {
							$model_number = $key;
						}

						$attribute_description[$this->config->get('config_language_id')]['name'] = $link['#text'][0];

						$filter_data = array(
							'filter_name'               => $link['#text'][0],
							'filter_attribute_group_id' => $attribute_group_id,
							'start'                     => 0,
							'limit'                     => 1
						);

						// Serching attribute in database
						$attributes = $this->model_catalog_attribute->getAttributesByAttributeGroupId($filter_data);

						if (!empty($attributes)) {
							$attribute_id = $attributes[0]['attribute_id'];
						} else {
							$attribute_data = array(
								'attribute_description' => $attribute_description,
								'attribute_group_id'    => $attribute_group_id,
								'sort_order'            => ''
							);

							// Add attributes to opencart
							$attribute_id = $this->model_catalog_attribute->addAttribute($attribute_data);
						}

						$product_attribute[$key] = array(
							'attribute_id' => $attribute_id
						);
					}

					foreach ($saw->get('.js-product-specs-row td:last-child') as $key => $link) {
						// Add model to temporary product
						if (isset($model_number) && $key == $model_number) {
							$model = trim($link['#text'][0]);
						}

						$product_attribute_description[$this->config->get('config_language_id')]['text'] = $link['#text'][0];

						// Add attributes with values to temporary product
						$product_attribute[$key]['product_attribute_description'][$this->config->get('config_language_id')]['text'] = $link['#text'][0];
					}

					$product_description_extended = $saw->get('.js-marketing-content-iframe')->toArray();

					if (isset($product_description_extended[0]['src']) && !empty($product_description_extended[0]['src'])) {
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'https:' . $product_description_extended[0]['src']);
						curl_setopt($ch, CURLOPT_POST, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						$html = curl_exec($ch);
						curl_close($ch);

						$saw = new nokogiri($html);

						$product_description_extended = $saw->get('#wc-reset')->toXml();
						
						preg_match_all('#<root><div id="wc-reset">(.+?)</div></root>#is', $product_description_extended, $product_description_extended);

						if (isset($product_description_extended[1][0])) {
							$description .= preg_replace('/<a(.*)>|<\/a>/iU', '', $product_description_extended[1][0]);
						}
					}

					if (isset($name)) {
						$product_data = array(
							'product_id'        => $product['product_id'],
							'model'             => $model,
							'manufacturer_id'   => $manufacturer_id,
							'price'             => $price,
							'image'             => $image,
							'name'              => $name,
							'description'       => $description,
							'product_image'     => $product_image,
							'product_attribute' => $product_attribute
						);

						// Add parse values to temporary product
						$this->model_module_pvnm_parser->updateFoundProduct($product_data);
					} else {
						$this->model_module_pvnm_parser->updateFoundProductStatus($product['product_id'], 3);
					}
				}

				$json['next'] = $next;
			} elseif (isset($categories[$next + 1]['category_id'])) {
				$this->model_module_pvnm_parser->updateFoundProductStatusByCategory($category_id, 3);

				$next++;

				$json['next'] = $next;
			} else {
				$this->model_module_pvnm_parser->updateFoundProductStatusByCategory($category_id, 3);

				unset($next);
			}

			$filter_data = array(
				'filter_status' => 1
			);

			$total_parse_products = $this->model_module_pvnm_parser->getTotalProduct($filter_data);

			$total_found_products = $this->model_module_pvnm_parser->getTotalProduct();

			$json['success'] = sprintf($this->language->get('text_success_parse'), $total_parse_products, $total_found_products);
		} else {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadProducts() {
		$this->load->language('module/pvnm_parser');

		$this->load->model('module/pvnm_parser');
		$this->load->model('catalog/product');

		$json = array();

		if ($this->validate()) {
			$next = $this->request->post['next'];

			$filter_data = array(
				'filter_status' => 1,
				'limit'         => 1
			);

			$load_products = $this->model_module_pvnm_parser->getFoundProduct($filter_data);

			if (count($load_products) > 0) {
				foreach ($load_products as $product) {
					// Load images to opencart
					if (isset($product['image']) && $product['image'] != '') {
						if (!is_dir(DIR_IMAGE . 'catalog/pvnm_parser')) {
							mkdir(DIR_IMAGE . 'catalog/pvnm_parser', 0700);
						}

						$pre_url = 'catalog/pvnm_parser/' . mb_substr($this->translit($product['name']), 0, 100) . '/';

						if (!is_dir(DIR_IMAGE . $pre_url)) {
							mkdir(DIR_IMAGE . $pre_url, 0700);
						}

						$imagefile = file_get_contents($product['image']);
						$imagetype = substr(strrchr(basename(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8')), '.'), 1);
						$imagename = $this->translit(basename(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), '.' . $imagetype));
						$imageurl = $pre_url . $imagename . '.' . $imagetype;

						if ($imagefile != false){
							file_put_contents(DIR_IMAGE . $imageurl, $imagefile);
						}

						if (file_exists(DIR_IMAGE . $imageurl)) {
							$image = $imageurl;
						}
					} else {
						$image = '';
					}

					$product_description[$this->config->get('config_language_id')] = array(
						'name'             => $product['name'],
						'description'      => $product['description'],
						'tag'              => '',
						'meta_title'       => $product['name'],
						'meta_description' => '',
						'meta_keyword'     => ''
					);

					$product_attributes = $this->model_module_pvnm_parser->getProductAttributes($product['product_id']);

					$product_data = array(
						'model'               => $product['model'], 
						'sku'                 => '',
						'upc'                 => '',
						'ean'                 => '',
						'jan'                 => '',
						'isbn'                => '',
						'mpn'                 => '',
						'location'            => '',
						'quantity'            => 100,
						'minimum'             => 1,
						'subtract'            => 1,
						'stock_status_id'     => 5,
						'date_available'      => date('Y-m-d'),
						'manufacturer_id'     => $product['manufacturer_id'], 
						'shipping'            => 1,
						'price'               => $product['price'],
						'points'              => 0,
						'weight'              => 0,
						'weight_class_id'     => 1,
						'length'              => 0,
						'width'               => 0,
						'height'              => 0,
						'length_class_id'     => 1,
						'status'              => 1,
						'tax_class_id'        => 0,
						'sort_order'          => 0,
						'image'               => $image,
						'product_description' => $product_description,
						'product_store'       => array(0),
						'product_attribute'   => $product_attributes,
						'product_category'    => array($product['category_id']),
						'keyword'             => $this->translit(mb_strtolower($product['name']))
					);

					// Add product to opencart
					$this->model_catalog_product->addProduct($product_data);

					$this->model_module_pvnm_parser->updateFoundProductStatus($product['product_id'], 2);
				}

				$json['next'] = $next;

				$filter_data = array(
					'filter_status' => 2
				);

				$total_load_products = $this->model_module_pvnm_parser->getTotalProduct($filter_data);

				$total_found_products = $this->model_module_pvnm_parser->getTotalProduct();

				$json['success'] = sprintf($this->language->get('text_success_loaded'), $total_load_products, $total_found_products);
			} else {
				unset($next);

				$filter_data = array(
					'filter_status' => 2
				);

				$total_load_products = $this->model_module_pvnm_parser->getTotalProduct($filter_data);

				$json['success'] = sprintf($this->language->get('text_success_parsing'), $total_load_products);
			}
		} else {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function translit($str) {
		$replace = array(
			"А"=>"a",       "а"=>"a",       " "=>"_",
			"Б"=>"b",       "б"=>"b",       "."=>"_",
			"В"=>"v",       "в"=>"v",       "/"=>"_",
			"Г"=>"g",       "г"=>"g",       ","=>"_",
			"Д"=>"d",       "д"=>"d",       "-"=>"_",
			"Е"=>"e",       "е"=>"e",       "("=>"_",
			"Ё"=>"e",       "ё"=>"e",       ")"=>"_",
			"Ж"=>"j",       "ж"=>"j",       "["=>"_",
			"З"=>"z",       "з"=>"z",       "]"=>"_",
			"И"=>"i",       "и"=>"i",       "="=>"_",
			"Й"=>"y",       "й"=>"y",       "+"=>"_",
			"К"=>"k",       "к"=>"k",       "*"=>"_",
			"Л"=>"l",       "л"=>"l",       "?"=>"_",
			"М"=>"m",       "м"=>"m",       "\""=>"_",
			"Н"=>"n",       "н"=>"n",       "'"=>"_",
			"О"=>"o",       "о"=>"o",       "&"=>"_",
			"П"=>"p",       "п"=>"p",       "%"=>"_",
			"Р"=>"r",       "р"=>"r",       "#"=>"_",
			"С"=>"s",       "с"=>"s",       "@"=>"_",
			"Т"=>"t",       "т"=>"t",       "!"=>"_",
			"У"=>"u",       "у"=>"u",       ";"=>"_",
			"Ф"=>"f",       "ф"=>"f",       "№"=>"_",
			"Х"=>"h",       "х"=>"h",       "^"=>"_",
			"Ц"=>"ts",      "ц"=>"ts",      ":"=>"_",
			"Ч"=>"ch",      "ч"=>"ch",      "~"=>"_",
			"Ш"=>"sh",      "ш"=>"sh",      "\\"=>"_",
			"Щ"=>"sch",     "щ"=>"sch",     "Ґ"=>"G",
			"Ъ"=>"",        "ъ"=>"y",       "є"=>"e",
			"Ы"=>"i",       "ы"=>"i",       "Є"=>"E",
			"Ь"=>"j",       "ь"=>"j",       "і"=>"i",
			"Э"=>"e",       "э"=>"e",       "І"=>"I",
			"Ю"=>"yu",      "ю"=>"yu",      "ї"=>"i",
			"Я"=>"ya",      "я"=>"ya",      "Ї"=>"I",
			"$"=>"_",       "&amp;"=>"_",   "__"=>"_"
		);

		$new_str = strtr($str, $replace);

		return strtr($new_str, $replace);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/pvnm_parser')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('module/pvnm_parser');

		$this->model_module_pvnm_parser->install();
	}

	public function truncate() {
		$this->load->model('module/pvnm_parser');

		$this->model_module_pvnm_parser->truncate();
	}

	public function uninstall() {
		$this->load->model('module/pvnm_parser');

		$this->model_module_pvnm_parser->uninstall();
	}
}