<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CRM_Controller {

    
   public function __construct()
    {
        parent::__construct();
        $user = $this->autorize();
		$this->user =  $user;
    } 

    protected $user;     
     
	public function index()
	{
        $this->mainpage();
	}


	// Главная страница
	public function mainpage($part = 1){ 
		 // Попытка авторизоватся
		 $data['user'] = $this->user;

		 // Верхнее меню
		 $data['upmenu'] = $this->quotes->get_upmenu();
		 // Получаем массив данных с названим раздлов
		 $data['sparts'] = $this->quotes->get_folders_names();

		 // Список главных разделов
		 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive", "4"=>"Dashboard");

		 // Количество вниутри разделов
		 $data['countInParts'] = $this->quotes->count_sum_inside_part();
		 //print_r($data['countInParts']);

		 $GPart = 4; //$this->quotes->get_parentPart_by_folder($part);
		 $GPart = $data['sPartsNames'][$GPart];

		 $data['menu_active'] = $GPart;
		 $data['menu_active2'] = $part;

    	 $data['nowDay'] = $this->getNowDate();

		  // Колчество на странице
		  if($this->session->userdata('limit')){
	     		$data['LIMIT'] = $LIMIT = $this->session->userdata('limit');
		  }else{
		 		$data['LIMIT'] = $LIMIT = 10;
		  }
		  // [END] Колчество на странице

		  // Сортировка
		  if($this->session->userdata('sort')){
	     		$data['iSORT'] = $iSORT = $this->session->userdata('sort');
	     		$data['S'] = $S = $this->session->userdata($iSORT);
		  }else{
		 		$data['iSORT'] = $iSORT = "q.moveDate";
	     		$data['S'] = $S = "DESC";
		  }

		  
		  if($this->session->userdata('sort')){
		  	if($data['iSORT']!="shipper"){
		    	$data['SORT'] = $SORT = "ORDER BY q.".$iSORT." ".$S;
		  	}else{
		  		$data['SORT'] = $SORT = "ORDER BY c.Email ".$S;
		  	}
		  }else{
		    $data['SORT'] = $SORT = "ORDER BY q.moveDate DESC";
		  }
		  // [END] Сортировка


		 // Количество общее
		  $data['countQ'] = $this->quotes->get_quotes_part_count($part); 

		  // Получаем список для таблицы на вывод
		  $data['query'] =  $this->quotes->get_quotes_part_page($part,$SORT, $LIMIT, 0);

		  // Получаем все непрочитаные алерты
		  
		  $data['ishowAlerts'] = 1;
		  //print_r($user);



		  $this->load->view('admin/Header' ,$data);

		  $data['cenceled'] = $data['quoted'] = $data['dispached'] = $data['ordered'] = 0;

		  $data['countQuotes'] = $this->quotes->get_count_quotes(0);

		  $data['quoted'] = $this->quotes->get_statistic_info(75, 0);
		  $data['ordered'] = $this->quotes->get_statistic_info(83, 0);
		  $data['dispached'] = $this->quotes->get_statistic_info(87, 0);
		  $data['cenceled'] = $this->quotes->get_statistic_info(91, 0);


		  $GsendStat = $this->quotes->getG_sent_statistic();	
		  $data['emailsSent'] = $GsendStat->countSent;
		  $data['emailsOpened'] = count($this->emails->get_all_opened_stat());
		  $data['emailsRecived'] = count($this->emails->get_all_recived_stat());	
		  $data['emailsWaiting'] = $GsendStat->countWaiting;	
	      if($data['emailsWaiting'] < 0) $data['emailsWaiting'] = 0;

		  $data['countNotices'] = $this->quotes->get_count_notices(0);	

		  $data['formRequestCount'] = $this->emails->get_forms_count(0);
		  $data['formInitCount'] = $this->emails->get_forms_count(1);
		  $data['formSheduleCount'] = $this->emails->get_forms_count(2);

		  $data['globalsum'] = $this->quotes->get_global_orders_sum();
		  $data['chargered'] = $this->quotes->get_chargered_sum();

		  //exit();
		  $this->load->view('admin/upmenu' ,$data);
		  $this->load->view('admin/containerBegin' ,$data);
		  $this->load->view('admin/parts/dashboard' ,$data);
		 /* $this->load->view('admin/tables/'.strtolower($GPart).'/header', $data);
		  $OddClass = "second";
		  foreach($data['query']->result() as $item){

    		if($OddClass == "second"){ $OddClass = ""; }else{ $OddClass = "second"; }
    		$data['data'] = $item;
    		
    		$data['OddClass'] = $OddClass;
    		$data['countNotices'] = $this->quotes->get_count_notices($item->id);
    			
		  	$this->load->view('admin/tables/'.strtolower($GPart).'/item', $data);
		  }
		  
		  $this->load->view('admin/tables/'.strtolower($GPart).'/footer', $data);
		  */
		  $this->load->view('admin/containerEnd' ,$data);
		  $this->load->view('admin/Footer', $data);
	}

	// Блок нотайсов для квот
	public function get_quote_notices(){
		$qid = $this->input->post('qid');
		$data['notices'] = $this->quotes->get_notices($qid);
		$data['qid'] = $qid;
		$this->load->view('admin/parts/notices', $data);
	}

	// добавление нотайса
	public function add_notice(){
		// Попытка авторизоватся
		$data['user'] = $this->user;

		$qid = $this->input->post('qid');
		$text = $this->input->post('Ntext');
		$addArray = array(
			'qid'=>$qid,
			'text'=>$text,
			'uid'=>$data['user']->id,
			'atDate'=>date('Y-m-d H:i:s')); 
		$this->quotes->add_notice($addArray);

		echo '<div style="border-bottom:solid 1px #dddddd">
			<div style="font-size:12px;">
				'.date('m/d/y h:i A').'
			</div>
			<div style="font-size:12px;">
				'.$data['user']->email.'
			</div>
			<div style="font-size:13px; margin-top:5px;">
				'.nl2br($text).'
			</div>
		</div>';
	}

	// Добавление папки к разделу
	public function add_second_menu(){
		$spart = $this->input->post('spart');
		$sname = $this->input->post('sname');
		$addArray = array(
			'sname'=>$sname,
			'spart'=>$spart);
		$newid = $this->quotes->add_second_menu($addArray);
		echo '<div ondblclick="delete_folder(\''.base_url().'\', '.$newid.'); $(this).fadeOut(500);" onclick=" var ithis = $(this); setTimeout(function(){showPartQuotes(\''.base_url().'\','.$newid.', ithis)},100);"  class="upmenuButton " class="upmenuButton ">'.$sname.' (0)</div>';

	}

	// Удаление папки из раздела
	public function del_second_menu(){
		$did = $this->input->post('did');
		$this->quotes->delete_second_menu($did);
	}

	// Колчество на странице
	public function set_limit($limit = 100){
      	$this->session->set_userdata(array('limit'=>$limit));
      	//redirect(base_url(), 'refresh');
	}

	// Сортировка
	public function set_sort($s, $sort){
		// Сортировка
	    if($s == 0){
	      $this->session->set_userdata(array('sort'=>$sort));	
	      $this->session->set_userdata(array($sort=>"ASC"));	
	    }else{
	      $this->session->set_userdata(array('sort'=>$sort));	
	      $this->session->set_userdata(array($sort=>"DESC"));
	      
	    }
		//redirect(base_url(), 'refresh');
	}



  // Подгрузка вконце таблицы
  public function get_more_quots(){
  		// Попытка авторизоватся
		$data['user'] = $this->user;

	    $getMore = $this->input->post('getMore');
	  	$spart = $this->input->post('spart');

	  	// Верхнее меню
		$data['upmenu'] = $this->quotes->get_upmenu();
	  	
	  	// Получаем массив данных с названим раздлов
		 $data['sparts'] = $this->quotes->get_folders_names();
		
		 // Список главных разделов
		 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");		 

	  	if($this->session->userdata('limit')){
	     	$LIMIT = $this->session->userdata('limit');
	 	}else{
	 		$LIMIT = 10;
	 	}


	 	 $type = $data['sparts'][$spart]->spart;
	 	 $type = strtolower($type);

	 	 // Сортировка
		  if($this->session->userdata('sort')){
	     		$data['iSORT'] = $iSORT = $this->session->userdata('sort');
	     		$data['S'] = $S = $this->session->userdata($iSORT);
		  }else{
		 		$data['iSORT'] = $iSORT = "q.id";
	     		$data['S'] = $S = "DESC";
		  }

		  
		  if($this->session->userdata('sort')){
		    if($data['iSORT']!="shipper"){
		    	$data['SORT'] = $SORT = "ORDER BY q.".$iSORT." ".$S;
		  	}else{
		  		$data['SORT'] = $SORT = "ORDER BY c.Email ".$S;
		  	}
		  }else{
		    $data['SORT'] = $SORT = "ORDER BY q.moveDate DESC";
		  }
		  // [END] Сортировка

	    $data['ishowHeader'] = 0; 


		 // Количество общее
		  $data['countQ'] = $this->quotes->get_quotes_part_count($spart); 

		  // Получаем список для таблицы на вывод
		  $data['query'] =  $this->quotes->get_quotes_part_page($spart, $SORT, $LIMIT, ($getMore*$LIMIT));



		  $OddClass = "second";
		  foreach($data['query']->result() as $item){
    		if($OddClass == "second"){ $OddClass = ""; }else{ $OddClass = "second"; }
    		$data['data'] = $item;
    		$data['countNotices'] = $this->quotes->get_count_notices($item->id);
    		$data['OddClass'] = $OddClass;
		  	$this->load->view('admin/tables/'.$type.'/item', $data);
		  }
	      
	  
	  // [END] Подгрузка вконце таблицы
  }

  // Подгрузка вконце таблицы
  public function get_quots_part($partid = 0){
  		// Попытка авторизоватся
		 $data['user'] = $this->user;

  		// Верхнее меню
		$data['upmenu'] = $this->quotes->get_upmenu();

		// Получаем массив данных с названим раздлов
		$data['sparts'] = $this->quotes->get_folders_names();


		 
		  // Список главных разделов
		 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");
		
		if($partid == 0){
  			$partid = $this->input->post('partid');
  		}
	  	if($this->session->userdata('limit')){
	     	$data['LIMIT'] = $LIMIT = $this->session->userdata('limit');
	 	}else{
	 		$data['LIMIT'] = $LIMIT = 10;
	 	}

	 	//print_r($data['sparts']);	
	 	 $type = $data['sparts'][$partid]->spart;
	 	 $type = strtolower($type);

	 	 // Сортировка
		  if($this->session->userdata('sort')){
	     		$data['iSORT'] = $iSORT = $this->session->userdata('sort');
	     		$data['S'] = $S = $this->session->userdata($iSORT);
		  }else{
		 		$data['iSORT'] = $iSORT = "q.moveDate";
	     		$data['S'] = $S = "DESC";
		  }

		  if($data['iSORT'] == "moveDate") $data['S'] = $S =  "DESC";
		  if($this->session->userdata('sort')){
		    if($data['iSORT']!="shipper"){
		    	$data['SORT'] = $SORT = "ORDER BY q.".$iSORT." ".$S;
		  	}else{
		  		$data['SORT'] = $SORT = "ORDER BY c.Email ".$S;
		  	}
		  }else{
		    $data['SORT'] = $SORT = "ORDER BY q.moveDate DESC";
		  }


		  // [END] Сортировка

	 	 // Количество общее
		  $data['countQ'] = $this->quotes->get_quotes_part_count($partid);
	      $data['query'] = $this->quotes->get_quotes_part_page($partid, $SORT, $LIMIT, $PAGE = 0);
	     
	
	 	  $this->load->view('admin/tables/'.$type.'/header', $data);

	 	$OddClass = "second";	
	 	foreach($data['query']->result() as $item){
    		if($OddClass == "second"){ $OddClass = ""; }else{ $OddClass = "second"; }
    		$data['data'] = $item;
    		$data['OddClass'] = $OddClass;
    		$data['countNotices'] = $this->quotes->get_count_notices($item->id);
		  	$this->load->view('admin/tables/'.$type.'/item', $data);
		  }
	 	
	 	  $this->load->view('admin/tables/'.$type.'/footer', $data);

	      
	  
	  // [END] Подгрузка вконце таблицы
  }

   // Подгрузка вконце таблицы
  public function get_quots_search(){
  		// Попытка авторизоватся
		 $data['user'] = $this->user;

		 $value = $this->input->post('value');
		 $name = $this->input->post('name');

  		// Верхнее меню
		$data['upmenu'] = $this->quotes->get_upmenu();

		// Получаем массив данных с названим раздлов
		$data['sparts'] = $this->quotes->get_folders_names();


		 
		  // Список главных разделов
		 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");
		
		
	  	
	 	$data['LIMIT'] = $LIMIT = 1000;
	 	

	 	 //$type = $data['sparts'][$partid]->spart;
	 	 //$type = strtolower($type);

	 	 // Сортировка
		  if($this->session->userdata('sort')){
	     		$data['iSORT'] = $iSORT = $this->session->userdata('sort');
	     		$data['S'] = $S = $this->session->userdata($iSORT);
		  }else{
		 		$data['iSORT'] = $iSORT = "q.id";
	     		$data['S'] = $S = "DESC";
		  }

		  
		  if($this->session->userdata('sort')){
		    if($data['iSORT']!="shipper"){
		    	$data['SORT'] = $SORT = "ORDER BY q.".$iSORT." ".$S;
		  	}else{
		  		$data['SORT'] = $SORT = "ORDER BY c.FirstName ".$S;
		  	}
		  }else{
		    $data['SORT'] = $SORT = "ORDER BY q.moveDate DESC";
		  }
		  // [END] Сортировка

	 	 // Количество общее
		  $data['countQ'] = 0;
		  if($name == "all"){
		  	//echo $value;
		  		$data['query'] = $this->quotes->get_quotes_search_page2($name, $value, $SORT, $LIMIT, $PAGE = 0);
		  }else{
		  		$data['query'] = $this->quotes->get_quotes_search_page($name, $value, $SORT, $LIMIT, $PAGE = 0);	
		  }
	      
	     
	
	 	  $this->load->view('admin/tables/quotes/header', $data);
	 	$OddClass = "second";	
	 	 foreach($data['query']->result() as $item){
    		if($OddClass == "second"){ $OddClass = ""; }else{ $OddClass = "second"; }
    		$data['data'] = $item;
    		$data['OddClass'] = $OddClass;
    		$data['countNotices'] = $this->quotes->get_count_notices($item->id);
		  	$this->load->view('admin/tables/quotes/item', $data);
		  }
	 	
	 	  $this->load->view('admin/tables/quotes/footer', $data);

	      
	  
	  // [END] Подгрузка вконце таблицы
  }


  // Подгрузка меню верхнего
  public function get_upmenu(){
  			// Попытка авторизоватся
		$data['user'] = $this->user;
		 
  		$selected_menu = $this->input->post('smenu');
  		$selected_menu2 = $this->input->post('smenu2');

  		// Количество общее
		$data['countQ'] = $this->quotes->get_quotes_part_count(0);
		
		// Пересчитываем цыфру для меню  
	  	$this->quotes->recount_inside_parts($selected_menu2);

	  	// Список главных разделов
		 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");

	  	// Верхнее меню
		$data['upmenu'] = $this->quotes->get_upmenu();

		// Количество вниутри разделов
		$data['countInParts'] = $this->quotes->count_sum_inside_part();
		//print_r($data['countInParts']);

		$data['menu_active'] = $selected_menu;
		$data['menu_active2'] = $selected_menu2;

    	$data['nowDay'] = $this->getNowDate();	     
 		$this->load->view('admin/upmenu', $data);
	 
	      
	  
	  // [END] Подгрузка вконце таблицы
  }

  // Блок редактироване квоты
  public function get_fullinfo(){
  	$qid = $this->input->post('qid');
  	$data['quote'] = $this->quotes->get_quote_by_id_full($qid);
  	$this->load->view('admin/parts/profile' , $data);

  }

  // Блок bookit
  public function get_bookit(){
  	$qid = $this->input->post('qid');
  	$data['quote'] = $this->quotes->get_quote_order_by_id($qid);
  	$this->load->view('admin/parts/bookit' , $data);

  }

  // Блок dispatch
  public function get_dispatch(){
  	$qid = $this->input->post('qid');
  	$data['quote'] = $this->quotes->get_quote_order_by_id($qid);
  	if((!isset($data['quote']->pAddrCity))||($data['quote']->pAddrCity == "")) $data['quote']->pAddrCity = $data['quote']->distFromCity;
  	if((!isset($data['quote']->dAddrCity))||($data['quote']->dAddrCity == "")) $data['quote']->dAddrCity = $data['quote']->distToCity;
  	if((!isset($data['quote']->pAddrState))||($data['quote']->pAddrState == "")) $data['quote']->pAddrState = $data['quote']->distFromState;
  	if((!isset($data['quote']->dAddrState))||($data['quote']->dAddrState == "")) $data['quote']->dAddrState = $data['quote']->distToState;
  	if((!isset($data['quote']->pAddrZip))||($data['quote']->pAddrZip == "0")) $data['quote']->pAddrZip = $data['quote']->distFromZip;
  	if((!isset($data['quote']->dAddrZip))||($data['quote']->dAddrZip == "0")) $data['quote']->dAddrZip = $data['quote']->distToZip;
  	$data['quote']->driver = $this->drivers->get_driver_by_id($data['quote']->driverid);
  	$data['demails'] = $this->emails->get_drivers_shablons();
  	$this->load->view('admin/parts/dispatch' , $data);

  }

	
	// Пересчитываем количество квот внутри папок
  function recountInsidePartsAll($partId){
  		echo $this->quotes->recount_inside_parts($partId);
  }	  

  // Пересчитываем количество квот внутри папок
  public function recountInsideParts($partId){
  		echo $this->quotes->recount_inside_parts($partId);
  }
  // Перенос заявки
  public function set_quote_part(){
  	$spart = $this->input->post('spart');
  	$qid = $this->input->post('qid');
  	$qids = explode('`',$qid);
  	if(count($qids) > 1){
  		foreach($qids as $qid){
  			$this->domove($qid, $spart);	
  		}	
  	}else{	
  		$this->domove($qid, $spart);
  	}	
  }


  // Расчет дистанции
  public function get_dist($qid = 0){
  	$apiurl = $this->input->post('disturl');
  	//echo 'https://maps.googleapis.com/maps/api/distancematrix/'.$apiurl;

  	$dist = json_decode(
		file_get_contents(
			"http://calc-api.ru/app:geo-api/null".str_replace(' ','%20',$apiurl)
		),
		true
	);
  	//$json = $this->getSslPage('https://maps.googleapis.com/maps/api/distancematrix/'.str_replace(' ','%20',$apiurl));
  	//$result =  json_decode ($json);
  	//print_r($result);
  	//$dist = (round(($result->rows[0]->elements[0]->distance->value/1000) * 0.62137119));
  	

  	echo round($dist['distanse']* 0.62137119)." mi";
  	$dist = round($dist['distanse']* 0.62137119);
  	if($qid != 0){
  		$q = $this->quotes->get_quote_by_id($qid);
  		echo "~".$q->price_per_mile;
  	}

  	$this->quotes->update_quote($qid, 'distance', $dist);

  	//print_r($result);
  }

 // полученние блока квоты
  function show_fullinfo_quote($showMap = 0){
  	$data['user'] = $user = $this->user;

  	// Верхнее меню
	$data['upmenu'] = $this->quotes->get_upmenu();
  	
  	// Получаем массив данных с названим раздлов
	 $data['sparts'] = $this->quotes->get_folders_names();
	
	 // Список главных разделов
	 $data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");



  	$qid = $this->input->post('qid');

  	$data['quote'] = $this->quotes->get_quote_order_by_id($qid);

  	if($data['quote']->deposit == 0){
  		$this->quotes->update_quote($qid, 'deposit', $user->uDeposit);	
  		$data['quote']->deposit = $user->uDeposit;
  	}
  	


  	$GPart = $this->quotes->get_parentPart_by_folder($data['quote']->spart);
    $data['Gpart'] = $GPart = $data['sPartsNames'][$GPart];

    $data['partName'] = $this->quotes->get_folder_name_by_id($data['quote']->spart); 

  	$data['showmap'] = $showMap;
  	if($showMap == 0){
	  	$apiurl = 'v1?q='.trim($data['quote']->carYear).' '.trim($data['quote']->carModel).' EXTERIOR&num=1&imgSize=large&searchType=image&cx=001831839543928330304:ki3uttjkkma&key=AIzaSyC1PragiWZvD_sx7EMdHOA7GUz-lXHSgb0';
	    $json = $this->getSslPage('https://www.googleapis.com/customsearch/'.str_replace(' ','%20',$apiurl));
	  	$result =  json_decode ($json);
	}
	//print_r($result);
  	if(isset($result->items[0]->link)){
  		$data['carpic'] =  $result->items[0]->link;
	}else{
		$data['carpic'] = "-";
	}

	

	if($data['Gpart'] == 'Leads'){
  		$this->load->view('admin/parts/leadblock', $data);
	}else if($data['Gpart'] == 'Quotes'){
		$this->load->view('admin/parts/quoteblock', $data);
	}else if($data['Gpart'] == 'Orders'){
		$this->load->view('admin/parts/orderblock', $data);
	}else if($data['Gpart'] == 'Archive'){
		$this->load->view('admin/parts/archiveblock', $data);
	} 
  }

  // Перенаправление лида в квоту
  function convertToQuote(){
  	$qid = $this->input->post('qid');
  	$quote = $this->quotes->get_quote_by_id($qid);
  	if($quote->price != 0){
  		$this->domove($qid, '75');
  		echo "+";
  	}else{
  		echo "-";
  	}

  }

  
  // Предпросмотр машины
  public function get_carpic(){
  	$apiurl = $this->input->post('req');
  	//echo 'https://maps.googleapis.com/maps/api/distancematrix/'.$apiurl;
  	$json = $this->getSslPage('https://www.googleapis.com/customsearch/'.str_replace(' ','%20',$apiurl));
  	//$json = file_get_contents('https://www.googleapis.com/customsearch/'.str_replace(' ','%20',$apiurl));
  	//print_r($result);
  	$result =  json_decode ($json);
  	//print_r($result);
  	echo "<div style='font-size:14px; text-align:center; color:#ffffff; padding:10px;'>";
  	echo "<img src='".$result->items[0]->link."'/></div>";
  	
  }

  // Добавление цены на дистанцию
  public function add_dest_price($qid, $val){
  	//echo "a";
  	//exit();
  	$quote = $this->quotes->get_quote_by_id($qid);
  	$vals = explode('~', urldecode ($val));
  	foreach($vals as $item){
  		$values = explode('`', $item);
  		//print_r($values);
  		$price_per_mile = $values[0]; 
  		$driverName = $values[1]; 
  		$phone = $values[2]; 
  		$fax = $values[3]; 
  		$coment = $values[4]; 
  		$firstDate = $values[5]; 
  		$secondDate = $values[6]; 
  		//$coment2 = $values[7]; 

  		$this->db->like('name', trim($driverName));
  		$d = $this->db->get('drivers');
  		//print_r($d->row());
  		if($d->num_rows() > 0){
  			$addArray = array(
			'from_state'=>trim($quote->distFromState),
			'to_state'=>trim($quote->distToState),
			'driver_id'=>$d->row()->id,
			'atDate'=>date('Y-m-d H:i:s'),
			'price_per_mile'=>$price_per_mile,
			'fromDate'=>trim($firstDate),
			'toDate'=>date('Y-m-d H:i:s' ,strtotime(trim($secondDate))),
			'coment'=>trim($driverName)."<br/>".trim($phone)."<br/>".trim($fax)."<br/>".trim($coment)."<br/>".trim(trim($firstDate))."<br/>".trim(trim($secondDate)).$coment2
			);
			$this->db->insert('drivers_prices', $addArray);
			//echo "+".$d->row()->id."<br/>";
  		}else{

  			$addArrayD = array(
  				'dtype'=>"-",
  				'name'=>trim($driverName),
  				'phone'=>trim($phone),
  				'fax'=>trim($fax),
  				'coment'=>$coment2);
  			$this->db->insert('drivers', $addArrayD);
  			$did = $this->db->insert_id();
  			echo "+".$did."<br/>";
  			$addArray = array(
				'from_state'=>trim($quote->distFromState),
				'to_state'=>trim($quote->distToState),
				'driver_id'=>$did,
				'atDate'=>date('Y-m-d H:i:s'),
				'price_per_mile'=>$price_per_mile,
				'fromDate'=>trim($firstDate),
				'toDate'=>date('Y-m-d H:i:s' ,strtotime(trim($secondDate))),
				'coment'=>trim($driverName)."<br/>".trim($phone)."<br/>".trim($fax)."<br/>".trim(trim($fax))."<br/>".trim(trim($firstDate))."<br/>".trim(trim($secondDate)).$coment2
			);
			$this->db->insert('drivers_prices', $addArray);
  			//echo "-";
  		}


  		
  	} 
  	//echo urldecode ($val);
  	/*echo $val = str_replace('_', '.', $val);

  	$price_per_mile = $val;

	$this->quotes->update_quote($qid, 'price_per_mile', $price_per_mile);

	$quote = $this->quotes->get_quote_by_id($qid);
	//print_r($quote);
	$dist = $quote->distance;
	if($quote->distance == 0){
		$json = $this->getSslPage('https://maps.googleapis.com/maps/api/distancematrix/json?origins='.str_replace(' ','%20',trim($quote->distFromCity)).'&destinations='.str_replace(' ','%20',trim($quote->distToCity)).'&key=AIzaSyDvRSeKIC0S17NjclJBgZFEF6iIRGKzo7U');
  	//echo $json;
  		$result =  json_decode ($json);
  		//print_r($result);
  		$dist = round(round(($result->rows[0]->elements[0]->distance->value/1000) * 0.62137119));
		$this->quotes->update_quote($quote->id, 'distance', $dist);
	}
	//echo $price_per_mile;
	echo $tarif = (($price_per_mile*$dist));
	$this->quotes->update_quote($quote->id, 'price', $tarif);

	$addArray = array(
		'from_state'=>trim($quote->distFromState),
		'to_state'=>trim($quote->distToState),
		'driver_id'=>0,
		'atDate'=>date('Y-m-d H:i:s'),
		'price_per_mile'=>$price_per_mile
		);
	$this->db->insert('drivers_prices', $addArray);

  	//if($val > 0){
  	//	$this->quotes->update_quote($quote->id, 'price_per_mile', $val);
  	//}
  	echo "<script type='text/javascript'>window.close();</script>";
  	*/
  	echo "<script type='text/javascript'>window.close();</script>";
  }	

  public function get_email_change(){
  	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	$data['notSave'] = 0;
	$data['user'] = $user = $this->user;

	$data['hash'] = time();
  	$data['eid']= $eid = $this->input->post('eid');
  	if($eid != 0){
  		$data['data'] = $this->emails->get_email_by_id($eid);	
  	}else{
  		if((!isset($user->Fname))||($user->Fname == "")) $user->Fname = "I HAVE NO NAME IN MY SETINGS";
  		$addArray = array(
			'name'=>"Onced send (not save as template)",
			'subject'=>"",
			'text'=>"",
			'etype'=>1,
			//'AutomatedTo'=>$AutomatedTo,
			'OnlyInworkTime'=>0,
		//	'cc'=>$Ecc,
			'bcc'=>"",
			'efrom'=>$user->email,
			'enfrom'=>$user->Fname,
			'replyto'=>$user->email

	    );
  		$this->db->insert('emails', $addArray);
  		$eid = $this->db->insert_id();
  		$data['data'] = $this->emails->get_email_by_id($eid);
  		$data['notSave'] = 1;
  	}
  	$this->load->view('admin/perscab/emailEdit', $data);	
    
   }

  // Добавить email в писок для рассылки у папка
  public function add_emails_to_list(){
	  	$eid = $this->input->post('eid');
	  	$fid = $this->input->post('fid');
	  	$elist = $this->emails->add_emails_to_list($eid, $fid);
	  	$id = $elist->id;
	  	$email = $this->emails->get_email_by_id($elist->eid);
	  	echo '<table style="width:100%;"><tr class="contentRow" id="erow<?=$data->id?>">
             
              <td  style="font-size:12px; padding-left:10px; color:#000000 !important;">'.$email->name.'</td>
              <td  style="font-size:15px; text-align:center; width:100px;"><input type="text" style="border:none; text-align:right; width:70px; background-color:inherit; font-size:inherit; color:#000000 !important; padding:0px; color:inherit;" onchange="changeEmailValue(\'sendAfterPrev\', $(this).val(), '.$id.');" value="0" /> <span style="font-size:13px; color:#000000 !important; font-style:italic; margin-left:-2px;">sec</span></td>
              <td  style="font-size:13px; text-align:center; cursor:pointer; color:#000000 !important;" onclick="delete_email_from_list(\''.base_url().'\', '.$id.');">x</td>
            </tr></table>';
  }   

  // Личный кабинет пользователя
  public function get_perscab_page(){
  		// Попытка авторизоватся
		 $data['user'] = $this->user;
  		// Количество общее

  		 $data['queryA'] = $this->emails->get_all_emails_full();
  		 $data['queryD'] = $this->drivers->get_all_emails_full();
  		 $data['querySMS'] = $this->emails->get_all_sms_full();
  		
		$data['folders'] = $this->quotes->get_all_folders();

		// Список главных разделов
		$data['sPartsNames'] = array( "0" => "Leads", "1"=>"Quotes", "2"=>"Orders", "3"=>"Archive");

		foreach($data['folders'] as $parts){
				//print_r($parts->spart);
				$parts->Emails = $this->emails->get_all_emails_list($parts->id);

				$data['parts'][$parts->spart][] = $parts;
		}

  		$this->load->view('admin/perscab/profile', $data);
  }

  // CONFIG
  // Редактирование письма
  public function chemailvalue(){
  		$name = $this->input->post('name');
  		$value = $this->input->post('value');
  		$id = $this->input->post('id');
  		$this->db->query("UPDATE `emails_lists` SET `".$name."`='".$value."' WHERE `id`=".$id);
  }

  // Удаление письма
  public function deleteemail($id){
  		$this->db->delete('emails', array('id'=>$id));
  		redirect(base_url()."admin/", 'refresh');
  }

   // Удаление письма из списка
  public function deleteemailfromfolder($id){
  		$this->db->delete('emails_lists', array('id'=>$id));
  		//redirect(base_url()."admin/", 'refresh');
  }

  // Добавление письма
  public function addchemail(){

  	
  	$Ename = $this->input->post('Ename');

  	$Efrom = $this->input->post('Efrom');
  	$Enfrom = $this->input->post('Enfrom');
  	$Ereplyto = $this->input->post('Ereplyto');
  //	$Ecc = $this->input->post('Ecc');
  	$Ebcc = $this->input->post('Ebcc');
  	
  	$Esubject = $this->input->post('Esubject');
  	 $Etext = $this->input->post('Etext');
  	   $Eid = $this->input->post('Eid');
  	   //$AutomatedTo = $this->input->post('AutomatedTo');
  	   $OnlyInworkTime = $this->input->post('OnlyInworkTime');
  	   $Etype = $this->input->post('Etype');

  	   if(!isset($Etype)) $Etype = 0;

  	  $sendToDriver = $this->input->post('sendToDriver');
  	  if((isset($sendToDriver))&&($sendToDriver == "on")) {
  	  	$sendToDriver = 1;
  	  }else{
  	  	$sendToDriver = 0;
  	  }


	    $Ename = addslashes($Ename);
	    $Esubject = addslashes($Esubject);
	    $Etext = addslashes($Etext);
	    if(!($Eid > 0)){
	    	$addArray = array(
	    		'name'=>$Ename,
	    		'subject'=>$Esubject,
	    		'text'=>$Etext,
	    		'etype'=>$Etype,
	    		'sendToDriver'=>$sendToDriver,
	    		'OnlyInworkTime'=>$OnlyInworkTime,
	    	//	'cc'=>$Ecc,
	    		'bcc'=>$Ebcc,
	    		'efrom'=>$Efrom,
	    		'enfrom'=>$Enfrom,
	    		'replyto'=>$Ereplyto

	    		);
	    	$this->db->insert('emails',$addArray);
	    	echo $this->db->insert_id();
	    }else{
	    	$chArray = array(
	    		'name'=>$Ename,
	    		'subject'=>$Esubject,
	    		'text'=>$Etext,
	    		'sendToDriver'=>$sendToDriver,	
	    		//'AutomatedTo'=>$AutomatedTo,
	    		'OnlyInworkTime'=>$OnlyInworkTime,
	    	//	'cc'=>$Ecc,
	    		'bcc'=>$Ebcc,
	    		'efrom'=>$Efrom,
	    		'replyto'=>$Ereplyto,
	    		'enfrom'=>$Enfrom);

	    	$this->db->where('id',$Eid);
	 		$this->db->update('emails', $chArray);

	    }

	    //echo $this->db->last_query();
	  	
	    //redirect(base_url()."admin/", 'refresh');

  }
  // [END] CONFIG

// Редактирование квоты из верхнего блока
  public function update_fullinfo_quote(){
  	 // Попытка авторизоватся
     $data['user'] = $this->user;

     $newVals = array();	
     $qid = $this->input->post('qid');
     $newVals['FirstName'] = $cFname = $this->input->post('Fname');
     $newVals['Email'] = $cEmail = $this->input->post('Femail');
     $newVals['Phone'] = $cPhone = $this->input->post('Fphone');
 	// $newVals['Phone2'] = $cPhone2 = $this->input->post('Fphone2');

 	 $quote = $this->quotes->get_quote_by_id_full($qid);


 	$persid = $quote->contact;
 	$chArray = array(
 		'FirstName'=>$cFname,
 		'Email'=>$cEmail,
 		'Phone'=>$cPhone,
 		//'Phone2'=>$cPhone2,
 		);

 	$this->quotes->update_users_contact($persid, $chArray);

 	$newVals['carModel'] = $carModel = $this->input->post('FcarModel');	
 	$newVals['carMake'] = $carMake = $this->input->post('FcarMake');		

 	$newVals['distFromCity'] = $distFromCity = $this->input->post('FdistFromCity');
 	$newVals['distFromState'] = $distFromState = $this->input->post('FdistFromState');
 	$newVals['distFromZip'] = $distFromZip = $this->input->post('FdistFromZip');

 	$newVals['distToCity'] = $distToCity = $this->input->post('FdistToCity');
 	$newVals['distToState'] = $distToState = $this->input->post('FdistToState');
 	$newVals['distToZip'] = $distToZip = $this->input->post('FdistToZip');	

 	$newVals['distance'] = $distance = $this->input->post('Fdistance');	

 	//$newVals['arriveDate'] = $arriveDate = str_replace('/', '.', $this->input->post('FarrDate'));
 	 $arriveDate = date_parse_from_format('m/d/y', $this->input->post('FarrDate')); 
 	 $newVals['arriveDate'] = $arriveDate = $arriveDate['year']."-".$arriveDate['month']."-".$arriveDate['day'];

 	$newVals['price'] = $price = $this->input->post('FtotalPrice') - $this->input->post('Fdeposit');		
 	$newVals['deposit'] = $deposit = $this->input->post('Fdeposit');

 	$newVals['CarrierPay'] = $CarrierPay = $this->input->post('priceValF');	
 	$newVals['price_per_mile'] = $price_per_mile = $this->input->post('pricePMValG');	

 	$chArray = array(
 		'distFromCity'=>$distFromCity,
 		'distFromState'=>$distFromState,
 		'distFromZip'=>$distFromZip,
 		'distToCity'=>$distToCity,
 		'distToState'=>$distToState,
 		'distToZip'=>$distToZip,
 		'arriveDate'=>date('Y.m.d', strtotime($arriveDate)),
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'distance'=>$distance,
 		'CarrierPay'=>$CarrierPay,
 		'price_per_mile'=>$price_per_mile
 		);	
	
 	$this->quotes->update_quote_full($qid, $chArray);

 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		if($quote[$key] != $val) 
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 	}


 	$pAddrStreet = $this->input->post('pAddr');
	$dAddrStreet = $this->input->post('dAddr');
	if(($dAddrStreet != "")||($pAddrStreet != "")){
		$persid = $quote->Pcontact;
	 	$chArray = array(
	 		'Addr_street'=>$pAddrStreet,
	 		'ctype'=>1
	 		);

	 
	 	$Pcontact = $this->quotes->update_users_contact($persid, $chArray); 	
	 	$newVals = $chArray;
	 	foreach($newVals as $key=>$val){
	 		$quote = (array) $quote;
	 		//echo $quote[$key]." ".$val." ; ";
	 		if($quote[$key] != $val){ 
	 			if($val == "") $val = "EMPTY"; 
	 			$this->statistic->set_quote_statistic($quote['id'], "Pickup ".$key, $quote[$key], $val, $this->user->id, 0);
	 			
	 		}
	 	}	
	 	
	 	$quote = (object) $quote;
	 	
	 	
	 	

	 	$persid = $quote->Dcontact;
	 	$chArray = array(
	 		'Addr_street'=>$dAddrStreet,
	 		'ctype'=>2
	 		);

	 	$Dcontact = $this->quotes->update_users_contact($persid, $chArray); 	
		$newVals = $chArray;


	 	foreach($newVals as $key=>$val){
	 		$quote = (array) $quote;
	 		//echo $quote[$key]." ".$val." ; ";
	 		if($quote[$key] != $val) 
	 			$this->statistic->set_quote_statistic($quote['id'], "Dispatch ".$key, $quote[$key], $val, $this->user->id, 0);
	 	}	
	 	$quote = (object) $quote;
	 	
	 	$chArray = array(
	 		'qid'=>$qid,
	 		'Pcontact'=>$Pcontact,
	 		'Dcontact'=>$Dcontact
	 		);

	 	$this->quotes->update_quote_order($qid, $chArray);
	}
  }


