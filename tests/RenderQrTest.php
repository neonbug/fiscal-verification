<?php namespace Neonbug\FiscalVerification\Tests;

class RenderQrTest extends \PHPUnit_Framework_TestCase
{

    protected function initFiscalVerification($config)
    {
        return new \Neonbug\FiscalVerification\FiscalVerification(
            $config['client_key_filename'],
            $config['client_key_password'],
            $config['ca_public_key_filename'],
            $config['base_url']
        );
    }
    
    protected function getTestSignedZoi($fiscal_verification)
    {
        return $fiscal_verification->signZoi(
            '1234567801.01.2015 01:00:00123premise1edevice130.41',
            false
        );
    }
    
    protected function checkConfig($config)
    {
        return $config['client_key_filename'] != null &&
            $config['client_key_password'] != null &&
            $config['ca_public_key_filename'] != null &&
            $config['base_url'] != null;
    }
    
    public function testRenderQrWithDefaultParams()
    {
        $config = include('_config.php');
        
        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }
        
        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);
        
        $image = $fiscal_verification->renderQrCodeAsImage($signed_zoi, 12345678, time());
        $image_width  = imagesx($image);
        $image_height = imagesy($image);
        
        $expected_image_size = 300 /* size */ + 20 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }
    
    public function testRenderQrWithCustomSize()
    {
        $config = include('_config.php');
        
        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }
        
        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);
        
        $image = $fiscal_verification->renderQrCodeAsImage($signed_zoi, 12345678, time(), 100);
        $image_width  = imagesx($image);
        $image_height = imagesy($image);
        
        $expected_image_size = 100 /* size */ + 20 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }
    
    public function testRenderQrWithCustomSizeAndPadding()
    {
        $config = include('_config.php');
        
        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
            return;
        }
        
        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);
        
        $image = $fiscal_verification->renderQrCodeAsImage($signed_zoi, 12345678, time(), 100, 5);
        $image_width  = imagesx($image);
        $image_height = imagesy($image);
        
        $expected_image_size = 100 /* size */ + 10 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }
}
