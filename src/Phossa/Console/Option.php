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
 * Command line option
 *
 * @package \Phossa\Console
 * @author  Hong Zhang <phossa@126.com>
 * @see     \Phossa\Console\OptionInterface
 * @version 1.0.0
 * @since   1.0.0 added
 */
class Option implements OptionInterface
{
    // <editor-fold defaultstate="collapsed" desc="option types">

    /**#@+
     * value type constants
     *
     * @const
     */

    /**
     * string, the default type
     */
    const TYPE_STRING       = 's';

    /**
     * integer
     */
    const TYPE_INTEGER      = 'i';

    /**
     * number, int or float etc.
     */
    const TYPE_NUMBER       = 'n';

    /**
     * readable directory
     */
    const TYPE_DIR_READ     = 'd';

    /**
     * readable directory
     */
    const TYPE_DIR_WRITE    = 'D';

    /**
     * file for read
     */
    const TYPE_FILE_READ    = 'f';

    /**
     * file for write, (truncate & write)
     */
    const TYPE_FILE_WRITE   = 'F';

    /**#@-*/

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="option settings">

    /**#@+
     * settings for OPTION
     *
     * @const
     */

    /**
     * option required
     */
    const SETTING_REQUIRED  = 1;

    /**
     * option may need a value
     */
    const SETTING_VALUE     = 2;

    /**
     * option value is required
     */
    const SETTING_VALUE_REQ = 4;

    /**#@-*/

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="option properties">

    /**
     * OPTION name
     *
     * @var    string
     * @access protected
     */
    protected $name;

    /**
     * OPTION value
     *
     * @var    string|array|null
     * @access protected
     */
    protected $value;

    /**
     * OPTION rules
     *
     * @var    array
     * @access protected
     */
    protected $rules = [
        // OPTION type
        'type'          => self::TYPE_STRING,

        // type validation callable: function($value, $type): bool { }
        'validate'      => null,

        // OPTION settings
        'settings'      => 0,

        // default values if any, scalar or array
        'default'       => null,

        // OPTION synonyms
        'synonyms'      => [],

        // help message
        'help'          => '',

        // language (for help message)
        'lang'          => 'en_US',
    ];

    // </editor-fold>

    /**
     * constructor
     *
     * @param  string $name option name
     * @param  array $rules option rules
     * @return void
     * @access public
     */
    public function __construct(
        /*# string */ $name,
        array $rules = []
    ) {
        $this->setName($name)->setRules($rules);
    }