// Редактирование квоты
 public function update_quote(){
 	// Попытка авторизоватся
    $data['user'] = $this->user;
    //print_r($this->input->post

    $qid = $this->input->post('qid');

    // Client Contact
 	$FirstName = $this->input->post('FirstName');
 	$Mobile = $this->input->post('Mobile');
 	$Email = $this->input->post('Email');
 	$timeZone = $this->input->post('timeZone');
 	$Phone = $this->input->post('Phone');
 	$Phone2 = $this->input->post('Phone2');
 	$predContact = $this->input->post('predContact');
 	$clientNotice = $this->input->post('clientNotice');

 	$arriveDate = date_parse_from_format('m/d/y', $this->input->post('arriveDate')); 
 	$arriveDate = $arriveDate['year']."-".$arriveDate['month']."-".$arriveDate['day'];
 	
 	$shipperNote = $this->input->post('shipperNote');
 	// [END] Client Contact

 	// Vehicle
 	$carYear = $this->input->post('carYear');
 	$vechinesRun = $this->input->post('vechinesRun');
 	$carMake = $this->input->post('carMake');
 	$shipVia = $this->input->post('shipVia');
 	$carModel = $this->input->post('carModel');
 	$carVin = $this->input->post('carVin');
 	$carType = $this->input->post('carType');
 	$Vnote = $this->input->post('Vnote');
 	// [END] Vehice

 	// Price
 	$TotalPrice = $this->input->post('TotalPrice');
 	$deposit = $this->input->post('deposit');
 	$CarrierPay = $this->input->post('CarrierPay');
 	$BalancePaydBy = $this->input->post('BalancePaydBy');
 	$price = $TotalPrice - $deposit;
 	// [END] Price

 	// Origin & destanation
 	$distFromCity = $this->input->post('distFromCity');
 	$distFromState = $this->input->post('distFromState');
 	$distFromZip = $this->input->post('distFromZip');
 	$distFromCountry = $this->input->post('distFromCountry');

    $distToCity = $this->input->post('distToCity');
 	$distToState = $this->input->post('distToState');
 	$distToZip = $this->input->post('distToZip');
 	$distToCountry = $this->input->post('distToCountry');
 	// [END] Origin & destanation


 	$quote = $this->quotes->get_quote_order_by_id($qid);

 	// Client contact
 	$chArray = array(
 		'FirstName'=>$FirstName,
 		'Phone'=>$Phone,
 		'Phone2'=>$Phone2,
 		'Email'=>$Email,
 		'Mobile'=>$Mobile,
 		'timeZone'=>$timeZone,
 		'predContact'=>$predContact,
 		'clientNotice'=>$clientNotice,
 		'ctype'=>0
 	);

 
 	$Pcontact = $this->quotes->update_users_contact($quote->contact, $chArray); 	
 	$newVals = $chArray;
 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY"; 
 			$this->statistic->set_quote_statistic($quote['id'], "Client ".$key, $quote[$key], $val, $this->user->id, 0);
 			
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Client contact


 	// Quote
 	$chArray = array(
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'orderDate'=>date('Y-m-d H:i:s'),
 		'CarrierPay'=>$CarrierPay,
 		'carYear'=>$carYear,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'carType'=>$carType,
 		'vechinesRun'=>$vechinesRun,
 		'shipVia'=>$shipVia,
 		'carVin'=>$carVin,
 		'Vnotes'=>$Vnote,
 		'shipperNote'=>$shipperNote,
 		'arriveDate'=>date('Y.m.d', strtotime($arriveDate)),
 		'distFromCity'=>$distFromCity,
 		'distFromState'=>$distFromState,
 		'distFromZip'=>$distFromZip,
 		'distFromCountry'=>$distFromCountry,
 		'distToCity'=>$distToCity,
 		'distToState'=>$distToState,
 		'distToZip'=>$distToZip,
 		'distToCountry'=>$distToCountry
 		);	

 	
 	$this->quotes->update_quote_full($qid, $chArray);

 	$newVals = $chArray;
	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY";
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Quote


/*
 	$newVals = array();
 	
 	$qid = $this->input->post('qid');
 	$newVals['FirstName'] = $cFname = $this->input->post('cFname');
 	$newVals['SecondName'] = $cSname = $this->input->post('cSname');
 	$newVals['Company'] = $cCompany = $this->input->post('cCompany');
 	$newVals['Email'] = $cEmail = $this->input->post('cEmail');
 	$newVals['Phone'] = $cPhone = $this->input->post('cPhone');
 	$newVals['Phone2'] = $cPhone2 = $this->input->post('cPhone2');
 	$newVals['Mobile'] = $cMobile = $this->input->post('cMobile');
 	$newVals['Fax'] = $cFax = $this->input->post('cFax');
 	$newVals['Addr_street'] = $cAddrStreet = $this->input->post('cAddrStreet');
 	$newVals['Addr_city'] = $cAddrCity = $this->input->post('cAddrCity');
 	$newVals['Addr_state'] = $cAddrState = $this->input->post('cAddrState');
 	$newVals['Addr_zip'] = $cAddrZip = $this->input->post('cAddrZip');
 	$newVals['Addr_country'] = $cAddrCountry = $this->input->post('cAddrCountry');

 	$quote = $this->quotes->get_quote_by_id_full($qid);


 	$persid = $quote->contact;
 	$chArray = array(
 		'FirstName'=>$cFname,
 		'SecondName'=>$cSname,
 		'Company'=>$cCompany,
 		'Email'=>$cEmail,
 		'Phone'=>$cPhone,
 		'Phone2'=>$cPhone2,
 		'Mobile'=>$cMobile,
 		'Fax'=>$cFax,
 		'Addr_street'=>$cAddrStreet,
 		'Addr_city'=>$cAddrCity,
 		'Addr_state'=>$cAddrState,
 		'Addr_zip'=>$cAddrZip,
 		'Addr_country'=>$cAddrCountry,
 		);

 	$this->quotes->update_users_contact($persid, $chArray);

	$newVals['distFromCity'] = $distFromCity = $this->input->post('distFromCity');
 	$newVals['distFromState'] = $distFromState = $this->input->post('distFromState');
 	$newVals['distFromZip'] = $distFromZip = $this->input->post('distFromZip');
 	$newVals['distFromCountry'] = $distFromCountry = $this->input->post('distFromCountry');

 	$newVals['distToCity'] = $distToCity = $this->input->post('distToCity');
 	$newVals['distToState'] = $distToState = $this->input->post('distToState');
 	$newVals['distToZip'] = $distToZip = $this->input->post('distToZip');
 	$newVals['distToCountry'] = $distToCountry = $this->input->post('distToCountry');
 	
 	$newVals['arriveDate'] = $arriveDate = $this->input->post('arriveDate');		
 	$newVals['price'] = $price = $this->input->post('price');		
 	$newVals['deposit'] = $deposit = $this->input->post('deposit');

 	$newVals['carYear'] = $carYear = $this->input->post('carYear');	
 	$newVals['carModel'] = $carModel = $this->input->post('carModel');	
 	$newVals['carMake'] = $carMake = $this->input->post('carMake');	
 	$newVals['carType'] = $carType = $this->input->post('carType');	

 	$chArray = array(
 		'distFromCity'=>$distFromCity,
 		'distFromState'=>$distFromState,
 		'distFromZip'=>$distFromZip,
 		'distFromCountry'=>$distFromCountry,
 		'distToCity'=>$distToCity,
 		'distToState'=>$distToState,
 		'distToZip'=>$distToZip,
 		'distToCountry'=>$distToCountry,
 		'arriveDate'=>date('Y.m.d', strtotime($arriveDate)),
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'carYear'=>$carYear,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'carType'=>$carType
 		);	
	
 	$this->quotes->update_quote_full($qid, $chArray);

	$newVals['vechinesRun'] = $vehicleRun = $this->input->post('vehicleRun');		
 	$newVals['shipVia'] = $shipVia = $this->input->post('shipVia');		
 	$newVals['shipperNote'] = $shipperNote = $this->input->post('shipperNote');

 	$chArray = array(
 		'qid'=>$qid,
 		'vechinesRun'=>$vehicleRun,
 		'shipVia'=>$shipVia,
 		'shipperNote'=>$shipperNote);
 	$this->quotes->update_quote_info($qid, $chArray);		

 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		if($quote[$key] != $val) 
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 	}
	*/
 }
 
 // Редактирование Bookit
 public function update_quote_order(){
 	// Попытка авторизоватся
    $data['user'] = $this->user;
 	
 	$qid = $this->input->post('qid');

 	// Client Contact
 	$FirstName = $this->input->post('FirstName');
 	$Mobile = $this->input->post('Mobile');
 	$Email = $this->input->post('Email');
 	$timeZone = $this->input->post('timeZone');
 	$Phone = $this->input->post('Phone');
 	$Phone2 = $this->input->post('Phone2');
 	$predContact = $this->input->post('predContact');
 	$clientNotice = $this->input->post('clientNotice');
 	// [END] Client Contact

 	// Vehicle
 	$carYear = $this->input->post('carYear');
 	$vechinesRun = $this->input->post('vechinesRun');
 	$carMake = $this->input->post('carMake');
 	$shipVia = $this->input->post('shipVia');
 	$carModel = $this->input->post('carModel');
 	$carVin = $this->input->post('carVin');
 	$carType = $this->input->post('carType');
 	$Vnote = $this->input->post('Vnote');
 	// [END] Vehice

 	// Pickup Info
 	$pFname = $this->input->post('pFname');
 	$pAddrStreet = $this->input->post('pAddrStreet');
 	$pPhone = $this->input->post('pPhone');
 	$pPhone2 = $this->input->post('pPhone2');
 	$pAddrCity = $this->input->post('pAddrCity');
 	$pAddrState = $this->input->post('pAddrState');
 	$pMobile = $this->input->post('pMobile');
 	$pAddrZip = $this->input->post('pAddrZip');
 	// [END] Pickup Info
 	
 	// Delivery Info
 	$dFname = $this->input->post('dFname');
 	$dAddrStreet = $this->input->post('dAddrStreet');
 	$dPhone = $this->input->post('dPhone');
 	$dPhone2 = $this->input->post('dPhone2');
 	$dAddrCity = $this->input->post('dAddrCity');
 	$dAddrState = $this->input->post('dAddrState');
 	$dMobile = $this->input->post('dMobile');
 	$dAddrZip = $this->input->post('dAddrZip');
 	// [END] Delivery Info

 	// Dates
 	$LoadDate = date_parse_from_format('m/d/y', $this->input->post('LoadDate')); 
 	$LoadDate = $LoadDate['year']."-".$LoadDate['month']."-".$LoadDate['day'];

 	$LoadDateEnd = date_parse_from_format('m/d/y', $this->input->post('LoadDateEnd')); 
 	$LoadDateEnd = $LoadDateEnd['year']."-".$LoadDateEnd['month']."-".$LoadDateEnd['day'];
 	
 	$DeliveryDate = date_parse_from_format('m/d/y', $this->input->post('DeliveryDate')); 
 	$DeliveryDate = $DeliveryDate['year']."-".$DeliveryDate['month']."-".$DeliveryDate['day'];
 	
 	$DeliveryDateEnd = date_parse_from_format('m/d/y', $this->input->post('DeliveryDateEnd')); 
 	$DeliveryDateEnd = $DeliveryDateEnd['year']."-".$DeliveryDateEnd['month']."-".$DeliveryDateEnd['day'];
 	// [END] Dates

 	// Price
 	$TotalPrice = $this->input->post('TotalPrice');
 	$deposit = $this->input->post('deposit');
 	$CarrierPay = $this->input->post('CarrierPay');
 	$BalancePaydBy = $this->input->post('BalancePaydBy');
 	$price = $TotalPrice - $deposit;
 	// [END] Price

 	$quote = $this->quotes->get_quote_order_by_id($qid);

 	// Quote
 	$chArray = array(
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'orderDate'=>date('Y-m-d H:i:s'),
 		'CarrierPay'=>$CarrierPay,
 		'carYear'=>$carYear,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'carType'=>$carType,
 		'vechinesRun'=>$vechinesRun,
 		'shipVia'=>$shipVia,
 		'carVin'=>$carVin,
 		'Vnotes'=>$Vnote
 		);	

 	
 	$this->quotes->update_quote_full($qid, $chArray);

 	$newVals = $chArray;
	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY";
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Quote


 	// Client contact
 	$chArray = array(
 		'FirstName'=>$FirstName,
 		'Phone'=>$Phone,
 		'Phone2'=>$Phone2,
 		'Email'=>$Email,
 		'Mobile'=>$Mobile,
 		'timeZone'=>$timeZone,
 		'predContact'=>$predContact,
 		'clientNotice'=>$clientNotice,
 		'ctype'=>0
 	);

 
 	$Pcontact = $this->quotes->update_users_contact($quote->contact, $chArray); 	
 	$newVals = $chArray;
 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY"; 
 			$this->statistic->set_quote_statistic($quote['id'], "Client ".$key, $quote[$key], $val, $this->user->id, 0);
 			
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Client contact

 	// Pickup contact
 	$persid = $quote->Pcontact;
 	$chArray = array(
 		'FirstName'=>$pFname,
 		'Phone'=>$pPhone,
 		'Phone2'=>$pPhone2,
 		'Mobile'=>$pMobile,
 		'Addr_street'=>$pAddrStreet,
 		'Addr_city'=>$pAddrCity,
 		'Addr_state'=>$pAddrState,
 		'Addr_zip'=>$pAddrZip,
 		'ctype'=>1
 		);

 
 	$Pcontact = $this->quotes->update_users_contact($persid, $chArray); 	
 	$newVals = $chArray;
 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY"; 
 			$this->statistic->set_quote_statistic($quote['id'], "Pickup ".$key, $quote[$key], $val, $this->user->id, 0);
 			
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Pickup contact

 	// Delivery contact
 	$persid = $quote->Dcontact;
 	$chArray = array(
 		'FirstName'=>$dFname,
 		'Phone'=>$dPhone,
 		'Phone2'=>$dPhone2,
 		'Mobile'=>$dMobile,
 		'Addr_street'=>$dAddrStreet,
 		'Addr_city'=>$dAddrCity,
 		'Addr_state'=>$dAddrState,
 		'Addr_zip'=>$dAddrZip,
 		'ctype'=>2
 		);

 	$Dcontact = $this->quotes->update_users_contact($persid, $chArray); 	
	$newVals = $chArray;


 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val) 
 			$this->statistic->set_quote_statistic($quote['id'], "Dispatch ".$key, $quote[$key], $val, $this->user->id, 0);
 	}	
 	$quote = (object) $quote;
 	// [END] contact

 	// Order
 	$chArray = array(
 		'qid'=>$quote->id,
 		'Pcontact'=>$Pcontact,
 		'Dcontact'=>$Dcontact,
 		'LoadDate'=>date('Y.m.d', strtotime($LoadDate)),
 		'LoadDateEnd'=>date('Y.m.d', strtotime($LoadDateEnd)),
 		'DeliveryDate'=>date('Y.m.d', strtotime($DeliveryDate)),
 		'DeliveryDateEnd'=>date('Y.m.d', strtotime($DeliveryDateEnd)),
 		'BalancePaydBy'=>$BalancePaydBy
 		);

 	$this->quotes->update_quote_order($qid, $chArray);


	$newVals = $chArray;
	$quote = (array) $quote;
	//$this->statistic->set_quote_statistic($quote['id'], "MOVE", "QUOTE", "ORDER", $this->user->id, 0);
	foreach($newVals as $key=>$val){
 		
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY";
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 		}
 	}
 	// [END] Order

 	$this->domove($qid, 83);

 	 
 } 

 // Редактирование Dispatch
 public function update_quote_dispatch(){
 	// Попытка авторизоватся
    $data['user'] = $this->user;
 	
 	$qid = $this->input->post('qid');

 	$quote = $this->quotes->get_quote_order_by_id($qid);
 	
 	// Driver
 	$driverid = $this->input->post('driverid');
 	$Carrier = $this->input->post('Carrier');
 	$DriverPhone = $this->input->post('DriverPhone');
 	$DriverPhone2 = $this->input->post('DriverPhone2');
 	$DriverCargo = $this->input->post('DriverCargo');
 	$DriverMC = $this->input->post('DriverMC');
 	$DriverAddr = $this->input->post('DriverAddr');
 	$DriverMobile = $this->input->post('DriverMobile');
 	$DriverPolicNumber = $this->input->post('DriverPolicNumber');
 	$DriverContact = $this->input->post('DriverContact');
 	$DriverEmail = $this->input->post('DriverEmail');
 	$DriverTrailerType = $this->input->post('DriverTrailerType');
 	// [END] Driver
 	

 	// Vehicle
 	$carYear = $this->input->post('carYear');
 	$vechinesRun = $this->input->post('vechinesRun');
 	$carMake = $this->input->post('carMake');
 	$shipVia = $this->input->post('shipVia');
 	$carModel = $this->input->post('carModel');
 	$carVin = $this->input->post('carVin');
 	$carType = $this->input->post('carType');
 	$Vnote = $this->input->post('Vnote');
 	// [END] Vehice

 	// Pickup Info
 	$pFname = $this->input->post('pFname');
 	$pAddrStreet = $this->input->post('pAddrStreet');
 	$pPhone = $this->input->post('pPhone');
 	$pPhone2 = $this->input->post('pPhone2');
 	$pAddrCity = $this->input->post('pAddrCity');
 	$pAddrState = $this->input->post('pAddrState');
 	$pMobile = $this->input->post('pMobile');
 	$pAddrZip = $this->input->post('pAddrZip');
 	// [END] Pickup Info
 	
 	// Delivery Info
 	$dFname = $this->input->post('dFname');
 	$dAddrStreet = $this->input->post('dAddrStreet');
 	$dPhone = $this->input->post('dPhone');
 	$dPhone2 = $this->input->post('dPhone2');
 	$dAddrCity = $this->input->post('dAddrCity');
 	$dAddrState = $this->input->post('dAddrState');
 	$dMobile = $this->input->post('dMobile');
 	$dAddrZip = $this->input->post('dAddrZip');
 	// [END] Delivery Info

 	// Dates
 	//$LoadDate = $this->input->post('LoadDate');
 	//$LoadDateEnd = $this->input->post('LoadDateEnd');
 	//$DeliveryDate = $this->input->post('DeliveryDate');
 	//$DeliveryDateEnd = $this->input->post('DeliveryDateEnd');
 	$LoadDate = date_parse_from_format('m/d/y', $this->input->post('LoadDate')); 
 	$LoadDate = $LoadDate['year']."-".$LoadDate['month']."-".$LoadDate['day'];

 	$LoadDateEnd = date_parse_from_format('m/d/y', $this->input->post('LoadDateEnd')); 
 	$LoadDateEnd = $LoadDateEnd['year']."-".$LoadDateEnd['month']."-".$LoadDateEnd['day'];
 	
 	$DeliveryDate = date_parse_from_format('m/d/y', $this->input->post('DeliveryDate')); 
 	$DeliveryDate = $DeliveryDate['year']."-".$DeliveryDate['month']."-".$DeliveryDate['day'];
 	
 	$DeliveryDateEnd = date_parse_from_format('m/d/y', $this->input->post('DeliveryDateEnd')); 
 	$DeliveryDateEnd = $DeliveryDateEnd['year']."-".$DeliveryDateEnd['month']."-".$DeliveryDateEnd['day'];
 	// [END] Dates

 	// Price
 	$TotalPrice = $this->input->post('TotalPrice');
 	$deposit = $this->input->post('deposit');
 	$CarrierPay = $this->input->post('CarrierPay');
 	$BalancePaydBy = $this->input->post('BalancePaydBy');
 	$price = $TotalPrice - $deposit;
 	// [END] Price

 	// Driver
 	$chArray = array(
 		'name'=>$Carrier,
 		'phone'=>$DriverPhone,
 		'phone2'=>$DriverPhone2,
 		'dCargo'=>$DriverCargo,
 		'dMC'=>$DriverMC,
 		'addr2'=>$DriverAddr,
 		'mobile'=>$DriverMobile,
 		'ciferki'=>$DriverPolicNumber,
 		'contact'=>$DriverContact,
 		'email'=>$DriverEmail,
 		'trailerType'=>$DriverTrailerType
 		);
 	
 	if((!isset($driverid))||($driverid == 0)){
 		$chArray['dtype'] = "Carrier";
 	}

 	$driverid = $this->drivers->update_driver($driverid, $chArray);
 	
 	// [END] Driver

 	$quote = $this->quotes->get_quote_order_by_id($qid);

 	// Quote
 	$chArray = array(
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'orderDate'=>date('Y-m-d H:i:s'),
 		'CarrierPay'=>$CarrierPay,
 		'carYear'=>$carYear,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'carType'=>$carType,
 		'vechinesRun'=>$vechinesRun,
 		'shipVia'=>$shipVia,
 		'carVin'=>$carVin,
 		'Vnotes'=>$shipperNote,
 		'driverid'=>$driverid
 		);	

 	
 	$this->quotes->update_quote_full($qid, $chArray);

 	$newVals = $chArray;
	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY";
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Quote


 	// Pickup contact
 	$persid = $quote->Pcontact;
 	$chArray = array(
 		'FirstName'=>$pFname,
 		'Phone'=>$pPhone,
 		'Phone2'=>$pPhone2,
 		'Mobile'=>$pMobile,
 		'Addr_street'=>$pAddrStreet,
 		'Addr_city'=>$pAddrCity,
 		'Addr_state'=>$pAddrState,
 		'Addr_zip'=>$pAddrZip,
 		'ctype'=>1
 		);

 
 	$Pcontact = $this->quotes->update_users_contact($persid, $chArray); 	
 	$newVals = $chArray;
 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY"; 
 			$this->statistic->set_quote_statistic($quote['id'], "Pickup ".$key, $quote[$key], $val, $this->user->id, 0);
 			
 		}
 	}	
 	$quote = (object) $quote;
 	// [END] Pickup contact

 	// Delivery contact
 	$persid = $quote->Dcontact;
 	$chArray = array(
 		'FirstName'=>$dFname,
 		'Phone'=>$dPhone,
 		'Phone2'=>$dPhone2,
 		'Mobile'=>$dMobile,
 		'Addr_street'=>$dAddrStreet,
 		'Addr_city'=>$dAddrCity,
 		'Addr_state'=>$dAddrState,
 		'Addr_zip'=>$dAddrZip,
 		'ctype'=>2
 		);

 	$Dcontact = $this->quotes->update_users_contact($persid, $chArray); 	
	$newVals = $chArray;


 	foreach($newVals as $key=>$val){
 		$quote = (array) $quote;
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val) 
 			$this->statistic->set_quote_statistic($quote['id'], "Dispatch ".$key, $quote[$key], $val, $this->user->id, 0);
 	}	
 	$quote = (object) $quote;
 	// [END] contact

 	// Order
 	$chArray = array(
 		'qid'=>$quote->id,
 		'Pcontact'=>$Pcontact,
 		'Dcontact'=>$Dcontact,
 		'LoadDate'=>date('Y.m.d', strtotime($LoadDate)),
 		'LoadDateEnd'=>date('Y.m.d', strtotime($LoadDateEnd)),
 		'DeliveryDate'=>date('Y.m.d', strtotime($DeliveryDate)),
 		'DeliveryDateEnd'=>date('Y.m.d', strtotime($DeliveryDateEnd)),
 		'BalancePaydBy'=>$BalancePaydBy
 		);

 	$this->quotes->update_quote_order($qid, $chArray);


	$newVals = $chArray;
	$quote = (array) $quote;
	//$this->statistic->set_quote_statistic($quote['id'], "MOVE", "QUOTE", "ORDER", $this->user->id, 0);
	foreach($newVals as $key=>$val){
 		
 		//echo $quote[$key]." ".$val." ; ";
 		if($quote[$key] != $val){ 
 			if($val == "") $val = "EMPTY";
 			$this->statistic->set_quote_statistic($quote['id'], $key, $quote[$key], $val, $this->user->id, 0);
 		}
 	}
 	// [END] Order

	$emailid = $this->input->post('sendDriverEmail');
	$DriverEmail = $this->input->post('DriverEmail');
 	if((isset($emailid))&&($emailid!=0)&&($DriverEmail != "")){
 		$qid = $quote['id'];
 		$eid = $emailid;
	 	$addArray = array(
			'eid'=>$eid,
			'qid'=>$qid,
			'atDate'=>date('Y-m-d H:i:s'),
			'sendAtDate'=>date('Y-m-d H:i:s'),
			'specEmail'=>$DriverEmail
		);
 		$this->emails->add_to_send_process($addArray);
 	}
 	
 	$this->domove($qid, 87);
 	
 } 

 // Добавляем все записи в таблицу с полной информацией о квоте
 public function add_info_rows_to_quotes(){
 	$this->quotes->add_info_rows_to_quotes();
 } 

 // Обновляем значения профиля админа
 public function crm_user_update_info(){
 	$uid = $this->input->post('uid');
 	$Cname = $this->input->post('Cname');
 	$Fname = $this->input->post('Fname');
 	$Email = $this->input->post('Email');
 	$Fax = $this->input->post('Fax');
 	$Phone = $this->input->post('Phone');
 	$Phone2 = $this->input->post('Phone2');
 	$Mobile = $this->input->post('Mobile');
 	$addrStreet = $this->input->post('addrStreet');
 	$addrCity = $this->input->post('addrCity');
 	$addrState = $this->input->post('addrState');
 	$qIdStart = $this->input->post('qIdStart');
 	$uTimezone = $this->input->post('uTimezone');
 	$uDeposit = $this->input->post('uDeposit');
 	$CDtoken = $this->input->post('CDtoken');
 	
 	$addArray = array(
 		'Cname'=>$Cname,
 		'Fname'=>$Fname,
 		'email'=>$Email,
 		'Fax'=>$Fax,
 		'Phone'=>$Phone,
 		'Phone2'=>$Phone2,
 		'Mobile'=>$Mobile,
 		'addrStreet'=>$addrStreet,
 		'addrCity'=>$addrCity,
 		'addrState'=>$addrState,
 		'qIdStart'=>$qIdStart,
 		'uTimezone'=>$uTimezone,
 		'uDeposit'=>$uDeposit,
 		'CDtoken'=>$CDtoken);	

 	$this->users->update_user($uid, $addArray);	
 }
 
