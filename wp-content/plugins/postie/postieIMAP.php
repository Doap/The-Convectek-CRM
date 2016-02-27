<?php

/**
 * @author Dirk Elmendorf
 * @style Compliant
 * @testframework Compliant
 * @package Postie 
 * @copyright Copyright 2005 Dirk Elmendorf
 */

/**
 * This class handles the details of an IMAP connection
 *
 * @author Dirk Elmendorf
 * @package Postie
 */
class PostieIMAP {

    var $_connected;
    var $_protocol;
    var $_ssl;
    var $_self_cert;
    var $_tls_on;
    var $_connection;
    var $_mailbox;

    function PostieIMAP($protocol = "imap", $ssl_on = false, $self_cert = true) {
        $this->_connected = false;
        $this->_tls_on = false;
        $this->_protocol = strtolower($protocol);
        $this->_ssl = $ssl_on;
        $this->_self_cert = $self_cert;
    }

    /**
     * call this to turn on TLS
     */
    function TLSOn() {
        $this->_tls_on = true;
        DebugEcho($this->_protocol . ": TLS enabled");
    }

    /**
     * call this if you want to verify the cert
     */
    function RealCert() {
        $this->self_cert = false;
    }

    /**
     * Shows if the object is actually connected
     * @return boolean
     */
    function isConnected() {
        return($this->_connected);
    }

    /**
     * Opens a connection to the server
     * @return boolean
     */
    function connect($server, $port, $login, $password) {
        $option = "/service=" . $this->_protocol;

        if ($this->_ssl) {
            $option .= "/ssl";
        }
        if ($this->_tls_on) {
            $option .= "/tls";
        } else {
            $option .= "/notls";
        }
        if ($this->_self_cert) {
            $option .= "/novalidate-cert";
        }
        if (preg_match("/google|gmail/i", $server)) {
            //Fix from Jim Hodgson http://www.jimhodgson.com/2006/07/19/postie/
            DebugEcho($this->_protocol . ": using Google INBOX");
            $this->_mailbox = "{" . $server . ":" . $port . $option . "}INBOX";
        } else {
            $this->_mailbox = "{" . $server . ":" . $port . $option . "}";
        }
        DebugEcho($this->_protocol . ": connection string - {$this->_mailbox}");
        //Exchange connection, but requires PHP 5.3.2
        if (version_compare(phpversion(), '5.3.2', '<')) {
            $this->_connection = imap_open($this->_mailbox, $login, $password);
        } else {
            DebugEcho($this->_protocol . ": disabling GSSAPI");
            $this->_connection = imap_open($this->_mailbox, $login, $password, NULL, 1, array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));
        }

        if ($this->_connection) {
            $this->_connected = true;
            DebugEcho($this->_protocol . ": connected");
        } else {
            EchoError("imap_open failed: " . imap_last_error());
        }
        return $this->_connected;
    }

    /**
     * Returns a count of the number of messages
     * @return integer
     */
    function getNumberOfMessages() {
        $status = imap_status($this->_connection, $this->_mailbox, SA_ALL);
        DebugDump($status);
        if ($status) {
            return max($status->messages, imap_num_msg($this->_connection));
        } else {
            EchoError("Error imap_status did not return a value");
            //DebugDump($this);
            return 0;
        }
    }

    /**
     * Gets the raw email message from the server
     * @return string
     */
    function fetchEmail($index, $ignoreMailState) {

        $header_info = imap_headerinfo($this->_connection, $index);
        //DebugDump($header_info);

        if (true == $ignoreMailState || IsDebugMode() || $header_info->Recent == 'N' || $header_info->Unseen == 'U') {
            $email = imap_fetchheader($this->_connection, $index);
            $email .= imap_body($this->_connection, $index);

            return $email;
        } else {
            return 'already read';
        }
    }

    /**
     * Marks a message for deletion
     */
    function deleteMessage($index) {
        DebugEcho($this->_protocol . ": deleting message $index");
        imap_delete($this->_connection, $index);
    }

    /**
     * Handles purging any files that are marked for deletion
     */
    function expungeMessages() {
        DebugEcho($this->_protocol . ": expunge");
        imap_expunge($this->_connection);
    }

    /**
     * Handles disconnecting from the server
     */
    function disconnect() {
        DebugEcho($this->_protocol . ": closing connection");
        imap_close($this->_connection);
        $this->_connection = false;
    }

    /**
     * @return string
     */
    function error() {
        DebugDump(imap_errors());
        return(imap_last_error());
    }

    /**
     * Handles returning the right kind of object
     * @return PostieIMAP|PostieIMAPSSL|PostimePOP3SSL
     * @static
     */
    static function &Factory($protocol) {
        switch (strtolower($protocol)) {
            case "imap":
                $object = new PostieIMAP();
                break;
            case "imap-ssl":
                $object = new PostieIMAPSSL();
                break;
            case "pop3-ssl":
                $object = new PostiePOP3SSL();
                break;
            default:
                die("$protocol not supported");
        }
        return($object);
    }

}

/**
 * This class handles the details of an IMAP-SSL connection
 *
 * @author Dirk Elmendorf
 * @package Postie
 */
class PostieIMAPSSL Extends PostieIMAP {

    function PostieIMAPSSL($protocol = "imap", $ssl_on = true, $self_cert = true) {
        PostieIMAP::PostieIMAP($protocol, $ssl_on, $self_cert);
    }

}

/**
 * This class handles the details of an POP3-SSL connection
 *
 * @author Dirk Elmendorf
 * @package Postie
 */
class PostiePOP3SSL Extends PostieIMAP {

    function PostiePOP3SSL($protocol = "pop3", $ssl_on = true, $self_cert = true) {
        PostieIMAP::PostieIMAP($protocol, $ssl_on, $self_cert);
    }

}
