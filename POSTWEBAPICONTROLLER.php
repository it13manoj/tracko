<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class POSTWEBAPICONTROLLER extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model('AUTHOMODEL');
   	$this->load->library('globals');
  }


public 	function singupusers(){
		 $token=$_POST['token']; 
		 $MapApi= $this->globals->MapAipKey();
		if($this->globals->set_token($token)=='true'){
		if(isset($_POST['name'])&& $_POST['name']!="")
			$data['uname']=$_POST['name'];
		if(isset($_POST['mobile'])&& $_POST['mobile']!="")
			$data['mobile']=$_POST['mobile'];
		if(isset($_POST['email'])&& $_POST['email']!="")
			$data['email']	=$_POST['email'];
		if(isset($_POST['type'])&& $_POST['type']!="")
			$data['utype']	=$_POST['type'];
		if(isset($_POST['auth_id'])&& $_POST['auth_id']!="")
			$data['auth_id']=$_POST['auth_id'];
		if(isset($_POST['device_token'])&& $_POST['device_token']!="")
			$data['device_token'] =$_POST['device_token'];
		if(isset($_POST['password'])&& $_POST['password']!="")
			$data['upassword']= $this->globals->password_check($_POST['password']);
			$data['uentdt']= date('Y-m-d H:i:s');

			$validat=$this->AUTHOMODEL->insert_validation('users',$_POST['email'],$_POST['mobile'],$_POST['status']);
			$valid= explode(',', $validat);
           
			if($valid[0]=='true'){
                if($valid[2]!=0 && $valid[2]!=''){
                    	$this->AUTHOMODEL->Facebooklogin($_POST['email'],$_POST['device_token'],$_POST['password'],$_POST['token']);
                }else{
                  $array=array('status'=>0,
					'message'=>$valid[1].' Already Registered ');
				echo json_encode($array);  
                }
				
			}else{
				$this->AUTHOMODEL->record_insert('users',$data);

				$data1['uid']= $this->db->insert_id();
				if($_POST['type']==1){
					$data1['veh_name']=$_POST['name'];
					if(isset($_POST['mobile'])&& $_POST['mobile']!="")
					$data1['veh_contectno']=$_POST['mobile'];
					if(isset($_POST['price']) && $_POST['price']!=''){
						$data1['price']=$_POST['price'];
					}
				    
					if(isset($_POST['categoryid']) && $_POST['categoryid']!=''){
						$data1['veh_catid']=$_POST['categoryid'];
					}
                    if(isset($_POST['serviceid']) && $_POST['serviceid']!=''){
						$data1['veh_service']=$_POST['serviceid'];
					}
					if(isset($_POST['address']) && $_POST['address']!=''){
					$address=$_POST['address'];
					$address = str_replace(" ", "+", $address);
					$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$MapApi");
					$json = json_decode($json);
					$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
					$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
				}
					$data1['veh_latitude']=$lat;
					$data1['veh_longitude']=$long;
					$data1['veh_address']=$_POST['address'];
					$data1['veh_entydt']=date('Y-m-d H:i:s');
					$this->AUTHOMODEL->record_insert('vehicle',$data1);
					
				}elseif($_POST['type']==0){

					if(isset($_POST['address']) && $_POST['address']!=''){
					$address=$_POST['address'];
					$address = str_replace(" ", "+", $address);
					$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$MapApi");
					$json = json_decode($json);
					$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
					$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
				}
					$data1['latitude']=$lat;
					$data1['longitude']=$long;
					$data1['address']=$_POST['address'];
					$data1['name']=$_POST['name'];
					if(isset($_POST['mobile'])&& $_POST['mobile']!="")
					$data1['mobile']=$_POST['mobile'];
					$data1['email']	=$_POST['email'];
					$data1['enterydt']= date('Y-m-d H:i:s');
					$this->AUTHOMODEL->record_insert('customer',$data1);

					
				}
                $condition['email'] =$_POST['email'];
                $condition['upassword']=$this->globals->password_check($_POST['password']);
                $customerrec= $this->AUTHOMODEL->getallbytwocond('*','users',$condition);
                if($customerrec>0){
					foreach($customerrec as $ft){
						$arr[]=$ft;
					
					}
               
				$array=array('status'=>1,
					'message'=>'Your are Successfully registered ','data'=>$arr);
				echo json_encode($array);
           
				  
			}
            }
		}else{
			$array=array(
				'Status'=>0,
				'Message'=>"4040 Error"
			);
		}
}