// Изменение цены за милю
public function set_price_per_mile(){
	$qid = $this->input->post('qid');
	$price_per_mile = $this->input->post('price_per_mile');

	$this->quotes->update_quote($qid, 'price_per_mile', $price_per_mile);

	$quote = $this->quotes->get_quote_by_id($qid);
	//print_r($quote);
	$dist = $quote->distance;
	if($quote->distance == 0){
  		$dist = json_decode(
		file_get_contents(
			"http://calc-api.ru/app:geo-api/null".str_replace(' ','%20','?a='.$quote->distFromState.",".trim($quote->distFromCity).'&b='.trim($quote->distToState).",".trim($quote->distToCity))
		),
		true
		);
	  	
	  	$dist = round($dist['distanse']* 0.62137119);
		$this->quotes->update_quote($quote->id, 'distance', $dist);
	}
	//echo $price_per_mile;
	echo $tarif = (($price_per_mile*$dist)+$quote->deposit);
	$this->quotes->update_quote($quote->id, 'CarrierPay', ($price_per_mile*$dist));
}

// Редактирование значения у квоты
public function set_quote_new_value(){
	$qid = $this->input->post('qid');
	$name = $this->input->post('name');
	$value = $this->input->post('value');
	$this->quotes->update_quote($qid, $name, $value);
}

