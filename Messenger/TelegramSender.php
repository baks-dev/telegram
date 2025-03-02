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

namespace BaksDev\Telegram\Messenger;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use DateInterval;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsMessageHandler]
final class TelegramSender
{
    private ?string $token;

    public function __construct(
        #[Target('telegramLogger')] private readonly LoggerInterface $logger,
        private readonly AppCacheInterface $appCache,
        private readonly DeduplicatorInterface $deduplicator,
    ) {}

    public function __invoke(TelegramMessage $message): array
    {
        $Deduplicator = $this->deduplicator
            ->namespace('telegram')
            ->expiresAfter(DateInterval::createFromDateString('60 seconds'))
            ->deduplication([$message]);

        if($Deduplicator->isExecuted())
        {
            return [];
        }

        $cache = $this->appCache->init('telegram');
        $this->token = $message->getToken();

        $HttpClient = HttpClient::create()->withOptions(
            ['base_uri' => 'https://api.telegram.org/bot'.$this->token.'/']
        );

        $response = $HttpClient->request(
            'POST',
            $message->getMethod(),
            ['json' => $message->getOption()]
        );

        if($response->getStatusCode() !== 200)
        {
            if($message->getMethod() === 'deleteMessage')
            {
                return [];
            }

            $this->logger->critical('Ошибка отправки сообщения', [
                self::class.':'.__LINE__,
                $message->getOption()
            ]);

            return [];
        }

        $Deduplicator->save();

        if($message->getMethod() === 'getFile')
        {
            return $this->saveFile($response);
        }

        $result = $response->toArray();

        if($message->getMethod() === 'sendMessage')
        {
            $option = $message->getOption();

            /** Сохраняем идентификатор системного сообщения */
            if(isset($result['result']['message_id']))
            {
                $systemItem = $cache->getItem('system-'.$option['chat_id']);
                $systemItem->set($result['result']['message_id']);
                $cache->save($systemItem);
            }

            /** Если указано удаляем сообщение */
            if(!empty($option['delete']))
            {
                foreach($option['delete'] as $delete)
                {
                    if(empty($delete))
                    {
                        continue;
                    }

                    try
                    {
                        $HttpClient->request(
                            'POST',
                            'deleteMessage',
                            ['json' => ['message_id' => $delete, 'chat_id' => $option['chat_id']]]
                        );
                    }
                    catch(Exception)
                    {
                        continue;
                    }
                }
            }
        }

        return $result;
    }


    public function saveFile(ResponseInterface $response): array
    {
        $dataFile = $response->toArray();

        /// https://api.telegram.org/file/bot6571592607:AAGW19cNaJIf5dGQpTKrTIyXITR9OoawZqg/photos/file_1080.jpg
        if($dataFile['ok'])
        {
            $HttpClient = HttpClient::create()->withOptions(
                ['base_uri' => 'https://api.telegram.org/file/bot'.$this->token.'/']
            );

            $download = $HttpClient->request(
                'GET',
                $dataFile['result']['file_path']
            );

            $pathInfo = pathinfo($dataFile['result']['file_path']);
            $extension = $pathInfo['extension'];

            $tmpfname = tempnam(sys_get_temp_dir(), $dataFile['result']['file_unique_id']);
            $tmp_file = sys_get_temp_dir().'/'.$dataFile['result']['file_unique_id'].'.'.$extension;
            rename($tmpfname, $tmp_file);

            $handle = fopen($tmp_file, "w");
            fwrite($handle, $download->getContent());
            fclose($handle);

            $dataFile = array_merge(
                $dataFile,
                ['tmp_file' => sys_get_temp_dir().'/'.$dataFile['result']['file_unique_id'].'.'.$extension]
            );

        }

        return $dataFile;
    }

}
