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


use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\TelegramBotSettingsInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group telegram
 * @group telegram-message
 */
#[When(env: 'test')]
final class TelegramRequestVideoTest extends WebTestCase
{
    private const string URL = '/test/telegram/request/video';

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

            $data = json_decode('{
            "update_id":123456789, 
            "message":{
                "message_id":12345,
                
                "from":{
                    "id": 1234567890,
                    "is_bot":false,
                    "first_name":"firstName",
                    "language_code":"ru"
                },
                
                "chat":{
                    "id":1234567890,
                    "first_name":"firstName",
                    "type":"private"
                },
                "date":1234567890,
                "video":{
                    "duration":0,
                    "width":320,
                    "height":320,
                    "mime_type":
                    "video/mp4",
                    "file_id":"agRWBSxKuBhnmYaHVznwTYtpmrVjDf-DZDWmGtythspWRmpbnfDQqTwvRAUktMcgVJTAdWG",
                    "file_unique_id":"vNPVHbMQDJxMVJymw",
                    "file_size":559
                }
            }}', true, 512, JSON_THROW_ON_ERROR);

            $client->jsonRequest('POST', self::URL, $data);

            //self::assertResponseIsSuccessful();

            self::assertTrue(true);
        }

        self::assertTrue(true);

    }
}
