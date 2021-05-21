<?php namespace Neonbug\FiscalVerification\Test;

use Endroid\QrCode\Writer\PngWriter;

class RenderQrTest extends BaseTestCase
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
        }

        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);

        $data = $fiscal_verification->calculateQrCodeData($signed_zoi, 12345678, time());
        $image = $fiscal_verification->renderQrCodeAsImage(new PngWriter(), $data);

        $image_gd     = imagecreatefromstring($image->getString());
        $image_width  = imagesx($image_gd);
        $image_height = imagesy($image_gd);

        $expected_image_size = 300 /* size */ + 20 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }

    public function testRenderQrWithCustomSize()
    {
        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
        }

        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);

        $data = $fiscal_verification->calculateQrCodeData($signed_zoi, 12345678, time());
        $image = $fiscal_verification->renderQrCodeAsImage(new PngWriter(), $data, 100);

        $image_gd     = imagecreatefromstring($image->getString());
        $image_width  = imagesx($image_gd);
        $image_height = imagesy($image_gd);

        $expected_image_size = 100 /* size */ + 20 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }

    public function testRenderQrWithCustomSizeAndPadding()
    {
        $config = include('_config.php');

        if (!$this->checkConfig($config)) {
            $this->markTestSkipped('Config is empty');
        }

        $fiscal_verification = $this->initFiscalVerification($config);
        $signed_zoi = $this->getTestSignedZoi($fiscal_verification);

        $data = $fiscal_verification->calculateQrCodeData($signed_zoi, 12345678, time());
        $image = $fiscal_verification->renderQrCodeAsImage(new PngWriter(), $data, 100, 5);

        $image_gd     = imagecreatefromstring($image->getString());
        $image_width  = imagesx($image_gd);
        $image_height = imagesy($image_gd);

        $expected_image_size = 100 /* size */ + 10 /* padding */;
        $this->assertEquals($image_width * $image_height, $expected_image_size * $expected_image_size);
        //TODO find a better check
    }
}
