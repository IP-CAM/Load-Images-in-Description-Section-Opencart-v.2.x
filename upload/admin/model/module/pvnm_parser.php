<?php
class ModelModulePvnmParser extends Model {
	public function foundProduct($url, $category_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "pvnm_parser_product SET url = '" . $this->db->escape($url) . "', category_id = '" . (int)$category_id . "'");
	}

	public function updateFoundProduct($data = array()) {
		$this->db->query("UPDATE " . DB_PREFIX . "pvnm_parser_product SET 
			model = '" . $this->db->escape($data['model']) . "', 
			manufacturer_id = '" . (int)$data['manufacturer_id'] . "', 
			price = '" . $this->db->escape($data['price']) . "', 
			image = '" . $this->db->escape($data['image']) . "', 
			name = '" . $this->db->escape($data['name']) . "', 
			description = '" . $this->db->escape($data['description']) . "', 
			status = 1 
		WHERE product_id = '" . (int)$data['product_id'] . "'");

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "pvnm_parser_product_attribute SET product_id = '" . (int)$data['product_id'] . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
	}

	public function updateFoundProductStatus($product_id, $status) {
		$this->db->query("UPDATE " . DB_PREFIX . "pvnm_parser_product SET status = '" . (int)$status . "' WHERE product_id = '" . (int)$product_id . "'");
	}

	public function updateFoundProductStatusByCategory($category_id, $status) {
		$this->db->query("UPDATE " . DB_PREFIX . "pvnm_parser_product SET status = '" . (int)$status . "' WHERE category_id = '" . (int)$category_id . "' AND status = 0");
	}

	public function getFoundProduct($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pvnm_parser_product WHERE product_id > 0";

		if (isset($data['filter_category_id']) && !is_null($data['filter_category_id'])) {
			$sql .= " AND category_id = '" . (int)$data['filter_category_id'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['limit'])) {
			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalProduct($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "pvnm_parser_product WHERE product_id > 0";

		if (isset($data['filter_category_id']) && !is_null($data['filter_category_id'])) {
			$sql .= " AND category_id = '" . (int)$data['filter_category_id'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['limit'])) {
			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "pvnm_parser_product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pvnm_parser_product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "pvnm_parser_product (
			`product_id` int(11) NOT NULL AUTO_INCREMENT,
			`category_id` int(11) NOT NULL,
			`url` text NOT NULL,
			`name` varchar(255),
			`description` text,
			`model` varchar(64),
			`sku` varchar(64),
			`upc` varchar(12),
			`ean` varchar(14),
			`jan` varchar(13),
			`isbn` varchar(17),
			`mpn` varchar(64),
			`location` varchar(128),
			`quantity` int(4) DEFAULT '0',
			`stock_status_id` int(11),
			`image` text,
			`manufacturer_id` int(11),
			`shipping` tinyint(1) DEFAULT '1',
			`price` decimal(15,4) DEFAULT '0.0000',
			`points` int(8) DEFAULT '0',
			`tax_class_id` int(11),
			`date_available` date DEFAULT '0000-00-00',
			`weight` decimal(15,8) DEFAULT '0.00000000',
			`weight_class_id` int(11) DEFAULT '0',
			`length` decimal(15,8) DEFAULT '0.00000000',
			`width` decimal(15,8) DEFAULT '0.00000000',
			`height` decimal(15,8) DEFAULT '0.00000000',
			`length_class_id` int(11) DEFAULT '0',
			`subtract` tinyint(1) DEFAULT '1',
			`minimum` int(11) DEFAULT '1',
			`sort_order` int(11) DEFAULT '0',
			`status` tinyint(1) DEFAULT '0',
			`viewed` int(5) DEFAULT '0',
			`date_added` datetime,
			`date_modified` datetime,
			PRIMARY KEY (`product_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
		);

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "pvnm_parser_product_attribute (
			`product_id` int(11) NOT NULL,
			`attribute_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`text` text NOT NULL,
			PRIMARY KEY (`product_id`,`attribute_id`,`language_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"
		);
	}

	public function truncate() {
		$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "pvnm_parser_product");
		$this->db->query("TRUNCATE TABLE " . DB_PREFIX . "pvnm_parser_product_attribute");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pvnm_parser_product");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pvnm_parser_product_attribute");
	}
}