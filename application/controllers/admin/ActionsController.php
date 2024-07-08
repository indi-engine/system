<?php
class Admin_ActionsController extends Indi_Controller_Admin_Exportable {

    /**
     * Show popup with records to be added to DNS for the LETS_ENCRYPT_DOMAIN
     * in order to ensure outgoing emails deliverability
     */
    public function dnsAction() {

        // Prepare external IP address and DKIM data
        $addr = $this->exec('wget -qO- http://ipecho.net/plain');
        $host = getenv('LETS_ENCRYPT_DOMAIN');
        $dkim = $this->exec("cat /etc/opendkim/keys/$host/mail.txt");
        $dkim = Indi::rexm('~\((.+)\)~', $dkim, 1);
        $dkim = str_replace('"', '', $dkim);
        $dkim = wrap($dkim, '<span style="word-wrap: break-word; white-space: pre-line;">');

        // Show popup
        $this->popup([
            'title' => "Records to be added to DNS for $host domain - for outgoing emails deliverability",
            'icon' => false,
            'buttons' => false,
            'items' => [
                'xtype' => 'grid',
                'width' => 600,
                'store' => [
                    'data' => [
                        [ 'type' => 'MX',  'name' => '@'              , 'data' => 'blackhole.io' ],
                        [ 'type' => 'TXT', 'name' => '@'              , 'data' => "v=spf1 a mx ip$addr ~all" ],
                        [ 'type' => 'TXT', 'name' => '_dmarc'         , 'data' => 'v=DMARC1; p=none' ],
                        [ 'type' => 'TXT', 'name' => 'mail._domainkey', 'data' => $dkim ],
                    ]
                ],
                'columns' => [
                    [ 'text' => 'Type', 'dataIndex' => 'type', 'width' => 40 ],
                    [ 'text' => 'Name', 'dataIndex' => 'name', 'width' => 100 ],
                    [ 'text' => 'Data', 'dataIndex' => 'data', 'flex' => 1],
                ],
                'viewConfig' => [
                    'enableTextSelection' => true,
                ]
            ],
        ]);
    }
}