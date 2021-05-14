<?php

    class inspec{
        
        public function __construct(array $res){
        	$this->clstr = $res['cleanstr'];
        	$this->crud = $res['crud'];
        	$this->rndr = $res['render'];
        	$this->fima = $res['fileman'];
            $this->seda = $_SESSION['u'];
        }

        // Método inicial
        public function index(){
        	$d = array(
                'data' => array(
                    'header'	=>  $this->rndr->renderHeader('Inspecciones'),
                    'footer'	=>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'flotas'    =>  self::lists(array('idlst'=>11,'str'=>'SELECCIONE FLOTA','def'=>'')),
                    'marcas'    =>  self::lists(array('idlst'=>2,'str'=>'SELECCIONE MARCA','def'=>'')),
                    'tecnicos'  =>  self::tecnicos(array('def'=>'','typ'=>'retu')),
                    'empresas'  =>  self::empresas(array('def'=>'','typ'=>'retu')),
                    'estados'   =>  self::estados('return',''),
                    'usuario'   =>  $this->seda['idu']
                ),
                'file' => 'html/inspec/index.html'
            );

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }
       
        

        // Listar mantenimientos y servicios  
        public function listar(array $data){ 
             $sql = "SELECT  DATE(a.fec_crea) 'Fecha registro',
                        CONCAT(UPPER(ma.label), ' ', UPPER(m.label)) 'Marca/Modelo', e.internalNumber 'Número interno',
                        c.name Cliente, s.name Proyecto, a.startDate 'Fecha inicio', a.endDate 'Fecha fin',
                        CONCAT('<a idreg=\"',a.id,'\" href=\"editar\" rel=\"inspec\" action=\"upd\" title=\"Editar Mantenimiento\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-pencil\"></i></a>') MODIFICAR
                    FROM tec_equipment e, tec_valists ma, tec_valists m, tec_sites s, tec_company c, tec_activities a, tec_typeactivity t, tec_activ_techs tec_a, tec_techs tech
                    WHERE e.idModel = m.id
                        AND m.valfather = ma.id
                        AND e.siteId = s.id
                        AND s.companyId = c.id
                        AND a.idTypeAct = t.id
                        AND e.id = a.idEquip 
                        AND tec_a.idactiv = a.id 
                        AND tech.id = tec_a.idtech
                        AND t.id = 1";

            $dp = array();
            
            $im = 1;

            foreach ($data as $k => $v) {

                if( strlen($v) > 0 ){

                    switch($k) {


                        case 'slcCliente':

                            $sql .= " AND s.companyId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcSitios':

                            $sql .= " AND e.siteId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;


                        case 'slcFlotas':

                            $sql .= " AND e.typeEquipamentId = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcMarca':

                            $sql .= " AND m.valfather = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'slcModelo':

                            $sql .= " AND e.idModel = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                        case 'txtNumInter':

                            $sql .= " AND e.internalNumber = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'slcTecnico':

                            $sql .= " AND tech.id = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'txtFecInicio':

                            $sql .= " AND a.startDate = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;

                        case 'txtFecFin':

                            $sql .= " AND a.endDate = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'string']);
                            
                        break;


                        case 'slcEstado':

                            $sql .= " AND e.edo_reg = ? ";
                            array_push($dp, ['kpa'=>$im,'val'=>$v,'typ'=>'int']);
                            
                        break;

                    }

                    $im++;

                }

            }

            $sql .= " GROUP BY a.id ";

            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ccols = array(0,1,2,6,7);
            echo $this->rndr->table_html($aw, $ccols, 'tabHtml');

        }

        // Lanzar formulario de nuevo registro
        public function nuevo(int $data){

            $lbl = self::eqlabel($data);
            $d = array(
                'data' => array(
                    'header'	    =>  $this->rndr->renderHeader('Gestionar mantenimientos'),
                    'footer'        =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'hidIdActiv'       =>  self::lastReg(),
                    'hidIdEquip'       =>  $lbl['ideq'],
                    'idsite'        =>  $lbl['sid'],
                    'eqlabel'       =>  $lbl['lbl'],
                    'horomet'       =>  $lbl['hor'],
                    'accorde'       =>  self::collapseData($data,self::lastReg()),
                    'locatio'       =>  self::locations(array('def'=>'','typ'=>'retu','sit'=>$lbl['sid'])),
                    'familia'       =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categoria'     =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍAS','def'=>'')),
                    'partype'       =>  self::partype('return',''),
                    'estados'       =>  self::estados('return',''),
                    'tecnicos'      =>  self::lstec($lbl['sid']),
                    'usuario'       =>  $this->seda['idu'] 
                ),
                'file' => 'html/inspec/nuevo.html'
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

        public function lastReg(){
            $sqlLastId = "SELECT MAX(id) id FROM tec_activities a WHERE a.edo_reg = ?";
            $reLastId = $this->crud->select_id( $sqlLastId, '1', 'arra');
            $arLastId = $reLastId['res'];
            if ($arLastId){
                $idActiv = ($arLastId['id']+1);
            }else{
                $idActiv = 1;
            }
           
            return $idActiv;
        }
       
        // Mostrar datos para editar
        public function editar(int $data){

           

            $sql = "SELECT *
                FROM ".BD_PREFI."equipment e, ".BD_PREFI."valists ma, ".BD_PREFI."valists m, ".BD_PREFI."sites s, ".BD_PREFI."company c, ".BD_PREFI."activities a, ".BD_PREFI."typeactivity t, ".BD_PREFI."activ_techs tec_a, ".BD_PREFI."techs tchs
                WHERE e.idModel = m.id
                    AND m.valfather = ma.id
                    AND e.siteId = s.id
                    AND s.companyId = c.id
                    AND a.idTypeAct = t.id
                    AND e.id = a.idEquip 
                    AND tec_a.idactiv = a.id 
                    AND tchs.id = tec_a.idtech
                    AND a.id = ?
                    LIMIT 1;";

                    $re = $this->crud->select_id($sql, $data, 'arra');
                    $ar = $re['res'];


            $lbl = self::eqlabel($ar['idEquip']);
            

            $d = array(
                'data' => array(
                    'header'	    =>  $this->rndr->renderHeader('Editar mantenimientos'),
                    'footer'        =>  $this->rndr->renderFooter(EMP_NAME,YEARCOPY),
                    'hidIdEquip'       =>  $lbl['ideq'],
                    'hidIdActiv'       =>  $data,
                    'startDate'       =>  $ar['startDate'],
                    'endDate'       =>  $ar['endDate'],
                    'startHour'       =>  $ar['startHour'],
                    'endHour'       =>  $ar['endHour'],
                    'horometerIni'       =>  $ar['horometerIni'],
                    'horometerEnd'       =>  $ar['horometerEnd'],
                    'idLocation'       =>  $ar['idLocation'],
                    'observaciones'       =>  $ar['observaciones'],
                    'idActiv'       => $data,
                    'totalCostClient'       => self::totOtros($data),
                    'totalPercentaje'       => self::totPercentTec($data),
                    
                    'idsite'        =>  $lbl['sid'],
                    'eqlabel'       =>  $lbl['lbl'],
                    'horomet'       =>  $lbl['hor'],
                    'activities'       =>  self::Activities(array('def'=>$ar['idTypeAct'],'typ'=>'retu')),
                    'accorde'       =>  self::collapseData($ar['idEquip'],$data),
                    'locatio'       =>  self::locations(array('def'=>$ar['idLocation'],'typ'=>'retu','sit'=>$lbl['sid'])),
                    'familia'       =>  self::lists(array('idlst'=>4,'str'=>'SELECCIONE FAMILIA','def'=>'')),
                    'categoria'     =>  self::lists(array('idlst'=>5,'str'=>'SELECCIONE CATEGORÍAS','def'=>'')),
                    'partype'       =>  self::partype('return',''),
                    'estados'       =>  self::estados('return',''),
                    'tecnicos'      =>  self::lstecActiv($ar['idactiv']),
                    'reps'      =>  self::lsRepsActiv($ar['idactiv']),
                    'Listtecnicos'      =>  self::lstec($lbl['sid']),
                    'usuario'       =>  $this->seda['idu'] 
                ),
                'file' => 'html/inspec/editar.html'
            );
            

            foreach ($ar as $key => $value) {
                $d['data'][$key] = $value;
            }


            

            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }
        public function totOtros($data){
            
            $sql = "SELECT vtotal FROM tec_activ_part_serv taps WHERE taps.idactiv = ? AND taps.edo_reg = ?";
                $dp = array();
                array_push($dp, ['kpa'=>1,'val'=>$data,'typ'=>'int']);
                array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
                $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');

                $ar = $aw['res'];
                $totalOtros;
                if($ar){
                    foreach ($ar as $key => $value) {
                        $totalOtros += $value['vtotal'];
                    }
                }else{
                    $totalOtros = 0;
                }
                
                return $totalOtros;

        }

        public function totPercentTec($data){
            
            $sql = "SELECT SUM(parper) total FROM `tec_activ_techs` at WHERE at.`idactiv` = ? and at.edo_reg = 1";
            
            $aw = $this->crud->select_id($sql, $data, 'arra');

            $ar = $aw['res'];
            if($ar){
                $totalOtros = $ar['total'];
            }else{
                $totalOtros = 0;
            }
            
            return($totalOtros);

        }
        

        public function editarCompAsync(array $data){

          
            
            if($data['idActiv']){
                $sql = "SELECT * FROM tec_activ_comp WHERE idactiv = ? AND idcomp = ? ";
                $dp = array();
                array_push($dp, ['kpa'=>1,'val'=>$data['idActiv'],'typ'=>'int']);
                array_push($dp, ['kpa'=>2,'val'=>$data['idCompo'],'typ'=>'int']);
                $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
                $ar = $aw['res'];
                
                if($ar){
                    $info['observ'] = $data['valor'];
                    $info['usu_mod'] = $this->seda['idu'];
                    $info['fec_mod'] = date('Y-m-d H:i:s');
                    $info['ip_mod']  = Firewall::ipCatcher();
        
                    $where = array('idactiv'=>$data['idActiv'],
                                    'idcomp'=>$data['idCompo']);
        
                    $resp = $this->crud->update($info,BD_PREFI.'activ_comp',$where);
                    
                }else{
                    $infr['idcomp'] = $data['idCompo'];
                    $infr['idactiv'] = $data['idActiv'];
                    $infr['observ'] = $data['valor'];
                    $infr['edo_reg'] = 1;
                    $infr['usu_crea'] = $this->seda['idu'];
                    $infr['fec_crea'] = date('Y-m-d H:i:s');
                    $infr['ip_crea']  = Firewall::ipCatcher();
                    
                    // inserta  relacion del componente nuevo con el equipo   
    
                    $rr = $this->crud->insert($infr,BD_PREFI.'activ_comp');
                    ;
                }
            }else{
                $info['edo_reg'] = '2';
                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();
    
                $where = array('idField'=>$data['idField'],
                                'idComponent'=>$data['idCompo']);
    
                $resp = $this->crud->update($info,BD_PREFI.'compo_vals',$where);
                
                $info2['idField'] = $data['idField'];
                $info2['idComponent'] = $data['idCompo'];
                $info2['valField'] = $data['valor'];
                $info2['edo_reg'] = 1;
                $info2['usu_crea'] = $this->seda['idu'];
                $info2['fec_crea'] = date('Y-m-d H:i:s');
                $info2['ip_crea']  = Firewall::ipCatcher();
    
                $resp2 = $this->crud->insert($info2,BD_PREFI.'compo_vals');
            }
        }

        public function changeCompo(array $data){

            $info = array();

            $info['edo_reg'] = 0;
            $info['usu_mod'] = $this->seda['idu'];
            $info['fec_mod'] = date('Y-m-d H:i:s');
            $info['ip_mod']  = Firewall::ipCatcher();

            $where = array('id'=>$data['idCompoOld']);

            // actualiza el estado del componente antiguo a inactivo

            $resp = $this->crud->update($info,BD_PREFI.'components',$where);
            unset($info,$where,$resp);

            $info2 = array();

            $info2['edo_reg'] = 2;
            $info2['usu_mod'] = $this->seda['idu'];
            $info2['fec_mod'] = date('Y-m-d H:i:s');
            $info2['ip_mod']  = Firewall::ipCatcher();
            
            $where2 = array('id'=>$data['compos']);

            // actualizar el estado del componente nuevo a asignado
            
            $resp2 = $this->crud->update($info2,BD_PREFI.'components',$where2);
            
            unset($info2,$where2,$resp2);

            $info3 = array();

            $info3['edo_reg'] = 0;
            $info3['usu_mod'] = $this->seda['idu'];
            $info3['fec_mod'] = date('Y-m-d H:i:s');
            $info3['ip_mod']  = Firewall::ipCatcher();

            $where3 = array('idEquip'=>$data['idEquip'],
                            'idCompo'=>$data['idCompoOld']);
            
            
            // actualizar la relacion del componente con el equipo                

            $resp3 = $this->crud->update($info3,BD_PREFI.'equip_compos',$where3);
           
            unset($info3,$where3,$resp3);
            

            $sql = "SELECT c.id idReg  FROM tec_equip_compos c WHERE c.idCompo = ?";
                    
            $re = $this->crud->select_id($sql, $data['compos'], 'arra');
            $ar = $re['res'];
            
           
          
            if (!empty($re)) {
                
                unset($info2,$where2,$resp2);

                $info3 = array();

                $info3['idEquip'] = $data['idEquip'];
                $info3['edo_reg'] = 1;
                $info3['usu_mod'] = $this->seda['idu'];
                $info3['fec_mod'] = date('Y-m-d H:i:s');
                $info3['ip_mod']  = Firewall::ipCatcher();

                $where3 = array('idCompo'=>$data['compos']);
                
                
                // actualizar la relacion del componente con el equipo                

                $resp3 = $this->crud->update($info3,BD_PREFI.'equip_compos',$where3);

               
            }else {
               
                $infr['idEquip'] = $data['idEquip'];
                $infr['idCompo'] = $data['compos'];
                $infr['edo_reg'] = 1;
                $infr['usu_crea'] = $this->seda['idu'];
                $infr['fec_crea'] = date('Y-m-d H:i:s');
                $infr['ip_crea']  = Firewall::ipCatcher();
                
                // inserta  relacion del componente nuevo con el equipo   

                $rr = $this->crud->insert($infr,BD_PREFI.'equip_compos');
                
            }
        }


        public function lstecActiv(int $idActiv){

            $sql = "SELECT t.id, t.document, t.name, v.label , at.parper 
                FROM tec_techs t, tec_activities a , tec_activ_techs at , tec_valists v
                    WHERE a.id = at.idActiv 
                        and at.idTech = t.id 
                        and t.idGroup = v.id
                        and v.idlist = 9
                        and a.id = ?
                        and at.edo_reg = ?";
                    
            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$idActiv,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $tr = '';
            $item = 1;

            
            foreach ($ar as $k => $v) {

                $btns = '<button class="btn btn-info btn-sm edit-dty-tec" type="button" idfila="'.$k.'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
		        $btns .= '<button class="btn btn-danger btn-sm dele-dty-tec" type="button" idfila="'.$k.'"><i class="fa fa-times"></i></button>';
                
                $hids = '<input type="hidden" name="hidCurLinTecTab'.$k.'" id="hidCurLinTecTab'.$k.'" value="'.$k.'">';
                $hids .= '<input type="hidden" name="hidCurIdTecTab'.$k.'" id="hidCurIdTecTab'.$k.'" value="'.$v['id'].'">';
                
                $tr .= '<tr id="trTecTab'.$k.'">';
                    //$tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.$v['repu'].'</td>';
                    $tr .= '<td id="tdCeduTecTab'.$k.'" class="text-center">'.$v['document'].$hids.'</td>';
                    $tr .= '<td id="tdNombTecTab'.$k.'" class="text-center">'.$v['name'].'</td>';
                    $tr .= '<td id="tdGrupTecTab'.$k.'" class="text-center">'.$v['label'].'</td>';
                    $tr .= '<td id="tdPorcTecTab'.$k.'" class="text-center">'.$v['parper'].'</td>';
                    $tr .= '<td class="text-center">'.$btns.'</td>';
                $tr .= '</tr>';

                $item++;

            }

            return $tr;

        }

        public function lsRepsActiv(int $idActiv){

            $sql = "SELECT p.description, aps.cant, p.partNum, aps.vunit, aps.vtotal , aps.idpartserv
                FROM tec_activities a, tec_parts p, tec_activ_part_serv aps
                    WHERE aps.idactiv = a.id 
                        and aps.idpartserv = p.id 
                        and a.id = ? 
                        and aps.edo_reg = ?";
                    
            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$idActiv,'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            $tr = '';
            $item = 1;
            
            foreach ($ar as $k => $v) {

                $btns = '<button class="btn btn-info btn-sm edit-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idpartserv'].'"><i class="fa fa-pencil"></i></button>&nbsp;&nbsp;';
		        $btns .= '<button class="btn btn-danger btn-sm dele-dty-rep" type="button" idfila="'.$k.'" idx="'.$v['idpartserv'].'"><i class="fa fa-times"></i></button>';
                
                $hids = '<input type="hidden" name="hidFami'.$k.'" id="hidFami'.$k.'" value="'.$v['document'].'">';
                $hids .= '<input type="hidden" name="hidCats'.$k.'" id="hidCats'.$k.'" value="'.$v['name'].'">';
                $hids .= '<input type="hidden" name="hidRepu'.$k.'" id="hidRepu'.$k.'" value="'.$v['idGroup'].'">';
                $hids .= "<input type='hidden' name='hidVals".$k."' id='hidVals".$k."' value='".$v['parper']."'>";
                
                $tr .= '<tr id="tr'.$k.'">';
                    //$tr .= '<td id="tdItem'.$k.'" class="text-center">'.$hids.$v['repu'].'</td>';
                    $tr .= '<td id="tdRepu'.$k.'" class="text-center">'.$v['description'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['cant'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['partNum'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['vunit'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['vtotal'].'</td>';
                    $tr .= '<td class="text-center">'.$btns.'</td>';
                $tr .= '</tr>';

                $item++;

            }

            return $tr;

        }








        // Acción de guardar
        public function guardar(array $data){

    
            $info = array(
                'idEquip'       =>  $data['idEquip'],
                'idTypeAct'     =>  isset($data['idActivnew']) ? isset($data['idActivnew']) : 1,
                'idLocation'    =>  (int)$data['slcLocal'],
                'startDate'     =>  $data['txtFecIniMmto'],
                'endDate'       =>  $data['txtFecFinMmto'],
                'startHour'     =>  $data['txtHoraInicio'],
                'endHour'       =>  $data['txtHoraFinal'],
                'horometerIni'  =>  $data['txtHoroIni'],
                'horometerEnd'  =>  $data['txtHoroFin'],
                'observaciones' =>  $data['tarObservActiv'],
                'edo_reg'       =>  1
            );

          if( !empty($data['hidId']) ){

                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('id'=>$data['hidId']);

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

                    $sqlT = "SELECT ta.idtech FROM tec_activ_techs ta WHERE ta.idactiv = ? and ta.idtech  = ?";

                    $dpc = array();

                    array_push($dpc, ['kpa'=>1,'val'=>$data['hidId'],'typ'=>'int']);
                    array_push($dpc, ['kpa'=>2,'val'=>$vt['idetec'],'typ'=>'int']);
                    
                
                    $awt = $this->crud->select_group($sqlT, count($dpc), $dpc, 'arra');
                    $ar = $awt['res'];
                    

                    if( $ar ){

                        $inft['usu_mod'] = $this->seda['idu'];
                        $inft['fec_mod'] = date('Y-m-d H:i:s');
                        $inft['ip_mod']  = Firewall::ipCatcher();
        
                        $wt = array('idtech'=>$vt['idetec'],'idactiv'=>$data['hidId']);
                        
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
                'file' => 'html/inspec/respsave.html'
            );


            $this->rndr->setData($d);
            echo $this->rndr->rendertpl();

        }

        // Acordeón de elementos
        private function collapseData(int $data, int $idActiv){

            $sqlFth = "SELECT ec.id idreg, ec.idCompo idelem, p.description, p.partserv
                        FROM tec_equip_compos ec, tec_components c, tec_parts p
                        WHERE ec.idCompo = c.id
                            AND c.idComponent = p.id
                            AND p.partserv = 'P'
                            AND ec.idEquip = ?
                            AND ec.edo_reg = 1
                            AND c.edo_reg = 2
                            GROUP BY ec.idCompo
                            UNION
                            SELECT er.idEqRep idreg, er.idRepo idelem, p.description, p.partserv
                            FROM tec_equip_repos er, tec_parts p
                            WHERE er.idEquip = ?
                                AND er.edo_reg = 1
                                AND er.idRepo = p.id;";

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
                
                $fobserv = self::observCompo(array('idElem'=>$vf['idelem'],'idActiv'=>$idActiv));
                $obsfld = $fobserv;

                if(!$obsfld){
                   $obsfld = "";
                }
                

                $conttitle = $kf+1;
                $ac .= '    <div class="card mb-2">
									
                                <div class="card-header bg-custom-ac" id="head'.$kf.'">
                                    <div class="row">
                                        <div class="col-lg-10">
                                            <a id="tit'.$fldsCont.$vf['idreg'].'" class="btn btn-link '.$col.'" data-toggle="collapse" data-target="#coll'.$kf.'" aria-expanded="false" aria-controls="coll'.$kf.'">
                                                '.$vf['description'].' < '.$conttitle.' >
                                            </a>
                                        </div>

                                        
                                        <div class="col-lg-2 pt-2 pr-4 form-group form-check text-right">
                                            <input type="checkbox" class="form-check-input chk-compo" id="chkRev'.$vf['idelem'].'" name="chkRev'.$vf['idelem'].'">
                                            <label class="form-check-label form-control-label" for="chkRev'.$vf['idelem'].'">Revisado</label>
                                        </div> 
                                    </div>
                                </div>

                                <div id="coll'.$kf.'" class="collapse '.$cl2.'" aria-labelledby="head'.$kf.'" data-parent="#accordionCompos">
                                    <div class="card-body bg-light">
                                        <div id="rowMsg'.$vf['idelem'].'" class="row hidden-row">
                                            <div class="col-lg-12">
                                                <div id="contMsg'.$vf['idelem'].'" class="alert alert-success"></div>
                                            </div>
                                        </div> 
                                        
                                        <div class="row">
                                            
                                            <input type="hidden" class="form-control" name="hid'.$fldsCont.'Reemp'.$vf['idreg'].'" id="hid'.$fldsCont.'Reemp'.$vf['idreg'].'" value="'.$vf['idreg'].'">
                                            
                                            <div id="fldCont'.$vf['idelem'].'" class="col-lg-12 row">
                                            
                                                '.$fl.'
                                                
                                            </div>

                                            
                                            <div class="col-lg-12 '.$paramP.'">
                                                <div class="form-group"><label class="form-control-label" for="tarObservElem'.$fldsCont.$vf['idreg'].'">Observación</label>
                                                    <textarea onchange="chgValComp(this.value,this.id,'.$data['idco'].')"  class="form-control ctrl-fld" name="tarObservElem'.$fldsCont.$vf['idreg'].'" id="'.$vf['idelem'].'" rows="3">'.$obsfld.'</textarea>                                                
                                                </div>
                                            </div>

                                        
                                            <div class="col-md-6">
                                                <button onclick="changeCompoActiv('.$vf['idelem'].')" id="btnChng'.$vf['idelem'].'" type="button" class="btn btn-success btn-sm '.$clsOpen.'" idelem="'.$vf['idelem'].'" idreg="'.$vf['idreg'].'">
                                                    Cambiar &nbsp;&nbsp;<i class="fa fa-refresh"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>';

            }

            $ac .= '</div>';

            return $ac;

        }

        //Render observaciones

        public function observCompo(array $data){
         
            $sql = "SELECT ac.observ  FROM tec_activ_comp ac WHERE ac.idcomp = ? AND ac.idactiv = ? and ac.edo_reg = 1";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['idElem'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['idActiv'],'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];
            
            
            return($ar[0]['observ']);
        }

        // Render de campos de control con valores
        public function control_fill(array $data) {

            

            $sql = "SELECT cv.idField, cv.valField, v.label campo, v.id idVal
                    FROM tec_compo_vals cv, tec_equip_compos ec, tec_valists v, tec_components c
                    WHERE cv.idComponent = ec.idCompo
                        AND cv.idField = v.id
                        AND ec.idCompo = c.id
                        AND ec.idEquip = ?
                        AND cv.idComponent = ?
                        AND ec.edo_reg = ?
                        AND c.edo_reg = ?
                        AND cv.edo_reg = ?
                        GROUP BY v.id";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>$data['ideq'],'typ'=>'int']);
            array_push($dp, ['kpa'=>2,'val'=>$data['idco'],'typ'=>'int']);
            array_push($dp, ['kpa'=>3,'val'=>1,'typ'=>'int']);
            array_push($dp, ['kpa'=>4,'val'=>2,'typ'=>'int']);
            array_push($dp, ['kpa'=>5,'val'=>1,'typ'=>'int']);
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
                
                if ($v['campo'] == 'Serial' || $v['campo'] == 'Consecutivo'){
                    $fbox .= '<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">'.$v['campo'].'</label>
                            <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'" readonly>
                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                        </div>
                    </div>';
                }else{
                    switch ($art['idType']) {
                    
                        case 41: // Número
                                $fbox .= '<div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="form-control-label">'.$v['campo'].'</label>
                                                <input onchange="chgValComp(this.value,this.id,'.$data['idco'].')" type="number" id="'.$v['idField'].'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'">
                                                <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                            </div>
                                          </div>';
                        break;
    
                        case 42: // Fecha
                            $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].'</label>
                                            <input onchange="chgValComp(this.value,this.id,'.$data['idco'].')" type="date" id="'.$v['idField'].'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'" >
                                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                        </div>
                                      </div>';
                        break;
    
                        case 43: // Texto
                            $fbox .= '<div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">'.$v['campo'].'</label>
                                            <input onchange="chgValComp(this.value,this.id,'.$data['idco'].')" type="text" id="'.$v['idField'].'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$v['valField'].'">
                                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$v['idVal'].'">
                                        </div>
                                      </div>';
                        break;
                        
                    }
                }

                

            }
            if($data['visualizacion']){
                echo $fbox;
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
                                            <input type="number" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'">
                                            <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$datfi['val'].'">
                                        </div>
                                      </div>';
                    break;

                    case 42: // Fecha
                        $fbox = '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="date" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'">
                                        <input type="hidden" id="'.$hfld.'" name="'.$hfld.'" value="'.$datfi['val'].'">
                                    </div>
                                  </div>';
                    break;

                    case 43: // Texto
                        $fbox = '<div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">'.$v['campo'].'</label>
                                        <input type="text" id="'.$fld.'" name="'.$fld.'" placeholder="'.$v['campo'].'" class="form-control ctrl-fld" value="'.$datfi['val'].'">
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
                        '<a href=\"nuevo\" rel=\"inspec\" action=\"sel\" title=\"Seleccionar equipo\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-check\"></i></a>' SELECCIONAR
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
        private function Activities(array $conf){

            $sql = "SELECT tpa.id, tpa.name label
                    FROM ".BD_PREFI."typeactivity tpa
                        WHERE tpa.edo_reg = ?;";

            $dp = array();
            array_push($dp, ['kpa'=>1,'val'=>1,'typ'=>'int']);
            $aw = $this->crud->select_group($sql, count($dp), $dp, 'arra');
            $ar = $aw['res'];

            if( empty($ar) ){
                $sl = '';
            } else {
                $sl = $this->rndr->renderSelect($ar, 'SELECCIONE ACTIVIDAD', $conf['def']);
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
                    FROM ".BD_PREFI."parts p
                    WHERE p.partserv = ?
                        AND p.idFamily = ?
                        AND p.idCategory = ?
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
            array_push($dp, ['kpa'=>4,'val'=>1,'typ'=>'int']);
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
                    $tr .= '<td id="tdRepu'.$k.'" class="text-center">'.$v['lrep'].'</td>';
                    $tr .= '<td id="tdFami'.$k.'" class="text-center">'.$v['lfam'].'</td>';
                    $tr .= '<td id="tdCate'.$k.'" class="text-center">'.$v['lcat'].'</td>';
                    $tr .= '<td class="text-center">'.$btns.'</td>';
                $tr .= '</tr>';

                $item++;

            }

            return $tr;

        }
        public function deleteTecs($data){
                
            
                $info['edo_reg'] = 0;
                $info['usu_mod'] = $this->seda['idu'];
                $info['fec_mod'] = date('Y-m-d H:i:s');
                $info['ip_mod']  = Firewall::ipCatcher();

                $where = array('idtech'=>$data['idTech'],'idactiv'=>$data['idActiv']);

                $resp = $this->crud->update($info,BD_PREFI.'activ_techs',$where);

                print_r($resp);
                
        }
        public function deleteRepo($data){
            
            $info['edo_reg'] = 0;
            $info['usu_mod'] = $this->seda['idu'];
            $info['fec_mod'] = date('Y-m-d H:i:s');
            $info['ip_mod']  = Firewall::ipCatcher();

            $where = array('idpartserv'=>$data['idRepo'],'idactiv'=>$data['idActiv']);

            $resp = $this->crud->update($info,BD_PREFI.'activ_part_serv',$where);
            
            
            
    }

        
        public function savemmtos(array $data){

           

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
               
                $resp = $this->crud->insert($info,BD_PREFI.'equipment');

            }


            $resp = $this->crud->insert($info,BD_PREFI.'activities');

           

            

            if( $resp['rta'] == 'OK' ){

                $cls = 'alert-success';
                $msg = 'Información guardada correctamente. &nbsp;&nbsp;<i class="fa fa-check" aria-hidden="true"></i>';

            
            }
        }

    }

?>