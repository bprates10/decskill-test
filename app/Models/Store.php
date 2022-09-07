<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Store
 * 
 * @property int $id
 * @property string $cpf
 * @property string $name
 * @property string $owner
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Transaction[] $transactions
 *
 * @package App\Models
 */
class Store extends Model
{
	protected $table = 'store';

	protected $fillable = [
		'cpf',
		'name',
		'owner'
	];

	public function transactions()
	{
		return $this->hasMany(Transaction::class, 'id_store');
	}
}