// Редактирование значения у контакта
public function set_contact_new_value(){
	$cid = $this->input->post('cid');
	$name = $this->input->post('name');
	$value = $this->input->post('value');
	$this->quotes->update_contact($cid, $name, $value);
}

// Покзать блок история операций для квоты
public function get_quote_history(){

	$qid = $this->input->post('qid');
	$data['stat'] = $this->statistic->get_quote_statistic($qid);
	$this->load->view('admin/parts/qhistory', $data);	
}  

// Получаем таблицу с водителями
public function  get_drivers_table(){
	$data['user'] = $this->user->id;
	$qid = $this->input->post('qid');
	$data['quote'] = $quote = $this->quotes->get_quote_by_id($qid);
	$data['qid'] = $qid;
    //print_r($data['user']);
    //exit();
	$data['drivers'] = $this->drivers->get_drivers(trim($quote->distFromState), trim($quote->distToState));

	
	//print_r($data['drivers']);
	$counter = 0;
	foreach($data['drivers'] as $driver){
		$data['drivers'][$counter]->countNotices = $this->quotes->get_count_notices(($driver->driver_id*(-1)));
		//echo $driver->driver_id."-".$data['drivers'][$counter]->countNotices."|";
		$counter++;
	}	

	//print_r($data['drivers']);
	$this->load->view('admin/parts/drivers', $data);
}

