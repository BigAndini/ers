<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PreReg\Service;

use ersEntity\Entity;
use Zend\View\Model\ViewModel;
use DOMPDFModule\View\Model\PdfModel;

/**
 * eTicket Serivce
 */
class ETicketService
{
    protected $_sl;
    protected $_package;
    protected $_participant;
    protected $_agegroup;
    protected $_pItems;
    protected $_Items;
    
    public function __construct() {
    }
    
    /**
     * set ServiceLocator
     * 
     * @param ServiceLocator $sl
     */
    public function setServiceLocator($sl) {
        $this->_sl = $sl;
    }
    
    /**
     * get ServiceLocator
     * 
     * @return ServiceLocator
     */
    protected function getServiceLocator() {
        return $this->_sl;
    }
    
    /**
     * set Package
     * 
     * @param \ersEntity\Entity\Package $package
     */
    public function setPackage(Entity\Package $package) {
        $this->_package = $package;
        $this->setParticipant($package->getParticipant());
    }
    
    /**
     * get Package
     * 
     * @return Entity\Package
     */
    public function getPackage() {
        return $this->_package;
    }
    
    /**
     * set Participant
     * 
     * @param \ersEntity\Entity\User $participant
     */
    public function setParticipant(Entity\User $participant) {
        $this->_participant = $participant;
    }
    
    /**
     * get Participant
     * 
     * @return Entity\User
     */
    public function getParticipant() {
        return $this->_participant;
    }
    
    /**
     * set Agegroup
     * 
     * @param \ersEntity\Entity\Agegroup $agegroup
     */
    public function setAgegroup(Entity\Agegroup $agegroup) {
        $this->_agegroup = $agegroup;
    }
    
    /**
     * get Agegroup
     * 
     * @return Entity\Agegroup
     */
    public function getAgegroup() {
        return $this->_agegroup;
    }

    public function generatePdf() {
        $config = $this->getServiceLocator()->get('Config');
        
        /*switch($type) {
            case 'personalized':
                break;
            case 'unpersonalized':
                break;
            default:
                throw new \Exception('Cannot generate eTicket for type: '.$type);
        }*/
        
        if(!extension_loaded('gd')) {
            throw new \Exception('PHP Extension gd needs to be loaded.');
        }
        
        /*
         * PDF creation
         */
        $pdf = new PdfModel();
        $pdf->setOption("paperSize", "a4"); //Defaults to 8x11
        $pdf->setOption("paperOrientation", "portrait"); //Defaults to portrait
        #$name = 'Andreas Nitsche';
        #$code = 'AABBCCDD';
        $name = $this->getParticipant()->getFirstname().' '.$this->getParticipant()->getSurname();
        $code = $this->getPackage()->getCode()->getValue();

        /*
         * QR-Code creation
         */
        $qr = $this->getServiceLocator()->get('QRCode');
        $qr->isHttps(); // or $qr->isHttp();
        #$qr->setData('http://prereg.eja.net/onsite/register/'.  \urlencode($code));
        $onsitereg = $config['ERS']['onsitereg'];
        # ensure the url has no trailing slash
        \rtrim( $onsitereg, '/\\' );
        $qr->setData($onsitereg.'/'.\urlencode($code));
        
        $qr->setCorrectionLevel('H', 0);
        $qr->setDimensions(200, 200);
        $qr_config = array(
            'adapter'      => 'Zend\Http\Client\Adapter\Socket',
            'ssltransport' => 'tls',
            'sslcapath'    => '/etc/ssl/certs/',
            'sslverifypeer' => false,
        );

        // Instantiate a client object
        $client = new \Zend\Http\Client($qr->getResult(), $qr_config);

        // The following request will be sent over a TLS secure connection.
        $response = $client->send();
        
        $qr_content = $response->getContent();
        $base64_qrcode = "data:image/png;base64,".  \base64_encode($qr_content);
        
        #file_put_contents(getcwd().'/public/img/qrcode.png', $qr_content);
        
        /*
         * Barcode creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array(
            'text' => $code, 
            'barHeight' => 40,
            'factor' => 1.1,
        );

        // No required options
        $rendererOptions = array();

        // Draw the barcode in a new image,
        $imageResource = \Zend\Barcode\Barcode::factory(
            'code39', 'image', $barcodeOptions, $rendererOptions
        )->draw();
        
        ob_start(); //Start output buffer.
            imagejpeg($imageResource); //This will normally output the image, but because of ob_start(), it won't.
            $contents = ob_get_contents(); //Instead, output above is saved to $contents
        ob_end_clean(); //End the output buffer.
        
        #file_put_contents(getcwd().'/public/img/barcode2.jpg', $contents);
        
        $base64_barcode = "data:image/png;base64,".  \base64_encode($contents);
        
        /* 
         * generate PDF
         */
        $pdfView = new ViewModel();
        #$pdfView->setTemplate('pre-reg/test/generatepdf');
        $pdfView->setTemplate('pdf/eticket');
        $pdfView->setVariables(array(
            'name' => $name,
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $pdfRenderer = $this->getServiceLocator()->get('ViewPdfRenderer');
        $html = $pdfRenderer->getHtmlRenderer()->render($pdfView);
        $pdfEngine = $pdfRenderer->getEngine();

        $pdfEngine->load_html($html);
        $pdfEngine->render();
        $pdfContent = $pdfEngine->output();
        
        $filename = $config['ERS']['name_short']."_eTicket_".preg_replace('/\ /', '_', $name);
        
        # TODO: make ticket_path configurable
        $ticket_path = getcwd().'/data/etickets';
        \rtrim( $onsitereg, '/\\' );
        if(!is_dir($ticket_path)) {
            mkdir($ticket_path);
        }
        $filePath = $ticket_path.'/'.$filename.'.pdf';
        file_put_contents($filePath, $pdfContent);
        
        return $filePath;
    }
}
