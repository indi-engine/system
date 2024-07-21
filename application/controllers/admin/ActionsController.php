<?php
class Admin_ActionsController extends Indi_Controller_Admin_Exportable {

    /**
     * Show popup with records to be added to DNS for the LETS_ENCRYPT_DOMAIN
     * in order to ensure outgoing emails deliverability
     */
    public function dnsAction() {

        // Get domains
        $hostA = explode(' ', trim(getenv('EMAIL_SENDER_DOMAIN') ?: getenv('LETS_ENCRYPT_DOMAIN')));

        // If no domains - flush failure
        if (count($hostA) === 0) jflush(false, 'EMAIL_SENDER_DOMAIN is empty');

        // If no prompt was submitted so far
        if (!isset(Indi::get()->answer)) {

            // Prepare external IP address and DKIM data
            $addr = $this->exec('wget -qO- http://ipecho.net/plain');

            // Prepare DNS records for each domain
            $items = [];
            foreach ($hostA as $host) {

                // Prepare dkim
                $dkim = $this->exec("cat /etc/opendkim/keys/$host/mail.txt");
                $dkim = Indi::rexm('~\((.+)\)~', $dkim, 1);
                $dkim = str_replace(['"', '<br>', PHP_EOL, "\t  "], '', $dkim);
                $dkim = str_replace(PHP_EOL, '', $dkim);
                $dkim = wrap($dkim, '<span style="word-wrap: break-word; white-space: pre-line;">');

                // Prepare tab data
                $items []= [
                    'xtype' => 'grid',
                    'title' => $host,
                    'width' => 600,
                    'store' => [
                        'data' => [
                            [ 'type' => 'MX',  'name' => '@'              , 'data' => 'blackhole.io' ],
                            [ 'type' => 'TXT', 'name' => '@'              , 'data' => "v=spf1 a mx ip4:$addr ~all" ],
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
                ];
            }
        }

        // Show popup
        $prompt = $this->prompt([
            'title' => "Records to be added to DNS for outgoing emails deliverability",
            'icon' => false,
            'items' => [
                'controller' => 'actions',
                'xtype' => 'tabpanel',
                'items' => $items ?? [],
                'dockedItems' => [[
                    'dock' => 'bottom',
                    'defaults' => [
                        'width' => '100%',
                        'margin' => '10 0 0 0'
                    ],
                    'items' => [
                        [
                            'xtype' => 'textfield',
                            'isCustomField' => true,
                            //'readOnly' => true,
                            'disabled' => true,
                            'fieldLabel' => 'From address',
                            'value' => 'info@' . $hostA[0],
                            'name' => 'from',
                            'labelWidth' => 100,
                        ],
                        [
                            'xtype' => 'textfield',
                            'isCustomField' => true,
                            'fieldLabel' => 'Send test email to',
                            'value' => getenv('GIT_COMMIT_EMAIL'),
                            'name' => 'to',
                            'labelWidth' => 100
                        ],
                        [
                            'xtype' => 'textfield',
                            'isCustomField' => true,
                            'fieldLabel' => 'Subject',
                            'value' => 'Test subject',
                            'name' => 'subject',
                            'labelWidth' => 100
                        ],
                        [
                            'xtype' => 'textarea',
                            'isCustomField' => true,
                            'fieldLabel' => 'Message',
                            'value' => 'Test message',
                            'name' => 'message',
                            'labelWidth' => 100,
                            'growMin' => 2,
                            'grow' => true,
                            'growMax' => 100,
                            'margin' => '10 0 1 0'
                        ],
                    ]
                ]],
            ],
        ]);

        // Validate prompt data
        jcheck([
            'from,to' => [
                'req' => true,
                'rex' => 'email'
            ],
            'subject,message' => [
                'req' => true,
                'rex' => 'varchar255'
            ]
        ], $prompt);

        // Prepare mail
        $mailer = Indi::mailer();
        $mailer->From    = $prompt['from'];
        $mailer->Subject = $prompt['subject'];
        $mailer->Body    = $prompt['message'];
        $mailer->AddAddress($prompt['to']);

        // Send mail via PHPMailer
        $mailer->Send()
            ? wflush(true, "Email was sent to {$prompt['to']} so please check if it reached that address")
            : wflush(false, $mailer->ErrorInfo);
    }
}