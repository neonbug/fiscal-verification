<?php namespace Neonbug\FiscalVerification\Test;

use Neonbug\FiscalVerification\Invoice\ReferenceInvoice;
use Neonbug\FiscalVerification\Invoice\ReferenceSalesBook;

class ReferenceTest extends BaseTestCase
{
    protected function getTestHeader($fiscal_verification)
    {
        return $fiscal_verification->generateHeader();
    }

    protected function ensureTestPremiseExists($fiscal_verification, $tax_number)
    {
        $header = $this->getTestHeader($fiscal_verification);

        $message_id = 'de305d54-75b4-431b-adb2-eb6b9e5' . mt_rand(10000, 99999);

        $tax_number          = $tax_number;
        $business_premise_id = 'premise1';
        $validity_date       = time();
        $software_suppliers  = array($tax_number);
        $special_notes       = 'test premise';
        $close_premise       = false;

        $business_premise    = new \Neonbug\FiscalVerification\BusinessPremise\MovableBusinessPremise('C');

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
        $token                     = $fiscal_verification->signRequest($header, $premise);
        $send_return_value         = $fiscal_verification->sendPremise($token);
        $business_premise_response = json_decode($send_return_value['payload']);
    }

    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null &&
            $config['tax_number'] != null;
    }

    protected function getInvoiceObject($fiscal_verification, $config, $data)
    {
        // ensure test premise exists
        $this->ensureTestPremiseExists($fiscal_verification, $config['tax_number']);

        $premise_id           = 'premise1';
        $tax_number           = $config['tax_number'];
        $issue_date_time      = 1420070400; //1.1.2015 @ 0:00 (UTC)
        $business_premise_id  = $premise_id;
        $electronic_device_id = 'edevice1';
        $invoice_number       = $data['invoice_number'];
        $invoice_amount       = 30.41123123;
        $operator_tax_number  = $tax_number;
        $payment_amount       = 11.23123123;
        $customer_vat_number  = '12345677';

        $zoi = $fiscal_verification->generateZoi(
            $tax_number,
            $issue_date_time,
            $business_premise_id,
            $electronic_device_id,
            $invoice_number,
            $invoice_amount
        );
        $protected_id = $fiscal_verification->signZoi($zoi);

        $taxes_per_seller = new \Neonbug\FiscalVerification\Invoice\TaxesPerSeller();
        $taxes_per_seller->vat = $data['vats'];

        $message_id = 'de305d54-75b4-431b-adb2-eb6b9e5' . mt_rand(10000, 99999);

        $invoice = new \Neonbug\FiscalVerification\Invoice\ElectronicInvoice(
            $message_id,
            $invoice_number,
            $invoice_amount,
            $payment_amount,
            $tax_number,
            $business_premise_id,
            $electronic_device_id,
            $issue_date_time,
            $operator_tax_number,
            $protected_id,
            'B'
        );

        $invoice->taxes_per_seller[] = $taxes_per_seller;
        $invoice->customer_vat_number = $customer_vat_number;

        return $invoice;
    }

    public function testReferenceInvoice()
    {
        require_once('_helpers.php');
        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
        }

        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            $config['base_url']
        );
        // step 1: generate header
        $header = $this->getTestHeader($fiscal_verification);

        $data = array(
            'invoice_number' => 124,
            'vats'           => array(
                array(
                    'TaxRate'       => 0,
                    'TaxAmount'     => 0,
                    'TaxableAmount' => -1
                )
            )
        );

        $invoice = $this->getInvoiceObject($fiscal_verification, $config, $data);

        $invoice->addReferenceInvoice(
            new ReferenceInvoice(
                123,
                'premise1',
                'edevice1',
                time()
            )
        );
        $invoice->setSpecialNotes(
            implode('', [
                'T-CHSEQ#',
                1,
                'DT',
                date('Y-m-d\TH:i:s'),
            ])
        );

        $invoice = $fiscal_verification->generateInvoice($invoice);

        $invoice_response = $this->sendTestInvoice($fiscal_verification, $header, $invoice);

        $error_message = ($invoice_response->InvoiceResponse->Header->MessageID ==
            '00000000-0000-0000-0000-000000000000' ?
            'API returned an error: ' . json_encode($invoice_response->InvoiceResponse->Error) :
            null);

        $this->assertNull($error_message);
    }

    public function testReferenceSalesBook()
    {
        require_once('_helpers.php');
        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
        }

        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            $config['base_url']
        );

        // step 1: generate header
        $header = $this->getTestHeader($fiscal_verification);

        $data = array(
            'invoice_number' => 124,
            'vats'           => array(
                array(
                    'TaxRate'       => 0,
                    'TaxAmount'     => 0,
                    'TaxableAmount' => -1
                )
            )
        );
        $invoice = $this->getInvoiceObject($fiscal_verification, $config, $data);

        $invoice->addReferenceSalesBook(
            new ReferenceSalesBook(
                123,
                'premise1',
                'edevice1',
                time()
            )
        );
        $invoice->setSpecialNotes(
            implode('', [
                'T-CHSEQ#',
                1,
                'DT',
                date('Y-m-d\TH:i:s'),
            ])
        );

        $invoice = $fiscal_verification->generateInvoice($invoice);

        $invoice_response = $this->sendTestInvoice($fiscal_verification, $header, $invoice);

        $error_message = ($invoice_response->InvoiceResponse->Header->MessageID ==
            '00000000-0000-0000-0000-000000000000' ?
            'API returned an error: ' . json_encode($invoice_response->InvoiceResponse->Error) :
            null);

        $this->assertNull($error_message);
    }

    protected function sendTestInvoice($fiscal_verification, $header, $invoice)
    {
        // step 3: sign request
        $token = $fiscal_verification->signRequest($header, $invoice);

        // step 4: send request
        $send_return_value = $fiscal_verification->sendInvoice($token);

        $invoice_response = json_decode($send_return_value['payload']);
        //fix DateTime in response, since it doesn't conform to FURS' own schema
        $invoice_response->InvoiceResponse->Header->DateTime .= '+00:00';
        //fix MessageID in response, since it doesn't conform to FURS' own schema in case of an error
        if ($invoice_response->InvoiceResponse->Header->MessageID == '000000000000000000000000000000000000') {
            $invoice_response->InvoiceResponse->Header->MessageID = '00000000-0000-0000-0000-000000000000';
        }

        $validation = validateAgainstSchema('FiscalVerificationSchema.json', $invoice_response);
        $this->assertTrue($validation['valid'], 'Errors during validation: ' . print_r($validation['errors'], true));

        return $invoice_response;
    }

    //TODO implement these tests:
    //testSalesBookInvoice
}
