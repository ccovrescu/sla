<?php namespace Tlt\TicketBundle\Model;

use TCPDF;

class ICRPDF extends TCPDF {
    //Page header
    public function Header() {
        // Logo
        $image_file = 'https://www.teletrans.ro/img/logo_old.png';
        $this->Image($image_file, 10, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);

        // Set font
        $this->SetFont('times', '', 8);

        // Page number
        $this->Cell(0, 10, 'Cod: TLT-06.01.03, Ed. august 2003, Rev. 8', 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
}