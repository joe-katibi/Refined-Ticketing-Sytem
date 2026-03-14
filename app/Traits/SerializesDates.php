<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kibet
 * Date: 4/6/2019
 * Time: 12:19 PM
 */

namespace App\Traits;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

/**
 * Trait SerializesDates
 * @package App\Traits
 */
trait SerializesDates
{
    // use HasAttributes;

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    //    return parent::getDateFormat();
    }


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


}

?>
