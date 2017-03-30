<?php
class ControllerExtensionPaymentSCPay extends Controller {
	public function index() {
		$this->load->language('extension/payment/sc_pay');

		$data['text_testmode'] = $this->language->get('text_testmode');
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['testmode'] = $this->config->get('sc_pay_test');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
 		$total = $order_info['total'];

		$nwbutton = str_replace("50", $total, $this->config->get('sc_pay_button_link'));

		$newbutton = $nwbutton .  '&redirect_url=' . urlencode(base64_encode($this->url->link('extension/payment/sc_pay/callback', '', true).'&order_id='.$this->session->data['order_id']));

		if (!$this->config->get('sc_pay_test')) {
			$data['action'] = $newbutton;

		} else {
			$data['action'] = 'https://www.secapay.com';
		}

		$this->load->model('checkout/order');

		if ($order_info) {
			$data['business'] = $newbutton;
			$data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						
						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$data['products'][] = array(
					'name'     => htmlspecialchars($product['name']),
					'model'    => htmlspecialchars($product['model']),
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}

			$data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0) {
				$data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);
			} else {
				$data['discount_amount_cart'] -= $total;
			}

			$data['currency_code'] = $order_info['currency_code'];
			$data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['country'] = $order_info['payment_iso_code_2'];
			$data['email'] = $order_info['email'];
			$data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['lc'] = $this->session->data['language'];
			$data['return'] = $this->url->link('checkout/success');
			$data['notify_url'] = $this->url->link('extension/payment/sc_pay/callback', '', true);
			$data['cancel_return'] = $this->url->link('checkout/checkout', '', true);

			if (!$this->config->get('sc_pay_transaction')) {
				$data['paymentaction'] = 'authorization';
			} else {
				$data['paymentaction'] = 'sale';
			}

			$data['custom'] = $this->session->data['order_id'];

			return $this->load->view('extension/payment/sc_pay', $data);
		}
	}

	public function callback() {
		$ref = "";
		$url = $this->url->link('checkout/checkout');

		if(isset($this->request->get['secapay_ref'])){
			$ref = $this->request->get['secapay_ref'];
		}

		$order_id = $this->request->get['order_id'];

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);
	
		if ($order_info) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, 'https://secapay.com/transactions/status/'.$ref);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($curl);
			$order_status_id = $this->config->get('config_order_status_id');

			if (!$response)
			 {
				$url = $this->url->link('checkout/checkout');;
			} 
			else
			{
				$response = json_decode($response, true);
				$status = strtoupper($response['name']);
				switch($status) {
					case 'SUCCESS':
					$order_status_id = $this->config->get('sc_pay_completed_status_id');
					$url = $this->url->link('checkout/success');
					break;
					case 'FAILED':
					$order_status_id = $this->config->get('sc_pay_failed_status_id');
					$url = $this->url->link('checkout/checkout');
					break;
					case 'PENDING':
					$order_status_id = $this->config->get('sc_pay_pending_status_id');
					$url = $this->url->link('checkout/checkout');
					break;
				}
			}
			curl_close($curl);
			$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
			$this->response->redirect($url);

		} else {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
			$this->response->redirect($url);
		}
		$this->response->redirect($url);
	} 
}