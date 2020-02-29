<?php

namespace Ghustavh97\Larakey\Padlock;

use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

class Access
{
    public $to;
    public $to_id;
    public $to_type;

    public function __construct($to)
    {
        $this->to = $to;
        $this->to_id = Larakey::WILDCARD_TOKEN;
        $this->to_type = Larakey::WILDCARD_TOKEN;

        $this->initialize();
    }

    protected function initialize()
    {
        if ($this->to instanceof Model && $this->to->exists) {
            $this->to_id = $this->to->id;
            $this->to_type = \get_class($this->to);
        } elseif ($this->to instanceof Model && ! $this->to->exists) {
            $this->to_type = \get_class($this->to);
        } elseif (is_string($this->to)) {
            if (! class_exists($this->to) && config(Config::$checkifClassExists)) {
                throw ClassDoesNotExist::check($this->to);
            }

            $this->to_type = $this->to;
        }
    }

    public function getPivot(): array
    {
        return ['to_id' => $this->to_id, 'to_type' => $this->to_type];
    }

    public function hasFullRange(): bool
    {
        return $this->to_type === Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    public function hasClassRange(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    public function hasModelInstanceRange(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id !== Larakey::WILDCARD_TOKEN;
    }
}
