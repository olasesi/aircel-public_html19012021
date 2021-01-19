<?php 
class ControllerExtensionAccountPurpletreeMultivendorBulkproductupload extends Controller{
	
	public function index(){
		$this->load->language('purpletree_multivendor/bulkproductupload');
		$this->load->model('extension/purpletree_multivendor/bulkproductupload');
		$this->document->setTitle($this->language->get('heading_title'));
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}else{
			        if(isset($store_detail['store_status']) && $store_detail[  'multi_store_id'] != $this->config->get('config_store_id')){	
						$this->response->redirect($this->url->link('account/account','', true));
				    }
		        }
	    $this->getData();
	}
	/* ************************************************************************************ */
		public function export(){

		$this->load->language('purpletree_multivendor/bulkproductupload');
		$this->load->model('extension/purpletree_multivendor/bulkproductupload');
		$this->load->model('catalog/product');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true));
		}
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
		$cwd = getcwd();
		$dir = 'library/purpletree_multivendor';
		chdir( DIR_SYSTEM.$dir );
		require_once( 'class_excel/PHPExcel/IOFactory.php' );
		chdir( $cwd );	

		$fileName = 'PurpletreePoductExport';
		$postSellerId	=	$this->customer->getId();
		$postLanguageId=$this->request->post['language'];
		$objPHPexl = new PHPExcel();

		$objPHPexl->getProperties()->setCreator("PurpleTree")->setLastModifiedBy("purple")->setTitle("PurpleTree Software")->setSubject("Purpletree")->setDescription("PurpleTree")->setKeywords("Purple")->setCategory("Purple software");
 
		$this->exportExcelData($objPHPexl,'General','product_description',$postSellerId,$postLanguageId,0);		
		$objPHPexl->createSheet(); 
		$this->exportExcelData($objPHPexl,'Data','product',$postSellerId,$postLanguageId,1);
		$objPHPexl->createSheet();
		$this->exportLinksData($objPHPexl,'Links',$postSellerId,$postLanguageId,2);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Attribute','product_attribute',$postSellerId,$postLanguageId,3);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Recurring','product_recurring',$postSellerId,$postLanguageId,4);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Discount','product_discount',$postSellerId,$postLanguageId,5);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Special','product_special',$postSellerId,$postLanguageId,6);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Image','product_image',$postSellerId,$postLanguageId,7);
		$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Rewardpoints','product_reward',$postSellerId,$postLanguageId,8);
	 	$objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'SEO','seo_url',$postSellerId,$postLanguageId,9);
		 $objPHPexl->createSheet();
		$this->exportExcelData($objPHPexl,'Design','product_to_layout',$postSellerId,$postLanguageId,10); 
		$objPHPexl->createSheet();		$this->exportExcelData($objPHPexl,'ProductOption','product_option',$postSellerId,$postLanguageId,11);  
		$objPHPexl->createSheet(); 
		$this->exportExcelData($objPHPexl,'ProductOptionValue','product_option_value',$postSellerId,$postLanguageId,12);    
		$objPHPexl->setActiveSheetIndex(0);
		 $objWriter = PHPExcel_IOFactory::createWriter($objPHPexl, 'Excel2007');
		 $objWriter->save(DIR_SYSTEM.'/library/purpletree_multivendor/export/'.$fileName.'.xlsx'); 
		 $attachment_location = DIR_SYSTEM."/library/purpletree_multivendor/export/".$fileName.".xlsx";

		if (file_exists($attachment_location)) {
		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
		header("Cache-Control: public");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length:".filesize($attachment_location));
		header("Content-Disposition: attachment; filename=PurpletreePoductExport.xlsx");
		readfile($attachment_location);
		$this->session->data['success'] = $this->language->get('text_success');
		die();
		} else {
		die("File not found.");
			}
			}
		}
		
		public function exportExcelData($objPHPexl,$tabName,$tblName,$seller_id,$language,$sheetIndex){
	
		$objPHPexl->setActiveSheetIndex($sheetIndex);
		$tableName=array();
		$exportData=array();
		//$tableName=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName($tblName);

		if($tblName=='seo_url'){
		$exportData=$this->model_extension_purpletree_multivendor_bulkproductupload->getExportSeoUrlData($tblName,$seller_id,$language);		
		} else {
		$exportData=$this->model_extension_purpletree_multivendor_bulkproductupload->getExportData($tblName,$seller_id,$language);			
		}

		if($tblName=='seo_url')
		{
			$seoExp=array();
			$seoExp=$exportData;
			$exportData=array();
			if(!empty($seoExp)){
				foreach($seoExp as $seourlData){
				if($seourlData['store_id']==0){
					$storeName='Default';
				}else {
				$storeName=$this->model_extension_purpletree_multivendor_bulkproductupload->getstoreByName($seourlData['store_id']);	
				}
				$exportData[]=array(
				'product_id'=>$seourlData['product_id'],
				'store'=>$storeName,
				'keyword'=>htmlspecialchars_decode($seourlData['keyword'])
				);	
					
				}
			}
		$tableName=array('A'=>'product_id','B'=>'store','C'=>'keyword');
		}		

		if($tblName=='product_to_layout')
		{
			$productLayout=array();
			$productLayout=$exportData;
			$exportData=array();
			if(!empty($productLayout)){
				foreach($productLayout as $layoutData){
				if($layoutData['store_id']==0){
					$storeName='Default';
					
				}else {
				$storeName=$this->model_extension_purpletree_multivendor_bulkproductupload->getstoreByName($layoutData['store_id']);	
				}
				$layout=$this->model_extension_purpletree_multivendor_bulkproductupload->getLayoutName($layoutData['layout_id']);
				
				$exportData[]=array(
				'product_id'	=>	$layoutData['product_id'],
				'store'		=>	$storeName,
				'layout'		=>	$layout
				);	
	
				}
			}
		$tableName=array('A'=>'product_id','B'=>'store','C'=>'layout');
					
		}

		if($tblName=='product_description')
		{
		$tblNames=array();
		$exportD=array();
		$tblNames=$tableName;
		$exportD=$exportData;

		$tableName=array();
		$exportData=array();
		if(!empty($exportD)){
			foreach($exportD as $kv=>$vv){
				//$approved=$this->model_extension_purpletree_multivendor_bulkproductupload->is_approved($vv['product_id']);	
			$exportData[]=array('product_id'=>$vv['product_id'],
						'name'=>htmlspecialchars_decode($vv['name']),
						'description'=>htmlspecialchars_decode($vv['description']),
						'tag'=>htmlspecialchars_decode($vv['tag']),
						'meta_title'=>htmlspecialchars_decode($vv['meta_title']),
						'meta_description'=>htmlspecialchars_decode($vv['meta_description']),
						'meta_keyword'=>htmlspecialchars_decode($vv['meta_keyword']),
						//'is_approved'=>$approved
						);
			}
			}
		$tableName=array('A'=>'product_id','B'=>'name','C'=>'description','D'=>'tag','E'=>'meta_title','F'=>'meta_description','G'=>'meta_keyword');
		}
			if($tblName=='product')
			{

			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){

				foreach($exportD as $kv=>$vv){
				$tax_class=$this->model_extension_purpletree_multivendor_bulkproductupload->getTaxClass($vv['tax_class_id']);
				$stockstatus=$this->model_extension_purpletree_multivendor_bulkproductupload->getStockStatus1($vv['stock_status_id']);
				$manufacturerName=$this->model_extension_purpletree_multivendor_bulkproductupload->getManufacturerName($vv['manufacturer_id']);
				$weight_class=$this->model_extension_purpletree_multivendor_bulkproductupload->getWeightClassName($vv['weight_class_id'],$this->request->post['language']);
				$length_class=$this->model_extension_purpletree_multivendor_bulkproductupload->getlengthClassName($vv['length_class_id'],$this->request->post['language']);
				$server=explode('admin/',HTTPS_SERVER);
				$metal=array();
				$extra_price_type=array();
				$metal = $this->language->get('text_metals');
				if($vv['metal']!=0){
				if(array_key_exists($vv['metal'],$metal)){
				$metalData=	$metal[$vv['metal']];
				} else {
				$metalData=	'';	
				}
				} else {
				$metalData='';		
				}
				$extra_price_type = $this->language->get('text_extrapricetype');
				if(array_key_exists($vv['price_extra_type'],$extra_price_type)){
				$extraTypeData= $extra_price_type[$vv['price_extra_type']];	
				} else {
					$extraTypeData= '';	
				}
				$exportData[]=array(
							'add_product'			=>	'',
							'product_id'		=>	$vv['product_id'],
							'model'				=>	$vv['model'],
							'sku'				=>	$vv['sku'],
							'upc'				=>	$vv['upc'],
							'ean'				=>	$vv['ean'],
							'jan'				=>	$vv['jan'],
							'isbn'				=>	$vv['isbn'],
							'mpn'				=>	$vv['mpn'],
							'location'			=>	$vv['location'],
							'quantity'			=>	$vv['quantity'],
							'stock_status'	=>	$stockstatus,
							'image'				=>	$vv['image']?$server[0].'image/'.$vv['image']:'',
							'manufacturer'	=>	$manufacturerName,
							'shipping'			=>	(int)$vv['shipping']?'Yes':'No',
							'price'				=>	$vv['price'],
							'price_extra_type'	=>	$extraTypeData,
							'price_extra'		=>	$vv['price_extra'],
							'points'			=>	$vv['points'],
							'tax_class'		=>	$tax_class,
							'date_available'	=>	$vv['date_available'],
							'weight'			=>	$vv['weight'],
							'weight_class'	=>	$weight_class,
							'length'			=>	$vv['length'],
							'width'				=>	$vv['width'],
							'height'			=>	$vv['height'],
							'length_class'	=>	$length_class,
							'subtract'			=>	(int)$vv['subtract']?'Yes':'No',
							'minimum'			=>	$vv['minimum'],
							'sort_order'		=>	$vv['sort_order'],
							'status'			=>	(int)$vv['status']?'Enabled':'Disabled',
							//'viewed'			=>	$vv['viewed'],
							'metal'				=>	$metalData
							);
				}
			}
			$tableName=array('A'=>'add_product','B'=>'product_id','C'=>'model','D'=>'sku','E'=>'upc','F'=>'ean','G'=>'jan','H'=>'isbn','I'=>'mpn','J'=>'location','K'=>'quantity','L'=>'stock_status','M'=>'image','N'=>'manufacturer','O'=>'shipping','P'=>'price','Q'=>'price_extra_type','R'=>'price_extra','S'=>'points','T'=>'tax_class','U'=>'date_available','V'=>'weight','W'=>'weight_class','X'=>'length','Y'=>'width','Z'=>'height','AA'=>'length_class','AB'=>'subtract','AC'=>'minimum','AD'=>'sort_order','AE'=>'status','AF'=>'metal');
			}
			if($tblName=='product_attribute')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$attribute_name=$this->model_extension_purpletree_multivendor_bulkproductupload->getAttributeName($vv['attribute_id']);	
					
				$exportData[]=array('product_id'=>$vv['product_id'],
							'attribute'=>htmlspecialchars_decode($attribute_name),
							'text'=>htmlspecialchars_decode($vv['text'])
							);
					
				}
			}
		$tableName=array('A'=>'product_id','B'=>'attribute','C'=>'text');
			}
			
			if($tblName=='product_image')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				
			$server=explode('admin/',HTTPS_SERVER);
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
				$exportData[]=array(
									'product_image_id'		=>	$vv['product_image_id'],
									'product_id'			=>	$vv['product_id'],
									'image'					=>	$vv['image']?$server[0].'image/'.$vv['image']:'',
									'sort_order'			=>	$vv['sort_order']
							);
						}
			}
					}
					$tableName=array('A'=>'product_image_id','B'=>'product_id','C'=>'image','D'=>'sort_order');
			}

			if($tblName=='product_reward')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$customerGroupName=$this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupName($vv['customer_group_id'],$this->request->post['language']);
				$exportData[]=array('product_id'=>$vv['product_id'],
							'customer_group'=>$customerGroupName,
							'points'=>$vv['points']
							);
					
				}
			}
			$tableName=array('A'=>'product_id','B'=>'customer_group','C'=>'points');
			}			
			if($tblName=='product_discount')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$customerGroupName=$this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupName($vv['customer_group_id'],$this->request->post['language']);
				$exportData[]=array(
							'product_discount_id'			=>	$vv['product_discount_id'],
							'product_id'					=>	$vv['product_id'],
							'customer_group'				=>	$customerGroupName,
							'quantity'						=>	$vv['quantity'],
							'priority'						=>	$vv['priority'],
							'price'							=>	$vv['price'],
							'date_start'					=>	$vv['date_start'],
							'date_end'						=>	$vv['date_end']
							);
				}
			}
		$tableName=array('A'=>'product_discount_id','B'=>'product_id','C'=>'customer_group','D'=>'quantity','E'=>'priority','F'=>'price','G'=>'date_start','H'=>'date_end');
			}
			
			if($tblName=='product_special')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$customerGroupName=$this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupName($vv['customer_group_id'],$this->request->post['language']);
				$exportData[]=array(
							'product_special_id'			=>	$vv['product_special_id'],
							'product_id'					=>	$vv['product_id'],
							'customer_group'				=>	$customerGroupName,
							'priority'						=>	$vv['priority'],
							'price'							=>	$vv['price'],
							'date_start'					=>	$vv['date_start'],
							'date_end'						=>	$vv['date_end']
							);
				}
			}

			$tableName=array('A'=>'product_special_id','B'=>'product_id','C'=>'customer_group','D'=>'priority','E'=>'price','F'=>'date_start','G'=>'date_end');
			}
	
			if($tblName=='product_recurring')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$customerGroupName=$this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupName($vv['customer_group_id'],$this->request->post['language']);
			
					$recurring=$this->model_extension_purpletree_multivendor_bulkproductupload->getRecurringName($vv['recurring_id'],$this->request->post['language']);
					
				$exportData[]=array(
							'product_id'					=>	$vv['product_id'],
							'recurring'					=>	$recurring,
							'customer_group'				=>	$customerGroupName
							);
				}
			}
			$tableName=array('A'=>'product_id','B'=>'recurring','C'=>'customer_group');
			}
			

			if($tblName=='product_option')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){

					$type=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionType($vv['option_id']);	
					$name=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionName($vv['option_id']);	
				$exportData[]=array('product_id'=>$vv['product_id'],
				
							'name'=>htmlspecialchars_decode($name),
							'type'=>$type,
							'value'=>htmlspecialchars_decode($vv['value']),
							'required'=>(int)$vv['required']?'Yes':'No'
							);
					
				}
			}
			$tableName=array('A'=>'product_id','B'=>'name','C'=>'type','D'=>'value','E'=>'required');
			}
			
			if($tblName=='product_option_value')
			{
			$tblNames=array();
			$exportD=array();
			$tblNames=$tableName;
			$exportD=$exportData;
			$tableName=array();
			$exportData=array();
			if(!empty($exportD)){
				foreach($exportD as $kv=>$vv){
					$type=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionName($vv['option_id']);

					$name=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionValueName($vv['option_id'],$vv['option_value_id'],$this->request->post['language']);	

				$exportData[]=array(
							'product_id'		=>$vv['product_id'],
							'option'			=>htmlspecialchars_decode($type),
							'option_value'	=>htmlspecialchars_decode($name),
							'quantity'			=>$vv['quantity'],
							'subtract'			=>($vv['subtract'])?'Yes':'No',
							'price'				=>$vv['price'],
							'price_prefix'		=>$vv['price_prefix'],
							'points'			=>$vv['points'],
							'points_prefix'		=>$vv['points_prefix'],
							'weight'			=>$vv['weight'],
							'weight_prefix'		=>$vv['weight_prefix']
							);
	
				}
			}
			$tableName=array('A'=>'product_id','B'=>'option','C'=>'option_value','D'=>'quantity','E'=>'subtract','F'=>'price','G'=>'price_prefix','H'=>'points','I'=>'points_prefix','J'=>'weight','K'=>'weight_prefix');
			}
			

		if(!empty($tableName)){	
		foreach($tableName as $key => $tbName)
		{
			if($tbName=='name' || $tbName=='description' || $tbName=='tag' || $tbName=='meta_title' || $tbName=='meta_description' || $tbName=='meta_keyword' || $tbName=='date_available' || $tbName=='download' || $tbName=='category' || $tbName=='filter' || $tbName=='related' || $tbName=='text' || $tbName=='date_start' || $tbName=='date_end' || $tbName=='value' ){
			$objPHPexl->getActiveSheet()->getStyle($key)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
			}
		
		$objPHPexl->getActiveSheet()->setCellValue($key.'1',$tbName);	
		$objPHPexl->getActiveSheet($sheetIndex)->getColumnDimension($key)->setWidth(strlen($tbName)+4);
		$objPHPexl->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(25);
		$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		 $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->getStartColor()->setARGB('800080');
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('D3D3D3'); 
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFont()->setBold(true)->setName('Verdana')->setSize(10)->getColor()->setRGB('FFFFFF');
		}
		}

		$objPHPexl->getActiveSheet()->freezePaneByColumnAndRow( 1, 2 );
		$i=0;
		if(!empty($exportData) && $exportData!=NULL){
		foreach($exportData as $key => $exportData){
			$ii = $i+2;	
			if($tableName!=NULL){
			foreach($tableName as $k=>$v){
			$objPHPexl->getActiveSheet()->setCellValue($k.''.$ii, $exportData[$v]);
			}
			}
			$i++;
		} 
		}
	 $objPHPexl->getActiveSheet()->setTitle($tabName);
	}
	
	public function exportLinksData($objPHPexl,$tabName,$seller_id,$language,$sheetIndex){	
		$objPHPexl->setActiveSheetIndex($sheetIndex);
		$tableName=array();
		$exportData=array();
		$links=array();
		$vvv=array();
		$tblfield=array();
		$product_to_download=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName('product_to_download');
		$product_to_category=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName('product_to_category');
		$product_filter=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName('product_filter');
		$product_related=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName('product_related');
		$product_to_store=$this->model_extension_purpletree_multivendor_bulkproductupload->getTableName('product_to_store');
		$links=array_merge(array_values($product_to_download),array_values($product_to_category),array_values($product_filter),array_values($product_related),array_values($product_to_store));
		$vvv=array_unique($links);
		$xcx=range('A','Z');
		$dd=array_combine(array_slice($xcx,0,count($vvv),true),$vvv);
		$dd['B']='download';
		$dd['C']='category';
		$dd['D']='filter';
		$dd['E']='related';
		$dd['F']='store';
		$ddddd = $this->model_extension_purpletree_multivendor_bulkproductupload->getExportData('product',$seller_id,$language);
		if(!empty($ddddd)&& $ddddd != NULL){
		$exportData=array_column($ddddd,'product_id');
		}
		$fetchData = array();
		$fetchData1 = array();
		if(!empty($exportData)){
	foreach($exportData as $value_product_id){
		if($value_product_id!='' || $value_product_id!=NULL ){
	
			// product Id
					$fetchData[$value_product_id] = array("product_id"=>$value_product_id);
			// product Id
			// Download id

		$downldproducts = $this->model_extension_purpletree_multivendor_bulkproductupload->getProductDownloads($value_product_id);

			if(!empty($downldproducts)) {
				if(!empty($fetchData[$value_product_id])) {
						$downld=array(); 
					$x= explode(",",$downldproducts['download_id']);
					if(!empty($x)){
					foreach($x as $download){
						if($this->model_extension_purpletree_multivendor_bulkproductupload->getProductDownloadsName($download,$this->request->post['language'])){
						$downld[] =$this->model_extension_purpletree_multivendor_bulkproductupload->getProductDownloadsName($download,$this->request->post['language']);
						}
					}
				}
					if(!empty($downld)){
					$dwn=implode(",",$downld);
					} else { $dwn=''; }

					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("download"=>$dwn));
				}
			}			
			else {
				$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("download"=>""));
			}
			// Download id
			// category id
		$cateoresproduct = $this->model_extension_purpletree_multivendor_bulkproductupload->getProductCategories($value_product_id);
		//$this->load->model('catalog/category');
			$categories=array();
			$cate=array();
			if(!empty($cateoresproduct)) {
				if(!empty($fetchData[$value_product_id])) {
					
						$categories=explode(",",$cateoresproduct['category_id']);
						if(!empty($categories)){
						foreach($categories as $categoryid){
							$category_info = $this->model_extension_purpletree_multivendor_bulkproductupload->getCategory($categoryid,$this->request->post['language']);

							if ($category_info) {
								$cate[] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
							
							}
						}	
						}
				if(!empty($cate)){
					$categoryName=implode(",",$cate);

				} else {
					$categoryName='';
				}				
	
					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("category"=>htmlspecialchars_decode($categoryName)));
				}
			} else {
				$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("category"=>''));
				
			}

			// filter
			
					$filterproduct = $this->model_extension_purpletree_multivendor_bulkproductupload->getProductFilter($value_product_id);
					//$this->load->model('catalog/filter');
				$filterexp=array();
				$filterData=array();
			if(!empty($filterproduct)) {
				if(!empty($fetchData[$value_product_id])) {
						$filterexp=explode(",",$filterproduct['filter_id']);
					if(!empty($filterexp)){
						foreach($filterexp as $filterId){
								$filter_info = $this->model_extension_purpletree_multivendor_bulkproductupload->getFilter($filterId);

								if ($filter_info) {
									$filterData[] =$filter_info['group'] . ' &gt; ' . $filter_info['name'];
									
								}	
						}
						if(!empty($filterData)){
							$filterImp=implode(",",$filterData);
							
						} else {
							$filterImp='';
							
						}
					}
					
					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("filter"=>htmlspecialchars_decode($filterImp)));
				} 
			}else {
					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("filter"=>''));		
			}
			