public function userlogin(){
	
			$condition=array();
			$token=$_POST['token'];
			if($this->globals->set_token($token)=='true'){
			$get_data=$_POST['logintype'];
			if (filter_var($get_data, FILTER_VALIDATE_EMAIL)) {
				$condition['email'] =$get_data;
				$cond="email";
			}else{
				$condition['mobile'] = $get_data;
				$cond="mobile";
			}
		
            $logindata['device_token']=$_POST['device_token'];
                
            $condition['upassword']=$this->globals->password_check($_POST['password']);
			$customerrec= $this->AUTHOMODEL->getallbytwocond('*','users',$condition);
			
            $this->AUTHOMODEL->editonerecords('users',$cond,$get_data,$logindata);
		
			if($customerrec>0){
					foreach($customerrec as $ft){
						$arr[]=$ft;
						if($ft['utype']==1){
							$userid=$ft['uid'];
							$this->AUTHOMODEL->userlog($userid);
							$lat=$_POST['lat'];
							$long=$_POST['long'];
							$this->AUTHOMODEL->loglanlong($userid,$lat,$long,1);
						}
					
					}
                $array=array(
							"status"=>1,
							"message"=>'You Are Successfully Login',
                            "data"=>$arr);
				}else{
						$array=array("status"=>'0',
									"message"=>'Invalid Username and Password');
					 }
					}else{
						$array=array("status"=>'0',
									"message"=>'Invalid Username and Password');
					}
                   
		 		echo  json_encode($array);
        }


	public function ShowAllCategory (){
		$token=$_POST['token'];
		if($this->globals->set_token($token)=='true'){
			$details=$this->AUTHOMODEL->AllCustomerDetails('category');
			foreach($details as $ft){
			$arr[]=$ft;
			}
			$array=array('status'=>1,
					'message'=>'Successfully Created Category','data'=>$arr);
				echo $data= json_encode($array);
		}else{
            $array=array('status'=>0,
					'message'=>'Not Create Category');
				echo $data= json_encode($array);
		}


	}

	public function ListOfTechnician(){
             $token=$_POST['token'];
             $u_id=$_POST['userid'];
             $Serviceid1=ltrim($_POST['categoryid'],'[');
           	  $id= str_replace(' ','',rtrim($Serviceid1,']'));
            $lag=$_POST['lag'];
            $log=$_POST['log'];
		if($this->globals->set_token($token)=='true'){

	$servid=explode(',',$id);

  	$size=sizeof($servid); 
  	if($id==''){
  		$array=array('status'=>0,
					'message'=>'No Technician avaliable');
				echo $data= json_encode($array);
			}else{
  	$condition='';
  	$arrayss=array();
  	for ($i=0; $i <$size ; $i++) { 
  		  $condition.=" and tech_serviceid=".$servid[$i];
  	}
  		$technician=$this->AUTHOMODEL->FourTableJoin("select tech_technicianid as id from technician_list where 0=0 and tech_technicianid!=$u_id and tech_serviceid in($id) group by tech_technicianid having count(tech_technicianid)>=$size");
  		
  			if(!empty($technician)){
  					foreach($technician as $ft){
  			  		 $tid=$ft['id'];
  					$technicians=$this->AUTHOMODEL->FourTableJoin("select count(tech_technicianid) as tids,tech_technicianid from technician_list where 0=0 and tech_technicianid!=$u_id and  tech_technicianid =$tid group by tech_technicianid HAVING tids>=$size");
  					//echo $this->db->last_query();

  			if(!empty($technicians)){
					foreach($technicians as $techid){
					$ids=$techid['tech_technicianid'];
  					array_push($arrayss,$ids);
  				}
  			}

  		}
  	}

  	$alltechid=implode(',', $arrayss);
  	if($alltechid==''){

  		$array=array('status'=>0,
					'message'=>'No Technician avaliable');
				echo $data= json_encode($array);


  	}else{

		//$technician=$this->AUTHOMODEL->FourTableJoin("select u.uid,u.uname from users u left join  vehicle v on v.uid=u.uid where veh_catid = $id and veh_status=1 and u.status=1 and u.utype=1");
        
		/*$technician=$this->AUTHOMODEL->FourTableJoin("select ( 3959 * acos( cos( radians($lag) ) * cos( radians( veh_latitude ) ) * cos( radians( veh_longitude ) - radians($log) ) + sin( radians($lag) ) * sin( radians( veh_latitude ) ) ) ) AS distance,u.uid,u.uname from users u left join  vehicle v on v.uid=u.uid where veh_service = $id and veh_status=1 and u.status=1 and u.utype=1 HAVING distance < 25");
        */
        
        $technician=$this->AUTHOMODEL->FourTableJoin("select (6371 * 2 * ASIN(SQRT(POWER(SIN(('".$lag."
          ' - ABS(veh_latitude)) * PI() / 180 / 2), 2) + COS('".$log."
          ' * PI() / 180) * COS(ABS(veh_latitude) * PI() / 180) * POWER(SIN(('".$log."
          ' - veh_longitude) * PI() / 180 / 2), 2)))) AS distance,u.uid,u.uname from users u left join  vehicle v on v.uid=u.uid where  0=0 and u.uid in($alltechid) and veh_status=1 and u.status=1 and u.utype=1 group by u.uid HAVING distance < 25");
        
        
        
        
        
        
		if(empty($technician)){
			
			$array=array('status'=>0,
					'message'=>'No Technician avaliable');
				echo $data= json_encode($array);	
			
		}else{
		foreach ($technician as $key => $value) {
			$arr[]=$value;
		}
		$array=array('status'=>1,
					'message'=>'technician List ','data'=>$arr);
				echo $data= json_encode($array);

	   }
    }
	}
}

	}


public function TechClintlocaton(){
	$token=$_POST['token'];
  		$id=$_POST['userid'];
  		if($this->globals->set_token($token)=='true'){
  			$technician=$this->AUTHOMODEL->FourTableJoin("select c.address as useraddress ,v.veh_address as technicianaddress FROM users u left JOIN customer c on c.uid=u.uid left JOIN vehicle v on v.uid=u.uid where u.uid=$id");
            
  			//$technician=$this->AUTHOMODEL->FourTableJoin("select c.latitude,c.longitude,c.address as cadd ,v.veh_address as vadd FROM users u left JOIN customer c on c.uid=u.uid left JOIN vehicle v on v.uid=u.uid where u.uid=$id");
            
            
            
  			if(empty($technician)){
  				$array=array('status'=>0,
					'message'=>'No Records Found ');
				echo $data= json_encode($array);
  				
  			}else{
  			if($technician[0]['useraddress']!=''){
  				$address=$technician;
  				$array=array('status'=>1,
					'message'=>'technician Address ','data'=>$address);
				echo $data= json_encode($array);
  			}else{
  				$address=$technician;
  				$array=array('status'=>1,
					'message'=>'technician Address ','data'=>$address);
				echo $data= json_encode($array);
				}	
  		}
    }
}


/*
    
  public function UserTakeOrder(){
		$token=$_POST['token'];
		if($this->globals->set_token($token)=='true'){
		$data['ord_catid'] =$_POST['categoryid'];
		$data['ord_custid'] =$_POST['userid'];
		$data['ord_velid'] =$_POST['technicianid'];
		$data['ord_time'] =$_POST['ordertime'];
		$data['ord_entdy'] =date('Y-m-d H:i:s');
		$data['ord_straddress'] =$_POST['technicianaddress'];
		$data['ord_endadd'] =$_POST['usersaddress'];
		if(isset($_POST['name']) && $_POST['name']!=''){
			$data['name']=$_POST['name'];
		}
		if(isset($_POST['contact']) && $_POST['contact']!=''){
			$data['contact']=$_POST['contact'];
		}
		$this->AUTHOMODEL->record_insert('order_now',$data);
		$data1['map_ordid']=$this->db->insert_id();
		$address = $_POST['technicianaddress'];
		 $address = str_replace(" ","+",$address); 
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=AIzaSyCgMWVCNSd7JrIqIRCc3wRyDZttXFIvtMI");
		$json = json_decode($json);
	 	$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
	 	$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
	 	$data1['map_latitude']=$lat;
	 	$data1['map_longtitute']=$long;
	 	$data1['map_entdt']=date('Y-m-d H:i:s');
	 	$this->AUTHOMODEL->record_insert('vel_map',$data1);

		$array=array(
							"status"=>1,
							"message"=>'Successfully Created Order'
						);
		echo json_encode($array);
		}else{
		echo " Error 404 ";
		exit();
	}
}
*/

    
    
  public function UserTakeOrder(){
		$token=$_POST['token'];
		$MapApi= $this->globals->MapAipKey();
		if($this->globals->set_token($token)=='true'){   
          
  if(isset($_FILES['video']['name']) && $_FILES['video']['name']!=''){
     	$path = './uploads/video';  
        $folder = 'video';
        if(!is_dir($path))
        mkdir($path);
        $path2=$path."/";
        if(!is_dir($path2))
        mkdir($path2);
        if ( ! $path || ! $path2 )
        return;
 		if(!empty($_FILES["video"]['name'])){
        $target_dir = './uploads/'.$folder.'/';
            $timestamp= date('YmdHis');
            $filename=$_FILES["video"]["name"];
            @$extension=end(explode(".", $filename));
             $newfilename=$timestamp.".".$extension;
            $target_file = $target_dir . basename($newfilename);
             $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
             	$arr[]=$target_dir.$newfilename;  
          }
      
      $data['order_video'] =json_encode($arr);
  }
}     
            
            $arraydata[]='';
            
               
        if(isset($_POST['images_string']) && $_POST['images_string']!=''){
            $encode=$_POST['images_string'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'1' .".".$extension;
            $path = './uploads/image/'.$newfilename;  
            $file=fopen($path, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path);
            /*$arr1[]= $path;
            $data['order_img'] =json_encode($arr1);*/
        }
            if(isset($_POST['images_string_one']) && $_POST['images_string_one']!=''){
            $encode=$_POST['images_string_one'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'2' .".".$extension;
            $path1 = './uploads/image/'.$newfilename;  
            $file=fopen($path1, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path1);
            /*$arr1[]= $path;
            $data['order_img'] =json_encode($arr1);*/
        }
            if(isset($_POST['images_string_two']) && $_POST['images_string_two']!=''){
            $encode=$_POST['images_string_two'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'3' .".".$extension;
            $path2 = './uploads/image/'.$newfilename;  
            $file=fopen($path2, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path2);
           /* $arr1[]= $path;
            $data['order_img'] =json_encode($arr1);*/
        }
            if(!empty($arraydata)){
           $data['order_img'] =json_encode($arraydata); 
            }
            
            
       
        $data['order_mileage'] =$_POST['Mileage'];       
        $data['order_serviceid'] =$_POST['ServiceID'];       
        $data['ord_catid'] =$_POST['categoryid'];
        $data['description'] =$_POST['description'];

		$data['ord_custid'] =$_POST['userid'];
		$data['order_estamatetime'] =$_POST['estamatetime'];

		$data['ord_velid'] =$_POST['technicianid'];  
		$data['order_time'] =$_POST['Time'];
		$data['ord_time'] =date('Y-m-d H:i:s',strtotime($_POST['ordertime']));
		$data['ord_entdy'] =date('Y-m-d H:i:s');
		$data['ord_straddress'] =$_POST['technicianaddress'];
		$data['ord_endadd'] =$_POST['usersaddress'];
        $data['order_modified_datetime']=date('Y-m-d',strtotime($_POST['ordertime']));
        $data['order_modify_time']=$_POST['Time'];
		if(isset($_POST['name']) && $_POST['name']!=''){
			$data['name']=$_POST['name'];
		}
		if(isset($_POST['contact']) && $_POST['contact']!=''){
			$data['contact']=$_POST['contact'];
		}
		/* End */

					/* ORDER LANG LONG */
					$address =urlencode ( $_POST['usersaddress']);
					$address = str_replace(" ", "+", $address);
					$url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=England&key=$MapApi";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					$response = curl_exec($ch);
					curl_close($ch);
					$response = json_decode($response);
					$lat = $response->results[0]->geometry->location->lat;
					$long = $response->results[0]->geometry->location->lng; 
					$data['order_lang']=$lat;
					$data['order_long']=$long;
					/* END*/

		 $this->AUTHOMODEL->record_insert('order_now',$data); 
            $orderid=$this->db->insert_id();
            $serv['s_ordid']=$orderid;
            $serv['s_service_id']=$_POST['ServiceID']; ;
            $serv['s_techid']=$_POST['technicianid']; ;
            $this->AUTHOMODEL->record_insert('user_service',$serv);

			$data1['map_ordid']=$orderid;
            
            $address =urlencode ( $_POST['usersaddress']);
            $address = str_replace(" ", "+", $address);
          
            $url = "https://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=England&key=$MapApi";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response);

            $lat = $response->results[0]->geometry->location->lat;
            $long = $response->results[0]->geometry->location->lng;  
            
		/*$address = "Chandigarh, Daria, Chandigarh, 160102";
		 $address = str_replace(" ","+",$address); 
		$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=urlencode($address)&key=AIzaSyCgMWVCNSd7JrIqIRCc3wRyDZttXFIvtMI");
		$json = json_decode($json);
	 	$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
	 	$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};*/
            
            
            
	 	$data1['map_latitude']=$lat;
	 	$data1['map_longtitute']=$long;
	 	$data1['map_entdt']=date('Y-m-d H:i:s');
	 	//$this->AUTHOMODEL->record_insert('vel_map',$data1);
            
                                $data2['uid']=$_POST['technicianid'];
                                $data3['uid']=$_POST['userid'];
                                $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$data2);
                                $username=$this->AUTHOMODEL->fiendfield('users','uname',$data3);
                                $name=ucwords(strtolower($username['uname']));
                                $devicetoken=$device_token['device_token'];
                                $title="Order Status";
                                $message=$name.' Order to You Please accept the order';
                                $arrss[]=array("OrderId"=>$orderid);

		$array=array(
							"status"=>1,
							"message"=>'Successfully Created Order',
                            "data"=>$arrss
						);
		echo json_encode($array);
						$uid=$_POST['technicianid'];
            $this->AUTHOMODEL->sendNotification($devicetoken, $message,$title,$orderid,$uid);
		}else{
		$array=array(
							"status"=>0,
							"message"=>'Order is not Create'
						);
		echo json_encode($array);
	}
}

    
    
    
    
    
    
    
    
    
    
    
    
    


public function ShowUsersOrderCustomer(){
	$token=$_POST['token'];
		$id=$_POST['userid'];
		$data['uid'] =$id;
		$itmes='';
		
		if($this->globals->set_token($token)=='true'){
			$valid=$this->AUTHOMODEL->ValidToAnyId('users',$data);
			if($valid){

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.id=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.veh_id=o.ord_velid where 0=0 and ord_custid=$id and ord_status!=2  order by veh_name,ord_time,cat_name ASC");*/

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=cu.uid left join users us on us.uid =v.uid where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2  order by date_format(ord_time,'%Y-%m-%d') DESC");
                */
                
                
                
               $details=$this->AUTHOMODEL->FourTableJoin("
select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,
o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,
o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,
o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,
o.ord_status as order_status,o.ord_endadd as order_end_address,
o.ord_straddress as order_start_address,o.order_img as ordreimage,
o.order_video as ordervideo,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,
user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,
user.utype as usertype,v.veh_id,v.veh_name as technician_name,
v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,
v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,
v.veh_status as techinican_status,v.price as technician_price,v.veh_service as serviceId,
o.order_modify_time,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,
n.notice_uid as reasonuserid,n.notice_data reasoin,n.notice_status as detetestatus,
o.order_feedback as feedback,usl.s_service_id from order_now o 
left join users user on user.uid=o.ord_custid 
left join category c on c.cat_id = o.ord_catid 
left join vehicle v on v.uid=o.ord_velid 
left join users u on u.uid=user.uid 
left join users us on us.uid =v.uid 
left join tech_serviecs ts on ts.serv_id=v.veh_service 
left join notification_type n on n.notice_orderid=o.ord_id 
left join user_service usl on usl.s_ordid=o.ord_id 
where 0=0 and (o.ord_custid=$id) and ord_status !=4  group by o.ord_id order by o.ord_id desc;

");
                
                	

				if(empty($details)){
						$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
				}else{
					
				foreach($details as $ft){
					$Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
					if(!empty($itemid)){
                    $data=" and serv_id in($itemid)";
                    $getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
                    $ft['service_name']=implode(',',$getitmen['ServiceName']);
                     $ft['technician_price']=$getitmen['totalAmount'];
                    }
					$arr[]=$ft;
				}

				$array=array(
							"status"=>1,
							"message"=>'All Records',"data "=>$arr
						);
				echo  json_encode($array);
                }
			}else{
					$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
			}

		}else{
			$array=array(
							"status"=>0,
							"message"=>'404 Error '
						);
					echo  json_encode($array);
		}
}

    
    
    
    
    
    
    
    
    


public function getparticularordersbyuser(){
	$token=$_POST['token'];
		$id=$_POST['userid'];
		$data['uid'] =$id;
		if($this->globals->set_token($token)=='true'){
			$valid=$this->AUTHOMODEL->ValidToAnyId('users',$data);
			if($valid){

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.id=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.veh_id=o.ord_velid where 0=0 and ord_custid=$id and ord_status!=2  order by veh_name,ord_time,cat_name ASC");*/

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=cu.uid left join users us on us.uid =v.uid where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2  order by date_format(ord_time,'%Y-%m-%d') DESC");
                */
                
                
                
               $details=$this->AUTHOMODEL->FourTableJoin("

select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,
o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,o.order_lang
,o.order_long,o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,
o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status as order_status,
o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.order_img as ordreimage,
o.order_video as ordervideo,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as id ,
user.uname as name,user.mobile as mobile,user.email as  email,user.utype as usertype,v.veh_id,
v.veh_service as serviceId,v.veh_name as technician_name,v.veh_contectno as technician_mobileno,
v.veh_number,v.veh_address as technician_address,v.veh_latitude as technician_latitude,v.veh_longitude
 as techinician_longitude,v.veh_status as techinican_status,v.price as technician_price,o.order_modify_time,
 o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,n.notice_uid as reasonuserid,
 n.notice_data reasoin,n.notice_status as detetestatus,o.order_feedback as feedback,usl.s_service_id 
 from order_now o 
 left join users user on user.uid=o.ord_custid 
 left join category c on c.cat_id = o.ord_catid 
 left join vehicle v on v.uid=o.ord_velid 
 left join users u on u.uid=user.uid 
 left join users us on us.uid =v.uid 
 left join tech_serviecs ts on ts.serv_id=v.veh_service 
 left join notification_type n on n.notice_orderid=o.ord_id 
 left join user_service usl on usl.s_ordid=o.ord_id  
 where 0=0 and (o.ord_velid=$id) and ord_status in(0,1,3) group by o.ord_id  order by o.ord_id DESC

");
                
                
                	

				if(empty($details)){
						$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
				}else{
				foreach($details as $ft){
					$Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
					if(!empty($itemid)){
					$data=" and serv_id in($itemid)";
					$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
					$ft['service_name']=implode(',',$getitmen['ServiceName']);
					$ft['technician_price']=$getitmen['totalAmount'];
					}
				$arr[]=$ft;
				}
				$array=array(
							"status"=>1,
							"message"=>'All Records',"data "=>$arr
						);
				echo  json_encode($array);
                }
			}else{
					$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
			}

		}else{
			$array=array(
							"status"=>0,
							"message"=>'404 Error '
						);
					echo  json_encode($array);
		}
}









    /*
     * Technician pannel your order
     * completed status
     * Customer panel completed order status
     */
public function getordersbystatus(){
	$token=$_POST['token'];
		$id=$_POST['userid'];
		$status=$_POST['Status'];
		$data['uid'] =$id;
		if($this->globals->set_token($token)=='true'){
			$valid=$this->AUTHOMODEL->ValidToAnyId('users',$data);
			if($valid){

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.id=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.veh_id=o.ord_velid where 0=0 and ord_custid=$id and ord_status!=2  order by veh_name,ord_time,cat_name ASC");*/

				/*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=cu.uid left join users us on us.uid =v.uid where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2  order by date_format(ord_time,'%Y-%m-%d') DESC");
                */
                
                
                
               $details=$this->AUTHOMODEL->FourTableJoin("

select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid 
as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as 
order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status 
as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.order_img as 
ordreimage,o.order_video as ordervideo,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as
 id ,user.uname as name,user.mobile as mobile,user.email as  email,user.utype as usertype,v.veh_id,v.veh_name
 as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,v.veh_latitude 
 as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as 
 technician_price,v.veh_service as serviceId,o.order_modify_time,o.order_arrival as arrivalstauts,o.order_arrival_time
 as arrivaltime,n.notice_uid as reasonuserid,n.notice_data reasoin,n.notice_status as detetestatus,o.order_feedback as
  feedback ,usl.s_service_id from order_now o 
  left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join
   users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service 
   left join notification_type n on n.notice_orderid=o.ord_id left join
    user_service usl on usl.s_ordid=o.ord_id  where 0=0 and 
    (o.ord_custid=$id) and ord_status=$status group by o.ord_id  order by o.ord_id DESC

");
                
                
                	

				if(empty($details)){
						$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
				}else{
				foreach($details as $ft){
					$Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
					if(!empty($itemid)){
					$data=" and serv_id in($itemid)";
					$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
					$ft['service_name']=implode(',',$getitmen['ServiceName']);
					$ft['technician_price']=$getitmen['totalAmount'];
					}
				$arr[]=$ft;
				}
				$array=array(
							"status"=>1,
							"message"=>'All Records',"data "=>$arr,"data1"=>$arr
						);
				echo  json_encode($array);
                }
			}else{
					$array=array(
							"status"=>0,
							"message"=>'Records Not Found'
						);
					echo  json_encode($array);
					
			}

		}else{
			$array=array(
							"status"=>0,
							"message"=>'404 Error '
						);
					echo  json_encode($array);
		}
}

/*
 * Technician pannel custormer order
 * completed status
 *
 */
    public function getordersbystatusTech(){
        $token=$_POST['token'];
        $id=$_POST['userid'];
        $status=$_POST['Status'];
        $data['uid'] =$id;
        if($this->globals->set_token($token)=='true'){
            $valid=$this->AUTHOMODEL->ValidToAnyId('users',$data);
            if($valid){

                /*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.id=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.veh_id=o.ord_velid where 0=0 and ord_custid=$id and ord_status!=2  order by veh_name,ord_time,cat_name ASC");*/

                /*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=cu.uid left join users us on us.uid =v.uid where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2  order by date_format(ord_time,'%Y-%m-%d') DESC");
                */



                $details=$this->AUTHOMODEL->FourTableJoin("

select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid 
as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as 
order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status 
as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.order_img as 
ordreimage,o.order_video as ordervideo,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as
 id ,user.uname as name,user.mobile as mobile,user.email as  email,user.utype as usertype,v.veh_id,v.veh_name
 as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,v.veh_latitude 
 as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as 
 technician_price,v.veh_service as serviceId,o.order_modify_time,o.order_arrival as arrivalstauts,o.order_arrival_time
 as arrivaltime,n.notice_uid as reasonuserid,n.notice_data reasoin,n.notice_status as detetestatus,o.order_feedback as
  feedback ,usl.s_service_id from order_now o 
  left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join
   users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service 
   left join notification_type n on n.notice_orderid=o.ord_id left join
    user_service usl on usl.s_ordid=o.ord_id  where 0=0 and 
    (o.ord_velid=$id) and ord_status=$status group by o.ord_id  order by o.ord_id DESC

");




                if(empty($details)){
                    $array=array(
                        "status"=>0,
                        "message"=>'Records Not Found'
                    );
                    echo  json_encode($array);

                }else{
                    foreach($details as $ft){
                        $Serviceid1=ltrim($ft['s_service_id'],'[');
                        $itemid= str_replace(' ','',rtrim($Serviceid1,']'));
                        if(!empty($itemid)){
                            $data=" and serv_id in($itemid)";
                            $getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
                            $ft['service_name']=implode(',',$getitmen['ServiceName']);
                            $ft['technician_price']=$getitmen['totalAmount'];
                        }
                        $arr[]=$ft;
                    }
                    $array=array(
                        "status"=>1,
                        "message"=>'All Records',"data "=>$arr,"data1"=>$arr
                    );
                    echo  json_encode($array);
                }
            }else{
                $array=array(
                    "status"=>0,
                    "message"=>'Records Not Found'
                );
                echo  json_encode($array);

            }

        }else{
            $array=array(
                "status"=>0,
                "message"=>'404 Error '
            );
            echo  json_encode($array);
        }
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    


	public function OrderProcessing(){
	$token=$_POST['token'];
  	if($this->globals->set_token($token)=='true'){
	$orderid=$_POST['orderid'];
	//$data['r_order']=$_POST['orderid'];
	//$data['r_sttime']=date('Y-m-d H:i:s');
	//$data['r_entrydt']=date('Y-m-d H:i:s');
	$data1['ord_status']=3;
    $data1['order_starttomovetime']=date('Y-m-d H:i:s');
	$this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$data1);
	//$this->AUTHOMODEL->record_insert('report',$data);
        
                                $techordid['ord_id']=$_POST['orderid'];
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$techordid);

                                $id['uid']=$uid['ord_velid'];
                                $username=$this->AUTHOMODEL->fiendfield('users','uname',$id);
                                $username=$username['uname'];
                                $name=ucwords(strtolower($username)); 
        
        
                                $techordid['ord_id']=$_POST['orderid'];
      
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$techordid);
                                // $this->db->last_query();
        
                                $id['uid']=$uid['ord_custid'];
                                $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$id);
                                    //echo $this->db->last_query();
        
                                $devicetoken=$device_token['device_token'];

        
                                $title="Order Status";
                                $message='Techinican '.$name.' Move for  your order Now you can track on the way';
                                
        
	$array=array(
				'status'=>1,
				'message'=>"Order is Processing"
			);
	echo json_encode($array);
		$uid=$uid['ord_custid'];
        $this->AUTHOMODEL->sendNotification($devicetoken,$message,$title,$orderid,$uid);
}else{
	$array=array(
				'status'=>0,
				'Error'=>" Error 404 "
			);
	echo json_encode($array);
	}
	}

public function UserOrderReport(){
	$id=$_POST['technicianid'];
		$token=$_POST['token'];
		if($this->globals->set_token($token)=='true'){
		$technician=$this->AUTHOMODEL->FourTableJoin("select v.price,c.cat_name,o.ord_id,o.ord_catid,o.Time,o.order_starttomovetime,o.order_modified_datetime,o.ord_straddress,o.ord_custid,o.ord_status,r.r_id,r.r_order,r.r_sttime,r.r_endtime,r.r_entrydt,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime from order_now o  left join report r on r.r_order= o.ord_id left join category c on c.cat_id=o.ord_catid left join vehicle v on v.uid=o.ord_velid where ord_velid=$id  order by ord_time group by o.ord_id DESC");

		foreach ($technician as $key => $value) {
  				$arr[]=$value;
  			}
  			$array=array("status"=>1,
  						  "message"=>'Technician Order Records',
  						  "data"=>$arr);
  			echo json_encode($array);

	}else{
		$array=array(
				'status'=>0,
				'Error'=>" Error 404 "
			);
	echo json_encode($array);	
	}
}


public function UserOrderCompleted(){
	$token=$_POST['token'];
  	if($this->globals->set_token($token)=='true'){
	$orderid=$_POST['Orderid'];
	//$data['r_endtime']=date('Y-m-d H:i:s');
	$data1['ord_status']=4;
	$this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$data1);

    //$reports=$this->AUTHOMODEL->FourTableJoin("select r_id from report where r_order=$orderid order by r_id desc limit 1");

    		 $orderdetails =$this->AUTHOMODEL->FourTableJoin("SELECT ord_entdy,order_starttomovetime,order_arrival_time,order_accepttime,order_estamatetime from order_now where 0=0 and ord_id=$orderid");
            	foreach($orderdetails as $ft)
            	$data['r_order']=$_POST['Orderid'];
            	$data['r_sttime']=date('Y-m-d H:i:s',strtotime($ft['ord_entdy']));
            	$data['r_entrydt']=date('Y-m-d H:i:s');
            	$data['r_endtime']=date('Y-m-d H:i:s');
            	// $data['r_notes']=$_POST['Note'];
            	$data['r_problems']=1;
            	$data['r_estamate_time']=$ft['order_estamatetime'];
            	$data['r_visiteagain']=0;
            	$data['r_moveto_order']=date('Y-m-d H:i:s',strtotime($ft['order_starttomovetime']));
            	$data['r_arrival_order']=date('Y-m-d H:i:s',strtotime($ft['order_arrival_time']));
            	$this->AUTHOMODEL->record_insert('report',$data);



      	/*$rid=$reports[0]['r_id'];
		$this->AUTHOMODEL->editonerecords('report','r_id',$rid,$data);*/
      
	$payment=$this->AUTHOMODEL->ReportStatus($orderid);
        
        
        $pay['pay_ordid']=$_POST['Orderid'];
		$pay['pay_staus']=1;
        $valid=$this->AUTHOMODEL->ValidToAnyId('payment',$pay);
        if($valid<1){
                $pays['pay_custid']=$_POST['Customerid'];
				$pays['pay_catid']=$_POST['Categoryid'];
				$pays['pay_velid']=$_POST['Technicianid'];
				$pays['pay_ordid']=$_POST['Orderid'];
				$pays['pay_amt']=$_POST['OrderAmount'];
				$pays['pay_type']=$_POST['PaymentType'];
				$pays['pay_entdt']=date('Y-m-d H:i:s');
            
                if(isset($_POST['ExtraCategory']) && $_POST['ExtraCategory']!=''){
                 $addservice['addserivce_catid']=$_POST['ExtraCategory'];   
                }
                if(isset($_POST['ExtraService']) && $_POST['ExtraService']!=''){
                     $addservice['add_service']=$_POST['ExtraService'];   
                    }
               if(isset($_POST['ExtraServiceName']) && $_POST['ExtraServiceName']!=''){
                     $addservice['add_servicename']=$_POST['ExtraServiceName'];   
                    }

                if(isset($_POST['ExtraPrice']) && $_POST['ExtraPrice']!=''){
                     $addservice['addserivce_price']=$_POST['ExtraPrice'];   
                    }
                if(isset($_POST['Customerid']) && $_POST['Customerid']!=''){
                     $addservice['addserivce_uid']=$_POST['Customerid'];   
                    }
                if(isset($_POST['Technicianid']) && $_POST['Technicianid']!=''){
                     $addservice['addserivce_techid']=$_POST['Technicianid'];   
                    }
                if(isset($_POST['Orderid']) && $_POST['Orderid']!=''){
                     $addservice['addserivce_ordid']=$_POST['Orderid'];   
                    }
                
				$this->AUTHOMODEL->record_insert('payment',$pays);
                $addservice['addserivce_paymentid']=$this->db->insert_id();
                $addservice['addserivce_entrydt']=date('Y-m-d H:i:s');
                $this->AUTHOMODEL->record_insert('addservice',$addservice);
           
        }
        
        
           /* $ordid=$payment['ord_id'];
            $status=1;
            $custid=$payment['ord_custid'];
            $catid=$payment['ord_catid'];
            $price=$payment['price'];
            $vehid=$payment['veh_id'];
            $url=base_url()."api/payment?token=$token&orderid=$ordid&status=$status&custid=$custid&catid=$catid&vehid=$vehid&amount=".$price."";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD,true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                $output = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
        */
        
        
        

        
        
                                $techordid['ord_id']=$_POST['Orderid'];
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$techordid);

                                $id['uid']=$uid['ord_velid'];
                                $username=$this->AUTHOMODEL->fiendfield('users','uname',$id);
                                $username=$username['uname'];
                                $name=ucwords(strtolower($username)); 
        
        
                                $techordid['ord_id']=$_POST['Orderid'];
      
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$techordid);
                                 $this->db->last_query();
        
                                $id['uid']=$uid['ord_custid'];
                                $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$id);
                                    //echo $this->db->last_query();
        
                                $devicetoken=$device_token['device_token'];

        
                                $title="Order Status";
                                $message=$name.' Your order payment is done';
        
        
        
        
        
        
        
	$array=array(
				'status'=>1,
				'message'=>"Order is Completed"
			);
	echo json_encode($array);
			$uid=$uid['ord_custid'];
        $this->AUTHOMODEL->sendNotification($devicetoken,$message,$title,$orderid,$uid);
}else{
	$array=array(
				'status'=>0,
				'Error'=>" Error 404 "
			);
	echo json_encode($array);
	}
}