// Получение таблицы Локальных компаний
public function get_localcompanies_table()
{

    $data['user'] = $this->user;
    $qid = $this->input->post('qid');
    $data['sorting']=$this->input->post('sorting');
    //print_r($data['sorting']);
    $data['quote'] = $quote = $this->quotes->get_quote_by_id($qid);
    $data['qid'] = $qid;
    $data['drivers'] = $this->drivers->get_drivers_local(trim($quote->distFromState), trim($quote->distToState), $data['user'], $data['sorting']);
    //$data['tab'] = 'local';

    $counter = 0;
    foreach($data['drivers'] as $driver){
        $data['drivers'][$counter]->countNotices = $this->quotes->get_count_notices(($driver->id*(-1)));
        //echo $driver->driver_id."-".$data['drivers'][$counter]->countNotices."|";
        //echo $driver->id."---".$driver->drivers_id."<br/>";
        $counter++;
    }
    if($data['sorting']=="Origin")
    {
        $data['sorting']="Destination";
    }
    else
    {
        $data['sorting']="Origin";
    }
    //print_r($data['drivers']);
    $this->load->view('admin/parts/drivers_local', $data);
}

// Получение таблицы Фаворайтс
public function get_favorites_table()
{
    $data['user'] = $this->user;
    $qid = $this->input->post('qid');
    $data['sorting']=$this->input->post('sorting');
    $data['quote'] = $quote = $this->quotes->get_quote_by_id($qid);
    $data['qid'] = $qid;
    $data['drivers'] = $this->drivers->get_drivers_favorite($data['user']);
    //$data['tab'] = 'favorites';


    $counter = 0;
    foreach($data['drivers'] as $driver){
        $data['drivers'][$counter]->countNotices = $this->quotes->get_count_notices(($driver->id*(-1)));
        //echo $driver->driver_id."-".$data['drivers'][$counter]->countNotices."|";
        //echo $driver->id."---".$driver->drivers_id."<br/>";
        $counter++;
    }

    //print_r($data['drivers']);
    $this->load->view('admin/parts/drivers_favorites', $data);
}

