<?php
namespace App\Helpers;

class ArrayHelper
{
    public function getParentAndAddress(String $page): array {
        
        $address = null;
        $parent = null;
        
        preg_match_all("|[a-z0-9_]+|",$page,$out);

      
        if (count($out[0]) > 1) {
            $address = array_pop($out[0]);
            $parent = implode("/", $out[0]);
        }
        elseif (count($out[0]) == 1) {
            $address = array_pop($out[0]);
            $parent = "";
        } 

        return ['address'=>$address,
                'parent'=>$parent ];


    }
}