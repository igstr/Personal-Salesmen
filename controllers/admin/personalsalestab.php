<?php 
    class personalsalestabController extends ModuleAdminController {
        public function __construct() {
          parent::__construct();
        }


        public function renderList(){
            //include(_PS_MODULE_DIR_.'personalsalesmen/personalsalesmen.php'); 
            $this->personalsales = new personalsalesmen();
            //$this->personalsales->displayAdvert();
            $msg="";
    		if (isset($_POST['idcus'])){
    			if (is_numeric($_POST['idcus'])){
    				$msg=$this->personalsales->linkcustomerbyid($_POST['idcus'],1);
    			}
    		}
            
    		//$this->personalsales->token = $this->token;
    		//echo $this->personalsales->displayinputid();
    		
            return $msg.$this->personalsales->displayAdvert(1).$this->personalsales->displayinputid(1).$this->personalsales->displayFooter(1);
        }
    }
?>