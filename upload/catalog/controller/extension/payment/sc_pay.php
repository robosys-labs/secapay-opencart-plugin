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