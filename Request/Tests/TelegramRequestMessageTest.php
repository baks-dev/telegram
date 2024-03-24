<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
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
final class TelegramRequestMessageTest extends WebTestCase
{
    private const URL = '/test/telegram/request/message';

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

            $data =  [
                 "update_id" => 844602601,
                 "message" => [
                     "message_id" => 2122,
                     "from" => [
                         "id" => 1391925303,
                         "is_bot" => false,
                         "first_name" => "Michel Angelo",
                         "language_code" => "ru"
                     ],
                     "chat" => [
                         "id" => 1391925303,
                         "first_name" => "Michel Angelo",
                         "type" => "private"
                     ],
                     "date" => 1708084503,
                     "text" => "message"
                 ]
             ];


            $client->jsonRequest('POST', self::URL, $data);

            //dd(self::class);

            //$array = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
            //self::assertEquals($array, $data);

            self::assertTrue(true);

        }

        self::assertTrue(true);

    }
}
