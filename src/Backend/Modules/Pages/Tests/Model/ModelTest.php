<?php

namespace Backend\Modules\Pages\Tests\Model;

use Backend\Modules\Pages\Engine\Model;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ModelTest extends TestCase
{
    private string $userTemplatePath;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('FRONTEND_FILES_PATH')) {
            define('FRONTEND_FILES_PATH', sys_get_temp_dir() . '/forkcms-pages-model-test/Files');
        }
        if (!defined('FRONTEND_FILES_URL')) {
            define('FRONTEND_FILES_URL', '/src/Frontend/Files');
        }

        $this->userTemplatePath = FRONTEND_FILES_PATH . '/Pages/UserTemplate';
        (new Filesystem())->mkdir($this->userTemplatePath);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->userTemplatePath);

        parent::tearDown();
    }

    public function testUrlIsEncoded(): void
    {
        self::assertEquals(
            'http://www.google.be/Quote',
            Model::getEncodedRedirectUrl('http://www.google.be/Quote')
        );
        self::assertEquals(
            'http://www.google.be/Quote%22HelloWorld%22',
            Model::getEncodedRedirectUrl('http://www.google.be/Quote"HelloWorld"')
        );
        self::assertEquals(
            'http://www.google.be/Quote%27HelloWorld%27',
            Model::getEncodedRedirectUrl("http://www.google.be/Quote'HelloWorld'")
        );
        self::assertEquals(
            'http://cédé.be/Quote%22HelloWorld%22',
            Model::getEncodedRedirectUrl('http://cédé.be/Quote"HelloWorld"')
        );
    }

    public function testDuplicateUserTemplateImagesIgnoresNonUserTemplateBlocks(): void
    {
        $block = [
            'extra_type' => 'rich_text',
            'html' => '<img data-ft-type="image" src="' . FRONTEND_FILES_URL . '/Pages/UserTemplate/hero.jpg">',
        ];

        self::assertSame($block, Model::duplicateUserTemplateImages($block));
    }

    public function testDuplicateUserTemplateImagesIgnoresBlocksWithoutImages(): void
    {
        $block = ['extra_type' => 'usertemplate', 'html' => '<p>No images here</p>'];

        self::assertSame($block, Model::duplicateUserTemplateImages($block));
    }

    public function testDuplicateUserTemplateImagesIgnoresExternalImageUrls(): void
    {
        $block = [
            'extra_type' => 'usertemplate',
            'html' => '<img data-ft-type="image" src="https://example.com/hero.jpg">',
        ];

        self::assertSame($block, Model::duplicateUserTemplateImages($block));
    }

    public function testDuplicateUserTemplateImagesCopiesTheFileUnderANewName(): void
    {
        file_put_contents($this->userTemplatePath . '/hero.jpg', 'fake-image-content');

        $originalSrc = FRONTEND_FILES_URL . '/Pages/UserTemplate/hero.jpg';
        $block = [
            'extra_type' => 'usertemplate',
            'html' => '<img data-ft-type="image" src="' . $originalSrc . '">',
        ];

        $result = Model::duplicateUserTemplateImages($block);

        self::assertFileExists($this->userTemplatePath . '/hero.jpg');
        self::assertFileExists($this->userTemplatePath . '/hero-2.jpg');
        self::assertStringContainsString(
            FRONTEND_FILES_URL . '/Pages/UserTemplate/hero-2.jpg',
            $result['html']
        );
        self::assertStringNotContainsString('"' . $originalSrc . '"', $result['html']);
    }
}
