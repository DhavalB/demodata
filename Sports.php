<?php
    class Sports extends CI_Controller {

    public function __construct() {
        
        parent::__construct();
        $this->load->model('FamilyInfo_model');
        $this->load->model('Sports_model');
        $this->load->model('Sports_school_model');
        $this->load->model('Sports_club_model');
        $this->load->model('Sports_season_model');
        $this->load->model('Sports_team_position_model');
        $this->load->model('Sports_name_model');
        $this->load->model('Schoolname_model');
        $this->load->model('Clubname_model');
        $this->load->model('Sports_stats_model');
        $this->load->model('Sports_competition_model');
        $this->load->model('Sports_stats_master_model');
        /*Changes after Sports Model added*/
        $this->load->model('AddSports_model');
        $this->load->model('Sports_comp_stats_model');
    }

    /*Get Season and Sports Pick list*/
    /*Done*/
    public function GetSeasonPickList(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {

            $mem_id = $this->input->post('mem_id');
            $user_id = $this->input->post('user_id');
            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
            if(!empty($is_member))
            {
                $final_array=$sports=$sportssss="";
                $where=array("is_active" => 1);

                $select_1="id,season";
                $select_2="id,name";
                $final_array['sports_season']=$this->Sports_season_model->getAnyData($where,$select_1);
                $final_array['sports_school']=$this->Sports_school_model->getAnyData($where,$select_2);
                $final_array['sports_club']=$this->Sports_club_model->getAnyData($where,$select_2);

                $sports_list=$this->Sports_name_model->getAnyData($where,$select_2);
                $select_3="id,sports_id,position";
                $position=$this->Sports_team_position_model->getAnyData($where,$select_3);

                if(!empty($sports_list))
                {
                    foreach ($sports_list as $key => $value) {
                        $sports="";
                        if(!empty($value['id']))
                        {
                            $sports['Sports_name']= $value['name'];
                            $sports['Sports_id']= $value['id'];
                            $where_2=array(
                                "is_active" => 1,
                                "sports_id" => $value['id']
                            );

                            $data_pos = $this->Sports_team_position_model->getAnyData($where_2,$select_3);
                            if(!empty($data_pos))
                            {
                                foreach ($data_pos as $k => $v) {

                                    $sports['position'][]=array(
                                        "id" =>$v['id'],
                                        "position" => $v['position'],
                                    );

                                }
                            }
                            else
                            {
                                $sports['position']=[];
                            }

                        }
                        $final_array['Sports'][] = $sports;

                    }
                }

                /* Validate */

                $join_arr[] = array(
                    "table_name" => "sports",
                    "cond" => "tbl_sports_main.id = sports.sport_id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_name",
                    "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_season",
                    "cond" => "sports.season_id = sports_season.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_school_level",
                    "cond" => "sports.school_team_id = sports_school_level.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_club_level",
                    "cond" => "sports.club_age_id = sports_club_level.id ",
                    "type" => "left"
                );

                $where_3=array(
                    "mem_id" => $mem_id,
                );

                $select_4="count(tbl_sports_main.sports_id) as total,sports.id as id,sports_name.name,sports_season.season,sports_school_level.name as schoolname,sports_club_level.name as clubname";
                $group_by="sports.id";

                $data_validate = $this->AddSports_model->getAnyData($where_3,$select_4,"","",$join_arr,$group_by);
                foreach ($data_validate as $ke => $val) {

                    if($val['schoolname'] != '' && $val['schoolname'] != null)
                    {
                        $sportssss[]=array(
                                "sports_name" =>'School-'.$val['name'],
                                "sports_id"=>$val['id'],

                                "season"=>$val['season'],
                                "name"=>$val['schoolname'],
                        );
                    }

                    if($val['clubname'] != '' && $val['clubname'] != null)
                    {
                        $sportssss[]=array(
                                "sports_name" =>'Club-'.$val['name'],
                                "sports_id"=>$val['id'],
                                "season"=>$val['season'],
                                "name"=>$val['clubname']
                        );
                    }
                }
                $final_array['Validation']=$sportssss;
                $response['code'] = 1;
                $response['status'] = "success";
                $response['data'] = $final_array;

            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'User is either inactive or deleted';
            }

        echo json_encode($response);
       }
    }

    /*Start: Sports Added Y*/
    /*Done*/
    public function AddSportsdata(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('season', 'Season', 'trim|required');
        $this->form_validation->set_rules('sport', 'Sport', 'trim|required');

        if($this->input->post['school_level'] == '' || $this->input->post['club_level'] =='')
        {
            if($this->input->post['school_level'] !='')
            {
                $this->form_validation->set_rules('school_level', 'School_level', 'trim|required');
                $this->form_validation->set_rules('school_name', 'School_name', 'trim|required');
                $this->form_validation->set_rules('school_pos', 'School_pos', 'trim|required');
            }
            if($this->input->post['club_level'] !='')
            {
                $this->form_validation->set_rules('club_level', 'Club_level', 'trim|required');
                $this->form_validation->set_rules('club_name', 'Club_name', 'trim|required');
                $this->form_validation->set_rules('club_pos', 'Club_pos', 'trim|required');
            }
        }

        if ($this->form_validation->run() === FALSE) {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'Please enter all fields';
                echo json_encode($response);
        } else {

            $postVar = $this->input->post();

            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];

            $mem_array = array(
                    "id" => $mem_id,
                    "user_id" => $user_id,
                    "is_active" => 1,
                    "is_deleted" => 0
            );
            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);

            if(!empty($is_member))
            {
                $sport=$postVar['sport'];
                $season=$postVar['season'];

                $school_level=isset($postVar['school_level'])?$postVar['school_level']:"";
                $club_level=isset($postVar['club_level']) ? $postVar['club_level'] : "";
                $school_name=isset($postVar['school_name']) ? $postVar['school_name'] : "";
                $school_pos=isset($postVar['school_pos']) ? $postVar['school_pos'] : "";
                $club_name=isset($postVar['club_name']) ? $postVar['club_name'] : "";
                $club_pos=isset($postVar['club_pos']) ? $postVar['club_pos'] : "";
                $data_dec=isset($postVar['sports_stats']) ? $postVar['sports_stats'] : "";

                /*Start: For Insert Sports Data*/
                    $sports_stats_data['season_id']=$season;
                    $sports_stats_data['school_team_id']=$school_level;
                    $sports_stats_data['club_age_id']=$club_level;
                    $sports_stats_data['school_name']=$school_name;
                    $sports_stats_data['school_position_id']=$school_pos;
                    $sports_stats_data['club_name']=$club_name;
                    $sports_stats_data['club_position_id']=$club_pos;
                    $sports_stats_data['created_at']=date("Y-m-d H:i:s");
                /*End: For Insert Sports Data*/

                /*Check School level And club LEvel*/
                 $sport_school_exist=$sport_club_exist="";

                    if(!empty($school_level) && !empty($club_level))
                    {
                        if(!empty($school_level))
                        {
                            $where_sport=array(
                                "mem_id" => $mem_id,
                                "sports_id" => $sport,
                                "school" => 1
                            );

                            $sport_school_exist=$this->AddSports_model->getAnyData($where_sport);
                        }
                        
                        if(!empty($club_level))
                        {
                            $where_sport=array(
                                "mem_id" => $mem_id,
                                "sports_id" => $sport,
                                "club" => 1
                            );

                            $sport_club_exist=$this->AddSports_model->getAnyData($where_sport);
                        }
                    }
                    else
                    {
                        if(!empty($school_level))
                        {
                            $where_sport=array(
                                "mem_id" => $mem_id,
                                "sports_id" => $sport,
                                "school" => 1
                            );

                            $sport_school_exist=$this->AddSports_model->getAnyData($where_sport);
                        }
                        
                        if(!empty($club_level))
                        {
                            $where_sport=array(
                                "mem_id" => $mem_id,
                                "sports_id" => $sport,
                                "club" => 1
                            );

                            $sport_club_exist=$this->AddSports_model->getAnyData($where_sport);
                        }
                    }

                /*Check Sports exists or not*/

                /*
                    Note:
                    ->  This is Temporarry Please do not change code without permit.
                    ->  Still need to work on it.
                */
                /*Testing Sports Duplication*/
                if(empty($sport_club_exist) && empty($sport_school_exist)) 
                {
                    $where_sport_limit=array(
                        "mem_id" => $mem_id,
                    );

                    $select="SUM(school) AS school_sum,SUM(club) AS club_sum";
                    $check_sport_limit=$this->AddSports_model->getAnyData($where_sport_limit,$select);

                    $sum_sports=($check_sport_limit[0]['school_sum'])+($check_sport_limit[0]['club_sum']);

                    if($sum_sports < 10)
                    {   
                        if(!empty($school_level) && !empty($club_level))
                        {
                            $sum_sports=$sum_sports+2;
                        }

                        if($sum_sports <= 10)
                        {

                            $where_sport_duplicate=array(
                                    "mem_id" => $mem_id,
                                    "sports_id" => $sport,
                            );

                            $check_sport_duplicate=$this->AddSports_model->getAnyData($where_sport);

                            $count_sports=count($check_sport_duplicate);
                            
                            if($count_sports == 0)
                            {
                                $sports_data['mem_id']=$mem_id;
                                $sports_data['sports_id']=$sport;
                                $sports_data['created_at']=date("Y-m-d H:i:s");


                                $where_sport_season_duplicate=array(
                                    "season_id" => $season,
                                    "sport_id" => $sport
                                );

                                if(!empty($school_level))
                                {
                                    $sports_data['school']=1;
                                    $sports_data['club']=0;
                                    $insert=$this->AddSports_model->insert($sports_data);

                                    if(!empty($insert))
                                    {
                                        
                                        $sports_stats_data_school=$sports_stats_data;
                                        unset($sports_stats_data_school['club_age_id']);
                                        unset($sports_stats_data_school['club_name']);
                                        unset($sports_stats_data_school['club_position_id']);
                                        $sports_stats_data_school['sport_id']=$insert;
                                        $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_school,$data_dec);
                                        
                                    }
                                }

                                if(!empty($club_level))
                                {
                                    $sports_data['school']=0;
                                    $sports_data['club']=1;
                                    $insert=$this->AddSports_model->insert($sports_data);

                                    if(!empty($insert))
                                    {   
                                        $sports_stats_data_club=$sports_stats_data;
                                        unset($sports_stats_data_club['school_team_id']);
                                        unset($sports_stats_data_club['school_name']);
                                        unset($sports_stats_data_club['school_position_id']);
                                        $sports_stats_data_club['sport_id']=$insert;
                                        $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_club,$data_dec);
                                    }
                                }

                                $response['code'] = 1;
                                $response['status'] = "success";
                                $response['data'] = $sports_stats_data;   
                            }
                            else
                            {
                                $response['code'] = 0;
                                $response['status'] = "error";
                                $response['message'] = 'Sports already exists';
                            }
                        }
                        else
                        {
                            $response['code'] = 0;
                            $response['status'] = "error";
                            $response['message'] = 'Only Ten sports allow to add';
                            echo json_encode($response);die;
                        }
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = 'Only Ten sports allow to add';
                    }

                }
                else if(empty($sport_club_exist) && !empty($sport_school_exist))
                {

                    $where_sport_limit=array(
                        "mem_id" => $mem_id,
                    );

                    $select="SUM(school) AS school_sum,SUM(club) AS club_sum";
                    $check_sport_limit=$this->AddSports_model->getAnyData($where_sport_limit,$select);
                    $sum_sports=($check_sport_limit[0]['school_sum'])+($check_sport_limit[0]['club_sum']);
                    
                    if($sum_sports < 10)
                    {  

                        $where_sport=array(
                            "mem_id" => $mem_id,
                            "sports_id" => $sport
                        );

                        $sport_exist=$this->AddSports_model->getAnyData($where_sport);
                        $insert=$sport_exist[0]['id'];

                        if(!empty($insert))
                        {
                            $where_sport_season_duplicate=array(
                            "season_id" => $season,
                            "sport_id" => $insert
                            );
                            $check_sport_season_duplicate=count($this->Sports_model->getAnyData($where_sport_season_duplicate));
                            if($check_sport_season_duplicate == 0)
                            {


                                if(empty($sport_club_exist))
                                {
                                    /*Check Club Level empty or not*/
                                    if(!empty($club_level))
                                    {
                                        $sports_data['mem_id']=$mem_id;
                                        $sports_data['sports_id']=$sport;
                                        $sports_data['created_at']=date("Y-m-d H:i:s");
                                        $sports_data['school']=0;
                                        $sports_data['club']=1;
                                        $insert_data=$this->AddSports_model->insert($sports_data);

                                        if(!empty($insert_data))
                                        {   
                                            $sports_stats_data_club=$sports_stats_data;
                                            unset($sports_stats_data_club['school_team_id']);
                                            unset($sports_stats_data_club['school_name']);
                                            unset($sports_stats_data_club['school_position_id']);
                                            $sports_stats_data_club['sport_id']=$insert_data;
                                            $insert_other_sports=$this->InsertSports_Data($insert_data,$sports_stats_data_club,$data_dec);
                                        }
                                    }
                                }

                                if(!empty($sport_school_exist))
                                {
                                    if(!empty($school_level))
                                    {   
                                        $where_sport=array(
                                            "mem_id" => $mem_id,
                                            "sports_id" => $sport,
                                            "school" => 1
                                        );

                                        $sport_exist=$this->AddSports_model->getAnyData($where_sport);
                                        $insert=$sport_exist[0]['id'];


                                        if(!empty($insert))
                                        {
                                                $where_school_sport=array(
                                                    "mem_id" => $mem_id,
                                                    "sports_id" => $sport,
                                                    "school!=" =>0 
                                                );

                                                $sports_val=$this->AddSports_model->getAnyData($where_school_sport);
                                                $sports_stats_data['sport_id']=$sports_val[0]['id'];
                                                $insert=$sports_val[0]['id'];
                                                $sports_data['school']=1;
                                                $sports_data['club']=0;

                                                if(!empty($insert))
                                                {    
                                                    $sports_stats_data_school=$sports_stats_data;
                                                    unset($sports_stats_data_school['club_age_id']);
                                                    unset($sports_stats_data_school['club_name']);
                                                    unset($sports_stats_data_school['club_position_id']);
                                                    $sports_stats_data_school['sport_id']=$insert;
                                                    $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_school,$data_dec);       
                                                }

                                                if($insert_other_sports == 1)
                                                {
                                                    $response['code'] = 1;
                                                    $response['status'] = "success";
                                                    $response['data'] = $sports_stats_data;
                                                }

                                            
                                            
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $response['code'] = 0;
                                $response['status'] = "error";
                                $response['message'] = 'Season already exists';
                            }
                        }
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = 'Only Ten sports allow to add';
                    }
                    echo json_encode($response);die;
                }


                else if(!empty($sport_club_exist) && empty($sport_school_exist))
                {

                    $where_sport_limit=array(
                        "mem_id" => $mem_id,
                    );

                    $select="SUM(school) AS school_sum,SUM(club) AS club_sum";
                    $check_sport_limit=$this->AddSports_model->getAnyData($where_sport_limit,$select);
                    $sum_sports=($check_sport_limit[0]['school_sum'])+($check_sport_limit[0]['club_sum']);
                    
                    if($sum_sports < 10)
                    {  
                        $where_sport=array(
                            "mem_id" => $mem_id,
                            "sports_id" => $sport,
                        );

                        $sport_exist=$this->AddSports_model->getAnyData($where_sport);
                        $insert=$sport_exist[0]['id'];
                        $where_sport_season_duplicate=array(
                            "season_id" => $season,
                            "sport_id" => $insert
                        );

                        $check_sport_season_duplicate=count($this->Sports_model->getAnyData($where_sport_season_duplicate));
                        if($check_sport_season_duplicate == 0)
                        {
                            if(empty($sport_school_exist))
                            {
                                /*Check Club Level empty or not*/
                                if(!empty($school_level))
                                {
                                    $sports_data['mem_id']=$mem_id;
                                    $sports_data['sports_id']=$sport;
                                    $sports_data['created_at']=date("Y-m-d H:i:s");
                                    $sports_data['school']=1;
                                    $sports_data['club']=0;
                                    $insert_data=$this->AddSports_model->insert($sports_data);

                                    if(!empty($insert_data))
                                    {   
                                        $sports_stats_data_club=$sports_stats_data;
                                        unset($sports_stats_data_club['club_age_id']);
                                        unset($sports_stats_data_club['club_name']);
                                        unset($sports_stats_data_club['club_position_id']);
                                        $sports_stats_data_club['sport_id']=$insert_data;
                                        $insert_other_sports=$this->InsertSports_Data($insert_data,$sports_stats_data_club,$data_dec);
                                    }
                                }
                            }

                            if(!empty($sport_club_exist))
                            {
                                if(!empty($club_level))
                                {
                                    $where_sport=array(
                                        "mem_id" => $mem_id,
                                        "sports_id" => $sport,
                                        "club" => 1
                                    );

                                    $sport_exist=$this->AddSports_model->getAnyData($where_sport);
                                    $insert=$sport_exist[0]['id'];
                                  
                                    if(!empty($insert))
                                    {

                                        $where_school_sport=array(
                                            "mem_id" => $mem_id,
                                            "sports_id" => $sport,
                                            "club!=" =>0 
                                        );

                                        $sports_val=$this->AddSports_model->getAnyData($where_school_sport);
                                        $sports_stats_data['sport_id']=$sports_val[0]['id'];
                                        $insert=$sports_val[0]['id'];
                                        $sports_data['school']=0;
                                        $sports_data['club']=1;

                                        if(!empty($insert))
                                        {    
                                            $sports_stats_data_school=$sports_stats_data;
                                            unset($sports_stats_data_school['school_team_id']);
                                            unset($sports_stats_data_school['school_name']);
                                            unset($sports_stats_data_school['school_position_id']);
                                            $sports_stats_data_school['sport_id']=$insert;
                                            $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_school,$data_dec);       
                                        }

                                        if($insert_other_sports == 1)
                                        {
                                            $response['code'] = 1;
                                            $response['status'] = "success";
                                            $response['data'] = $sports_stats_data;
                                        }

                                    }
                                    
                                }


                            }
                        }
                        else
                        {
                            $response['code'] = 0;
                            $response['status'] = "error";
                            $response['message'] = 'Season already exists';
                                        
                        }
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = 'Only Ten sports allow to add';
                    }
                    echo json_encode($response);die;
                }

                else
                {
                    $where_sport=array(
                        "mem_id" => $mem_id,
                        "sports_id" => $sport
                    );

                    $sport_exist=$this->AddSports_model->getAnyData($where_sport);
                    $insert=$sport_exist[0]['id'];

                    if(!empty($insert))
                    {
                        $where_sport_season_duplicate=array(
                            "season_id" => $season,
                            "sport_id" => $insert
                        );

                        $check_sport_season_duplicate=count($this->Sports_model->getAnyData($where_sport_season_duplicate));
                        if($check_sport_season_duplicate == 0)
                        {
                            if(!empty($school_level))
                            {

                                $where_school_sport=array(
                                    "mem_id" => $mem_id,
                                    "sports_id" => $sport,
                                    "school!=" =>0 
                                );

                                $sports_val=$this->AddSports_model->getAnyData($where_school_sport);

                                $sports_stats_data['sport_id']=$sports_val[0]['id'];
                                $insert=$sports_val[0]['id'];
                                $sports_data['school']=1;
                                $sports_data['club']=0;


                                if(!empty($insert))
                                {    
                                    $sports_stats_data_school=$sports_stats_data;
                                    unset($sports_stats_data_school['club_age_id']);
                                    unset($sports_stats_data_school['club_name']);
                                    unset($sports_stats_data_school['club_position_id']);
                                    $sports_stats_data_school['sport_id']=$insert;
                                    $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_school,$data_dec);       
                                }
                            }



                            if(!empty($club_level))
                            {

                                $where_school_sport=array(
                                    "mem_id" => $mem_id,
                                    "sports_id" => $sport,
                                    "club!=" =>0 
                                );

                                $sports_val=$this->AddSports_model->getAnyData($where_school_sport);
                                
                                $sports_stats_data['sport_id']=$sports_val[0]['id'];
                                $insert=$sports_val[0]['id'];
                                
                                $sports_data['school']=0;
                                $sports_data['club']=1;

                                if(!empty($insert))
                                {   
                                    $sports_stats_data_club=$sports_stats_data;
                                    unset($sports_stats_data_club['school_team_id']);
                                    unset($sports_stats_data_club['school_name']);
                                    unset($sports_stats_data_club['school_position_id']);
                                    $sports_stats_data_club['sport_id']=$insert;
                                    $insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data_club,$data_dec);
                                }
                            }

                            //$insert_other_sports=$this->InsertSports_Data($insert,$sports_stats_data,$data_dec);
                            if($insert_other_sports == 1)
                            {
                                $response['code'] = 1;
                                $response['status'] = "success";
                                $response['data'] = $sports_stats_data;
                            }
                        }
                        else
                        {
                            $response['code'] = 0;
                            $response['status'] = "error";
                            $response['message'] = 'Season already exists';
                        }
                    }
                }

            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'User is either inactive or deleted';
            }
              echo json_encode($response);
            }
    }
    /* End : Sports Add */


    /*Add Sports DATA*/
    function InsertSports_Data($sprt_id,$sprt_array,$data_dec){
        if(!empty($sprt_id) && !empty($sprt_array))
        {
            $insert_sports_data=$this->Sports_model->insert($sprt_array);    
            if(!empty($data_dec))
            {
                $data_array=json_decode($data_dec);
                $insert_stats="";

                if(!empty($data_array))
                {
                    if(!empty($sprt_array['school_team_id']) && !empty($sprt_array['club_age_id']))
                    {
                        $insert_stats=$this->save_stats_data($sprt_id,$data_array,"insert","both");
                    }

                    else
                    {
                        if(!empty($sprt_array['school_team_id']))
                        {
                            $insert_stats=$this->save_stats_data($sprt_id,$data_array,"insert","school");
                        }

                        if(!empty($sprt_array['club_age_id']))
                        {
                            $insert_stats=$this->save_stats_data($sprt_id,$data_array,"insert","club");
                        }
                    }
                }
            }

            if($insert_sports_data > 0)
            {
                return 1;       
            }
            else
            {
                return 0;
            }
        }
    }
    /**/
    /*Start: Get Sports Data*/


    /*End: Get Sports Data*/
    /*Done*/
    public function GetSportsdata(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('season', 'Season', 'trim|required');
        $this->form_validation->set_rules('sport', 'Sport', 'trim|required');
        // $this->form_validation->set_rules('type', 'Type', 'trim|required');


        if ($this->form_validation->run() === FALSE) {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'Please enter all fields';
                echo json_encode($response);
        } else {

                $postVar = $this->input->post();
                $mem_id = $postVar['mem_id'];
                $user_id = $postVar['user_id'];


                $mem_array = array(
                        "id" => $mem_id,
                        "user_id" => $user_id,
                        "is_active" => 1,
                        "is_deleted" => 0
                    );

                $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
                if(!empty($is_member))
                {

                    $sport = $postVar['sport'];
                    $season = $postVar['season'];

                    $sport_check=array(
                        "sports.id" => $sport,
                        //"tbl_sports_main.mem_id" => $mem_id,
                        "sports.season_id" => $season
                    );

                    $join_arr[] = array(
                        "table_name" => "tbl_sports_main",
                        "cond" => "tbl_sports_main.id = sports.sport_id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_name",
                        "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_season",
                        "cond" => "sports.season_id = sports_season.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_school_level",
                        "cond" => "sports.school_team_id = sports_school_level.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_club_level",
                        "cond" => "sports.club_age_id = sports_club_level.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_competition",
                        "cond" => "sports.id = sports_competition. sports_id ",
                        "type" => "left"
                    );

                    $select_4="count(tbl_sports_main.sports_id) as total,sports_competition.id as comp_id,tbl_sports_main.id as mainsports,sports.school_position_id as school_id,sports.club_position_id as club_id,sports.id as id,sports_name.name,sports_season.season,sports_school_level.name as school_team,sports_club_level.name as club_team,sports.club_name,sports.school_name,sports.season_id,tbl_sports_main.sports_id as sports_name_id";
                    $group_by="sports.id";
                    $data_validate = $this->Sports_model->getAnyData($sport_check,$select_4,"","",$join_arr,$group_by);
                   
                    if(!empty($data_validate))
                    {
                        if(!empty($data_validate[0]['club_id']) && $data_validate[0]['club_id'] != 0)
                        {
                            $where_sc_pos=array(
                                "id" => $data_validate[0]['club_id']
                            );
                            $select_sc="id,position";
                            $club_pos=$this->Sports_team_position_model->getAnyData($where_sc_pos,$select_sc);
                            $data_validate[0]['club_postion_name'] = $club_pos[0]['position'];
                            $data_validate[0]['club_postion_id'] = $club_pos[0]['id'];

                        }
                        else
                        {
                            $data_validate[0]['club_postion_name'] = "";
                            $data_validate[0]['club_postion_id'] = "";
                        }

                        if(!empty($data_validate[0]['school_id']) && $data_validate[0]['school_id'] != 0)
                        {
                            $where_cl_pos=array(
                                "id" => $data_validate[0]['school_id']
                            );
                            $select_cl="id,position";
                            $school_pos=$this->Sports_team_position_model->getAnyData($where_cl_pos,$select_cl);
                            $data_validate[0]['school_postion_name'] = $school_pos[0]['position'];
                            $data_validate[0]['school_postion_id'] = $school_pos[0]['id'];

                        }
                        else
                        {
                            $data_validate[0]['school_postion_name'] = "";
                            $data_validate[0]['school_postion_id'] = "";
                        }
                       

                        $where_comp=array(
                            "sports_id" => $data_validate[0]['mainsports'],
                        );

                        $check_stats_used=$this->Sports_stats_model->getAnyData($where_comp);
                        $finalArr="";
                        
                        if(!empty($check_stats_used))
                        {
                            $i=0;
                            foreach ($check_stats_used as $key => $value) {
                                    
                                    $finalArr[$i]['stats_name']=$value['choosen_stats'];
                                    $finalArr[$i]['stats_id']=$value['id'];
                                    $finalArr[$i]['type']=$value['type'];

                                    if(!empty($value['choosen_stats']))
                                    {
                                        $where_stats_dependancy=array(
                                            "name" => $value['choosen_stats'],
                                            "sports_id" =>   $data_validate[0]['sports_name_id']
                                        );

                                        $stats_dependancy=$this->Sports_stats_master_model->getAnyData($where_stats_dependancy);
                                        if(!empty($stats_dependancy))
                                        {
                                            if($stats_dependancy[0]['type'] == 0) {$type="Number";}
                                            if($stats_dependancy[0]['type'] == 1) {$type="Percent";}
                                            if($stats_dependancy[0]['type'] == 2) {$type="Decimal";}

                                            $finalArr[$i]['type']=$type;
                                            $finalArr[$i]['is_dependant']=0;
                                            $finalArr[$i]['dependancy']=[];
                                            $finalArr[$i]['formula']="";

                                            if($stats_dependancy[0]['is_dependant'] == 1)
                                            {
                                                $finalArr[$i]['is_dependant']=1;
                                                $dependancy=unserialize($stats_dependancy[0]['dependant_stats']);                                
                                                $j=0;
                                                $finalArrSt="";
                                                if(!empty($dependancy))
                                                {
                                                    foreach ($dependancy as $kd => $vd) {
                                                        $finalArrSt[$j]=$vd;
                                                        $j++;
                                                    }
                                                     $finalArr[$i]['dependancy']=$finalArrSt;
                                                }
                                                else
                                                {
                                                    $finalArr[$i]['dependancy']=[];
                                                }

                                                $finalArr[$i]['formula']=trim($stats_dependancy[0]['formula']);
                                                
                                            }
                                            $finalArr[$i]['user_help']=trim($stats_dependancy[0]['user_help']);
                                            
                                            if($stats_dependancy[0]['toggle'] == 0)
                                            {
                                                $toggle="No";
                                            }
                                            else
                                            {
                                                $toggle="Yes";
                                            }
                                            $finalArr[$key]['toggle']=$toggle;
                                        }
                                    }

                                    $where_stats_used=array(
                                        "sports_id" =>  $sport,
                                        "choosen_stats" => $value['id'],
                                        "compition_id" => $data_validate[0]['comp_id']
                                    );

                                    $check_stats_used_data=$this->Sports_comp_stats_model->getAnyData($where_stats_used);

                                if(!empty($check_stats_used_data))
                                {
                                    $finalArr[$i]['used']="1";
                                }

                                else
                                {
                                    $finalArr[$i]['used']="0";
                                }
                                $i++;
                            }
                            $data_validate[0]['sports_stats']=$finalArr;

                        }
                        else
                        {
                            $data_validate[0]['sports_stats']=[];
                        }


                        $response['code'] = 1;
                        $response['status'] = "success";
                        $response['data'] = $data_validate;
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = 'No Data Found';
                    }
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = 'User is either inactive or deleted';
                }
                echo json_encode($response);
        }
    }


    /*Start: Sports Update Y*/
    /*Done*/
    public function UpdateSportsdata(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('season', 'Season', 'trim|required');
        $this->form_validation->set_rules('sport', 'Sport', 'trim|required');
        $this->form_validation->set_rules('update_sport', 'Update_sport', 'trim|required');

        if($this->input->post['school_level'] == '' || $this->input->post['club_level'] =='')
        {
            if($this->input->post['school_level'] !='')
            {
                $this->form_validation->set_rules('school_level', 'School_level', 'trim|required');
                $this->form_validation->set_rules('school_name', 'School_name', 'trim|required');
                $this->form_validation->set_rules('school_pos', 'School_pos', 'trim|required');
            }
            if($this->input->post['club_level'] !='')
            {
                $this->form_validation->set_rules('club_level', 'Club_level', 'trim|required');
                $this->form_validation->set_rules('club_name', 'Club_name', 'trim|required');
                $this->form_validation->set_rules('club_pos', 'Club_pos', 'trim|required');
            }
        }

        if ($this->form_validation->run() === FALSE) {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'Please enter all fields';
                echo json_encode($response);
        } else {

            $postVar = $this->input->post();
            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];
            $mem_array = array(
                    "id" => $mem_id,
                    "user_id" => $user_id,
                    "is_active" => 1,
                    "is_deleted" => 0
                );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);

            if(!empty($is_member))
            {
                $sport=$postVar['sport'];
                $update_sport=$postVar['update_sport'];
                $season=$postVar['season'];

                $school_level=isset($postVar['school_level'])?$postVar['school_level']:"";
                $club_level=isset($postVar['club_level']) ? $postVar['club_level'] : "";
                $school_name=isset($postVar['school_name']) ? $postVar['school_name'] : "";
                $school_pos=isset($postVar['school_pos']) ? $postVar['school_pos'] : "";
                $club_name=isset($postVar['club_name']) ? $postVar['club_name'] : "";
                $club_pos=isset($postVar['club_pos']) ? $postVar['club_pos'] : "";
                $data_dec=isset($postVar['sports_stats']) ? $postVar['sports_stats'] : "";
                $stats_delete=isset($postVar['delete_stats']) ? $postVar['delete_stats'] : "";


                $school_array=$club_array=$is_school_data=$is_club_data="";

                $where_sport_limit=array(
                    "mem_id" => $mem_id,
                );

                $select="SUM(school) AS school_sum,SUM(club) AS club_sum";


                $check_sport_limit=$this->AddSports_model->getAnyData($where_sport_limit,$select);
                $sum_sports=($check_sport_limit[0]['school_sum'])+($check_sport_limit[0]['club_sum']);
                $insert="";

                $where_sport_duplicate=array(
                    "mem_id" => $mem_id,
                    "sports_id" => $sport,
                );

                $check_sport_duplicate=$this->AddSports_model->getAnyData($where_sport_duplicate);
                if(!empty($check_sport_duplicate))
                {
                    $where_sport=array(
                        "id"=>$update_sport
                    );

                    $get_sprt=$this->Sports_model->getAnyData($where_sport);

                    $sprt_id=$get_sprt[0]['sport_id'];
                    $where_sport_season_duplicate=array(
                        "season_id" => $season,
                        "sport_id " => $sprt_id,
                        "id !=" => $update_sport
                    );

                    $check_sport_season_duplicate=count($this->Sports_model->getAnyData($where_sport_season_duplicate,"","",""));
                    $insert_sports_data="";
                    if($check_sport_season_duplicate == 0)
                    {
                        $sports_stats_data['season_id']=$season;
                        $sports_stats_data['school_team_id']=$school_level;
                        $sports_stats_data['club_age_id']=$club_level;
                        $sports_stats_data['school_name']=$school_name;
                        $sports_stats_data['school_position_id']=$school_pos;
                        $sports_stats_data['club_name']=$club_name;
                        $sports_stats_data['club_position_id']=$club_pos;
                        $sports_stats_data['updated_at']=date("Y-m-d H:i:s");

                        $where_sports=array(
                            "id" => $update_sport
                        );
                        /*pr($stats_delete);die;*/
                        $insert_sports_data=$this->Sports_model->update($sports_stats_data,$where_sports);

                        if(!empty($insert_sports_data))
                        {
                            /*Start: Delete Stats Data*/

                            if(!empty($stats_delete))
                            {
                                $delete_array=json_decode($stats_delete,true);
                                $delete_stats="";
                                $final_st=$final_dt="";
                                if(!empty($delete_array))
                                {
                                   foreach ($delete_array as $dk => $dv) {
                                       if(!empty($dv['id']))
                                       {
                                            if(!empty($dv['name']))
                                            {
                                                $where_stats=array(
                                                    "id" => $dv['id'],
                                                    "choosen_stats" => $dv['name']
                                                );

                                                $check_is_stats=$this->Sports_stats_model->getAnyData($where_stats);

                                                if(!empty($check_is_stats))
                                                {
                                                    $delete_stats_data=$this->Sports_stats_model->delete($where_stats,"");
                                                }
                                            }
                                       }
                                   }
                                }
                            }
                            /*End: Delete Stats Data*/



                            if(!empty($data_dec))
                            {
                                $data_array=json_decode($data_dec,true);
                                $insert_stats="";
                                $final_st=$final_dt="";

                               if(!empty($data_array))
                                {
                                    if(!empty($school_level))
                                    {
                                        $insert_stats=$this->save_stats_data($sprt_id,$data_array,"insert","school","","checkbx");
                                    }

                                    if(!empty($club_level))
                                    {
                                        $insert_stats=$this->save_stats_data($sprt_id,$data_array,"insert","club","","checkbx");
                                    }
                                }

                                if($insert_stats != "")
                                {
                                    $response['stats_data'] = "stats updated successfully";
                                }


                            }

                            /*Start: Delete Stats Data*/

                            if(!empty($stats_delete))
                            {
                                $delete_array=json_decode($stats_delete,true);
                                $delete_stats="";
                                $final_st=$final_dt="";
                                if(!empty($delete_array))
                                {
                                   foreach ($delete_array as $dk => $dv) {
                                       if(!empty($dv['id']))
                                       {
                                            if(!empty($dv['name']))
                                            {
                                                $where_stats=array(
                                                    "id" => $dv['id'],
                                                    "choosen_stats" => $dv['name']
                                                );

                                                $check_is_stats=$this->Sports_stats_model->getAnyData($where_stats);

                                                if(!empty($check_is_stats))
                                                {
                                                    $delete_stats_data=$this->Sports_stats_model->delete($where_stats,"");
                                                }
                                            }
                                       }
                                   }
                                }
                            }
                            /*End: Delete Stats Data*/
                            $response['code'] = 1;
                            $response['status'] = "success";
                            $response['data'] = $sports_stats_data;
                        }
                    }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "Error";
                    $response['message'] = "This Season already used";
                }
        }
        else
        {
            $response['code'] = 0;
            $response['status'] = "Error";
            $response['message'] = "No data found";
        }
                }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "User is either inactive or deleted";
            }


        echo json_encode($response);
        }
    }
    /*End: Sports Update*/

    /*Start:Get Sports_Compition Data Y*/
    /*Done*/
    public function GetCompData(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('compition_id', 'Compition_id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
        $this->form_validation->set_rules('type', 'Type', 'trim|required');


        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {

            $postVar = $this->input->post();
            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];
            $type = $postVar['type'];
            $mem_array = array(
                    "id" => $mem_id,
                    "user_id" => $user_id,
                    "is_active" => 1,
                    "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
            if(!empty($is_member))
            {
                $compition_id = $postVar['compition_id'];
                $sports_id = $postVar['sports_id'];

                $where_comp_sports=array(
                    "sports_competition.id" => $compition_id,
                    "sports_competition.sports_id" => $sports_id,
                    "sports.id" => $sports_id
                );

                $join_arr1[] = array(
                    "table_name" => "sports",
                    "cond" => "sports_competition.sports_id = sports.id ",
                    "type" => "left"
                );

                $is_comp=$this->Sports_competition_model->getAnyData($where_comp_sports,"","","",$join_arr1);
                if(!empty($is_comp))
                {

                    $where_3=array(
                        "mem_id" => $mem_id,
                        "sports.id" => $is_comp[0]['id'],
                    );

                    $join_arr[] = array(
                        "table_name" => "tbl_sports_main",
                        "cond" => "tbl_sports_main.id = sports.sport_id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_competition",
                        "cond" => "sports_competition.sports_id = sports.sport_id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_name",
                        "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_season",
                        "cond" => "sports.season_id = sports_season.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_school_level",
                        "cond" => "sports.school_team_id = sports_school_level.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_club_level",
                        "cond" => "sports.club_age_id = sports_club_level.id ",
                        "type" => "left"
                    );

                    $group_by="sports_competition.id";

                    $select_4="sports_competition.oponent_name,sports.club_name,sports.school_name,tbl_sports_main.sports_id as sports_name_id,sports_school_level.name as school_team,sports_club_level.name as club_team,sports.school_position_id as school_id,sports.club_position_id as club_id,sports_competition.id,sports_competition.comp_date,";
                    $data_validate = $this->Sports_model->getAnyData($where_3,$select_4,"","",$join_arr);   

                    if(!empty($data_validate))
                    {
                    $is_comp[0]['postion_name'] = "";
                    $is_comp[0]['team_name'] = "";
                    $final_info=array();
                        
                    if($type == "School")
                    {

                        if(!empty($data_validate[0]['school_id']) && $data_validate[0]['school_id'] != 0)
                        {
                            $where_cl_pos=array(
                                "id" => $data_validate[0]['school_id']
                            );
                            $select_cl="id,position";

                            $school_pos=$this->Sports_team_position_model->getAnyData($where_cl_pos,$select_cl);
                            $is_comp[0]['postion_name'] = $school_pos[0]['position'];
                        }
                            $is_comp[0]['team_name'] = $data_validate[0]['school_team'];
                            
                    }
                        else
                        {   
                            if(!empty($data_validate[0]['club_id']) && $data_validate[0]['club_id'] != 0)
                            {
                                $where_sc_pos=array(
                                    "id" => $data_validate[0]['club_id']
                                );
                                $select_sc="id,position";
                                $club_pos=$this->Sports_team_position_model->getAnyData($where_sc_pos,$select_sc);
                                
                                $is_comp[0]['postion_name'] = $club_pos[0]['position'];

                            }
                            $is_comp[0]['team_name'] = $data_validate[0]['club_team'];
                        }

                      
                    $final_image=$final_video="";
                    if(!empty($is_comp[0]['image']))
                    {
                        $image=unserialize($is_comp[0]['image']);
                    
                        if(!empty($image))
                        {
                            $i=0;
                            foreach ($image as $key => $value) {
                                $final_image[$i]=base_url()."assets/sports/".$mem_id."/".$value;
                                $i++;
                            }
                            $is_comp[0]['image']=$final_image;                            
                        }
                        else
                        {
                            $is_comp[0]['image']=[];
                        }
                    }
                    else
                    {
                        $is_comp[0]['image']=[];
                    }
                    if(!empty($is_comp[0]['video']))
                    {
                        $video=unserialize($is_comp[0]['video']);
                        if(!empty($video))
                        {
                            $j=0;
                            foreach ($video as $key => $value) {
                               $final_video[$j]=base_url()."assets/sports/".$mem_id."/".$value;
                               $j++;
                            }
                            $is_comp[0]['video']=$final_video;
                        }
                        else
                        {
                            $is_comp[0]['video']=[];
                        }

                    }
                    else
                    {
                        $is_comp[0]['video']=[];
                    }

                    $where_comp=array(
                        "sports_stats.sports_id" => $sports_id,
                        "sports_stats.compition_id" => $compition_id
                    );

                    $join_stats[] = array(
                        "table_name" => "sports_stats_main",
                        "cond" => "sports_stats.choosen_stats = sports_stats_main.id ",
                        "type" => "left"
                    );

                    $select_comp="sports_stats_main.id,sports_stats_main.sports_id,sports_stats_main.choosen_stats,sports_stats.type,sports_stats.id as val_id,sports_stats.value,sports_stats.compition_id";
                    $check_stats_used=$this->Sports_comp_stats_model->getAnyData($where_comp,$select_comp,"","",$join_stats);
                    // pr($check_stats_used);
                    $finalArr=[];
                    if(!empty($check_stats_used))
                    {
                        $i=0;
                        foreach ($check_stats_used as $key => $value) {

                         if(!empty($value['choosen_stats']))
                            {
                                $where_stats_dependancy=array(
                                    "name" => $value['choosen_stats'],
                                    "sports_id" =>   $data_validate[0]['sports_name_id']
                                );

                                $stats_dependancy=$this->Sports_stats_master_model->getAnyData($where_stats_dependancy);
                           
                                if(!empty($stats_dependancy))
                                {
                                    if($stats_dependancy[0]['type'] == 0) {$type="Number";}
                                    if($stats_dependancy[0]['type'] == 1) {$type="Percent";}
                                    if($stats_dependancy[0]['type'] == 2) {$type="Decimal";}

                                    $finalArr[$i]['type']=$type;
                                    $finalArr[$i]['is_dependant']=0;
                                    $finalArr[$i]['dependancy']=[];
                                    $finalArr[$i]['formula']="";

                                    if($stats_dependancy[0]['is_dependant'] == 1)
                                    {
                                        $finalArr[$i]['is_dependant']=1;
                                        $dependancy=unserialize($stats_dependancy[0]['dependant_stats']);                                
                                        $j=0;
                                        $finalArrSt="";
                                        if(!empty($dependancy))
                                        {
                                            foreach ($dependancy as $kd => $vd) {
                                                $finalArrSt[$j]=$vd;
                                                $j++;
                                            }
                                            $finalArr[$i]['dependancy']=$finalArrSt;
                                        }
                                        else
                                        {
                                            $finalArr[$i]['dependancy']=[];
                                        }                                            
                                        $finalArr[$i]['formula']=trim($stats_dependancy[0]['formula']);

                                    }
                                    $finalArr[$key]['user_help']=trim($stats_dependancy[0]['user_help']);
                                    
                                    if($stats_dependancy[0]['toggle'] == 0)
                                    {
                                        $toggle="No";
                                    }
                                    else
                                    {
                                        $toggle="Yes";
                                    }
                                    $finalArr[$key]['toggle']=$toggle;

                                }
                            }


                            $finalArr[$i]['stats_name']=$value['choosen_stats'];
                            $finalArr[$i]['stats_id']=$value['id'];
                            $finalArr[$i]['type']=$value['type'];
                            $finalArr[$i]['value']=$value['value'];
                            $finalArr[$i]['val_id']=$value['val_id'];
                            $finalArr[$i]['compition_id']=$value['compition_id'];
                            $i++;
                        }
                        $is_comp[0]['players_stats']=$finalArr;
                    }
                    else
                    {
                        $is_comp[0]['players_stats']=$finalArr;
                    }
                }
                    $response['code'] = 1;
                    $response['status'] = "success";
                    $response['data'] = $is_comp[0];
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = "No data found";
                }

            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "User is either inactive or deleted";
            }

            echo json_encode($response);
        }
    }
    /*End:Get Sports_Compition Data*/


    /*Start: Update Player Stats Compition Data*/

    /*End: Update Player Stats Compition Data*/


    /*Done*/
    public function GetPlayerStatPickList(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {

            $mem_id = $this->input->post('mem_id');
            $user_id = $this->input->post('user_id');
            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);

            if(!empty($is_member))
            {
                $final_array="";

                $where_3=array(
                        "mem_id" => $mem_id,
                );

                $join_arr[] = array(
                    "table_name" => "sports",
                    "cond" => "tbl_sports_main.id = sports.sport_id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_name",
                    "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_season",
                    "cond" => "sports.season_id = sports_season.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_school_level",
                    "cond" => "sports.school_team_id = sports_school_level.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_club_level",
                    "cond" => "sports.club_age_id = sports_club_level.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                        "table_name" => "sports_team_position",
                        "cond" => "sports.school_position_id = sports_team_position.id ",
                        "type" => "left"
                );


                $select_4="count(tbl_sports_main.sports_id) as total,sports.id as id,sports_name.name,sports_season.season,sports_school_level.name as schoolname,sports_club_level.name as clubname,sports.club_name,sports.school_name,sports_team_position.position,sports.season_id";
                $group_by="sports.id";

                $data_validate = $this->AddSports_model->getAnyData($where_3,$select_4,"","",$join_arr,$group_by);
                if(!empty($data_validate))
                {

                    $school_level=$club_level=$club_sports=$school_sports=$sport_array=$join_arr2="";
                    $j=0;
                    foreach ($data_validate as $key => $val) {

                        if(!empty($val['id']))
                        {
                                if($val['schoolname'] != '' && $val['schoolname'] != null)
                                {
                                    $school_level=$val['schoolname'];
                                    $school_name=$val['school_name'];
                                    $school_sports='School-'.$val['name'];
                                    $school_postion=$val['position'];
                                    $season_id=$val['season_id'];
                                }

                                if($val['clubname'] != '' && $val['clubname'] != null)
                                {
                                    $club_level=$val['clubname'];
                                    $club_name=$val['club_name'];
                                    $club_sports='Club-'.$val['name'];
                                    $club_postion=$val['position'];
                                    $season_id=$val['season_id'];

                                }

                                $where_stats=array(
                                        "sports_stats.sports_id" =>$val['id'],
                                    );
                                $join_arr2[]=array(
                                                "table_name" => "sports_stats",
                                                "cond" => "sports.id = sports_stats.sports_id ",
                                                "type" => "left"
                                         );
                                $select_stats="sports_stats.choosen_stats,sports_stats.id,sports_stats.team_type,sports_stats.type,sports_stats.value,sports_stats.compition_id";
                                $data_stats = $this->Sports_model->getAnyData($where_stats,$select_stats,"","",$join_arr2);
                                if(!empty($data_stats))
                                {
                                    $final_stats_school=array();
                                    $final_stats_club=array();

                                    $i=0;
                                    $k=0;
                                    if($school_sports != '')
                                    {
                                        $final_array[$j]['sports_data']=array(
                                            "sports_name" =>$school_sports,
                                            "name" => $school_name,
                                            "level" => $school_level,
                                            "position" => $school_postion,
                                            "season"=>$val['season'],
                                            "sports_id"=>$val['id'],
                                            "season_id"=>$season_id
                                        );
                                        foreach ($data_stats as $ks => $va) {
                                                if($va['team_type'] == "school")
                                                {
                                                    $final_stats_school[$i]['id']=$va['id'];
                                                    $final_stats_school[$i]['choosen_stats']=$va['choosen_stats'];
                                                    $final_stats_school[$i]['team_type']=$va['team_type'];
                                                    $final_stats_school[$i]['type']=$va['type'];
                                                    $final_stats_school[$i]['value']=$va['value'];
                                                    $final_stats_school[$i]['compition_id']=$va['compition_id'];
                                                    $i++;
                                                }
                                        }
                                    $final_array[$j]['sports_stats']=$final_stats_school;
                                    }

                                    if($club_sports != "")
                                    {
                                        $final_array[$j]['sports_data']=array(
                                            "sports_name" =>$club_sports,
                                            "name" => $club_name,
                                            "level" => $club_level,
                                            "position" => $club_postion,
                                            "season"=>$val['season'],
                                            "sports_id"=>$val['id'],
                                            "season_id"=>$season_id
                                        );
                                        foreach ($data_stats as $kc => $kv) {
                                            if($kv['team_type'] == "club")
                                            {
                                                $final_stats_club[$k]['id']=$kv['id'];
                                                $final_stats_club[$k]['choosen_stats']=$kv['choosen_stats'];
                                                $final_stats_club[$k]['team_type']=$kv['team_type'];
                                                $final_stats_club[$k]['type']=$kv['type'];
                                                $final_stats_club[$k]['value']=$kv['value'];
                                                $final_stats_club[$k]['compition_id']=$kv['compition_id'];
                                                $k++;
                                            }
                                        }
                                        $final_array[$j]['sports_stats']=$final_stats_club;
                                    }

                                }
                                else
                                {
                                    $final_array['sports_stats']=[];
                                }
                        }
                        $join_arr2=$data_stats="";
                        $j++;
                    }
                    $new_Data['Sports_Screen']=$final_array;
                    $response['code'] = 1;
                    $response['status'] = "success";
                    $response['data'] = $new_Data;
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['data'] = "No Data Found";
                }
            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'User is either inactive or deleted';

            }

            echo json_encode($response);
        }
    }

    /*Done: testing needed Y*/
    public function AddPlayerStats(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
        $this->form_validation->set_rules('team_id', 'Team_id', 'trim|required');
        $this->form_validation->set_rules('comp_date', 'Comp_date', 'trim|required');
        $this->form_validation->set_rules('opp_name', 'Opp_name', 'trim|required');
        $this->form_validation->set_rules('location', 'Location', 'trim|required');
        $this->form_validation->set_rules('type', 'Type', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {
            $postVar = $this->input->post();
            $mem_id = $this->input->post('mem_id');
            $user_id = $this->input->post('user_id');
            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);

            if(!empty($is_member))
            {
                if (!empty($_FILES)) {
                    $folder_id = $mem_id;
                    $count1 = $count2 = 0;
                    $path_root = "./assets/sports/";
                    if (!is_dir($path_root)) {
                            mkdir($path_root);
                    }

                    $path = "./assets/sports/" . $folder_id;
                    if (!is_dir($path)) {
                        mkdir($path);
                    }

                    $config['upload_path'] = $path;
                    $config['allowed_types'] = 'gif|jpeg|jpg|png|mp4|mov|mkv|quicktime';
                    $video_ext = array("mp4", "mov", "mkv", "quicktime");
                    $image_ext = array("gif", "jpeg", "jpg", "png");
                    $config['max_size'] = '0';
                    $config['max_width'] = '0';
                    $config['max_height'] = '0';
                    $file_count = count($_FILES['documents']['name']);
                    $document_file = $_FILES;
                    for ($i = 0; $i < $file_count; $i++) {

                        $_FILES['documents1']['name'] = time() . "_" . $document_file['documents']['name'][$i];
                        $_FILES['documents1']['type'] = $document_file['documents']['type'][$i];
                        $_FILES['documents1']['tmp_name'] = $document_file['documents']['tmp_name'][$i];
                        $_FILES['documents1']['error'] = $document_file['documents']['error'][$i];
                        $_FILES['documents1']['size'] = $document_file['documents']['size'][$i];


                        $this->load->library('upload', $config);
                        $this->upload->initialize($config);
                        $extension = strtolower(end((explode(".", $_FILES['documents1']['name']))));

                        if (in_array($extension, $video_ext)) {
                            if ($this->upload->do_upload('documents1')) {
                                $fileData = $this->upload->data();
                                $doc_video[$count1] = $fileData['file_name'];
                                $count1++;
                            }
                        } elseif (in_array($extension, $image_ext)) {
                            if ($this->upload->do_upload('documents1')) {
                                $fileData = $this->upload->data();
                                $doc_image[$count2] = $fileData['file_name'];
                                $count2++;
                            }
                        }
                    }
                }

                /* For video */
                if (!empty($doc_video)) {
                    $document_video = serialize($doc_video);
                } else {
                    $document_video = "";
                }
                /* for audio */
                if (!empty($doc_image)) {
                    $document_image = serialize($doc_image);
                } else {
                    $document_image = "";
                }
                $upload_video = $document_video;
                $upload_image = $document_image;


                $team_id=$postVar['team_id'];
                $sports_id=$postVar['sports_id'];
                $comp_id=isset($postVar['comp_id']) ? $postVar['comp_id']:"";
                $comp_date=$postVar['comp_date'];
                $opp_name=$postVar['opp_name'];
                $location=$postVar['location'];
                $won=isset($postVar['won']) ? $postVar['won']:"";
                $lost=isset($postVar['lost']) ? $postVar['lost'] : "";
                $comments=isset($postVar['comments']) ? $postVar['comments'] : "";
                $comp_type=isset($postVar['type']) ? $postVar['type'] : "";
                $data_dec=isset($postVar['add_stats_val']) ? $postVar['add_stats_val'] : "";
                $delete_documents = json_decode($this->input->post('delete_documents'));

                //pr($delete_documents);die;

                $where=array(
                        "id" => $sports_id
                );
                $is_sports_id=$this->Sports_model->getAnyData($where);
                if(!empty($is_sports_id))
                {
                        $add_player_stats['sports_id']=$sports_id;
                        $add_player_stats['oponent_name']=$opp_name;
                        $add_player_stats['location']=$location;
                        $add_player_stats['won']=$won;
                        $add_player_stats['lost']=$lost;
                        $add_player_stats['comments']=$comments;
                        $add_player_stats['comp_date']=$comp_date;
                        if(empty($comp_id))
                        {
                            $add_player_stats['image']=$upload_image;
                            $add_player_stats['video']=$upload_video;

                            $add_player_stats['created_at']=date("Y-m-d H:i:s");
                            $insert=$this->Sports_competition_model->insert($add_player_stats);
                            $data_array=json_decode($data_dec);

                            if(!empty($data_array))
                            {
                                $update_stats=$this->save_stats_data($insert,$data_array,"insert",$comp_type,$sports_id);
                            }
                        }
                        else
                        {
                            $where_cmp=array(
                                "id" => $comp_id
                            );

                            $is_comp_id=$this->Sports_competition_model->getAnyData($where_cmp);
                            if(!empty($is_comp_id))
                            {

                                /* start: delete document */
                                $all_doc_name = array();
                                $final_docs_img = $final_docs_video = "";
                                
                                if (!empty($delete_documents) || !empty($upload_video) || !empty($upload_image)) {
                                        $select_comp = "image,video";
                                        $where_comp = array('id' => $comp_id,);
                                        $all_doc_name = $this->Sports_competition_model->getAnyData($where_comp, $select_comp, '', '', '');
                                }

                                if (!empty($delete_documents)) {

                                    $path = "./assets/sports/" . $mem_id . "/";

                                    $docs_img = unserialize($all_doc_name[0]['image']);
                                    $doc_img = array();
                                    if (!empty($docs_img)) {
                                        foreach ($delete_documents[0] as $key => $value) {
                                            if(in_array($value,$docs_img)){
                                                //delete image from server
                                                if (file_exists($path . $value)) {
                                                    $delete = unlink($path . $value);
                                                }
                                                $docs_img = array_values(array_diff($docs_img, array($value)));
                                            }
                                        }
                                        $doc_img = $docs_img;
                                    }

                                    if (!empty($doc_img)) {
                                        $final_docs_img = serialize($doc_img);
                                    }

                                    $docs_video = unserialize($all_doc_name[0]['video']);
                                    $doc_video = array();

                                    if (!empty($docs_video)) {

                                        foreach ($delete_documents[0] as $key => $value) {
                                            if(in_array($value,$docs_video)){
                                                //delete image from server
                                                if (file_exists($path . $value)) {
                                                    $delete = unlink($path . $value);
                                                }
                                                //remove from db
                                                $docs_video = array_values(array_diff($docs_video, array($value)));
                                            }
                                        }
                                        $doc_video = $docs_video;
                                    }
                                    if (!empty($doc_video)) {
                                        $final_docs_video = serialize($doc_video);
                                    }
                                }
                                /* end: delete document */

                                /* start: updating document */

                                if (!empty($upload_video) || !empty($upload_image)) {
                                    $all_doc_video = array();
                                    $all_doc_img = array();
                                    if (!empty($delete_documents)) {
                                        if (!empty($final_docs_img)) {
                                            $all_doc_img = unserialize($final_docs_img);
                                        }
                                        if (!empty($final_docs_video)) {
                                            $all_doc_video = unserialize($final_docs_video);
                                        }
                                    } else {
                                        $all_doc_img = unserialize($all_doc_name[0]['image']);
                                        $all_doc_video = unserialize($all_doc_name[0]['video']);
                                    }

                                    if (!empty($upload_image)) {
                                        $upload_image=unserialize($upload_image);
                                        foreach ($upload_image as $data) {
                                            $all_doc_img[] = $data;
                                        }
                                        $final_docs_img = serialize($all_doc_img);
                                    }
                                    if (!empty($upload_video)) {
                                        $upload_video=unserialize($upload_video);
                                        foreach ($upload_video as $data) {

                                            $all_doc_video[] = $data;
                                        }
                                        $final_docs_video = serialize($all_doc_video);
                                    }
                                }
                                if (!empty($delete_documents) || !empty($upload_image)) {
                                    $add_player_stats['image'] = $final_docs_img;
                                }
                                if (!empty($delete_documents) || !empty($upload_video)) {
                                    $add_player_stats['video'] = $final_docs_video;
                                }

                                /* end: updating document */

                                $add_player_stats['updated_at']=date("Y-m-d H:i:s");
                                $update=$this->Sports_competition_model->update($add_player_stats,$where_cmp);
                                if(!empty($update))
                                {
                                    $data_array=json_decode($data_dec);
                                    if(!empty($data_array))
                                    {
                                        $update_stats=$this->save_stats_data($comp_id,$data_array,"update",$comp_type);
                                    }
                                }
                            }
                        }
                        $response['code'] = 1;
                        $response['status'] = "success";
                        $response['data'] = $add_player_stats;
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = 'No data found';
                }


            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'User is either inactive or deleted';
            }
            echo json_encode($response);
        }
    }


    /*Done y*/
    function save_stats_data($sports_id,$data,$type,$comp_type,$sport_type="",$checkbx=""){
         
        if(!empty($data) && !empty($sports_id))
        {
            $this->load->model('Sports_stats_model');
            if($type == "insert")
            {
                    $final_data=$stats_insert="";
                    foreach ($data as $key => $value) {                        
                        if(!empty($sport_type))
                        {
                            $final_data['choosen_stats'] = $value->stats;
                            $final_data['value']=$value->value;
                            $final_data['compition_id']=$sports_id;
                            $final_data['sports_id']=$sport_type;
                            $final_data['team_type']=$comp_type;
                            $final_data['created_at']=date("Y-m-d H:i:s");
                            $stats_insert = $this->Sports_comp_stats_model->insert($final_data);
                        }
                        else if(!empty($checkbx)){
                           
                            $where_stats_val=array(
                                "sports_id" => $sports_id,
                                "choosen_stats" => $value['stats']
                            );
                            
                            $select="choosen_stats as stats,type";
                            $stats_data=$this->Sports_stats_model->getAnyData($where_stats_val,$select);
                            if(empty($stats_data))
                            {
                                $final_data['choosen_stats'] = $value['stats'];
                                $final_data['type']=$value['type'];
                                $final_data['sports_id']=$sports_id;
                                $final_data['team_type']=$comp_type;
                                $final_data['created_at']=date("Y-m-d H:i:s");
                                $stats_insert = $this->Sports_stats_model->insert($final_data);
                            }
                        }
                        else
                        {
                            $final_data['choosen_stats'] = $value->stats;
                            $final_data['type']=$value->type;
                            $final_data['sports_id']=$sports_id;
                            $final_data['team_type']=$comp_type;
                            $final_data['created_at']=date("Y-m-d H:i:s");
                            $stats_insert = $this->Sports_stats_model->insert($final_data);
                        }
                    }

                    if(!empty($stats_insert))
                    {
                        return $stats_insert;
                    }
                    else
                    {
                        return 0;
                    }
            }
            else
            {
                $final_data=$stats_update="";
                if(!empty($sport_type))
                {
                    foreach ($data as $key => $value) {
                        if($value['id'] == 0)
                        {
                            $final_data['choosen_stats'] = $value['stats'];
                            $final_data['type']=$value['type'];
                            $final_data['sports_id']=$sports_id;
                            $final_data['created_at']=date("Y-m-d H:i:s");
                            $stats_insert = $this->Sports_stats_model->insert($final_data);

                            if(!empty($stats_insert))
                            {
                                $final_data_ins['team_type']=$comp_type;
                                $final_data_ins['choosen_stats']=$stats_insert;
                                $final_data_ins['type']=$value['type'];
                                $final_data_ins['sports_id']=$sports_id;
                                $stats_comp_insert = $this->Sports_comp_stats_model->insert($final_data_ins);
                            }
                        }
                        else
                        {
                            $where['id']=$value['id'];
                            $where['sports_id']=$sports_id;
                            $final_data['choosen_stats'] = $value['stats'];
                            $final_data['type']=$value['type'];

                            $stats_update = $this->Sports_stats_model->update($final_data,$where);
                            if(!empty($stats_update))
                            {

                                $where['id']=$value['id'];
                                $where['sports_id']=$sports_id;
                                $final_data['choosen_stats']=$value['id'];
                                $final_data['type']=$value['type'];
                                $final_data['team_type']=$comp_type;
                                $final_data['sports_id']=$sports_id;

                                $stats_comp_update = $this->Sports_comp_stats_model->update($final_data,$where);
                            }
                        }
                    }
                }
                else
                {
                    foreach ($data as $key => $value) {
                        $where['id']=$value->id;
                        $set['value'] = $value->value;
                        $set['compition_id']=$sports_id;
                        $final_data['team_type']=$comp_type;
                        $set['updated_at']=date("Y-m-d H:i:s");
                        $stats_update = $this->Sports_comp_stats_model->update($set,$where);
                    }

                    if(!empty($stats_update))
                    {
                        return $stats_update;
                    }
                    else
                    {
                        return 0;
                    }
                }
            }
        }
    }

    /*Done Y*/
    public function getSportsPickSeasonal(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {
            $postVar = $this->input->post();
            $mem_id = $this->input->post('mem_id');
            $user_id = $this->input->post('user_id');

            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);

            if(!empty($is_member))
            {
                $final_array="";

                $join_arr[] = array(
                    "table_name" => "sports",
                    "cond" => "tbl_sports_main.id = sports.sport_id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_name",
                    "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_season",
                    "cond" => "sports.season_id = sports_season.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_school_level",
                    "cond" => "sports.school_team_id = sports_school_level.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                    "table_name" => "sports_club_level",
                    "cond" => "sports.club_age_id = sports_club_level.id ",
                    "type" => "left"
                );

                $join_arr[] = array(
                        "table_name" => "sports_team_position",
                        "cond" => "sports.school_position_id = sports_team_position.id ",
                        "type" => "left"
                );

                $where_3=array(
                    "mem_id" => $mem_id,
                );

                $select_4="count(tbl_sports_main.sports_id) as total,sports.sport_id,tbl_sports_main.school,tbl_sports_main.club,sports.id as id,sports.school_team_id,sports.club_age_id,sports_name.name,sports_season.season,sports_school_level.name as schoolname,sports_club_level.name as clubname,sports.club_name,sports.school_name,sports_team_position.position,sports.season_id";
                $group_by="sports.sport_id";

                $data_validate = $this->AddSports_model->getAnyData($where_3,$select_4,"","",$join_arr,$group_by);
                if(!empty($data_validate))
                {
                    $where_clb_sports=$final_array_club_season=$final_array_school_season=$join_arr_clb=$final_array_season=$select_scl_sport=$join_arr_sch=$old_sports_id=$school_level=$club_level=$club_sports=$school_sports_id=$club_sports_id=$school_sports=$sport_array=$join_arr2="";
                    $i=0;
                    $final_array=array();
                    foreach ($data_validate as $key => $val) {
                        $final_array_school_season=array();
                        $where_clb_sports="";
                        $slect_stats_check="";
                        if(!empty($val['id']))
                        {   
                            if($val['sport_id'] != $old_sports_id)
                            {
                                $lastsc_name="";
                                $stats_flag="0";
                                $where_stats_check=array(
                                    "sports_id" => $val['sport_id']
                                );
                                $slect_stats_check=$this->Sports_stats_model->getAnyData($where_stats_check);
                                
                                if(!empty($slect_stats_check))
                                {
                                    $stats_flag="1";
                                }
                                if($val['school'] != '' && $val['school'] == 1)
                                {

                                        $school_sports='School-'.$val['name'];
                                        $school_sports_id=$val['sport_id'];
                                        $schooltype=$val['schoolname'];
                                        $position=$val['position'];
                                        $schoolname=$val['school_name'];

                                    if($val['school_team_id'] != '' && $val['sport_id'] != '')
                                    {
                                        $where_sch_sports=array(
                                            "sport_id" => $val['sport_id']
                                        );

                                        $select_scl_sports="tbl_sports_main.*,sports.id,sports.sport_id,sports.season_id,sports.school_team_id,sports_season.season";

                                        $join_arr_sch[] = array(
                                            "table_name" => "sports",
                                            "cond" => "tbl_sports_main.id = sports.sport_id ",
                                            "type" => "left"
                                        );


                                        $join_arr_sch[] = array(
                                            "table_name" => "sports_season",
                                            "cond" => "sports.season_id = sports_season.id ",
                                            "type" => "left"
                                        );
                                        $orderby="sports_season.season ASC";
                                        $select_scl_sport=$this->AddSports_model->getAnyData($where_sch_sports,$select_scl_sports,$orderby,"",$join_arr_sch);
                                        
                                        if(!empty($select_scl_sport))
                                        {
                                            foreach ($select_scl_sport as $skey => $svalue) {
                                                $final_array_school_season[$skey]=array(
                                                    "season_id" => $svalue['season_id'],
                                                    "season" => $svalue['season'],
                                                    "sport_main_id" => $svalue['id']
                                                );

                                            }

                                        }

                                        else
                                        {
                                            $final_array_school_season=[];
                                        }

                                        $final_array[]=array(
                                            "name" => $schoolname,
                                            "school_position" => $position,
                                            "team_type" => $schooltype,
                                            "sports_name" =>$school_sports,
                                            "sports_id" => $school_sports_id,
                                            "stats_added" => $stats_flag,
                                            "check_season" => $final_array_school_season
                                        );
                                        $final_array_school_season="";
                                    }

                                    $join_arr_sch=$where_sch_sports=$select_scl_sport=$final_array_school_season=$school_sports_id="";
                                }

                                if($val['club'] != '' && $val['club'] == 1)
                                {
                                    $i++;
                                    $club_sports='Club-'.$val['name'];
                                    $club_sports_id=$val['sport_id'];
                                    $clubtype=$val['clubname'];
                                    $position=$val['position'];
                                    $clubname=$val['club_name'];

                                    if($val['club_age_id'] != '' && $val['sport_id'] != '')
                                    {
                                        $where_clb_sports=array(
                                             "sport_id" => $val['sport_id']
                                        );

                                        $select_clb_sports="tbl_sports_main.*,sports.id,sports.sport_id,sports.season_id,sports.club_age_id,sports_season.season";

                                         $join_arr_clb[] = array(
                                             "table_name" => "sports",
                                             "cond" => "tbl_sports_main.id = sports.sport_id ",
                                             "type" => "left"
                                         );

                                         $join_arr_clb[] = array(
                                             "table_name" => "sports_season",
                                             "cond" => "sports.season_id = sports_season.id ",
                                             "type" => "left"
                                         );
                                         $orderby="sports_season.season ASC";
                                        $select_clb_sport=$this->AddSports_model->getAnyData($where_clb_sports,$select_clb_sports,$orderby,"",$join_arr_clb);
                                        if(!empty($select_clb_sport))
                                        {
                                            foreach ($select_clb_sport as $ckey => $cvalue) {
                                                $final_array_club_season[$ckey]=array(
                                                    "season_id" => $cvalue['season_id'],
                                                    "season" => $cvalue['season'],
                                                    "sport_main_id" => $cvalue['id']
                                                );
                                            }

                                        }
                                        else
                                        {
                                            $final_array_club_season=[];
                                        }

                                        $final_array[]=array(
                                            "name" => $clubname,
                                            "club_position" => $position,
                                            "team_type" => $clubtype,
                                            "sports_name" =>$club_sports,
                                            "sports_id" => $club_sports_id,
                                            "stats_added" => $stats_flag,
                                            "check_season" => $final_array_club_season
                                        );
                                        $final_array_club_season="";
                                     }
                                    $join_arr_clb=$select_clb_sport=$where_clb_sports=$final_array_club_season=$club_sports_id="";
                                }
                                $old_sports_id=$val['sport_id'];
                            }
                        }
                        $join_arr2=$data_stats="";
                        $i++;

                    }   
                    if(!empty($final_array))
                    {
                        $response['code'] = 1;
                        $response['status'] = "success";
                        $response['data'] = $final_array;   
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";   
                    }

                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = 'No data found';
                }
            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = 'User is either inactive or deleted';
            }

        }
         echo json_encode($response);
    }

    /*Done Y*/
    public function getSportsAllData(){
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('sports', 'Sports', 'trim|required');
        $this->form_validation->set_rules('season', 'Season', 'trim|required');
        $this->form_validation->set_rules('type', 'Type', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {

            $postVar = $this->input->post();
            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];
            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
            if(!empty($is_member))
            {
                $sports = $postVar['sports'];
                $season = $postVar['season'];
                $type = $postVar['type'];


                if(!empty($sports) && !empty($season))
                {
                    $final_array="";
                    
                    $where_3=array(
                        "mem_id" => $mem_id,
                        "tbl_sports_main.id" => $sports,
                        "sports.season_id" => $season
                    );

                    $join_arr[] = array(
                        "table_name" => "sports",
                        "cond" => "tbl_sports_main.id = sports.sport_id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_competition",
                        "cond" => "sports_competition.sports_id = sports.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_name",
                        "cond" => "tbl_sports_main.sports_id = sports_name.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_season",
                        "cond" => "sports.season_id = sports_season.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_school_level",
                        "cond" => "sports.school_team_id = sports_school_level.id ",
                        "type" => "left"
                    );

                    $join_arr[] = array(
                        "table_name" => "sports_club_level",
                        "cond" => "sports.club_age_id = sports_club_level.id ",
                        "type" => "left"
                    );

                    $group_by="sports_competition.id";
                    $select_4="sports_competition.oponent_name,sports.id as spr_id,sports.club_name,sports.school_name,sports_school_level.name as school_team,sports_club_level.name as club_team,sports.school_position_id as school_id,sports.club_position_id as club_id,sports_competition.id,sports_competition.comp_date,";
                    $order_by="sports_competition.oponent_name ASC";
                    $data_validate = $this->AddSports_model->getAnyData($where_3,$select_4,$order_by,"",$join_arr);
                    if(!empty($data_validate))
                    {
                        $final_info['postion_name'] = "";
                        $final_info['postion_id'] = "";
                        $final_info['name'] = "";
                        $final_info['team_name'] = "";
                        $final_info=array();
                        
                        if($type == "School")
                        {

                            if(!empty($data_validate[0]['school_id']) && $data_validate[0]['school_id'] != 0)
                            {
                                $where_cl_pos=array(
                                    "id" => $data_validate[0]['school_id']
                                );
                                $select_cl="id,position";
                                $school_pos=$this->Sports_team_position_model->getAnyData($where_cl_pos,$select_cl);
                                $final_info['postion_name'] = $school_pos[0]['position'];
                                $final_info['postion_id'] = $school_pos[0]['id'];
                            }

                            $final_info['name'] = $data_validate[0]['school_name'];
                            $final_info['team_name'] = $data_validate[0]['school_team'];
                            
                        }
                        else
                        {   
                            if(!empty($data_validate[0]['club_id']) && $data_validate[0]['club_id'] != 0)
                            {
                                $where_sc_pos=array(
                                    "id" => $data_validate[0]['club_id']
                                );
                                $select_sc="id,position";
                                $club_pos=$this->Sports_team_position_model->getAnyData($where_sc_pos,$select_sc);
                                
                                $final_info['postion_name'] = $club_pos[0]['position'];
                                $final_info['postion_id'] = $club_pos[0]['id'];

                            }
                            $final_info['name'] = $data_validate[0]['club_name'];
                            $final_info['team_name'] = $data_validate[0]['club_team'];
                            
                        }

                        $data_val_opo_st=$data_val_opo=$where_opo=$join_arr2=$final_array_dates=$final_array=$data_stats=$lastdate=$final_array_stats=$final_array_date_comp=$final_array_date=$lastname=$school_level=$club_level=$club_sports=$school_sports_id=$club_sports_id=$school_sports=$sport_array=$join_arr2="";
                        $i=0;
                        foreach ($data_validate as $key => $val) {

                            if(!empty($val['id']))
                            {
                                if(($val['oponent_name'] != $lastname))
                                {
                                $final_array[$i]['oponent_name']= $val['oponent_name'];

                                    $where_opo=array(
                                        "oponent_name" => $val['oponent_name'],
                                        "sports_competition.sports_id" => $data_validate[0]['spr_id']
                                    );

                                    $data_validate_opo = $this->Sports_competition_model->getAnyData($where_opo,"","","");
                                    
                                    if(!empty($data_validate_opo))
                                    {
                                        $j=0;
                                        foreach ($data_validate_opo as $dk => $dv) {
                                            
                                            $data_val_opo[$j]['compition_id']=$dv['id'];
                                            $data_val_opo[$j]['date']=$dv['comp_date'];
                                            $data_val_opo[$j]['location']=$dv['location'];
                                            $data_val_opo[$j]['image']="";                              
                                            if(!empty($dv['image']))
                                            {
                                                $image=unserialize($dv['image']);
                                                $data_val_opo[$j]['image']=base_url()."assets/sports/".$mem_id."/".$image[0];
                                            }

                                            if(!empty($dv['id']))
                                            {
                                                $where_stats=array(
                                                    "sports_stats.compition_id" => $dv['id'],
                                                );

                                                $join_arr2[]=array(
                                                    "table_name" => "sports_stats_main",
                                                    "cond" => "sports_stats_main.id = sports_stats.choosen_stats ",
                                                    "type" => "left"
                                                );
                                                $select="sports_stats_main.choosen_stats,sports_stats.value,sports_stats.id as id,sports_stats.compition_id";
                                                $data_validate_opo_stats = $this->Sports_comp_stats_model->getAnyData($where_stats,$select,"","",$join_arr2);

                                                if(!empty($data_validate_opo_stats))
                                                {
                                                    $k=0;
                                                    foreach ($data_validate_opo_stats as $dsk => $dsv) {
                                                       
                                                        if($dsv['compition_id'] == $dv['id'])
                                                        {
                                                            $data_val_opo_st[$k]['id']=$dsv['id'];
                                                            $data_val_opo_st[$k]['stats']=$dsv['choosen_stats'];
                                                            $data_val_opo_st[$k]['value']=$dsv['value'];
                                                        }
                                                        $k++;
                                                    }
                                                    $data_val_opo[$j]['stats_data']=$data_val_opo_st;
                                                }
                                                else
                                                {
                                                    $data_val_opo[$j]['stats_data']=[];
                                                }

                                                $join_arr2=$data_validate_opo_stats=$where_stats=$data_val_opo_st="";
                                            }
                                            $j++;
                                        }

                                    }

                                    $lastname=$val['oponent_name'];
                                    $final_array[$i]['comp_data']=$data_val_opo;
                                    $data_val_opo="";
                                    $i++;
                                }
                            }
                        }
                        $response['code'] = 1;
                        $response['status'] = "success";
                        $response['data'] = $final_info;
                        if(!empty($final_array))
                        {
                            $response['data']['comp_info']=$final_array;
                        }
                        else
                        {
                            $response['data']['comp_info']=[];
                        }
                    }
                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = "No data found";
                    }
                }
            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "User is either inactive or deleted";
            }
            echo json_encode($response);
        }
    }

    /*Done Y*/
    public function getStatsList(){
        $this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {
                $sports_id=$this->input->post('sports_id');
                $is_sports_arr=array(
                    "id" => $sports_id
                );

                $is_sports_id=$this->Sports_name_model->getAnyData($is_sports_arr);
                if(!empty($is_sports_id))
                {
                    $is_stats_arr=array(
                        "sports_id" => $sports_id
                    );
                    
                    /*
                        @Change: Order By added
                    */

                    $orderby="priority ASC";
                    $is_stats=$this->Sports_stats_master_model->getAnyData($is_stats_arr,"",$orderby);
                    
                    if(!empty($is_stats))
                    {
                        $finalArr="";
                        foreach ($is_stats as $key => $value) {
                            $finalArr[$key]['name']=$value['name'];
                            $finalArr[$key]['id']=$value['id'];
                            if($value['type'] == 0) {$type="Number";}
                            if($value['type'] == 1) {$type="Percent";}
                            if($value['type'] == 2) {$type="Decimal";}
                            $finalArr[$key]['type']=$type;
                            $finalArr[$key]['is_dependant']=0;
                            $finalArr[$key]['dependancy']=[];
                            $finalArr[$key]['formula']="";
                            if($value['is_dependant'] == 1)
                            {
                                $finalArr[$key]['is_dependant']=1;
                                $dependancy=unserialize($value['dependant_stats']);                                
                                
                                $j=0;
                                $finalArrSt="";
                                if(!empty($dependancy))
                                {
                                    foreach ($dependancy as $kd => $vd) {
                                        $finalArrSt[$j]=$vd;
                                        $j++;
                                    }
                                    $finalArr[$key]['dependancy']=$finalArrSt;
                                }
                                else
                                {
                                    $finalArr[$key]['dependancy']=[];
                                }
                                //$newStr = str_replace('\"', '', $value['formula']);
                                
                                $finalArr[$key]['formula']= trim($value['formula']); 
                                
                            }
                            $finalArr[$key]['user_help']=trim($value['user_help']);

                        }
                        $responseArr['stat_list']=$finalArr;
                        $response['code'] = 1;
                        $response['status'] = "success";
                        $response['data'] = $responseArr;
                    }

                    else
                    {
                        $response['code'] = 0;
                        $response['status'] = "error";
                        $response['message'] = "No data found";
                    }
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = "No data found";
                }

                        
            echo json_encode($response);
        }
    }

    /*Start : Get Season Dates*/
    public function GetSeasonDates(){
        $this->form_validation->set_rules('season_id', 'Season_id', 'trim|required');
        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {
            $season_id=$this->input->post('season_id');     
            
            $where=array(
                    "id" => $season_id,
                    "is_active" => 1
            );
            
            $is_season=$this->Sports_season_model->getAnyData($where);
            //pr($is_season);die;
            if(!empty($is_season))
            {
                $final_date_arr="";
                if(!empty($is_season[0]['season']))
                {   
                    if(is_numeric($is_season[0]['season']) == 1)
                    {
                        for ($i=1; $i <=12 ; $i++) {    

                            if(strlen($i) == 1) {
                                $i="0".$i;
                            }

                            $final_date_arr[]=$is_season[0]['season']."-".$i;       
                        }
                    } 

                    else
                    {
                        $check=explode('-', $is_season[0]['season']);

                        $final_date_arr2="";
                        $final_date_arr1="";
                            if(strpos($is_season[0]['season'], '-') !== false) {

                                $year=explode('-', $is_season[0]['season']);
                                $first_year=$year[0];
                                $second_year=$year[1];
                                if(!empty($first_year))
                                {
                                    for ($i=1; $i <=12 ; $i++) {    
                                        if(strlen($i) == 1) {
                                            $i="0".$i;
                                        }
                                        $final_date_arr1[]=$year[0]."-".$i;       
                                    }
                                }

                                if(!empty($second_year))
                                {
                                    for ($i=1; $i <=12 ; $i++) {    
                                        if(strlen($i) == 1) {
                                            $i="0".$i;
                                        }
                                        $final_date_arr2[]=$year[1]."-".$i;       
                                    }
                                }

                                $final_date_arr=array_merge($final_date_arr1,$final_date_arr2);

                            } else {

                                $year=explode(' ', $is_season[0]['season']);
                                $first_year=$year[0];
                                $second_year=$year[1];

                                if($first_year == "winter")
                                {
                                    $final_date_arr=array(
                                        $second_year."-12",
                                        $second_year."-01",                                        
                                        $second_year."-02"                                        
                                    );
                                }

                                if($first_year == "spring")
                                {
                                    $final_date_arr=array(
                                        $second_year."-03",
                                        $second_year."-04",                                        
                                        $second_year."-05"                                        
                                    );
                                }

                                if($first_year == "summer")
                                {
                                    $final_date_arr=array(
                                        $second_year."-06",
                                        $second_year."-07",                                        
                                        $second_year."-08"                                        
                                    );
                                }

                                if($first_year == "fall")
                                {
                                    $final_date_arr=array(
                                        $second_year."-09",
                                        $second_year."-10",                                        
                                        $second_year."-11"                                        
                                    );
                                }


                            }


                    }
                    
                    $response['code'] = 1;
                    $response['status'] = "success";
                    $response['data'] = $final_date_arr;  
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = "No data found";     
                }
            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "Pleas Enter Correct Season";     
            }

            echo json_encode($response);
        }
    }
    /*End : Get Season Dates*/


    /*Start: Delete Sports*/
    /*
        --> If Sports haven't added Compition then only delete the sports
        --> Get Sport id and Delete the sport 
        --> Check if last season then Delete Full Sport Data.
    */
    function DeleteSports(){
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
        $this->form_validation->set_rules('season_id', 'Season_id', 'trim|required');
        
        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);
        } else {
            $postVar = $this->input->post();
            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];
            
            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
            if(!empty($is_member))
            {
                $sports = $postVar['sports_id'];
                $season = $postVar['season_id'];

                /*
                ->  Check if there any compition or not with this sport
                */
                $where_sports=array(
                    "sports.id" => $sports,
                    "season_id" => $season
                );

                $join_arr[] = array(
                    "table_name" => "sports_competition",
                    "cond" => "sports_competition.sports_id = sports.id ",
                    "type" => "inner"
                );

                $sports_data = $this->Sports_model->getAnyData($where_sports,"","","",$join_arr);

                /*If not compition data saved delete the sport data*/

                if(empty($sports_data))
                {
                    $where_sport_data=array(
                        "sports.id" => $sports,
                        "season_id" => $season
                    );

                    $join_mainsport[] = array(
                        "table_name" => "tbl_sports_main",
                        "cond" => "tbl_sports_main.id = sports.sport_id",
                        "type" => "left"
                    );
                    $delete_sport = $this->Sports_model->getAnyData($where_sport_data,"","","",$join_mainsport);
                    $did="";
                    if(!empty($delete_sport))
                    {
                        $did=$delete_sport[0]['sport_id'];
                        $where_sprt_cnt=array(
                            "sport_id" => $did 
                        );

                        $delete_sport_count_data = $this->Sports_model->getAnyData($where_sprt_cnt);
                        $del_cnt=count($delete_sport_count_data);
                        if($del_cnt == 1)
                        {
                        /*If last sport left then delete all stats and sports detail*/
                        $sports_data = $this->Sports_model->delete($where_sports);
                        if(!empty($sports_data))
                        {
                            $where_main_sports=array(
                                "id" => $delete_sport[0]['sport_id']
                            );

                            $where_main_sports_stats=array(
                                "sports_id" => $delete_sport[0]['sport_id']
                            );

                            $main_sport = $this->AddSports_model->delete($where_main_sports);
                            /*$main_sport_stats = $this->Sports_stats_model->delete($where_main_sports_stats,'sports_id');*/
                            //$main_sport_stats = $this->Sports_stats_model->delete($delete_sport[0]['sport_id'],$where_main_sports_stats);
                            if(!empty($main_sport))
                            {
                                $response['code'] = 1;
                                $response['status'] = "success";
                                $response['message'] = "Sport deleted successfully";
                            }
                        }    
                    }
                    else
                    {
                        /*Delete the Sports*/
                        $sports_data = $this->Sports_model->delete($where_sports);
                        if(!empty($sports_data))
                        {
                            $response['code'] = 1;
                            $response['status'] = "success";
                            $response['message'] = "Sport deleted successfully";
                        }
                    }      

                    }
                    $delete_sport_count = count($delete_sport);
                    
                          
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = "Already sport used in compition";
                }
            }

            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "User either deleted or inactive"; 
            }

            echo json_encode($response);
        }
    }

    /*End: Delete Sports*/

    /*Insert season*/
    function SeasonList(){
        for($i=2014; $i<=2034; $i++)
        {
            for ($j=0; $j <12 ; $j++) {
                   
                     if($j==0)
                     {
                        $insert_mo['season']=$i." JAN";
                     }
                     if($j==1)
                     {
                        $insert_mo['season']=$i." FEB";
                     }if($j==2)
                     {
                        $insert_mo['season']=$i." MAR";
                     }if($j==3)
                     {
                        $insert_mo['season']=$i." APR";
                     }if($j==4)
                     {
                        $insert_mo['season']=$i." MAY";
                     }if($j==5)
                     {
                        $insert_mo['season']=$i." JUN";
                     }if($j==6)
                     {
                        $insert_mo['season']=$i." JUL";
                     }if($j==7)
                     {
                        $insert_mo['season']=$i." AUG";
                     }if($j==8)
                     {
                        $insert_mo['season']=$i." SEP";
                     }if($j==9)
                     {
                        $insert_mo['season']=$i." OCT";
                     }if($j==10)
                     {
                        $insert_mo['season']=$i." NOV";
                     }if($j==11)
                     {
                        $insert_mo['season']=$i." DEC";
                     }  
                    $inser_season=$this->Sports_season_model->insert($insert_mo);     
            }
           
        }
    }

    /*Start: Delete Compitition*/
    function DeleteCompData()
    {
        $this->form_validation->set_rules('user_id', 'User_id', 'trim|required');
        $this->form_validation->set_rules('mem_id', 'Mem_id', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports_id', 'trim|required');
        $this->form_validation->set_rules('season_id', 'Season_id', 'trim|required');
        $this->form_validation->set_rules('comp_id', 'Comp_id', 'trim|required');

        if ($this->form_validation->run() === FALSE) {
            $response['code'] = 0;
            $response['status'] = "error";
            $response['message'] = 'Please enter all fields';
            echo json_encode($response);die;
        } else {

            $postVar = $this->input->post();
            $mem_id = $postVar['mem_id'];
            $user_id = $postVar['user_id'];
            $sports=$postVar['sports_id'];
            $season=$postVar['season_id'];
            $comp_id=$postVar['comp_id'];


            $mem_array = array(
                "id" => $mem_id,
                "user_id" => $user_id,
                "is_active" => 1,
                "is_deleted" => 0
            );

            $is_member=$this->FamilyInfo_model->family_member_data($mem_array);
            if(!empty($is_member))
            {
                $where_sports=array(
                    "sports.id" => $sports,
                    "sports.season_id" => $season,
                    "sports_competition.id" => $comp_id
                );

                $join_arr[] = array(
                    "table_name" => "sports_competition",
                    "cond" => "sports_competition.sports_id = sports.id ",
                    "type" => "inner"
                );
                $select="sports_competition.id as compid";
                $delete_sport = $this->Sports_model->getAnyData($where_sports,$select,"","",$join_arr);
                if(!empty($delete_sport))
                {
                    $delete_id=$delete_sport[0]['compid'];
                    $where_del=array(
                        "id" =>$delete_id
                    );

                    $where_del_data=array(
                        "compition_id" =>$delete_id
                    );
                    $delete_comp=$this->Sports_competition_model->delete($where_del);
                    $delete_comp_data=$this->Sports_comp_stats_model->delete($where_del_data,"compid");

                    $response['code'] = 1;
                    $response['status'] = "success";
                    $response['message'] = "Compitition deleted successfully";
                }
                else
                {
                    $response['code'] = 0;
                    $response['status'] = "error";
                    $response['message'] = "No data found";
                }
            }
            else
            {
                $response['code'] = 0;
                $response['status'] = "error";
                $response['message'] = "User either deleted or inactive"; 
            }
            echo json_encode($response);
        }
    }
    /*End: Delete Compitition*/

}
?>