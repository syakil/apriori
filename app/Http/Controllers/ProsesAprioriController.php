<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;


class ProsesAprioriController extends Controller{

    public function proses(Request $request){

        $min = $request->min_support;
        $min_conf = $request->min_confidence;

        $data_mentah = DB::select('SELECT id_penjualan,GROUP_CONCAT(DISTINCT CONCAT(kode_produk)) AS produk FROM penjualan_detail GROUP BY id_penjualan ASC');

        $data_penjualan = array();
        
        foreach ($data_mentah as $key => $value) {
            
            $produk = explode(",",$value->produk);
            $data_penjualan[] = $produk;

        }
        
        $jumlah_penjualan = count($data_penjualan);
        
        $clearitemset1= DB::select('TRUNCATE `itemset1`');
        $clearitemset2= DB::select('TRUNCATE `itemset2`');
        $clearitemset3= DB::select('TRUNCATE `itemset3`');
        $clearconfidence = DB::select('TRUNCATE `confidence`');
    
        $item_set1 = DB::select('INSERT INTO `itemset1`(`atribut`, `jumlah`) SELECT kode_produk,count(DISTINCT id_penjualan) FROM `penjualan_detail` GROUP BY kode_produk');

        $support_item1 = DB::select('UPDATE `itemset1` SET support = jumlah/'.$jumlah_penjualan .'*100 ');
        $lolos_item1 = DB::select('UPDATE `itemset1` SET lolos = if(support >= '.$min.',1,0) ');

        $item_set1 = DB::select('SELECT atribut FROM `itemset1` WHERE lolos = 1');
        $jumlah_lolos_itemset1 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE lolos = 1');

        if ($jumlah_lolos_itemset1 > 0) {        
        
            $varian = array();

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
            $itemset2 = '';
            $itemsetjumlah = array();

            for ($j=0; $j < $jumlahvarianitemset2 ; $j++) { 
                
                $varianset2a = $varian[$j]['atribut1'];
                $varianset2b = $varian[$j]['atribut2'];

                $jumlahtransaksi2 = $this->hitung_set2($data_mentah,$varianset2a,$varianset2b);
                
                
                $itemsetjumlah[] = ['atribut1' => $varian[$j]['atribut1'],'atribut2' => $varian[$j]['atribut2'],'jumlah'=>$jumlahtransaksi2,'support'=>$jumlahtransaksi2/$jumlah_penjualan * 100, 'lolos' => ($jumlahtransaksi2/$jumlah_penjualan * 100 >= $min)?1:0];
                        
            }

            for ($j=0; $j < $jumlahvarianitemset2 ; $j++) { 
                $itemset2 .= '('. $itemsetjumlah[$j]['atribut1'] .','.$itemsetjumlah[$j]['atribut2'].','.$itemsetjumlah[$j]['jumlah'].','.$itemsetjumlah[$j]['support'].','.$itemsetjumlah[$j]['lolos'].'),';
            }

            $itemset2 = substr($itemset2, 0, -1);

            $insertitemset2 = DB::select('INSERT INTO itemset2 (atribut1,atribut2,jumlah,support,lolos) VALUES '.$itemset2);
  
            $item_set2_atribut1 = DB::select('SELECT atribut FROM `itemset1` WHERE atribut IN (SELECT atribut1 FROM `itemset2` WHERE lolos = 1)');
            $item_set2_atribut2 = DB::select('SELECT atribut FROM `itemset1` WHERE atribut IN (SELECT atribut2 FROM `itemset2` WHERE lolos = 1)');
            $jumlah_lolos_itemset2_atribut1 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE atribut IN (SELECT atribut1 FROM `itemset2` WHERE lolos = 1)');
            $jumlah_lolos_itemset2_atribut2 = DB::select('SELECT count(atribut) as jumlah FROM `itemset1` WHERE atribut IN (SELECT atribut2 FROM `itemset2` WHERE lolos = 1)');

            $itemset12 = array_merge($item_set2_atribut1,$item_set2_atribut2);
            $itemset3 = '';
            $jumlahitemset3= count($itemset12);

            if ($jumlahitemset3 > 0) {
                
                $varianset3 = [];

                for ($s=0; $s < $jumlahitemset3 ; $s++) { 
                    
                    for ($t=0; $t < $jumlahitemset3 ; $t++) { 
                        
                        for ($u=0; $u < $jumlahitemset3 ; $u++) {

                            if ($itemset12[$s]->atribut != $itemset12[$t]->atribut && $itemset12[$t]->atribut != $itemset12[$u]->atribut && $itemset12[$s]->atribut != $itemset12[$u]->atribut ) {
                                
                                $cekvarian1 = array('atribut1' => $itemset12[$s]->atribut,'atribut2' => $itemset12[$t]->atribut,'atribut3' => $itemset12[$u]->atribut);
                                $cekvarian2 = array('atribut1' => $itemset12[$s]->atribut,'atribut2' => $itemset12[$u]->atribut,'atribut3' => $itemset12[$t]->atribut);
                                $cekvarian3 = array('atribut1' => $itemset12[$t]->atribut,'atribut2' => $itemset12[$s]->atribut,'atribut3' => $itemset12[$u]->atribut);
                                $cekvarian4 = array('atribut1' => $itemset12[$t]->atribut,'atribut2' => $itemset12[$u]->atribut,'atribut3' => $itemset12[$s]->atribut);
                                $cekvarian5 = array('atribut1' => $itemset12[$u]->atribut,'atribut2' => $itemset12[$t]->atribut,'atribut3' => $itemset12[$s]->atribut);
                                $cekvarian6 = array('atribut1' => $itemset12[$u]->atribut,'atribut2' => $itemset12[$s]->atribut,'atribut3' => $itemset12[$t]->atribut);
                                


                                if (!in_array($cekvarian1,$varianset3) && !in_array($cekvarian2,$varianset3) && !in_array($cekvarian3,$varianset3) && !in_array($cekvarian4,$varianset3) && !in_array($cekvarian5,$varianset3) && !in_array($cekvarian6,$varianset3)) {
                                    
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
                    
                    $varianset3a = $varianset3[$j]['atribut1'];
                    $varianset3b = $varianset3[$j]['atribut2'];
                    $varianset3c = $varianset3[$j]['atribut3'];

                    $jumlahtransaksi3 = $this->hitung_set3($data_mentah,$varianset3a,$varianset3b,$varianset3c);
                    
                    $itemsetjumlah3[] = ['atribut1' => $varianset3[$j]['atribut1'],'atribut2' => $varianset3[$j]['atribut2'],'atribut3'=>$varianset3[$j]['atribut3'],'jumlah' =>$jumlahtransaksi3,'support'=>$jumlahtransaksi3/$jumlah_penjualan*100,'lolos' => ($jumlahtransaksi3/$jumlah_penjualan*100 >= $min) ? 1:0
                    ];
                            
                }

                for ($j=0; $j < $jumlahvarianitemset3 ; $j++) { 
                    $itemset3 .= '('. $itemsetjumlah3[$j]['atribut1'] .','.$itemsetjumlah3[$j]['atribut2'].','.$itemsetjumlah3[$j]['atribut3'].','.$itemsetjumlah3[$j]['jumlah'].','.$itemsetjumlah3[$j]['support'].','.$itemsetjumlah3[$j]['lolos'].'),';
                }

                $itemset3 = substr($itemset3, 0, -1);

                $insertitemset2 = DB::select('INSERT INTO itemset3 (atribut1,atribut2,atribut3,jumlah,support,lolos) VALUES '.$itemset3);
            

            }
            
            $jumlah_lolos_itemset3 = DB::select('SELECT count(lolos) AS jumlah FROM `itemset3` WHERE lolos = 1');

            if ($jumlah_lolos_itemset3[0]->jumlah > 0) {
                
                $data_itemset3 = DB::select('SELECT * FROM `itemset3` WHERE lolos = 1');

                $hitung_confidence = array();

                foreach ($data_itemset3 as $key => $value) {
                    
                    $atribut1 = $value->atribut1;
                    $atribut2 = $value->atribut2;
                    $atribut3 = $value->atribut3;
                    $support_xuy = $value->support;

                    // a,b => c
                    $hitung_confidence1 = $this->hitung_confidence($data_mentah,$atribut1,$atribut2,$atribut3,$support_xuy,$min_conf,$jumlah_penjualan,$min);
        
                    $hitung_confidence2 = $this->hitung_confidence($data_mentah,$atribut2,$atribut3,$atribut1,$support_xuy,$min_conf,$jumlah_penjualan,$min);
                    
                    $hitung_confidence3 = $this->hitung_confidence($data_mentah,$atribut3,$atribut1,$atribut2,$support_xuy,$min_conf,$jumlah_penjualan,$min);

                    array_push($hitung_confidence,$hitung_confidence1,$hitung_confidence2,$hitung_confidence3) ;
                }
                $itemconfidence = '';
                $jumlahconfidence = count($hitung_confidence);
                for ($j=0; $j < $jumlahconfidence ; $j++) { 
                    $itemconfidence .= '('. 
                    $hitung_confidence[$j]['kombinasi1'] .','.$hitung_confidence[$j]['kombinasi2'].','.$hitung_confidence[$j]['kombinasi3'].','.$hitung_confidence[$j]['support_xUy'].','.
                    $hitung_confidence[$j]['support_x'].','.
                    $hitung_confidence[$j]['confidence'] .','.
                    $hitung_confidence[$j]['lolos'] .','.
                    $hitung_confidence[$j]['min_support'] .','.
                    $hitung_confidence[$j]['min_confidence'] .','.
                    $hitung_confidence[$j]['nilai_uji_lift'] .',"'.
                    $hitung_confidence[$j]['korelasi_rule'] .'",'.
                    $hitung_confidence[$j]['id_process'] .','.
                    $hitung_confidence[$j]['jumlah_a'] .','.
                    $hitung_confidence[$j]['jumlah_b'] .','.
                    $hitung_confidence[$j]['jumlah_ab'] .','.
                    $hitung_confidence[$j]['px'] .','.
                    $hitung_confidence[$j]['py'] .','.
                    $hitung_confidence[$j]['pxuy'] .','.
                    $hitung_confidence[$j]['from_itemset'] .'),';
                }

                $itemconfidence = substr($itemconfidence, 0, -1);
                
                $insertconfidence = DB::select('INSERT INTO confidence (`kombinasi1`, `kombinasi2`, `kombinasi3`, `support_xUy`, `support_x`, `confidence`, `lolos`, `min_support`, `min_confidence`, `nilai_uji_lift`, `korelasi_rule`, `id_process`, `jumlah_a`, `jumlah_b`, `jumlah_ab`, `px`, `py`, `pxuy`, `from_itemset`)  VALUES '.$itemconfidence);
            }

        }

        return redirect()->route('apriori.hasil', ['success'=>'Proses Berhasil !']);;

    }


    public function hasil(){

        return view('apriori.hasil');

    }



    public function hitung_set2($data_mentah,$varianset2a,$varianset2b){
        // dd($varianset2a);
        $jumlahset2 =0;
        $data_cek = array();
        // dd($data_mentah);   
        foreach ($data_mentah as $key => $value) {
            
            $items = ",".strtoupper($value->produk).",";
            $item_variasi1 = ",".strtoupper($varianset2a).",";
            $item_variasi2 = ",".strtoupper($varianset2b).",";
            
            $pos1 = strpos($items, $item_variasi1);
            $pos2 = strpos($items, $item_variasi2);
            
            if($pos1!==false && $pos2!==false){
                $jumlahset2++;  
            }
         
        }

        return $jumlahset2;

    }


    public function hitung_set3($data_mentah,$varianset3a,$varianset3b,$varianset3c){

        $jumlahset3 =0;

        foreach ($data_mentah as $key => $value) {
            
            $items = ",".strtoupper($value->produk).",";
            $item_variasi1 = ",".strtoupper($varianset3a).",";
            $item_variasi2 = ",".strtoupper($varianset3b).",";
            $item_variasi3 = ",".strtoupper($varianset3c).",";
            
            $pos1 = strpos($items, $item_variasi1);
            $pos2 = strpos($items, $item_variasi2);
            $pos3 = strpos($items, $item_variasi3);
            

            if($pos1!==false && $pos2!==false &&  $pos3!==false){
                $jumlahset3++;  
            }

        }

        return $jumlahset3;

    }


    public function hitung_confidence($data_mentah,$atribut1,$atribut2,$atribut3,$support_xuy,$min_conf,$jumlah_penjualan,$min){

        $jumlah_itemset_1_kombinasi = DB::select('SELECT jumlah FROM `itemset1` WHERE atribut = '.$atribut3);
        $jumlah_itemset_2_kombinasi = $this->hitung_set2($data_mentah,$atribut1,$atribut2);        
        $jumlah_itemset_3_kombinasi = $this->hitung_set3($data_mentah,$atribut1,$atribut2,$atribut3);

        $jumlah_kemunculanA = $jumlah_itemset_2_kombinasi;
        $jumlah_kemunculanB = $jumlah_itemset_1_kombinasi[0]->jumlah;
        $jumlah_kemunculanAB = $jumlah_itemset_3_kombinasi;

        $nilai_suport_itemset_2_kombinasi = $jumlah_itemset_2_kombinasi/$jumlah_penjualan*100;

        $confidence = $support_xuy/$nilai_suport_itemset_2_kombinasi*100;
        
        $lolos = ($confidence >= $min_conf) ? 1:0;
        
        $paUb = $jumlah_kemunculanAB/$jumlah_penjualan;

        
        $nilai_uji_lift = $paUb/(($jumlah_kemunculanA/$jumlah_penjualan)*($jumlah_kemunculanB/$jumlah_penjualan));
        
        switch (true) {
            case $nilai_uji_lift < 1:
                $korelasi_rule = "korelasi negatif";
                break;
            
            case $nilai_uji_lift > 1:
                $korelasi_rule = "korelasi positif";
                break;
            default:
                $korelasi_rule = "tidak ada korelasi";
                break;
        }

        return $data = [
            "kombinasi1" => $atribut1,
            "kombinasi2" => $atribut2,
            "kombinasi3" => $atribut3,
            "support_xUy" => $support_xuy,
            "support_x" => $nilai_suport_itemset_2_kombinasi,
            "confidence" => $confidence,
            "lolos" => $lolos,
            "min_support" => $min,
            "min_confidence" => $min_conf,
            "nilai_uji_lift" => $nilai_uji_lift,
            "korelasi_rule" => $korelasi_rule,
            "id_process" => 1,
            "jumlah_a" => $jumlah_kemunculanA,
            "jumlah_b" => $jumlah_kemunculanB,
            "jumlah_ab" => $jumlah_kemunculanAB,
            "px" => ($jumlah_kemunculanA/$jumlah_penjualan),
            "py" => ($jumlah_kemunculanB/$jumlah_penjualan),
            "pxuy" => $paUb,
            "from_itemset"=>3
        ];        

    }
}