public function UserForgetPassword(){
	$arr= array();
		$condition=array();
		$set='';
		$token=$_POST['token'];
		if($this->globals->set_token($token)=='true'){
		$get_data=$_POST['emailid'];
		if (filter_var($get_data, FILTER_VALIDATE_EMAIL)) {
			$condition['email'] =$get_data;
			$set="email";
		}
		/*else{
			$condition['mobile'] = $get_data;
			$set="email";
		}*/
		$valid=$this->AUTHOMODEL->ValidToAnyId('users',$condition);
       
		if($valid['uid']>0){
		$pass=rand(11111111,99999999);
		$condition['upassword']=$this->globals->password_check($pass);
		$this->AUTHOMODEL->editonerecords('users',$set,$get_data,$condition);
        $subject="Reset password";
        $message="Your new password is $pass ";
		$this->AUTHOMODEL->Sendmails($get_data,$message,$subject);
		//$this->AUTHOMODEL->sendEmail( 'info@gmail.com','Your Reset Password is ', $get_data, $pass);
			$array=array(
				'status'=>1,
				'message'=>"Password send successfully on your email"
			);
		}else{
			$array=array(
				'Status'=>0,
				'Message'=>"Not Found Records"
			);
		}
		echo json_encode($array);
	}else{
		$array=array(
				'Status'=>0,
				'Message'=>"404 Error"
			);
	}
}
    
      

    
    
    
    
