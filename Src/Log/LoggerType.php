<?php

namespace Extra\Src\Log;

enum LoggerType
{
    case STACK;
    case DAILY;
    case MONTHLY;
}