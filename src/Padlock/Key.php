<?php

namespace Ghustavh97\Larakey\Padlock;

use Ghustavh97\Larakey\Larakey;
use Ghustavh97\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Ghustavh97\Larakey\Traits\LarakeyHelpers;
use Ghustavh97\Larakey\Exceptions\ClassDoesNotExist;

class Key
{
    use LarakeyHelpers;

    public $to;

    public $to_id;

    public $to_type;
    
    public $permission;

    public function __construct($to, $permission = null)
    {
        $this->to = $to;
        $this->to_id = Larakey::WILDCARD_TOKEN;
        $this->to_type = Larakey::WILDCARD_TOKEN;
        $this->permission = $permission;

        $this->initialize();
    }

    protected function initialize()
    {
        if ($this->to instanceof Model && $this->to->exists) {
            $this->to_id = $this->to->id;
            $this->to_type = \get_class($this->to);
        } elseif ($this->to instanceof Model && ! $this->to->exists) {
            $this->to_type = \get_class($this->to);
        } elseif (is_string($this->to) && ! $this->isWildcardToken($this->to)) {
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

    public function hasAllAccess(): bool
    {
        return $this->to_type === Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    public function hasClassAccess(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    public function hasModelInstanceAccess(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id !== Larakey::WILDCARD_TOKEN;
    }

    public function unlocks($instance, $permission): bool
    {
        return $instance->permissions->contains(function ($padlock) use ($permission) {
            return (string) $padlock->id === (string) $permission->id
                && ((string) $padlock->to_id === (string) $this->to_id
                || (string) $padlock->to_id === Larakey::WILDCARD_TOKEN)
                && ((string) $padlock->to_type === (string) $this->to_type
                || (string) $padlock->to_type === Larakey::WILDCARD_TOKEN);
        });
    }
    
    // public function
}
