<?php
class ControllerExtensionModuleBrowserNotification extends Controller {

    // set FCM browser token
	public function setToken() {

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['token'] != "") {
            
            $this->load->model('extension/module/browser_notification');
            $module = $this->model_extension_module_browser_notification->setToken($this->request->post['token']);
        }
    }
}