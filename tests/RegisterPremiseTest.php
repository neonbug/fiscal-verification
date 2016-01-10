<?php namespace Neonbug\FiscalVerification\Tests;

class RegisterPremiseTest extends \PHPUnit_Framework_TestCase
{

    protected function getTestHeader($fiscal_verification)
    {
        return $fiscal_verification->generateHeader();
    }
    
    protected function getTestMovablePremise()
    {
        return new \Neonbug\FiscalVerification\BusinessPremise\MovableBusinessPremise('C');
    }
    
    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null &&
            $config['tax_number'] != null;
    }
    
    public function testRegisterMovablePremise()
    {
        $business_premise = $this->getTestMovablePremise();
        $this->registerPremise($business_premise);
    }
    
    public function testRegisterImmovablePremise()
    {
        $business_premise = new \Neonbug\FiscalVerification\BusinessPremise\ImmovableBusinessPremise(
            111,
            222,
            333,
            'Test street',
            '1',
            'Ljubljana - Vič',
            'Ljubljana',
            '1000',
            'A'
        );
        $this->registerPremise($business_premise);
    }
    
    protected function registerPremise(\Neonbug\FiscalVerification\BusinessPremise\BusinessPremise $business_premise)
    {
        require_once('_helpers.php');
        $config = include('_config.php');
        
        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }
        
        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            $config['base_url']
        );
        
        // step 1: generate header
        $header = $this->getTestHeader($fiscal_verification);
        
        $message_id = 'de305d54-75b4-431b-adb2-eb6b9e5' . mt_rand(10000, 99999);
        
        $tax_number          = $config['tax_number'];
        $business_premise_id = 'premise' . mt_rand(10000, 99999);
        $validity_date       = time(); //date('Y-m-d');
        $software_suppliers  = array($config['tax_number']);
        $special_notes       = 'my special notes';
        $close_premise       = false;
        
        // step 2: generate request
        $premise = $fiscal_verification->generateRegisterPremise(
            $message_id,
            $tax_number,
            $business_premise_id,
            $validity_date,
            $business_premise,
            $software_suppliers,
            $special_notes,
            $close_premise
        );
        
        $validation = validateAgainstSchema('FiscalVerificationSchema.json', json_decode($premise));
        $this->assertTrue($validation['valid'], 'Errors during validation: ' . print_r($validation['errors'], true));
        
        // step 3: sign request
        $token = $fiscal_verification->signRequest($header, $premise);
        
        // step 4: send request
        $send_return_value = $fiscal_verification->sendPremise($token);
        
        $premise_response = json_decode($send_return_value['payload']);
        //fix DateTime in response, since it doesn't conform to FURS' own schema
        $premise_response->BusinessPremiseResponse->Header->DateTime .= '+00:00';
        
        $validation = validateAgainstSchema('FiscalVerificationSchema.json', $premise_response);
        $this->assertTrue($validation['valid'], 'Errors during validation: ' . print_r($validation['errors'], true));
        
        // close the premise we just opened
        
        $close_premise = true;
        
        // step 2: generate request
        $close_premise = $fiscal_verification->generateRegisterPremise(
            $message_id,
            $tax_number,
            $business_premise_id,
            $validity_date,
            $business_premise,
            $software_suppliers,
            $special_notes,
            $close_premise
        );
        
        $validation = validateAgainstSchema('FiscalVerificationSchema.json', json_decode($close_premise));
        $this->assertTrue($validation['valid'], 'Errors during validation: ' . print_r($validation['errors'], true));
        
        // step 3: sign request
        $token = $fiscal_verification->signRequest($header, $close_premise);
        
        // step 4: send request
        $send_return_value = $fiscal_verification->sendPremise($token);
        
        $premise_response = json_decode($send_return_value['payload']);
        //fix DateTime in response, since it doesn't conform to FURS' own schema
        $premise_response->BusinessPremiseResponse->Header->DateTime .= '+00:00';
        //fix MessageID in response, since it doesn't conform to FURS' own schema in case of an error
        if ($premise_response->BusinessPremiseResponse->Header->MessageID == '000000000000000000000000000000000000') {
            $premise_response->BusinessPremiseResponse->Header->MessageID = '00000000-0000-0000-0000-000000000000';
        }
        
        $validation = validateAgainstSchema('FiscalVerificationSchema.json', $premise_response);
        $this->assertTrue($validation['valid'], 'Errors during validation: ' . print_r($validation['errors'], true));
        
        $error_message = ($premise_response->BusinessPremiseResponse->Header->MessageID ==
            '00000000-0000-0000-0000-000000000000' ?
            'API returned an error: ' . json_encode($premise_response->BusinessPremiseResponse->Error) :
            null);
        $this->assertTrue($error_message === null, $error_message);
    }
}
