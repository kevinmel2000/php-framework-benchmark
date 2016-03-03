<?php defined('SYSPATH') OR die('No direct script access.') ?>
<?php if (Kohana::$environment >= Kohana::DEVELOPMENT) {
?>
<?php

// Unique error identifier
$error_id = uniqid('error');

?>
<style type="text/css">
    #kohana_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
    #kohana_error h1,
    #kohana_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
    #kohana_error h1 a,
    #kohana_error h2 a { color: #fff; }
    #kohana_error h2 { background: #222; }
    #kohana_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
    #kohana_error p { margin: 0; padding: 0.2em 0; }
    #kohana_error a { color: #1b323b; }
    #kohana_error pre { overflow: auto; white-space: pre-wrap; }
    #kohana_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
    #kohana_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
    #kohana_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
    #kohana_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
    #kohana_error pre.source span.line { display: block; }
    #kohana_error pre.source span.highlight { background: #f0eb96; }
    #kohana_error pre.source span.line span.number { color: #666; }
    #kohana_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
    #kohana_error ol.trace li { margin: 0; padding: 0; }
    .js .collapsed { display: none; }
</style>
<script type="text/javascript">
    document.documentElement.className = document.documentElement.className + ' js';
    function koggle(elem)
    {
        elem = document.getElementById(elem);

        if (elem.style && elem.style['display'])
        // Only works with the "style" attr
            var disp = elem.style['display'];
        else if (elem.currentStyle)
        // For MSIE, naturally
            var disp = elem.currentStyle['display'];
        else if (window.getComputedStyle)
        // For most other browsers
            var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

        // Toggle the state of the "display" style
        elem.style.display = disp == 'block' ? 'none' : 'block';
        return false;
    }
</script>
<div id="kohana_error">
    <h1><span class="type"><?php echo $class ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo htmlspecialchars( (string) $message, ENT_QUOTES, Kohana::$charset, TRUE); ?></span></h1>
    <div id="<?php echo $error_id ?>" class="content">
        <p><span class="file"><?php echo Debug::path($file) ?> [ <?php echo $line ?> ]</span></p>
        <?php echo Debug::source($file, $line) ?>
        <ol class="trace">
            <?php foreach (Debug::trace($trace) as $i => $step): ?>
            <li>
                <p>
					<span class="file">
						<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
                        <a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo Debug::path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
                        <?php else: ?>
                        {<?php echo __('PHP internal call') ?>}
                        <?php endif ?>
					</span>
                    &raquo;
                    <?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo __('arguments') ?></a><?php endif ?>)
                </p>
                <?php if (isset($args_id)): ?>
                <div id="<?php echo $args_id ?>" class="collapsed">
                    <table cellspacing="0">
                        <?php foreach ($step['args'] as $name => $arg): ?>
                        <tr>
                            <td><code><?php echo $name ?></code></td>
                            <td><pre><?php echo Debug::dump($arg) ?></pre></td>
                        </tr>
                        <?php endforeach ?>
                    </table>
                </div>
                <?php endif ?>
                <?php if (isset($source_id)): ?>
                <pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
                <?php endif ?>
            </li>
            <?php unset($args_id, $source_id); ?>
            <?php endforeach ?>
        </ol>
    </div>
    <h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Environment') ?></a></h2>
    <div id="<?php echo $env_id ?>" class="content collapsed">
        <?php $included = get_included_files() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Included files') ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo Debug::path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php $included = get_loaded_extensions() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo __('Loaded extensions') ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo Debug::path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
        <?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($GLOBALS[$var] as $key => $value): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars( (string) $key, ENT_QUOTES, Kohana::$charset, TRUE); ?></code></td>
                    <td><pre><?php echo Debug::dump($value) ?></pre></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php endforeach ?>
    </div>
