<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\CnaeFile;
use App\Models\Transaction;
use App\Models\Store;
use Carbon\Carbon;
use Psy\Readline\Transient;

class CnaeFileController extends Controller
{
    public function index(Request $request)
    {
        try {
            // return Transaction::all()->groupBy('');
            $aux = Store::join('transaction', 'transaction.id_store', '=', 'store.id')
                ->selectRaw(
                    '
                        store.name,
                        store.owner,
                        transaction.id,
                        CASE
                            WHEN transaction.type = 1 THEN "Débito"
                            WHEN transaction.type = 2 THEN "Boleto"
                            WHEN transaction.type = 3 THEN "Financiamento"
                            WHEN transaction.type = 4 THEN "Crédito"
                            WHEN transaction.type = 5 THEN "Recebimento Empréstimo"
                            WHEN transaction.type = 6 THEN "Vendas"
                            WHEN transaction.type = 7 THEN "Recebimento TED"
                            WHEN transaction.type = 8 THEN "Recebimento DOC"
                            WHEN transaction.type = 9 THEN "Aluguel"
                        end as type,
                        transaction.date,
                        value,
                        balance_before_operation,
                        balance_after_operation'
                )
                // ->groupBy('store.id')
                ->get();
            return $aux;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $request->all();

        try {
            $data = $data['file'];
            $hashFile = substr($data['hash_invoice'], 23, strlen($data['hash_invoice']));

            $fileInfo = [
                'name' => $data['nameFile'],
                'extension' => $data['extension'],
                'type' => $data['typeFile'],
                'hash' => $data['hash_invoice'],
            ];

            $id_cnae_file = $this->insertFileInfo($fileInfo)->id;

            if (!$id_cnae_file) {
                return response()->json(['message' => 'Erro ao gravar arquivo CNAE'], 500);
            }

            $aux = base64_decode($hashFile);
            $transacoes = explode("\n", $aux);

            for ($i = 0; $i < count($transacoes); $i++) {

                if (strlen($transacoes[$i]) < 1) {
                    continue;
                }

                $this->insertTransaction($transacoes[$i], $id_cnae_file);
            }

            return response()->json(['message' => 'Importado com sucesso']);
        } catch (\Exception $err) {
            return $err->getMessage();
        }
    }

    public function getBalance()
    {
        try {
            $aux = Store::join('transaction', 'transaction.id_store', '=', 'store.id')
                ->selectRaw('store.name, store.owner, transaction.balance_after_operation')
                ->where('transaction.id', function ($query) {
                    $query->selectRaw('max(t.id)')
                        ->from('transaction as t')
                        ->whereRaw('t.id_store = store.id');
                })
                ->get();
            // dd($aux);
            return $aux;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function insertFileInfo($fileInfo)
    {
        try {
            $id_cnae_file = CnaeFile::create($fileInfo);
            // dd($id_cnae_file->id);
            return $id_cnae_file;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function insertStore($cpf, $store_owner, $store_name)
    {
        DB::beginTransaction();

        try {

            $data = [
                'cpf' => $cpf,
                'owner' => $store_owner,
                'name' => $store_name
            ];

            DB::commit();

            return Store::create($data)->id;
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
        }
    }

    public function insertTransaction($row, $id_cnae_file)
    {
        try {
            $cpf = trim(substr($row, 19, 11));
            $store_owner =  trim(utf8_encode(substr($row, 48, 14)));
            $store_name = trim(utf8_encode(substr($row, 62, 19)));
            $id_store = Store::where('cpf', $cpf)->get();

            if (count($id_store->toArray()) < 1) {
                $id_store = $this->insertStore($cpf, $store_owner, $store_name);
            } else {
                $id_store = $id_store->toArray()[0]['id'];
            }

            $concatdate = substr($row, 1, 8) . ' ' . substr($row, 42, 6);
            $dateFormat = date('d/m/Y', strtotime($concatdate));
            $hourFormat = date('H:i:s', strtotime($concatdate));
            $type = substr($row, 0, 1);
            $value = substr($row, 9, 10) / 100;

            $value_last_transaction = $this->getLastOperationValue($id_store);
            // dd($value_last_transaction, $value, $type);
            $value_after_transaction = $this->calcBalance($type, $value_last_transaction, $value);
            // dd($value_after_transaction);
            $transaction = [
                'id_cnae_file' => $id_cnae_file,
                'id_store' => $id_store,
                'type' => $type,
                'date' => $dateFormat,
                'value' => $value,
                'card' => substr($row, 30, 12),
                'hour' => $hourFormat,
                'balance_before_operation' => $value_last_transaction,
                'balance_after_operation' => $value_after_transaction
            ];

            Transaction::create($transaction);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function getLastOperationValue($id_store)
    {
        $lastOperation = 0;

        try {
            $store = Transaction::where('id_store', $id_store)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            if (count($store->toArray()) > 0) {
                // dd($store->toArray());
                $lastOperation = $store->toArray()[0]['balance_after_operation'];
            }

            return $lastOperation;
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
        }
    }

    public function calcBalance($type, $currentValue, $newValue)
    {
        switch ($type) {
            case '1':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
                $total = $currentValue + $newValue;
                break;
            case '2':
            case '3':
            case '9':
                $total = $currentValue - $newValue;
                break;
        }

        return $total;
    }
}
