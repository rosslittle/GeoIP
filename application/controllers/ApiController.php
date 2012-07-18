<?php

class ApiController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    
    public function requestAction()
    {
        // start
        $str_ip = $this->_getParam('ip');
        $zend_validate_ip = new Zend_Validate_Ip();
        if (is_null($str_ip) || !($zend_validate_ip->isValid($str_ip)))
        {
            $arr_data = array(
                'outcome'       => 'failed',
                'reason'        => 'validation error'
            );
            $this->_helper->json($arr_data);
            return;
        }
        
        // lets get our IP Address ip number
        $arr_ip_parts = explode('.', $str_ip);
        $int_ipnum = $arr_ip_parts[0]*16777216 + $arr_ip_parts[1]*65536 + $arr_ip_parts[2]*256 + $arr_ip_parts[3];
        
        // lets get our info from the DB
        $dbtable_blocks = new Application_Model_DbTable_Blocks();
        $arr_results_block = $dbtable_blocks->fetchAll("`endIpNum` >= $int_ipnum", "endIpNum ASC", 1);

        if (count($arr_results_block) != 1)
        {
            $arr_data = array(
                'outcome'       => 'failed',
                'reason'        => 'not found'
            );
            $this->_helper->json($arr_data);
            return;
        }
        else
        {
            $arr_results_block = $arr_results_block->toArray();
            $arr_results_block = $arr_results_block[0];
        }
        
        $dbtable_locations = new Application_Model_DbTable_Locations();
        $arr_results_location = $dbtable_locations->fetchRow("locId = {$arr_results_block['locId']}")->toArray();
        
        $dbtable_countries = new Application_Model_DbTable_Countries();
        $arr_results_countries = $dbtable_countries->fetchRow("`country_code` = '{$arr_results_location['country']}'")->toArray();
        
        $arr_data = array(
            'outcome'           => 'success',
            'ip'                => $str_ip,
            'country_name'      => $arr_results_countries['country_name'],
            'country_code'      => $arr_results_countries['country_code'],
            'region'            => $arr_results_location['region'],
            'city'              => $arr_results_location['city'],
            'postalcode'        => $arr_results_location['postalCode'],
        );
        
        $this->_helper->json($arr_data);
        // end
    }
}