<?php

namespace App\Entity;

enum Status: string {
    case Created = 'created';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Complited = 'complited';

}