<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Telegram\Commands;

use BaksDev\Telegram\Api\TelegramSendDocument;
use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\TelegramBotSettingsInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'baks:telegram:send:document',
    description: 'Отправляет видео в телеграм'
)]
class TelegramSendDocumentCommand extends Command
{
    public function __construct(
        #[Autowire(env: 'TELEGRAM_NOTIFIER')] private readonly string $TELEGRAM_NOTIFIER,
        private readonly TelegramBotSettingsInterface $telegramBotSettings,
        private readonly TelegramSendDocument $telegramSendDocument,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = $this->telegramBotSettings->settings();

        if(!$settings)
        {
            $io->success('Не найдено настроек для телеграм бота');
        }

        //        $response = $this->telegramSendVideo
        //            ->token($settings->getToken())
        //            ->chanel($this->TELEGRAM_NOTIFIER)
        //            ->video('http://bundles.baks.dev/live/2024/02/06/22/27/18-06000.ts')
        //            ->send()
        //        ;

        //        $response = $this->telegramSendMessage
        //            ->token($settings->getToken())
        //            ->chanel($this->TELEGRAM_NOTIFIER)
        //            ->message('Проверка')
        //            ->send()
        //        ;

        //dump($response);
        //dd($settings->getToken());

        $response = $this->telegramSendDocument
            ->token($settings->getToken())
            ->chanel($this->TELEGRAM_NOTIFIER)
            ->document('https://bundles.baks.dev/live/2024/02/06/22/27/18-06000.zip')
            ->send();

        //        $response = $this->telegramSendVideo
        //            ->token($settings->getToken())
        //            ->chanel($this->TELEGRAM_NOTIFIER)
        //            //->video('https://bundles.baks.dev/live/2024/02/06/22/27/18-06000.zip')
        //            //->video('https://bundles.baks.dev/live/video_2024-02-08_23-56-48.mp4')
        //            ->send()
        //        ;

        // Connection #0 to host api.telegram.org left intact


        dump($response);

        $io->success('baks:telegram:send:file');

        return Command::SUCCESS;
    }
}
