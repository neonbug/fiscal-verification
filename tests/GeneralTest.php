<?php namespace Neonbug\FiscalVerification\Test;

use Exception;

class GeneralTest extends BaseTestCase
{

    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null;
    }

    public function testUnknownHost()
    {
        $this->expectException(Exception::class);
        $this->expectErrorMessageMatches('#Curl error#');

        $message = 'test' . mt_rand(1, 10000);

        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
        }

        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            'https://unknown-host'
        );

       $fiscal_verification->sendEcho($message);
    }
}
