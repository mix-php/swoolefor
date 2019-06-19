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
     * @var string
     */
    public $watch;

    /**
     * @var int
     */
    public $delay;

    /**
     * @var string
     */
    public $ext;

    /**
     * @var int
     */
    public $signal;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            'cmd'    => ['string', 'filter' => ['trim']],
            'daemon' => ['in', 'range' => [1, 0], 'strict' => true],
            'watch'  => ['string', 'filter' => ['trim']],
            'delay'  => ['integer', 'unsigned' => true],
            'ext'    => ['string', 'filter' => ['trim']],
            'signal' => ['integer', 'unsigned' => true],
        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'main' => ['required' => ['cmd'], 'optional' => ['daemon', 'watch', 'delay', 'ext', 'signal']],
        ];
    }

    /**
     * 消息
     * @return array
     */
    public function messages()
    {
        return [
            'cmd.required'   => '\'--cmd\' option cannot be empty.',
            'delay.integer'  => '\'--delay\' option can only be number.',
            'signal.integer' => '\'--signal\' option can only be number.',
        ];
    }

}
