<?php
include(_PS_MODULE_DIR_.'personalsalesmen/personalsalesmen.php');

class personalsalestab14 extends AdminTab
{
  	public function __construct(){
	    $this->personalsales = new personalsalesmen();
	    return parent::__construct();
  	}

  	public function display(){
  		$this->personalsales->displayAdvert();
			
		if (isset($_POST['idcus'])){
			if (is_numeric($_POST['idcus'])){
			
				$this->personalsales->linkcustomerbyid($_POST['idcus']);
			}
		}		
		
		$this->personalsales->token = $this->token;
		
		$this->personalsales->displayinputid();
		$this->personalsales->displayFooter();
  	}
}
?>