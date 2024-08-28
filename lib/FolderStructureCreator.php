<?php

class FolderStructureCreator {

    public function getFolderStructure(){
        return [
            'app' => [
                'Enums',
                'Http' => [
                    'Controllers'
                ],
                'Models'
            ],
            'database' => [
                'migrations'
            ]
        ];
    }

    public function createFolderStructure($folders, $prepath = null){

        foreach ($folders as $findex => $fname){
            if(is_int($findex)){
                // create folder $fname
                $fpath = (!is_null($prepath) ? $prepath : '').$fname;
                if(!is_dir($fpath)){
                    dump('create folder '.$fpath);
                    mkdir($fpath);
                }
            }else{
                // create folder $findex
                $fpath = (!is_null($prepath) ? $prepath : '').$findex;
                if(!is_dir($fpath)){
                    dump('create folder '.$fpath);
                    mkdir($fpath);
                }

                $newprepath = $prepath.$findex.'/';
                if(is_array($fname)){
                    $this->createFolderStructure($fname, $newprepath);
                }
            }
        }

        //dd($folders);
    }

}