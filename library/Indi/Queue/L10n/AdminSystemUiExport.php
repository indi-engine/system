<?php
class Indi_Queue_L10n_AdminSystemUiExport extends Indi_Queue_L10n_AdminSystemUi {

    /**
     * Fraction dir
     */
    public $fractionDir = VDR . '/system';

    /**
     * Type
     *
     * @var
     */
    public $type = 'ui';

    /**
     * Use trait
     */
    use Indi_Queue_L10n_AdminExport;
}
