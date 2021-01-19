<?php
require 'class.phpmailer.php';  //code added by ici - 31/07/2019
class ControllerMailTransaction extends Controller {
	public function index(&$route, &$args, &$output) {
		$this->load->language('mail/transaction');

		$this->load->model('account/customer');
		
		$customer_info = $this->model_account_customer->getCustomer($args[0]);

		if ($customer_info) {
			$data['text_received'] = sprintf($this->language->get('text_received'), $this->config->get('config_name'));
			$data['text_amount'] = $this->language->get('text_amount');
			$data['text_total'] = $this->language->get('text_total');
			
			$data['amount'] = $this->currency->format($args[2], $this->config->get('config_currency'));
			$data['total'] = $this->currency->format($this->model_account_customer->getTransactionTotal($args[0]), $this->config->get('config_currency'));
	
			/*$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
	
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/transaction', $data));
			$mail->send();*/

		    //****code added by ici - 31/07/2019 ***//
            /*$mailb = new PHPMailer;
            $mailb->isSMTP();
            $mailb->SMTPSecure = 'tls';
            $mailb->SMTPOptions = array(
             'ssl' => array(
             'verify_peer' => false,
             'verify_peer_name' => false,
              'allow_self_signed' => true
             )
            );        
            
            $mailb->SMTPAuth = true;
            $mailb->Host = 'mail.obejor.com';
            //$mailb->Port = 465;
            $mailb->Port = 587;
            $mailb->Username = 'sales@obejor.com';
            $mailb->Password = 'Google22.@';
            $mailb->setFrom('sales@obejor.com');
            $mailb->addAddress($customer_info['email']);
            $mailb->addCC("obejorbusiness@gmail.com");
            //$mailb->addBCC("icisystemng@gmail.com");
            $mailb->Subject = html_entity_decode(sprintf($this->language->get('text_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');
            $mailb->isHTML(true);
            $mailb->Body = $this->load->view('mail/transaction', $data);
            $mailb->send();*/
        
            //****end************************//
			
		}
	}
}