public function OrderCancle(){
	 $token=$_POST['token'];
   
  	if($this->globals->set_token($token)=='true'){
	$orderid=$_POST['orderid'];
	$data1['ord_status']=2;
	$this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$data1);
        
                             $ordid['ord_id']=$_POST['orderid'];
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$ordid);
                                $id['uid']=$uid['ord_custid'];
                                $username=$this->AUTHOMODEL->fiendfield('users','uname',$id);
                                $username=$username['uname'];
                                $name=ucwords(strtolower($username));
        
                                $uid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$ordid);
                                $id['uid']=$uid['ord_velid'];
                                $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$id);
                                $devicetoken=$device_token['device_token'];
                                $devicetoken=$device_token['device_token'];
        
                                $title="Order Status";
                                $message='User '.$name.' Cancel the Order ';
                                
        
        
        
        
	$array=array(
				'status'=>1,
				'message'=>"Order is Canceled "
			);
	echo json_encode($array);
	$uid=$uid['ord_velid'];
    $this->AUTHOMODEL->sendNotification($devicetoken, $message,$title,$orderid,$uid);
    }else{
	$array=array(
				'status'=>0,
				'Error'=>" Error 404 "
			);
	echo json_encode($array);
	}
	}

    
    
    
    
public function GetCurrentLocation(){
	$token=$_POST['token'];
  	if($this->globals->set_token($token)=='true'){
  		$data['map_ordid']=$_POST['orderid'];
  		$data['map_latitude']=$_POST['latitude'];
  		$data['map_longtitute']=$_POST['longitude'];
  		$data['map_stop_time']=$_POST['stoptime'];
  		
  		$data['map_entdt']=date('Y-m-d H:i:s');
/*        $t['ord_id']=$_POST['orderid'];
        $status=$this->AUTHOMODEL->fiendfield('order_now','ord_status',$t);*/
       /* if($t['ord_status']==3){*/
  		$this->AUTHOMODEL->record_insert('vel_map',$data);
        
        $array=array('status'=>1,"message"=>"Successfuly Current Location Inserted");
        echo json_encode($array);
  	}
}

    
 public function UpdateUserPassword(){
            $oldpass=$_POST['oldpassword'];
            $newpass=$_POST['newpassword'];
            $email=$_POST['emailid'];
            $data['email']=$_POST['emailid'];
            $data['upassword']=$this->globals->password_check($oldpass);
             $upassword=$this->globals->password_check($newpass);
            $resetpassword= $this->AUTHOMODEL->getallbytwocond('uid','users',$data);
           $data1['upassword']=$upassword;
  if(!empty($resetpassword)){
         $this->AUTHOMODEL->editonerecords('users','email',$email,$data1);
      
         $array=array('status'=>1,'message'=>"Successfully update password");
      echo json_encode($array);
     }else{
         $array=array('status'=>0,'message'=>"Old password doesn't match");
       echo json_encode($array);
   
 } 
}
    
    
    
    
    public function GetAllLocations(){
		$token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
	  		$oid=$_POST['orderid'];
			  $map= $this->AUTHOMODEL->FourTableJoin("select map_latitude,map_longtitute,map_id from vel_map where 0=0 and map_ordid=$oid order by map_id desc limit 1 ");
			  
			  $t['ord_id']=$oid;
			  $techid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$t);
			  if(!empty($techid)){
			  $tid['uid']=$techid['ord_velid'];
			  $techid=$this->AUTHOMODEL->fiendfield('users','uname',$tid);
			  }
			  
	  		if($map[0]['map_id']>0){
	  			foreach($map as $row){
					if(!empty($techid)){
					$row['name']=$techid['uname'];
					}
	  				$arr[]=$row;
				  }
				 
	  			$array=array('status'=>1,'message'=>'All Map Address','data'=>$arr);
	  			echo json_encode($array);
	  		}else{
	  			$array=array('status'=>0,'message'=>'Map Not Found');
	  			echo json_encode($array);
	  		}
	  	}
	}
    
    
    
    
    public function ShowAdditionalPage(){
		//$token=$_POST['token'];
	  	//if($this->globals->set_token($token)=='true'){
	  		$data['pg_id']=$this->uri->segment(3);
			$page= $this->AUTHOMODEL->getallbytwocond('page_contain','pages_containe',$data);
			echo $page[0]['page_contain'];
	  		// $array=array("status"=>1,"message"=>"All Content Page","data" =>$page);
	  		// echo json_encode($array);
	  	//}
	}




/* Vehical Active And Inactive Or Delete */

	public function VehicalStatus(){
		$token=$_POST['token'];
		$id=$_POST['TechId'];
		$data1['uid']=$id;
		if($this->globals->set_token($token)=='true'){
		$valid=$this->AUTHOMODEL->ValidToAnyId('vehicle',$data1);
		if($valid){
        $status=$_POST['status'];
		$data['veh_status']=$_POST['status'];
        $data3['status']=$_POST['status'];
        $this->AUTHOMODEL->editonerecords('vehicle','uid',$id,$data);
        $this->AUTHOMODEL->editonerecords('users','uid',$id,$data3);
        if($_POST['status']==0)
        $mgs= "Now you are inactive";
        elseif($_POST['status']==1)
        $mgs=  "Now you are active";
    	elseif ($_POST['status']==2)
         $mgs=  "Successfully Delete";
    	}else{
    	$mgs=  "Not Found Records";
   		 }
   		 $array=array('status'=>$status,'message'=>$mgs);
   		 echo json_encode($array);
		}else{
			echo "Error 404";
			exit();
		}
	}

    
   
    
    
    public function mobileverification(){
        $mobile=$_POST['mobileno'];
        $Email=$_POST['EmailId'];
        $otp= mt_rand(100000, 999999);
        $validat=$this->AUTHOMODEL->mobileverification($mobile,$otp);
        $msg="Your OTP is $otp";
        $subject="OTP Verification";
        $this->AUTHOMODEL->Sendmails($Email,$msg,$subject);
        $massages=$otp;
        $array=array('status'=>1,'OTP'=>$massages,'message'=>"Successfully otp sent to your mobile number");
        echo json_encode($array);
        
    }
    
    public function FindStatus(){
        $data['email']=$_POST['emailid'];
        $status=$this->AUTHOMODEL->fiendfield('users','status',$data);
        $st=$status['status'];
        $array=array('status'=>$st,'message'=>"User Status");
        echo json_encode($array);
        
    }
    
    
    
    
    
    

	public function GetServices(){
		$token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
	  		$data['serv_category']=$_POST['Categoryid'];
	  		$services= $this->AUTHOMODEL->getallbytwocond('*','tech_serviecs',$data);
	  		if(!empty($services)){
	  			foreach($services as $row){
	  				$arr[]=$row;
	  			}
	  			$array=array('status'=>1,'message'=>'You Services ','data'=>$arr);
	  			echo json_encode($array);
	  		}else{
	  			$array=array('status'=>0,'message'=>'Services Not Found');
	  			echo json_encode($array);
	  		}
	  	}
	}


    public function ModifyDateTime(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            
    if(isset($_FILES['video']['name']) && $_FILES['video']['name']!=''){
     	$path = './uploads/video';  
        $folder = 'video';
        if(!is_dir($path))
        mkdir($path);
        $path2=$path."/";
        if(!is_dir($path2))
        mkdir($path2);
        if ( ! $path || ! $path2 )
        return;
 		if(!empty($_FILES["video"]['name'])){
        $target_dir = './uploads/'.$folder.'/';
            $timestamp= date('YmdHis');
            $filename=$_FILES["video"]["name"];
            @$extension=end(explode(".", $filename));
             $newfilename=$timestamp.".".$extension;
            $target_file = $target_dir . basename($newfilename);
             $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
             	$arr[]=$target_dir.$newfilename;  
          }
      
      $modifydt['order_video'] =json_encode($arr);
  }
}
 

			$arraydata=array();
			$find['ord_id']=$_POST['OrderId'];
			$findimage=$this->AUTHOMODEL->fiendfield("order_now","order_img",$find);
			//$imagesdata=json_decode($findimage);
			 $imagename = json_decode($findimage['order_img']);
			if(!empty($findimage['order_img'])){
			 $size=sizeof($imagename);
			for($i=0;$i<$size;$i++){
				array_push($arraydata,$imagename[$i]);
				}
			}
			
        if(isset($_POST['images_string']) && $_POST['images_string']!=''){
            $encode=$_POST['images_string'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'1' .".".$extension;
            $path = './uploads/image/'.$newfilename;  
            $file=fopen($path, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path);
            /*$arr1[]= $path;
            $data['order_img'] =json_encode($arr1);*/
            }
            if(isset($_POST['images_string_one']) && $_POST['images_string_one']!=''){
            $encode=$_POST['images_string_one'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'2' .".".$extension;
            $path1 = './uploads/image/'.$newfilename;  
            $file=fopen($path1, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path1);
            /*$arr1[]= $path;
            $data['order_img'] =json_encode($arr1);*/
            }
            if(isset($_POST['images_string_two']) && $_POST['images_string_two']!=''){
            $encode=$_POST['images_string_two'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.'3' .".".$extension;
            $path2 = './uploads/image/'.$newfilename;  
            $file=fopen($path2, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path2);
            }
           if(!empty($arraydata)){
             $modifydt['order_img'] =json_encode($arraydata);  
           }
           
			$modifydt['order_modified_datetime']=date('Y-m-d',strtotime($_POST['ModifyDate']));
			$modifydt['ord_time']=date('Y-m-d',strtotime($_POST['ModifyDate']));
			$modifydt['order_modify_time']=$_POST['ModifyTime'];
			$modifydt['order_time']=$_POST['ModifyTime'];
            $orderid=$_POST['OrderId'];
            $this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$modifydt); 
            $arr=array('status'=>1,'message'=>"Successfully Modify Order");
            echo json_encode($arr);
            }
        }
    
    
    
    
    public function OrderFeedBack(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['order_feedback']=$_POST['OrderFeedBack'];
            $orderid=$_POST['OrderId'];
           /* $modifydt['order_visittryagain']=$_POST['VisitTryAgain'];
            $modifydt['order_problemstatus']=$_POST['ProblemSolved'];*/
           if($this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$modifydt)==true){

            $arr=array('status'=>1,'message'=>"Successfuly submit your feedback");
           } else{
           	$arr=array('status'=>0,'message'=>"Sorry your feedback is not sumbit");
           }
            echo json_encode($arr);
        }
    }
    
    
    public function CheckOrderExists(){
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
             $userid=$_POST['userid'];
             $technicianid=$_POST['technicianid'];  
            $orderstatus = $this->AUTHOMODEL->FourTableJoin("select ord_id from order_now where ord_custid =$userid and ord_velid=$technicianid and ord_status in(0,1,3)");
           if($orderstatus!=''){
                $arr=array('status'=>1,'message'=>"Technician already has ordered, So Select another technician");
           }else{
                $arr=array('status'=>0,'message'=>"Success ");
           }
           echo json_encode($arr);
        }
    }
    
    
    public function OrderShowAfterCreateOrder(){
        $id=$_POST['OrderId'];
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
        $details=$this->AUTHOMODEL->FourTableJoin("select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,v.veh_id,v.veh_name as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as technician_price,o.order_modify_time,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,o.order_feedback as feedback,usl.s_service_id from order_now o left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service left join user_service usl on usl.s_ordid=o.ord_id  where 0=0 and o.ord_id='".$id."' and ord_status!=2 group by o.ord_id  order by o.ord_id ASC");
           
        if(!empty($details)){

            foreach($details as $ft)
            	 $Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
				if(!empty($itemid)){
				$data=" and serv_id in($itemid)";
				$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
				$ft['service_name']=implode(',',$getitmen['ServiceName']);
				
				 $ft['technician_price']=$getitmen['totalAmount'];
				}
            	$arr[]=$ft;
                $array=array('status'=>1,'message'=>'Show Orders','data'=>$arr);
        }else{
             $array=array('status'=>0,'message'=>'Not found records ');
        }
       
        }else{
             $array=array('status'=>0,'message'=>'Server Error 404');
        }
         echo json_encode($array);
    }
    
     public function InvoiceNumber(){
        $id=$_POST['OrderId'];
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
        $details=$this->AUTHOMODEL->FourTableJoin("select p.pay_id as InvoiceNo,p.pay_entdt as payment_entrydt,p.pay_amt as payment_amount,ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,v.veh_id,v.veh_name as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as technician_price,o.order_modify_time,ex.addserivce_catid as extracategoryid,ex.addserivce_entrydt as extraserviceentrydt,ex.addserivce_price as extraprice,ex.add_service as extraserviceid,cat.cat_name as extracategoryname,exservice.serv_name as extraservicename,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,o.order_feedback as feedback,usl.s_service_id from order_now o left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service left join payment p on p.pay_ordid=o.ord_id left join addservice ex on ex.addserivce_paymentid=p.pay_id left join category cat on cat.cat_id =ex.addserivce_catid left join tech_serviecs exservice on exservice.serv_id = ex.add_service  left join user_service usl on usl.s_ordid=o.ord_id where 0=0 and ord_id='".$id."' and ord_status!=2 group by o.ord_id  order by o.ord_id ASC");
           
        if(!empty($details)){
            foreach($details as $ft)
            	 $Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
				if(!empty($itemid)){
				$data=" and serv_id in($itemid)";
				$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
				$ft['service_name']=implode(',',$getitmen['ServiceName']);
				
				 $ft['technician_price']=$getitmen['totalAmount'];
				}
                $arr[]=$ft;
                $array=array('status'=>1,'message'=>'Show Orders','data'=>$arr);
        }else{
             $array=array('status'=>0,'message'=>'Not found records ');
        }
       
        }else{
             $array=array('status'=>0,'message'=>'Server Error 404');
        }
         echo json_encode($array);
    }
    
    
    public function PaymentHistory(){
         $id=$_POST['userid'];
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
        $details=$this->AUTHOMODEL->FourTableJoin("select p.pay_id as InvoiceNo,p.pay_entdt as payment_entrydt,p.pay_amt as payment_amount,ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,v.veh_id,v.veh_name as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as technician_price,o.order_modify_time,ex.addserivce_catid as extracategoryid,ex.addserivce_entrydt as extraserviceentrydt,ex.addserivce_price as extraprice,ex.add_service as extraserviceid,cat.cat_name as extracategoryname,ex.add_servicename as extraservicename,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,o.order_feedback as feedback,usl.s_service_id from payment p left join order_now o on p.pay_ordid=o.ord_id  left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service left join addservice ex on ex.addserivce_paymentid=p.pay_id left join category cat on cat.cat_id =ex.addserivce_catid left join tech_serviecs exservice on exservice.serv_id = ex.add_service left join user_service usl on usl.s_ordid=o.ord_id  where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2 group by o.ord_id  order by p.pay_id ASC");
           
        if(!empty($details)){
            foreach($details as $ft){
            	$Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
				if(!empty($itemid)){
				$data=" and serv_id in($itemid)";
				$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
				$ft['service_name']=implode(',',$getitmen['ServiceName']);
				
				 $ft['technician_price']=$getitmen['totalAmount'];
				}
                $arr[]=$ft;
          		 
				$array=array('status'=>1,'message'=>'Show Orders','data'=>$arr);
			}
        }else{
             $array=array('status'=>0,'message'=>'Not found records ');
        }
       
        }else{
             $array=array('status'=>0,'message'=>'Server Error 404');
        }
         echo json_encode($array);
    }
    
    
    