//Добавление в Фаворайтс
public function add_favorites()
{
    $data_insert = array();
    $data_insert['id'] = 0;
    $data_insert['drivers_id'] = $this->input->post('drivers_id');
    $data_insert['users_crm_id'] = $this->user->id;
    $data_insert['datetime'] = new DateTime();
    $data_insert['datetime'] = $data_insert['datetime']->format('Y-m-d H:i:s');
    $this->db->insert('drivers_favorites', $data_insert);

}

//Удаление из Фаворайтс
public function remove_favorites()
{
    $data_delete = array();
    $data_delete['drivers_id_f'] = $this->input->post('drivers_id_f');
    $data_delete['users_crm_id'] = $this->user->id;
    $this->db->where('drivers_id', $data_delete['drivers_id_f'])
        ->where('users_crm_id', $data_delete['users_crm_id'])
        ->delete('favorites');

    //return null;
}

//---------SSF-Скрипт заполнения штатов (addrState.drivers)-----------
/*
public function get_localcompanies_table()
{
$this->db->select("id, (SUBSTRING_INDEX(addr2,',',-1)) as addrState", false)
->from('drivers');
$result=$this->db->get();
$data=$result->result_array();
$num=$result->num_rows();
    //print_r($data[0]['state']);

for($i=0;$i<$num;$i++)
{
$data[$i]['addrState']=substr($data[$i]['addrState'], 0,2);
    //$this->db->set('addrState', $data[$i]['addrState']);

    //print_r($i.'| |'.$data[$i]['id'].'| |'.$data[$i]['addrState'].'<br/>');
}
print_r('<br/>'."Update Start");
//$this->db->where('id', $data);
$this->db->update_batch('drivers', $data, 'id');
print_r('<br/>'."End");
}
*/
//---------------------------------------------------------------------------------------
/*----TFC-- S Q L Запрос создания таблицы favorites---------------------------
CREATE TABLE `drivers_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drivers_id` int(11) NOT NULL,
  `users_crm_id` int(11) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8;
/---------------------------------------------------------------------------------------*/

// Добавление пустого лида
public function add_empty_lead(){

	// Попытка авторизоватся
	$user = $this->autorize();
	$data['user'] = $user;

	$spart = $this->input->post('spart');
	$vehicleRun = 0;		
 	$shipVia = 0;		
 	$shipperNote = "";	

 	$distFromCity = ""; 	
 	$distFromState = "";
 	$distFromZip = 0;
 	$distFromCountry = "";

 	$distToCity = "";
 	$distToState = "";
 	$distToZip = 0;
 	$distToCountry = "";
 	
 	$arriveDate = "";		
 	$price = 0;		
 	$deposit = $user->uDeposit;

 	$carYear = 0;	
 	$carModel = "";	
 	$carMake = "";	
 	$carType = "";	

 	$cFname = "";
 	$cSname = "";
 	$cCompany = "";
 	$cEmail = "";
 	$cPhone = "";
 	$cPhone2 = "";
 	$cPhone3 = "";
 	$cMobile = "";
 	$cFax = "";
 	$cAddrStreet = "";
 	$cAddrCity = "";
 	$cAddrState = "";
 	$cAddrZip = 0;
 	$cAddrCountry = "";

 	$qid = 0;
	$chArray = array(
 		'FirstName'=>trim($cFname),
 		'SecondName'=>trim($cSname),
 		'Company'=>trim($cCompany),
 		'Email'=>trim($cEmail),
 		'Phone'=>trim($cPhone),
 		'Phone2'=>trim($cPhone2),
 		'Mobile'=>trim($cMobile),
 		'Fax'=>trim($cFax),
 		'Addr_street'=>trim($cAddrStreet),
 		'Addr_city'=>trim($cAddrCity),
 		'Addr_state'=>trim($cAddrState),
 		'Addr_zip'=>trim($cAddrZip),
 		'Addr_country'=>trim($cAddrCountry),
 		);

 	$contact = $this->quotes->update_users_contact(0, $chArray);
	
	
  	
  	

	$price = 0;
	$price_per_mile = 0;
	$dist = 0;

 	$chArray = array(
 		'spart'=>$spart,
 		'contact'=>$contact,
 		'distFromCity'=>$distFromCity,
 		'distFromState'=>$distFromState,
 		'distFromZip'=>$distFromZip,
 		'distFromCountry'=>$distFromCountry,
 		'distToCity'=>$distToCity,
 		'distToState'=>$distToState,
 		'distToZip'=>$distToZip,
 		'distToCountry'=>$distToCountry,
 		'arriveDate'=>$arriveDate,
 		'price'=>$price,
 		'deposit'=>$deposit,
 		'carYear'=>$carYear,
 		'carModel'=>$carModel,
 		'carMake'=>$carMake,
 		'carType'=>$carType,
 		'addDate'=>date('Y-m-d H:i:s'),
 		'price_per_mile'=>$price_per_mile, 
 		'distance'=>$dist,
 		'vechinesRun'=>$vehicleRun,
 		'shipVia'=>$shipVia,
 		'shipperNote'=>$shipperNote
 		);	
	
 	$qid = $this->quotes->update_quote_full($qid, $chArray);

 	echo $qid;



}

// Пометить алерт как прочитаный
public function update_action_as_read(){
	$aid = $this->input->post('aid');
	$this->statistic->update_action_as_read($aid);
}

// Пометить алерт как прочитаный
public function update_openemail_as_read(){
	$eid = $this->input->post('eid');
	$this->emails->update_open_as_read($eid);
}

// Прописать токен для пользования CD
public function add_cdtoken($token){
	// Попытка авторизоватся
	 $data['user'] = $user = $this->user;
	 //print_r($user);
	// echo $token;
	 $this->db->update('users_crm', array('CDtoken'=>""));
	 $this->db->where('id', $user->id);
	 $this->db->update('users_crm', array('CDtoken'=>$token));
	 redirect(base_url()."admin/main/", 'refresh');
}

// Получаем алерты список
public function get_alerts_list(){
	$user = $this->user;
	$aid = $this->input->post('aid');
	$firstLoad = $this->input->post('firstLoad');
	if($firstLoad == 1) $user->lastAlerts = "0000-00-00 00:00:00";  
	$data['alerts'] = $this->statistic->get_not_read_alerts($aid, $user->lastAlerts);
	if($aid == 2)
		$this->users->update_user($user->id, array('lastAlerts'=>date('Y-m-d H:i:s')));
	echo count($data['alerts'])."~";
	foreach($data['alerts'] as $alert) {  
    	echo'<div onclick="updateActionAsRead(\''.base_url().'\', \''.$alert->id.'\'); $(this).css(\'background-color\',\'#dddddd\').css(\'color\', \'#000000\');  $(\'#countAlerts'.$aid.'\').html(parseInt($(\'#countAlerts'.$aid.'\').html())-1);" style="padding:5px; padding-left:20px; padding-right:20px; color:#000000; border-bottom:solid 1px #dddddd;">'.$alert->atDate.'<br/><span style="font-size:10px;">quote id:</span> <span   onclick="dosearch(\''.base_url().'\', \'q.id\', '.$alert->qid.');" style="cursor:pointer; text-decoration:underline;"><b>'.str_pad ($alert->qid, 7,"0",STR_PAD_LEFT).'</b></span><br/><span style="font-size:10px;">action:</span>'.$alert->action.'</div>';
    } 
}