/* 						echo "<pre>";
			print_r($filterexp);
					  */
			// filter
			// product related
			//$this->load->model('catalog/product');
			$productrelated = $this->model_extension_purpletree_multivendor_bulkproductupload->getProductRelated($value_product_id);
				$relatedPro=array();
			if(!empty($productrelated)) {
				if(!empty($fetchData[$value_product_id])) {
					if(!empty($productrelated['related_id'])){
						$relatedProduct=explode(",",$productrelated['related_id']);
						foreach($relatedProduct as $relatedP){
								$related_info = $this->model_extension_purpletree_multivendor_bulkproductupload->getProduct($relatedP,$this->request->post['language']);

								if ($related_info) {
									$relatedPro[] =$related_info['name'];
									
								}
							
						}

					}
					if(!empty($relatedPro)){
					$relatedProductData=implode(",",$relatedPro);	
					} else {
					$relatedProductData='';	
					}
					
					
					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("related"=>htmlspecialchars_decode($relatedProductData)));
				}
			} else {
					$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("related"=>''));
			}

			// product related
			// product to store
			
			$productstore = $this->model_extension_purpletree_multivendor_bulkproductupload->getProductToStore($value_product_id);
			//$productsto = $this->model_extension_purpletree_multivendor_bulkproductupload->getstoreByName($store);
			

			$storeId=array();
			$productsto=array();
			if(!empty($productstore) and $productstore!=NULL){
			
				$storeId=explode(",",$productstore['store_id']);
				if(!empty($storeId)){
				foreach($storeId as $sid){
					if($sid!=0){
								if($this->model_extension_purpletree_multivendor_bulkproductupload->getstoreByName($sid)!=NULL){
							$productsto[] = $this->model_extension_purpletree_multivendor_bulkproductupload->getstoreByName($sid);
								}
							}else {
					
							$productsto[]='Default';	
				}		
				} 
			}
				$storename=implode(",",$productsto);
			} else {
			$storename='Default';	
	
			}

				$fetchData[$value_product_id] = array_merge($fetchData[$value_product_id],array("store"=>$storename)); 
			// product to store
		}
	} 
	}
	if(!empty($dd)){
		foreach($dd as $key => $tbName)
		{
			if($tbName=='name' || $tbName=='description' || $tbName=='tag' || $tbName=='meta_title' || $tbName=='meta_description' || $tbName=='meta_keyword' || $tbName=='date_available' || $tbName=='download' || $tbName=='category' || $tbName=='filter' || $tbName=='related' || $tbName=='text' || $tbName=='date_start' || $tbName=='date_end' || $tbName=='value' ){
			$objPHPexl->getActiveSheet()->getStyle($key)->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
			}
		
			
		$objPHPexl->getActiveSheet()->setCellValue($key.'1',$tbName);	
		$objPHPexl->getActiveSheet($sheetIndex)->getColumnDimension($key)->setWidth(strlen($tbName)+4);
		$objPHPexl->getActiveSheet($sheetIndex)->getRowDimension(1)->setRowHeight(25);
		$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		 $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFill()->getStartColor()->setARGB('800080');
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)->getColor()->setRGB('D3D3D3'); 
		  $objPHPexl->getActiveSheet($sheetIndex)->getStyle($key.'1')->getFont()->setBold(true)->setName('Verdana')->setSize(10)->getColor()->setRGB('FFFFFF');
		}
	}
		$objPHPexl->getActiveSheet()->freezePaneByColumnAndRow( 1, 2 );
		$i=0;
		

		if(!empty($fetchData) && $fetchData!=NULL ){
		foreach($fetchData as $key => $exportData){
			$ii = $i+2;	
			if(!empty($dd)){
			foreach($dd as $k=>$v){
			$objPHPexl->getActiveSheet()->setCellValue($k.''.$ii, $exportData[$v]);
			}
			}
			$i++;
		} 
		}
	 $objPHPexl->getActiveSheet()->setTitle($tabName);	
	}
	


	/* ************************************************************************************ */
	public function upload(){
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$this->load->language('purpletree_multivendor/bulkproductupload');
		
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		if(!$this->customer->validateSeller()) {
			$this->load->language('purpletree_multivendor/ptsmultivendor');
			$this->session->data['error_warning'] = $this->language->get('error_license');
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true));
		}
	if($this->config->get('module_purpletree_multivendor_subscription_plans')){

		$total_plan_Product=array();		
		$this->load->model('extension/purpletree_multivendor/bulkproductupload');

		if($this->config->get('module_purpletree_multivendor_multiple_subscription_plan_active')){
			$total_plan_Product = $this->model_extension_purpletree_multivendor_bulkproductupload->getNoOfProductForMultiplePlan($this->customer->getId());
		} else {
			$total_plan_Product = $this->model_extension_purpletree_multivendor_bulkproductupload->getNoOfProduct($this->customer->getId());
		}

		if($total_plan_Product['no_of_product']>0){
			$plan_product=$total_plan_Product['no_of_product'];			
		} else {
		$plan_product=0;	
		}
		
		$getSsellerplanStatus = $this->model_extension_purpletree_multivendor_bulkproductupload->getSsellerplanStatus($this->customer->getId());
		if(!$getSsellerplanStatus) {
			$this->session->data['error_warning']=$this->language->get('error_subscription_plan');
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true));
		}
		}
		$cwd = getcwd();
		$dir = 'library/purpletree_multivendor';
		chdir( DIR_SYSTEM.$dir );
		require_once( 'class_excel/PHPExcel.php' );
		chdir( $cwd );		
		$data= array();
		$this->load->language('purpletree_multivendor/bulkproductupload');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			//////     Create Seller folder       ///////
			//if (!empty($this->request->post['seller_id'])) {
				$sseller_id = $this->customer->getId();
				$seller_folder = "Seller_".$sseller_id;
				$directory = DIR_IMAGE . 'catalog';
				if (!is_dir($directory . '/' . $seller_folder)) {
					
					if (!isset($json['error'])) {
						mkdir($directory . '/' . $seller_folder, 0777);
						chmod($directory . '/' . $seller_folder, 0777);

						@touch($directory . '/' . $seller_folder . '/' . 'index.html');

					}
				}
			//}
		   ///////   End Create Seller folder    //////
			if($_FILES["import"]['tmp_name'] != '') {	
		  $mimes = array('application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		   if(in_array($_FILES["import"]["type"],$mimes)){
			$excelFileName=$_FILES["import"]['tmp_name'];
			$excelFileType = PHPExcel_IOFactory::identify($excelFileName);
			$objReader = PHPExcel_IOFactory::createReader($excelFileType);
			$objReader->setReadDataOnly(true);
			$objExcel = $objReader->load($excelFileName);
			
			$excelSheetNames = $objExcel->getSheetNames($excelFileName);
			$return = array();
			$excelSheetData = array();
			if(!empty($excelSheetNames)){
			foreach($excelSheetNames as $key => $sheetName){
				$objExcel->setActiveSheetIndexByName($sheetName);
				$excelSheetData[$sheetName] = $objExcel->getActiveSheet()->toArray(null, true,true,true);
			}
			}
			/* --------------------------------------< Data >------------------------------------ */
			
				$datas = array();			
				$passed_array = array();			
				$failed_array = array();			
				$new_products = array();			
				$update_products = array();	

			if (array_key_exists("Data",$excelSheetData)) {
				$this->load->model('tool/image');
				$logger = new Log('error.log'); 
				$this->load->model('extension/purpletree_multivendor/bulkproductupload');
				$proddsas = $this->model_extension_purpletree_multivendor_bulkproductupload->getTotalProductsProductTable();
				//$numberOfField=32;
				//$tableName='product';
				
					$produtbl=array(
						'A'=>'add_product',
						'B' => 'product_id',
						'C' => 'model',
						'D' => 'sku',
						'E' => 'upc',
						'F' => 'ean',
						'G' => 'jan',
						'H' => 'isbn',
						'I' => 'mpn',
						'J' => 'location',
						'K' => 'quantity',
						'L' => 'stock_status',
						'M' => 'image',
						'N' => 'manufacturer',
						'O' => 'shipping',
						'P' => 'price',
						//'Q' => 'price_extra_type',
						//'R' => 'price_extra',
						'S' => 'points',
						'T' => 'tax_class',
						'U' => 'date_available',
						'V' => 'weight',
						'W' => 'weight_class',
						'X' => 'length',
						'Y' => 'width',
						'Z' => 'height',
						'AA' => 'length_class',
						'AB' => 'subtract',
						'AC' => 'minimum',
						'AD' => 'sort_order',
						'AE' => 'status',
						//'AE' => 'viewed',
						//'AF' => 'metal'
					);
					$produc= $this->CompareTable($produtbl,$excelSheetData['Data'][1],$logger,'Data');
							
				if(!empty($excelSheetData['Data']) && $produc ) {
				$datas = $this->excelSheetProductData($excelSheetData['Data']);
						if(!empty($proddsas)) {
							foreach($proddsas as $ssssa) {
								$passed_array[] = $ssssa['product_id'];
							}
						}
						if(!empty($datas)) {
				foreach($datas as $key => $data)
				{
				if($key!=1) {	
				
				$sourcecode = $this->GetImageFromUrl(trim($data['image']));
				try {
						if(is_numeric($data['product_id']) && $data['product_id']!='' && !is_string($data['product_id']))
						{
					 ///start///
					//for metal
				        $metal_type='';	
					if(isset($data['metal'])){						
					   if(!empty($data['metal'])){
							if($data['metal']=='Gold'){
							  $metal_type=1;	
							}elseif($data['metal']=='Silver'){
							  $metal_type=2;	
							}elseif($data['metal']=='Platinum'){
							  $metal_type=3;	
							}elseif($data['metal']=='Palladium'){
							  $metal_type=4;	
							}elseif($data['metal']=='Copper'){
							  $metal_type=5;	
							}elseif($data['metal']=='Rhodium'){
							  $metal_type=6;	
							}	
							}elseif($data['metal']==''){
							  $metal_type=0;	
							}
						}
						//For stock_status
						$stock_status = $this->model_extension_purpletree_multivendor_bulkproductupload->getStockStatusId($data['stock_status']);
						
						//For Manufacturer_id
						$manufacturer = $this->model_extension_purpletree_multivendor_bulkproductupload->getManufacturerId($data['manufacturer']);
				       
					    //For Shipping
						if($data['shipping']=='Yes'){
						   $shipping_status=1;
						}else{
						   $shipping_status=0;
						}
						
						//For Price Extra Type
						 $price_ext_type='';
						 if(isset($data['price_extra_type'])){	
						if(!empty($data['price_extra_type'])){
							if($data['price_extra_type']=='Fixed'){
							  $price_ext_type=1;	
							}elseif($data['price_extra_type']=='Percentage'){
							  $price_ext_type=2;	
							}
						}elseif($data['price_extra_type']==''){
							  $price_ext_type=0;	
							}
						}
						//For Tax_Class
						
						$tex_class = $this->model_extension_purpletree_multivendor_bulkproductupload->getTaxClassId($data['tax_class']);
						
						//For Weight_Class_Id 
						$weight_class = $this->model_extension_purpletree_multivendor_bulkproductupload->getWeightClassId($data['weight_class']);
						
						//For Length_Class_Id
                        $length_class = $this->model_extension_purpletree_multivendor_bulkproductupload->getLengthClassId($data['length_class']);						
						
						//For Subtract Stock
						if($data['subtract']=='Yes'){
						   $subtract_status=1;
						}else{
						   $subtract_status=0;
						}
						
						//For status
						if($data['status']=='Enabled'){
							if($this->config->get('module_purpletree_multivendor_product_approval')) {
								$data_status = 0;
							} else {
								$data_status=1;
							}
						}else{
						   $data_status=0;
						}
						
						$imagesourc = '';
						if($sourcecode !== FALSE) {							
							$imagesourc = 'catalog/Seller_'.$sseller_id.'/'.basename(trim($data['image']));
						}
						
						$filter_data = array(
						'add_product'=>$data['add_product'],
						'product_id' => $data['product_id'],
						//'metal' => $metal_type,
						'model' => $data['model'],
						'sku' => $data['sku'],
						'upc' => $data['upc'],
						'ean' => $data['ean'],
						'jan' => $data['jan'],
						'isbn' => $data['isbn'],
						'mpn' => $data['mpn'],
						'location' => $data['location'],
						'quantity' => $data['quantity'],
						'stock_status_id' => $stock_status,
						//'image' => $data['image'],
						'manufacturer_id' => $manufacturer,
						'shipping' => $shipping_status,
						'price' => $data['price'],
						//'price_extra_type' => $price_ext_type,
						//'price_extra' => $data['price_extra'],
						'points' => $data['points'],
						'tax_class_id' => $tex_class,
						'date_available' => $data['date_available'],
						'weight' => $data['weight'],
						'weight_class_id' => $weight_class,
						'length' => $data['length'],
						'width' => $data['width'],
						'height' => $data['height'],
						'length_class_id' => $length_class,
						'subtract' => $subtract_status,
						'minimum' => $data['minimum'],
						'sort_order' => $data['sort_order'],
						'status' => $data_status,
						'image'	=>$imagesourc,
						);
						
						   ///End///
						

							$numofproduct=$this->model_extension_purpletree_multivendor_bulkproductupload->getTotalProductProductTable($filter_data);
							//if($numofproduct==0){
								$addProduct_action=strtoupper(trim($filter_data['add_product']));
								if($addProduct_action==='YES' ){
								// Subscription
									if($this->config->get('module_purpletree_multivendor_subscription_plans')){
									$total_store_Product=array();
										$total_store_Product = $this->model_extension_purpletree_multivendor_bulkproductupload->sellerTotalProduct($this->customer->getId());
									if($total_store_Product['total_product']>0){
										$store_product=$total_store_Product['total_product'];			
									} else {
									$store_product=0;	
									}
									if($plan_product < $store_product){
										$this->session->data['error_warning']=$this->language->get('error_subscription_plan_limit');
			
			                         $this->response->redirect($this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '', true));
									}
									}
								// Subscription
								
								$product_id = $this->model_extension_purpletree_multivendor_bulkproductupload->addProductDataTab($filter_data);
								if($sourcecode !== FALSE){							
								$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$sseller_id."/".basename(trim($data['image'])), 'w');
								@fwrite($savefile, $sourcecode);
								@fclose($savefile);
								}
								if($sourcecode !== FALSE){							
							if(!empty($this->request->post['seller_id'])){
								$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$this->request->post['seller_id']."/".basename(trim($data['image'])), 'w');
							}else{
								$savefile = @fopen(DIR_IMAGE."catalog/".basename(trim($data['image'])), 'w');
							}
								
								@fwrite($savefile, $sourcecode);
								@fclose($savefile);
								}
								$logger->write("Product ID ".$product_id." Uploaded Successfully");
								$passed_array[] = $product_id;	
								$new_products[$filter_data['product_id']] = $product_id;								
							}else {
								if(isset($this->request->post['dataoverwrite'])==true)
								{
								if(isset($filter_data['product_id'])){
								$product_check=$this->model_extension_purpletree_multivendor_bulkproductupload->getInProductId($filter_data['product_id']);	
								 if($product_check){	
									
								$this->model_extension_purpletree_multivendor_bulkproductupload->editProductDataTab($filter_data);
								if($sourcecode !== FALSE){							
								$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$sseller_id."/".basename(trim($data['image'])), 'w');
								@fwrite($savefile, $sourcecode);
								@fclose($savefile);}
								$logger->write("Product ID ".$filter_data['product_id']." Updated Successfully"); 
								$passed_array[] = $filter_data['product_id'];
								$update_products[] = $filter_data['product_id'];
								
								} else {									
								$logger->write("Product ID ".$filter_data['product_id']." Invalid !");
								$failed_array[] = $filter_data['product_id'];
								}
								}
								
								} else {
									
								$logger->write("Product ID ".$filter_data['product_id']." Duplicate !");
								$failed_array[] = $filter_data['product_id'];	
								}
							}
							
						}else {
						
						$logger->write("Product ID ".$data['product_id']." Invalid !");
						$failed_array[] = $data['product_id'];	
						}
				}catch(Exception $e){ 
						$logger->write("Product ID ".$data['product_id']." Error ! ".$e->getMessage()); 
						$failed_array[] = $data['product_id'];	
						}
				}
					}
				}
			}
		}
			/* --------------------------------------< /Data >--------------------------------- */

			/* --------------------------------------< General Tab >---------------------------*/
			if (array_key_exists("General",$excelSheetData)) {
				//$this->load->model('localisation/language');
				$excelSheetTableFieldss = array();
				
				 $genData=$this->excelSheetProductData($excelSheetData['General']);
				 
			/* 		foreach($excelSheetData['General'] as $key=>$value){
						$excelSheetTableField =array_values($value);
						$excelSheetTableFieldss[]= array_splice($excelSheetTableField,0,$numberOfField);
					} */
					
					$tbl=array(
					            'A' =>	'product_id',
								'B' =>  'name',
								'C' =>  'description',
								'D' =>  'tag',
								'E' =>  'meta_title',
								'F' =>  'meta_description',
								'G' =>  'meta_keyword',
								//'H' =>  'is_approved'
								);
							$gen= $this->CompareTable($tbl,$excelSheetData['General'][1],$logger,'General');				
			}
			try {
			if(!empty($genData) && $gen && $produc) {
					$langs = array();
					$dsds = array();
					$dattt = array();
					$gfgf = array();
					$gfgf11 = array();
					$ddd = array();
					$this->load->model('extension/purpletree_multivendor/bulkproductupload');
				/* 	foreach($excelSheetTableFieldss as $keyyyy => $value1) {
					if($keyyyy == 0){
						foreach($value1 as $key=>$value) {
							$gfgf[$value] = $value;
						}
					}
						$ddd[] = array_combine($gfgf,$value1);
					} */
					$ddd=$genData;
					$langidd 			= $this->request->post['language'];
					$generalData = array();
if(!empty($ddd)) {
	foreach($ddd as $key11 => $ffff) {

		
		if($key11 != 1){

	if(!in_array($ffff['product_id'],$failed_array)) {
				$product_id				= (int)$ffff['product_id'];
			$product_id_old			= (int)$ffff['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}
				if(in_array($product_id,$passed_array)) {
					

			//$product_id			= $ffff['product_id'];
			$name 				= $ffff['name'];
			$description 		= $ffff['description'];
			$tag 				= $ffff['tag'];
			$meta_title 		= $ffff['meta_title'];
			$meta_description 	= $ffff['meta_description'];
			$meta_keyword 		= $ffff['meta_keyword'];
			$generalData['product_description'][$langidd] = array(
					'product_id' 		=> $product_id,
					'name' 				=> $name,
					'description' 		=> $description,
					'tag' 				=> $tag,
					'meta_title' 		=> $meta_title,
					'meta_description' 	=> $meta_description,
					'meta_keyword' 		=> $meta_keyword
				);

		try {

			if(is_numeric($product_id) && $product_id !='') {

				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductGeneralTab($generalData);
					$logger->write("Product ID ".$product_id." General data Uploaded Successfully"); 
				} else {

					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) {

						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductGeneralTab($generalData);
						$logger->write("Product ID ".$product_id."General data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id." General data Failed !");
							$failed_array[] = $product_id;
						}
					
				}
			} else {
				$logger->write("Product ID ".$product_id." General data Invalid !");
				$failed_array[] = $product_id;
			}
		}catch(Exception $e){ 
				$logger->write("Product ID".$product_id." General data Error! ".$e->getMessage()); 
				$failed_array[] = $product_id;
				}
				//

	}
		} 
	}
	}
		}
		}
		}catch(Exception $e){ 
				$logger->write("General data Error! ".$e->getMessage()); 
				}
		

	/* --------------------------------------< /General Tab >--------------------------- */	
	/* --------------------------------------< Links >--------------------------- */	

			 if (array_key_exists("Links",$excelSheetData)) {
				 
			try {
				$excelSheetData['Links'][1];
				$excelSheetLinkData = array();
				 $excelSheetLinkData=$this->excelSheetProductData($excelSheetData['Links']);
			/* 	foreach($excelSheetData['Links'] as $key=>$vlaue ) {
					$excelSheetLinkData[]=array_combine(array_values($excelSheetData['Links'][1]),array_values($vlaue));
				} */

 						$linktbl=array(
								'product_id' => 'product_id',
								'download' => 'download',
								'category' => 'category',
								'filter' => 'filter',
								'related' => 'related',
								'store' => 'store'
								);
							$linkvalidate= $this->CompareTable($linktbl,$excelSheetData['Links'][1],$logger,'Links');
				 
				
				$value1 = array();
				if(!empty($excelSheetLinkData) && $linkvalidate && $gen && $produc ) {
				foreach($excelSheetLinkData as $key=>$value)
				{
					
				if($key!=1){
		if(!in_array($value['product_id'],$failed_array)) {
		$product_id				= (int)$value['product_id'];
				$product_id_old			= (int)$value['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id];
					}
					
		if(in_array($product_id,$passed_array)) {
			
/* -------------------------------------DOWNLOAD----------------------------------------------------- */
								
			$downloadId=array();
			if(substr_count($value['download'],',')>0){
				$exp1= explode(',',$value['download']);
				if(!empty($exp1)){
					foreach($exp1 as $vvv1){
						$downloadId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getDownloadId($vvv1,$this->request->post['language']);
							}
						}
					} else {
					$downloadId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getDownloadId($value['download'],$this->request->post['language']);	
					}
	
/* -------------------------------------CATEGORY----------------------------------------------------- */
			$category_id=array();
			$id=array();
			if(substr_count($value['category'],',')>0){
				$exp= explode(',',trim($value['category']));
					if(!empty($exp)){
					foreach($exp as $vvv){
						if(substr_count($vvv,'>')>0){
						$impData=explode('>',trim($vvv));
						
						if(!empty($impData)){
							$lastname		= end($impData);
							$secondLast		= prev($impData);
							$lastids		= $this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryIds($lastname,$this->request->post['language']);
							if(!empty($lastids)) {
									$ccids=array();
									if(!empty($lastids)){
									foreach($lastids as $catiidss){
										$ccids[] = $catiidss['category_id'];
									}
							}
									$y=array();
									if(!empty($ccids)){
								foreach($ccids as $ciddd){
									$x = $this->model_extension_purpletree_multivendor_bulkproductupload->getParentId($ciddd);
									if(!empty($x)) {
										foreach($x as $x1) {
											$y[$x1] = $ciddd;
										}
									}
								}
							}
								$secondLastId	=	$this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryIds1($secondLast,$this->request->post['language']);
								if($secondLastId != '') {
									if(!empty($y)){
									foreach($y as $y1 =>$y2) {
										if($y1 == $secondLastId) {
											$category_id[] = $y2;
										}
									}
								}
								}
							} 
						}
						}else {
						$category_id[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryId(htmlspecialchars($vvv,ENT_QUOTES));
						}
						
							}
						}
					} else {
						//without comma
						$vvv = $value['category'];
						if(substr_count($vvv,'>')>0){
						$impData=explode('>',trim($vvv));
						
						if(!empty($impData)){
							$lastname		= end($impData);
							$secondLast		= prev($impData);
							$lastids		= $this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryIds($lastname,$this->request->post['language']);
							if(!empty($lastids)) {
									$ccids=array();
									if(!empty($lastids)){
									foreach($lastids as $catiidss){
										$ccids[] = $catiidss['category_id'];
										}
									}
									$y=array();
									if(!empty($ccids)){
								foreach($ccids as $ciddd){
									$x = $this->model_extension_purpletree_multivendor_bulkproductupload->getParentId($ciddd);
									if(!empty($x)) {
										foreach($x as $x1) {
											$y[$x1] = $ciddd;
										}
									}
								}
							}
								$secondLastId	=	$this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryIds1($secondLast,$this->request->post['language']);
								if($secondLastId != '') {
									if(!empty($y)){
									foreach($y as $y1 =>$y2) {
										if($y1 == $secondLastId) {
											$category_id[] = $y2;
										}
									}
								}
								}
							} 
						}
						}else {
						$category_id[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getCategoryId(htmlspecialchars($vvv,ENT_QUOTES));
						}
						//without comma
						
						//
					
						
					}
					
					
/* -------------------------------------/CATEGORY----------------------------------------------------- */
/* -------------------------------------FILTER----------------------------------------------------- */
			$filterId=array();
			if(substr_count($value['filter'],',')>0){
				$exp2= explode(',',$value['filter']);
				if(!empty($exp2)){
					foreach($exp2 as $vvv2){
						if(!empty($vvv2)){
							$filterGroup=explode(">",$vvv2);
							$filterGroupId= $this->model_extension_purpletree_multivendor_bulkproductupload->getFilterGroupId(htmlspecialchars($filterGroup[0]),$this->request->post['language']);
							if($filterGroupId!=''){
							$filterId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getFilterId(htmlspecialchars($filterGroup[1]),$this->request->post['language'],$filterGroupId);
							}
						}
						}
						}
					} else {
						if(substr_count($value['filter'],'>')>0){
						$x=explode(">",$value['filter']);
						$filtergid=$this->model_extension_purpletree_multivendor_bulkproductupload->getFilterGroupId(trim($x[0]),$this->request->post['language']);

						if($filtergid!=''){
						$filterId[]=$this->model_extension_purpletree_multivendor_bulkproductupload->getFilterId(htmlspecialchars($x[1]),$this->request->post['language'],$filtergid); 
						}
					} else {
						
					$filterId='';	
					}
					}
		
/* -------------------------------------/FILTER----------------------------------------------------- */			
/* -------------------------------------STORE----------------------------------------------------- */	
			$StoreId=array();
			if(substr_count($value['store'],',')>0){
				$exp4= explode(',',$value['store']);
				if(!empty($exp4)){
					foreach($exp4 as $vvv4){
						//echo '--'.$vvv4.'--';
						   if($this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($vvv4)==''){

							$StoreId[] = '0';   
						   } else {
							  // echo "b";
							$StoreId[] = $this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($vvv4);
						   }
							}
						}
					} else {
						if($this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($value['store'])==''){
						$StoreId[]='0';		
						//echo "c";
						} else {
							//echo "d";
						$StoreId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($value['store']);
						}
					}
					
		
/* -------------------------------------RELATED----------------------------------------------------- */
					
				$relatedId=array();
				if(substr_count($value['related'],',')>0){
				$exp5= explode(',',$value['related']);
				if(!empty($exp5)){
					foreach($exp5 as $vvv5){
						
				
						$relatedId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getProductIdByName(htmlspecialchars($vvv5),$this->request->post['language']);

							}
						}
					} else {
						$relatedId[]= $this->model_extension_purpletree_multivendor_bulkproductupload->getProductIdByName(htmlspecialchars($value['related']),$this->request->post['language']);
					}
					

/* -------------------------------------RELATED----------------------------------------------------- */
				//$product_id 					= $value['product_id'];
				$value1['product_id']  			= $product_id ;
				$value1['product_category'] 	= $category_id;
				$value1['product_filter'] 		= $filterId;
				$value1['product_store']    	= $StoreId;
				$value1['product_download']  	= $downloadId;
				$value1['product_related']  	= $relatedId;

				//$product_filter 			= explode(',',$value['filter']);
				//$product_related 			= explode(',',$value['related_id']);
/* 				$value1['manufacturer_id']   = $value['manufacturer_id'];
				$value1['manufacturer'] 		= $value['manufacturer']; */

		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
				
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductLinkTab($value1);
					$logger->write("Product ID ".$product_id." Link data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])== true) {
						if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) {
						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductLinkTab($value1,$product_id);
						$logger->write("Product  ID ".$product_id." Link data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id." Link data Duplicate !");
							
							
						}
					
				}
			} }else {
				$logger->write("Product ID ".$product_id." Link data Invalid");


			}
		}catch(Exception $e){ 

				$logger->write("Product ID ".$product_id." Link data Error! ".$e->getMessage()); 
				
				}
				}
	}
			}

				}
				}
						} catch(Exception $e){ 

				$logger->write("Link data Error! ".$e->getMessage()); 
				
				}

			}

		/* --------------------------------------< /Links >------------------------------- */	
	
		
		/* --------------------------------------< Attribute >------------------------------- */
				$excelSheetAttributeData=array();
			$attribute_language_id 			= $this->request->post['language'];
			 if (array_key_exists("Attribute",$excelSheetData)) {
				 try {
					 $excelSheetAttributeData=$this->excelSheetProductData($excelSheetData['Attribute']);	
			/* 	foreach($excelSheetData['Attribute'] as $key=>$vlaue ) {
					$excelSheetAttributeData[]=array_combine(array_values($excelSheetData['Attribute'][1]),array_values($vlaue));
				} */
				
							$attribTbl=array(
										'product_id' => 'product_id',
										'attribute' => 'attribute',
										'text' => 'text'
									);
							$attribvalidate= $this->CompareTable($attribTbl,$excelSheetData['Attribute'][1],$logger,'Attribute');
				 
				if(!empty($excelSheetAttributeData) && $attribvalidate && $gen && $produc ) {
				foreach($excelSheetAttributeData as $key=>$valueAttribute)
				{
					$attributeData=array();
				if($key!=1){
				if(!in_array($valueAttribute['product_id'],$failed_array)) {
				$product_id				= (int)$valueAttribute['product_id'];
				$product_id_old			= (int)$valueAttribute['product_id'];
			
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}
				if(in_array($product_id,$passed_array)) {
				$attribueId=$this->model_extension_purpletree_multivendor_bulkproductupload->getAttributeId($valueAttribute['attribute'],$attribute_language_id); 

				$attributeData['product_attribute'][]= array(
				'language_id'						=>		$attribute_language_id,
				'product_id'						=>		$product_id	,
				'attribute_id'						=>		$attribueId,
				'product_attribute_description'		=>	array(	
				 $attribute_language_id				=>	array(	
				 'text'								=>      $valueAttribute['text']
				)));
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductAttributeTab($attributeData);
					$logger->write("Product ID ".$product_id." Attribute data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) {
						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductAttributeTab($attributeData);
						$logger->write("Product  ID ".$product_id." Attribute data Updated Successfully"); 
					
						} else {
							$logger->write("Product ID ".$product_id." Attribute data Duplicate!");
						
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Attribute data Invalid");
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Attribute data Error! ".$e->getMessage()); 
			
				}
				}
				}
			}

				}
			 }

}catch(Exception $e){ 
				$logger->write("Attribute data Error! ".$e->getMessage()); 
			
				}
			}	
		/* --------------------------------------< /Attribute >------------------------------ */

		/* --------------------------------------< Recurrng >---------------------------- */	
