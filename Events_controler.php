<?php 
	class Events_controler extends CI_Controller {
	    public function __construct() {
	    	parent::__construct();
	    	$this->load->model("UserManagement_model");
	    	$this->load->model("UserConnection_model");	
	    	$this->load->model("EventsType_model");
	    	$this->load->model("Events_model");
	    	$this->load->model("EventsData_model");	  
	    	$this->load->model("UserPrefrences_model");	  	
	    	$this->load->model("CommedianList_model");	  	
	    	$this->load->model("PerformerMaster_model");	  	
	    }

	    /*
	    	@Retrive Data of Sports events
	    	@Parameter : My local, ISO local, ISO Nation 
	    */
	    public function displaySportsEvents(){
	   		$_POST = jsonRequestPara();
	   		$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('type', 'Type', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	        	$type = $requestParams['type'];
	        	$event_type = !empty($requestParams['event_type'])?$requestParams['event_type']:"sport";

	        	$date = !empty($requestParams['date'])?$requestParams['date']:"";

	        	$page = !empty($requestParams['page'])?$requestParams['page']:1;
				$limit = "20";
	            $offset = ($page - 1)*$limit;
	            $limit_offset = $limit.','.$offset;


	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){

	        			$SportsData = array();
	        			$FinalArr = array();
	        			$conected_count = 0;
	        			$connected_user = array();
	        			
	        			/*
	        				latitude, longitude
	        			*/
	        				
        				$latitude = !empty($IsUser[0]->latitude)?$IsUser[0]->latitude:"";
						$longitude = !empty($IsUser[0]->longitude)?$IsUser[0]->longitude:"";
						$zipcode = !empty($IsUser[0]->zip_code)?$IsUser[0]->zip_code:"";

						/*$SelectData = "events_data.id as eventid, events_data.seatgeek_event_id, events_data.performer1name as performer1, events_data.performer2name as performer2, events_type.name as event_name, events_data.ticket_link, events_data.eventname, events_data.venue,events_data.eventdate, events_data.performer1id, events_data.performer2id, events_data.sub_event_id";*/

						$SelectData = "events_data.id as eventid, events_data.seatgeek_event_id, events_data.performer1name as performer1, events_data.performer2name as performer2, events_type.name as event_name, events_data.ticket_link, events_data.eventname, events_data.venue,events_data.eventdate, events_data.performer1id, events_data.performer2id, events_data.sub_event_id,postalcode,((ACOS(SIN($latitude * PI() / 180) * SIN(`latitude` * PI() / 180) + COS($latitude * PI() / 180) * COS(`latitude` * PI() / 180) * COS(($longitude - `longitude`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance";

						$join_arr[] = array(
			                "table_name" => "events_sub_type",
			                "cond" => "events_sub_type.id = events_data.sub_event_id",
			                "type" => "inner"
		            	);

						$join_arr[] = array(
			                "table_name" => "events_type",
			                "cond" => "events_type.id = events_sub_type.event_type_id",
			                "type" => "inner"
		            	);


	        			/* Here Check Type and based on that return the data */
	        			if($type == "iso_local"){
	        				$having = "distance <= 50";

							/*if(empty($date)){
								$WhereSports = array(
									"postalcode" => $zipcode,
								);
							}else{
								$WhereSports = array(
									"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}*/

							if(empty($date)){
								$WhereSports = array(
									//"postalcode" => $zipcode,
								);
							}else{
								$WhereSports = array(
									//"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}

							
	        			}elseif($type == "iso_nation"){
	        				
	        				/*if(empty($date)){
								$WhereSports = array(
									"postalcode" => $zipcode,
								);

							}else{
								
								$WhereSports = array(
									"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}*/

							$having = "distance >= 50";
	        				
	        				if(empty($date)){
								$WhereSports = array(
									//"postalcode" => $zipcode,
								);

							}else{
								
								$WhereSports = array(
									//"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}

	        			}else{

	        				/*Default it will set as a My Local*/
							/*Check the same Zipcode exists or not and retrive the all data with the same Zip Code*/
							/*if(empty($date)){
								$WhereSports = array(
									"postalcode" => $zipcode,
								);
							}else{
								$WhereSports = array(
									"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}*/

							$having = "distance <= 50";
	        				/*Default it will set as a My Local*/
							/*Check the same Zipcode exists or not and retrive the all data with the same Zip Code*/
							if(empty($date)){
								$WhereSports = array(
									//"postalcode" => $zipcode,
								);
							}else{
								$WhereSports = array(
									//"postalcode" => $zipcode,
									"eventdate <=" => $date,
								);
							}
	        			}

	        			//echo $event_type;die;

	        			if($event_type == 'comedian'){
	        				$join_arr = array();
	        				
	        				$join_arr[] = array(
				                "table_name" => "comedian",
				                "cond" => "comedian.id = events_data.sub_event_id",
				                "type" => "inner"
		            		);

		            		/*$SelectData = "events_data.id as eventid, events_data.seatgeek_event_id, events_data.performer1name as performer1, events_data.performer2name as performer2, comedian.name as event_name, events_data.ticket_link, events_data.eventname, events_data.venue,events_data.eventdate, events_data.performer1id, events_data.performer2id,events_data.sub_event_id";*/

		            		$SelectData = "events_data.id as eventid, events_data.seatgeek_event_id, events_data.performer1name as performer1, events_data.performer2name as performer2, comedian.name as event_name, events_data.ticket_link, events_data.eventname, events_data.venue,events_data.eventdate, events_data.performer1id, events_data.performer2id,postalcode,events_data.sub_event_id,((ACOS(SIN($latitude * PI() / 180) * SIN(`latitude` * PI() / 180) + COS($latitude * PI() / 180) * COS(`latitude` * PI() / 180) * COS(($longitude - `longitude`) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS distance";

	        				$WhereSports['events_data.event_type_id'] = 3;
	            		} elseif ($event_type == 'music') {
	            			$WhereSports['events_data.event_type_id'] = 4;
	            		} elseif ($event_type == 'concert') {
	            			$WhereSports['events_data.event_type_id'] = 2;
	            		}else{
	            			$WhereSports['events_data.event_type_id'] = 1;
	            		}

	            		$groupBy = "events_data.performer1id, events_data.performer2id";
	        			$SportsDataCount = $this->EventsData_model->getAnyData($WhereSports,$SelectData,"","",$join_arr, $groupBy, $having);
	        			$Totalcount = count($SportsDataCount);
	        			$SportsData = $this->EventsData_model->getAnyData($WhereSports,$SelectData,"",$limit_offset,$join_arr, $groupBy, $having);
	        			#pr($SportsData);die;
	        			if(!empty($SportsData)){

	        				foreach ($SportsData as $key => $value) {
	        					
	        					$performer1 = $value->performer1;
	        					$performer2 = $value->performer2;


	        					$TeamId1 = array();
								$TeamId2 = array();
								$finTeam = array();

								$TeamId1count = $TeamId1count = 0;

	        					/*Check the Preferences are there or not*/
	        					$performer1id = $value->performer1id;
	        					$performer2id = $value->performer2id;

	        					if($event_type == "comedian"){
	        						$performer1id = $value->sub_event_id;
									$performer2id = $value->sub_event_id;
	        					}

        						if(!empty($value->performer1id) && $value->performer1id != 0){
        							$TeamId1 = getUserZipcodeData($zipcode, $performer1id, $user_id, $event_type);
        						}

								if(!empty($value->performer2id) && $value->performer2id != 0){
        							$TeamId2 = getUserZipcodeData($zipcode, $performer2id, $user_id, $event_type);
        						}	

        						$TeamId1count = count($TeamId1);
        						$TeamId2count = count($TeamId2);

	        					$finTeam['performer1'] =array(
	        						"teamname" => !empty($performer1)?$performer1:"tbd",
	        						"teamid" => !empty($performer1id)?$performer1id:"0",
	        						"connected_user" => $TeamId1,
	        						"conected_count" => $TeamId1count, 
	        					);
	        					
	        					$finTeam['performer2'] =array(
	        						"teamname" => !empty($performer2)?$performer2:"tbd",
	        						"teamid" => !empty($performer2id)?$performer2id:"0",
	        						"connected_user" => $TeamId2,
	        						"conected_count" => $TeamId2count,
	        					);

	        					/*Get the same events details from the DB*/
	        					$WhereSports['performer1id'] = $value->performer1id;
	        					$WhereSports['performer2id'] = $value->performer2id;

	        					/*Events details with same Details of events*/
	        					$PerformedData = $this->EventsData_model->getAnyData($WhereSports,$SelectData,"","",$join_arr);
	        					$EventsFinalData = array();
	        					
	        					if(!empty($PerformedData)){
	        						foreach ($PerformedData as $pkey => $pval) {
	        							$EventsFinalData[] = array(
	        								"id" => !empty($pval->eventid)?$pval->eventid:"",
			        						"seatgeek_id" => !empty($pval->seatgeek_event_id)?$pval->seatgeek_event_id:"",
			        						"ticket_link" => !empty($pval->ticket_link)?$pval->ticket_link:"",
			        						"eventname" => !empty($pval->eventname)?$pval->eventname:"",
			        						"venue" => !empty($pval->venue)?$pval->venue:"",
			        						"eventdate" => !empty($pval->eventdate)?$pval->eventdate:"",
	        							);
	        						}
	        					}

	        					$FinalArr[] = array(

	        						"sportsname" => !empty($value->event_name)?$value->event_name:"",
	        						"performer" => !empty($finTeam)?$finTeam:array(),
	        						"evnetsdetails" => $EventsFinalData

	        					);
	        				}

	        				$response['status'] = 1;
			           	 	$response['message'] = 'Events data';
			           	 	$response['total_count'] = $Totalcount;
			           	 	$response['data'] = $FinalArr;

	        			}else{
		        			$response['status'] = 0;
			           	 	$response['message'] = 'No events found nearest to you';
			           	 	$response['data'] = array();
	        			}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	    	}
	    	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }
	    /*
	    	Display Sports List with the Count of added team by user
	    */
	    public function displaySportsList(){
	    	$_POST = jsonRequestPara();
	   		$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			
	        			/*Get the sports data and dispaly with team count*/
	        			$WhereSportsData = array(
	        				//"status" => 1,
	        				"event_id" => 1
	        			);
	        			$SelectData = "name, events_parent_id, id";
	        			$SportsData = $this->EventsType_model->getAnyData($WhereSportsData,$SelectData);
	        			$FinalArr = array();
	        			if(!empty($SportsData)){
	        				/*get a list of user's selected teams*/
	        				foreach ($SportsData as $key => $value) {
	        					$WhereUsr = array(
	        						"user_id" => $user_id,
	        						"sports_id" => $value->id
 	        					);
	        					$selectedata = "count(id) as counter";
 	        					$userData = $this->UserPrefrences_model->getAnyData($WhereUsr, $selectedata);
 	        					$counter = 0;
 	        					if(!empty($userData)){
 	        						$counter = $userData[0]->counter;
 	        					}

 	        					$FinalArr[$key]['sports_name'] = $value->name;
 	        					$FinalArr[$key]['sports_id'] = $value->id;
 	        					$FinalArr[$key]['added_team'] = $counter;
	
	        				}
 	        				$response['status'] = 1;
			           	 	$response['message'] = 'Sports data';
			           	 	$response['data'] = $FinalArr;
	        			}else{
		        			$response['status'] = 0;
			           	 	$response['message'] = 'No data found';
			           	 	$response['data'] = array();
	        			}
	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*Add Sports Team list*/
	    public function AddSportsTeam(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');
	    	$this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$prefrence_id = $requestParams['prefrence_id'];

						$sports_id = $requestParams['sports_id'];
						$type = !empty($requestParams['type'])?$requestParams['type']:"pro";


						$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {
							
								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => $sports_id,
									"user_id" => $user_id
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								if(empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$AddPrefrenceArr = array(
										"user_id" => $user_id,
										"prefrence_id" => $value,
										"sports_id" => $sports_id, 
										"created_at" => date("Y-m-d H:i:s")
									);

									$PreferencesData = $this->UserPrefrences_model->insertData($AddPrefrenceArr);
								}
							}

							/*Retrive the added Sports from the prefrences list*/
							$AddedSportsTeam = $this->add_deleteSportsTeam($sports_id, $type, $user_id);
							$response['status'] = 1;
			           	 	$response['message'] = 'Team added successfully';
			           	 	$response['data'] = $AddedSportsTeam;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No team added, please try again';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*Delete added sports team from list*/
	   	public function DeleteSportsTeam(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');
	    	$this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$prefrence_id = $requestParams['prefrence_id'];
	        			$sports_id = $requestParams['sports_id'];

	        			$type = !empty($requestParams['type'])?$requestParams['type']:"pro";


	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {

								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => $sports_id,
									"user_id" => $user_id,
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								if(!empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$WhereDeleteTeam = array(
										"id" => $PreferencesData[0]->id,
									);

									$PreferencesData = $this->UserPrefrences_model->delete($WhereDeleteTeam);
								}/*else{
			        			$response['status'] = 0;
				           	 	$response['message'] = 'Team already deleted from your list';
				           	 	$response['data'] = array();
								}*/
							}

							$AddedSportsTeam = $this->add_deleteSportsTeam($sports_id, $type, $user_id);


							$response['status'] = 1;
			           	 	$response['message'] = 'Team deleted successfully';
			           	 	$response['data'] = $AddedSportsTeam;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No team deleted';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


	    /*Get Sports Team list*/
	  //  	public function getSportsTeams(){
	  //   	$_POST = jsonRequestPara();

	  //   	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	  //   	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	  //   	$this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
	  //   	$this->form_validation->set_rules('type', 'Type', 'trim|required');
	  //   	if ($this->form_validation->run() === FALSE) {
	  //           $response['status'] = 0;
	  //           $response['message'] = 'Please enter all required fields';
	  //       } else {

	  //   	$requestParams = $this->input->post();
	    		
   //  		$user_id = $requestParams['user_id'];
   //      	$device_token = $requestParams['device_token'];
   //      	$sports_id = $requestParams['sports_id'];
   //      	$type = $requestParams['type'];

	  //       /*Pagination*/
			// $page = !empty($requestParams['page'])?$requestParams['page']:1;
			// $limit = "20";
	  //       $offset = ($page - 1)*$limit;
	  //       $limit_offset = $limit.','.$offset;

	  //       $search = !empty($requestParams['search'])?$requestParams['search']:"";

	  //      	$is_prefrence = !empty($requestParams['is_prefrence'])?$requestParams['is_prefrence']:"no";


   //      	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

   //      	if(!empty($IsUser)){
   //      		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){

   //  				$join_arr[] = array(
	  //   				"table_name" => "events_sub_type",
	  //   				"cond" => "events_sub_type.id = performer_master.sub_event_id",	
	  //   				"type" => "inner"
   //  				);

			// 		$join_arr[] = array(
	  //   				"table_name" => "events_type",
	  //   				"cond" => "events_sub_type.event_type_id = events_type.id",	
	  //   				"type" => "inner"
   //  				);


   //  				$WhereSports = array(
	  //       			"performer_master.event_type_id" => 1,
	  //       			"events_sub_type.event_type_id" => $sports_id,
	  //       			"events_sub_type.type" => $type
	  //       		);


	  //       		if(!empty($search)){
	  //       			$WhereSports['performer_master.name LIKE'] = "%".$search."%";
	  //       			$limit_offset = "";
	  //       		}

			// 		if($is_prefrence == "yes"){
   //      				$join_arr[] = array(
		 //    				"table_name" => "user_prefrences",
		 //    				"cond" => " performer_master.performer_id = user_prefrences.prefrence_id AND user_prefrences.user_id = ".$user_id,
		 //    				"type" => "INNER"
   //      				);
        			
   //  				}else{
   //  					$join_arr[] = array(
		 //    				"table_name" => "user_prefrences",
		 //    				"cond" => " user_prefrences.prefrence_id = performer_master.performer_id AND user_prefrences.user_id = ".$user_id,
		 //    				"type" => "LEFT"
   //      				);

   //      				$WhereSports["user_prefrences.prefrence_id"] = NULL;
   //  				}



   //  				$selectData = "performer_master.name as performer, performer_master.performer_id, events_sub_type.name";
 
   //  				$groupBy = "performer_master.performer_id";

   //  				$orderBy = "events_sub_type.event_type_id ASC";

			// 		$PrefrenceData = $this->PerformerMaster_model->getAnyData($WhereSports, $selectData, $orderBy, $limit_offset, $join_arr,$groupBy);

			// 		#pr($PrefrenceData);die;
			// 		//echo $this->db->last_query();
			// 		$FinalData = $teamData = array();
			// 		$lastTeam = "";

			// 		if(!empty($PrefrenceData)){
			// 			foreach ($PrefrenceData as $key => $value) {
			// 				$TeamName = $value->name;

			// 				if($TeamName != $lastTeam){
			// 					$teamData = $TeamName;				
			// 				}
							
			// 				$teamData[] = array(
			// 					"prefrenceID" => $value->performer_id,
			// 	        		"preference_name" => $value->performer,
			// 	        		"preference_added" => $is_prefrence
			// 				);

							
			// 				//$FinalData['team'] = $teamData;

			// 				$lastTeam = $TeamName;
			// 			}

			// 			$DataResponse = $teamData;

			// 			$response['status'] = 1;
			//            	$response['message'] = "Sport's events data";
			//            	$response['data'] = $DataResponse;
			// 		}else{
			// 			$response['status'] = 0;
			//            	$response['message'] = 'No data found';
			//            	$response['data'] = $FinalData;
			// 		}
        			
   //      		}else{
   //  				$response['status'] = 0;
	  //          	 	$response['message'] = 'User is inactive or deleted, please try again';
	  //          	 	$response['data'] = array();
   //      		}
	  //       }else{
   //      		$response['status'] = 999;
	  //           $response['message'] = 'Token mismatch, please try again';
	  //          	$response['data'] = array();
	  //       }

	  //   	}

	  //       $JSONresponse=J_endecode($response,"jencode");
	  //       echo $JSONresponse;
	  //   }


	     public function getSportsTeams(){
	    	$_POST = jsonRequestPara();

	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
	    	$this->form_validation->set_rules('type', 'Type', 'trim|required');
	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {

	    	$requestParams = $this->input->post();
	    		
    		$user_id = $requestParams['user_id'];
        	$device_token = $requestParams['device_token'];
        	$sports_id = $requestParams['sports_id'];
        	$type = $requestParams['type'];

	        /*Pagination*/
			$page = !empty($requestParams['page'])?$requestParams['page']:1;
			$limit = "20";
	        $offset = ($page - 1)*$limit;
	        $limit_offset = $limit.','.$offset;

	        $search = !empty($requestParams['search'])?$requestParams['search']:"";

	       	$is_prefrence = !empty($requestParams['is_prefrence'])?$requestParams['is_prefrence']:"no";


        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

        	if(!empty($IsUser)){
        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
        			/*get a team name based on sports name*/
        			$WhereSportsData = array(
        				"events_type.id" => $sports_id,
        				"events_sub_type.status" => 1,
        				"events_sub_type.type" => $type
        			);
        			#pr($WhereSportsData);die;
        			$join_arr[] = array(
        				"table_name" => "events_sub_type",
        				"cond" => "events_sub_type.event_type_id = events_type.id",	
        				"type" => "inner"
        			);
        			
        			$SportsEventsData = $this->EventsType_model->getAnyData($WhereSportsData,"","","",$join_arr);
        			#pr($SportsEventsData);die;
        			$FinalData = array();
					$FinalArrData = array();
        			if(!empty($SportsEventsData)){
        				$i = 0;
        				/*Get a Team details based on the purticular sub type of sports*/
        				#pr($SportsEventsData);die;
        				foreach ($SportsEventsData as $key => $value) {
        					$WhereEventsData = array(
        						"sub_event_id" => $value->id,
        					);

        					if(!empty($search)){

		        				$WhereEventsData = "";

		        				$WhereEventsData = "sub_event_id = ".$value->id." AND (performer1name LIKE '%".$search."%')";

		        				//$limit_offset = "";
		        			}

        					$groupBy = "performer1id";
        					$SelectData = "performer1id,performer1name";
        					$Performer1EventsData = $this->EventsData_model->getAnyData($WhereEventsData,$SelectData,"","","",$groupBy);

        					if(!empty($search)){

		        				$WhereEventsData = "";

		        				$WhereEventsData = "sub_event_id = ".$value->id." AND (performer2name LIKE '%".$search."%')";

		        				//$limit_offset = "";
		        			}

        					$groupBy2 = "performer2id";
        					$SelctedData2 = "performer2id,performer2name";
        					$Performer2EventsData = $this->EventsData_model->getAnyData($WhereEventsData,$SelctedData2,"","","",$groupBy2);

        					$FinalArr1 = array();
							$FinalArr2 = array();
        					/*Get the team list based on prefrenceID 1*/
        					$k=0;

        					$FinalArrMusical = array();

        					if(!empty($Performer1EventsData)){
        						
        						foreach ($Performer1EventsData as $pkey => $pvalue) {
		        					
		        					/*Check The user alreadye selcted this team in preference or not*/
		        					$WherePrefere = array(
		        						"user_id" => $user_id,
		        						"prefrence_id" => $pvalue->performer1id
		        					);

		        					$selectUser = "id";

		        					$userDataPref = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

		        					$Connected = "no";
		        					if(!empty($userDataPref)){
		        						$Connected = "yes";
		        					}

		        					if(!empty($pvalue->performer1id) && !empty($pvalue->performer1name)){


		        						if($is_prefrence != "yes"){
			        						if($Connected == "no"){
					        					$FinalArr1[$k] = array(
					        						"prefrenceID" => $pvalue->performer1id,
				        							"preference_name" => $pvalue->performer1name,
				        							"preference_added" => $Connected
					        					);
					        				}
					        			}else{
					        				if($Connected == "yes"){
					        					$FinalArr1[$k] = array(
					        						"prefrenceID" => $pvalue->performer1id,
				        							"preference_name" => $pvalue->performer1name,
				        							"preference_added" => $Connected
					        					);
					        				}
					        			}

		        						$k++;
	        						}
        						}

        					}

        					/*Get the team list based on prefrenceID 2*/
        					if(!empty($Performer2EventsData)){
        						
        						foreach ($Performer2EventsData as $pkey => $pvalue) {
		        					
		        					/*Check The user alreadye selcted this team in preference or not*/
		        					$WherePrefere = array(
		        						"user_id" => $user_id,
		        						"prefrence_id" => $pvalue->performer2id
		        					);

		        					$selectUser = "id";

		        					$userDataPref = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

		        					$Connected = "no";
		        					if(!empty($userDataPref)){
		        						$Connected = "yes";
		        					}

		        					if(!empty($pvalue->performer2id) && !empty($pvalue->performer2name)){

		        						if($is_prefrence != "yes"){
			        						if($Connected == "no"){
					        					$FinalArr1[$k] = array(
					        						"prefrenceID" => $pvalue->performer2id,
				        							"preference_name" => $pvalue->performer2name,
				        							"preference_added" => $Connected
					        					);
					        				}
					        			}else{
					        				if($Connected == "yes"){
					        					$FinalArr1[$k] = array(
					        						"prefrenceID" => $pvalue->performer2id,
				        							"preference_name" => $pvalue->performer2name,
				        							"preference_added" => $Connected
					        					);
					        				}
					        			}
		        						
		        						$k++;	
	        						}
        						}
        					}
        					#pr($FinalArr2);die;
        					$FinalData = array_merge($FinalArr1,$FinalArr2);
        					$check = array_values(array_unique($FinalData, SORT_REGULAR));
/*
        					if(!empty($page)){
								if($page == 1){
									$start = 0;	
									$limit = ($start + 20);
									$FinalArrMusical = array_slice($check, $start, $limit);
								}else{
									$start = "-".$page * 20;
									$limit = ($start + 20);	

									$FinalArrMusical = array_slice($check, $start, $limit);
								}
    						}*/


        					$FinalArrData[$i] = array(
        						"title" => !empty($value->name)?$value->name:"",
        						"teams" => !empty($check)?$check:array()
        					);

        					$FinalData = $check = "";
        					$i++;
        				}

	        			$response['status'] = 1;
		           	 	$response['message'] = "Sports data";
		           	 	$response['data'] = $FinalArrData;
        			}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'No data found';
		           	 	$response['data'] = array();
        			}
        		}else{
    				$response['status'] = 0;
	           	 	$response['message'] = 'User is inactive or deleted, please try again';
	           	 	$response['data'] = array();
        		}
	        }else{
        		$response['status'] = 999;
	            $response['message'] = 'Token mismatch, please try again';
	           	$response['data'] = array();
	        }

	    	}

	        $JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }



	    /*COMEDIAN MANAGEMENT*/
	    /*Get a Comedian list*/
	    public function getComedianList(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];

		        /*Pagination*/
				$page = !empty($requestParams['page'])?$requestParams['page']:1;
				$limit = "20";
		        $offset = ($page - 1)*$limit;
		        $limit_offset = $limit.','.$offset;

		        $search = !empty($requestParams['search'])?$requestParams['search']:"";

		       	$is_prefrence = !empty($requestParams['is_prefrence'])?$requestParams['is_prefrence']:"no";

	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			
	        			/*Get a list of comedain*/
	        			$WhereCom = array(
	        				"comedian.status" => 1,
	        			);	

	        			if(!empty($search)){
	        				$WhereCom = "";
							$WhereCom = array(
								"name LIKE" => "%". $search ."%",
								"status" => 1,
							);
							
							$limit_offset = "";        				
	        			}

	        			if($is_prefrence == "yes"){

	        				$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " user_prefrences.prefrence_id = comedian.id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "INNER"
	        				);
    					}else{

	    					$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " user_prefrences.prefrence_id = comedian.id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "LEFT"
	        				);

        					$WhereCom["user_prefrences.prefrence_id"] = NULL;
    					}

						$groupBy = "comedian.id";

						$SelectedData = "comedian.id as cid, name";

	        			$ComedainData = $this->CommedianList_model->getAnyData($WhereCom, $SelectedData, "", $limit_offset, $join_arr, $groupBy);
	        			$FinalArr = array();

	        			if(!empty($ComedainData)){
	        				foreach ($ComedainData as $key => $value) {
	        					$FinalArr[] = array(
	        						"prefrenceID" => $value->cid,
									"preference_name" => $value->name,
									"preference_added" => $is_prefrence
	        					);
	        				}

	        				$response['status'] = 1;
		           	 		$response['message'] = "Comedian's Data";
		           	 		$response['data'] = $FinalArr;	

	        			}else{
	        				$response['status'] = 0;
		           	 		$response['message'] = 'No data found';
		           	 		$response['data'] = array();	
	        			}
	        			

	        		}else{
	        			
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


	    /*Add Comedian Prefrence*/
	    public function addComedianPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    		$prefrence_id = $requestParams['prefrence_id'];

	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			/*Add the prefrences in list*/

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {
							
								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 8,
									"user_id" => $user_id
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								
								if(empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$AddPrefrenceArr = array(
										"user_id" => $user_id,
										"prefrence_id" => $value,
										"sports_id" => 8, 
										"created_at" => date("Y-m-d H:i:s")
									);

									$PreferencesData = $this->UserPrefrences_model->insertData($AddPrefrenceArr);
								}
							}

							$comedianDataa = $this->add_deleteComedianPorformer($user_id);

							$response['status'] = 1;
			           	 	$response['message'] = 'Comedian added successfully';
			           	 	$response['data'] = $comedianDataa;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No team added, please try again';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }

	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*Delete Comedian*/
	   	public function deleteComedianPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$prefrence_id = $requestParams['prefrence_id'];

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {

								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 8,
									"user_id" => $user_id,
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								if(!empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$WhereDeleteTeam = array(
										"id" => $PreferencesData[0]->id,
									);

									$PreferencesData = $this->UserPrefrences_model->delete($WhereDeleteTeam);
								}
							}

							$comedianDataa = $this->add_deleteComedianPorformer($user_id);

							$response['status'] = 1;
			           	 	$response['message'] = 'Comedian deleted successfully';
			           	 	$response['data'] = $comedianDataa;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No team deleted';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*MUSiC Management*/
	   	public function getMusicFestivalList(){
	    	
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];

	        	/*Pagination*/
				$page = !empty($requestParams['page'])?$requestParams['page']:1;
				$limit = "20";
		        $offset = ($page - 1)*$limit;
		        $limit_offset = $limit.','.$offset;

		        /*Search*/
		        $search = !empty($requestParams['search'])?$requestParams['search']:"";

		       	$is_prefrence = !empty($requestParams['is_prefrence'])?$requestParams['is_prefrence']:"no";
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){	        			
	        		
						$join_arr[] = array(
		    				"table_name" => "events_sub_type",
		    				"cond" => "events_sub_type.id = performer_master.sub_event_id",	
		    				"type" => "inner"
        				);

						$join_arr[] = array(
		    				"table_name" => "events_type",
		    				"cond" => "events_sub_type.event_type_id = events_type.id",	
		    				"type" => "inner"
        				);


        				$WhereMuiscEvents = array(
		        			"performer_master.event_type_id" => 4
		        		);


		        		if(!empty($search)){
		        			$WhereMuiscEvents['performer_master.name LIKE'] = "%".$search."%";
		        			$limit_offset = "";
		        		}

						if($is_prefrence == "yes"){
	        				$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " performer_master.performer_id = user_prefrences.prefrence_id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "INNER"
	        				);

	        			
	        				//$WhereMuiscEvents["user_prefrences.prefrence_id"] = "NOT NULL";
        				}else{
        					$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " user_prefrences.prefrence_id = performer_master.performer_id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "LEFT"
	        				);

	        				$WhereMuiscEvents["user_prefrences.prefrence_id"] = NULL;

        					//`user_prefrences`.`prefrence_id`IS NULL
	        				//$WhereMuiscEvents = 
        				}



        				$selectData = "performer_master.name, performer_master.performer_id";

        				$groupBy = "performer_master.performer_id";

						$PrefrenceData = $this->PerformerMaster_model->getAnyData($WhereMuiscEvents, $selectData, "", $limit_offset, $join_arr,$groupBy);
						//echo $this->db->last_query();
						$FinalData = array();

						if(!empty($PrefrenceData)){
							foreach ($PrefrenceData as $key => $value) {

								$FinalData[] = array(
									"prefrenceID" => $value->performer_id,
									"preference_name" => $value->name,
									"preference_added" => $is_prefrence
								);

							}


							$response['status'] = 1;
				           	$response['message'] = 'Music events data';
				           	$response['data'] = $FinalData;
						}else{
							$response['status'] = 0;
				           	$response['message'] = 'No data found';
				           	$response['data'] = $FinalData;
						}

	        		}else{
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


	    /*Add Comedian Prefrence*/
	    public function addMusicFestivalPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    		$prefrence_id = $requestParams['prefrence_id'];

	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			/*Add the prefrences in list*/

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {
							
								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 7,
									"user_id" => $user_id
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								
								if(empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$AddPrefrenceArr = array(
										"user_id" => $user_id,
										"prefrence_id" => $value,
										"sports_id" => 7, 
										"created_at" => date("Y-m-d H:i:s")
									);

									$PreferencesData = $this->UserPrefrences_model->insertData($AddPrefrenceArr);
								}
							}

							$MusicData = $this->add_deleteMusicData($user_id);
							$response['status'] = 1;
			           	 	$response['message'] = 'Music added successfully';
			           	 	$response['data'] = $MusicData;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No Music added, please try again';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }

	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*Delete Comedian*/
	   	public function deleteMusicFestivalPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$prefrence_id = $requestParams['prefrence_id'];

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {

								/*Check The music already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 7,
									"user_id" => $user_id,
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								if(!empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$WhereDeleteTeam = array(
										"id" => $PreferencesData[0]->id,
									);

									$PreferencesData = $this->UserPrefrences_model->delete($WhereDeleteTeam);
								}
							}

							$MusicData = $this->add_deleteMusicData($user_id);
							$response['status'] = 1;
			           	 	$response['message'] = 'Music deleted successfully';
			           	 	$response['data'] = $MusicData;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No data deleted';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


   	 	/*CONCERT Management*/
	   	public function getArtistList(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];

	        	/*Pagination*/
				$page = !empty($requestParams['page'])?$requestParams['page']:1;
				$limit = "20";
		        $offset = ($page - 1)*$limit;
		        $limit_offset = $limit.','.$offset;

		        /*Search*/
		        $search = !empty($requestParams['search'])?$requestParams['search']:"";

		        $is_prefrence = !empty($requestParams['is_prefrence'])?$requestParams['is_prefrence']:"no";
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
		        		
		        		$join_arr[] = array(
		    				"table_name" => "events_sub_type",
		    				"cond" => "events_sub_type.id = performer_master.sub_event_id",	
		    				"type" => "inner"
        				);

						$join_arr[] = array(
		    				"table_name" => "events_type",
		    				"cond" => "events_sub_type.event_type_id = events_type.id",	
		    				"type" => "inner"
        				);

        				$WhereMuiscEvents = array(
		        			"performer_master.event_type_id" => 2
		        		);

		        		if(!empty($search)){
		        			$WhereMuiscEvents['performer_master.name LIKE'] = "%".$search."%";
		        			$limit_offset = "";
		        		}

						if($is_prefrence == "yes"){
	        				$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " performer_master.performer_id = user_prefrences.prefrence_id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "inner"
	        				);
        				}else{
        					$join_arr[] = array(
			    				"table_name" => "user_prefrences",
			    				"cond" => " user_prefrences.prefrence_id = performer_master.performer_id AND user_prefrences.user_id = ".$user_id,
			    				"type" => "LEFT"
	        				);

	        				$WhereMuiscEvents["user_prefrences.prefrence_id"] = NULL;
        				}



        				$selectData = "performer_master.name, performer_master.performer_id";

        				$groupBy = "performer_master.performer_id";
						
						$PrefrenceData = $this->PerformerMaster_model->getAnyData($WhereMuiscEvents, $selectData, "",$limit_offset,$join_arr,$groupBy);
						
						$FinalData = array();

						if(!empty($PrefrenceData)){
							foreach ($PrefrenceData as $key => $value) {

								$FinalData[] = array(
									"prefrenceID" => $value->performer_id,
									"preference_name" => $value->name,
									"preference_added" => $is_prefrence
								);

							}

							$response['status'] = 1;
				           	$response['message'] = "Artist's events data";
				           	$response['data'] = $FinalData;
						}else{
							$response['status'] = 0;
				           	$response['message'] = 'No data found';
				           	$response['data'] = $FinalData;
						}


	        		}else{
	        			
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


	    /*Add Comedian Prefrence*/
	    public function addArtistPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    		$prefrence_id = $requestParams['prefrence_id'];

	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			/*Add the prefrences in list*/

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {
							
								/*Check The team already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 6,
									"user_id" => $user_id
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								
								if(empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$AddPrefrenceArr = array(
										"user_id" => $user_id,
										"prefrence_id" => $value,
										"sports_id" => 6, 
										"created_at" => date("Y-m-d H:i:s")
									);

									$PreferencesData = $this->UserPrefrences_model->insertData($AddPrefrenceArr);
								}
							}
							$ArtistData = $this->add_deleteArtistData($user_id);
							$response['status'] = 1;
			           	 	$response['message'] = 'Artist added successfully';
			           	 	$response['data'] = $ArtistData;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No artist added, please try again';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
	           	 		$response['message'] = 'User is inactive or deleted, please try again';
	           	 		$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
	            	$response['message'] = 'Token mismatch, please try again';
	           		$response['data'] = array();
	        	}
	        }

	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }

	    /*Delete Comedian*/
	   	public function deleteArtistPrefrences(){
	    	$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('prefrence_id', 'Prefrence_id', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$prefrence_id = $requestParams['prefrence_id'];

	        			$prefrence_idData = explode(',', $prefrence_id);

						if(!empty($prefrence_idData)){
							foreach ($prefrence_idData as $key => $value) {

								/*Check The music already exists in user or not*/
								$WhereCheckuser = array(
									"prefrence_id" => $value,
									"sports_id" => 6,
									"user_id" => $user_id,
								);

								$PreferencesData = $this->UserPrefrences_model->getAnyData($WhereCheckuser);
								if(!empty($PreferencesData)){
									/*Add the Preferneces in DB*/
									$WhereDeleteTeam = array(
										"id" => $PreferencesData[0]->id,
									);

									$PreferencesData = $this->UserPrefrences_model->delete($WhereDeleteTeam);
								}
							}

							$ArtistData = $this->add_deleteArtistData($user_id);

							$response['status'] = 1;
			           	 	$response['message'] = 'Artist deleted successfully';
			           	 	$response['data'] = $ArtistData;
						}else{
							$response['status'] = 0;
			           	 	$response['message'] = 'No data deleted';
			           	 	$response['data'] = array();
						}

	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }
	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	    }


	    /*Get team details after add-delete opertaion perform*/

	    public function add_deleteSportsTeam($sports_id, $type, $user_id){
	    	$FinalArrData =  array();

			$WhereSportsData = array(
				"events_type.id" => $sports_id,
				"events_sub_type.status" => 1,
				"events_sub_type.type" => $type
			);

			$join_arr[] = array(
				"table_name" => "events_sub_type",
				"cond" => "events_sub_type.event_type_id = events_type.id",	
				"type" => "inner"
			);
		        			
			$SportsEventsData = $this->EventsType_model->getAnyData($WhereSportsData,"","","",$join_arr);
			#pr($SportsEventsData);die;
			$FinalData = array();
			$FinalArrData = array();
			if(!empty($SportsEventsData)){
				$i = 0;
				/*Get a Team details based on the purticular sub type of sports*/
				#pr($SportsEventsData);die;
				foreach ($SportsEventsData as $key => $value) {
					$WhereEventsData = array(
						"sub_event_id" => $value->id,
					);

					$groupBy = "performer1id";
					$SelectData = "performer1id,performer1name";
					$Performer1EventsData = $this->EventsData_model->getAnyData($WhereEventsData,$SelectData,"","","",$groupBy);

					$groupBy2 = "performer2id";
					$SelctedData2 = "performer2id,performer2name";

					$Performer2EventsData = $this->EventsData_model->getAnyData($WhereEventsData,$SelctedData2,"","","",$groupBy2);

					$FinalArr1 = array();
					$FinalArr2 = array();
					/*Get the team list based on prefrenceID 1*/
					$k=0;
					if(!empty($Performer1EventsData)){
						
						foreach ($Performer1EventsData as $pkey => $pvalue) {
        					
        					/*Check The user alreadye selcted this team in preference or not*/
        					$WherePrefere = array(
        						"user_id" => $user_id,
        						"prefrence_id" => $pvalue->performer1id
        					);

        					$selectUser = "id";

        					$userDataPref = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

        					$Connected = "no";
        					if(!empty($userDataPref)){
        						$Connected = "yes";
        					}

        					$FinalArr1[$k] = array(
    							"prefrenceID" => $pvalue->performer1id,
    							"preference_name" => $pvalue->performer1name,
    							"preference_added" => $Connected
    						);	

    						$k++;

						}

					}

					/*Get the team list based on prefrenceID 2*/
					if(!empty($Performer2EventsData)){
						
						foreach ($Performer2EventsData as $pkey => $pvalue) {
        					
        					/*Check The user alreadye selcted this team in preference or not*/
        					$WherePrefere = array(
        						"user_id" => $user_id,
        						"prefrence_id" => $pvalue->performer2id
        					);

        					$selectUser = "id";

        					$userDataPref = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

        					$Connected = "no";
        					if(!empty($userDataPref)){
        						$Connected = "yes";
        					}

        					$FinalArr2[$k] = array(
    							"prefrenceID" => $pvalue->performer2id,
    							"preference_name" => $pvalue->performer2name,
    							"preference_added" => $Connected
    						);
    						
    						$k++;	
						}
					}

					$FinalData = array_merge($FinalArr1,$FinalArr2);
					$check = array_values(array_unique($FinalData, SORT_REGULAR));

					$FinalArrData[$i] = array(
						"title" => !empty($value->name)?$value->name:"",
						"teams" => !empty($check)?$check:array()
					);

					$FinalData = $check = "";
					$i++;
				}

           	 	return $FinalArrData;

	    	}
	    }

	    public function add_deleteComedianPorformer($user_id){
    		
    		$FinalArr = array();

    		$WhereCom = array(
				"comedian.status" => 1,
			);	

			$SelectedData = "comedian.id as cid, name";

			$ComedainData = $this->CommedianList_model->getAnyData($WhereCom, $SelectedData, "", "", "", "");
			
			$FinalArr = array();

			if(!empty($ComedainData)){
				
				foreach ($ComedainData as $key => $value) {
					/*Check the prefrences added or not by user*/
					$WherePrefData = array(
						"user_id" => $user_id,
						"prefrence_id" => $value->cid,
						"sports_id" => 8
					);

					$PrefrenceData = $this->UserPrefrences_model->getAnyData($WherePrefData);

					$PrefrenceAdded = !empty($PrefrenceData)?"yes":"no";

					$FinalArr[] = array(
						"prefrenceID" => $value->cid,
						"preference_name" => $value->name,
						"preference_added" => $PrefrenceAdded
					);
				}
   		 	}
   		 	return $FinalArr;
	   	}

	   	public function add_deleteMusicData($user_id){
		   	$PrefArr = array();

		   	$WhereEventsData = array(
				"events_type.id" => 7,
				"events_sub_type.status" => 1,
			);

			$join_arr[] = array(
				"table_name" => "events_sub_type",
				"cond" => "events_sub_type.event_type_id = events_type.id",	
				"type" => "inner"
			);
		
			$EventsData = $this->EventsType_model->getAnyData($WhereEventsData,"","","",$join_arr);
		
			$FinalData = array();
			$FinalArrData = array();
			if(!empty($EventsData)){
				/*Get a list of musician*/
				$WhereMuiscEvents = array(
					"event_type_id" => 4
				);

				$SelectData = "performer1id, performer1name, performer2id, performer2name";
				$PerformerEventsData = $this->EventsData_model->getAnyData($WhereMuiscEvents,$SelectData);
				
				$FinalPerformer1 = array();
				$FinalPerformer2 = array();

				if(!empty($PerformerEventsData)){
					/*Music Events Data*/
					foreach ($PerformerEventsData as $key => $value) {
					
						if($value->performer1id != 0 && $value->performer1name != ""){

							$WherePrefere = array(
	    						"user_id" => $user_id,
	    						"prefrence_id" => $value->performer1id
	    					);

	    					$selectUser = "id";

	    					$userDataPref1 = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

	    					$prefconected = !empty($userDataPref1)?"yes":"no";

	    					$FinalPerformer1[] = array(
	    						"prefrenceID" => $value->performer1id,
								"preference_name" => $value->performer1name,
								"preference_added" => $prefconected
	    					);

						}

						if($value->performer2id != 0 && $value->performer2name != ""){

							$WherePrefere = array(
	    						"user_id" => $user_id,
	    						"prefrence_id" => $value->performer2id
	    					);

	    					$selectUser = "id";

	    					$userDataPref2 = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

	    					$prefconected = !empty($userDataPref2)?"yes":"no";
	    					$FinalPerformer2[] = array(
	    						"prefrenceID" => $value->performer2id,
								"preference_name" => $value->performer2name,
								"preference_added" => $prefconected
	    					);
						}
					}

				    $FinalData = array_merge($FinalPerformer1,$FinalPerformer2);
					$PrefArr = array_values(array_unique($FinalData, SORT_REGULAR));

	   			}
	   		}

	   		return $PrefArr;
	   	}

	   	/*Add Delete Artists Data*/
	   	public function add_deleteArtistData($user_id){

		   	$WhereEventsData = array(
				"events_type.id" => 6,
				"events_sub_type.status" => 1,
			);

			$join_arr[] = array(
				"table_name" => "events_sub_type",
				"cond" => "events_sub_type.event_type_id = events_type.id",	
				"type" => "inner"
			);

			$EventsData = $this->EventsType_model->getAnyData($WhereEventsData,"","","",$join_arr);
			
			$FinalData = array();
			$FinalArrData = array();
			$PrefArr = array();
			if(!empty($EventsData)){
				/*Get a list of musician*/
				$WhereMuiscEvents = array(
					"event_type_id" => 2
				);

				$SelectData = "performer1id, performer1name, performer2id, performer2name";
				$PerformerEventsData = $this->EventsData_model->getAnyData($WhereMuiscEvents,$SelectData);
				
				$FinalPerformer1 = array();
				$FinalPerformer2 = array();

				if(!empty($PerformerEventsData)){
				/*Music Events Data*/
				foreach ($PerformerEventsData as $key => $value) {
				
					if($value->performer1id != 0 && $value->performer1name != 0){

						$WherePrefere = array(
							"user_id" => $user_id,
							"prefrence_id" => $value->performer1id
						);

						$selectUser = "id";

						$userDataPref1 = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

						$prefconected = !empty($userDataPref1)?"yes":"no";

						$FinalPerformer1[] = array(
							"prefrenceID" => $value->performer1id,
							"preference_name" => $value->performer1name,
							"preference_added" => $prefconected
						);

					}

					if($value->performer2id != 0 && $value->performer2name != 0){

						$WherePrefere = array(
							"user_id" => $user_id,
							"prefrence_id" => $value->performer2id
						);

						$selectUser = "id";

						$userDataPref2 = $this->UserPrefrences_model->getAnyData($WherePrefere, $selectUser);

						$prefconected = !empty($userDataPref2)?"yes":"no";
						$FinalPerformer2[] = array(
							"prefrenceID" => $value->performer2id,
							"preference_name" => $value->performer2name,
							"preference_added" => $prefconected
						);
					}
				}

			    $FinalData = array_merge($FinalPerformer1,$FinalPerformer2);
				$PrefArr = array_values(array_unique($FinalData, SORT_REGULAR));
	   		}
	   	}

	   	return $PrefArr;
	}
	   	
	   	
	   	/*Get Popular shows lists Comedian and Musical*/

	   	public function PopularShowsList(){
	   		$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('type', 'Type', 'trim|required');

	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){
	        			$type = $requestParams['type'];

	        			$WhereArr = array();

	        			if($type == "comedian"){
	        				$WhereArr["events_data.event_type_id"] = 3;
	        			}else{
	        				$WhereArr["events_data.event_type_id"] = 4;
	        			}

	        			$SelectData = "events_data.id as eventid, events_data.seatgeek_event_id, events_data.performer1name as performer1, events_data.performer2name as performer2, events_type.name as event_name, events_data.ticket_link, events_data.eventname, events_data.venue,events_data.eventdate, events_data.performer1id, events_data.performer2id, events_data.sub_event_id, event_popularity";

						$join_arr[] = array(
			                "table_name" => "events_sub_type",
			                "cond" => "events_sub_type.id = events_data.sub_event_id",
			                "type" => "inner"
		            	);

						$join_arr[] = array(
			                "table_name" => "events_type",
			                "cond" => "events_type.id = events_sub_type.event_type_id",
			                "type" => "inner"
		            	);

		            	$orderBy = "event_popularity DESC";

		            	$limit = "40";


	        			$EventsData = $this->EventsData_model->getAnyData($WhereArr, $SelectData, $orderBy, $limit, $join_arr);

	        			$response['status'] = 1;
		           	 	$response['message'] = 'Popular Shows Data';
		           	 	$response['data'] = $EventsData;        			
	        		}else{
	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{
	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }

	       	$JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	   	}


	   	/*Notify the connection for upcoming events*/

	   	public function UpcomingEventsNotification(){

	   		$_POST = jsonRequestPara();
	    	$this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
	    	$this->form_validation->set_rules('device_token', 'Device_token', 'trim|required');
	    	$this->form_validation->set_rules('event_id', 'Event_id', 'trim|required');
	    	$this->form_validation->set_rules('notify_user_id', 'Notify_user_id', 'trim|required');


	    	if ($this->form_validation->run() === FALSE) {
	            $response['status'] = 0;
	            $response['message'] = 'Please enter all required fields';
	        } else {
	        	$requestParams = $this->input->post();
	    		
	    		$user_id = $requestParams['user_id'];
	        	$device_token = $requestParams['device_token'];
	        	$event_id = $requestParams['event_id'];
	        	$notify_user_id = $requestParams['notify_user_id'];


	    
	        	$IsUser = CheckTokenAndUSerID($user_id, $device_token);

	        	if(!empty($IsUser)){
	        		
	        		if($IsUser[0]->status == 1 && $IsUser[0]->deleted == 0){

	        			/*Check the EVENT is exists or not*/

	        			$WhereEvent = array(
	        				"id" => $event_id
	        			);

	        			$EventsData = $this->EventsData_model->getAnyData($WhereEvent, "id, eventname, venue, eventdate");

	        			if(!empty($EventsData)){
	        				/*Send the notification to user*/
	        				$notify_user_idData = explode(',', $notify_user_id);
	        				#pr($notify_user_idData);die;
	        				if(!empty($notify_user_idData)){

	        					/*Send the notification to listed user*/
	        					foreach ($notify_user_idData as $key => $value) {
	        						
	        						$WhereLastDevice = array(
										"user_id" => $value
									);		

									$orderBy = "user_logs.id DESC";


									$join_arr[] = array(
						                "table_name" => "user",
						                "cond" => "user.id = user_logs.user_id",
						                "type" => "inner"
					            	);

									//$selectData = "id, eventname, eventdate, venue";

									$OponentNotifyData = $this->UserLogs_model->getAnyData($WhereLastDevice, "", $orderBy, "1", $join_arr, "");

									

									if(!empty($OponentNotifyData)){
										$sendUsername = $IsUser[0]->firstname;
										$firstname = $OponentNotifyData[0]->firstname;
										$eventname = $EventsData[0]->eventname;
										$eventdate = !empty($EventsData[0]->eventdate)?date("M d Y , h:i a", strtotime($EventsData[0]->eventdate)):"-";
										$venue = $EventsData[0]->venue;

										$message = $sendUsername." shared upcoming event ".$eventname." at ".$venue." on ".$eventdate;
										//echo $message;die;
										$notify = array(
											"message" => $message,
											"token" => $OponentNotifyData[0]->device_id,
											"priority" => "high",
											"event_id" => $event_id,
											"type" => "event"
										);
																					
										if($OponentNotifyData[0]->device_type == 1){
											/*Android*/
											send_android_notification($OponentNotifyData[0]->device_id, $message, "", $notify);
										}else{
											/*Ios*/
											send_ios_notification($OponentNotifyData[0]->device_id, $message, "", $notify);
										}
	        						}
	        						$join_arr = array();
	        					}

		        				$response['status'] = 1;
				           	 	$response['message'] = 'Event invitation shared successfully!';
				           	 	$response['data'] = array();

	        				}else{

		        				$response['status'] = 0;
				           	 	$response['message'] = 'No user selected, please try again!';
				           	 	$response['data'] = array();	
	        				}


	        			}else{
		        			$response['status'] = 0;
			           	 	$response['message'] = 'Event may be expired, please try again!';
			           	 	$response['data'] = array();
	        			}
	        				
	        		}else{

	        			$response['status'] = 0;
		           	 	$response['message'] = 'User is inactive or deleted, please try again';
		           	 	$response['data'] = array();
	        		}
	        	}else{

	        		$response['status'] = 999;
		            $response['message'] = 'Token mismatch, please try again';
		           	$response['data'] = array();
	        	}
	        }

	        $JSONresponse=J_endecode($response,"jencode");
	        echo $JSONresponse;
	   	}

	}
?>