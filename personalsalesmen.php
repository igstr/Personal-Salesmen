<?php 
class personalsalesmen extends Module {
	function __construct(){
		$this->name = 'personalsalesmen';
        $this->author = 'inform-all.nl';
		$this->tab = 'Other';
		$this->version = '2.6';
        $this->module_key = '';
        $this->dir = '/modules/personalsalesmen/';
		parent::__construct();
		$this->displayName = $this->l('Personal Salesmen');
		$this->description = $this->l('Personal Salesmen is for every shop that has personal sales within your emplyees. Developed by inform-all.nl');
		$this->tab = 'Admin';
        $this->tabClassName = 'personalsalestab';
        $this->tabParentName = 'AdminOrders';
	}

	public function viewAccess($disable = false){
	        $result = true;
	        return $result;
	}
   
	function install(){
		if (parent::install() == false){
            return false;
        }
    	if (!$id_tab) {
	      	$tab = new Tab();
            if ($this->psversion()==5 || $this->psversion()==6){
	      	    $tab->class_name = $this->tabClassName;
            } else {
                $tab->class_name = $this->tabClassName."14";
            }
	      	$tab->id_parent = Tab::getIdFromClassName($this->tabParentName);
	      	$tab->module = $this->name;
	      	$languages = Language::getLanguages();
	      	foreach ($languages as $language)
		        $tab->name[$language['id_lang']] = $this->displayName;
	    	$tab->add();
    	}

		$this-> context-> smarty-> assign ('HOOK_DISPLAY_BACK_OFFICE_HOME', Module :: hookExec ('displayBackOfficeHome')); 
		

		$this->copyDir(dirname(__FILE__) . '/override/controllers/admin/templates/', dirname(__FILE__) . '/../../override/controllers/admin/templates/');

		$this->registerHook('displayBackOfficeHome');

		Configuration::updateValue('NW_SALT', Tools::passwdGen(16));

		return Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'personalsalesmen` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_employee` INTEGER UNSIGNED NOT NULL DEFAULT \'1\',
			`id_customer` INTEGER UNSIGNED NOT NULL,
			PRIMARY KEY(`id`)
		) ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8');

