<?php
/**
 * Class Indi_Controller_Cron contrain actions that are executed with different frequency.
 * If Indi Engine is running as a a docker-compose project, then corresponging crontab-entries
 * are set up within apache-container, so custom Admin_CronController can be created extending
 * this class to override certain actions for custom needs
 */
class Indi_Controller_Cron extends Indi_Controller {

    public function minuteAction() {

    }

    public function hourlyAction() {

    }

    public function dailyAction() {

    }

    public function weeklyAction() {

    }

    public function monthlyAction() {

    }
}