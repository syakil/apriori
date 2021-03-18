<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller
{
    public function proses(){

        $min = 10;
        $transaksi = DB::select('SELECT count(DISTINCT id_penjualan) as jumlah FROM `penjualan_detail`');
        $jumlah_transaksi =  $transaksi[0]->jumlah;

        $clearitemset1= DB::select('TRUNCATE `itemset1`');
        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');
        $support_item1 = DB::select('UPDATE `itemset1` SET support =round( jumlah/'.$jumlah_transaksi .'*100),lolos = if(support > jumlah,1,0) ');
        $lolos_item1 = DB::select('UPDATE `itemset1` SET lolos = if(support >= '.$min.',1,0) ');
    }
}
