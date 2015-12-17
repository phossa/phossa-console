<?php
/*
 * Phossa Project
 *
 * @see         http://www.phossa.com/
 * @copyright   Copyright (c) 2015 phossa.com
 * @license     http://mit-license.org/ MIT License
 */
/*# declare(strict_types=1); */

namespace Phossa\Console;

/**
 * GetOptInterface
 *
 * @interface
 * @package \Console
 * @author  Hong Zhang <phossa@126.com>
 * @version 1.0.0
 * @since   1.0.0 added
 */
interface GetOptInterface
{
    /**
     * GNU compliant getopt
     *
     * Gnu short option definition:  'azo:d::'
     * Gnu long option defition:
     *
     *  array('user=', 'file==') OR array('user:', 'file::')
     *
     * @param  array $arguments command line arguments
     * @param  string|array $definitions (optional) definitions string or array
     * @param  array $rules (optional) default rules for all options
     * @return array
     * @throws Exception\DuplicationFoundException
     *         if option name or synonyms duplicated
     * @throws Exception\InvalidArgumentException
     *         if option definition is invalid
     * @access public
     * @api
     */
    public function getOpt(
        array $arguments,
        $definitions = '',
        array $rules = []
    )/*# : array */;

    /**
     * Define an option
     *
     * Instead of define options in getOpt(..., $short, $long) with short
     * or long definitions, complex option can be defined with this setOpt()
     *
     * @param  string $name option name
     * @param  array $rules (optional) option rules
     * @return GetOptionInterface this
     * @throws Exception\DuplicationFoundException
     *         if option name or synonyms duplicated
     * @access public
     * @api
     */
    public function setOpt(
        /*# string */ $name,
        array $rules = []
    )/*# : GetOptInterface */;
}