</div>
<?php
}
else {
?>
<?php defined('SYSPATH') or die('No direct script access.');
    ?>
<!DOCTYPE html>
<html>
<head>
    <style type="text/css">
        html, body {
            font-family: 'Open Sans',sans-serif;
        }
        body {
            color: #333333;
            font-size: 14px;
            line-height: 20px;
        }
        .container-fluid:before, .container-fluid:after {
            content: "";
            display: table;
            line-height: 0;
        }
        .container-fluid:after {
            clear: both;
        }
        .container-fluid:before, .container-fluid:after {
            content: "";
            display: table;
            line-height: 0;
        }
        .container-fluid {
            padding-left: 20px;
            padding-right: 20px;
        }
        .row-fluid:before, .row-fluid:after {
            content: "";
            display: table;
            line-height: 0;
        }
        .row-fluid:after {
            clear: both;
        }
        .row-fluid:before, .row-fluid:after {
            content: "";
            display: table;
            line-height: 0;
        }
        div.row-fluid {
            margin-bottom: 30px;
        }
        .row-fluid {
            width: 100%;
        }
        .row-fluid [class*="span"] {
            -moz-box-sizing: border-box;
            display: block;
            float: left;
            min-height: 30px;
        }
        .row-fluid .span2 {
            width: 14.8936%;
        }
        .row-fluid .span8 {
            width: 65.9574%;
        }
        .page-container {
            background: none repeat scroll 0 0 #FFFFFF;
            box-shadow: 1px 1px 2px 0 rgba(0, 0, 0, 0.3);
            margin-top: 50px;
        }
        h1.heading {
            font-size: 200px;
            font-weight: normal;
            padding: 20px;
            text-align: center;
        }
        h4.subheading {
            font-size: 70px;
            font-weight: normal;
            margin-left: 390px;
            margin-top: -200px;
            padding: 20px;
            text-align: center;
            transform: rotate(-90deg);
            -ms-transform:rotate(-90deg); /* IE 9 */
            -webkit-transform:rotate(-90deg); /* Safari and Chrome */
        }
        p.first-line {
            font-size: 20px;
            margin-top: 120px;
            text-align: center;
        }
        p.second-line {
            font-size: 16px;
            font-weight: lighter;
            text-align: center;
        }
        .buttonHolder {
            margin: 30px 0;
            text-align: center;
        }
        .btn-small {
            border-radius: 3px 3px 3px 3px;
            font-size: 11.9px;
            padding: 2px 10px;
        }
        .btn {
            -moz-border-bottom-colors: none;
            -moz-border-left-colors: none;
            -moz-border-right-colors: none;
            -moz-border-top-colors: none;
            background-color: #F5F5F5;
            background-image: linear-gradient(to bottom, #FFFFFF, #E6E6E6);
            background-repeat: repeat-x;
            border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) #B3B3B3;
            border-image: none;
            border-radius: 4px 4px 4px 4px;
            border-style: solid;
            border-width: 1px;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
            color: #333333;
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
            line-height: 20px;
            margin-bottom: 0;
            padding: 4px 12px;
            text-align: center;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
            vertical-align: middle;
        }
        a {
            color: #0088CC;
            text-decoration: none;
        }
        .btn, .btn:focus {
            background: none repeat scroll 0 0 #F2F2F2;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: none;
            color: #616161;
            outline: 0 none;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1);
        }
        .btn-danger, .btn-danger:focus {
            background: none repeat scroll 0 0 #FF4444;
        }
        .btn-danger, .btn-danger:focus, .btn-danger:hover, .btn-danger:active, .btn-danger.dropdown-toggle, .btn-danger.disabled, .btn-danger.disabled:hover, .btn-danger.disabled:active {
            border: 1px solid rgba(0, 0, 0, 0);
            color: #FFFFFF;
            outline: 0 none;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row-fluid">
        <div class="span2"></div>
        <div class="span8 page-container">
            <h1 class="heading">666</h1>
            <h4 class="subheading">error</h4>
            <p class="first-line">aaaaarggghhhh not again</p>
            <p class="second-line">probably someone messing with the source code. We'll fix it soon enough</p>
            <div class="buttonHolder">
                <a class="btn btn-small btn-danger" href="<?php echo URL::base(); ?>">Return to home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
}
?>