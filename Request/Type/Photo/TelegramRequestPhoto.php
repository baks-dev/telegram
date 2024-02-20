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

namespace BaksDev\Telegram\Request\Type\Photo;

use BaksDev\Telegram\Request\AbstractTelegramRequest;
use BaksDev\Telegram\Request\TelegramChatDTO;
use BaksDev\Telegram\Request\TelegramUserDTO;
use BaksDev\Telegram\Request\TelegramRequestInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see TelegramProto */
final class TelegramRequestPhoto extends AbstractTelegramRequest
{
    /**
     * @note Optional.
     * Сообщение представляет собой фотографию, доступные размеры фотографии.
     */
    private ?ArrayCollection $photos = null;

    /**
     * Photos
     */
    public function getPhotos(): ArrayCollection
    {
        return $this->photos;
    }

    public function addPhoto(TelegramRequestPhotoFile $photo): self
    {
        if($this->photos === null)
        {
            $this->photos = new ArrayCollection();
        }

        $this->photos->add($photo);

        return $this;
    }

}