    /**
     * {@inheritDoc}
     */
    public function setName(
        /*# string */ $name
    )/*# : OptionInterface */ {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()/*# : string */
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setRules(array $rules)/*# : OptionInterface */
    {
        // fix empty type
        if (isset($rules['type']) &&
            $rules['type'] === ''
        ) {
            $rules['type'] = self::TYPE_STRING;
        }
        
        $this->rules = array_replace($this->rules, $rules);
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()/*# : array */
    {
        return $this->rules;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue(
        /*# string */ $value = '',
        /*# string */ $seperator = '|'
    )/*# : bool */ {
        // set multiple values
        if ($seperator && strpos($value, $seperator) !== false) {
            $vals = explode($seperator, $value);
            foreach($vals as $val) {
                $this->setValue(trim($val), '');
            }
            return true;
        }

        // check with defaults
        if (!$this->checkDefault($value)) {
            throw new Exception\InvalidArgumentException(
                Message::get(Message::GETOPT_INVALID_VALUE, $value, $this->name),
                Message::GETOPT_INVALID_VALUE
            );
        }

        // validate value type
        if (!$this->checkValue($value)) {
            throw new Exception\InvalidArgumentException(
                Message::get(Message::GETOPT_INVALID_TYPE, $this->name),
                Message::GETOPT_INVALID_TYPE
            );
        }

        // set the value
        if (is_null($this->value)) {
            $this->value = $value;
        } else {
            if (is_array($this->value)) {
                $this->value[] = $value;
            } else {
                $this->value = array($this->value, $value);
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function resetValue()
    {
        $this->value = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getSynonyms()/*# : array */
    {
        return $this->rules['synonyms'];
    }

    /**
     * {@inheritDoc}
     */
    public function isRequired()/*# : bool */
    {
        return (bool) ($this->rules['settings'] & self::SETTING_REQUIRED);
    }

    /**
     * {@inheritDoc}
     */
    public function needValue()/*# : bool */
    {
        return (bool) ($this->rules['settings'] &
            (self::SETTING_VALUE | self::SETTING_VALUE_REQ)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isValueRequired()/*# : bool */
    {
        if ($this->needValue()) {
            return (bool) ($this->rules['settings'] & self::SETTING_VALUE_REQ);
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getHelp(
        /*# string */ $lang = ''
    )/*# : string */ {
        // localized help
        $h = 'help_' . $lang;
        if (isset($this->rules[$h])) return $this->rules[$h];

        // default help
        return $this->rules['help'];
    }

    /**
     * Check value type is right or not
     *
     * e.g.
     * <code>
     *     // set validator
     *     $opt->setRules(['validate' => function($val, $type) {
     *          $persons = ['Adam', 'Eva'];
     *          if ($type == 'p' && in_array($val, $persons)) return true;
     *          return false;
     *     }]);
     *
     *     // check person
     *     $opt->checkValue('Adam');
     * </code>
     *
     * @param  string $value value parsed from command line
     * @return bool
     * @access protected
     */
    protected function checkValue(/*# string */ $value)/*# : bool */
    {
        // no value needed
        if (!$this->needValue()) return true;

        // is string anyway
        switch ($this->rules['type']) {
            // string
            case self::TYPE_STRING :
                return true;

            // integer
            case self::TYPE_INTEGER :
                if (is_numeric($value) && $value == (int) $value) return true;
                break;

            // numeric
            case self::TYPE_NUMBER :
                if (is_numeric($value)) return true;
                break;

            // readable directory
            case self::TYPE_DIR_READ :
                if (is_dir($value)) return true;
                break;

            // writable directory
            case self::TYPE_DIR_WRITE :
                if (is_dir($value) && is_writable($value)) return true;
                break;

            // file readable (exists & readable)
            case self::TYPE_FILE_READ :
                if (is_file($value) && is_readable($value)) return true;
                break;

            // file writeable
            case self::TYPE_FILE_WRITE  :
                if (is_file($value)) {
                    if (is_writable($value)) return true;
                } else {
                    // writable dir
                    $dir = @dirname($value);
                    if (is_dir($dir) && is_writable($dir)) return true;
                }
                break;

            // extra checkings
            default :
                if ($this->rules['validate']) {
                    $func = $this->rules['validate'];
                    return $func($value, $this->rules['type']);
                }
                break;
        }

        // mismatch
        return false;
    }

    /**
     * Check value with default values
     *
     * @param  mixed &$value value to check with
     * @return bool
     * @throws Exception\InvalidArgumentException
     *         if value is required but missing
     * @access protected
     */
    protected function checkDefault(/*# string */ &$value)/*# : bool */
    {
        // no value needed
        if (!$this->needValue()) return true;

        // value is required but empty provided
        if ($this->isValueRequired() && $value === '') {
            throw new Exception\InvalidArgumentException(
                Message::get(Message::GETOPT_VALUE_REQUIRED, $this->name),
                Message::GETOPT_VALUE_REQUIRED
            );
        }

        // no defaults
        if (is_null($this->rules['default'])) return true;

        // has defaults
        $default = $this->rules['default'];

        // enum defaults
        if (is_array($default)) {
            // set value to first default
            if ($value === '') {
                $value = $default[0];
                return true;

            // value is in defaults
            } else if (in_array($value, $default)) {
                return true;

            // value is not right
            } else {
                return false;
            }

        // non enum default
        } else {
            // set value to default
            if ($value === '') $value = $default;
        }

        return true;
    }
}
