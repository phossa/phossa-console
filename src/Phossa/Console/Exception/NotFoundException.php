<?php
/*
 * Phossa Project
 *
 * @see         http://www.phossa.com/
 * @copyright   Copyright (c) 2015 phossa.com
 * @license     http://mit-license.org/ MIT License
 */
/*# declare(strict_types=1); */

namespace Phossa\Console\Exception;

/**
 * NotFoundException for \Phossa\Console
 *
 * @package \Phossa\Console
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa\Shared\Exception\NotFoundException
 * @version 1.0.0
 * @since   1.0.0 added
 */
class NotFoundException
    extends
        \Phossa\Shared\Exception\NotFoundException
    implements
        ExceptionInterface
{

}
