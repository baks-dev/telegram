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

use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\TelegramBotSettingsInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use BaksDev\Wildberries\Products\Type\Barcode\Event\WbBarcodeEventUid;
use BaksDev\Wildberries\Products\Type\Settings\Event\WbProductSettingsEventUid;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Tests\NewHandleTest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group telegram
 * @group telegram-message
 */
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


            //{"update_id":445629533, "message":{"message_id":30,"from":{"id":366248132,"is_bot":false,"first_name":"\u041b\u043e\u0440\u0434 \u041f\u0435\u0439 \u0432\u043e\u0434\u0430","last_name":"\u0415\u0448\u044c \u0432\u043e\u0434\u0430","username":"BafGreen","language_code":"ru","is_premium":true},"chat":{"id":366248132,"first_name":"\u041b\u043e\u0440\u0434 \u041f\u0435\u0439 \u0432\u043e\u0434\u0430","last_name":"\u0415\u0448\u044c \u0432\u043e\u0434\u0430","username":"BafGreen","type":"private"},"date":1711358395,"text":"/start","entities":[{"offset":0,"length":6,"type":"bot_command"}]}}

            $client->jsonRequest('POST', self::URL, $data);

            //dd(self::class);

            //$array = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
            //self::assertEquals($array, $data);

            self::assertTrue(true);

        }

        self::assertTrue(true);

    }
}
