<?php
// Standard GPL and phpdocs
namespace local_useremail_logs\output;                                                                                                         
                                                                                                                                    
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;                                                                                                                       
             
class index_page implements renderable, templatable {                                                                               
    /** @var string $sometext Some text to show how to pass data to a template. */                                                  
    var $userid = null;                                                                                                           
            
    public function __construct($userid) {                                                                                        
        $this->userid = $userid;                                                                                                
    }

    /**                                                                                                                             
     * Export this data so it can be used as the context for a mustache template.                                                   
     *                                                                                                                              
     * @return stdClass                                                                                                             
     */                                                                                                                             
    public function export_for_template(renderer_base $output) { 
    	global $DB;                                                                   
        $data = new stdClass();                                                                                                     
        $data->userid = $this->userid;                                                                                          
        $data->communicationid = $this->communicationid;
        $data->messageid =$this->messageid;
        


       $d1= $DB->get_records_sql("SELECT ctr.id, ctr.userid as communication_id, ctr.timecreated as sendtime, c.name as communcation_name, ct.name as communication_templates_name
       	FROM {communication_trigger} as ctr
       	INNER JOIN {communication} as c ON ctr.communicationid = c.id
       	INNER JOIN {communication_templates} as ct ON ct.id = c.template 
       	WHERE ctr.userid=$this->userid
       	");
       	//LEFT JOIN {email_custom_cron} as e_cc ON ctr.userid =e_cc.userid

       $d2 = $DB->get_records_sql("SELECT ctr.id, ctr.userid as communication_id, ctr.email_senddate as sendtime, 'Cron' as communcation_name, ct.name as communication_templates_name
       	FROM {email_custom_cron} as ctr
       	INNER JOIN {communication_templates} as ct ON ct.id = ctr.templateid 
       	WHERE ctr.userid=$this->userid
       	");
       $d = array_merge(array_values($d1),array_values($d2));
         
        foreach ($d as $key => $d1) {
         	$d1->sendtime = date("d F Y, h:i A", $d1->sendtime);
         	$d[$key] = $d1;
        }
        $data->communication=$d;
      	//echo "<pre>";
        //print_r($d); 
        //die;
        return $data;                                                                                                           
    }
}