/*
public function drop_all_down(){
	//echo "a";
	//exit();
	$this->happyend();
}
*/

public function get_Bstatistic(){
	$data['cenceled'] = $data['quoted'] = $data['dispached'] = $data['ordered'] = 0;
	
	$data['countQuotes'] = $this->quotes->get_count_quotes(0);

	$data['quoted'] = $this->quotes->get_statistic_info(75, 0);
	$data['ordered'] = $this->quotes->get_statistic_info(83, 0);
	$data['dispached'] = $this->quotes->get_statistic_info(87, 0);
	$data['cenceled'] = $this->quotes->get_statistic_info(91, 0);

	$GsendStat = $this->quotes->getG_sent_statistic();	
	$data['emailsSent'] = $GsendStat->countSent;
	$data['emailsOpened'] = count($this->emails->get_all_opened_stat());
	$data['emailsRecived'] = count($this->emails->get_all_recived_stat());
	$data['emailsWaiting'] = $GsendStat->countWaiting;	
	if($data['emailsWaiting'] < 0) $data['emailsWaiting'] = 0;
	//$data['countNotices'] = $this->quotes->get_count_notices(0);	

	$data['formRequestCount'] = $this->emails->get_forms_count(0);
    $data['formInitCount'] = $this->emails->get_forms_count(1);
    $data['formSheduleCount'] = $this->emails->get_forms_count(2);

    $CountLeadsNew = $this->quotes->get_quotes_part_count(1);
    $CountLeadsSwat = $this->quotes->get_quotes_part_count(70);

    // Количество вниутри разделов
	$data['countInParts'] = $this->quotes->count_sum_inside_part();
	$CountLeads = $data['countInParts'][0];

	$data['globalsum'] = $this->quotes->get_global_orders_sum();
	$data['chargered'] = $this->quotes->get_chargered_sum();

	echo $data['countQuotes']."`".$data['quoted']."`".$data['ordered']."`".$data['dispached']."`".$data['cenceled']."`".$data['emailsWaiting']."`".$data['emailsSent']."`".$data['emailsOpened']."`".$data['emailsRecived']."`".$data['formInitCount']."`".$data['formRequestCount']."`".$data['formSheduleCount']."`".$CountLeadsNew."`".$CountLeadsSwat."`".$CountLeads."`".$data['chargered']."`".$data['globalsum'];
}


	function get_rejected(){
		/*
		$args = array(
	    	'key' => 'K67vGlb_hFW4fQdMEyQulQ',
	    	'message' => array(
	        	"html" => "test",
	       	 	"text" => null,
	        	"from_email" => "info@swatmoves.com",
	        	"from_name" => "S.W.A.T.",
	        	"subject" => "TEST",
	        	"to" => array(array("email" => "growzer@gmail.com")),
	        	"track_opens" => true,
	        	"track_clicks" => true
	    	)
		);

		$curl = curl_init('https://mandrillapp.com/api/1.0/messages/send.json' );

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
		*/
		//$response = curl_exec($curl);
		//$res = json_decode($response);
		//echo $mandrill_id = $res[0]->_id;
		//print_r($res);

		$args = array(
	    	'key' => 'K67vGlb_hFW4fQdMEyQulQ',
	    	"id" => '4f469b73721d4441a4d642b2dddd162a'
		);


		$curl = curl_init('https://mandrillapp.com/api/1.0/messages/info.json' );

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
		$response = curl_exec($curl);

		print_r($response);

	

	}

	// do charge
	public function docharge(){
		$qid = $this->input->post('qid');
		$charged = $this->input->post('charged');

		$this->quotes->docharge($qid, $charged);

	}

	// ----------- SMS LAUNCHER
	public function sendSMS(){
		$number = $this->input->post('number');
		$smstext = $this->input->post('smstext');
		$qid = $this->input->post('qid');
		if($number != ""){
			//echo "Start send..";
			$wsdl = 'https://callfire.com/api/1.0/wsdl/callfire-service-http-soap12.wsdl';
			$client = new SoapClient($wsdl, array(
			  'soap_version' => SOAP_1_2,
			  'login'        => 'b0309e07e2de',  
			  'password'     => '32a7f16fe28e8592'));

			$sendTextRequest = array(
				  'BroadcastName' => 'SMS broadcast '.$number,
				  'ToNumber'      => $number,
				  'TextBroadcastConfig' => array(
				    'Message' => $smstext));
				$broadcastId = $client->sendText($sendTextRequest);
				echo "New broadcast ID: $broadcastId\n";
		}	

		$chArray = array(
			"qid"=>$qid,
			"sms_id"=>0,
			"atDate"=>date('Y-m-d H:i:s'),
			"specEmail"=>$number,
			"phone"=>$number
			);
		$this->db->insert('sms_sended', $chArray);
	}

	public function sms_launch_block(){
		$qid = $this->input->post('qid');
		$data['quote'] = $this->quotes->get_quote_by_id_full($qid);
		$data['qid'] = $qid; 
		$this->load->view('admin/parts/smslauncher', $data);
	}

	// ----------- /SMS LAUNCHER

	public function dashboard(){
		$data = "";
		$data['cenceled'] = $data['quoted'] = $data['dispached'] = $data['ordered'] = 0;

		  $data['countQuotes'] = $this->quotes->get_count_quotes(0);

		  $data['quoted'] = $this->quotes->get_statistic_info(75, 0);
		  $data['ordered'] = $this->quotes->get_statistic_info(83, 0);
		  $data['dispached'] = $this->quotes->get_statistic_info(87, 0);
		  $data['cenceled'] = $this->quotes->get_statistic_info(91, 0);


		  $GsendStat = $this->quotes->getG_sent_statistic();	
		  $data['emailsSent'] = $GsendStat->countSent;
		  $data['emailsOpened'] = count($this->emails->get_all_opened_stat());
		  $data['emailsRecived'] = count($this->emails->get_all_recived_stat());	
		  $data['emailsWaiting'] = $GsendStat->countWaiting;	
	      if($data['emailsWaiting'] < 0) $data['emailsWaiting'] = 0;

		  $data['countNotices'] = $this->quotes->get_count_notices(0);	

		  $data['formRequestCount'] = $this->emails->get_forms_count(0);
		  $data['formInitCount'] = $this->emails->get_forms_count(1);
		  $data['formSheduleCount'] = $this->emails->get_forms_count(2);

		  $data['globalsum'] = $this->quotes->get_global_orders_sum();
		  $data['chargered'] = $this->quotes->get_chargered_sum();
		$this->load->view('admin/parts/dashboard', $data);
	}

/*
	public function parseLeadsMeble(){
		$addtoFolder = 94;
		$leadsM = file_get_contents('huinia2.csv');
		$leadsM = explode("\n", $leadsM);
		for($i = 1; $i<count($leadsM)-1; $i=$i+4){
			//echo $leadsM[$i]."|".$leadsM[$i+1]."|".$leadsM[$i+2]."<br/><br/>";
			$vals = explode(';', $leadsM[$i]);
			$vals1 = explode(';', $leadsM[$i+1]);
			$vals2 = explode(';', $leadsM[$i+2]);

			
			$name = $vals[4];
			$email = $vals[5];
			$phone = $vals1[4];
			$atDate = $vals[6];
			
			
			$this->load->model('quotes_model', 'quotes');

			$this->db->like('Email', '%'.trim($email).'%');
			$contacts = $this->db->get('users_contact')->result();

			if(count($contacts) > 0){
				$addtoFolder = 74;
				$contact = $contacts->id;
				//echo '-'.$email = $vals[5]."<br/><br/>";

			}


			$qid = 0;
			$chArray = array(
		 		'FirstName'=>$name,
		 		'SecondName'=>"",
		 		'Company'=>"",
		 		'Email'=>$email,
		 		'Phone'=>$phone,
		 		'Phone2'=>"",
		 		'Mobile'=>"",
		 		'Fax'=>"",
		 		'Addr_street'=>"",
		 		'Addr_city'=>"",
		 		'Addr_state'=>"",
		 		'Addr_zip'=>"",
		 		'Addr_country'=>""
		 		);

		 	$contact = $this->quotes->update_users_contact(0, $chArray);

		 	$from = $vals[9];
		 	$to = $vals[10];
		 	$dist = $vals[11];

		 	if(substr($from, -2) == "US"){
		 		
				$from = trim(substr($from, 0, -2));
		 	}

		 	if(substr($to, -2) == "US"){
		 		
				$to = trim(substr($to, 0, -2));
		 	}
		 	$t = explode(' ', $to);
		 	$f = explode(' ', $from);

	 		$j = $f;
	 		unset($j[count($j)-1]);
	 		unset($j[0]);
	 		echo $f[0]. "-".implode(' ', $j)."-".$f[count($f)-1]."<br/><br/>";

	 		$fromCity = implode(' ', $j);
	 		$fromState = $f[0];
	 		$fromZip = $f[count($f)-1];

	 		$j = $t;
	 		unset($j[count($j)-1]);
	 		unset($j[0]);

	 		$toCity = implode(' ', $j);
	 		$toState = $t[0];
	 		$toZip = $t[count($t)-1];


	 		//echo '-'.$fromCity;
	 		//echo '-'.$toCity."<br/><br/>";


		 	$chArray = array(
		 		'spart'=>$addtoFolder,
		 		'contact'=>$contact,
		 		'distFromCity'=>$fromCity,
		 		'distFromState'=>$fromState,
		 		'distFromZip'=>$fromZip,
		 		'distFromCountry'=>"",
		 		'distToCity'=>$toCity,
		 		'distToState'=>$toState,
		 		'distToZip'=>$toZip,
		 		'distToCountry'=>"",
		 		'arriveDate'=>date('Y-m-d', strtotime($atDate)),
		 		'price'=>0,
		 		'deposit'=>0,
		 		'carYear'=>"-",
		 		'carModel'=>"-",
		 		'carMake'=>"-",
		 		'carType'=>"-",
		 		'addDate'=>date('Y-m-d H:i:s'),
		 		'moveDate'=>date('Y-m-d H:i:s'),
		 		'vechinesRun'=>0,
		 		'shipVia'=>0,
		 		'shipperNote'=>$vals[5]."|".$vals[6]."|".$vals1[2]."|".$vals[12],
		 		'distance'=>$dist
		 		);	
			
		 	$qid = $this->quotes->update_quote_full($qid, $chArray);


		 	$this->quotes->recount_inside_parts($addtoFolder);
		 	//$this->quotes->email_send_action($qid, '0', "ADD LEAD FROM OUT", '-1');



		}
	}
*/
	function moveDublicates(){
		/*
		$dublicatesG = $this->db->query("SELECT `Email`, `id` FROM `users_contact` GROUP BY `Email` HAVING count(*)>1;")->result();
		//print_r($dublicates);
		
		foreach($dublicatesG as $dubleG){
			$dublicatesContacts = $this->db->where('Email', $dubleG->Email)->get('users_contact')->result();
			$contact = $dublicatesContacts[0]->id;
			foreach($dublicatesContacts as $duble){

				//echo $duble->id;
				$this->db->where('contact', $duble->id);
				$quotes = $this->db->get('quotes')->result();
				//print_r($quotes);
				foreach($quotes as $quote){
					$chArray = array('spart'=>95, 'contact'=>$contact);
					$this->db->where('id', $quote->id);
					$this->db->update('quotes', $chArray);

				}

				if($duble->id != $contact){
					$this->db->where('id', $duble->id);
					$this->db->delete('users_contact');
				}
			}

		}
		

		
		

		$dublicatesG = $this->db->query("SELECT `contact`, `id`, `spart` FROM `quotes` WHERE `spart`= 95 GROUP BY `contact` HAVING count(*)=1;")->result();
		foreach($dublicatesG as $item){
			$item->contact."<br/>";
			$chArray = array('spart'=>94);
			$this->db->where('id', $item->id);
			$this->db->update('quotes', $chArray);
		}

		*/
		$this->quotes->recount_inside_parts(95);
		$this->quotes->recount_inside_parts(94);
		$this->quotes->recount_inside_parts(93);
		$this->quotes->recount_inside_parts(92);
	}
