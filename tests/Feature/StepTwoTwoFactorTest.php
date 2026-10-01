<?php

namespace Tests\Feature;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class StepTwoTwoFactorTest extends TestCase
{
    public function test_can_generate_secret_and_svg_qr_code(): void
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->assertNotEmpty($secret);

        $qrCodeUrl = $google2fa->getQRCodeUrl('5758inc Finance', 'bos@5758inc.com', $secret);
        $this->assertStringStartsWith('otpauth://totp/', $qrCodeUrl);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($qrCodeUrl);

        $this->assertStringContainsString('<svg', $svg);

        // Verify OTP calculation
        $otp = $google2fa->getCurrentOtp($secret);
        $this->assertTrue($google2fa->verifyKey($secret, $otp));
    }
}
