<?php

namespace App\Message;



class PageMessage
{
    public function __construct(private int $pageId)
    {
    }
    public function getPageId(): int

    {
        return $this->pageId;
    }
}