/*
	function get_huinia(){
		$text = file_get_contents('huinia.csv');
		$text =  explode("\n", $text);
		$proebanie = 0;
		$this->load->model('quotes_model', 'quotes');
		for($i = 0; $i< count($text)-1;  $i=$i+4){
			$item = explode(';',$text[$i]);
			$item2 = explode(';', $text[$i+1]);
			if($item[2] == "") { 
				$proebanie ++;
				continue;
			}
			//echo $text[$i]."|".$text[$i+1]."|".$text[$i+2]."|".$text[$i+3]."<br/><br/>";	


			$name = $item[4];
			$email = $item[5];
			$phone = $item2[4];

			$qid = 0;
			$chArray = array(
		 		'FirstName'=>$name,
		 		'SecondName'=>"",
		 		'Company'=>"",
		 		'Email'=>$email,
		 		'Phone'=>$phone,
		 		'Phone2'=>"",
		 		'Mobile'=>"",
		 		'Fax'=>"",
		 		'Addr_street'=>"",
		 		'Addr_city'=>"",
		 		'Addr_state'=>"",
		 		'Addr_zip'=>"",
		 		'Addr_country'=>""
		 		);

		 	$contact = $this->quotes->update_users_contact(0, $chArray);

		 	$atDate = $item[6];

		 	$from = $item[9];
		 	$to = $item[10];

		 	if(substr($from, -2) == "US"){
		 		
				$from = trim(substr($from, 0, -2));
		 	}

		 	if(substr($to, -2) == "US"){
		 		
				$to = trim(substr($to, 0, -2));
		 	}
		 	$t = explode(' ', $to);
		 	$f = explode(' ', $from);

	 		$j = $f;
	 		unset($j[count($j)-1]);
	 		unset($j[0]);
	 		echo $f[0]. "-".implode(' ', $j)."-".$f[count($f)-1]."<br/><br/>";

	 		$fromCity = implode(' ', $j);
	 		$fromState = $f[0];
	 		$fromZip = $f[count($f)-1];

	 		$j = $t;
	 		unset($j[count($j)-1]);
	 		unset($j[0]);

	 		$toCity = implode(' ', $j);
	 		$toState = $t[0];
	 		$toZip = $t[count($t)-1];

	 		//echo $t[0]. "-".implode(' ', $j)."-".$t[count($t)-1]."<br/><br/>";
	
		 	//echo $from."|".$to."<br/>";

		 	
		 	
		 	//$fromState
		 	//$fromZip

		 	//$toCity
		 //	$toState
		 	//$toZip


		 	$chArray = array(
		 		'spart'=>93,
		 		'contact'=>$contact,
		 		'distFromCity'=>$fromCity,
		 		'distFromState'=>$fromState,
		 		'distFromZip'=>$fromZip,
		 		'distFromCountry'=>"",
		 		'distToCity'=>$toCity,
		 		'distToState'=>$toState,
		 		'distToZip'=>$toZip,
		 		'distToCountry'=>"",
		 		'arriveDate'=>date('Y-m-d', strtotime($atDate)),
		 		'price'=>0,
		 		'deposit'=>0,
		 		'carYear'=>"-",
		 		'carModel'=>"-",
		 		'carMake'=>"-",
		 		'carType'=>"-",
		 		'addDate'=>date('Y-m-d H:i:s'),
		 		'moveDate'=>date('Y-m-d H:i:s'),
		 		'vechinesRun'=>0,
		 		'shipVia'=>0,
		 		'shipperNote'=>$item[12]."|".$item2[12]."|".$item[13]
		 		);	
			
		 	$qid = $this->quotes->update_quote_full($qid, $chArray);


		 	$this->quotes->recount_inside_parts(93);
		 	//$this->quotes->email_send_action($qid, '0', "ADD LEAD FROM OUT", '-1');

		}
		echo $proebanie;
	}

*/
/*
	function get_Jtracker_quotes(){
		
		$text = file_get_contents('importLeads/017.csv');
		$text = str_replace("&amp;", "", $text);
		$text =  explode("\n", $text);
		$proebanie = 0;
		$this->load->model('quotes_model', 'quotes');
		$i = 0;
		foreach($text as $item){
			$folderId = 96;
			$pos2 = strpos($item, "Status: Cancelled");

			$pos = strpos($item, "Status:");
			if($pos ==  false){
				$i++;
				continue;

			}
			//echo $item."<br/><br/>"; 
			//echo $text[$i]."|".$text[$i+1]."|".$text[$i+2]."|".$text[$i+3]."<br/><br/>";

			$item = explode(';',$text[$i]);
			$item2 = explode(';', $text[$i+1]);
			$item3 = explode(';', $text[$i+2]);
			if(!($pos ==  false)){
				$item4 = explode(';', $text[$i+3]);
				$i++;
			}else{

			}
		
		//echo implode("|",$item);
			
			$name = $item[4];
			$email = $item3[4];
			$phone = $item2[4];

			$this->db->where('Email', $email);
			$contactE = $this->db->get('users_contact')->result();
			if(count($contactE) == 0){
				
				$qid = 0;
				$chArray = array(
			 		'FirstName'=>$name,
			 		'SecondName'=>"",
			 		'Company'=>"",
			 		'Email'=>$email,
			 		'Phone'=>$phone,
			 		'Phone2'=>"",
			 		'Mobile'=>"",
			 		'Fax'=>"",
			 		'Addr_street'=>"",
			 		'Addr_city'=>"",
			 		'Addr_state'=>"",
			 		'Addr_zip'=>"",
			 		'Addr_country'=>""
			 		);

			 	$contact = $this->quotes->update_users_contact(0, $chArray);
			}else{
				$folderId = 79;
				$contact = $contactE[0]->id;
			}

		 	$atDate = $item[1];

		 	$from = $item[6];
		 	$to = $item2[6];

		 	
		 	
		 	$city = explode(',', $from);
		 	$fromCity = trim($city[0]);
		 	$state = explode(' ', str_replace("/", '', trim($city[1])));
		 	$fromState = trim($state[0]);
		 	if(isset($state[1])){
		 		$fromZip = trim($state[1]);
		 	}else{
		 		$fromZip = 0;
		 	}

		 	$city = explode(',', $to);
		 	$toCity = trim($city[0]);
		 	$state = explode(' ', str_replace("/", '', trim($city[1])));
		 	$toState = trim($state[0]);
		 	if(isset($state[1])){
		 		$toZip = trim($state[1]);
		 	}else{
		 		$toZip = 0;
		 	}

		 	$price = trim(str_replace('$', '', str_replace(',', '', $item[7])));
		 	$pos3 = strpos($item2[8], "Pay:");
		 	if($pos3 == false){
		 	 	$pos5 = strpos($item4[8], "Pay:");
		 	 	if(!($pos5 == false)){
		 	 		$carrierPay = explode(':', $item4[8]);
		 			$carrierPay = trim($carrierPay[1]);
		 	 	}
		 	}else{
		 		$carrierPay = explode(':', $item2[8]);
		 		$carrierPay = trim($carrierPay[1]);
		 	}


		 	
		 	$carrierPay = trim(substr(trim(str_replace('$', '', str_replace(',', '',$carrierPay))), 0, -3));

		 	
	 		$carrierPay = preg_replace('~[^0-9]+~','',$carrierPay); 

	 		 $carP = $item[5];
	 		 $carAr = explode(' ', $carP);

	 		 if($carAr[0] !="Multiple"){
	 		 	$carYear = trim($carAr[0]);
	 		 	$carMake = trim($carAr[1]);
	 		 	$carModel = trim($carAr[2]);
	 		 	if(isset($carAr[3])){
	 		 		$carModel .= " ".trim($carAr[3]);
	 		 	}

	 		 }else{
	 		 	$carYear = 0;
	 		 	$carMake = "-";
	 		 	$carModel = $item[5];
	 		 }

	 		 //echo $carYear."|".$carMake."|".$carModel."<br/><br/>";
	 		 
	 	

	 		//$price  = preg_replace('~[^0-9]+~','',$price); 
		 	$chArray = array(
		 		'spart'=>$folderId,
		 		'contact'=>$contact,
		 		'distFromCity'=>$fromCity,
		 		'distFromState'=>$fromState,
		 		'distFromZip'=>$fromZip,
		 		'distFromCountry'=>"",
		 		'distToCity'=>$toCity,
		 		'distToState'=>$toState,
		 		'distToZip'=>$toZip,
		 		'distToCountry'=>"",
		 		'arriveDate'=>date('Y-m-d', strtotime($atDate)),
		 		'price'=>$price,
		 		'deposit'=>0,
		 		'carYear'=>$carYear,
		 		'carModel'=>$carModel,
		 		'carMake'=>$carMake,
		 		'carType'=>"-",
		 		'addDate'=>date('Y-m-d H:i:s'),
		 		'moveDate'=>date('Y-m-d H:i:s'),
		 		'vechinesRun'=>0,
		 		'shipVia'=>0,
		 		'CarrierPay'=>intval($carrierPay),
		 		'shipperNote'=>'-'
		 		);	

		 	//print_r($chArray);

		 	//echo "<br/><br/>";
			
		 	$qid = $this->quotes->update_quote_full($qid, $chArray);


		 	$this->quotes->recount_inside_parts($folderId);
		 	//$this->quotes->email_send_action($qid, '0', "ADD LEAD FROM OUT", '-1');

		}
		//echo $proebanie;

	}
*/

	 //[ANCADD]
    public function get_sms_change(){
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

        $data['notSave'] = 0;
        $data['user'] = $user = $this->user;

        $data['hash'] = time();
        $data['smsid']= $smsid = $this->input->post('smsid');
        if($smsid != 0){
            $data['data'] = $this->emails->get_sms_by_id($smsid);
        }else{
            if((!isset($user->Fname))||($user->Fname == "")) $user->Fname = "I HAVE NO NAME IN MY SETINGS";
            $addArray = array(
                'name'=>"Onced send (not save as template)",
                //'subject'=>"",
                'text'=>"",
                'etype'=>1,
                //'AutomatedTo'=>$AutomatedTo,
                'OnlyInworkTime'=>0,
                //	'cc'=>$Ecc,
                'bcc'=>"",
                //'efrom'=>$user->email,
                //'enfrom'=>$user->Fname,
                //'replyto'=>$user->email

            );
            $this->db->insert('sms', $addArray);
            $eid = $data['eid'] = $this->db->insert_id();
            $data['data'] = $this->emails->get_sms_by_id($eid);
            $data['notSave'] = 1;
        }
        $this->load->view('admin/perscab/smsEdit', $data);

    }

// [ANCADD]
    public function deletesms($id){
        $this->db->delete('sms', array('id'=>$id));
        redirect(base_url()."admin/", 'refresh');
    }

    // [ANCADD]
    public function addchsms(){


        $Ename = $this->input->post('Sname');

        //$Efrom = $this->input->post('Efrom');
        //$Enfrom = $this->input->post('Enfrom');
        //$Ereplyto = $this->input->post('Ereplyto');
        //	$Ecc = $this->input->post('Ecc');
        //$Ebcc = $this->input->post('Ebcc');

        //$Esubject = $this->input->post('Esubject');
        $Etext = $this->input->post('Etext2');
        $Eid = $this->input->post('Eid');
        //$AutomatedTo = $this->input->post('AutomatedTo');
        $OnlyInworkTime = $this->input->post('OnlyInworkTime');
        $Etype = $this->input->post('Etype');

        if(!isset($Etype)) $Etype = 0;

        $sendToDriver = $this->input->post('sendToDriver');
        if((isset($sendToDriver))&&($sendToDriver == "on")) {
            $sendToDriver = 1;
        }else{
            $sendToDriver = 0;
        }


        //$Ename = addslashes($Ename);
        //$Esubject = addslashes($Esubject);
        //$Etext = addslashes($Etext);
        if(!($Eid > 0)){
            $addArray = array(
                'name'=>$Ename,
                //'subject'=>$Esubject,
                'text'=>$Etext,
                'etype'=>$Etype,
                'sendToDriver'=>$sendToDriver,
                'OnlyInworkTime'=>$OnlyInworkTime,
                //	'cc'=>$Ecc,
                //'bcc'=>$Ebcc,
                //'efrom'=>$Efrom,
                //'enfrom'=>$Enfrom,
                //'replyto'=>$Ereplyto

            );
            $this->db->insert('sms',$addArray);
            echo $this->db->insert_id();
        }else{
            $chArray = array(
                'name'=>$Ename,
                //'subject'=>$Esubject,
                'text'=>$Etext,
                'sendToDriver'=>$sendToDriver,
                //'AutomatedTo'=>$AutomatedTo,
                'OnlyInworkTime'=>$OnlyInworkTime,
                //	'cc'=>$Ecc,
                //'bcc'=>$Ebcc,
                //'efrom'=>$Efrom,
                //'replyto'=>$Ereplyto,
                //'enfrom'=>$Enfrom);
            );

            $this->db->where('id',$Eid);
            $this->db->update('sms', $chArray);

        }

        //echo $this->db->last_query();

        redirect(base_url()."admin/", 'refresh');

    }



}