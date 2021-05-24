<?php namespace Neonbug\FiscalVerification\Test;

class HeaderTest extends BaseTestCase
{

    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null &&
            $config['client_key_info'] != null &&
            $config['client_key_info']['serial'] != null &&
            $config['client_key_info']['issuer_name'] != null &&
            $config['client_key_info']['subject_name'] != null;
    }

    public function testHeader()
    {
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

        $header = $fiscal_verification->generateHeader();

        $this->assertJsonStringEqualsJsonString(
            $header,
            json_encode(array(
                'alg'          => 'RS256',
                'serial'       => strval($config['client_key_info']['serial']),
                'issuer_name'  => $config['client_key_info']['issuer_name'],
                'subject_name' => $config['client_key_info']['subject_name']
            ))
        );
    }
}
