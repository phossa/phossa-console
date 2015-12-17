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
 * OptionInterface
 *
 * Command line option interface
 *
 * @interface
 * @package \Phossa\Console
 * @author  Hong Zhang <phossa@126.com>
 * @version 1.0.0
 * @since   1.0.0 added
 */
interface OptionInterface
{
    /**
     * Set option name
     *
     * @param  string $name name to set
     * @return OptionInterface this
     * @access public
     */
    public function setName(
        /*# string */ $name
    )/*# : OptionInterface */;

    /**
     * Get option name
     *
     * @param  void
     * @return string
     * @access public
     */
    public function getName()/*# : string */;

    /**
     * Set option rules
     *
     * @param  array $rules option rules
     * @return OptionInterface this
     * @access public
     */
    public function setRules(array $rules)/*# : OptionInterface */;

    /**
     * Get option rules
     *
     * @param  void
     * @return array
     * @access public
     */
    public function getRules()/*# : array */;

    /**
     * Set option value[s]
     *
     * Multiple values can be set with seperator such as '|'
     *
     * @param  string $value (optional) value to set
     * @param  string $seperator (optional) value seperator
     *         empty '' means do NOT split value string
     * @return bool
     * @throws Exception\InvalidArgumentException
     *         if $value not match default sets or type is not right
     * @access public
     */
    public function setValue(
        /*# string */ $value = '',
        /*# string */ $seperator = '|'
    )/*# : bool */;

    /**
     * Reset option value to null
     *
     * @param  void
     * @return void
     * @access public
     */
    public function resetValue();

    /**
     * Get option value
     *
     * @param  void
     * @return string|array|null
     * @access public
     */
    public function getValue();

    /**
     * Get option synonyms
     *
     * @param  void
     * @return array
     * @access public
     */
    public function getSynonyms()/*# : array */;

    /**
     * Is this option required ?
     *
     * @param  void
     * @return bool
     * @access public
     */
    public function isRequired()/*# : bool */;

    /**
     * Need a value for this option ?
     *
     * @param  void
     * @return bool
     * @access public
     */
    public function needValue()/*# : bool */;

    /**
     * Is value required for this option ?
     *
     * @param  void
     * @return bool
     * @access public
     */
    public function isValueRequired()/*# : bool */;

    /**
     * Get option help for this language
     *
     * @param  string $lang (optional) language type
     * @return string
     * @access public
     */
    public function getHelp(
        /*# string */ $lang = ''
    )/*# : string */;
}
