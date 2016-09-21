<?php

class Application_Model_Library_MailHelper
{
	
    public function sendEmail( $toEmail, $from, $name, $message )
    {
        $mail = new Zend_Mail("UTF-8");

        $mail->setSubject("Poruka sa kontakt forme od " . $name);
        $mail->addTo($toEmail);
        $mail->setFrom($from, $name);
        $mail->setBodyHtml($message);
        $mail->setBodyText($message);
        
        return $result = $mail->send();
    }
    
}