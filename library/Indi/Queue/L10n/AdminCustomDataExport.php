<?php
class Indi_Queue_L10n_AdminCustomDataExport extends Indi_Queue_L10n_AdminCustomData {

    /**
     * Fraction dir
     */
    public $fractionDir = '';

    /**
     * Type
     *
     * @var
     */
    public $type = 'data';

    /**
     * Use trait
     */
    use Indi_Queue_L10n_AdminExport;
}
