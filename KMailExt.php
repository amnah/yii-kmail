<?php

// require base class
require_once dirname(__FILE__) . '/KMail.php';

class KMailExt extends Kmail {

    /**
     * Gets template of a swift mailer message with default settings
     * @return Swift_Mime_Message
     */
    public function getMessageTemplate() {
        $message = Swift_Message::newInstance();
        $message->setFrom(array(Yii::app()->params["adminEmail"] => "admin"));
        $message->setContentType("text/html");
        $message->setCharset("utf-8");
        return $message;
    }

    /**
     * Sends activation email to user after registration
     * @param $user
     * @param $profile
     */
    public function sendEmailActivationKey($user, $profile) {
        $message = $this->getMessageTemplate();
        $message->setSubject('Registration Activation');
        $message->setBody($this->renderView("activation", array("user"=>$user, "profile"=>$profile)));
        $message->setTo($user->email);
        $this->send($message);
    }
}