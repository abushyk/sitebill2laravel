<?php

class ModelsHelper {

    public function getModels(){

        $phisicaltables = [];

        $tables_names = [];
        $columns = [];
        $tables = [];

        $DBC = DBC::getInstance();


        $query = 'SHOW TABLES';
        $stmt = $DBC->query($query);
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $phisicaltables = array_merge($phisicaltables, array_values($ar));
            }
        }

        //dd($phisicaltables);





        $query = 'SELECT table_id, name FROM '.DB_PREFIX.'_table';
        $stmt = $DBC->query($query);
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                if(in_array(DB_PREFIX.'_'.$ar['name'], $phisicaltables)){
                    $tables_names[$ar['table_id']] = $ar['name'];
                }

            }
        }

        $query = 'SELECT * FROM '.DB_PREFIX.'_columns WHERE active = 1 AND table_id IN ('.implode(',', array_keys($tables_names)).')';
        $stmt = $DBC->query($query);
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                if(!isset($tables_names[$ar['table_id']])){
                    continue;
                }
                if(!isset($tables_names[$ar['table_id']])){
                    $tables[$tables_names[$ar['table_id']]] = [
                        'name' => $tables_names[$ar['table_id']],
                        'columns' => []
                    ];
                }
                $tables[$tables_names[$ar['table_id']]]['columns'][$ar['name']] = $ar;
            }
        }


        return $tables;
    }

    public function formatModels($tables){
        foreach ($tables as $tname => &$table){
            foreach ($table['columns'] as $column){
                $table['tablename'] = $tname;
                if($column['type'] === 'primary_key'){
                    $table['primary_key'] = $column['name'];
                    continue;
                }

                if($tname !== 'topic' && in_array($column['type'], ['select_box_structure'])){
                    $table['relations'][] = $column['name'];
                    $table['relation_tables'][] = 'topic';
                }

                if(in_array($column['type'], ['select_by_query', 'structure', 'tlocation', 'client_id', 'select_by_query_multi'])){
                    $table['relations'][] = $column['name'];
                    $table['relation_tables'][] = $column['primary_key_table'];
                }

                if(in_array($column['type'], ['select_box'])){
                    $table['enums'][] = $column['name'];
                }

                if(in_array($column['type'], ['photo', 'docuploads', 'uploads'])){
                    $table['media'][] = $column['name'];
                }
            }
        }
        return $tables;
    }

    public function createMigrations($models, $targetFolder){
        $createdMigrations = [];
        foreach ($models as $name => $model){
            //echo 'Получили модель: '.$name.'<br>';
            if(!isset($createdMigrations[$name])){
                $this->createMigration($model, $models, $createdMigrations);
            }
        }

        if(!empty($createdMigrations)){
            $tpl = file_get_contents(SITEBILL_DOCUMENT_ROOT.'/sitebill2laravel/stubs/migration.stub');
            $counter = 1;
            $datef = date('Y_m_d');


            foreach ($createdMigrations as $t => $d){
                $migrationname = $datef.'_'.$counter.'_create_'.$d['name'].'_table.php';
                $tplx = str_replace(['{Table}', '{Fields}'], [$d['content']['Table'], $d['content']['Fields']], $tpl);
                $f = fopen($targetFolder.'database/migrations/'.$migrationname, 'w');
                fwrite($f, $tplx);
                fclose($f);
                $counter += 1;
            }
        }


    }

    public function createMigration($model, $models, &$createdMigrations){
        if(isset($model['relation_tables']) && !empty($model['relation_tables'])){
            foreach ($model['relation_tables'] as $related_model){
                $related_table = $related_model;
                if(!isset($createdMigrations[$related_table]) && isset($models[$related_table])){
                    if($model['tablename'] !== $related_table){
                        $this->createMigration($models[$related_table], $models, $createdMigrations);
                    }
                }
            }
        }


        $vars = [];

        $vars['Table'] = '\''.$model['tablename'].'\'';

        $fields = [];
        foreach ($model['columns'] as $column){
            $r = $this->convertColumnToLaravel($column);
            if($r !== ''){
                $fields[] = $r;
            }

        }

        $vars['Fields'] = implode("\n", $fields);

        $createdMigrations[$model['tablename']] = [
            'name' => $model['tablename'],
            'content' => $vars
        ];

        //dd($vars);
    }

    public function convertColumnToLaravel($column){




        $type = $column['type'];
        $name = $column['name'];
        $ret = '';
        switch($type){
            case 'primary_key' : {
                break;
            }
            case 'safe_string' : {
                $ret = '$table->string(\''.$name.'\');';
                //
                break;
            }
            case 'hidden' : {
                $ret = '$table->string(\''.$name.'\');';
                // $table->string($name);
                break;
            }
            case 'checkbox' : {
                $ret = '$table->boolean(\''.$name.'\')->default(0);';
                // $table->boolean($name)->default(0);
                break;
            }
            case 'auto_add_value' : {
                $ret = '$table->string(\''.$name.'\');';
                // $table->string($name);
                break;
            }
            case 'textarea' : {
                $ret = '$table->text(\''.$name.'\');';
                // $table->text($name);
                break;
            }
            case 'textarea_editor' : {
                $ret = '$table->text(\''.$name.'\');';
                // $table->text($name);
                break;
            }
            case 'price' : {
                $ret = '$table->integer(\''.$name.'\');';
                // $table->integer('price');
                break;
            }
            case 'mobilephone' : {
                $ret = '$table->string(\''.$name.'\');';
                // $table->string($name);
                break;
            }
            case 'password' : {
                $ret = '$table->string(\''.$name.'\');';
                // $table->string($name);
                break;
            }
            case 'dtdatetime' : {
                $ret = '$table->dateTime(\''.$name.'\');';
                // $table->dateTime($name);
                break;
            }
            case 'dtdate' : {
                $ret = '$table->dateTime(\''.$name.'\');';
                // $table->dateTime($name);
                break;
            }
            case 'dttime' : {
                $ret = '$table->time(\''.$name.'\');';
                // $table->time($name);
                break;
            }
            case 'date' : {
                $ret = '$table->timestamp(\''.$name.'\');';
                // $table->timestamp($name);
                break;
            }
            case 'youtube' : {
                $ret = '$table->string(\''.$name.'\');';
                // $table->string($name);
                break;
            }
            case 'parameter' : {
                $ret = '$table->json(\''.$name.'\');';
                // $table->json($name);
                break;
            }

            case 'gadres' :
            case 'select_entity' :
            case 'separator' :
            case 'injector' :
            case 'compose' :
            case 'captcha' :
            case 'tlocation' :
            case 'attachment' :
            case 'uploadify_image' :
            case 'uploadify_file' : {
                break;
            }
            case 'grade' : {
                $ret = '$table->integer(\''.$name.'\')->default(0);';
                // $table->integer('price');
                break;
            }
            case 'select_box_structure' : {
                $ret = '$table->foreignId(\''.$name.'\')->default(0);';
                break;
            }
            case 'select_by_query' : {
                $ret = '$table->foreignId(\''.$name.'\')->default(0);';
                break;
            }
            case 'select_box' : {
                $ret = '$table->integer(\''.$name.'\')->default(0);';
                break;
            }

            case 'client_id' : {
                $ret = '$table->foreignId(\''.$name.'\')->default(0);';
                break;
            }


            case 'photo' :
            case 'docuploads' :
            case 'uploads' : {
                break;
            }
            case 'geodata' : {
                $ret = '$table->point(\''.$name.'\');';
                break;
            }
        }
        return $ret;
    }

    private function registerMediaCollection($element, $prevname = 'preview', $sizes = []){
        $defaultGraficMedia = ['image/jpeg', 'image/png', 'image/webp'];
        return '$this->addMediaCollection(\''.$element.'\')->acceptsMimeTypes(['.'\''.implode('\', \'', $defaultGraficMedia).'\''.'])->registerMediaConversions(function (Media $media) {$this->addMediaConversion(\''.$prevname.'\')->fit(Manipulations::FIT_CROP, '.$sizes[0].', '.$sizes[1].')->nonQueued();});';
    }

}