<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller
{
    public function proses(){

        $min = 30;

        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');
      

    }
}