public function notifications(){
        $this->AUTHOMODEL->check_isvalidated();
        $userid=array_slice($this->session->userdata,1,1);
        $this->load->view('default/header');
        $this->load->view('default/sidebar');
          
         $data['notification']=$this->AUTHOMODEL->FourTableJoin("select u.*,s.*,o.ord_id,n.* from notification_type n left join users u on n.notice_uid=u.uid left join order_now o on o.ord_id=n.notice_orderid left join tech_serviecs s on s.serv_id = o.order_serviceid where 0=0 group by notice_id order by notice_id desc");
        
        
       /*  $data['notification']=$this->AUTHOMODEL->FourTableJoin("select n.notice_date,n.notice_data,n.notice_orderid,n.notice_uid,o.ord_id,u.uname as veh_name,v.veh_service,s.serv_name from notification_type n left join order_now o on o.ord_id=n.notice_orderid left join users u on u.uid = o.ord_velid left join vehicle v on v.uid=u.uid left join tech_serviecs s on s.serv_id=v.veh_service   where 0=0  order by notice_id desc ");*/
        
        
        
        
        
       /* $data['notification']=$this->AUTHOMODEL->FourTableJoin("select                                             n.notice_date,n.notice_data,n.notice_orderid,n.notice_uid,o.ord_id,o.ord_velid,t.veh_name,t.veh_catid,t.veh_service,t.veh_contectno,s.serv_id,s.serv_name,u.uname,  from notification_type n left join order_now o on o.ord_id=n.notice_orderid left join vehicle t on t.uid=n.notice_uid left join tech_serviecs s on s.serv_id=t.veh_service  where 0=0 group by ord_id order by notice_id desc");
        */
        
        
        $this->load->view('ACCOUNTS0001/showallnotification',$data);
		$this->load->view('default/footer');
    }
    
    
    
    public function Cancelnotification(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            
        $custid['ord_id']=$_POST['orderid'];
        $orderid=$_POST['orderid'];
        $modifydt['ord_status']=2; 
       
        $this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$modifydt);
            
            
            
            
            
        /*$customerid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$custid);
        $technicianid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$custid); 
        $techid['uid']= $technicianid['ord_velid']; 
        $techname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
        $tnames=$techname['uname'];   
        $userid['uid']=$customerid['ord_custid']; 
        $emailid=$this->AUTHOMODEL->fiendfield('users','email',$userid);
        $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$userid);
        $email= $emailid['email']; 
        $divtoken= $device_token['device_token'];*/ 
            
            
        $data['notice_uid']=$_POST['userid'];
        $data['notice_data']=$_POST['define_reason'];
        $data['notice_status']=$_POST['Status'];
        $data['notice_date']=date('Y-m-d H:i:s');
        $data['notice_orderid']=$_POST['orderid'];
        $this->AUTHOMODEL->record_insert('notification_type',$data);    
        $uid=$_POST['customerortechnicianid'];
            
        $userdata=$this->AUTHOMODEL->FourTableJoin("select * from users where 0=0 and  uid=$uid  and status=1");
           
        foreach($userdata as $ft)  
        $divtoken=$ft['device_token'];  
        $get_data=$ft['email']; 
            if($ft['utype']==0){
                    $technicianid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$custid);
                    $techid['uid']= $technicianid['ord_velid']; 
                    $techname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
					$name=$techname['uname'];   
					$uid=$technicianid['ord_velid']; 
            }else{
                    $customerid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$custid);
                    $techid['uid']= $customerid['ord_custid']; 
                    $techname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
                     $name=$techname['uname'];  
					$uid=$customerid['ord_custid']; 
            }
          
        $msg="Your order is cancel By the $name";
        $title="Cancel Order";
            
        $message="Order is cancel by the $name ".'<p>'.$_POST['define_reason'].'</p>';
          
            $arr=array('status'=>1,'message'=>$msg);  
        echo json_encode($arr);
			$email="Bestdesingn@gmail.com";
			
            $this->AUTHOMODEL->sendNotification($divtoken, $msg,$title,$orderid,$uid);
            
            $this->AUTHOMODEL->Sendmails($email,$message,$title);
//            $this->AUTHOMODEL->SendMailResion($get_data,$message);
        }
        
    }
    
    
    
    
    
    
