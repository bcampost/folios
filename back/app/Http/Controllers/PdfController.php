<?php

namespace App\Http\Controllers;

use App\Pdf\PDF;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Folio;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\FolioAprobadoState;
use App\States\Folio\FolioSolicitadoState;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected $fpdf;
    public function __construct()
    {
        // $this->fpdf = new Fpdf('P','mm',array(215.9,279.4));
        $this->fpdf = new Fpdf('P','mm',array(215.9,279.4));
    }
    public function index(Request $request, Folio $folio)
    {
        //dd($folio);
        $imagen = $folio->getFirstMedia('caratula');
        $previoAprobado = $folio->transitions()->where("next_state_id", PrevioAprobadoState::getStateId())->first();
        $folioAprobado = $folio->transitions()->where("next_state_id", FolioAprobadoState::getStateId())->first();
        $folioSolicitado = $folio->transitions()->where("next_state_id", FolioSolicitadoState::getStateId())->first();

        $folio->load('product', 'project.deal', 'lastTransition','project');
        $data_json=json_decode($folio, true);
        $product = $folio->product;
        $customer = $folio->project->deal?->dealable();
        $category = $product?->category;
        $tornillos = $folio->screw_kits;
        $owner = $folio->owner;
        $sku = $product?->sku;
        $list_price = $product?->price;
        $list_cost = $product?->cost;
        $id = $data_json['id'];
        $type = $data_json['type'];
        $classification = $data_json['classification'];
        $cost = $data_json['cost'];
        $reference_product = $data_json['reference_product'];
        $melamina_color = $data_json['melamina_color'];
        $melamina_density = $data_json['melamina_density'];
        $project_id = $data_json['project_id'];
        $channel = $folio->project->channel;
        $assembly_number = $data_json['assembly_number'];
        $previo_code = $data_json['previo_code'];
        $folio_code = $data_json['folio_code'];
        $quantity = $data_json['quantity'];
        $height = $data_json['height'];
        $height = ($height != '') ? $height  : "";
        $width = $data_json['width'];
        $width = ($width != '') ? $width  : "";
        $depth = $data_json['depth'];
        $espesor = $melamina_density;
        $empaque = $data_json['package_type'];
        $chapacinta_color = $data_json['chapacinta_color'];
        $structure_color = $data_json['structure_color'];
        $tela_color = $data_json['tela_color'];
        $description = $data_json['description'];
        $created_at =$data_json['created_at'];
        $created_at = date("d-m-Y", strtotime($data_json['created_at']));
        $this->fpdf->AddFont('Poppins-Bold','','Poppins-Bold.php');
        $this->fpdf->AddFont('Poppins-Regular','','Poppins-Regular.php');
        $this->fpdf->AddFont('Montserrat-Bold','','Montserrat-Bold.php');
        $this->fpdf->AddFont('Montserrat-Regular','','Montserrat-Regular.php');
        $this->fpdf->SetFont('Arial', 'B', 15);
        $this->fpdf->SetFont('Poppins-Regular', '', 9);

        //Folios *****   Folios ************** Folios ****/
        if ($type=="folio"){
                $this->fpdf->AddPage();

                $xfile="logo-31.jpg";
            $fondo_lupita = storage_path('app/public/lineaitalia/' . ($xfile ?? 'default.png'));
                $this->fpdf->Rect(8,14,200,248);
                $this->fpdf->Image($fondo_lupita,7,12,55,0);
                //Fila 1
                $this->fpdf->Rect(8,14,50,15);
                $this->fpdf->Rect(58,14,90,15);
                $this->fpdf->SetFont('Montserrat-Regular', '', 10);
                $this->fpdf->SetXY(80,16);
                $this->fpdf->Cell(80,4,utf8_decode('PRODUCTOS ESPECIALES'));
                $this->fpdf->SetXY(82,21);
                $this->fpdf->Cell(80,4,utf8_decode('TIPO A - (F-ING-08)'));
                $this->fpdf->Rect(148,14,30,5);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->SetXY(156,15);
                $this->fpdf->Cell(10,3,utf8_decode("Revisión:"));
                $this->fpdf->Rect(178,14,30,5);
                $this->fpdf->SetXY(188,15);
                $this->fpdf->Cell(10,4,utf8_decode("1"));
                $this->fpdf->Rect(148,19,30,5);
                $this->fpdf->SetXY(151,20);
                $this->fpdf->Cell(10,3,utf8_decode("Última Revisión:"));
                $this->fpdf->Rect(178,24,30,5);
                $this->fpdf->SetXY(182,20);
                //$this->fpdf->Cell(10,3,utf8_decode($folio->lastTransition->created_at->format('d-m-Y')));
                $this->fpdf->Cell(10,4,"19/05/2024");
                $this->fpdf->Rect(148,24,30,5);
                $this->fpdf->SetXY(156,25);
                $this->fpdf->Cell(10,3,utf8_decode("Emisión:"));
                $this->fpdf->SetXY(182,25);
                $this->fpdf->Cell(10,4,"19/07/2023");
                //$this->fpdf->Cell(10,3,utf8_decode($created_at));
                //Fila 2
                $this->fpdf->Rect(8,30,27,5);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(8,31);
                $xtype = ($type == 'previo') ? "No. de Previo" : "No. de Folio";
                $this->fpdf->Cell(10,3,utf8_decode($xtype));
                $this->fpdf->Rect(35,30,40,5);
                $this->fpdf->SetXY(38,31);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $xcode = ($type == 'previo') ? $previo_code  : $folio_code;
                $this->fpdf->Cell(10,3,utf8_decode($xcode));
                $this->fpdf->Rect(75,30,28,5);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(78,31);
                $this->fpdf->Cell(10,3,utf8_decode("Clasificación:"));
                $this->fpdf->Rect(103,30,25,5);
                $this->fpdf->SetXY(110,31);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode($classification));
                $this->fpdf->Rect(128,30,50,5);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(134,31);
                $this->fpdf->Cell(10,3,utf8_decode("Fecha de solicitud: "));
                $this->fpdf->Rect(178,30,30,5);
                $this->fpdf->SetXY(182,31);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                if ( $folioSolicitado)
                    $this->fpdf->Cell(10,3,utf8_decode( $folioSolicitado->created_at->format('d-m-Y')));
                //Fila 3
                $this->fpdf->SetFillColor(0,0,0);
                $this->fpdf->Rect(8,35,200,5,'F');
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(88,36);
                $this->fpdf->Cell(10,3,utf8_decode("DATOS DEL PROYECTO"));
                //Fila 4
                $this->fpdf->SetFillColor(0,0,0);
                $this->fpdf->Rect(8,41,200,5,"F");
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(80,42);
                $this->fpdf->Cell(10,3,utf8_decode("DESCRIPCIÓN EN EL APLICATIVO"));

                //Fila 5
                $this->fpdf->SetTextColor(0,0,0);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->SetXY(10,49);
                $buffer = str_replace(array("\r", "\n"), '', $description);
                if (strlen($buffer)>180)
                    $this->fpdf->SetFont('Montserrat-Regular', '', 7);
                $this->fpdf->MultiCell(198,4,utf8_decode($buffer));
                //Fila 6
                $this->fpdf->Rect(8,60,200,5);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(12,61);
                $this->fpdf->Cell(10,3,utf8_decode("Vendedor:"));
                $this->fpdf->SetXY(30,61);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode($owner->name));
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(70,61);
                $this->fpdf->Cell(10,3,utf8_decode("Sucursal:"));
                $this->fpdf->SetXY(90,61);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode($owner->branch));
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(130,61);
                $this->fpdf->Cell(10,3,utf8_decode("Cliente:"));
                $this->fpdf->SetXY(150,61);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode($customer?->company_name));
                //Fila 7
                $this->fpdf->SetFillColor(0,0,0);
                $this->fpdf->Rect(8,65,200,5,"F");
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(40,66);
                $this->fpdf->Cell(10,3,utf8_decode("CARACTERÍSTICAS DEL PRODUCTO"));
                $this->fpdf->SetXY(150,66);
                $this->fpdf->Cell(10,3,utf8_decode("KIT DE TORNILLERÍA"));
                $y=$this->fpdf->GetY();
                $x=$this->fpdf->GetX();
                //Derecha varias filas
                if ($tornillos){
                    $ytornillos=$y+9;
                    $xtornillos=$x-27;
                    $this->fpdf->SetTextColor(0,0,0);
                    $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                    foreach ($tornillos as $row) {
                           $this->fpdf->SetXY($xtornillos,$ytornillos);
                           $this->fpdf->Cell(25,5,utf8_decode($row['quantity'] . " Piezas"),1);
                           $this->fpdf->Cell(50,5,utf8_decode($row['description'] ?? ''),1);
                           $ytornillos=$ytornillos+5;

                    }//Foreach
                }//If tornillos
                //Fila 8
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(10,$y+5);
                $this->fpdf->SetFillColor(191, 191, 191);
                $this->fpdf->SetTextColor(0,0,0);
                $this->fpdf->Rect(8,70,25,5);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->SetXY(12,71);
                $this->fpdf->Cell(10,3,utf8_decode("FAMILIA"));
                $this->fpdf->Rect(33,70,25,5);
                $this->fpdf->SetXY(35,71);
                $this->fpdf->Cell(10,3,utf8_decode("MELAMINA"));
                $this->fpdf->Rect(58,70,25,5);
                $this->fpdf->SetXY(60,71);
                $this->fpdf->Cell(10,3,utf8_decode("CHAPACINTA"));
                $this->fpdf->Rect(83,70,25,5);
                $this->fpdf->SetXY(89,71);
                $this->fpdf->Cell(10,3,utf8_decode("TELA"));
                $this->fpdf->Rect(108,70,25,5);
                $this->fpdf->SetXY(110,71);
                $this->fpdf->Cell(10,3,utf8_decode("ESTRUCTURA"));
                $this->fpdf->Rect(133,70,25,5);
                $this->fpdf->SetXY(135,71);
                $this->fpdf->Cell(10,3,utf8_decode("CANTIDAD"));
                $this->fpdf->Rect(158,70,50,5);
                $this->fpdf->SetXY(170,71);
                $this->fpdf->Cell(10,3,utf8_decode("DESCRIPCIÓN"));
                //Fila 9
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Rect(8,75,25,10);
                $this->fpdf->SetXY(11,78);
                if ($category){
                    if ($category->name!="COMPLEMENTOS Y ACCESORIOS" AND $category->name!="ESTACIONES DE TRABAJO")
                        $this->fpdf->Cell(10,3,utf8_decode($category->name));
                    else{
                        $this->fpdf->SetFont('Montserrat-Regular', '', 6);
                        $this->fpdf->MultiCell(22,3,utf8_decode($category->name),'','C');
                        $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                    }
                 }//If categoria
                $this->fpdf->Rect(33,75,25,10);
                $this->fpdf->SetXY(32,77);
                $this->fpdf->MultiCell(24,3,utf8_decode($melamina_color. " " . $melamina_density . "mm"),'','C');
                //$this->fpdf->MultiCell(24,3,utf8_decode($melamina_color),'','C');
                $this->fpdf->Rect(58,75,25,10);
                $this->fpdf->SetXY(60,77);
                $this->fpdf->MultiCell(20,3,utf8_decode($chapacinta_color),'','C');
                $this->fpdf->Rect(83,75,25,10);
                $this->fpdf->SetXY(85,77);
                $this->fpdf->MultiCell(20,3,utf8_decode($tela_color),'','C');
                $this->fpdf->Rect(108,75,25,10);
                $this->fpdf->SetXY(110,77);
                $this->fpdf->MultiCell(20,3,utf8_decode($structure_color),'','C');
                /*
                $this->fpdf->Rect(133,75,25,10);
                $this->fpdf->SetXY(135,77);
                $this->fpdf->MultiCell(20,3,utf8_decode($quantity),'','C');
                $this->fpdf->Rect(158,75,50,10);
                $this->fpdf->SetXY(170,77);
                $this->fpdf->MultiCell(20,3,utf8_decode(" "),'','C');
                */
                //Fila 10
                $this->fpdf->Rect(8,85,125,10);
                $this->fpdf->SetXY(8,86);
                $this->fpdf->SetFillColor(0, 0, 0);
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(75,5,utf8_decode("DIMENSIONES GENERALES DEL PRODUCTO"),'','','C',TRUE);
                //$this->fpdf->Rect(133,85,25,10);
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                //$this->fpdf->SetXY(135,85);
                //$this->fpdf->MultiCell(20,3,utf8_decode("1"),'','C');
                $this->fpdf->SetTextColor(0,0,0);
                //$this->fpdf->Rect(158,85,50,10);

                $this->fpdf->Rect(8,90,25,5);
                $this->fpdf->SetXY(14,92);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode("ALTO"));
                $this->fpdf->Rect(33,90,25,5);
                $this->fpdf->SetXY(37,92);
                $this->fpdf->Cell(10,3,utf8_decode('ANCHO'));
                $this->fpdf->Rect(58,90,25,5);
                $this->fpdf->SetXY(62,92);
                $this->fpdf->Cell(10,3,utf8_decode('PROFUNDO'));
                $this->fpdf->Rect(83,90,25,5);
                $this->fpdf->SetXY(83,92);
                $this->fpdf->Cell(10,3,utf8_decode('MODELO BASE'));
                $this->fpdf->Rect(108,90,25,5);
                $this->fpdf->SetXY(112,92);
                $this->fpdf->Cell(10,3,utf8_decode('CANTIDAD'));
                //Fila 11
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                $this->fpdf->Rect(8,95,25,10);
                $this->fpdf->SetXY(11,99);
                if ($height){
                    $this->fpdf->Cell(10,3,utf8_decode($height.'cm'));
                }else{
                    $this->fpdf->Cell(10,3,utf8_decode('DE LÍNEA'));
                }

                $this->fpdf->Rect(33,95,25,10);
                $this->fpdf->SetXY(37,99);
                if ($width){
                    $this->fpdf->Cell(10,3,utf8_decode($width.'cm'));
                }else{
                    $this->fpdf->Cell(10,3,utf8_decode('DE LÍNEA'));
                }

                $this->fpdf->Rect(58,95,25,10);
                $this->fpdf->SetXY(62,99);
                if ($depth){
                    $this->fpdf->Cell(10,3,utf8_decode($depth.'cm'));
                }else{
                    $this->fpdf->Cell(10,3,utf8_decode('DE LÍNEA'));

                }
                $this->fpdf->Rect(83,95,25,10);
                $this->fpdf->SetXY(86,99);
                $this->fpdf->Cell(16,3,utf8_decode($sku),'','','C');
                $this->fpdf->Rect(108,95,25,10);
                $this->fpdf->SetXY(112,99);
                $this->fpdf->Cell(16,3,$quantity,'','','C');
                //Fila 12
                //$this->fpdf->Rect(8,100,25,5);
                $this->fpdf->SetXY(8,105);
                $this->fpdf->SetFillColor(0, 0, 0);
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(75,5,utf8_decode("CAMBIOS"),'','','C',TRUE);
                //Fila 13
                $this->fpdf->Rect(8,110,25,5);
                $this->fpdf->SetXY(12,111);
                $this->fpdf->SetTextColor(0,0,0);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode("CUBIERTA"));
                $this->fpdf->Rect(33,110,25,5);
                $this->fpdf->SetXY(35,111);
                $this->fpdf->Cell(10,3,utf8_decode("ESTRUCTURA"));
                $this->fpdf->Rect(58,110,25,5);
                $this->fpdf->SetXY(58,111);
                $this->fpdf->Cell(10,3,utf8_decode("COMPONENTES"));
                $this->fpdf->Rect(83,110,25,5);
                $this->fpdf->SetXY(83,111);
                $this->fpdf->SetFont('Montserrat-Bold', '', 6);
                $this->fpdf->MultiCell(22,2,utf8_decode("COMBINACIÓN DE COLORES"),'','C');
                $this->fpdf->Rect(108,110,25,5);
                $this->fpdf->SetXY(112,111);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(10,3,utf8_decode("NUEVO"));
                //Fila 14
                $this->fpdf->Rect(8,115,25,5);
                $this->fpdf->Rect(33,115,25,5);
                $this->fpdf->Rect(58,115,25,5);
                $this->fpdf->Rect(83,115,25,5);
                $this->fpdf->Rect(108,115,25,5);
                //Fila 15
                $xfile="img1.jpg";
                //$fondo_lupita = storage_path('app/public/productos/' . ($xfile ?? 'default.png'));
                $fondo_lupita = null;

                if ($imagen) {
                    $this->fpdf->SetXY(12,125);
                    $this->fpdf->SetFont('Montserrat-Regular', '', 8);
                    $this->fpdf->Cell(10,3,"Imagen solo de referencia");
                    $fondo_lupita = $imagen->getPath();
                    $this->fpdf->Image($fondo_lupita,20,135,90,55);
                }

                $this->fpdf->SetXY(140,145);
                $this->fpdf->SetFont('Montserrat-Bold', '', 8);
                $this->fpdf->Cell(10,4,utf8_decode("Cantidad: "));
                $this->fpdf->Cell(8);
                $this->fpdf->Cell(10,4,"__________________");
                $this->fpdf->SetXY(140,156);
                $this->fpdf->Cell(10,4,utf8_decode("Puntos: "));
                $this->fpdf->Cell(8);
                $this->fpdf->Cell(10,4,"__________________");
                $this->fpdf->SetXY(140,167);
                $this->fpdf->Cell(10,4,utf8_decode("Fecha: "));
                $this->fpdf->Cell(8);
                $this->fpdf->Cell(10,4,"__________________");
                $this->fpdf->SetXY(140,178);
                $this->fpdf->Cell(10,2,utf8_decode("Fecha de"));
                $this->fpdf->SetXY(140,181);
                $this->fpdf->Cell(10,2,utf8_decode("producción:"));
                $this->fpdf->SetXY(159,180);
                $this->fpdf->Cell(10,4,"__________________");
                $this->fpdf->SetXY(140,200);
                $this->fpdf->Cell(10,2,utf8_decode("Descripción:"));
                $this->fpdf->SetXY(159,200);
                $this->fpdf->Cell(10,4,"__________________");
                $this->fpdf->SetXY(140,210);
                $this->fpdf->Cell(10,4,"________________________________");
                $this->fpdf->SetXY(140,220);
                $this->fpdf->Cell(10,4,"________________________________");
                $this->fpdf->SetXY(8,238);
                $this->fpdf->SetTextColor(255,255,255);
                $this->fpdf->Cell(200,4,utf8_decode("FIRMAS DE CONFORMIDAD PARA FABRICACIÓN DEL PRODUCTO "),'','','C',TRUE);
                $this->fpdf->SetTextColor(0,0,0);
                $this->fpdf->SetFont('Montserrat-Bold', '', 7);
                $this->fpdf->SetXY(20,254);
                $this->fpdf->Cell(12,4,utf8_decode("Ingeniería"));
                $this->fpdf->SetXY(70,254);
                $this->fpdf->Cell(12,4,utf8_decode("Compras"));
                $this->fpdf->SetXY(120,254);
                $this->fpdf->Cell(12,4,utf8_decode("Producción"));
                $this->fpdf->SetXY(180,254);
                $this->fpdf->Cell(12,4,utf8_decode("PGP"));
                $this->fpdf->SetXY(12,250);
                $this->fpdf->Cell(12,4,utf8_decode("_______________________"));
                $this->fpdf->SetXY(62,250);
                $this->fpdf->Cell(12,4,utf8_decode("_______________________"));
                $this->fpdf->SetXY(112,250);
                $this->fpdf->Cell(12,4,utf8_decode("_______________________"));
                $this->fpdf->SetXY(168,250);
                $this->fpdf->Cell(12,4,utf8_decode("_______________________"));


                $this->fpdf->Output();

        //Fin Previos ***********************  Fin de Folios *****************/

        // ********************    Previos  ********* Previos      ****** Previos  ** //
    }else{
        $this->fpdf->AddPage();

        $xfile="logo-31.jpg";
        $fondo_lupita = storage_path('app/public/lineaitalia/' . ($xfile ?? 'default.png'));
        $this->fpdf->Rect(8,14,200,248);
        $this->fpdf->Image($fondo_lupita,7,12,55,0);
        //Fila 1
        $this->fpdf->Rect(8,14,50,15);
        $this->fpdf->Rect(58,14,90,15);
        $this->fpdf->SetFont('Montserrat-Regular', '', 10);
        $this->fpdf->SetXY(80,16);
        $this->fpdf->Cell(80,4,utf8_decode('PRODUCTOS ESPECIALES'));
        $this->fpdf->SetXY(82,21);
        $this->fpdf->Cell(80,4,utf8_decode('TIPO A - (F-ING-08)'));
        $this->fpdf->Rect(148,14,30,5);
        $this->fpdf->SetFont('Montserrat-Regular', '', 8);
        $this->fpdf->SetXY(156,15);
        $this->fpdf->Cell(10,3,utf8_decode("Revisión:"));
        $this->fpdf->Rect(178,14,30,5);
        $this->fpdf->SetXY(188,15);
        $this->fpdf->Cell(10,4,utf8_decode("1"));
        $this->fpdf->Rect(148,19,30,5);
        $this->fpdf->SetXY(151,20);
        $this->fpdf->Cell(10,3,utf8_decode("Última Revisión:"));
        $this->fpdf->Rect(178,24,30,5);
        $this->fpdf->SetXY(182,20);
        //$this->fpdf->Cell(10,3,utf8_decode($folio->lastTransition->created_at->format('d-m-Y')));
        $this->fpdf->Cell(10,4,"19/05/2024");
        $this->fpdf->Rect(148,24,30,5);
        $this->fpdf->SetXY(156,25);
        $this->fpdf->Cell(10,3,utf8_decode("Emisión:"));
        $this->fpdf->SetXY(182,25);
        //$this->fpdf->Cell(10,3,utf8_decode($created_at));
        $this->fpdf->Cell(10,4,"19/07/2023");
        //Fila 2
        $this->fpdf->Rect(8,30,27,5);
        $this->fpdf->SetFont('Montserrat-Bold', '', 8);
        $this->fpdf->SetXY(8,31);
        $xtype = ($type == 'previo') ? "No. de Previo" : "No. de Folio";
        $this->fpdf->Cell(10,3,utf8_decode($xtype));
        $this->fpdf->Rect(35,30,40,5);
        $this->fpdf->SetXY(38,31);
        $this->fpdf->SetFont('Montserrat-Regular', '', 8);
        $xcode = ($type == 'previo') ? $previo_code  : $folio_code;
        $this->fpdf->Cell(10,3,utf8_decode($xcode));
        $this->fpdf->Rect(75,30,28,5);
        $this->fpdf->SetFont('Montserrat-Bold', '', 8);
        $this->fpdf->SetXY(78,31);
        $this->fpdf->Cell(10,3,utf8_decode("Clasificación:"));
        $this->fpdf->Rect(103,30,25,5);
        $this->fpdf->SetXY(110,31);
        $this->fpdf->SetFont('Montserrat-Regular', '', 8);
        $this->fpdf->Cell(10,3,utf8_decode($classification));
        $this->fpdf->Rect(128,30,50,5);
        $this->fpdf->SetFont('Montserrat-Bold', '', 8);
        $this->fpdf->SetXY(134,31);
        $this->fpdf->Cell(10,3,utf8_decode("Fecha de solicitud: "));
        $this->fpdf->Rect(178,30,30,5);
        $this->fpdf->SetXY(182,31);
        $this->fpdf->SetFont('Montserrat-Regular', '', 8);
        if ($previoAprobado)
                    $this->fpdf->Cell(10,3,utf8_decode($previoAprobado->created_at->format('d-m-Y')));
         //Fila 3
         $this->fpdf->SetFillColor(0,0,0);
         $this->fpdf->Rect(8,35,200,5,'F');
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(88,36);
         $this->fpdf->Cell(10,3,utf8_decode("DATOS DEL PROYECTO"));
         //Fila 4
         $this->fpdf->SetFillColor(0,0,0);
         $this->fpdf->Rect(8,42,200,5,'F');
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(20,43);
         $this->fpdf->Cell(10,3,utf8_decode("VENDEDOR"));
         $this->fpdf->Cell(47);
         $this->fpdf->Cell(10,3,utf8_decode("SUCURSAL"));
         $this->fpdf->Cell(30);
         $this->fpdf->Cell(10,3,utf8_decode("CANAL"));
         $this->fpdf->Cell(40);
         $this->fpdf->Cell(10,3,utf8_decode("CLIENTE"));
         $this->fpdf->ln(4);
         $this->fpdf->SetFont('Montserrat-Regular', '', 8);
         $this->fpdf->SetTextColor(0,0,0);
         $this->fpdf->Cell(60,6,utf8_decode($owner->name),'R'); //40,6
         $this->fpdf->Cell(10);
         $this->fpdf->Cell(20,6,utf8_decode($owner->branch),'R'); //40,6
         $this->fpdf->Cell(10);
         $this->fpdf->Cell(30,6,utf8_decode($channel),'R');
         $this->fpdf->Cell(2);
         if (strlen($customer?->company_name)<40)
            $this->fpdf->Cell(40,6,utf8_decode($customer?->company_name));
         else{
            $this->fpdf->SetFont('Montserrat-Regular', '', 6);
            $this->fpdf->Cell(40,6,utf8_decode($customer?->company_name));
         }
         //Fila 5
         $this->fpdf->SetFillColor(0,0,0);
         $this->fpdf->Rect(8,53,200,5,"F");
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(80,54);
         $this->fpdf->Cell(10,3,utf8_decode("DESCRIPCIÓN EN EL APLICATIVO"));

         //Fila 6
         $this->fpdf->SetTextColor(0,0,0);
         $this->fpdf->SetFont('Montserrat-Regular', '', 8);
         $this->fpdf->SetXY(10,59);
         //$buffer = str_replace(array("\r", "\n"), '', $buffer);
         $buffer = str_replace(array("\r", "\n"), '', $description);
         if (strlen($buffer)>180)
            $this->fpdf->SetFont('Montserrat-Regular', '', 7);
         $this->fpdf->MultiCell(198,4,utf8_decode($buffer));

         //Fila 7
         $this->fpdf->SetFillColor(0,0,0);
         $this->fpdf->Rect(8,67,200,5,"F");
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(40,68);
         $this->fpdf->Cell(0,3,utf8_decode("CARACTERÍSTICAS DEL PRODUCTO"),'','','C');
         $this->fpdf->SetXY(150,68);

         //Fila 8
         $this->fpdf->SetFillColor(191, 191, 191);
         $this->fpdf->SetTextColor(0,0,0);
         $this->fpdf->Rect(8,72,28,5);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(12,73);
         $this->fpdf->Cell(10,3,utf8_decode("FAMILIA"));
         $this->fpdf->Rect(36,72,28,5);
         $this->fpdf->SetXY(39,73);
         $this->fpdf->Cell(10,3,utf8_decode("MELAMINA"));
         $this->fpdf->Rect(64,72,28,5);
         $this->fpdf->SetXY(68,73);
         $this->fpdf->Cell(10,3,utf8_decode("ESPESOR"));
         $this->fpdf->Rect(92,72,28,5);
         $this->fpdf->SetXY(95,73);
         $this->fpdf->Cell(10,3,utf8_decode("CHAPACINTA"));
         $this->fpdf->Rect(120,72,28,5);
         $this->fpdf->SetXY(126,73);
         $this->fpdf->Cell(10,3,utf8_decode("TELA"));
         $this->fpdf->Rect(148,72,30,5);
         $this->fpdf->SetXY(151,73);
         $this->fpdf->Cell(10,3,utf8_decode("ESTRUCTURA"));
         $this->fpdf->Rect(178,72,30,5);
         $this->fpdf->SetXY(182,73);
         $this->fpdf->Cell(10,3,utf8_decode("EMPAQUE"));
         //Fila 9
         $this->fpdf->SetFont('Montserrat-Regular', '', 8);
         $this->fpdf->Rect(8,77,28,10);
         $this->fpdf->SetXY(11,80);
         if ($category){
            if ($category->name!="COMPLEMENTOS Y ACCESORIOS" AND $category->name!="ESTACIONES DE TRABAJO")
                $this->fpdf->Cell(10,3,utf8_decode($category->name));
            else{
                $this->fpdf->SetFont('Montserrat-Regular', '', 6);
                $this->fpdf->MultiCell(22,3,utf8_decode($category->name),'','C');
                $this->fpdf->SetFont('Montserrat-Regular', '', 8);
            }
         }//If categoria
         $this->fpdf->Rect(36,77,28,10);
         $this->fpdf->SetXY(34,79);
         $this->fpdf->MultiCell(24,3,utf8_decode($melamina_color. " " . $melamina_density . "mm"),'','C');
         //$this->fpdf->MultiCell(24,3,utf8_decode($melamina_color),'','C');
         $this->fpdf->Rect(64,77,28,10);
         $this->fpdf->SetXY(60,79);
         $this->fpdf->MultiCell(20,3,utf8_decode($espesor),'','C');
         $this->fpdf->Rect(92,77,28,10);
         $this->fpdf->SetXY(94,79);
         $this->fpdf->MultiCell(20,3,utf8_decode($chapacinta_color),'','C');
         $this->fpdf->Rect(120,77,28,10);
         $this->fpdf->SetXY(123,79);
         $this->fpdf->MultiCell(20,3,utf8_decode($tela_color),'','C');
         $this->fpdf->Rect(148,77,30,10);
         $this->fpdf->SetXY(150,79);
         $this->fpdf->MultiCell(20,3,utf8_decode($structure_color),'','C');
         $this->fpdf->Rect(178,77,30,10);
         $this->fpdf->SetXY(180,79);
         $this->fpdf->MultiCell(20,3,utf8_decode($empaque),'','C');

         //Fila 10
         $this->fpdf->SetFillColor(0,0,0);
         $this->fpdf->Rect(100,90,108,5,"F");
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(110,91);
         $this->fpdf->Cell(0,3,utf8_decode("COSTOS DE FABRICACIÓN"),'','','C');
         $this->fpdf->SetXY(150,68);
         //Fila 11
         $this->fpdf->Rect(8,95,200,5,"F");
         $this->fpdf->SetTextColor(255,255,255);
         $this->fpdf->SetFont('Montserrat-Bold', '', 8);
         $this->fpdf->SetXY(14,96);
         $this->fpdf->Cell(25,3,utf8_decode("CANTIDAD"));
         $this->fpdf->Cell(20);
         $this->fpdf->Cell(25,3,utf8_decode("MODELO BASE"));
         $this->fpdf->Cell(35);
         $this->fpdf->Cell(25,3,utf8_decode("MODELO BASE"));
         $this->fpdf->Cell(30);
         $this->fpdf->Cell(25,3,utf8_decode("PREVIO"));
         //Fila 12
         $this->fpdf->SetFont('Montserrat-Regular', '', 8);
         $this->fpdf->SetTextColor(0,0,0);
         $this->fpdf->Rect(8,95,32,15);
         $this->fpdf->SetXY(10,103);
         $this->fpdf->MultiCell(24,3,utf8_decode($quantity),'','C');
         $this->fpdf->Rect(40,95,61,15);
         $this->fpdf->SetXY(42,103);
         $this->fpdf->MultiCell(50,3,utf8_decode($sku),'','C');
         $this->fpdf->Rect(101,95,61,15);
         $this->fpdf->SetXY(104,103);
         $this->fpdf->MultiCell(50,3,utf8_decode("$ ".$list_cost ),'','C');
         $this->fpdf->Rect(162,95,46,15);
         $this->fpdf->SetXY(164,103);
         $this->fpdf->MultiCell(36,3,utf8_decode("$ ".$cost ),'','C');
        //Fila 13
         if ($imagen) {
            $fondo_lupita = $imagen->getPath();
            $this->fpdf->Image($fondo_lupita,20,135,90,55);
        }

        //Fila 14
        $this->fpdf->Line(75,214,142,214);
        $this->fpdf->SetXY(80,216);
        $this->fpdf->SetFont('Montserrat-Bold', '', 11);
        $this->fpdf->Cell(0,4,utf8_decode("Ing. Rene Orlando  Esparza "));


        $this->fpdf->Output();
    }
        //**********************Fin de Previos ****** */

        exit;
    }

    public function verData(Request $request, Folio $folio)
    {
        //dd($folio);
        var_dump($folio);
        $data_json=json_decode($folio, true);
        $id = $data_json['id'];
        $melamina_color = $data_json['melamina_color'];
        $melamina_density = $data_json['melamina_density'];
        $project_id = $data_json['project_id'];
        $assembly_number = $data_json['assembly_number'];
        $previo_code = $data_json['previo_code'];
        $folio_code = $data_json['folio_code'];
        $quantity = $data_json['quantity'];
        $height = $data_json['height'];
        $width = $data_json['width'];
        $depth = $data_json['depth'];
        $chapacinta_color = $data_json['chapacinta_color'];
        $structure_color = $data_json['structure_color'];
        $tela_color = $data_json['tela_color'];
        $description = $data_json['description'];
        $created_at =$data_json['created_at'];
        $created_at = date("d-m-Y", strtotime($data_json['created_at']));
        echo "<br> " . $created_at;

    }

}
