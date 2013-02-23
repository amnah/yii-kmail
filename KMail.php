<?php

/**
 * Yii email factory extension using the swiftmailer library
 *
 * @author amnah
 * @link https://github.com/amnah/yii-kmail
 * @link http://swiftmailer.org/
 */
class KMail extends CApplicationComponent{

    /**
     * Transport for mailing
     * @var string
     */
    public $transportType = "php";

    /**
     * Transport options, for swift mailer transport
     * @var array
     */
    public $transportOptions = array();

    /**
     * View path for rendering content
     */
    public $viewPath = "application.views.kmail";

    /**
     * Enable/disable sending actual emails
     * @var bool
     */
    public $dryRun = false;

    /**
     * Swift mailer object
     * @var Swift_Mailer
     */
    protected $_mailer;

    /**
     * Registers Swift Mailer's autoloaders
     * Create swift transport and swift mailer object
     */
    public function init() {
        // register swift's autoloader
//        require_once dirname(__FILE__) . '/swift/lib/swift_required.php';
        require_once dirname(__FILE__) . '/swift/lib/classes/Swift.php';
        Yii::registerAutoloader(array('Swift','autoload'));
        require_once dirname(__FILE__). '/swift/lib/swift_init.php';

        // set transport to php
        if (strtolower($this->transportType) == "php") {
            $transport = Swift_MailTransport::newInstance();
        }
        // set transport to smtp
        elseif (strtolower($this->transportType) == "smtp") {
            $transport = Swift_SmtpTransport::newInstance();

            // set options by calling method calls, ie setHost()
            foreach ($this->transportOptions as $option => $value) {
                $methodName = "set" . ucfirst($option);
                $transport->$methodName($value);
            }
        }

        // set swift mailer object
        $this->_mailer = Swift_Mailer::newInstance($transport);
    }

    /**
     * Gets the headers for the mail message
     * @param Swift_Message $message
     * @param bool $returnString
     * @return mixed string or array, depending on return type
     */
    protected function get_headers($message, $returnString = true) {

        // get headers in string
//        $headers_string = $message->toString(); // should use this, but the one below has better ordering
        $headersString = implode('', $message->getHeaders()->getAll());
        if ($returnString) {
            return $headersString;
        }

        // split up headers based on newline
        // at this point, still indexed by numbers
        $headersExplode = explode("\r\n", $headersString);

        // move type into key
        $headersArray = array();
        foreach ($headersExplode as $header) {
            // skip if empty
            if (!$header) {
                continue;
            }
            // split based on first colon
            list ($key, $data) = explode(":", $header);

            // add to return array
            $headersArray[$key] = $data;
        }

        return $headersArray;
    }

    /**
     * Counts the the total number of recipients of the email message
     * @param Swift_Message $message
     * @return int
     */
    protected function countMessageTotal($message) {
        $totals = array(
            $message->getTo() ? count($message->getTo()) : 0,
            $message->getCc() ? count($message->getCc()) : 0,
            $message->getBcc() ? count($message->getBcc()) : 0,
        );
        return array_sum($totals);
    }

    /**
     * Helper to send a message normally or in batch (aka, sending individual emails to each user)
     * Also, potentially handle failures
     * @param Swift_Mime_Message $message
     * @param bool $sendBatch
     * @return int
     */
    protected function send($message, $sendBatch = false) {

        // check for dry run mode (don't send any actual mail)
        if ($this->dryRun) {
            // log message and return count
            $this->logMessage($message, $sendBatch);
            return $this->countMessageTotal($message);
        }

        // keep track of emails send and failures
        $num_sent = 0;
        $failures = array();

        // send email normally
        if (!$sendBatch) {
            $num_sent = $this->_mailer->send($message, $failures);
        }
        // send in batch
        else {
            // iterate through the to
            $to = $message->getTo();
            foreach ($to as $address => $name) {
                // process email or email/name
                $message->setTo(is_int($address) ? $name : array($address => $name));

                // send the individual emails
                $num_sent += $this->_mailer->send($message, $failures);
            }
        }

        // process failures, log message, and return count
        if ($failures) {
            $this->processFailures($failures);
        }
        $this->logMessage($message, $sendBatch, $failures);
        return $num_sent;
    }

    /**
     * Process failures
     *      $failures = Array (
     *          0 => receiver@bad-domain.org,
     *          1 => other-receiver@bad-domain.org,
     *      )
     * @param $failures
     */
    protected function processFailures($failures) {
        $numFailures = count($failures);
        $addressText = $numFailures == 1 ? "address" : "addresses";
        Yii::app()->user->setFlash("error", "Error: Could not send email to $numFailures $addressText.");
    }

    /**
     * Logs message text
     * @param Swift_Mime_Message $message
     * @param bool $sendBatch
     * @param array $failures
     * @return string
     */
    protected function logMessage($message, $sendBatch, $failures = array()) {
        // count addresses
        $totalCount = $this->countMessageTotal($message);
        $failuresCount = count($failures);
        $successCount = $totalCount - $failuresCount;

        // add in dryRun and batch info first
        $logText  = $this->dryRun ? "(Dry run) " : "";
        $logText .= $sendBatch ? "(Batch mode) " : "";

        // log the text
        $logText .= "KMail [ Total $totalCount ] [ Success: $successCount ] [ Fail: $failuresCount ] \r\n";
        $logText .= "-----------------------------------------\r\n";
        $logText .= $this->get_headers($message)."\r\n";
        $logText .= $message->getBody();

        Yii::log($logText, CLogger::LEVEL_INFO, get_called_class());

        return $logText;
    }

    /**
     * Renders a view file for an email
     * Example:
     *      $message->setBody($this->renderView("activation", array("user"=>$user, "profile"=>$profile)));
     *
     * @param $view
     * @param array $data
     * @return string
     */
    protected function renderView($view, $data = array()) {

        // render partial for normal operation
        if(isset(Yii::app()->controller)) {
            return Yii::app()->controller->renderPartial("{$this->viewPath}.{$view}", $data, true);
        }
        else {
            // taken from YiiMail, thanks!

            // if Yii::app()->controller doesn't exist create a dummy
            // controller to render the view (needed in the console app)

            // renderPartial won't work with CConsoleApplication, so use
            // renderInternal - this requires that we use an actual path to the
            // view rather than the usual alias
            $actualPath = Yii::getPathOfAlias("{$this->viewPath}.{$view}") . ".php";
            $controller = new CController(get_called_class());
            return $controller->renderInternal($actualPath, $data, true);
        }
    }
}