public function SearchNotification(){
    $condition="";
    $QuerySting=array();
    if(isset($_REQUEST['date']) && $_REQUEST['date']!=''){
        $condition.=" and date_format(notice_date,'%Y-%m-%d') ='".date('Y-m-d',strtotime($_REQUEST['date']))."'";
        $QuerySting['notice_date']=$_REQUEST['date'];
    }
    
    if(isset($_REQUEST['month']) && $_REQUEST['month']!=''){
        $condition.=" and date_format(notice_date,'%m') =".$_REQUEST['month'];
        $QuerySting['notice_date']=$_REQUEST['month'];
    }
    
    if(isset($_REQUEST['year']) && $_REQUEST['year']!=''){
        $condition.=" and date_format(notice_date,'%Y') =".$_REQUEST['year'];
        $QuerySting['notice_date']=$_REQUEST['year'];
    }
  
     $notification=$this->AUTHOMODEL->FourTableJoin("select u.*,s.*,o.ord_id,n.* from notification_type n left join users u on n.notice_uid=u.uid left join order_now o on o.ord_id=n.notice_orderid left join tech_serviecs s on s.serv_id = o.order_serviceid where 0=0 group by notice_id order by notice_id desc");
    
   /* $notification =$this->AUTHOMODEL->FourTableJoin("select                                             n.notice_date,n.notice_data,n.notice_orderid,n.notice_uid,o.ord_id,o.ord_velid,t.veh_name,t.veh_catid,t.veh_service,t.veh_contectno,s.serv_id,s.serv_name  from notification_type n left join order_now o on o.ord_id=n.notice_orderid left join vehicle t on t.uid=n.notice_uid left join tech_serviecs s on s.serv_id=t.veh_service  where 0=0  group by ord_id order by notice_id desc");*/
    ?>
      <?php  $i=0; if(!empty($notification)){ foreach($notification as $ft){ $i++; ?>
                <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo ucwords(strtolower($ft['uname'])); ?></td>
                <td><?php echo ucwords(strtolower($ft['serv_name'])); ?></td>
                <td><?php echo $ft['ord_id']; ?></td>
                <td><?php echo date('d-M-Y',strtotime($ft['notice_date'])); ?></td>
                <td><?php echo ucwords(strtolower($ft['notice_data'])); ?></td>
                <td><?php if($ft['utype']==0) echo "Users"; else echo "Technician"; ?></td>
                </tr>
    <?php } } ?>
    <?php
    
    
    
}
    
    public function UpdateUserProfile(){
         $token=$_POST['token'];
        $arraydata[]='';
	  	if($this->globals->set_token($token)=='true'){
         if(isset($_POST['userprofileimage']) && $_POST['userprofileimage']!=''){
            $uid=$_POST['UserId'];
            $encode=$_POST['userprofileimage'];
            $filename="abc.jpg";
            $decode=base64_decode($encode);
            $timestamp= date('YmdHis');
            @$extension=end(explode(".", $filename));
            $newfilename=$timestamp.".".$extension;
            $path1 = './uploads/profile/'.$newfilename;  
            $file=fopen($path1, 'wb');
            $is_written=fwrite($file, $decode);
            fclose($file);
            array_push($arraydata,$path1);
           // $arr1[]= $path1;
			$data['user_img'] = $path1;
			$data['mobile']=$_POST['mobileno'];
			$this->AUTHOMODEL->editonerecords('users','uid',$uid,$data);
			$udata['uid']=$uid;

			$usertype=$this->AUTHOMODEL->fiendfield('users','utype',$udata);
			if($usertype['utype']==1){
				$tdata['veh_contectno']=$_POST['mobileno'];
				$this->AUTHOMODEL->editonerecords('vehicle','uid',$uid,$tdata);
			}elseif($usertype['utype']==0){
				$cdata['mobile']=$_POST['mobileno'];
				$this->AUTHOMODEL->editonerecords('customer','uid',$uid,$cdata);
			}
            $message=array('status'=>1,'message'=>"Successfully update user image");
            }else{
            $message=array('status'=>0,'message'=>"Not  update user image");
         }
        }else{
             $message=array('status'=>0,'message'=>"Server getting Error 404");
        }
        echo json_encode($message);
    }
    
    
    
    public function GetOrderStatus(){
        $token=$_POST['token'];
        $order_id=$_POST['OrderId'];
	  	if($this->globals->set_token($token)=='true'){
             $orderStatus =$this->AUTHOMODEL->FourTableJoin("SELECT ord_status,order_arrival from order_now where ord_id=$order_id");
                if(!empty($orderStatus)){
                    if($orderStatus[0]['ord_status']==0){
                        $status=$orderStatus[0]['ord_status'];
                        $arrival_status=$orderStatus[0]['order_arrival'];
                        $message="Order is Inactive";
                    }elseif($orderStatus[0]['ord_status']==1){
                        $status=$orderStatus[0]['ord_status'];
                        $arrival_status=$orderStatus[0]['order_arrival'];
                        $message="Order is Pending";
                    }elseif($orderStatus[0]['ord_status']==2){
                        $status=$orderStatus[0]['ord_status'];
                        $arrival_status=$orderStatus[0]['order_arrival'];
                        $message="Order is delete";  
                    }elseif($orderStatus[0]['ord_status']==3){
                        $status=$orderStatus[0]['ord_status'];
                        $arrival_status=$orderStatus[0]['order_arrival'];
                        $message="Order is Processing";  
                    }elseif($orderStatus[0]['ord_status']==4){
                        $status=$orderStatus[0]['ord_status'];
                        $arrival_status=$orderStatus[0]['order_arrival'];
                        $message="Order payment is  completment";  
                    }                    
                }else{
                     $status=0;
                    $message="Order is not Avaliable";
                    $arrival_status='';
                }
            
            
        }else{
            
              $status=0;
              $message="Server Error 404";
            $arrival_status='';
        }
        
        $array=array("status"=>$status,'arrival_status'=>$arrival_status,'message'=>$message);
        echo json_encode($array);
    }
    
    
    
    public function VisitAgain(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $orderid=$_POST['OrderId'];
            $visit['ord_status']=1;
            $visit['order_arrival']=0;
            $this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$visit); 
            //$modifydt['r_notes']=$_POST['Note'];
            $reports=$_POST['OrderId'];
            $orderdetails =$this->AUTHOMODEL->FourTableJoin("SELECT ord_entdy,order_starttomovetime,order_arrival_time,order_accepttime,order_estamatetime from order_now where 0=0 and ord_id=$orderid");
            foreach($orderdetails as $ft)
            	$data['r_order']=$_POST['OrderId'];
            	$data['r_sttime']=date('Y-m-d H:i:s',strtotime($ft['ord_entdy']));
            	$data['r_entrydt']=date('Y-m-d H:i:s');
            	$data['r_endtime']=date('Y-m-d H:i:s');
            	$data['r_notes']=$_POST['Note'];
            	$data['r_problems']=0;
            	$data['r_estamate_time']=$ft['order_estamatetime'];
            	$data['r_visiteagain']=1;
            	$data['r_moveto_order']=date('Y-m-d H:i:s',strtotime($ft['order_starttomovetime']));
            	$data['r_arrival_order']=date('Y-m-d H:i:s',strtotime($ft['order_arrival_time']));
            	$this->AUTHOMODEL->record_insert('report',$data);
            //$this->AUTHOMODEL->editonerecords('report','r_order',$reports,$modifydt); 
            $arr=array('status'=>1,'message'=>"Successfuly Visit Again");
            echo json_encode($arr);
            $reason=$_POST['Note'];
            $subject="Visite again";
            $message="Order is not complete <p>$reason</p>";
            $email="Bestdesingn@gmail.com";
           	$this->AUTHOMODEL->Sendmails($email,$message,$subject);   
        }
    }
    
    
    public function OrderStaus(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['ord_status']=$_POST['OrderStaus'];
            $orderid=$_POST['OrderId'];
            if(isset($_POST['OrderStaus']) && $_POST['OrderStaus']==1){
            	$modifydt['order_accepttime']=date('Y-m-d H:i:s');
            }
            $this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$modifydt); 
            $arr=array('status'=>1,'message'=>"Your request is accepted");
            echo json_encode($arr);
            $orderids['ord_id']=$_POST['OrderId'];
            $customerid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$orderids); 
            $custid['uid']=$customerid['ord_custid'];
            $techid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$orderids);
            $techname['uid']=$techid['ord_velid'];
            $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$custid);
            $tname=$this->AUTHOMODEL->fiendfield('users','uname',$techname);
            $name=$tname['uname'];
            $divtoken= $device_token['device_token']; 
            $msg="$name is  accepted your request";
			$title="Technician arrived";
			$uid=$customerid['ord_custid'];
            $this->AUTHOMODEL->sendNotification($divtoken, $msg,$title,$orderid,$uid);
        }
    }
    
    
    
    public function OrderArrivalStatus(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['order_arrival']=$_POST['OrderArrivalStatus'];
            $modifydt['order_arrival_time']=date('Y-m-d H:i:s');
            $orderid=$_POST['OrderId'];
            $this->AUTHOMODEL->editonerecords('order_now','ord_id',$orderid,$modifydt); 
            $arr=array('status'=>1,'message'=>"You have  arrived");
            echo json_encode($arr);
            
            $orderids['ord_id']=$_POST['OrderId'];
            $customerid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$orderids); 
            $custid['uid']=$customerid['ord_custid'];
            $techid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$orderids);
            $techname['uid']=$techid['ord_velid'];
            $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$custid);
            $tname=$this->AUTHOMODEL->fiendfield('users','uname',$techname);
            $name=$tname['uname'];
            $divtoken= $device_token['device_token']; 
            $msg="$name has arrived";
			$title="Technician arrived";
			$uid=$customerid['ord_custid'];
            $this->AUTHOMODEL->sendNotification($divtoken, $msg,$title,$orderid,$uid);
            
        }
    }
    
    
  public function ReportGenerate(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){

            $modifydt['r_visiteagain']=$_POST['VisitStatus'];
            $modifydt['r_problems']=$_POST['Problems'];
            $modifydt['r_notes']=$_POST['Note'];
            $orderid=$_POST['OrderId'];
            $ordrep=$this->AUTHOMODEL->FourTableJoin("select r_id from report where 0=0 and r_order=$orderid order by r_id desc limit 1");
            $id=$ordrep[0]['r_id'];
            $this->AUTHOMODEL->editonerecords('report','r_id',$id,$modifydt); 
            $arr=array('status'=>1,'message'=>"Successfuly Submit Reports");
            echo json_encode($arr);
            
            
            $details=$this->AUTHOMODEL->FourTableJoin("select p.pay_id as InvoiceNo,p.pay_entdt as payment_entrydt,p.pay_amt as payment_amount,ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,o.ord_status as order_status,o.ord_endadd as order_end_address,o.ord_straddress as order_start_address,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,v.veh_id,v.veh_name as technician_name,v.veh_contectno as technician_mobileno,v.veh_number,v.veh_service as serviceId,v.veh_address as technician_address,v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,v.veh_status as techinican_status,v.price as technician_price,o.order_modify_time,ad.addserivce_paymentid,ad.addserivce_price,ad.add_service,ad.add_servicename,testservice.serv_name,usl.s_service_id from order_now o left join users user on user.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=user.uid left join users us on us.uid =v.uid left join tech_serviecs ts on ts.serv_id=v.veh_service left join payment p on p.pay_ordid=o.ord_id left join addservice ad on ad.addserivce_paymentid=p.pay_id left join tech_serviecs testservice on testservice.serv_id=ad.add_service left join user_service usl on usl.s_ordid=o.ord_id  where 0=0 and p.pay_ordid='".$orderid."' and ord_status!=2  group by o.ord_id  order by date_format(ord_time,'%Y-%m-%d') DESC");
            foreach($details as $ft)
				$Serviceid1=ltrim($ft['s_service_id'],'[');
           	 		$itemid= str_replace(' ','',rtrim($Serviceid1,']'));
				if(!empty($itemid)){
				$data=" and serv_id in($itemid)";
				$getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
				$ft['service_name']=implode(',',$getitmen['ServiceName']);

				 $ft['technician_price']=$getitmen['totalAmount'];
				}
            $subject="Order Completed";
            $invoice=$ft['InvoiceNo'];    
            $orderid=$ft['order_id'];
            $username=ucwords(strtolower($ft['name']));
            $usermobile=$ft['mobile'];
            $techname=$ft['technician_name'];
            $technumber=$ft['technician_mobileno'];
            $servicename=$ft['service_name'];
            $payment=@$ft['payment_amount']-@$ft['addserivce_price'];
            $extraservice = ucwords(strtolower(ltrim(rtrim($ft['add_servicename'],']'),'[')));
            $extraserviceprice= $ft['addserivce_price'];
            $orderdate= date('Y-m-d H:i:s',strtotime($ft['user_order_date']));
            $paymentdate= date('Y-m-d H:i:s',strtotime($ft['payment_entrydt']));
            $total=@$payment+@$extraserviceprice;
           
            $message="<html><body><table class='CSSTableGenerator4' style='margin-top: 20px;border:1px solid black;width:100%'>
            <style>
            tr{
            border:1px solid black;
            }
            </style>
	 		<tbody>
	 			<tr ></tr>
 		<tr style='border-top: 1.5px solid black'>
 			<td style='border:1px solid black;'>Invoice No</td>
 			<td style='border:1px solid black;'>$invoice</td>
 		</tr>
                
         <tr style='border-top: 1.5px solid black'>
 			<td style='border:1px solid black;'>Order No</td>
 			<td style='border:1px solid black;'>$orderid</td>
 		</tr>
                    
 		<tr>
 			<td style='border:1px solid black;'>Name</td>
 			<td style='border:1px solid black;'>$username</td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Contact No</td>
 			<td style='border:1px solid black;'>$usermobile</td>
 		</tr>
 	
 		<tr>
 			<td style='border:1px solid black;'>Technician  Name</td>
 			<td style='border:1px solid black;'>$techname</td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Technician  No</td>
 			<td style='border:1px solid black;'>$technumber</td>
 		</tr>
        <tr>
 			<td style='border:1px solid black;'>Service </td>
 			<td style='border:1px solid black;'>$servicename</td>
 		</tr>
        <tr>
          <td style='border:1px solid black;'>Price</td>
          <td style='border:1px solid black;'>$payment</td>
        </tr>
        <tr>
 			<td style='border:1px solid black;'>Extra Service</td>
 			<td style='border:1px solid black;'>$extraservice</td>
 		</tr>
        <tr>
 			<td style='border:1px solid black;'>Extra Service  Amount</td>
 			<td style='border:1px solid black;'>$extraserviceprice</td>
 		</tr>       
                
 		<tr>
 			<td style='border:1px solid black;'>Order  Date</td>
 			<td style='border:1px solid black;'>$orderdate</td>
 		</tr>
                
        <tr>
 			<td style='border:1px solid black;'>Payment  Date</td>
 			<td style='border:1px solid black;'>$paymentdate</td>
 		</tr>
        <tr>
 			<td style='border:1px solid black;'>Total Amount</td>
 			<td style='border:1px solid black;'>$total</td>
 		</tr>

 		
 		</tbody>
 	</table></body></html>";
            $email="Bestdesingn@gmail.com";
           	$this->AUTHOMODEL->Sendmails($email,$message,$subject);
        }
  }  
    
    
    
    
    

  public function ReportGenerate1(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['r_visiteagain']=$_POST['VisitStatus'];
            $modifydt['r_problems']=$_POST['Problems'];
            $modifydt['r_notes']=$_POST['Note'];
            $orderid=$_POST['OrderId'];
            $this->AUTHOMODEL->editonerecords('report','r_order',$orderid,$modifydt); 
            $arr=array('status'=>1,'message'=>"Successfuly Submit Reports");
            echo json_encode($arr);
            $details=$this->AUTHOMODEL->FourTableJoin("select v.price,c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_id,o.ord_status,o.ord_entdy,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid where 0=0 and  ord_status!=2  and ord_id=$orderid ");
            $subject="Order Completed";
               $id= $details[0]['ord_id'];
            $name = ucwords(strtolower($details[0]['name'])); 
            $mobile =  $details[0]['mobile'];
            $techname=$details[0]['veh_name']; 
            $phone=$details[0]['veh_contectno']; 
            $price=$details[0]['price'];
            $orddate=date('Y-m-d H:i:s',strtotime($details[0]['ord_entdy']));
            $message="<html><body><table class='CSSTableGenerator4' style='margin-top: 20px;border:1px solid black;width:100%'>
            <style>
            tr{
            border:1px solid black;
            }
            </style>
	 		<tbody>
	 			<tr ></tr>
 		<tr style='border-top: 1.5px solid black;border:1px solid black;'>
 			<td style='border:1px solid black;'>Order No</td>
 			<td style='border:1px solid black;'>$id</td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Name</td>
 			<td style='border:1px solid black;'>$name</td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Contact No</td>
 			<td style='border:1px solid black;'>$mobile</td>
 		</tr>
 	
 		<tr>
 			<td style='border:1px solid black;'>Technician  Name</td>
 			<td style='border:1px solid black;'>$techname</td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Technician  No</td>
 			<td style='border:1px solid black;'>$phone </td>
 		</tr>
 		<tr>
 			<td style='border:1px solid black;'>Order  Date</td>
 			<td style='border:1px solid black;'>$orddate</td>
 		</tr>
    <tr>
      <td style='border:1px solid black;'>Price</td>
      <td style='border:1px solid black;'>$price</td>
    </tr>

 		
 		</tbody>
 	</table></body></html>";
            $email="Bestdesingn@gmail.com";
           	$this->AUTHOMODEL->Sendmails($email,$message,$subject);
        }
  }  
    
    
    
     public function MutualAskDeleteOption(){
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['notice_status']=$_POST['mAskToDeleteMutualOrder'];
            $modifydt['notice_data']=$_POST['Reason'];
            $modifydt['notice_uid']=$_POST['loginid'];
            $modifydt['notice_date']=date('Y-m-d H:i:s');
			$modifydt['notice_orderid']=$_POST['OrderId'];
			$orderid=$_POST['OrderId'];
            $this->AUTHOMODEL->record_insert('notification_type',$modifydt);
            $arr=array('status'=>1,'message'=>"Your order has deleted successfully");
            echo json_encode($arr);
            $uid=$_POST['customerortechnicianid'];
            
            $userdata=$this->AUTHOMODEL->FourTableJoin("select * from users where 0=0 and  uid=$uid  and status=1");
            foreach($userdata as $ft)  
            $divtoken=$ft['device_token'];  
            if($ft['utype']==0){
			$custid=$ft['uid'];
            $technicianid=$this->AUTHOMODEL->fiendfield('order_now','ord_velid',$custid);
            $techid['uid']= $technicianid['ord_velid']; 
            $techname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
            $name=$techname['uname'];   
            }else{
            $customerid=$this->AUTHOMODEL->fiendfield('order_now','ord_custid',$custid);
            $techid['uid']= $customerid['ord_custid']; 
            $techname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
            $name=$techname['uname'];  
            }
            $message=$_POST['Reason'];
            
            
            
            
           /* $device_token=$this->AUTHOMODEL->fiendfield('users','device_token',$techid);
            $tname=$this->AUTHOMODEL->fiendfield('users','uname',$techid);
            $name=$tname['uname'];
            $message=$_POST['Reason'];
            $divtoken= $device_token['device_token']; */
            $msg="$name has deleted your order";
            $title="Order deleted";
            $this->AUTHOMODEL->sendNotification($divtoken, $msg,$title,$orderid,$uid);
            //$this->AUTHOMODEL->SendMailDeleteorder($message);
            $adminmessage="$name has deleted order ".'<p>'.$message.'</p>';
			//$email="Bestdesingn@gmail.com";
			$email="it13manoj@gmail.com";
            $this->AUTHOMODEL->Sendmails($email,$adminmessage,$title);
            
        }
    }
    
    
    
    public function updateTechnicianlocation(){
         $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $modifydt['veh_latitude']=$_POST['latitude'];
            $modifydt['veh_longitude']=$_POST['longitude'];
             $login=$_POST['loginid'];
            /*$lat=$_POST['latitude'];
            $long=$_POST['longitude'];
           
             $this->AUTHOMODEL->loglanlong($login,$lat,$long,1);*/
             $this->AUTHOMODEL->editonerecords('vehicle','uid',$login,$modifydt); 
             $array=array("status"=>1,'message'=>'Success');
        }else{
            $array=array("status"=>0,'message'=>'Server Error 404');
           
        }
         echo json_encode($array);
    }
    
    
    public function maplocation(){
        $this->AUTHOMODEL->check_isvalidated();
        $this->load->view('default/header');
		$this->load->view('default/sidebar');
        $id=$this->uri->segment(3); 
        $result=$this->AUTHOMODEL->FourTableJoin("select count(map_id) as total from vel_map where map_ordid=$id");
        if(!empty($result[0]['total']>0)){
        	 $details=$this->AUTHOMODEL->FourTableJoin("select min(map_id) as minid ,max(map_id) as mixid from vel_map where map_ordid=$id");
       
        $minid=$details[0]['minid'];
        $maxid=$details[0]['mixid'];
        $data['customer']=$this->AUTHOMODEL->FourTableJoin("select m.map_latitude , m.map_longtitute,o.order_lang as latitude,o.order_long as longtitute,u.uname as customer from vel_map m left join order_now o on o.ord_id=m.map_ordid left join users u on u.uid = o.ord_custid  where map_id in($minid)");
        }else{
        	 $data['customer']='';
        }
       
       

        $this->load->view('ORDERBOOK0001/m/map',$data);
        $this->load->view('default/footer');

    }
    
    
    public function TechonMap(){
        $this->AUTHOMODEL->check_isvalidated();
        $this->load->view('default/header');
		$this->load->view('default/sidebar');
        $id=$this->uri->segment(3); 
        
        $data['technician']=$this->AUTHOMODEL->FourTableJoin("select t.veh_latitude,t.veh_longitude,u.uname as username  from vehicle t left join users u on u.uid=t.uid  where veh_id=$id");
        $this->load->view('VEHICLE0001/m/map',$data);
        $this->load->view('default/footer');
    }
    
        
    public function AllTechnicialonmap(){
        $this->AUTHOMODEL->check_isvalidated();
        $this->load->view('default/header');
		$this->load->view('default/sidebar');
         $data['technician']=$this->AUTHOMODEL->FourTableJoin("select t.veh_latitude,t.veh_longitude,u.uname as username  from vehicle t left join users u on u.uid=t.uid  where 0=0 and veh_status=1");
        $this->load->view('VEHICLE0001/m/alltechmap',$data);
        $this->load->view('default/footer');
    }
    
    
    
    public function mapalldata(){
        $id=$this->uri->segment(3); 
        
        //$id=$_REQUEST['id'];
        
           
        $details=$this->AUTHOMODEL->FourTableJoin("select min(map_id) as minid ,max(map_id) as mixid from vel_map where map_ordid=$id");
     
        $minid=$details[0]['minid'];
        $maxid=$details[0]['mixid'];
        $customer=$this->AUTHOMODEL->FourTableJoin("select o.order_lang , o.order_long,u.uname as customer from  order_now o  left join users u on u.uid = o.ord_custid  where ord_id= $id");
      
      
       $technician=$this->AUTHOMODEL->FourTableJoin("select m.map_latitude , m.map_longtitute,t.uname as technician ,t.utype as techtype from vel_map m left join order_now o on o.ord_id=m.map_ordid  left join users t on o.ord_velid=t.uid  where map_ordid=$id order by map_id desc limit 1");
      /*echo $this->db->last_query();
      die;*/
        foreach($customer as $user);
            
        foreach($technician as $tech);
        
        echo "<?xml version='1.0' encoding='UTF-8'?>
                <markers>
                <marker status='".$tech['technician']."' lat='".$tech['map_latitude']."' lng='".$tech['map_longtitute']."'/>
                <marker status='".$user['customer']."' lat='".$user['order_lang']."' lng='".$user['order_long']."'/>
                
                <!-- <marker status='busy' lat='37.433480' lng='-122.155062'/> -->
                <!-- <marker status='busy' lat='37.431480' lng='-122.145162'/> -->
                <!-- <marker status='busy' lat='37.429480' lng='-122.185162'/> -->
                <!-- <marker status='busy' lat='37.427480' lng='-122.165162'/> -->
                <!-- <marker status='busy' lat='37.425480' lng='-122.125162'/> -->
                <!-- <marker status='busy' lat='37.445427' lng='-122.162307'/> -->
                </markers>";
    }
    
    
      
    
    public function TechonLiveMap(){
        $id=$this->uri->segment(3);     
        $technician=$this->AUTHOMODEL->FourTableJoin("select t.veh_latitude,t.veh_longitude,u.uname as username  from vehicle t left join users u on u.uid=t.uid  where veh_id=$id");
      /*echo $this->db->last_query();
      die;*/
            
        foreach($technician as $tech);
        
        echo "<?xml version='1.0' encoding='UTF-8'?>
                <markers>
                <marker status='".$tech['username']."' lat='".$tech['veh_latitude']."' lng='".$tech['veh_longitude']."'/>
                </markers>";
    }
    
    
    
      
    
    public function AllTechonLiveMap(){
        $id=$this->uri->segment(3);     
        $technician=$this->AUTHOMODEL->FourTableJoin("select t.veh_latitude,t.veh_longitude,u.uname as username  from vehicle t left join users u on u.uid=t.uid  where 0=0 and veh_status=1");
      /*echo $this->db->last_query();
      die;*/
        echo "<?xml version='1.0' encoding='UTF-8'?>";    
        foreach($technician as $tech){
        
        echo "<markers>
                <marker status='".$tech['username']."' lat='".$tech['veh_latitude']."' lng='".$tech['veh_longitude']."'/>
                </markers>";
        }
    }
    
    
    
    public function GetServicePrice(){
        $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
            $id=$_REQUEST['ServiceId'];
            $serviceprice=$this->AUTHOMODEL->FourTableJoin("select serv_amount as service_price from tech_serviecs where serv_id=$id "); 
             $price=$serviceprice[0]['service_price'];
                $array=array('status'=>1,'message'=>'Service price','data'=>$serviceprice);
        }else{
            $array=array("status"=>0,'message'=>'Server Error 404');
        }
            echo json_encode($array);
    }
    
    
    public function userlogouttime(){
    	 $token=$_POST['token'];
	  	if($this->globals->set_token($token)=='true'){
			$id=$_POST['userloginid'];
			if(isset($_POST['lat']) && $_POST['lat']!=''){
			$lat=$_POST['lat'];
			}
			if(isset($_POST['long']) && $_POST['long']!=''){
			$long=$_POST['long'];
			$login=$_POST['userloginid'];
			$this->AUTHOMODEL->loglanlong($login,$lat,$long,2);
			}
			if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
			}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}else{
			$ip = $_SERVER['REMOTE_ADDR'];
			}
			$data['log_outip']= $ip;
			$udata['device_token']='null';
			$data['log_logoutdatetime']=date('Y-m-d H:i:s');
			$logid=$this->AUTHOMODEL->FourTableJoin("select log_id from user_log where log_user=$id and date_format(log_logoutdatetime,'%Y-%m-%d ')='0000-00-00'  order by log_id asc limit 1"); 
			if(!empty($logid)){
			$userlogid= $logid[0]['log_id'];
			 $this->AUTHOMODEL->editonerecords('user_log','log_id',$userlogid,$data);
			 /*echo $this->db->last_query();
			 die();*/
			 $this->AUTHOMODEL->editonerecords('users','uid',$userlogid,$udata);
			}else{
				$this->AUTHOMODEL->editonerecords('users','uid',$id,$udata);
			}
			$array=array('status'=>1,'message'=>"Logout successfully");
		}else{
			$array=array('status'=>0,'message'=>"404 Server Error");
		}
		echo json_encode($array);
    }
    
    
    
    public function distacetimemap(){
    	 $id=$_REQUEST['seconds_left']; 
    	 $MapApi= $this->globals->MapAipKey();
    	//  $details=$this->AUTHOMODEL->FourTableJoin("select min(map_id) as minid ,max(map_id) as mixid from vel_map where ");
     
        // $minid=$details[0]['minid'];
        // $maxid=$details[0]['mixid'];
        $customer=$this->AUTHOMODEL->FourTableJoin("select m.map_latitude , m.map_longtitute,u.uname as customer from vel_map m left join order_now o on o.ord_id=m.map_ordid left join users u on u.uid = o.ord_custid  where map_ordid=$id order by map_id desc limit 1");
      
      
       $technician=$this->AUTHOMODEL->FourTableJoin("select o.order_lang , o.order_long,t.uname as technician ,t.utype as techtype from order_now  o left join users t on o.ord_velid=t.uid  where ord_id in($id)");
//echo $this->db->last_query();
         foreach($customer as $user);
            
        foreach($technician as $tech);

      	 $techaddress=$user['map_latitude'].','.$user['map_longtitute'];
      	 $useraddress=$tech['order_lang'].','.$tech['order_long'];
    	//$url="https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=buxar&destinations=patna&key=AIzaSyCgMWVCNSd7JrIqIRCc3wRyDZttXFIvtMI";
    	 $url="https://maps.googleapis.com/maps/api/distancematrix/json?origins=$techaddress&destinations=$useraddress&language=fr-FR&key=$MapApi";
     	 $ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL, $url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	curl_setopt($ch, CURLOPT_USERPWD,true);
      	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
       	$output = curl_exec($ch);
       	$info = curl_getinfo($ch);
       	curl_close($ch);
       	$results=(array)json_decode($output);
       $first=(array)$results['rows'][0];
       foreach($first as $ft1)
       $second=$ft1;
   		foreach ($second as $key => $value) 
   			$distance=(array)$value->distance->text;
   			$duration=(array)$value->duration->text;
   			foreach ($distance as $key => $dis)
   			foreach ($duration as $key => $time)
       	?>
       	<tr>
       		<td><?php echo $results['origin_addresses'][0]; ?></td>
       		<td><?php echo $results['destination_addresses'][0]; ?></td>
       		<td><?php echo $dis; ?></td>
       		<td><?php echo $time; ?></td>

       	</tr>

       	<?php
       	$dmap['ord_id'] =$id;
        $msv=$this->AUTHOMODEL->fiendfield("order_now","order_estamatetime",$dmap);
        if($msv['order_estamatetime']==''){
        	$esamatetime['order_estamatetime']=$time;
        	$this->AUTHOMODEL->editonerecords('order_now','ord_id',$id,$esamatetime);
        }

    }
    
    public function CreateNewUserByTechnician(){
			$token=$_POST['token']; 
			$message='';
			$MapApi= $this->globals->MapAipKey();
			if($this->globals->set_token($token)=='true'){
				if(isset($_POST['name'])&& $_POST['name']!="")
				$data['uname']=$_POST['name'];
				if(isset($_POST['mobile'])&& $_POST['mobile']!="")
				$data['mobile']=$_POST['mobile'];
				if(isset($_POST['email'])&& $_POST['email']!="")
				$data['email']	=$_POST['email'];
				if(isset($_POST['type'])&& $_POST['type']!="")
				$data['utype']	=$_POST['type'];
				if(isset($_POST['device_token'])&& $_POST['device_token']!="")
				$data['device_token'] =$_POST['device_token'];
				$data['uentdt']= date('Y-m-d H:i:s');
				$validat=$this->AUTHOMODEL->insert_validation('users',$_POST['email'],$_POST['mobile'],$_POST['status']);
						$valid= explode(',', $validat);
						if($valid[0]=='true'){
						$useremail=$_POST['email'];
						$usermobile=$_POST['mobile'];
						$getnew_users=$this->AUTHOMODEL->FourTableJoin("select uid as UserId from users where email='".$useremail."' or mobile=$usermobile");
						foreach($getnew_users as $ft)
						$id=$ft['UserId'];
				}else{
						$pass=rand(11111111,99999999);
						$data['upassword']=$this->globals->password_check($pass);
						$this->AUTHOMODEL->record_insert('users',$data);
						$id= $this->db->insert_id();
						$data1['uid']=$id;
						if(isset($_POST['address']) && $_POST['address']!=''){
						$address=$_POST['address'];
						$address = str_replace(" ", "+", $address);
						$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$MapApi");
						$json = json_decode($json);
						$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
						$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
					}
						$data1['latitude']=$lat;
						$data1['longitude']=$long;
						$data1['address']=$_POST['address'];
						$data1['name']=$_POST['name'];
						if(isset($_POST['mobile'])&& $_POST['mobile']!="")
						$data1['mobile']=$_POST['mobile'];
						$data1['email']	=$_POST['email'];
						$data1['enterydt']= date('Y-m-d H:i:s');
						$this->AUTHOMODEL->record_insert('customer',$data1);

				}
						$condition['email'] =$_POST['email'];
		                $condition['uid']=$id;
		                $customerrec= $this->AUTHOMODEL->getallbytwocond('*','users',$condition);
		                if($customerrec>0){ foreach($customerrec as $ft){ $arr[]=$ft; }
						$array=array('status'=>1,'message'=>'Your are register  Successful','data'=>$arr);
						echo json_encode($array);
				
    		}
	}
}
    
    
    
    
    
    public function EmailValidation(){
    	$email=$_REQUEST['UserEmail'];
    	$pass=rand(11111111,99999999);
    	$this->AUTHOMODEL->Sendmails($get_data,$message,$subject);

    }
    
    
    public function CountImages(){
    	$id=$_REQUEST['OrderId'];
    	$data['ord_id']=$id;
    	$imagename=$this->AUTHOMODEL->fiendfield('order_now','order_img',$data);
    	$videoname=$this->AUTHOMODEL->fiendfield('order_now','order_video',$data);
    	$image=json_decode($imagename['order_img']);
    	$video=json_decode($videoname['order_video']);
    	$size=sizeof($image)-1;
    	$sizevideo=sizeof($video);
    	$array=array('status'=>1,"CountImage"=>$size,"VideoName"=>$sizevideo);
    	echo json_encode($array);

    }
    
    
    
    
      
    public function DailyReports(){
    $this->AUTHOMODEL->check_isvalidated();
    $this->load->view('default/header');
    $this->load->view('default/sidebar');
    $token=$this->globals->web_token();
    $id =  $this->uri->segment(3);
    $data['alltechnicianrecords']=$this->AUTHOMODEL->FourTableJoin("select uname,uid as techid from users where 0=0 and status=1 and utype=1 order by uname");
    $this->load->view('VEHICLE0001/daily_technician_report',$data);
    $this->load->view('default/footer');

    }
    
    public function loadmaproute(){
		$MapApi= $this->globals->MapAipKey();
		$id=$_REQUEST['orderid'];
		$lowerlimit=$_REQUEST['limitdata'];
		$string1=array();
		$maplanglong1=$this->AUTHOMODEL->FourTableJoin("select map_latitude as lag,map_longtitute as log,map_stop_time,map_entdt,map_id from vel_map  where map_ordid=$id and map_stop_time not in(1,5) limit $lowerlimit,10");
		
	    if(!empty($maplanglong1)){
	     foreach($maplanglong1 as $laglong1){ 
	     $lang=$laglong1['lag'];  
	     $long=$laglong1['log'];
	     $stop=$laglong1['map_stop_time']; 
	     $time=$laglong1['map_entdt'];
	     if($stop==1){
	          $arra1=array("title"=>$id,"lat"=>$lang,"lng"=>$long,"description"=>"","markeridel"=>"ideal","stoptime"=>$stop,'time'=>$time);
	         array_push($string1,$arra1);
	       }else{
	          $arra1=array("title"=>$id,"lat"=>$lang,"lng"=>$long,"description"=>"","markeridel"=>'','stoptime'=>$stop,'time'=>$time);
	         array_push($string1,$arra1);
	       }

	     /*End Route Create*/

	   }
	 }
	 $wayPoints1 =  $string1;
		?>
		 <?php $t=0;
 $logintime="";
 $logouttime="";

 if(!empty($wayPoints1)){
  //print_r($wayPoints1);
  $timeshift='';
foreach($wayPoints1 as $ftc){ $t++;
   $latitude=$ftc['lat'];
  $longitude=$ftc['lng'];
   $geocodeFromLatLong = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=true_or_false&key=AIzaSyDx8L4LRa0oiZrjmkuvBHZO7Q7Ndtsbda8');
  
    $filder1=json_decode($geocodeFromLatLong);
  

  //$geocodeFromLatLong = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false&key=AIzaSyAAX8CwqEWPRj0rrLo3nrwRmASF3KT0PDQ'); 
        $output = json_decode($geocodeFromLatLong);
         $status = $output->status;
        //Get address from json data
        $address = ($status=="OK")?$output->results[1]->formatted_address:'';
        //Return address of the given latitude and longitude
        if($ftc['markeridel']=="login"){
          $logintime=  date('d-m-Y H:i:s',strtotime($ftc['time']));
        }
        if($ftc['markeridel']=="logout"){
            $logouttime=  date('d-m-Y H:i:s',strtotime($ftc['time']));
        }
        if(date('d-m-Y H:i:s',strtotime($ftc['time']))!=$timeshift){
          $timeshift=date('d-m-Y H:i:s',strtotime($ftc['time']));
  ?>
 
 <tr>
   
    <td><?php if($ftc['markeridel']=="login") echo "Login"; elseif($ftc['markeridel']=="logout") echo "logout"; else echo $ftc['title']; ?></td>
    <td><?php if(!empty($address)){
            echo  $address;
        }else{
            return false;
        } ?></td>
        <td><?php $time= @$ftc['stoptime']; echo @gmdate("H:i:s",$time); ?></td>
        <td><?php echo date('d-m-Y H:i:s',strtotime($ftc['time'])); ?></td>
 </tr>
<?php } } } ?>
		<?php 
	}
    
    public function autoChangeDeviceToken(){
		$token=$_POST['token'];
		$array=array();
		if($this->globals->set_token($token)=='true'){
				$uid=$_POST['userid'];
				$device_token['device_token']=$_POST['deviceToken'];
				$this->AUTHOMODEL->editonerecords('users',$uid,$uid,$device_token);	
				$array=array('status'=>1);
		}else{
			$array=array('status'=>0,'message'=>'Server Error 404');
	   }
		echo json_encode($array);
	}
    
    
    public function notificationList(){
		$token=$_POST['token'];
		$array=array();
		if($this->globals->set_token($token)=='true'){
			$id=$_POST['userid'];
			$notificationlist=$this->AUTHOMODEL->FourTableJoin("select * from notification_list where list_uid=$id");
			if(!empty($notificationlist)){
			foreach($notificationlist as $notice){
				$row[]=$notice;
			}
			$array=array('status'=>1,'message'=>"Success",'data'=>$row);
		}else{
			  
			$array=array('status'=>0,'message'=>"Unsuccess");
		}	
		}else{

			$array=array('status'=>0,'message'=>"else works");
	   }
		echo json_encode($array);
	}





    public function ShowUsersOrder_tech(){
        $token=$_POST['token'];
        $id=$_POST['userid'];
        $data['uid'] =$id;
        $itmes='';

        if($this->globals->set_token($token)=='true'){
            $valid=$this->AUTHOMODEL->ValidToAnyId('users',$data);
            if($valid){

                /*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.id=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.veh_id=o.ord_velid where 0=0 and ord_custid=$id and ord_status!=2  order by veh_name,ord_time,cat_name ASC");*/

                /*$details=$this->AUTHOMODEL->FourTableJoin("select c.cat_id,c.cat_name,o.ord_catid,o.ord_custid,o.ord_velid,o.ord_entdy,o.ord_id,o.ord_status,o.ord_endadd,o.ord_straddress,o.ord_milege,o.ord_norqt,o.ord_time,cu.id,cu.name,cu.mobile,cu.email,v.veh_id,v.veh_name,v.veh_contectno,v.veh_number,v.veh_address,v.veh_latitude,v.veh_latitude,v.veh_longitude,v.veh_status from order_now o left join customer cu on cu.uid=o.ord_custid left join category c on c.cat_id = o.ord_catid left join vehicle v on v.uid=o.ord_velid left join users u on u.uid=cu.uid left join users us on us.uid =v.uid where 0=0 and (u.uid=$id or us.uid=$id) and ord_status!=2  order by date_format(ord_time,'%Y-%m-%d') DESC");
                */



                $details=$this->AUTHOMODEL->FourTableJoin("

select ts.serv_name as service_name,c.cat_id as category_id,c.cat_name as category_name,
o.ord_catid as order_category_id,o.order_time as technician_per_day_time ,
o.order_modified_datetime,o.ord_custid as order_customer_id,o.description,
o.ord_velid as order_technician_id,o.ord_entdy,o.ord_id as order_id,
o.ord_status as order_status,o.ord_endadd as order_end_address,
o.ord_straddress as order_start_address,o.order_img as ordreimage,
o.order_video as ordervideo,o.ord_milege,o.ord_norqt,o.ord_time as user_order_date,
user.uid as id ,user.uname as name,user.mobile as mobile,user.email as  email,
user.utype as usertype,v.veh_id,v.veh_name as technician_name,
v.veh_contectno as technician_mobileno,v.veh_number,v.veh_address as technician_address,
v.veh_latitude as technician_latitude,v.veh_longitude as techinician_longitude,
v.veh_status as techinican_status,v.price as technician_price,v.veh_service as serviceId,
o.order_modify_time,o.order_arrival as arrivalstauts,o.order_arrival_time as arrivaltime,
n.notice_uid as reasonuserid,n.notice_data reasoin,n.notice_status as detetestatus,
o.order_feedback as feedback,usl.s_service_id from order_now o 
left join users user on user.uid=o.ord_custid 
left join category c on c.cat_id = o.ord_catid 
left join vehicle v on v.uid=o.ord_velid 
left join users u on u.uid=user.uid 
left join users us on us.uid =v.uid 
left join tech_serviecs ts on ts.serv_id=v.veh_service 
left join notification_type n on n.notice_orderid=o.ord_id 
left join user_service usl on usl.s_ordid=o.ord_id 
where 0=0 and (o.ord_velid=$id) and ord_status !=4  group by o.ord_id order by o.ord_id asc;
");



                if(empty($details)){
                    $array=array(
                        "status"=>0,
                        "message"=>'Records Not Found'
                    );
                    echo  json_encode($array);

                }else{

                    foreach($details as $ft){
                        $Serviceid1=ltrim($ft['s_service_id'],'[');
                        $itemid= str_replace(' ','',rtrim($Serviceid1,']'));
                        if(!empty($itemid)){
                            $data=" and serv_id in($itemid)";
                            $getitmen=$this->AUTHOMODEL->GetItemName('tech_serviecs',$data);
                            $ft['service_name']=implode(',',$getitmen['ServiceName']);
                            $ft['technician_price']=$getitmen['totalAmount'];
                        }
                        $arr[]=$ft;
                    }

                    $array=array(
                        "status"=>1,
                        "message"=>'All Records',"data "=>$arr
                    );
                    echo  json_encode($array);
                }
            }else{
                $array=array(
                    "status"=>0,
                    "message"=>'Records Not Found'
                );
                echo  json_encode($array);

            }

        }else{
            $array=array(
                "status"=>0,
                "message"=>'404 Error '
            );
            echo  json_encode($array);
        }
    }



/* End */






}

?>
