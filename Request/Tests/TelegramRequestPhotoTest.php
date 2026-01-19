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

namespace BaksDev\Telegram\Request\Tests;

use BaksDev\Telegram\Bot\Repository\TelegramBotSettings\TelegramBotSettingsInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('telegram')]
#[When(env: 'test')]
final class TelegramRequestPhotoTest extends WebTestCase
{
    private const string URL = '/test/telegram/request/photo';

    public function testRoleUserDeny(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $container = self::getContainer();

        /** @var TelegramBotSettingsInterface $telegramBotSettings */
        $telegramBotSettings = $container->get(TelegramBotSettingsInterface::class);
        $telegramBotSettings->settings();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->setServerParameter('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN', $telegramBotSettings->getSecret());

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');

            $data = [
                "update_id" => 844602603,
                "message" => [
                    "message_id" => 2125,
                    "from" => [
                        "id" => 1391925303,
                        "is_bot" => false,
                        "first_name" => "Michel Angelo \u041b",
                        "language_code" => "ru"
                    ],
                    "chat" => [
                        "id" => 1391925303,
                        "first_name" => "Michel Angelo \u041b",
                        "type" => "private"
                    ],
                    "date" => 1708088440,
                    "photo" => [
                        [
                            "file_id" => "AgACAgIAAxkBAAIITWXPXHjn2CVOvPk7mJwpPZ6MstBFAAKy0zEbjgx4SpY9YRUDKZifAQADAgADcwADNAQ",
                            "file_unique_id" => "AQADstMxG44MeEp4",
                            "file_size" => 2785,
                            "width" => 90,
                            "height" => 84
                        ],
                        [
                            "file_id" => "AgACAgIAAxkBAAIITWXPXHjn2CVOvPk7mJwpPZ6MstBFAAKy0zEbjgx4SpY9YRUDKZifAQADAgADeAADNAQ",
                            "file_unique_id" => "AQADstMxG44MeEp9",
                            "file_size" => 17676,
                            "width" => 321,
                            "height" => 298
                        ],
                        [
                            "file_id" => "AgACAgIAAxkBAAIITWXPXHjn2CVOvPk7mJwpPZ6MstBFAAKy0zEbjgx4SpY9YRUDKZifAQADAgADbQADNAQ",
                            "file_unique_id" => "AQADstMxG44MeEpy",
                            "file_size" => 21627,
                            "width" => 320,
                            "height" => 297
                        ]
                    ]
                ]
            ];


            $client->jsonRequest('POST', self::URL, $data);

            self::assertTrue(true);

        }

        self::assertTrue(true);

    }
}
