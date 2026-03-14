<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kibet
 * Date: 3/6/2019
 * Time: 3:19 PM
 */

namespace App\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait HasUserColumns
 * @package App\Traits
 */
trait TracksUser
{
    /**
     * @var array
     */
    public $updateUserColumns = [];

    /**
     * @var array
     */
    public $createUserColumns = [];

    /**
     *
     */
    public static function bootTracksUser()
    {
        static::creating(function ($model) {

            foreach ($model->createUserColumns as $createUserColumn) {
                $model->{$createUserColumn} = Auth::id() && is_numeric(Auth::id()) ? Auth::id() : 0;
            }
        });
        static::updating(function ($model) {

            foreach ($model->updateUserColumns as $updateUserColumn) {
                $model->{$updateUserColumn} = Auth::id() && is_numeric(Auth::id()) ? Auth::id() : 0;
            }
        });
    }

    /**
     * @return array
     */
    public function getUpdateUserColumns(): array
    {
        return $this->updateUserColumns;
    }

    /**
     * @param array $updateUserColumns
     */
    public function setUpdateUserColumns(array $updateUserColumns): void
    {
        $this->updateUserColumns = $updateUserColumns;
    }

    /**
     * @return array
     */
    public function getCreateUserColumns(): array
    {
        return $this->createUserColumns;
    }

    /**
     * @param array $createUserColumns
     */
    public function setCreateUserColumns(array $createUserColumns): void
    {
        $this->createUserColumns = $createUserColumns;
    }

}
