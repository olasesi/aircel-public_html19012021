<?php
require 'class.phpmailer.php';  //code added by ici - 31/07/2019
class ControllerMailForgotten extends Controller {
	public function index(&$route, &$args, &$output) {			            
		$this->load->language('mail/forgotten');

		$data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$data['text_change'] = $this->language->get('text_change');
		$data['text_ip'] = $this->language->get('text_ip');
		
		$data['reset'] = str_replace('&amp;', '&', $this->url->link('account/reset', 'code=' . $args[1], true));
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($args[0]);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
		$mail->setText($this->load->view('mail/forgotten', $data));
		//$mail->send();
		
		//****code added by ici - 31/07/2019 ***//
        $mailb = new PHPMailer;
        $mailb->isSMTP();
        $mailb->SMTPSecure = 'ssl';
        $mailb->SMTPAuth = true;
        $mailb->SMTPOptions = array(
             'ssl' => array(
             'verify_peer' => false,
             'verify_peer_name' => false,
              'allow_self_signed' => true
             )
        );        
        
        $mailb->Host = 'mail.obejor.com';
        $mailb->Port = 465;
        $mailb->Username = 'sales@obejor.com';
        $mailb->Password = 'Google22.@';
        $mailb->setFrom('sales@obejor.com');
        $mailb->addAddress($args[0]);
        $mailb->addCC("obejorbusiness@gmail.com");
        //$mailb->addBCC("icisystemng@gmail.com");
        $mailb->Subject = html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');
        $mailb->isHTML(true);
        $mailb->Body = $this->load->view('mail/forgotten', $data);
        //$mailb->send();
        
        //****end************************//
        
        //added 05/03/2020 ici
        $encodedlink = urlencode("https://www.obejor.com.ng/index.php?route=account/reset&code=" . $args[1]);
        $tmess = "A new password was requested for Obejor Store customer account. To reset your password click on the link below: " . $encodedlink; 
        $tmess .= " The IP used to make this request was: " . $this->request->server['REMOTE_ADDR'];
        
		$bmessg = $tmess; //$this->load->view('mail/forgotten', $data);
		$subject = html_entity_decode(sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8');
		$cemail = $args[0];
		$postdata = "email=" . $cemail . "&body=" . $bmessg . "&subject=" . $subject;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.obejorgroup.com/sendemail.php");
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_POST, 1); 
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
	    curl_exec($ch);
		curl_close($ch);	
		                
        //****end************************//
        
		
	}
}
