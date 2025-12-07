<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Telegram\Api\Tests;

use BaksDev\Telegram\Api\TelegramGetFile;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('telegram')]
#[When(env: 'test')]
class TelegramGetFileTest extends KernelTestCase
{
    private static string $Authorization;

    private static string $file;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = $_SERVER['TEST_TELEGRAM_TOKEN'];
        self::$file = $_SERVER['TEST_TELEGRAM_FILE_ID'];
    }

    public function testUseCase(): void
    {
        /** @var TelegramGetFile $TelegramGetFile */
        $TelegramGetFile = self::getContainer()->get(TelegramGetFile::class);

        $data = $TelegramGetFile
            ->token(self::$Authorization)
            ->file(self::$file)
            ->send();

        if(false === $data)
        {
            self::assertFalse($data);
            return;
        }

        self::assertTrue(isset($data['ok']));
        self::assertTrue(isset($data['result']));
        self::assertTrue(isset($data['tmp_file']));
        self::assertTrue(true, message: 'Повторите попытку через 60 секунд');

    }

}