        return true;
	}
	
	public function uninstall(){
		if (parent::uninstall() == false) {
			return false;
		}

        // Uninstall Tabs
		$moduleTabs = Tab::getCollectionFromModule($this->name);
		if (!empty($moduleTabs)) {
			foreach ($moduleTabs as $moduleTab) {
				$moduleTab->delete();
			}
		}


		Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'personalsalesmen');

		if (is_file(dirname(__FILE__) . '/../../override/controllers/admin/templates/orders/helpers/view/view.tpl'))
            return unlink(dirname(__FILE__) . '/../../override/controllers/admin/templates/orders/helpers/view/view.tpl');

	    return true;
	}
	
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
	
	public function displayAdvert($return=0){
		$ret = "<div style=\"display:block; text-align:left; margin-top:5px;\">";
		$ret.= $this->l('Accounts wil never be deleted, only the links made in Personal Salesmen')."<br/><br/>";
		$ret.= "</div>";
        if ($return==0){
            echo $ret;
        } else {
            return $ret;
        }
	}
	
	public function displayFooter($return=0){
		$ret = "<div style=\"display:block; text-align:left; margin-top:5px;\">";
		$ret.= $this->l('proudly developed by')." <a style=\"font-weight:bold; color:green;\" href=\"http://inform-all.nl\" target=\"_blank\">Inform-All.nl</a><br/><br/>";
        $ret.= '<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2FInform-All-1744954845723557/&amp;width=200&amp;height=62&amp;show_faces=false&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=211918662219581" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:62px;" allowTransparency="true"></iframe>';
		$ret.= "</div>";
        if ($return==0){
            echo $ret;
        } else {
            return $ret;
        }
	}


	public function getContent()
	{
	    $output = null;
	 
	    if (Tools::isSubmit('submit'.$this->name))
	    {
	        $my_module_name = strval(Tools::getValue('personalsalesmen'));
	        if (!$my_module_name
	          || empty($my_module_name)
	          || !Validate::isGenericName($my_module_name))
	            $output .= $this->displayError($this->l('Invalid Configuration value'));
	        else
	        {
	            Configuration::updateValue('personalsalesmen', $my_module_name);
	            $output .= $this->displayConfirmation($this->l('Settings updated'));
	        }
	    }
	    return $output.$this->displayForm();
	}
	
	
	public function displayinputid($return=0){
	   if ($this->psversion()==5 || $this->psversion()==6){
	       $verps="";
	   } else {
	       $verps="14";
	   }

          $resultemp= Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'employee`');

        //idemploye
        $selectemp="<select name=\"idemp\" id=\"idemp\">";
        
         $selectemp .= "<option value =\"0\" hidden>Employee</option>";
         foreach ($resultemp as $k) {
         $selectemp .= "<option value=".$k["id_employee"].">".$k["firstname"]." ".$k["lastname"]."</option>" ;
        }

         $selectemp.="</select >";



        $resultcustomer = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'customer`');

        //idcustomer
        $selectCustomer="<select name=\"idcus\" id=\"idcus\">";
               
          $selectCustomer .= "<option value =\"0\" hidden>Client</option>";
         foreach ($resultcustomer as $k) {
         $selectCustomer .= "<option value=".$k["id_customer"].">".$k["firstname"]." ".$k["lastname"]."</option>" ;
        }

         $selectCustomer.="</select >";

          //idcustomer
        $selectCustomerdel="<select name=\"delcus\" id=\"delcus\">";
        $selectCustomerdel .= "<option value =\"0\" hidden>Client</option>";       
                    
         foreach ($resultcustomer as $k) {
         $selectCustomerdel .= "<option value=".$k["id_customer"].">".$k["firstname"]." ".$k["lastname"]."</option>" ;
        }

         $selectCustomerdel.="</select >";


        
        


          $resultlistcusemp = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT  pe.firstname as nomemploi ,
          	pe.lastname as nomemploi2 ,
          	ps.id_customer as idclient,
          	ps.firstname as nomclien ,
          	ps.lastname as nomcl2 
          	FROM '._DB_PREFIX_.'_personalsalesmen p
          	INNER JOIN '._DB_PREFIX_.'_customer ps 
          	ON p.id_customer=ps.id_customer
          	INNER JOIN '._DB_PREFIX_.'_employee pe 
          	ON pe.id_employee  = p.id_employee');

        //idcus
        $listecusemp="<table id=\"example\" class=\"table table-striped table-bordered\" cellspacing=\"0\" width=\"100%\"> <tr><thead><td style=\"color:green\">Employee</td> <td style=\"color:green\">Customer</td> <td style=\"color:green\">ID Customer</td></tr></thead>";
               
         
         foreach ($resultlistcusemp as $k) {
         $listecusemp .= "<tr><td>".$k["nomemploi"]."".$k["nomemploi2"]."</td> <td>".$k["nomclien"]." ".$k['nomcl2'] ." </td><td>".$k['idclient']."</td></tr>" ;
        }

         $listecusemp .="</table>


               
         ";
 
      
      


		$ret= "
		  <style>
			          table {
			    border-collapse: collapse;
			}

			table, th, td {
			    border: 1px solid ;
			    padding: 5px;
			    width: 400px;
			}
			select {
				width: 288px!important;
                text-align: center!important;
			}
			select:invalid { color: gray; }

		</style>
			<script>
			             $(document).ready(function() {
			    $('#example').DataTable();
			} );
						
			function linkcustomerbyid(id,msg){
				
					
						document.getElementById(\"linkbyid\").submit();
					
			}			
		</script>
		
		<fieldset style = \" width: 800px;\">
			<div align=\"center\" style=\"margin-bottom:20px;\">
			<h3>".$this->l('Link a customer to a employee.')."</h3>
				<form action=\"index.php?tab=personalsalestab$verps&token={$_GET['token']}\" method=\"post\" id=\"linkbyid\" name=\"linkbyid\">

				<strong>".$this->l('Select customer :')."<br/></strong><br>
				".$selectCustomer."<br/><br/>

				<strong>".$this->l('Select employee:')."<br/></strong><br>
				".$selectemp."<br/><br/>

				<img src=\"../modules/personalsalesmen/save.png\" onClick=\"linkcustomerbyid(document.getElementById('idcus'),'".$this->l('Are you sure you want link this customer:')." #"."'+document.getElementById('idcus').value+'"." ".$this->l('to this employee: ')." #"."'+document.getElementById('idemp').value+'"."');\" style=\"cursor:pointer;\" >
				<br> <br> <br> <br>
				<strong>".$this->l('Delete all of the linked employees of a customer.')."<br/></strong>
				<br> 

				".$selectCustomerdel."<br/><br/>

				<img src=\"../modules/personalsalesmen/delete.gif\" onClick=\"linkcustomerbyid(document.getElementById('delcus'),'".$this->l('Are you sure you want delete this customers links:')." #"."'+document.getElementById('delcus').value+'"."');\" style=\"cursor:pointer;\" >			
				</form>
			</div>
			<br>
			<br>
			<br>
			<center>
				<h3>List of linked customers/employees .</h3>
               ".$listecusemp."
               </center>
		</fieldset>
        ";
        
        if ($return==0){
            echo $ret;
        } else {
            return $ret;
        }
	}	
	
	public function linkcustomerbyid($id,$etat,$return=0){
		$psversion=$this->psversion();
		$idem = $_POST["idemp"];
		$delcus = (int)$_POST["delcus"];
		$Count = (int)Db::getInstance()->getValue('SELECT COUNT(p.id_customer) FROM '._DB_PREFIX_.'_personalsalesmen p where p.id_customer = '.$id.' ') ;


		if ($psversion==5 || $psversion==6){
			if ($delcus){
				$q = 'DELETE FROM '._DB_PREFIX_.'personalsalesmen WHERE id_customer="'.$delcus.'"';
				if(!Db::getInstance()->Execute($q)){$this->errorlog[] = $this->l("ERROR");}	
			}

			 elseif (($id != 0) && ($idem != 0) ){
				$q= 'INSERT INTO `'._DB_PREFIX_.'personalsalesmen`(`id_customer`,`id_employee`) VALUES ("'.$id.'","'.$idem.'")';
				if(!Db::getInstance()->Execute($q)){$this->errorlog[] = $this->l("ERROR");}		
			}

				
				
			if (empty($this->errorlog)){
				$ret= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Customer linked!').'" />'.$this->l('Customer linked!').'</div>';
			} else {
				$ret= '<div class="warn error"><img src="../img/admin/warning.gif" alt="'.$this->l('Something wrong').'" />'.$this->l('Something wrong').'</div>';
			}
				
			} else {
					$ret= '<div class="warn error"><img src="../img/admin/warning.gif" alt="'.$this->l('Customer with this id doesnt exists').'" />'.$this->l('Customer with this id doesnt exists').'</div>';
			}
        
        if ($return==0){
            echo $ret;
        } else {
            return $ret;
        }
	}


	public function displayForm()
	{
	    // Get default language
	    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$options = array(
		  array(
		    'id_option' => 1,                 // The value of the 'value' attribute of the <option> tag.
		    'name' => 'Show'              // The value of the text content of the  <option> tag.
		  ),
		  array(
		    'id_option' => 2,
		    'name' => 'Hide'
		  ),
		);
	     
	    // Init Fields form array
	    $fields_form[0]['form'] = array(
	        'legend' => array(
	            'title' => $this->l('Settings'),
	        ), 
			  'input' => array(       
			    array(           
			      'type' => 'select',
			      'label'=> $this->l('Extra order info for Personal Employees.'),
			      'name' => 'personalsalesmen',
			      'options' => array(
  				  	'query' => $options,                           
  				  	'id' => 'id_option',                            
  				  	'name' => 'name'                                
  				)
			     ),
			  ),
			  'submit' => array(
			    'title' => $this->l('Save'),       
			    'class' => 'button'   
			  )
			);
	     
	    $helper = new HelperForm();
	     
	    // Module, token and currentIndex
	    $helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
	     
	    // Language
	    $helper->default_form_language = $default_lang;
	    $helper->allow_employee_form_lang = $default_lang;
	     
	    // Title and toolbar
	    $helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	        'save' =>
	        array(
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ),
	        'back' => array(
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        )
	    );
	     
	    // Load current value
	    $helper->fields_value['personalsalesmen'] = Configuration::get('personalsalesmen');
	     
	    return $helper->generateForm($fields_form);
	}


    /**
     * Copie du contenu d'un dossier vers un autre emplacement
     * @param string $dir2copy : Chemin du dossier à copier
     * @param string $dir_paste : Chemin vers lequel copier le dossier
     * @return void
     */
    public function copyDir($dir2copy, $dir_paste) {
        if (is_dir($dir2copy)) {
            if ($dhd = opendir($dir2copy)) {
                while (($file = readdir($dhd)) !== false) {
                    if (!is_dir($dir_paste))
                        $create_dir = mkdir($dir_paste, 0777);
                    if (is_dir($dir2copy . $file) && $file != '..' && $file != '.')
                        $this->copyDir($dir2copy . $file . '/', $dir_paste . $file . '/');
                    elseif ($file != '..' && $file != '.')
                        $copy_file = copy($dir2copy . $file, $dir_paste . $file);
                }
                closedir($dhd);
            }
        }
    }
	
}
?>
