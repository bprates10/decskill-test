<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 * 
 * @property int $id
 * @property int $id_cnae_file
 * @property int $id_store
 * @property string $type
 * @property Carbon $date
 * @property float $value
 * @property string $card
 * @property string $hour
 * @property float $balance_before_operation
 * @property float $balance_after_operation
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property CnaeFile $cnae_file
 * @property Store $store
 *
 * @package App\Models
 */
class Transaction extends Model
{
	protected $table = 'transaction';

	protected $casts = [
		'id_cnae_file' => 'int',
		'id_store' => 'int',
		'value' => 'float',
		'balance_before_operation' => 'float',
		'balance_after_operation' => 'float'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'id_cnae_file',
		'id_store',
		'type',
		'date',
		'value',
		'card',
		'hour',
		'balance_before_operation',
		'balance_after_operation'
	];

	public function cnae_file()
	{
		return $this->belongsTo(CnaeFile::class, 'id_cnae_file');
	}

	public function store()
	{
		return $this->belongsTo(Store::class, 'id_store');
	}
}
