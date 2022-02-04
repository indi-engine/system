<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?=ini('general')->title ?: 'Indi Engine'?></title>
    <!-- Imploded and gzipped scripts and styles -->
    <?$this->other('gz')?>
    <script type="text/javascript" src="/js/admin/indi.all.<?=ini('lang')->admin?>.<?eif(isIE(),'ie','gz')?>.js"></script>
    <script type="text/javascript" src="/js/admin/indi.all.<?eif(isIE(),'ie','gz')?>.js"></script>
    <link type="text/css" rel="stylesheet" href="/css/admin/indi.all.<?eif(isIE(),'ie','gz')?>.css"/>
    <script>
    Ext.create('Indi', {
        statics: {
            std: '<?=STD?>',
            com: '<?=COM ? '' : '/admin'?>',
            pre: '<?=PRE?>',
            uri: <?=json_encode(uri()->toArray())?>,
            title: '<?=ini('general')->title ?: 'Indi Engine'?>',
            throwOutMsg: '<?=$this->throwOutMsg?>',
            lang: <?=json_encode($this->lang)?>
        }
    });
    </script>
</head>
<body class="i-login"><div id="i-login-box"></div></body>
</html>