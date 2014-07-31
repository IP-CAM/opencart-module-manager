<!-- Styles -->
<link rel="stylesheet" type="text/css" href="/admin/view/stylesheet/teil/modules-list.css">
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/sass-bootstrap-glyphicons/css/bootstrap-glyphicons.css">

<!-- Mignify popup -->
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/magnific-popup/dist/magnific-popup.css">

<!-- Animate.css -->
<link rel="stylesheet" href="/admin/view/javascript/teil/bower_components/animate.css/animate.css">

<!-- Token -->
<input type="hidden" value="<?php echo $token ?>" id="token">
<input type="hidden" value="<?php echo $this->config->get('config_email') ?>" id="admin-email">
<input type="hidden" value="<?php echo DIR_TEIL_MODULES ?>" id="dir-teil-modules">
<input type="hidden" value="<?php echo $this->config->get('config_admin_language') ?>" id="admin-language">