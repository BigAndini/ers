<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ErsBase\Service;

use ErsBase\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\View\Model\ViewModel;
use DOMPDFModule\View\Model\PdfModel;

/**
 * E-Ticket Serivce
 */
class ETicketService
{
    protected $_sl;
    protected $_language = 'en';
    protected $_products;
    protected $_package;
    protected $_participant;
    protected $_agegroup;
    protected $_personalItems;
    protected $_Items;
    
    public function __construct() {
        $this->_agegroup = null;
        $this->_Items = new ArrayCollection();
        $this->_personalItems = new ArrayCollection();
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
     * set Language
     * 
     * @param string $language
     */
    public function setLanguage($lang) {
        $this->_language = $lang;
    }
    
    /**
     * get Language
     * 
     * @return string
     */
    protected function getLanguage() {
        return $this->_language;
    }
    
    /**
     * set Products
     * 
     * @param string $products
     */
    public function setProducts($products) {
        $this->_products = $products;
    }
    
    /**
     * get Products
     * 
     * @return string
     */
    protected function getProducts() {
        return $this->_products;
    }
    
    /**
     * set Package
     * even if a participant can have multiple packages this service will only 
     * process one of them. So for multiple packages the participant will get 
     * multiple E-Tickets.
     * 
     * @param \ErsBase\Entity\Package $package
     */
    public function setPackage(Entity\Package $package) {
        $this->_package = $package;
        $this->setParticipant($package->getParticipant());
        
        foreach($package->getItems() as $item) {
            if($item->getProduct()->getPersonalized()) {
                $this->addPersonalItem($item);
            } else {
                $this->addItem($item);
            }
        }
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
     * @param \ErsBase\Entity\User $participant
     */
    public function setParticipant(Entity\User $participant) {
        $this->_participant = $participant;
        
        $agegroupService = $this->getServiceLocator()
                ->get('ErsBase\Service\AgegroupService:ticket');
        $agegroup = $agegroupService->getAgegroupByUser($participant);
        if($agegroup) {
            $this->_agegroup = $agegroup;
        } else {
            $this->_agegroup = null;
        }
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
     * @param \ErsBase\Entity\Agegroup $agegroup
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
    
    /**
     * Add Item entity to collection.
     *
     * @param \Entity\Item $item
     * @return \Entity\Order
     */
    public function addItem(Entity\Item $item)
    {
        $this->_Items[] = $item;

        return $this;
    }

    /**
     * Remove Item entity from collection.
     *
     * @param \Entity\Item $item
     * @return \Entity\Order
     */
    public function removeItem(Entity\Item $item)
    {
        $this->_Items->removeElement($item);

        return $this;
    }

    /**
     * Get Item entity collection.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->_Items;
    }

    /**
     * Add personal Item entity to collection.
     *
     * @param Entity\Item $item
     * @return Service\ETicketService
     */
    public function addPersonalItem(Entity\Item $item)
    {
        $this->_personalItems[] = $item;

        return $this;
    }

    /**
     * Remove personal Item entity from collection.
     *
     * @param Entity\Item $item
     * @return Service\ETicketService
     */
    public function removePersonalItem(Entity\Item $item)
    {
        $this->_personalItems->removeElement($item);

        return $this;
    }

    /**
     * Get personal Item entity collection.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPersonalItems()
    {
        return $this->_personalItems;
    }
    
    public function generatePdf() {
        $config = $this->getServiceLocator()->get('Config');
        
        if(!extension_loaded('gd')) {
            throw new \Exception('PHP Extension gd needs to be loaded.');
        }
        
        /*
         * PDF creation
         */
        $paperSize = 'a4';
        $paperOrientation = 'portrait';
        $pdf = new PdfModel();
        $pdf->setOption("paperSize", $paperSize); //Defaults to 8x11
        $pdf->setOption("paperOrientation", $paperOrientation); //Defaults to portrait
        
        $name = $this->getParticipant()->getFirstname().' '.$this->getParticipant()->getSurname();
        $code = $this->getPackage()->getCode()->getValue();

        /*
         * QR-Code creation
         */
        $qr = $this->getServiceLocator()->get('QRCode');
        $qr->isHttps(); // or $qr->isHttp();
        #$qr->setData('http://prereg.eja.net/onsite/register/'.  \urlencode($code));
        #$onsitereg = $config['ERS']['onsitereg'];
        $settingService = $this->getServiceLocator()
                ->get('ErsBase\Service\SettingService');
        $onsitereg = $settingService->get('ers.onsitereg');
        # ensure the url has no trailing slash
        \rtrim( $onsitereg, '/\\' );
        $qr->setData($onsitereg.'/'.\urlencode($code));
        
        $qr->setCorrectionLevel('H', 0);
        $qrWidth = 200;
        $qrHeight = 200;
        $qr->setDimensions($qrWidth, $qrHeight);
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
        
        # image overlay
        $qrImage = imagecreatefromstring($qr_content);
        
        if($this->getAgegroup()) {
            $text = $this->getAgegroup()->getName();
        } else {
            $text = 'adult';
        }
        
        $font_size = 20; // Font size is in pixels.
        $font_file = 'pdf/fonts/berlin_sans_fb_demi_bold.ttf'; // This is the path to your font file.

        // Retrieve bounding box:
        $type_space = imagettfbbox($font_size, 0, $font_file, $text);

        // Determine image width and height, 10 pixels are added for 5 pixels padding:
        $image_width = abs($type_space[4] - $type_space[0]) + 10;
        $image_height = abs($type_space[5] - $type_space[1]) + 10;

        // Create image:
        $textImage = imagecreatetruecolor($image_width, $image_height+1);

        // Allocate text and background colors (RGB format):
        $settingService = $this->getServiceLocator()
                ->get('ErsBase\Service\SettingService');
        $primaryColor = $settingService->get('pdf.primary-color');
        if($primaryColor == '' || substr($primaryColor,1,1) != '#') {
            $primaryColor = '#00D4F4';
        }
        list($r, $g, $b) = sscanf($primaryColor, "#%02x%02x%02x");
        $text_color = imagecolorallocate($textImage,$r,$g,$b);
        $bg_color = imagecolorallocate($textImage, 255, 255, 255);

        // Fill image:
        imagefill($textImage, 0, 0, $bg_color);

        // Fix starting x and y coordinates for the text:
        $x = 5; // Padding of 5 pixels.
        $y = $image_height - 5; // So that the text is vertically centered.

        // Add TrueType text to image:
        imagettftext($textImage, $font_size, 0, $x, $y, $text_color, $font_file, $text);

        /*
         * (200-30)/2 = 85
         * (200-70)/2 = 65
         */
        
        $dst_x = ($qrWidth-$image_width)/2;
        $dst_y = ($qrHeight-$image_height)/2;
        imagecopy($qrImage, $textImage, $dst_x, $dst_y, 0, 0, $image_width, $image_height);
        
        ob_start();
        imagepng($qrImage);
        $qrPng = ob_get_clean();
        
        #$base64_qrcode = "data:image/png;base64,".  \base64_encode($qr_content);
        $base64_qrcode = "data:image/png;base64,".  \base64_encode($qrPng);
        
        #file_put_contents(getcwd().'/public/img/qrcode.png', $qr_content);
        
        /*
         * Barcode creation
         */
        
        // Only the text to draw is required
        $barcodeOptions = array(
            'text' => $code, 
            'barHeight' => 40,
            'factor' => 1.1,
            'drawText' => false,
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
         * prepare items
         */
        $items = array();
        $personalized = false;
        foreach($this->getPackage()->getItems() as $item) {
            if($item->getStatus() == 'transferred') {
                continue;
            }
            $items[$item->getProductId()][] = $item;
            if($item->getPersonalized()) {
                $personalized = true;
            }
        }

        /* 
         * generate PDF
         */
        $pdfView = new ViewModel();
        #$pdfView->setTemplate('pdf/eticket_'.$this->getLanguage());
        $pdfView->setTemplate('pdf/eticket');
        $pdfView->setVariables(array(
            'name' => $name,
            'package' => $this->getPackage(),
            'items' => $items,
            'personalized' => $personalized,
            'products' => $this->getProducts(),
            'agegroup' => $this->getAgegroup(),
            'code' => $code,
            'qrcode' => $base64_qrcode,
            'barcode' => $base64_barcode,
        ));
        $pdfRenderer = $this->getServiceLocator()
                ->get('ViewPdfRenderer');
        $html = $pdfRenderer->getHtmlRenderer()->render($pdfView);
        $pdfEngine = $pdfRenderer->getEngine();
        
        $pdfEngine->set_paper($paperSize, $paperOrientation);
        $pdfEngine->set_base_path(getcwd().'/');
        $pdfEngine->load_html($html);
        $pdfEngine->render();
        $pdfContent = $pdfEngine->output();
        
        #$filename = $config['ERS']['name_short']."_E-Ticket_".$this->getPackage()->getCode()->getValue().'_'.$this->getLanguage();
        #$filename = $config['ERS']['name_short']."_E-Ticket_".$this->getPackage()->getCode()->getValue();
        $filename = $settingService->get('ers.name_short')."_E-Ticket_".$this->getPackage()->getCode()->getValue();
        
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