$excelSheetRecurringData=array();
		
			 if (array_key_exists("Recurring",$excelSheetData)) {
				 try {
				$excelSheetRecurringData=$this->excelSheetProductData($excelSheetData['Recurring']);		
				/* foreach($excelSheetData['Recurring'] as $key=>$vlaue ) {
					$excelSheetRecurringData[]=array_combine(array_values($excelSheetData['Recurring'][1]),array_values($vlaue));
				} */
				
									$recurrTbl=array(
									'product_id' => 'product_id',
									'recurring' => 'recurring',
									'customer_group' => 'customer_group'
									);
							$recurrvalidate= $this->CompareTable($recurrTbl,$excelSheetData['Recurring'][1],$logger,'Recurring');
				 
			if(!empty($excelSheetRecurringData) && $recurrvalidate && $gen && $produc ) {
				foreach($excelSheetRecurringData as $key=>$valueRecurring)
				{
					$recurringData=array();
				if($key!=1){
				if(!in_array($valueRecurring['product_id'],$failed_array)) {
				$recurring_product_id	  = (int)$valueRecurring['product_id'];
				$recurring_product_id_old = (int)$valueRecurring['product_id'];
				if (array_key_exists($recurring_product_id_old,$new_products)) {
					$recurring_product_id = $new_products[$recurring_product_id];
					}	
					
				if(in_array($recurring_product_id,$passed_array)) {
				$recurring_id= $this->model_extension_purpletree_multivendor_bulkproductupload->getRecurringId($valueRecurring['recurring'],$this->request->post['language']);
				
				$customer_group_id= $this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupId($valueRecurring['customer_group'],$this->request->post['language']);
				
				

				$product_id			 	=		$recurring_product_id;
				$recurringData['product_recurring'][]	 = array(
				'product_id'			=>		$recurring_product_id,
				'recurring_id'			=>		$recurring_id,
				'customer_group_id'		=>		$customer_group_id );
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($recurring_product_id_old,$new_products)) {
						$product_id = $new_products[$recurring_product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductRecurringTab($recurringData,$product_id);
					$logger->write("Product ID ".$product_id." Recurring data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) {
						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductRecurringTab($recurringData,$product_id);
						$logger->write("Product  ID ".$product_id." Recurring data Updated Successfully"); 
					
						} else {
							$logger->write("Product ID ".$product_id." Recurring data Duplicate !");
						
						}
				}
			} else {
				$logger->write("Product ID ".$product_id."  Recurring data Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Recurring data Error! ".$e->getMessage()); 
			
				}
				}
				}
			}

				}
			 }
			 	}catch(Exception $e){ 
				$logger->write("Recurring data Error! ".$e->getMessage()); 
			
				}
			}	

