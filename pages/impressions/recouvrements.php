<?php
    
    require('session.php');

    require('../fpdf/fpdf.php');
    require('connexion.php');
    
class myPDF extends FPDF {
    function footer(){    
        $this->Sety(-19);
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'Page '.$this->PageNo().' sur {nb}',0,0,'C');
        $this->Ln();
        $this->SetFont('Arial','',10);
         }
    function Table($pdo){
        
       
        $this->SetFont('Arial','B',24);
        $this->cell(270,6,'Action Juwa ASBL',0,0,'C');
        $this->Ln();
        $this-> Image('juwa.jpg',240,10,25,25);
        $this-> Image('juwa.jpg',40,10,25,25);
        $this->SetFont('Times','B',20);
       
        $this->Ln(15);
        $this->cell(270,6,utf8_decode('Liste des recouvrement par agent '),0,0,'C');
        $this->Ln();
     
         //Fin En-tête page 
    }
    //entete du tableau

    function headerTable(){
        $this->SetFont('courier','',11);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(15,7,utf8_decode('N°'),1,0,'C', true);
        
        $this->Cell(80,7,'Client',1,0,'C', true);
        
        $this->Cell(15,7,utf8_decode('Code'),1,0,'C', true);
        $this->Cell(30,7,'Telephone',1,0,'C', true);
        $this->Cell(45,7,utf8_decode('Date'),1,0,'C', true);
        $this->Cell(25,7,utf8_decode('Montant'),1,0,'C', true);
         $this->Cell(25,7,utf8_decode('Dette'),1,0,'C', true);
         $this->Cell(25,7,utf8_decode('Reste'),1,0,'C', true);
        
    }
    function vieTable($pdo){
        $i = 0;
        $sum = 0;
        $idAg = $_GET['id'];
        
        $res=$pdo->query("SELECT *, dettes.montant as dette, recouvrements.montant as rec FROM dettes 
        inner join recouvrements on recouvrements.codeEmprunt = dettes.code_dettes 
        inner join clients on clients.code_client = dettes.client 
        inner join user on user.code_user = recouvrements.idAgent and user.code_user = '$idAg'
        group by code_recouvrement
        ");
        while($data = $res->fetch()){
            $i++;
            $this->SetFont('times','',12);
            $this->SetFillColor(255, 255, 255);

            $this->Cell(15,7,utf8_decode($i),1,0,'C', true);
              $this->Cell(80,7,$data['noms_client'],1,0,'L', true);
            $this->Cell(15,7,utf8_decode($data['code_client']),1,0,'C', true);
          
            $this->Cell(30,7,$data['numero_telephone'],1,0,'L', true);
            $this->Cell(45,7,$data['dateRec'],1,0,'C', true);
            $this->Cell(25,7,$data['rec'],1,0,'C', true);
            $this->Cell(25,7,$data['dette'],1,0,'C', true);
            $idRec = $data['code_recouvrement'];
            
            $resSum =  $pdo->query("SELECT sum(montant) as sum FROM recouvrements where code_recouvrement = '$idRec' ");
            $dataSum = $resSum->fetch();
            
            $this->Cell(25,7,$data['dette'] - $dataSum['sum'],1,0,'C', true);
            
            $this->Ln();
            $sum += $data['rec'];
            $agent = $data[noms];
        }
        
         $this->Cell(185,7,"RECOUVREMENT TOTAL DE L'AGENT  ",1,0,'C', true);
         $this->SetFont('times','B',14);
         
        $this->Cell(75,7,$sum." FC",1,0,'C', true);
        
        $this->Ln(10);
         $this->Cell(140,7,"",0,0,'L', true);
         $this->Cell(80,7,"Ceci est le registre des operations de l'agent ".$agent,0,0,'L', true);
    }
function Totalite(){ 
$this->Ln(6);
$this->Cell(0,0,utf8_decode("Date d'impression : ").date('d/m/Y'),0,0,'L');
$this->Ln(6);
$this->Cell(0,0,utf8_decode("Heure d'impression : ").date('H:i:s'),0,0,'L');
$this->Ln(6);
$this->SetFillColor(3, 1, 10);
$this->Setx(27);
$this->Cell(230,1,'',1,0,'L', true);
$this->Ln();     
        }
  
}
$pdf = new myPDF();
$pdf->AliasNbPages();
$pdf->AddPage('M','A4',0);
$pdf->Table($pdo);
$pdf->Ln(7);
$pdf->headerTable();
$pdf->Ln(7);
$pdf->vieTable($pdo);
$pdf->Ln(5);
$pdf->Totalite($pdo);
$pdf->Ln(5);
$pdf->Cell(450,5,utf8_decode("Fait à Bukavu le ").date('d/m/Y'),0,0,'C');
$pdf->Ln();
$pdf->Cell(100,10,'',0,0);
$pdf->Cell(250,6,utf8_decode("Nom et Signature"),0,1,'C');
$pdf->Output();