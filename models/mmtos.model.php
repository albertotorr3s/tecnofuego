<?php

    class mmtos{
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
        	$this->rndr = $res['render'];
        	$this->fima = $res['fileman'];
            $this->seda = $_SESSION['u'];
        }

        // Método inicial
        public function index(){

            $lactiv = self::listactiv();

        	$d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Mantenimientos'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'flotas'    =>  self::lists(array('idlst'=>11,'str'=>'SELECCIONE FLOTA','def'=>'')),
                    'marcas'    =>  self::lists(array('idlst'=>2,'str'=>'SELECCIONE MARCA','def'=>'')),
                    'tecnicos'  =>  self::tecnicos(array('def'=>'','typ'=>'retu')),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'lactiv'    =>  $lactiv,
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/mmtos/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }
        private function listactiv(){

            $sql = "SELECT /*a.id Ítem, */DATE(a.fec_crea) 'Fecha registro', t.name Actividad, 
                           CONCAT(UPPER(ma.label), ' ', UPPER(m.label)) 'Marca/Modelo', e.internalNumber 'Número interno',
                           c.name Cliente, s.name Proyecto, a.startDate 'Fecha inicio', a.endDate 'Fecha fin'
                    FROM tec_equipment e, tec_valists ma, tec_valists m, tec_sites s, tec_company c, tec_activities a, tec_typeactivity t
                    WHERE e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND s.companyId = c.id
                        AND a.idTypeAct = t.id
                        AND e.id = a.idEquip
                        -- AND a.id > ? ";

            $dp = array();

            if( $_SESSION['u']['idp'] == 1 && $_SESSION['u']['ico'] == 1 ){
                $sql .= ';';
                //array_push($dp, ['kpa'=>1,'val'=>0,'typ'=>'int']);
            } else {
                $sql .= 'AND e.siteId = ?;';
                array_push($dp, ['kpa'=>1,'val'=>$_SESSION['u']['isi'],'typ'=>'int']);
            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            //$ccols = array(0,1,2,4,7,8,9);
            $ccols = array(0,3,6,7,8);
            return $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }
        

        // Listar productos y servicios  
        public function listar(array $data){ 


            print_r($data);

             $sql = "SELECT /*a.id Ítem, */DATE(a.fec_crea) 'Fecha registro', t.name Actividad, 
                           CONCAT(UPPER(ma.label), ' ', UPPER(m.label)) 'Marca/Modelo', e.internalNumber 'Número interno',
                           c.name Cliente, s.name Proyecto, a.startDate 'Fecha inicio', a.endDate 'Fecha fin',
                    FROM tec_equipment e, tec_valists ma, tec_valists m, tec_sites s, tec_company c, tec_activities a, tec_typeactivity t
                    WHERE e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND s.companyId = c.id
                        AND a.idTypeAct = t.id
                        AND e.id = a.idEquip
                        -- AND a.id > ? ";

            $dp = array();

            if( $_SESSION['u']['idp'] == 1 && $_SESSION['u']['ico'] == 1 ){
                $sql .= ';';
                //array_push($dp, ['kpa'=>1,'val'=>0,'typ'=>'int']);
            } else {
                $sql .= 'AND e.siteId = ?;';
                array_push($dp, ['kpa'=>1,'val'=>$_SESSION['u']['isi'],'typ'=>'int']);
            }

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            //$ccols = array(0,1,2,4,7,8,9);
            $ccols = array(0,3,6,7,8);
            return $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(int $data){
            $lbl = self::eqlabel($data);

            $d = array(
                'data' => array(
                    'header'	    =>  $this->rndr->renderHeader('Gestionar mantenimientos'),
                    'footer'        =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'hidIdEquip'       =>  $lbl['ideq'],
                    'idsite'        =>  $lbl['sid'],
                    'eqlabel'       =>  $lbl['lbl'],
                    'horomet'       =>  $lbl['hor'],
                    'accorde'       =>  self::collapseData($data),
                    'locatio'       =>  self::locations(array('def'=>'','typ'=>'retu','sit'=>$lbl['sid'])),
                    'familia'       =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categoria'     =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍAS','def'=>'')),
                    'partype'       =>  self::partype('return',''),
                    'estados'       =>  self::estados('return',''),
                    'tecnicos'      =>  self::lstec($lbl['sid']),
                    'usuario'       =>  $this->seda['idu'] 
                ),
                'file' => 'html/mmtos/nuevo.html'
            );
            
            $sqlComp = "SELECT ec.idCompo, vl.label  
                        FROM " . BD_PREFI . "equip_compos ec, " . BD_PREFI . "components c, " . BD_PREFI . "compo_vals cv,
                        " . BD_PREFI ."valists vl
                        WHERE ec.idEquip = ?
                        AND c.id = ec.idCompo 
                        AND cv.idComponent = c.id
                        AND cv.idField = vl.id
                        AND ec.edo_reg = ?;";
                            

            $dpc = array();
            array_push($dpc, ['kpa'=>1,'val'=>$lbl['ideq'],'typ'=>'int']);
            array_push($dpc, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            
            $awc = $this->crud->select_group($sqlComp, count($dpc), $dpc, 'arra');
            $lco = '';

            

            foreach ($awc['res'] as $kc => $vc) {
                $lco .= $vc['idCompo'].',';
            }

            $d['data']['lco'] = trim($lco,',');
            $d['data']['rps'] = self::lstrepos($lbl['ideq']);
            



            
            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Mostrar datos para editar
        public function editar(int $data){
            $sql = "SELECT c.idcateg id, c.categoria, c.edo_reg
                    FROM ".BD_PREFI."categenlaces c
                    WHERE c.idcateg = ?
                    LIMIT 1;";

            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar categorías de enlaces'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'estados'   =>  self::estados('return',$ar['edo_reg']),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/mmtos/editar.html'
            );

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acción de guardar
        public function guardar(array $data){

           
            
    
            $info = array(
                'idEquip'       =>  $data['idEquip'],
                'idTypeAct'     =>  2,
                'idLocation'    =>  $data['slcLocal'],
                'startDate'     =>  $data['txtFecIniMmto'],
                'endDate'       =>  $data['txtFecFinMmto'],
                'startHour'     =>  $data['txtHoraInicio'],
                'endHour'       =>  $data['txtHoraFinal'],
                'horometerIni'  =>  $data['txtHoroIni'],
                'horometerEnd'  =>  $data['txtHoroFin'],
                'observaciones' =>  $data['tarObservActiv'],
                'edo_reg'       =>  1
            );

          if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'activities',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();

                $resp = $this->crud->insert($info,BD_PREFI.'activities');

            } 


            if( $resp['rta'] == 'OK' ){


                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';

                // Valores de los técnicos que intervienen en la actividad
                $dtechs = json_decode($data['hidVlsTecs'], true);
                

                foreach ($dtechs as $kt => $vt) {

                    $inft = array(
                        'idactiv'   =>  (empty($data['hidId'])) ? $resp['lstId'] : $data['hidId'],
                        'idtech'    =>  $vt['idetec'],
                        'parper'    =>  $vt['portec'],
                        'edo_reg'   =>  1
                    );

                    

                    if( strlen(trim($vt['ide'])) > 0 ){

                        $inft['usu_mod'] = $this->seda['idu'];
                        $inft['fec_mod'] = date('Y-m-d H:i:s');
                        $inft['ip_mod']  = Firewall::ipCatcher();
        
                        $wt = array('id'=>$vt['ide']);
        
                        $rt = $this->crud->update($inft,BD_PREFI.'activ_techs',$wt);
        
                    } else {
        
                        $inft['usu_crea'] = $this->seda['idu'];
                        $inft['fec_crea'] = date('Y-m-d H:i:s');
                        $inft['ip_crea']  = Firewall::ipCatcher();
        
                        $rt = $this->crud->insert($inft,BD_PREFI.'activ_techs');
        
                    }
                    
                }

                // Insertar componentes
                $comps = json_decode($data['hidVlsComp'],true);

                                
                foreach ($comps as $kc => $vc) {
                    
                    $infc = array(
                        'idEquip'   =>  (empty($data['idEquip'])) ? $resp['lstId'] : $data['idEquip'],
                        'idCompo'   =>  $vc['idcomp'],
                        'edo_reg'   =>  1
                    );

                    $sqlC = "SELECT COUNT(*) cant
                            FROM tec_equip_compos ec
                            WHERE ec.idEquip = ?
                                AND ec.idCompo = ?
                            LIMIT 1;";

                    $dpc = array();
                    array_push($dpc, ['kpa'=>1,'val'=>$infc['idEquip'],'typ'=>'int']);
                    array_push($dpc, ['kpa'=>2,'val'=>$infc['idCompo'],'typ'=>'int']);
                    
                  
                    $awc = $this->crud->select_group($sqlC, count($dpc), $dpc, 'arra');
                   
                    

                    if( $awc['res'][0]['cant'] > 0 ){

                        $infc['usu_mod'] = $this->seda['idu'];
                        $infc['fec_mod'] = date('Y-m-d H:i:s');
                        $infc['ip_mod']  = Firewall::ipCatcher();

                        $whrc = array('idEquip'=>$infc['idEquip'],'idCompo'=>$infc['idCompo']);

                        $rc = $this->crud->update($infc,BD_PREFI.'equip_compos',$whrc);

                    } else {

                        $infc['usu_crea'] = $this->seda['idu'];
                        $infc['fec_crea'] = date('Y-m-d H:i:s');
                        $infc['ip_crea']  = Firewall::ipCatcher();

                        $rc = $this->crud->insert($infc,BD_PREFI.'equip_compos');

                    }
                    
                    
                    unset($infc,$whrc,$rc);

                    // Asingar componentes
                    $inf = array('edo_reg'=>2);
                    $whr = array('id'=>$vc['idcomp']);
                    $rsp = $this->crud->update($inf,BD_PREFI.'components',$whr);
                    unset($inf,$whr,$rsp);

                }
                 // Insertar repuestos
                
                $repos = json_decode($data['hidVlsComps'],true);
               
                foreach ($repos as $kc => $vc) {

                    $infrc = array(
                        'idactiv'       =>  (empty($data['hidId'])) ? $resp['lstId'] : $data['hidId'],
                        'idpartserv'    =>  $vc['idecom'],
                        'vunit'         =>  $vc['vuncom'], 
                        'cant'          =>  $vc['cancom'],
                        'vtotal'        =>  $vc['vtocom'],
                        'edo_reg'       =>  1
                    );


                    if( strlen(trim($vc['ide'])) > 0 ){

                        $infrc['usu_mod'] = $this->seda['idu'];
                        $infrc['fec_mod'] = date('Y-m-d H:i:s');
                        $infrc['ip_mod']  = Firewall::ipCatcher();
        
                        $wc = array('id'=>$vc['ide']);
        
                        $rrc = $this->crud->update($infrc,BD_PREFI.'activ_part_serv',$wc);
        
                    } else {
        
                        $infrc['usu_crea'] = $this->seda['idu'];
                        $infrc['fec_crea'] = date('Y-m-d H:i:s');
                        $infrc['ip_crea']  = Firewall::ipCatcher();
        
                        $rrc = $this->crud->insert($infrc,BD_PREFI.'activ_part_serv');
        
                    }
                    
                }
                // Eliminar componentes


                if( strlen($data['hidVlsDelComp']) > 0 ){

                    

                    $cps = explode(',',trim($data['hidVlsDelComp'],','));


                    foreach ($cps as $kcomp => $vcomp) {
 
                        $infcomp = array('edo_reg'=>1,'usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whrcomp = array('id'=>$vcomp);
                        $rspcomp = $this->crud->update($infcomp,BD_PREFI.'components',$whrcomp);
                        unset($infcomp,$whrcomp,$rspcomp);

                        $infcomp2 = array('edo_reg'=>0,'usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whrcomp2 = array('idCompo'=>$vcomp,'idEquip'=>$data['idEquip']);
                        $rspcomp2 = $this->crud->delete(BD_PREFI.'equip_compos',$whrcomp2);
                        // $whrcomp3 = array('idEquip'=>$data->hidId);
                        unset($infcomp2,$whrcomp2,$rspcomp2);


                    }
                    

                }
                // Eliminar repuestos
                if( strlen($data['hidVlsDelRep']) > 0 ){

                    
                    
                    $rps = explode(',',trim($data['hidVlsDelRep'],','));

                    foreach ($rps as $k => $v) {
                        $inf = array('edo_reg'=>'0','usu_mod'=>$this->seda['idu'],'fec_mod'=>date('Y-m-d H:i:s'),'ip_mod'=>Firewall::ipCatcher());
                        $whr = array('idEqRep'=>$v);
                        $rsp = $this->crud->update($inf,BD_PREFI.'equip_repos',$whr);
                        unset($inf,$whr,$rsp);
                    }
                    

                }

             } else {
                $cls = 'alert-danger';
                $msg = 'Hubo un error guardando la información: '.$resp['errmsg'].' &nbsp;&nbsp;<i class="fa fa-times" aria-hidden="true"></i>';
            } 

            $d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Gestionar mantenimientos'),
                    'footer'    =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'cls'       =>  $cls,
                    'msg'       =>  $msg
                ),
                'file' => 'html/mmtos/respsave.html'
            );


            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acordeón de elementos
        private function collapseData(int $data){


            $sqlFth = "SELECT ec.id idreg, ec.idCompo idelem, p.description, p.partserv
                        FROM tec_equip_compos ec, tec_components c, tec_parts p
                        WHERE ec.idCompo = c.id
                            AND c.idComponent = p.id
                            AND p.partserv = 'P'
                            AND ec.idEquip = ?
                            AND ec.edo_reg = 1";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data,'typ'=>'int']);
            $aw = $this->crud->select_group($sqlFth, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $ac = '<div class="accordion mt-4" id="accordionCompos">';

            foreach ($ar as $kf => $vf) {

                if( $kf != 0 ){
                    $col = 'collapsed';
                    $cl2 = '';
                } else {
                    $col = '';
                    $cl2 = ' show';
                }

                if( $vf['partserv'] == 'P' ) {
                    
                    $arfl = array('ideq'=>$data,'idco'=>$vf['idelem'],'idreg'=>$vf['idreg']);

                    $fb = self::control_fill($arfl);
                    unset($arfl);
                    $fl = $fb['fbox'];
                    $clsOpen = 'openModComp';
                    $fldsCont = 'Comp';
                    //$fl = $fb;

                } else {
                    $fb = self::repo_fill(array('idrep'=>$vf['idelem'],'ideqp'=>$data,'idreg'=>$vf['idreg']));
                    $fl = $fb;
                    $clsOpen = 'openModRepu';
                    $fldsCont = 'Repu';
                }
                
                $ac .= '<div class="card">
									
                            <div class="card-header bg-custom-ac" id="head'.$kf.'">
                                
                                <a class="btn btn-link '.$col.'" data-toggle="collapse" data-target="#coll'.$kf.'" aria-expanded="false" aria-controls="coll'.$kf.'">

                                <a id="tit'.$fldsCont.$vf['idreg'].'" class="btn btn-link '.$col.'" data-toggle="collapse" data-target="#coll'.$kf.'" aria-expanded="false" aria-controls="coll'.$kf.'">
                                    '.$vf['description'].'
                                </a>
                            </div>

                            <div id="coll'.$kf.'" class="collapse '.$cl2.'" aria-labelledby="head'.$kf.'" data-parent="#accordionCompos">
                                <div class="card-body bg-light">
                                    
                                    <div class="row">

                                    <input type="hidden" class="form-control" name="hid'.$fldsCont.'Reemp'.$vf['idreg'].'" id="hid'.$fldsCont.'Reemp'.$vf['idreg'].'" value="'.$vf['idreg'].'">
                                    
                                    <div id="fldCont'.$fldsCont.$vf['idreg'].'" class="row">
                                        '.$fl.'
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group"><label class="form-control-label" for="tarObservElem'.$fldsCont.$vf['idreg'].'">Observación</label>
                                                <textarea class="form-control ctrl-fld" name="tarObservElem'.$fldsCont.$vf['idreg'].'" id="tarObservElem'.$fldsCont.$vf['idreg'].'" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        
                                        <div class="col-lg-2 d-none"><button id="btnChng'.$vf['idelem'].'" type="button" class="btn btn-success btn-sm '.$clsOpen.'" idelem="'.$vf['idelem'].'" idreg="'.$vf['idreg'].'" model="mmtos" method="chngelem">
                                                Cambiar &nbsp;&nbsp;<i class="fa fa-refresh"></i>
                                            </button>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group form-check">
                                                <input type="hidden" class="form-check-input chk-compo" id="chkRev'.$vf['idelem'].'" name="chkRev'.$vf['idelem'].'">
                                                <label class="form-check-label form-control-label" for="chkRev'.$vf['idelem'].'"></label>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>';

            }

            $ac .= '</div>';

            return $ac;

        }

        // Render de campos de control con valores
        public function control_fill(array $data) {

            $sql = "SELECT cv.idField, cv.valField, v.label campo, v.id idVal
                    FROM tec_compo_vals cv, tec_equip_compos ec, tec_valists v, tec_parts_flds p
                    WHERE cv.idComponent = ec.idCompo
                        AND cv.idField = v.id
                        AND cv.idComponent = p.id
                        AND ec.idEquip = ?
                        AND cv.idComponent = ?;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['ideq'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['idco'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];

            foreach ($ar as $k => $v) {
                
                $fld = 'txt'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];
                $lflds .= $fld.',';
                $hfld = 'hid'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];

                $sqlGetType = "SELECT p.idType FROM tec_parts_flds p WHERE p.idField = ? LIMIT 1;";
                $dpt = array();
                array_push($dpt, ['kpa'=>1,'val'=>$v['idField'],'typ'=>'int']);
                $awt = $this->crud->select_group($sqlGetType, count($dpt), $dpt, 'arra');
                $art = $awt['res'][0];

                switch ($art['idType']) {
                    
                    case 41: // Número
                            $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].'</label>
                                            <input type="number" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'" readonly>
                                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                        </div>
                                      </div>';
                    break;

                    case 42: // Fecha
                        $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="date" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'" readonly>
                                        <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                    </div>
                                  </div>';
                    break;

                    case 43: // Texto
                        $fbox .= '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'" readonly>
                                        <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                    </div>
                                  </div>';
                    break;
                    
                }

            }
            
            return array('fbox'=>$fbox,'lflds'=>$lflds);

        }

        // Render de campos de repuestos
        public function repo_fill(array $data) {

            $sql = "SELECT er.idRepo, er.repvalues
                    FROM tec_equip_repos er
                    WHERE er.idRepo = ?
                        AND er.idEquip = ?
                        AND er.edo_reg = 1
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idrep'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['ideqp'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            $datfi = str_replace('[', '', $ar['repvalues']);
            $datfi = str_replace(']', '', $datfi);
            $datfi = json_decode($datfi,true);

            $sqlFld = "SELECT v.id, v.label campo, f.idType
                        FROM tec_parts_flds f, tec_valists v
                        WHERE f.idField = v.id
                            AND f.idPart = ?
                            AND f.idField = ?
                        LIMIT 1;";

            $dpf = array();
            array_push($dpf, ['kpa'=>1,'val'=>$data['idrep'],'typ'=>'int']);
            array_push($dpf, ['kpa'=>2,'val'=>$datfi['fld'],'typ'=>'int']);
            $awf = $this->crud->select_group($sqlFld, count($dpf), $dpf, 'arra');
            $arf = $awf['res'];

            foreach ($arf as $k => $v) {
                
                $fld = 'txt'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];
                $lflds .= $fld.',';
                $hfld = 'hid'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];

                switch ($v['idType']) {
                    
                    case 41: // Número
                            $fbox = '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].'</label>
                                            <input type="number" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'" readonly>
                                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$datfi['val'].'">
                                        </div>
                                      </div>';
                    break;

                    case 42: // Fecha
                        $fbox = '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="date" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'" readonly>
                                        <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$datfi['val'].'">
                                    </div>
                                  </div>';
                    break;

                    case 43: // Texto
                        $fbox = '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'" readonly>
                                        <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$datfi['val'].'">
                                    </div>
                                  </div>';
                    break;
                    
                }

            }

            return $fbox;

        }

        // Listado de equipos
        public function lsteqs(array $data){

            $sql = "SELECT e.id ÍTEM, e.internalNumber 'N° INTERNO', UPPER(m.label) MODELO, s.name PROYECTO,
                        '<a href=\"nuevo\" rel=\"mmtos\" action=\"sel\" title=\"Seleccionar equipo\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-check\"></i></a>' SELECCIONAR
					FROM ".BD_PREFI."equipment e, ".BD_PREFI."valists f, ".BD_PREFI."valists ma, ".BD_PREFI."valists m, ".BD_PREFI."sites s
					WHERE e.typeEquipamentId = f.id
                        AND e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id ";

			$dp = array();
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {

                        case 'slcClienteMod':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidCliente':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcSitiosMod':

                            $sql .= " AND e.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'hidProyecto':

                            $sql .= " AND e.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcFlotaMod':

                            $sql .= " AND e.typeEquipamentId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtNumIntMod':

                            $sql .= " AND e.internalNumber = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'slcMarcaMod':

                            $sql .= " AND m.valfather = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcModeloMod':

                            $sql .= " AND e.idModel = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= ';';

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,1,2,3,4,5);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Rótulo equipo a realizar actividad
        private function eqlabel(int $data){

            $sql = "SELECT e.id, e.internalNumber num_interno, UPPER(ma.label) marca, UPPER(m.label) modelo, 
                           f.label flota, s.name proyecto, c.name cliente, e.siteId, e.horometer
                    FROM ".BD_PREFI."equipment e, ".BD_PREFI."valists f, ".BD_PREFI."valists ma, 
                         ".BD_PREFI."valists m, ".BD_PREFI."sites s, ".BD_PREFI."company c
                    WHERE e.typeEquipamentId = f.id
                        AND e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND s.companyId = c.id
                        AND e.id = ?
                    LIMIT 1;";
                    
            $re = $this->crud->select_id($sql, $data, 'arra');
            $ar = $re['res'];

            $lbl = array(
                'lbl'=>$ar['num_interno'].' > '.$ar['marca'].' > '.$ar['modelo'].' > '.$ar['flota'].' > '.$ar['proyecto'].' > '.$ar['cliente'],
                'sid'=>$ar['siteId'],'hor'=>$ar['horometer'],'ideq'=>$ar['id']
            );

            return $lbl;

        }

        // Listado de empresas
        private function locations(array $conf){

            $sql = "SELECT l.id, l.name label
                    FROM ".BD_PREFI."locations l
                    WHERE l.siteId = ?
                        AND l.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$conf['sit'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];

            if( empty($ar) ){
                $sl = '';
            } else {
                $sl = $this->rndr->renderSelect($ar, 'SELECCIONE LOCALIZACIÓN', $conf['def']);
            }

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Datos del técnico buscado
        public function tecdata(int $data){

            $sql = "SELECT t.id, t.document, t.name tecnico, v.label grupo
                    FROM ".BD_PREFI."techs t, ".BD_PREFI."valists v
                    WHERE t.idGroup = v.id
                        AND t.id = ?
                        AND v.idlist = ?
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>9,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            echo json_encode(array('id'=>$ar['id'],'nombre'=>$ar['tecnico'],'grupo'=>$ar['grupo']),true);

        }

        // Datos de un componente, servicio o repuesto buscado por número de parte
        public function compdata(array $data){

            /*$sql = "SELECT p.id, p.idFamily, p.idCategory, p.partserv, p.partNum, v.value valproy, p.description
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_vals v
                    WHERE v.idPart = p.id
                        AND p.partNum = ?
                        AND v.idProject = ?
                        AND p.edo_reg = ?
                        AND ( EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_compos ec
                            WHERE ec.idCompo = p.id
                                AND ec.idEquip = ?
                        ) OR EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_repos er
                            WHERE er.idRepo = p.id
                                AND er.idEquip = ?
                        ) )
                    LIMIT 1;";*/

            $sql = "SELECT p.id, p.idFamily, p.idCategory, p.partserv, p.partNum, v.value valproy, p.description
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_vals v
                    WHERE v.idPart = p.id
                        AND p.partNum = ?
                        AND v.idProject = ?
                        AND p.edo_reg = ?
                    LIMIT 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['pnum'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['proj'],'typ'=>'int']);
            array_push($dp, ['kpa'=>3,'val'=>1,'typ'=>'int']);
            /*array_push($dp, ['kpa'=>4,'val'=>$data['equi'],'typ'=>'int']);
            array_push($dp, ['kpa'=>5,'val'=>$data['equi'],'typ'=>'int']);*/
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            echo json_encode($ar,true);

        }

        // Listado de componentes, servicios o repuestos según el tipo, categoría y familia
        public function complst(array $data){

            $sql = "SELECT p.id, p.description label
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_vals v
                    WHERE v.idPart = p.id
                        AND p.partserv = ?
                        AND p.idFamily = ?
                        AND p.idCategory = ?
                        AND v.idProject = ?
                        AND p.edo_reg = ? ;";

            /*if( $data['type'] != 'S' ){
                $sql .= "AND ( EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_compos ec
                            WHERE ec.idCompo = p.id
                                AND ec.idEquip = ?
                        ) OR EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_repos er
                            WHERE er.idRepo = p.id
                                AND er.idEquip = ?
                        ) );";
            } else {
                $sql .= ";";
            }*/

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['type'],'typ'=>'string']);
            array_push($dp, ['kpa'=>2,'val'=>$data['fami'],'typ'=>'int']);
            array_push($dp, ['kpa'=>3,'val'=>$data['cate'],'typ'=>'int']);
            array_push($dp, ['kpa'=>4,'val'=>$data['proj'],'typ'=>'int']);
            array_push($dp, ['kpa'=>5,'val'=>1,'typ'=>'int']);
            /*if( $data['type'] != 'S' ){
                array_push($dp, ['kpa'=>6,'val'=>$data['equi'],'typ'=>'int']);
                array_push($dp, ['kpa'=>7,'val'=>$data['equi'],'typ'=>'int']);
            }*/
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE PARTE O SERVICIO', '');

            echo $sl;

        }

        // Completar datos del servicio, parte o repuesto seleccionado
        public function compldata(array $data){

            $sql = "SELECT p.partNum, v.value valproj
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_vals v
                    WHERE v.idPart = p.id
                        AND p.id = ?
                        AND v.idProject = ?
                        AND p.edo_reg = ? ;";

            /*if( $data['type'] != 'S' ){
                $sql .= "AND ( EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_compos ec
                            WHERE ec.idCompo = p.id
                                AND ec.idEquip = ?
                        ) OR EXISTS (
                            SELECT 'z'
                            FROM ".BD_PREFI."equip_repos er
                            WHERE er.idRepo = p.id
                                AND er.idEquip = ?
                        ) );";
            } else {
                $sql .= ";";
            }*/

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['elem'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['proj'],'typ'=>'int']);
            array_push($dp, ['kpa'=>3,'val'=>1,'typ'=>'int']);
            /*if( $data['type'] != 'S' ){
                array_push($dp, ['kpa'=>4,'val'=>$data['equi'],'typ'=>'int']);
                array_push($dp, ['kpa'=>5,'val'=>$data['equi'],'typ'=>'int']);
            }*/
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'][0];

            echo json_encode($ar,true);

        }

        // Listado de empresas
        private function empresas(array $conf){

            $sql = "SELECT c.id, c.name label
                    FROM ".BD_PREFI."company c
                    WHERE c.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE CLIENTE', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de sitios por empresa
        public function sitios(array $conf){

            $sql = "SELECT s.id, s.name label
                    FROM ".BD_PREFI."sites s
                    WHERE s.edo_reg = ?
                        AND s.companyId = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$conf['val'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE PROYECTO/MINA', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de técnicos
        private function tecnicos(array $conf){

            $sql = "SELECT t.id, UPPER(t.name) label
                    FROM ".BD_PREFI."techs t
                    WHERE t.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE TÉCNICO', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado técnicos por proyecto
        private function lstec(int $idproy){

            $sql = "SELECT t.id, CONCAT(UPPER(t.name), ' - ', t.document) label
                    FROM ".BD_PREFI."techs t
                    WHERE t.siteId = ?
                        AND t.edo_reg = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$idproy,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE TÉCNICO', '');

            return $sl;

        }

        // Listado de tipos de parte
        private function partype(string $tyre, string $dfval){

            $ar = array(
                array('id'=>'S','label'=>'Servicio'),
                array('id'=>'P','label'=>'Parte'),
                array('id'=>'R','label'=>'Repuesto')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listas configuradas
        private function lists(array $data){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.idlist = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idlst'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            return $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);

        }

        // Lista dependiente
        public function listsdep(array $data){

            $sql = "SELECT v.id, v.label
                    FROM ".BD_PREFI."valists v
                    WHERE v.valfather = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idlst'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            echo $this->rndr->renderSelect($aw['res'], $data['str'], $data['def']);

        }

        // Listado de estados
        private function estados(string $tyre, string $dfval){

            $ar = array(
                array('id'=>1,'label'=>'ACTIVO'),
                array('id'=>0,'label'=>'INACTIVO')
            );

            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE ESTADO', $dfval);

            if( $tyre == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Listado de componentes
        public function repos(array $conf){

            $sql = "SELECT DISTINCT p.id, p.description label
                    FROM ".BD_PREFI."parts p, ".BD_PREFI."parts_flds f
                    WHERE p.id = f.idPart
                        AND p.partserv = 'R'
                        AND f.idField NOT IN (31,34)
                        AND p.idCategory = ?
                        AND p.idFamily = ?
                    ORDER BY 2;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$conf['val'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$conf['fam'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $sl = $this->rndr->renderSelect($ar, 'SELECCIONE REPUESTO', $conf['def']);

            if( $conf['typ'] == 'echo' ){
                echo $sl;
            } else {
                return $sl;
            }

        }

        // Render de campos de control
        public function controlr(int $data){

            $sql = "SELECT f.id, f.idField, c.label campo, f.idType
                    FROM tec_parts_flds f, tec_valists c
                    WHERE f.idField = c.id
                        AND f.idPart = ?
                        AND f.edo_reg = 1;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];

            foreach ($ar as $k => $v) {
                
                $fld = 'txt'.str_replace(" ", "", ucwords($v['campo'])).'-'.$v['idField'];

                switch ($v['idType']) {
                    
                    case 41: // Número
                            $fbox .= '<div class="col-lg-3 ctrlFldCont">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                            <input type="number" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control rep-fld-val" required>
                                        </div>
                                      </div>';
                    break;

                    case 42: // Fecha
                        $fbox .= '<div class="col-lg-3 ctrlFldCont">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                        <input type="date" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control rep-fld-val" required>
                                    </div>
                                  </div>';
                    break;

                    case 43: // Texto
                        $fbox .= '<div class="col-lg-3 ctrlFldCont">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].' <span class="text-danger">*</span></label>
                                        <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" idcamp="'.$v['idField'].'" class="form-control rep-fld-val" required>
                                    </div>
                                  </div>';
                    break;
                    
                }

            }
            
            echo $fbox;

        }
        public function lstrepos(int $equip){

            $sql = "SELECT er.idEqRep, er.idEquip, er.idRepo repu, p.description lrep, p.idFamily fami, f.label lfam, 
                        p.idCategory cats, c.label lcat, er.repvalues
                    FROM tec_equip_repos er, tec_parts p, tec_valists f, tec_valists c
                    WHERE er.idRepo = p.id
                        AND p.idFamily = f.id
                        AND p.idCategory = c.id
                        AND er.idEquip = ?
                        AND er.edo_reg = ?;";
                    
            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$equip,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $tr = '';
            $item = 1;
            
            foreach ($ar as $k => $v) {

                $btns = '<button class="btn btn-info btn-sm edit-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idEqRep'].'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
		        $btns .= '<button class="btn btn-danger btn-sm dele-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idEqRep'].'"><i class="fa fa-times"></i></button>';
                
                $hids = '<input type="hidden" name="hidFami'.$k.'" id="hidFami'.$k.'" value="'.$v['fami'].'">';
                $hids .= '<input type="hidden" name="hidCats'.$k.'" id="hidCats'.$k.'" value="'.$v['cats'].'">';
                $hids .= '<input type="hidden" name="hidRepu'.$k.'" id="hidRepu'.$k.'" value="'.$v['repu'].'">';
                $hids .= "<input type='hidden' name='hidVals".$k."' id='hidVals".$k."' value='".$v['repvalues']."'>";
                $hids .= "<input type='hidden' name='hididEqRep".$k."' id='hididEqRep".$k."' value='".$v['idEqRep']."'>";
                
                $tr .= '<tr id="tr'.$k.'">';
                    //$tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.$v['repu'].'</td>';
                    $tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.'<span class="spanRepu">'.$item.'</span></td>';
                    $tr .= '<td id="tdRepu'.$k.'">'.$v['lrep'].'</td>';
                    $tr .= '<td id="tdFami'.$k.'" class="text-center">'.$v['lfam'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['lcat'].'</td>';
                    $tr .= '<td class="text-center">'.$btns.'</td>';
                $tr .= '</tr>';

                $item++;

            }

            return $tr;

        }
        public function savemmtos(array $data){

            print_r($data);

            $info = array(
                'idEquip'       =>  $data['idEquip'],
                'idTypeAct'     =>  2,
                'idLocation'    =>  $data['slcLocal'],
                'startDate'     =>  $data['txtFecIniMmto'],
                'endDate'       =>  $data['txtFecFinMmto'],
                'startHour'     =>  $data['txtHoraInicio'],
                'endHour'       =>  $data['txtHoraFinal'],
                'horometerIni'  =>  $data['txtHoroIni'],
                'horometerEnd'  =>  $data['txtHoroFin'],
                'observaciones' =>  $data['tarObservActiv'],
                'edo_reg'       =>  1
            );

            if( !empty($data->hidId) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data->hidId);

                $resp = $this->crud->update($info,BD_PREFI.'equipment',$where);

            } else {

                $info['usu_crea'] = $this->seda['idu'];
                $info['fec_crea'] = date('Y-m-d H:i:s');
                $info['ip_crea']  = Firewall::ipCatcher();
                print_r($info);
                $resp = $this->crud->insert($info,BD_PREFI.'equipment');

            }


            $resp = $this->crud->insert($info,BD_PREFI.'activities');

            print_r($resp);

            

            if( $resp['rta'] == 'OK' ){

                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';

            
            }
        }

    }

?>