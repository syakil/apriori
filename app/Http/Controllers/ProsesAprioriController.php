<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller
{
    public function proses(){

        $min = 30;
        $jumlah_transaksi = DB::select('SELECT count(DISTINCT id_penjualan) FROM `penjualan_detail`');
        dd($jumlah_transaksi);

        $clearitemset1= DB::select('TRUNCATE `itemset1`');
        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');
        // $support_item1 = DB::select('UPDATE `itemset1` SET support = '. .' ')

    }
}
