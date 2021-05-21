<?php namespace Neonbug\FiscalVerification\Test;

class EchoTest extends BaseTestCase
{

    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null;
    }

    public function testEcho()
    {
        $message = 'test' . mt_rand(1, 10000);

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
        $response = $fiscal_verification->sendEcho($message);

        $this->assertJsonStringEqualsJsonString(
            json_encode($response),
            json_encode(array('EchoResponse' => $message))
        );
    }
}
