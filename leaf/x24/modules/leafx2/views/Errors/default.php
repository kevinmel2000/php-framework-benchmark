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
            <h1 class="heading"><?php echo $error_code; ?></h1>
            <h4 class="subheading">error</h4>
            <p class="first-line"><?php echo $error_rant; ?></p>
            <p class="second-line"><?php echo $error_message; ?></p>
            <div class="buttonHolder">
                <a class="btn btn-small btn-danger" href="<?php echo URL::base(); ?>">Return to home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>