<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class RepitationType extends Enum
{
    const NOTHING =   0;
    const DAILY   =   1;
    const WEEKLY  =   2;
}
