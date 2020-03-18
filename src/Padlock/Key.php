<?php

namespace Oslllo\Larakey\Padlock;

use Oslllo\Larakey\Larakey;
use Oslllo\Larakey\Padlock\Config;
use Illuminate\Database\Eloquent\Model;
use Oslllo\Larakey\Contracts\Permission;
use Oslllo\Larakey\Traits\LarakeyHelpers;
use Oslllo\Larakey\Exceptions\ClassDoesNotExist;

class Key
{
    use LarakeyHelpers;

    /**
     * The model to be unlocked.
     *
     * @var string|\Illuminate\Database\Eloquent\Model
     */
    public $to;

    /**
     * \Oslllo\Larakey\Models\HasPermission to model with id.
     *
     * @var string|int
     */
    public $to_id;

    /**
     * \Oslllo\Larakey\Models\HasPermission to model with type.
     *
     * @var string
     */
    public $to_type;
    
    /**
     * The permission to check.
     *
     * @var null|\Oslllo\Larakey\Contracts\Permission
     */
    public $permission;

    /**
     * Key class constructor.
     *
     * @param string|\Illuminate\Database\Eloquent\Model $to
     * @param null|\Oslllo\Larakey\Contracts\Permission $permission
     */
    public function __construct($to, $permission = null)
    {
        $this->to = $to;
        $this->to_id = Larakey::WILDCARD_TOKEN;
        $this->to_type = Larakey::WILDCARD_TOKEN;
        $this->permission = $permission;

        $this->initialize();
    }

    /**
     * Initialize Key class.
     *
     * @return void
     */
    protected function initialize()
    {
        if ($this->to instanceof Model) {
            $this->to_type = get_class($this->to);

            if ($this->to->exists) {
                $this->to_id = $this->to->id;
            }
        } elseif (is_string($this->to) && ! $this->isWildcardToken($this->to)) {
            if (! class_exists($this->to)) {
                throw ClassDoesNotExist::check($this->to);
            }

            $this->to_type = $this->to;
        }
    }

    /**
     * Get \Oslllo\Larakey\Models\HasPermission to pivot.
     *
     * @return array
     */
    public function getPivot(): array
    {
        return ['to_id' => $this->to_id, 'to_type' => $this->to_type];
    }

    /**
     * Check if key has access all access to permisison.
     *
     * @return boolean
     */
    public function hasFullAccess(): bool
    {
        return $this->to_type === Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    /**
     * Check if key has access to permission with class.
     *
     * @return boolean
     */
    public function hasClassAccess(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id === Larakey::WILDCARD_TOKEN;
    }

    /**
     * Check if key has access to permission with model instance.
     *
     * @return boolean
     */
    public function hasModelInstanceAccess(): bool
    {
        return $this->to_type !== Larakey::WILDCARD_TOKEN
            && $this->to_id !== Larakey::WILDCARD_TOKEN;
    }

    /**
     * Determine if key unlock padlock/permission.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @param \Oslllo\Larakey\Contracts\Permission|Oslllo\Larakey\Models\Permission $permission
     * @return boolean
     */
    public function unlocks(Model $instance, Permission $permission): bool
    {
        return $instance->permissions->contains(function ($padlock) use ($permission) {
            return (string) $padlock->id === (string) $permission->id
                && ((string) $padlock->to_id === (string) $this->to_id
                || (string) $padlock->to_id === Larakey::WILDCARD_TOKEN)
                && ((string) $padlock->to_type === (string) $this->to_type
                || (string) $padlock->to_type === Larakey::WILDCARD_TOKEN);
        });
    }
}
