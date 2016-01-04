<?php namespace Neonbug\FiscalVerification\Tests;

class EchoTest extends \PHPUnit_Framework_TestCase
{
    
    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null;
    }

    /**
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp #Curl error#
     */
    public function testUnknownHost()
    {
        $message = 'test' . mt_rand(1, 10000);
        
        $config = include('_config.php');
        
        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }
        
        $fiscal_verification = new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            'https://unknown-host'
        );
        
        $response = $fiscal_verification->sendEcho($message);
    }
}
