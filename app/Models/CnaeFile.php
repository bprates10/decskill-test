<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CnaeFile
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $hash
 * @property string|null $type
 * @property string|null $extension
 * 
 * @property Collection|Transaction[] $transactions
 *
 * @package App\Models
 */
class CnaeFile extends Model
{
	protected $table = 'cnae_file';
	public $timestamps = false;

	protected $fillable = [
		'name',
		'hash',
		'type',
		'extension'
	];

	public function transactions()
	{
		return $this->hasMany(Transaction::class, 'id_cnae_file');
	}
}
