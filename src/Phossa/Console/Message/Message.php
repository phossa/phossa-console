<?php
/*
 * Phossa Project
 *
 * @see         http://www.phossa.com/
 * @copyright   Copyright (c) 2015 phossa.com
 * @license     http://mit-license.org/ MIT License
 */
/*# declare(strict_types=1); */

namespace Phossa\Console\Message;

use Phossa\Shared\Message\MessageAbstract;

/**
 * Message class for Phossa\Console
 *
 * @package \Phossa\Console
 * @author  Hong Zhang <phossa@126.com>
 * @version 1.0.0
 * @since   1.0.0 added
 */
class Message extends MessageAbstract
{
    /**#@+
     * @var   int
     */

    /**
     * Invalid value "%s" for "%s"
     */
    const GETOPT_INVALID_VALUE      = 1512141115;

    /**
     * Invalid value type for option "%s"
     */
    const GETOPT_INVALID_TYPE       = 1512141116;

    /**
     * Value is required for option "%s"
     */
    const GETOPT_VALUE_REQUIRED     = 1512141117;

    /**
     * Option name is duplicated for "%s"
     */
    const GETOPT_OPTION_NAME_DUP    = 1512141118;

    /**
     * Invalid option definition "%s"
     */
    const GETOPT_INVALID_OPTION     = 1512141119;

    /**
     * Argument error "%s"
     */
    const GETOPT_ARGUMENT_ERROR     = 1512141120;

    /**
     * Option "%s" missing
     */
    const GETOPT_OPTION_MISSING     = 1512141121;

    /**
     * Unknown option "%s"
     */
    const GETOPT_OPTION_UNKNOWN     = 1512141122;
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    protected static $messages = [
        self::GETOPT_INVALID_VALUE      => 'Invalid value "%s" for option "%s"',
        self::GETOPT_INVALID_TYPE       => 'Invalid value type for option "%s"',
        self::GETOPT_VALUE_REQUIRED     => 'Value is required for option "%s"',
        self::GETOPT_OPTION_NAME_DUP    => 'Option name is duplicated for "%s"',
        self::GETOPT_INVALID_OPTION     => 'Invalid option definition "%s"',
        self::GETOPT_ARGUMENT_ERROR     => 'Argument error "%s"',
        self::GETOPT_OPTION_MISSING     => 'Option "%s" missing',
        self::GETOPT_OPTION_UNKNOWN     => 'Unknown option "%s"',
    ];
}
