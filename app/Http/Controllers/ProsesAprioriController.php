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
        $varian = [];

        for ($i=0; $i < $jumlah_lolos_itemset1[0]->jumlah ; $i++) { 
            
            for ($h=0; $h < $jumlah_lolos_itemset1[0]->jumlah ; $h++) { 
                
                if ($item_set1[$i]->atribut != $item_set1[$h]->atribut) {
                    
                    $cekvarian1 = array('atribut1' => $item_set1[$i]->atribut,'atribut2' => $item_set1[$h]->atribut);
                    $cekvarian2 = array('atribut1' => $item_set1[$h]->atribut,'atribut2' => $item_set1[$i]->atribut);

                    if (!in_array($cekvarian1,$varian) && !in_array($cekvarian2,$varian)) {
                        
                        $varian[] = ['atribut1' => $item_set1[$i]->atribut,'atribut2' => $item_set1[$h]->atribut];

                    }

                }
               
            }
        }

        $jumlahvarianitemset2 = count($varian);

        $itemsetjumlah = array();

        for ($j=0; $j < $jumlahvarianitemset2 ; $j++) { 
            
            $jumlahtransaksi2 = DB::select('SELECT count(DISTINCT id_penjualan) as jumlah FROM penjualan_detail WHERE kode_produk = '.$varian[$j]['atribut1'] . '
                         AND id_penjualan IN(SELECT id_penjualan FROM penjualan_detail WHERE kode_produk = '.$varian[$j]['atribut2'].')');
            
            $itemsetjumlah[] = ['atribut1' => $varian[$j]['atribut1'],'atribut2' => $varian[$j]['atribut2'],'jumlah'=>$jumlahtransaksi2[0]->jumlah
            ];
                     
        }

        for ($j=0; $j < $jumlahvarianitemset2 ; $j++) { 
            $itemset2 .= '('. $itemsetjumlah[$j]['atribut1'] .','.$itemsetjumlah[$j]['atribut2'].','.$itemsetjumlah[$j]['jumlah'].'),';
        }

        $itemset2 = substr($itemset2, 0, -1);

        $insertitemset2 = DB::select('INSERT INTO itemset2 (atribut1,atribut2,jumlah) VALUES '.$itemset2);
        $support_item2 = DB::select('UPDATE `itemset2` SET support =round( jumlah/'.$jumlah_transaksi .'*100)');
        $lolos_item2 = DB::select('UPDATE `itemset2` SET lolos = if(support >= '.$min.',1,0) ');

        
    }

    public function jumlah_itemset2($variasi1,$variasi2){

        

    }


}
