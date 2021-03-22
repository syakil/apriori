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
        $clearitemset2= DB::select('TRUNCATE `itemset2`');
        $clearitemset3= DB::select('TRUNCATE `itemset3`');
    

        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');

        $support_item1 = DB::select('UPDATE `itemset1` SET support =round( jumlah/'.$jumlah_transaksi .'*100),lolos = if(support > jumlah,1,0) ');
        $lolos_item1 = DB::select('UPDATE `itemset1` SET lolos = if(support >= '.$min.',1,0) ');

        $item_set1 = DB::select('SELECT atribut FROM `itemset1` WHERE lolos = 1');
        $jumlah_lolos_itemset1 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE lolos = 1');
        $itemset2 = '';
        $varian = [];

        if ($jumlah_lolos_itemset1 > 0) {
         
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


            $item_set2_atribut1 = DB::select('SELECT atribut FROM `itemset1` WHERE atribut IN (SELECT atribut1 FROM `itemset2` WHERE lolos = 1)');
            $item_set2_atribut2 = DB::select('SELECT atribut FROM `itemset1` WHERE atribut IN (SELECT atribut2 FROM `itemset2` WHERE lolos = 1)');
            $jumlah_lolos_itemset2_atribut1 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE atribut IN (SELECT atribut1 FROM `itemset2` WHERE lolos = 1)');
            $jumlah_lolos_itemset2_atribut2 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE atribut IN (SELECT atribut2 FROM `itemset2` WHERE lolos = 1)');
            $itemset12 = array_merge($item_set2_atribut1,$item_set2_atribut2);
            $itemset3 = '';
            $jumlahitemset2= count($itemset12);

            if ($jumlahitemset2 > 0) {
         
                $varianset3 = [];

                for ($s=0; $s < $jumlahitemset2 ; $s++) { 
                    
                    for ($t=0; $t < $jumlahitemset2 ; $t++) { 
                        
                        for ($u=0; $u < $jumlahitemset2 ; $u++) {

                            if ($item_set1[$s]->atribut != $item_set1[$t]->atribut && $item_set1[$t]->atribut != $item_set1[$u]->atribut && $item_set1[$s]->atribut != $item_set1[$u]->atribut ) {
                                
                                $cekvarian1 = array('atribut1' => $itemset12[$s]->atribut,'atribut2' => $itemset12[$t]->atribut,'atribut3' => $itemset12[$u]->atribut);
                                $cekvarian2 = array('atribut1' => $itemset12[$s]->atribut,'atribut2' => $itemset12[$u]->atribut,'atribut3' => $itemset12[$t]->atribut);
                                $cekvarian3 = array('atribut1' => $itemset12[$t]->atribut,'atribut2' => $itemset12[$s]->atribut,'atribut3' => $itemset12[$u]->atribut);
                                $cekvarian4 = array('atribut1' => $itemset12[$t]->atribut,'atribut2' => $itemset12[$u]->atribut,'atribut3' => $itemset12[$s]->atribut);
                                $cekvarian5 = array('atribut1' => $itemset12[$u]->atribut,'atribut2' => $itemset12[$t]->atribut,'atribut3' => $itemset12[$s]->atribut);
                                $cekvarian6 = array('atribut1' => $itemset12[$u]->atribut,'atribut2' => $itemset12[$s]->atribut,'atribut3' => $itemset12[$t]->atribut);
                                


                                if (!in_array($cekvarian1,$varian) && !in_array($cekvarian2,$varian) && !in_array($cekvarian3,$varian) && !in_array($cekvarian4,$varian) && !in_array($cekvarian5,$varian) && !in_array($cekvarian6,$varian)) {
                                    
                                    $varianset3[] = ['atribut1' => $itemset12[$s]->atribut,'atribut2' => $itemset12[$t]->atribut,'atribut3' => $itemset12[$u]->atribut];
                                    
                                }
                                
                            }
                        }
                    
                    }
                }

                $jumlahvarianitemset3 = count($varianset3);
                $itemsetjumlah3 = array();
                $itemset3 = '';
                
                for ($j=0; $j < $jumlahvarianitemset3 ; $j++) { 
                    $jumlahtransaksi3 = DB::select('SELECT count(DISTINCT id_penjualan) as jumlah FROM penjualan_detail WHERE kode_produk = '.$varianset3[$j]['atribut1'] . '
                    AND id_penjualan IN(SELECT id_penjualan FROM penjualan_detail WHERE kode_produk = '.$varianset3[$j]['atribut2'].' AND id_penjualan IN(SELECT id_penjualan FROM penjualan_detail WHERE kode_produk = '.$varianset3[$j]['atribut3'].'))');
                    $itemsetjumlah3[] = ['atribut1' => $varianset3[$j]['atribut1'],'atribut2' => $varianset3[$j]['atribut2'],'atribut3'=>$varianset3[$j]['atribut3'],'jumlah' =>$jumlahtransaksi3[0]->jumlah,'support'=>$jumlahtransaksi3[0]->jumlah/$jumlah_transaksi*100,'lolos' => ($jumlahtransaksi3[0]->jumlah/$jumlah_transaksi*100 >= $min) ? 1:0
                    ];
                            
                }

                for ($j=0; $j < $jumlahvarianitemset3 ; $j++) { 
                    $itemset3 .= '('. $itemsetjumlah3[$j]['atribut1'] .','.$itemsetjumlah3[$j]['atribut2'].','.$itemsetjumlah3[$j]['atribut3'].','.$itemsetjumlah3[$j]['jumlah'].','.$itemsetjumlah3[$j]['support'].','.$itemsetjumlah3[$j]['lolos'].'),';
                }

                $itemset3 = substr($itemset3, 0, -1);

                $insertitemset2 = DB::select('INSERT INTO itemset3 (atribut1,atribut2,atribut3,jumlah,support,lolos) VALUES '.$itemset3);
            

            }

        }        
    }

    public function jumlah_itemset2($variasi1,$variasi2){

        

    }


}
