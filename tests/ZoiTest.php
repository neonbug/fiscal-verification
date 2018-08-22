<?php namespace Neonbug\FiscalVerification\Tests;

class ZoiTest extends \PHPUnit_Framework_TestCase
{

    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null;
    }

    public function testGenerateZoi()
    {
        $config = include('_config.php');

        date_default_timezone_set($config['timezone'] ?: 'Europe/Ljubljana');

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

        $tax_number           = 12345678;
        $issue_date_time      = 1420070400; //1.1.2015 @ 0:00 (UTC)
        $business_premise_id  = 'premise1';
        $electronic_device_id = 'edevice1';
        $invoice_number       = '123';
        $invoice_amount       = 30.41123123;

        $zoi = $fiscal_verification->generateZoi(
            $tax_number,
            $issue_date_time,
            $business_premise_id,
            $electronic_device_id,
            $invoice_number,
            $invoice_amount
        );

        $this->assertEquals($zoi, '1234567801.01.2015 01:00:00123premise1edevice130.41');
    }

    public function testSignZoi()
    {
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

        $test_zoi = '1234567801.01.2015 01:00:00123premise1edevice130.41';
        $signature = $fiscal_verification->signZoi(
            $test_zoi,
            false
        );

        $ret = openssl_verify(
            $test_zoi,
            $signature,
            openssl_pkey_get_public(file_get_contents($config['client_key_filename'])),
            'sha256WithRSAEncryption'
        );

        $this->assertEquals($ret, 1);
    }

    /**
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp #Error reading certs.*#
     */
    public function testPrivateKeyReadFail()
    {
        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }

        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            'wrongpass',
            $config['ca_public_key_filename'],
            $config['base_url']
        );

        $test_zoi = '1234567801.01.2015 01:00:00123premise1edevice130.41';
        $signature = $fiscal_verification->signZoi(
            $test_zoi,
            false
        );
    }
}
