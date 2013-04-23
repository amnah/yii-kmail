<?php

// require base class
// check if this file is outside the extension folder
if (file_exists(dirname(__FILE__) . '/kmail/KMailBase.php')) {
    require_once dirname(__FILE__) . '/kmail/KMailBase.php';
}
// check if this file is within the extension folder
elseif (file_exists(dirname(__FILE__) . '/KMailBase.php')) {
    require_once dirname(__FILE__) . '/KMailBase.php';
}


class KMail extends KmailBase {

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
     * Send test message
     * @param string $to
     * @param string $messageSubject
     * @param string $messageBody
     */
    public function sendTestEmail($to, $messageSubject, $messageBody) {
        $message = $this->getMessageTemplate();
        $message->setSubject($messageSubject);
        $message->setBody($messageBody);
        $message->setTo($to);
        $this->send($message);
    }

    /**
     * Example for using view template
     * @param User $user
     * @param User_profile $profile
     */
    public function sendEmailActivationKey($user, $profile) {

        // note that this uses the view based on $this->viewPath
        $body = $this->renderView("activation", array(
            "user"=>$user,
            "profile"=>$profile
        ));

        $message = $this->getMessageTemplate();
        $message->setSubject('Email Activation');
        $message->setBody($body);
        $message->setTo($user->email);
        $this->send($message);
    }
}
