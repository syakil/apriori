<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller
{
    public function proses(){

        $data_transaksi = DB::select('select distinct id_transaksi from transaksi');

        $data = array();
        $data_transaksi = json_decode($data_transaksi,false);
        foreach ($data_transaksi as $value) {
            
            $data_produk = DB::select('select kode_produk from transaksi where id_transaksi = '. $value );

            dd($data_produk);

        }

    }
}