/* --------------------------------------< /Recurring >--------------------------- */
/* --------------------------------------< Discount >----------------------------- */	
		$excelSheetDiscountData=array();
	
			 if (array_key_exists("Discount",$excelSheetData)) {
				 try {
				$excelSheetDiscountData=$this->excelSheetProductData($excelSheetData['Discount']);		
/* 				foreach($excelSheetData['Discount'] as $key=>$vlaue ) {
					$excelSheetDiscountData[]=array_combine(array_values($excelSheetData['Discount'][1]),array_values($vlaue));
				} */
				
							
								$discTbl=array(
										'product_discount_id' => 'product_discount_id',
										'product_id' => 'product_id',
										'customer_group' => 'customer_group',
										'quantity' => 'quantity',
										'priority' => 'priority',
										'price' => 'price',
										'date_start' => 'date_start',
										'date_end' => 'date_end'
								);
							$discvalidate= $this->CompareTable($discTbl,$excelSheetData['Discount'][1],$logger,'Discount');
				
			if(!empty($excelSheetDiscountData) && $discvalidate && $gen && $produc ) {
				foreach($excelSheetDiscountData as $key=>$valueDiscount)
				{
				$discountData=array();
				if($key!=1){
				if(!in_array($valueDiscount['product_id'],$failed_array)) {
					
				$product_id				= (int)$valueDiscount['product_id'];
				$product_id_old			= (int)$valueDiscount['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}	
					
				if(in_array($product_id,$passed_array)) {
				$customer_group_id1= $this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupId($valueDiscount['customer_group'],$this->request->post['language']);
				
				if(date('Y-m-d',strtotime($valueDiscount['date_start']))=='1970-01-01' || date('Y-m-d',strtotime($valueDiscount['date_start']))=='' ){
				$setDiscountStartDate='0000-00-00';
				}else {
				$setDiscountStartDate=Date('Y-m-d',strtotime($valueDiscount['date_start']));
				
				}
				
				if(date('Y-m-d',strtotime($valueDiscount['date_end']))=='1970-01-01' || date('Y-m-d',strtotime($valueDiscount['date_end']))=='' ){
				$setDiscountEndDate='0000-00-00';
				}else {
				$setDiscountEndDate=date('Y-m-d',strtotime($valueDiscount['date_end']));
				
				}	

				$product_discount_id	=		$valueDiscount['product_discount_id'];
				$discountData['product_discount'][]	 = array(
				'product_discount_id'   =>      $valueDiscount['product_discount_id'],
				'product_id'			=>		$product_id,
				'customer_group_id'		=>		$customer_group_id1,
				'quantity'				=>		$valueDiscount['quantity'],
				'priority'				=>		$valueDiscount['priority'],
				'price'					=>		$valueDiscount['price'],
				'date_start'			=>		$setDiscountStartDate,
				'date_end'				=>		$setDiscountEndDate);
				
				
				$discountId=$this->model_extension_purpletree_multivendor_bulkproductupload->getProductDiscountId();
				$discount_id=array();
				if(!empty($discountId)){
				foreach($discountId as $key => $disId){
				$discount_id[]=$disId['product_discount_id'];	
				}
				}

				
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];

					if($product_discount_id ==''){
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductDiscountTab($discountData);
					$logger->write("Product ID ".$product_id." Discount data Uploaded Successfully"); 
					} elseif(in_array($product_discount_id,$discount_id)) {
					$logger->write("Product ID ".$product_id." Discount data ".$product_discount_id."  duplicate "); 
					}else {
					$logger->write("Product ID ".$product_id." Discount data ".$product_discount_id." Invalid Id"); 
					}
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 
					if($product_discount_id=='') {
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductDiscountTab($discountData);
					$logger->write("Product ID ".$product_id." Discount data Uploaded Successfully");	
					}elseif(in_array($product_discount_id,$discount_id)) {
					$this->model_extension_purpletree_multivendor_bulkproductupload->editProductDiscountTab($discountData);
					$logger->write("Product  ID ".$product_id." Discount data Updated Successfully");
					} else {
					$logger->write("Product  ID ".$product_id." Discount data ".$product_discount_id." Invalid Id");	
					} 					
						} else {
							$logger->write("Product ID ".$product_id." Discount data Duplicate !");					
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Discount data Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Discount data Error! ".$e->getMessage()); 
				
				}
				}
				}
			}
				}
			 }
	}catch(Exception $e){ 
				$logger->write("Discount data Error! ".$e->getMessage()); 
				
				}
				
			}	
		/* --------------------------------------< /Discount >--------------------------- */
		/* ********************************************************************************** */
		/* --------------------------------------< Special >---------------------------- */	
		$excelSheetSpecialData=array();
	
			 if (array_key_exists("Special",$excelSheetData)) {
				 try {
					$excelSheetSpecialData=$this->excelSheetProductData($excelSheetData['Special']);				
			/* 	foreach($excelSheetData['Special'] as $key=>$vlaue ) {
					$excelSheetSpecialData[]=array_combine(array_values($excelSheetData['Special'][1]),array_values($vlaue));
				} */
		
							$specTbl=array(
								'product_special_id' => 'product_special_id',
								'product_id' => 'product_id',
								'customer_group' => 'customer_group',
								'priority' => 'priority',
								'price' => 'price',
								'date_start' => 'date_start',
								'date_end' => 'date_end'
							);
							$specvalidate= $this->CompareTable($specTbl,$excelSheetData['Special'][1],$logger,'Special');
				 
				
			if (!empty($excelSheetSpecialData) && $specvalidate && $gen && $produc ) {
				foreach($excelSheetSpecialData as $key=>$valueSpecial)
				{
				$specialData=array();
				if($key!=1){
				if(!in_array($valueSpecial['product_id'],$failed_array)) {
						$product_id				= (int)$valueSpecial['product_id'];
						$product_id_old			= (int)$valueSpecial['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}
					
				if(in_array($product_id,$passed_array)) {
					
					$customer_group_id2= $this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupId($valueSpecial['customer_group'],$this->request->post['language']);
					
					
				if(date('Y-m-d',strtotime($valueSpecial['date_start']))=='1970-01-01' || date('Y-m-d',strtotime($valueSpecial['date_start']))=='' ){
				$setSpecialStartDate='0000-00-00';
				}else {
				$setSpecialStartDate=Date('Y-m-d',strtotime($valueSpecial['date_start']));
				}
				
				if(date('Y-m-d',strtotime($valueSpecial['date_end']))=='1970-01-01' || date('Y-m-d',strtotime($valueSpecial['date_end']))=='' ){
				$setSpecialEndDate='0000-00-00';
				}else {
				$setSpecialEndDate=date('Y-m-d',strtotime($valueSpecial['date_end']));
				
				}	

				$product_special_id		=		$valueSpecial['product_special_id'];
				$specialData['product_special'][]	= array(
				'product_special_id'	=>		$valueSpecial['product_special_id'],
				'product_id'			=>		$product_id,
				'customer_group_id'		=>		$customer_group_id2,
				'priority'				=>		$valueSpecial['priority'],
				'price'					=>		$valueSpecial['price'],
				'date_start'			=>		$setSpecialStartDate,
				'date_end'				=>		$setSpecialEndDate );

				$specialid=$this->model_extension_purpletree_multivendor_bulkproductupload->getProductSpecialId();
				$special_id=array();
				if(!empty($specialid)){
				foreach($specialid as $key => $speId){
				$special_id[]=$speId['product_special_id'];	
				}
				}
		try {
			if(is_numeric($product_id) && $product_id !='') {
			if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					if($product_special_id ==''){
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductSpecialTab($specialData,$product_id);
					$logger->write("Product ID ".$product_id." Special data Uploaded Successfully"); 
					} elseif(in_array($product_special_id,$special_id)) {
					$logger->write("Product ID ".$product_id." Special data ".$product_special_id."  duplicate id "); 
					}else {
					$logger->write("Product ID ".$product_id." Special data ".$product_special_id." Invalid Id"); 
					}

				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 
					if($product_special_id=='') {
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductSpecialTab($specialData,$product_id);
					$logger->write("Product ID ".$product_id." Special data Uploaded Successfully");	
					}elseif(in_array($product_special_id,$special_id)) {
					$this->model_extension_purpletree_multivendor_bulkproductupload->editProductSpecialTab($specialData,$product_id);
					$logger->write("Product  ID ".$product_id." Special data Updated Successfully");
					} else {
					$logger->write("Product  ID ".$product_id." Special data ".$product_special_id." Invalid Id");	
					} 

						} else {
							$logger->write("Product ID ".$product_id."Special data Duplicate !");
						
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Special data Invalid");
				
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Special data Error! ".$e->getMessage()); 
			
				}
				}
				}
			}
				}
			 }
}catch(Exception $e){ 
				$logger->write("Special data Error! ".$e->getMessage()); 
			
				}

				
			}	
		/* --------------------------------------< /Special >--------------------------- */	
		/* ********************************************************************************** */
		/* --------------------------------------< Reward Points >---------------------------- */	
			$excelSheetRewardpointsData=array();
	
			 if (array_key_exists("Rewardpoints",$excelSheetData)) {
				 try {
				$excelSheetRewardpointsData=$this->excelSheetProductData($excelSheetData['Rewardpoints']);		
			/* 	foreach($excelSheetData['Rewardpoints'] as $key=>$vlaue ) {
					$excelSheetRewardpointsData[]=array_combine(array_values($excelSheetData['Rewardpoints'][1]),array_values($vlaue));
				} */
				
							$rewardTbl=array(
							'product_id' => 'product_id',
							'customer_group' => 'customer_group',
							'points' => 'points'
							);
							$rewardvalidate= $this->CompareTable($rewardTbl,$excelSheetData['Rewardpoints'][1],$logger,'Rewardpoints');
				 
			if (!empty($excelSheetRewardpointsData) && $rewardvalidate && $gen && $produc ) {
				foreach($excelSheetRewardpointsData as $key=>$valueRewardpoints)
				{
				$rewardpointsData=array();
				if($key!=1){
				if(!in_array($valueRewardpoints['product_id'],$failed_array)) {
					$product_id				= (int)$valueRewardpoints['product_id'];
					$product_id_old			= (int)$valueRewardpoints['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}
				if(in_array($product_id,$passed_array)) {
				$customer_group_id3= $this->model_extension_purpletree_multivendor_bulkproductupload->getCustomerGroupId($valueRewardpoints['customer_group'],$this->request->post['language']);


				$rewardpointsData['product_reward'][$customer_group_id3]	 = array(
				'product_id'			=>		$product_id,
				'points'				=>		$valueRewardpoints['points'] );

		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductRewardpointsTab($rewardpointsData);
					$logger->write("Product ID ".$product_id." Reward points data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 

						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductRewardpointsTab($rewardpointsData);
						$logger->write("Product  ID ".$product_id."Reward points data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id."Reward points data Duplicate !");
							
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Reward points data Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write(" Product ID ".$product_id." Reward points data Error! ".$e->getMessage()); 
			
				}
				}
				}
			}
			
				}				}
}catch(Exception $e){ 
				$logger->write("Reward Points data Error! ".$e->getMessage()); 
			
				}

			}	
		/* ----------------------------------< /Reward Points >--------------------------- */			
       /* ******************************************************************************** */
	   
			/* --------------------------------------< SEO >---------------------------- */	
			$excelSheetSeoData=array();
	
			 if (array_key_exists("SEO",$excelSheetData)) {
				 try {
				$excelSheetSeoData=$this->excelSheetProductData($excelSheetData['SEO']);		
			/* 	foreach($excelSheetData['SEO'] as $key=>$vlaue ) {
					$excelSheetSeoData[]=array_combine(array_values($excelSheetData['SEO'][1]),array_values($vlaue));
				} */
					$seTbl=array(
						'product_id' => 'product_id',
						//'store' => 'store',
						'keyword' => 'keyword'
					);
					$sevalidate= $this->CompareTable($seTbl,$excelSheetData['SEO'][1],$logger,'SEO');
				 
				//if(!empty($excelSheetSeoData && $sevalidate && $gen && $produc )) {
				if(!empty($excelSheetSeoData) && $sevalidate && $gen && $produc ) {
				foreach($excelSheetSeoData as $key=>$valueSeo)
				{
				$seoData=array();
				$seoLanguageId 			= $this->request->post['language'];
				if($key!=1){
				if(!in_array($valueSeo['product_id'],$failed_array)) {
					$product_id				= (int)$valueSeo['product_id'];
					$product_id_old			= (int)$valueSeo['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}
				if(in_array($product_id,$passed_array)) {
					if(isset($valueSeo['store'])){
					if($valueSeo['store']=='Default'){
					 $storeId=0;
					} else {
					$storeId=$this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($valueSeo['store']);	
				} } else {
					$storeId=0;
				}
				$seoData['product_seo_url'][(int)$storeId] = array(
				'product_id'			=>		$product_id,
				$seoLanguageId				=>		$valueSeo['keyword'] );

		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductSeoTab($seoData);
					$logger->write("Product ID ".$product_id." SEO Data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 

						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductSeoTab($seoData);
						$logger->write("Product  ID ".$product_id." SEO Data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id."SEO Data Duplicate Product Id !");
						
						}
				}
			} else {
				$logger->write("Product ID ".$product_id."SEO Data Product Id Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id."SEO Data Error! ".$e->getMessage()); 
			
				}
				}
				}
				}
				}
				}
				}catch(Exception $e){ 
				$logger->write("SEO data Error! ".$e->getMessage()); 
			
				}
				
			}	
		    /* ----------------------------------< /SEO >--------------------------- */
		       /* ************************************************************************* */
	   
			/* --------------------------------------< Design >---------------------------- */	
			$excelSheetDesignData=array();
	
			 if (array_key_exists("Design",$excelSheetData)) {
				 try {
				$excelSheetDesignData=$this->excelSheetProductData($excelSheetData['Design']);	
				/* foreach($excelSheetData['Design'] as $key=>$vlaue ) {
					$excelSheetDesignData[]=array_combine(array_values($excelSheetData['Design'][1]),array_values($vlaue));
				} */
				//if(!empty($excelSheetData['Design'] as $key=>$vlaue ) {
					$desiTbl=array(
							'product_id' => 'product_id',
							'store' => 'store',
							'layout' => 'layout'
					);
					$desivalidate= $this->CompareTable($desiTbl,$excelSheetData['Design'][1],$logger,'Design');
				 
				if(!empty($excelSheetDesignData) && $desivalidate && $gen && $produc ) {
				foreach($excelSheetDesignData as $key=>$valueDesign)
				{
				$designData=array();

				if($key!=1){
				if(!in_array($valueDesign['product_id'],$failed_array)) {
				$product_id				= (int)$valueDesign['product_id'];
					$product_id_old			= (int)$valueDesign['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}	
				if(in_array($product_id,$passed_array)) {
					if($valueDesign['store']=='Default' || $valueDesign['store']==''){
					$storeId=0;
					} else {
					$storeId= $this->model_extension_purpletree_multivendor_bulkproductupload->getStoreId($valueDesign['store']);	
					}
					if($valueDesign['layout']==''){
					$layoutId= 0;	
					} else {
					$layoutId= $this->model_extension_purpletree_multivendor_bulkproductupload->getLayoutId($valueDesign['layout']);	
					}

				
				$designData['product_layout'][$product_id]= array(
				$storeId	=>		$layoutId );

		try {
			if(is_numeric($product_id) && $product_id !='') {
			if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductDesignTab($designData);
					$logger->write("Product ID ".$product_id."  Design Data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 

						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductDesignTab($designData);
						$logger->write("Product  ID ".$product_id." Design Data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id." Design Data Duplicate Product Id !");
							
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Design Data Product Id Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Design Data Error! ".$e->getMessage()); 
				
				}
				}
				}
				}
				}
				}
}catch(Exception $e){ 
				$logger->write("Design data Error! ".$e->getMessage()); 
			
				}
			}	
			/*-------------------------------------< /Design >--------------------------- */
			/* ************************************************************************** */
			/* --------------------------------------< Seller >--------------------------*/	
			//$this->config->get('module_purpletree_multivendor_product_approval');
				if($this->customer->getId() && $gen && $produc ) {
			$excelSheetSellerData=array();
			 if (array_key_exists("General",$excelSheetData)) {
				 try {
				$excelSheetSellerData=$this->excelSheetProductData($excelSheetData['General']);		
/* 				foreach($excelSheetData['General'] as $key=>$vlaue ) {
					$excelSheetSellerData[]=array_combine(array_values($excelSheetData['General'][1]),array_values($vlaue));
				} */
				if(!empty($excelSheetSellerData)) {
				foreach($excelSheetSellerData as $key=>$valueseller)
				{
				$sellerIdData=array();
				if($key!=1){
				if(!in_array($valueseller['product_id'],$failed_array)) {
					$product_id				= (int)$valueseller['product_id'];
					$product_id_old			= (int)$valueseller['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}					
				if(in_array($product_id,$passed_array)) {
				$sellerIdData['seller_data'][]= array(
				'seller_id'		=>		$this->customer->getId(),
				'product_id'	=>		$product_id,
				'is_approved'	=>		($this->config->get('module_purpletree_multivendor_product_approval'))?'0':'1'
				);
			try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];
					$this->model_extension_purpletree_multivendor_bulkproductupload->assignDataToSeller($sellerIdData);
					$logger->write("Product ID ".$product_id." Seller Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 

						$this->model_extension_purpletree_multivendor_bulkproductupload->editAssignDataToSeller($sellerIdData);
						$logger->write("Product  ID ".$product_id." Seller Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id."  Seller Duplicate Product Id !");
						
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Seller Product Id Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Seller Error! ".$e->getMessage()); 
			
				}
				}
				}
				}
				}
				}

			
			}catch(Exception $e){ 
				$logger->write("Seller data Error! ".$e->getMessage()); 
			
				}
		} 
		}		
		/* ----------------------------------< /Seller >--------------------------- */
		/* **************************************************************************/
		/* --------------------------------------< Image >-------------------------- */	
			$excelSheetImageData=array();	
			 if (array_key_exists("Image",$excelSheetData)) {
				 try {
					$excelSheetImageData=$this->excelSheetProductData($excelSheetData['Image']);		
				/* foreach($excelSheetData['Image'] as $key=>$vlaue ) {
					$excelSheetImageData[]=array_combine(array_values($excelSheetData['Image'][1]),array_values($vlaue));
				}	 */			
				$this->load->model('tool/image');
				
								$imgTbl=array(
									'product_image_id' => 'product_image_id',
									'product_id' => 'product_id',
									'image' => 'image',
									'sort_order' => 'sort_order'
					);
					$imgvalidate= $this->CompareTable($imgTbl,$excelSheetData['Image'][1],$logger,'Image');
				 
				if(!empty($excelSheetImageData) && $imgvalidate && $gen && $produc ) {
				foreach($excelSheetImageData as $key=>$valueImage)
				{
				$imageData=array();
				if($key!=1){
				if(!in_array($valueImage['product_id'],$failed_array)) {
					$product_id				= (int)$valueImage['product_id'];
					$product_id_old			= (int)$valueImage['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}	
					
				if(in_array($product_id,$passed_array)) {	
				$img='';
				//$img=$this->checkRemoteFile(trim($valueImage['image']));

				//if($img){
				$sourcecode = $this->GetImageFromUrl(trim($valueImage['image']));
				//}
				
/* 				$source = HTTPS_SERVER. trim($valueImage['image']);
				$destination= DIR_IMAGE."catalog/".basename(trim($valueImage['image']));  */
				if($sourcecode !== FALSE) {
				//copy($source,$destination);
				$product_image_id			=		$valueImage['product_image_id'];
				
				$imageData['product_image'][]= array(
				'product_image_id'			=>		$valueImage['product_image_id'],
				'product_id'				=>		$product_id,
				'image'						=>		'catalog/Seller_'.$sseller_id.'/'.basename(trim($valueImage['image'])),
				'sort_order'				=>		$valueImage['sort_order'] 
				);
				$imageId=$this->model_extension_purpletree_multivendor_bulkproductupload->getProductImageId();
				$image_id=array();
				if(!empty($imageId)){
				foreach($imageId as $key => $imgId){
				$image_id[]=$imgId['product_image_id'];	
				}
				}
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id_old];
						if($product_image_id ==''){
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductImageTab($imageData);					
					$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$sseller_id."/".basename(trim($valueImage['image'])), 'w');
					@fwrite($savefile, $sourcecode);
					@fclose($savefile);	
					$logger->write("Product ID ".$product_id." Image data Uploaded Successfully"); 
					} elseif(in_array($product_image_id,$image_id)) {
					$logger->write("Product ID ".$product_id." Image data ".$product_image_id."  duplicate id "); 
					}else {
					$logger->write("Product ID ".$product_id." Image data ".$product_image_id." Invalid Id"); 
					}

				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) {
					if($product_image_id=='') {
					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductImageTab($imageData);					
					$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$sseller_id."/".basename(trim($valueImage['image'])), 'w');
					@fwrite($savefile, $sourcecode);
					@fclose($savefile);	
					$logger->write("Product ID ".$product_id." Image data Uploaded Successfully");	
					}elseif(in_array($product_image_id,$image_id)) {
					$this->model_extension_purpletree_multivendor_bulkproductupload->editProductImageTab($imageData);					
					$savefile = @fopen(DIR_IMAGE."catalog/Seller_".$sseller_id."/".basename(trim($valueImage['image'])), 'w');
					@fwrite($savefile, $sourcecode);
					@fclose($savefile);	
					$logger->write("Product  ID ".$product_id." Image data Updated Successfully");
					} else {
					$logger->write("Product  ID ".$product_id." Image data ".$product_image_id." Invalid Id");	
					} 					
						} else {
							//$logger->write("Product ID ".$product_id."Image data  Duplicate Id !");
						}
				}
			} else {
				$logger->write("Product ID ".$product_id." Image data Id Invalid");	
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Image data Error! ".$e->getMessage()); 
			
							}
				} else {
				$logger->write("Product Id".$product_id."Image data Not Found "); 		
				}
						}
					}
				}
			}
				 }
				}catch(Exception $e){ 
				$logger->write("Image data Error! ".$e->getMessage()); 
			
				}
		}	
			/*-------------------------------------< /Image >--------------------------- */
			   /* ************************************************************************* */
	   
			/* --------------------------------------< Product Option >---------------------------- */	
			$excelSheetProductOptionData=array();
			 if (array_key_exists("ProductOption",$excelSheetData)) {
				$excelSheetProductOptionData=$this->excelSheetProductData($excelSheetData['ProductOption']);	
			/* 	if(!empty($excelSheetData['ProductOption'])) {
				foreach($excelSheetData['ProductOption'] as $key=>$vlaue ) {
					$excelSheetProductOptionData[]=array_combine(array_values($excelSheetData['ProductOption'][1]),array_values($vlaue));
				}
			 } */
			 }
			 			$prooptTbl=array(
							'product_id' => 'product_id',
							'name' => 'name',
							'type' => 'type',
							'value' => 'value',
							'required' => 'required'
					);
					$prooptvalidate= $this->CompareTable($prooptTbl,$excelSheetData['ProductOption'][1],$logger,'ProductOption');
				 
				if(!empty($excelSheetProductOptionData) && $prooptvalidate && $gen && $produc ) {
				foreach($excelSheetProductOptionData as $key=>$valueOption)
				{
				$productOptionData=array();

				if($key!=1){
				if(!in_array($valueOption['product_id'],$failed_array)) {
					$product_id				= (int)$valueOption['product_id'];
					$product_id_old			= (int)$valueOption['product_id'];
				if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}	
				if(in_array($product_id,$passed_array)) {

				$opt_id=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionId($valueOption['type']);
				
					if($valueOption['required']=='Yes'){
						$required=1;
					}else {
						$required=0;
					}
				$productOptionData['product_option'][]= array(
				'product_id'	=>		$product_id,
				'option_id'		=>		$opt_id,
				'value'			=>		$valueOption['value'],
				'required'		=>		$required,
				);
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if(isset($opt_id)) {
				
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];

					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductOptionTab($productOptionData);
					$logger->write("Product ID ".$product_id." Option Data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 

						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductOptionTab($productOptionData);
						$logger->write("Product  ID ".$product_id." Option Data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id." Option Data Duplicate Product Id !");
						
						}
				}
			} else {
				
				$logger->write("product ID ".$product_id." , Option Data Invalid !");
				
			}
			} else {
				$logger->write("Product ID ".$product_id." Option Data Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id." Option Data Error! ".$e->getMessage()); 
			
				}
				}
				}
				}
			}
			 }
			/*-------------------------------------< / Product Option >--------------------------- */
			 /* ************************************************************************* */
	   
			/* ---------------------------------< Product Option value >---------------------------- */	
			$excelSheetProductOptionValueData=array();
			 if (array_key_exists("ProductOptionValue",$excelSheetData)) {
				$excelSheetProductOptionValueData=$this->excelSheetProductData($excelSheetData['ProductOptionValue']);	
		/* 		if(!empty($excelSheetData['ProductOptionValue'])){
				foreach($excelSheetData['ProductOptionValue'] as $key=>$vlaue ) {
					$excelSheetProductOptionValueData[]=array_combine(array_values($excelSheetData['ProductOptionValue'][1]),array_values($vlaue));
				}
			 } */
			 }
			 			 	$prooptvalueTbl=array(
										'product_id' => 'product_id',
										'option' => 'option',
										'option_value' => 'option_value',
										'quantity' => 'quantity',
										'subtract' => 'subtract',
										'price' => 'price',
										'price_prefix' => 'price_prefix',
										'points' => 'points',
										'points_prefix' => 'points_prefix',
										'weight' => 'weight',
										'weight_prefix' => 'weight_prefix'
					);
					$prooptvaluevalidate= $this->CompareTable($prooptvalueTbl,$excelSheetData['ProductOptionValue'][1],$logger,'ProductOptionValue');
				 
			 if(!empty($excelSheetProductOptionValueData) && $prooptvaluevalidate && $gen && $produc ) {
				foreach($excelSheetProductOptionValueData as $key=>$valueProductOption)
				{
				$productOptionValueData=array();

				if($key!=1){
					if(!in_array($valueProductOption['product_id'],$failed_array)) {
					$product_id				= (int)$valueProductOption['product_id'];
					$product_id_old			= (int)$valueProductOption['product_id'];
					if (array_key_exists($product_id_old,$new_products)) {
					$product_id = $new_products[$product_id];
					}	
				if(in_array($product_id,$passed_array)) {
				
				$opt_id=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionId($valueProductOption['option']);
				
				$opt_value_id=$this->model_extension_purpletree_multivendor_bulkproductupload->getOptionValueId($valueProductOption['option_value'],$opt_id);
								
				$product_option_id=$this->model_extension_purpletree_multivendor_bulkproductupload->getProductOptionId($product_id,$opt_id);
				if($valueProductOption['subtract']=='Yes'){
				$subtract=1;	
				} else {
				$subtract=0;	
				}
				$productOptionValueData['product_option_values'][]= array(
				'product_id'		=>		$product_id,
				'option_id'			=>		$opt_id,
				'product_option_id'	=>		$product_option_id,
				'option_value_id'	=>		$opt_value_id,
				'quantity'			=>		$valueProductOption['quantity'],
				'subtract'			=>		$subtract,
				'price'				=>		$valueProductOption['price'],
				'price_prefix'		=>		$valueProductOption['price_prefix'],
				'points'			=>		$valueProductOption['points'],
				'points_prefix'		=>		$valueProductOption['points_prefix'],
				'weight'			=>		$valueProductOption['weight'],
				'weight_prefix'		=>		$valueProductOption['weight_prefix']
				);
		try {
			if(is_numeric($product_id) && $product_id !='') {
				if(isset($opt_id)) {
			if(($opt_id!='' && $opt_value_id!='') && $product_option_id!=''){
				
				if (array_key_exists($product_id_old,$new_products)) {
						$product_id = $new_products[$product_id_old];

					$this->model_extension_purpletree_multivendor_bulkproductupload->addProductOptionValueTab($productOptionValueData);
					$logger->write("Product ID ".$product_id." product option value Data Uploaded Successfully"); 
				} else {
					if(isset($this->request->post['dataoverwrite'])==true && in_array($product_id,$update_products)) { 
				
						$this->model_extension_purpletree_multivendor_bulkproductupload->editProductOptionvalueTab($productOptionValueData);
						$logger->write("Product  ID ".$product_id." product option value Data Updated Successfully"); 
						} else {
							$logger->write("Product ID ".$product_id." product option value Data Duplicate Id !");
							
						}
						}
	} else {
		$logger->write("Product ID ".$product_id." product option value Data is invalid !");
				}
			} else {
				
				$logger->write("option ID ".$product_id." , product option value Data Invalid Option Value !");
				
			}
			} else {
				$logger->write("Product ID ".$product_id." product option value Data Invalid");
			
			}
		}catch(Exception $e){ 
				$logger->write("Product ID ".$product_id."  product option value Data Error! ".$e->getMessage()); 
			
				}
				}
				}
				}
				}
			 }
			/*--------------------< / Product Option Value >--------------------------- */
			
			$this->session->data['success'] = $this->language->get('text_bulkuploadsuccess');
			$url='';
			$this->response->redirect($this->url->link('extension/account/purpletree_multivendor/bulkproductupload', '' . $url, true));
		} else {
			$this->session->data['error_warning'] = $this->language->get('text_invalidfile');
		}
			} else {
			$this->session->data['error_warning'] = $this->language->get('text_nofile');
		}
			
		}
		$this->index();	
	}	
	/* -------------------------------------Image download--------------------------------- */
	
		 public function GetImageFromUrl($link)
		{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 0);
				curl_setopt($ch, CURLOPT_URL,$link);
				//curl_setopt($ch, CURLOPT_NOBODY, 1);
				//curl_setopt($ch, CURLOPT_FAILONERROR, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result=curl_exec($ch);
				curl_close($ch);
				return $result;
		}
	public function CompareTable($tableFirst,$tableSecond,$logger,$tab){				
				$array_diff= array_diff($tableFirst,$tableSecond);
					if(empty($array_diff)){
						return true;	
					} else {
						if(!empty($array_diff)){
						foreach($array_diff as $prod_key=> $prod_value){	
								$logger->write($tab." Tab - ".$prod_value." Column is Not match");	
						}
						}
						return false;
						
					}
				}
		
