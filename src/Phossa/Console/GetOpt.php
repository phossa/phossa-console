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

use Phossa\Console\Message\Message;

/**
 * Parse command line options
 *
 * Highlights:
 *
 * 1. support one default value in optional mode '::' and if no param provided
 *
 *      e.g. 'x::[i=12]',
 *
 *           '-x' accepts an optional integer, when value is missing,
 *                default to 12
 *
 * 2. support type verification for integer, filename, directory etc.
 *
 *      e.g. 'f::[f=config.php]' (readable file)
 *
 *           '-f' accepts an optional readable filename, default is 'config.php'
 *
 * 3. support short/long options, backward compatible with Gnu getopt
 *
 * 4. support enum values for option in ':' mode
 *
 *      e.g. 'y:[=2014|2015]'
 *
 *           '-y' must provide a value, only accept 2014 or 2015
 *
 * 5. required option (always needed, has required value)
 *
 *      e.g. 'y#:[i]'
 *
 *           '-y' is a required option, accepts integer only
 *
 *      e.g. 'y#::[i=12]'
 *
 *           '-y' is a required option, when value is missing, default to 12
 *
 * Terminologies:
 *
 * 1. options: started with the leading '-' (short options) or '--' (long
 *          options) e.g. '-h', '--username'. '--' followed by whitespace
 *          means end of options
 *
 * 2. values | parameters: arguments following the short/long options with
 *          a leading space or '='. eg. '-v 1', '--user=hong'.
 *
 *
 * Features:
 *
 * 1. clustered short options. e.g. '-xyz' equals '-x','-y','-z' as long as
 *    no parameters required for those options
 *
 * 2. space or equal sign can be used with parameters. e.g. '-u 0', '-u=0',
 *    '--user phossa', '--user=phossa'
 *
 * 3. verification for parameters as scalar type string/integer/number/file
 *    are supported
 *
 * 4. ' -- ' is used to signal the end of options processing
 *
 *
 * Definitions:
 *
 * 1. 'user|username|u:[s]' means '--user' or '--username' or '-u' are synonyms,
 *     and the option requires a string parameter.
 *
 * 2. 'x::[i]' means '-x' has no synomyms, and takes an optional integer
 *    parameter
 *
 * 3. 'help|h' means no parameter
 *
 * 4. 'u#:[i]' means '-u' is required and accepts an integer value
 *
 *
 * Default values:
 *
 * 1. '=$DEFAULT' means, If option value not set, use $DEFAULT as default
 *
 *     e.g. 'x::[i=0]' means '-x' takes an optional integer parameter
 *          with default 0
 *
 * 2. enum values seperated by '|'. e.g.
 *
 *      's:[=open|close]' means '-s' requires a param either 'open' or 'close'
 *
 *
 * Verifications:
 *
 * 1. 'i' : integer. e.g. 'x::[i=0]', optional integer default to 0.
 *
 * 2. 's' : string. e.g. 'k:[s]', string parameter required
 *
 * 3. 'n' : a number
 *
 * 4. 'f' : readable filename, check readability
 *
 * 5. 'F' : writable filename
 *
 * 6. 'd' : readable directory
 *
 * 7. 'D' : writeable directory
 *
 *
 * Seperations:
 *
 * use comma as option defintion seperations. IF comma is part of the
 * parameter, you must quote comma in the quotes.
 *
 * OR
 *
 * pass in as array
 *
 * e.g.
 *
 *   short options (no ',')
 *    hd#:[d]c#::[f=config.php]o::q::[="wow,wow2"]
 *
 *   long options (with long name & synonyms)
 *    help|h,d#:[d],c#::[f=config.php],o::,q::[="wow,wow2"]
 *
 *   same as
 *
 *     array('help|h', 'd#:[d]', ...)
 *
 *  1. '-h', '-help': no parameter
 *  2. '-d': REQUIRED option, need a readable directory param
 *  3. '-c': REQUIRED option, may need a filename, if param not set, use
 *           'config.php' as default
 *  4. '-o': need optional string param
 *  5. '-q': may need an optional string param, default to 'wow,wow2'
 *
 *
 * @package \Phossa\Console
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa\Console\GetOptionInterface
 * @version 1.0.0
 * @since   1.0.0 added
 */
class GetOpt implements GetOptInterface
{
    /**
     * Configs for GetOpt
     *
     * @var    array
     * @access protected
     */
    protected $configs = [
        // reset options for each getOpt()
        'reset'                 => true,

        // seperator used in option definition string
        'optionSeperator'       => ',',

        // option synonyms seperator
        'synonymsSeperator'     => '|',

        // option value seperator
        'valueSeperator'        => '|',

        // char to indicate required option
        'requiredOptionChar'    => '#'
    ];

    /**
     * option pool
     *
     * @var    OptionInterface[]
     * @access protected
     */
    protected $options = [];

