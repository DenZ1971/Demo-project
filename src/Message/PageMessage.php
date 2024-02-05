<?php

namespace App\Message;



class PageMessage
{
    public function __construct(private int $page)
    {
    }
    public function getPage(): int

    {

        return $this->page;
    }
}