/* 		
		public function checkRemoteFile($url)
				{
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$url);
						// don't download content
						curl_setopt($ch, CURLOPT_NOBODY, 1);
						curl_setopt($ch, CURLOPT_FAILONERROR, 1);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						$result = curl_exec($ch);
						curl_close($ch);
						if($result !== FALSE)
						{
							return true;
						}
						else
						{
							return false;
						}
				}  */
	/* -----------------------------------------< Data Tab Function >---------------------- */
	protected function excelSheetProductData($tab=array())
	{
		
		$field_data=array();
		$data=array();
		if(!empty($tab)){
		foreach($tab as $key=>$result){
			foreach($result as $field=>$value){
			 $field_data[$tab[1][$field]]=$value;
			}
			$data[$key]=$field_data;
		}
		
		return $data;
	}
	}
	/* -----------------------------------------< /Data Tab Function >---------------------- */
			protected function getData() {
		$url = '';
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', '', true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/purpletree_multivendor/bulkproductupload', '' . $url, true)
		);	
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_list'] = $this->language->get('text_list');		
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');        
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_upload'] = $this->language->get('button_upload');
		$data['text_upload_info'] = $this->language->get('text_upload_info');
		$data['text_import'] = $this->language->get('text_import');
		$data['text_select_language'] = $this->language->get('text_select_language');
		$data['text_select_seller_id'] = $this->language->get('text_select_seller_id');
		//$data['text_bulkuploadsuccess'] = $this->language->get('text_bulkuploadsuccess');
		$data['text_aproved_product'] = $this->language->get('text_aproved_product');
		 
		//$data['user_token'] = $this->session->data['user_token'];
		
		$data['text_bulk_product_upload'] = $this->language->get('text_bulk_product_upload');	
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
		
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];

		unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}
        $data['max_time'] = ini_get("max_execution_time")/100;
		
		$data['memory_limit'] = ini_get("memory_limit");
		
		$data['action'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload/upload', '', true);	
		   $data['export'] = $this->url->link('extension/account/purpletree_multivendor/bulkproductupload/export', '', true);
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
	    $data['column_right'] = $this->load->controller('common/column_right');	
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['lang']=$this->model_extension_purpletree_multivendor_bulkproductupload->getAllLanguage();
			$data['seller_template'] = HTTPS_SERVER."image/catalog/seller_purpletree_bulk_product_upload.xlsx";
			$data['next_product_id'] = 1;
		$data['seller_id']=$this->model_extension_purpletree_multivendor_bulkproductupload->getAllSellerId();
		$lastproductid = $this->model_extension_purpletree_multivendor_bulkproductupload->getlastproductId();
		if(isset($lastproductid)) {
			$data['next_product_id'] = $lastproductid+1;
		}
		$this->response->setOutput($this->load->view('account/purpletree_multivendor/bulkproduct_upload', $data));		
	}
}
if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
		if(!empty($input)){
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
	}
        return $array;
    }
}
?>