    /**
     * option factory if any
     *
     * @var    callable
     * @access protected
     */
    protected $factory;

    /**
     * Error message
     *
     * @var     string
     * @type    string
     * @access  protected
     */
    protected $error;

    /**
     * constructor
     *
     * @param  array $configs (optional) getopt configs
     * @param  callable $optionFactory (optional) option factory if any
     * @access public
     */
    public function __construct(
        array $configs = [],
        callable $optionFactory = null
    ) {
        // set getopt configs
        if ($configs) $this->configs = array_replace($this->configs, $configs);

        // set option factory
        if ($optionFactory) $this->factory = $optionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setOpt(
        /*# string */ $name,
        array $rules = []
    )/*# : GetOptInterface */ {
        // create an option
        if ($this->factory) {
            $func = $this->factory;
            $opt  = $func($name, $rules);
        } else {
            $opt = new Option($name, $rules);
        }

        // synonyms
        $syn   = $opt->getSynonyms();
        $syn[] = $name;
        foreach($syn as $n) {
            if (isset($this->options[$n])) {
                throw new Exception\DuplicationFoundException(
                    Message::get(Message::GETOPT_OPTION_NAME_DUP, $n),
                    Message::GETOPT_OPTION_NAME_DUP
                );
            }
            $this->options[$n] = $opt;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOpt(
        array $arguments,
        $definitions = '',
        array $rules = []
    )/*# : array */ {
        // reset options
        if ($this->configs['reset']) {
            foreach($this->options as $n => $o) {
                $o->resetValue();
            }
        }

        // parse definitions
        if ($definitions) $this->parseDefinition($definitions, $rules);

        // parse arguments
        return $this->parseArguments($arguments);
    }

    /**
     * Get the option by the name. Used for unit testing only
     *
     * @param  string $name option name
     * @return OptionInterface
     * @throws Exception\NotFoundException
     *         if option not found
     * @access public
     */
    public function get(
        /*# string */ $name
    )/*# : OptionInterface */ {
        if (isset($this->options[$name])) return $this->options[$name];
        throw new Exception\NotFoundException(
            Message::get(Message::GETOPT_OPTION_UNKNOWN, $name),
            Message::GETOPT_OPTION_UNKNOWN
        );
    }

    /**
     * Parse option definitions
     *
     * @param  array|string $definitions option definitions
     * @param  array $rules (optional) default rules for all options
     * @return void
     * @throws Exception\InvalidArgumentException
     *         if option definition is invalid
     * @access protected
     */
    protected function parseDefinition(
        $definitions,
        array $rules = []
    ) {
        // configs
        $sep = $this->configs['optionSeperator'];
        $req = $this->configs['requiredOptionChar'];
        $syn = $this->configs['synonymsSeperator'];
        $vse = $this->configs['valueSeperator'];

        // array is ok
        if (is_array($definitions)) {

        // parse string
        } else if (is_string($definitions)) {
            $def = preg_replace('/\[[^\]]++\]/', '', $definitions);

            // split option seperated by ','
            if (strpos($def, $sep) !== false ||
                strpos($def, $syn) !== false
            ) {
                $val  = '\[[^\]]++\]';
                $char = preg_quote($sep);
                $reg  = sprintf(
                    '(?<=%s|^)[^%s\[]*(?:%s[^%s\]]*)*(?=%s|\Z)',
                     $char, $char, $val, $char, $char
                );
                if (preg_match_all("/$reg/", $definitions, $m)) {
                    $definitions = $m[0];
                }

            // split short options
            } else {
                $reg = '\w' . '(?:' . preg_quote($req) . ')?' .
                       '(?:[:=]{1,2}(?:\[[^\]]++\])?)?';
                if (preg_match_all("/$reg/", $definitions, $m)) {
                    $definitions = $m[0];
                }
            }
        }

        // failed
        if (!is_array($definitions)) {
            throw new Exception\InvalidArgumentException(
                Message::get(
                    Message::GETOPT_INVALID_OPTION,
                    is_string($definitions) ?
                        $definitions :
                        gettype($definitions)
                ),
                Message::GETOPT_INVALID_OPTION
            );
        }

        // regex
        $reg = '^(?<name>[\w' . preg_quote($syn) . ']++)' .
               '(?<required>' . preg_quote($req) . ')?' .
               '(?:' .
                 '(?<value>[:=]{1,2})' .
                 '(?:\[(?<type>[^=\]]++)?(?:=(?<default>[^\]]+))?\])?' .
               ')?$';

        foreach($definitions as $def) {
            if (preg_match("/$reg/", $def, $m)) {
                $names = explode($syn, $m['name']);
                $name  = array_shift($names);
                $nrule = [
                    'type'      => isset($m['type']) ?
                                    $m['type'] :
                                    Option::TYPE_STRING,
                    'settings'  => (isset($m['required']) && $m['required'] ?
                                       Option::SETTING_REQUIRED : 0 ) |
                                   (isset($m['value']) ?
                                       (strlen($m['value']) == 1 ?
                                           Option::SETTING_VALUE_REQ :
                                           Option::SETTING_VALUE ) : 0
                                   ),
                    'default'   => isset($m['default']) ?
                                   (strpos($m['default'], $vse) !== false ?
                                       explode($vse, $m['default']) :
                                       $m['default']
                                   ) :
                                   null,
                    'synonyms'  => $names,
                ];
                $this->setOpt(
                    $name,
                    empty($rules) ? $nrule : array_replace($rules, $nrule)
                );
            }
        }
    }

    /**
     * Parse command line arguments
     *
     * @param  array $arguments command line arguments
     * @return array
     * @throws Exception\InvalidArgumentException
     *         if command line argument has error
     * @access protected
     */
    protected function parseArguments(
        array $arguments
    )/*# : array */ {
        $nonopts  = [];
        $goodopts = [];

        while(list($i, $arg) = each($arguments)) {

            // stop processing options
            if ($arg == '--') {
                $nonopts = array_merge(
                    $nonopts,
                    array_slice($arguments, $i + 1)
                );
                break;
            }

            // from stdin ?
            if ($arg == '-') {
                $nonopts[] = $arg;
                continue;
            }

            // options
            if ($arg[0] == '-') {
                if (!$this->parseOne($arg, $arguments)) break;
            } else {
                $nonopts[] = $arg;
            }
        }

        // clean up
        if (empty($this->error)) {
            foreach($this->options as $opt) {
                $val = $opt->getValue();
                if (!is_null($val)) {
                    $goodopts[$opt->getName()] = $val;
                } else if ($opt->isRequired()) {
                    throw new Exception\InvalidArgumentException(
                        Message::get(
                            Message::GETOPT_OPTION_MISSING, $opt->getName()
                        ),
                        Message::GETOPT_OPTION_MISSING
                    );
                }
            }
        } else {
            throw new Exception\InvalidArgumentException(
                Message::get(
                    Message::GETOPT_ARGUMENT_ERROR,
                    $this->error
                ),
                Message::GETOPT_ARGUMENT_ERROR
            );
        }

        // result
        return array($goodopts, $nonopts);
    }

    /**
     * Parse one argument at a time
     *
     * @param  string $arg specific argument
     * @param  array &$arguments all command line arguments
     * @return bool
     * @access protected
     */
    protected function parseOne(
        /*# string */ $arg,
        array &$arguments
    )/*# : bool */ {

        // no value
        $value = false;

        // value serpator
        $vse = $this->configs['valueSeperator'];

        // long option
        if ($arg[1] == '-') {
            $short = false;
            $rem = substr($arg, 2);

            // --user=phossa
            if (strpos($rem, '=') !== false) {
                list($o, $value) = explode('=', $rem);
                $opts = [ $o ];

            // --user phossa
            } else {
                $opts = [ $rem ];
            }

        // short option(s) '-x' OR '-xyz'
        } else {
            $short = true;
            $opts  = str_split(substr($arg, 1), 1);
        }

        // loop thru options
        foreach($opts as $j => $opt) {

            // unknown option
            if (!isset($this->options[$opt])) {
                $this->error = Message::get(
                    Message::GETOPT_OPTION_UNKNOWN, $opt
                );
                return false;
            }

            $o = $this->options[$opt];

            // --user=phossa case
            if ($value) return $o->setValue($value, $vse);

            // no value needed, set $o value to empty ''
            if (!$o->needValue()) {
                $o->setValue();
                continue;
            }

            // value needed but not required
            if (!$o->isValueRequired()) {
                // short option, remaining is the value
                if ($short && $j < sizeof($opts) - 1) {
                    $value = join('', array_slice($opts, $j + 1));
                    return $o->setValue($value, $vse);

                // look at the next argument
                } else {

                    // next argument (value) not found
                    if (!list(, $value) = each($arguments))
                        return $o->setValue();

                    // next argument is another option, go back
                    if ($value[0] == '-') {
                        current($arguments) ?
                            prev($arguments) :
                            end($arguments);
                        return $o->setValue();
                    }

                    // next argument is a value
                    return $o->setValue($value, $vse);
                }
            }

            // value is required
            if (!$short || $j == sizeof($opts) - 1) {

                // value not found
                if (!list(, $value) = each($arguments)) {
                    $this->error = Message::get(
                        Message::GETOPT_OPTION_MISSING, $opt
                    );
                    return false;
                }

                // next arg is an option
                if ($value[0] == '-') {
                    $this->error = Message::get(
                        Message::GETOPT_OPTION_MISSING, $opt
                    );
                    return false;
                }

                return $o->setValue($value, $vse);

            } else {
                // short option
                $value = join('', array_slice($opts, $j + 1));

                return $o->setValue($value, $vse);
            }
        }

        return true;
    }
}
