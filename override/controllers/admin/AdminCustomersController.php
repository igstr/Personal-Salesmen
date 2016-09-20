<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @property Customer $object
 */
class AdminCustomersController extends AdminCustomersControllerCore
{
    protected $delete_mode;

    protected $_defaultOrderBy = 'date_add';
    protected $_defaultOrderWay = 'DESC';
    protected $can_add_customer = true;
    protected static $meaning_status = array();

    public function __construct()
    {
        parent::__construct();
        
        $this->bootstrap = true;
        $this->required_database = true;
        $this->required_fields = array('newsletter','optin');
        $this->table = 'customer';
        $this->className = 'Customer';
        $this->lang = false;
        $this->deleted = true;
        $this->explicitSelect = true;

        $this->allow_export = true;

        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $titles_array = array();
        $genders = Gender::getGenders($this->context->language->id);
        foreach ($genders as $gender) {
            /** @var Gender $gender */
            $titles_array[$gender->id_gender] = $gender->name;
        }

        global $cookie;

        if ($cookie->id_employee == 1)
        {
            $this->_select = '
            (SELECT so.id_employee FROM `'._DB_PREFIX_.'personalsalesmen` so WHERE so.id_customer = a.id_customer) as personalsales,
            a.date_add, gl.name as title, (
                SELECT SUM(total_paid_real / conversion_rate)
                FROM '._DB_PREFIX_.'orders o
                WHERE o.id_customer = a.id_customer
                '.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').'
                AND o.valid = 1
            ) as total_spent, (
                SELECT c.date_add FROM '._DB_PREFIX_.'guest g
                LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest
                WHERE g.id_customer = a.id_customer
                ORDER BY c.date_add DESC
                LIMIT 1
            ) as connect';
            $this->_join = 'LEFT JOIN '._DB_PREFIX_.'gender_lang gl ON (a.id_gender = gl.id_gender AND gl.id_lang = '.(int)$this->context->language->id.')';
        }else
        {
            $this->_select = '
                a.date_add,
                gl.name as title,
                (SELECT SUM(total_paid_real / conversion_rate) FROM '._DB_PREFIX_.'orders o WHERE o.id_customer = a.id_customer '.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').' AND o.valid = 1) as total_spent,
                (SELECT c.date_add FROM '._DB_PREFIX_.'guest g LEFT JOIN '._DB_PREFIX_.'connections c ON c.id_guest = g.id_guest WHERE g.id_customer = a.id_customer ORDER BY c.date_add DESC LIMIT 1) as connect,
            (SELECT so.id_employee FROM `'._DB_PREFIX_.'personalsalesmen` so WHERE so.id_customer = a.id_customer) as personalsales
            ';
            
            $this->_join = 'LEFT JOIN '._DB_PREFIX_.'gender_lang gl ON (a.id_gender = gl.id_gender AND gl.id_lang = '.(int)$this->context->language->id.')
            JOIN `'._DB_PREFIX_.'personalsalesmen` psm ON (psm.`id_employee` = '.$cookie->id_employee.' AND psm.`id_customer` = a.`id_customer`)
            ';
        }

        $this->_use_found_rows = false;
        $this->fields_list = array(
            'id_customer' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'title' => array(
                'title' => $this->l('Social title'),
                'filter_key' => 'a!id_gender',
                'type' => 'select',
                'list' => $titles_array,
                'filter_type' => 'int',
                'order_key' => 'gl!name'
            ),
            'firstname' => array(
                'title' => $this->l('First name')
            ),
            'lastname' => array(
                'title' => $this->l('Last name')
            ),
            'email' => array(
                'title' => $this->l('Email address')
            ),
        );

        if (Configuration::get('PS_B2B_ENABLE')) {
            $this->fields_list = array_merge($this->fields_list, array(
                'company' => array(
                    'title' => $this->l('Company')
                ),
            ));
        }

        $this->fields_list = array_merge($this->fields_list, array(
            'total_spent' => array(
                'title' => $this->l('Sales'),
                'type' => 'price',
                'search' => false,
                'havingFilter' => true,
                'align' => 'text-right',
                'badge_success' => true
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'filter_key' => 'a!active'
            ),
            'newsletter' => array(
                'title' => $this->l('Newsletter'),
                'align' => 'text-center',
                'type' => 'bool',
                'callback' => 'printNewsIcon',
                'orderby' => false
            ),
            'optin' => array(
                'title' => $this->l('Opt-in'),
                'align' => 'text-center',
                'type' => 'bool',
                'callback' => 'printOptinIcon',
                'orderby' => false
            ),
            'date_add' => array(
                'title' => $this->l('Registration'),
                'type' => 'date',
                'align' => 'text-right'
            ),
            'connect' => array(
                'title' => $this->l('Last visit'),
                'type' => 'datetime',
                'search' => false,
                'havingFilter' => true
            )
        ));

        $this->shopLinkType = 'shop';
        $this->shopShareDatas = Shop::SHARE_CUSTOMER;

        // Check if we can add a customer
        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->can_add_customer = false;
        }

        self::$meaning_status = array(
            'open' => $this->l('Open'),
            'closed' => $this->l('Closed'),
            'pending1' => $this->l('Pending 1'),
            'pending2' => $this->l('Pending 2')
        );
        
        AdminController::__construct();

    }
   /**
     * add to $this->content the result of Customer::SearchByName
     * (encoded in json)
     *
     * @return void
     */
    public function ajaxProcessSearchCustomers()
    {
        global $cookie;
        $CustRay = array();

        $sql = 'SELECT * FROM '._DB_PREFIX_.'personalsalesmen WHERE id_employee = '.$cookie->id_employee.'';
        if ($Listresults = Db::getInstance()->ExecuteS($sql))
            foreach ($Listresults as $row)
                array_push($CustRay, $row['id_customer']);
        

        if ($cookie->id_employee == 1){
        #if(count($CustRay) > 1){
            $searches = explode(' ', Tools::getValue('customer_search'));
            $customers = array();
            $searches = array_unique($searches);
            foreach ($searches as $search) {
                if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                    foreach ($results as $result) {
                        if ($result['active']) {
                            $customers[$result['id_customer']] = $result;
                        }
                    }
                }
            }
    
            if (count($customers)) {
                $to_return = array(
                    'customers' => $customers,
                    'found' => true
                );
            } else {
                $to_return = array('found' => false);
            }
    
            $this->content = Tools::jsonEncode($to_return);
        }
        else{
            $searches = explode(' ', Tools::getValue('customer_search'));
            $customers = array();
            $searches = array_unique($searches);
            foreach ($searches as $search) {
                if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                    foreach ($results as $result) {
                        if ($result['active'] && in_array($result['id_customer'], $CustRay)) {
                                    $customers[$result['id_customer']] = $result;
                        }
                    }
                }
            }
    
            if (count($customers)) {
                $to_return = array(
                    'customers' => $customers,
                    'found' => true
                );
            } else {
                $to_return = array('found' => false);
            }
    
            $this->content = Tools::jsonEncode($to_return);
        }
    }
}