<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller{

    public function proses(){

        $min = 10;
        $transaksi = DB::select('SELECT count(DISTINCT id_penjualan) as jumlah FROM `penjualan_detail`');
        $jumlah_transaksi =  $transaksi[0]->jumlah;
    
        $clearitemset1= DB::select('TRUNCATE `itemset1`');
        $clearitemset1= DB::select('TRUNCATE `itemset2`');
    
        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');

        $support_item1 = DB::select('UPDATE `itemset1` SET support =round( jumlah/'.$jumlah_transaksi .'*100),lolos = if(support > jumlah,1,0) ');
        $lolos_item1 = DB::select('UPDATE `itemset1` SET lolos = if(support >= '.$min.',1,0) ');

        $item_set1 = DB::select('SELECT atribut FROM `itemset1` WHERE lolos = 1');
        $jumlah_lolos_itemset1 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE lolos = 1');
        $itemset2 = '';

        for ($i=0; $i < $jumlah_lolos_itemset1[0]->jumlah ; $i++) { 
            
            for ($h=0; $h < $jumlah_lolos_itemset1[0]->jumlah ; $h++) { 
                
                if ($item_set1[$i]->atribut !=  $item_set1[$h]->atribut) {
               
                    $itemset2 .=  '('.$item_set1[$i]->atribut .','. $item_set1[$h]->atribut.'),';
                
                }
        
            }
        }

        $itemset2 = substr($itemset2, 0, -1);

        $insertitemset2 = DB::select('INSERT INTO itemset2 (atribut1,atribut2) VALUES '.$itemset2);
        
    }

    public function jumlah_itemset2($variasi1,$variasi2){

        

    }


}
