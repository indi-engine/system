<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?=Indi::ini('general')->title ?: 'Indi Engine'?></title>
    <?$this->other('gz')?>
    <script type="text/javascript" src="/library/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="/library/ckfinder/ckfinder.js"></script>
    <!-- Imploded and gzipped scripts and styles -->
    <script type="text/javascript" src="/js/admin/indi.all.<?=Indi::ini('lang')->admin?>.<?eif(isIE(),'ie','gz')?>.js"></script>
    <script type="text/javascript" src="/js/admin/indi.all.<?eif(isIE(),'ie','gz')?>.js"></script>
    <?if (Indi::ini('gmap')->key){?>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?=Indi::ini('gmap')->key?>"></script>
    <?}?>
    <?if (Indi::ini('ymap')->mode){?>
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
    <?}?>
    <script type="text/javascript" src="/library/Highstock-2.1.9/js/highstock.src.js"></script>
    <script src="/library/Highstock-2.1.9/current-price-indicator.js"></script>
    <link type="text/css" rel="stylesheet" href="/css/admin/indi.all.<?eif(isIE(),'ie','gz')?>.css"/>
</head>
<body id="body">
<script>
Ext.require(['*']);
Ext.create('Indi', {
    statics: {
        std: '<?=STD?>',
        com: '<?=COM ? '' : '/admin'?>',
        pre: '<?=PRE?>',
        uri: <?=json_encode(Indi::uri()->toArray())?>,
        time: <?=time()?>,
        menu: <?=json_encode($this->menu)?>,
        ini: {
            ws: <?=json_encode(array_merge((array) Indi::ini('ws'), ['pem' => is_file(DOC . STD . '/core/application/ws.pem')]))?>,
            demo: <?=Indi::demo(false) ? 'true' : 'false'?>
        },
        user: {
            title: '<?=admin()->title()?>',
            uid: '<?=admin()->profileId . '-' . admin()->id?>',
            role: '<?=admin()->foreign('profileId')->title?>',
            dashboard: <?=($d=admin()->foreign('profileId')->dashboard) ? '\'' . $d . '\'': 'false'?>,
            maxWindows: <?=admin()->foreign('profileId')->maxWindows ?: 15?>
        }
    }
});
</script>
<div style="display: none;">
    <div id="i-section-index-action-index-content"><?=$this->render('index/index.php');?></div>
    <div id="i-response-html"></div>
</div>
<label data-token="dummy" class="js-start_client_call"></label>
</body>
</html>