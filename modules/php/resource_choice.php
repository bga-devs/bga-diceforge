<?php

namespace DiceForge\Resources;

enum ResourceChoice: int
{
    case RC_NOTHING_TODO  = -1;
    case RC_RESSOURCE     = 1;
    case RC_FORGESHIP     = 2;
    case RC_ACTION_CHOICE = 3;
    case RC_SIDE_CHOICE   = 4;
    case RC_MAZE          = 5;
    case RC_MISFORTUNE    = 6;
}
