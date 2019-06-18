<?php

namespace Cli\Models;

use Mix\Validate\Validator;

/**
 * Class RunForm
 * @package Cli\Models
 * @author liu,jian <coder.keda@gmail.com>
 */
class RunForm extends Validator
{

    /**
     * @var string
     */
    public $cmd;

    /**
     * @var string
     */
    public $daemon;

    /**
     * @var int
     */
    public $interval;

    /**
     * @var int
     */
    public $stopSignal;

    /**
     * @var int
     */
    public $stopWait;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'cmd'        => ['string', 'filter' => ['trim']],
            'daemon'     => ['in', 'range' => [1, 0], 'strict' => true],
            'interval'   => ['integer', 'unsigned' => true],
            'stopSignal' => ['integer', 'unsigned' => true],
            'stopWait'   => ['integer', 'unsigned' => true],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'main' => ['required' => ['cmd'], 'optional' => ['daemon', 'interval', 'stopSignal', 'stopWait']],
        ];
    }

    /**
     * 消息
     * @return array
     */
    public function messages()
    {
        return [
            'cmd.required'       => '\'--cmd\' option cannot be empty.',
            'interval.integer'   => '\'--interval\' option can only be numbers.',
            'stopSignal.integer' => '\'--stop-signal\' option can only be numbers.',
            'stopWait.integer'   => '\'--stop-wait\' option can only be numbers.',
        ];
    }

}
