<?php

namespace Ghustavh97\Larakey;

use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

class LarakeyPermissionScope
{
    public $model;
    public $to_id;
    public $to_type;

    public function __construct($model)
    {
        $this->model = $model;
        $this->to_id = Larakey::WILDCARD_TOKEN;
        $this->to_type = Larakey::WILDCARD_TOKEN;

        $this->initialize();
    }

    protected function initialize()
    {
        if ($this->model instanceof Model && $this->model->exists) {
            $this->to_id = $this->model->id;
            $this->to_type = \get_class($this->model);
        } elseif ($this->model instanceof Model && ! $this->model->exists) {
            $this->to_type = \get_class($this->model);
        } elseif (is_string($this->model)) {
            if (! class_exists($this->model) && config(Larakey::$checkifClassExists)) {
                throw ClassDoesNotExist::check($this->model);
            }

            $this->to_type = $this->model;
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
